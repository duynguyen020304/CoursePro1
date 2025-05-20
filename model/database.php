<?php

class Database
{
    private string $host   = 'localhost';
    private string $user   = 'duy_admin';
    private string $pass   = 'duyadmin';
    private string $dbService = 'QUANLYKHOAHOC';
    private string $charset = 'AL32UTF8';
    private int $databasePort = 1521;

    protected mixed $conn = null;
    private ?string $lastError = null;
    private ?string $lastQuery = null;
    private int $affectedRows = 0;
    protected bool $inTransaction = false;

    public function __construct(
        string $host = '',
        string $user = '',
        string $pass = '',
        string $dbService = '',
        int $port = 0,
        string $charset = ''
    ) {
        $this->host = !empty($host) ? $host : $this->host;
        $this->user = !empty($user) ? $user : $this->user;
        $this->pass = !empty($pass) ? $pass : $this->pass;
        $this->dbService = !empty($dbService) ? $dbService : $this->dbService;
        $this->databasePort = $port ?: $this->databasePort;
        $this->charset = !empty($charset) ? $charset : $this->charset;

        $connection_string = $this->host;
        if (strpos($this->host, ':/') === false && strpos($this->host, '=') === false) {
            $connection_string = "//{$this->host}:{$this->databasePort}/{$this->dbService}";
        }

        try {
            $this->conn = @oci_connect(
                $this->user,
                $this->pass,
                $connection_string,
                $this->charset
            );

            if (!$this->conn) {
                $error = oci_error();
                $this->handleOracleError($error, "Oracle Connection Failed. User: '{$this->user}', String: '{$connection_string}'");
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            $msg = "[DB-OCI8] Connection Exception: {$this->lastError}";
            error_log($msg);
            $this->conn = null;
        }
    }

    public function isConnected(): bool
    {
        return $this->conn !== null && $this->conn !== false;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function getLastQuery(): ?string
    {
        return $this->lastQuery;
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    public function execute(string $sql): mixed
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Oracle database';
            return false;
        }

        $this->lastQuery = $sql;
        $this->affectedRows = 0;

        $stid = @oci_parse($this->conn, $sql);
        if (!$stid) {
            $error = oci_error($this->conn);
            $this->handleOracleError($error, 'OCI Parse failed');
            return false;
        }

        $execute_mode = OCI_DEFAULT;
        $is_tcl = preg_match('/^\s*(COMMIT|ROLLBACK)/i', $sql);
        $is_select = preg_match('/^\s*SELECT/i', $sql);

        if ($this->inTransaction && !$is_tcl) {
            $execute_mode = OCI_NO_AUTO_COMMIT;
        } elseif (!$is_select && !$is_tcl) {
            $execute_mode = OCI_COMMIT_ON_SUCCESS;
        }

        if (!@oci_execute($stid, $execute_mode)) {
            $error = oci_error($stid);
            $this->handleOracleError($error, 'OCI Execute failed');
            @oci_free_statement($stid);
            return false;
        }

        if (preg_match('/^\s*(INSERT|UPDATE|DELETE|MERGE)/i', $sql)) {
            $this->affectedRows = @oci_num_rows($stid);
        }

        return $stid;
    }
    public function executePrepared(string $sql, array $bindParams = []): mixed
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Oracle database';
            error_log('[DB-OCI8] executePrepared: Not connected.');
            return false;
        }

        $this->lastQuery = $sql;
        $this->affectedRows = 0;

        $stid = @oci_parse($this->conn, $sql);
        if (!$stid) {
            $error = oci_error($this->conn);
            $this->handleOracleError($error, 'OCI Parse failed for prepared statement');
            return false;
        }

        $lob_descriptors = [];

        foreach ($bindParams as $key => $value) {
            $paramType = SQLT_CHR;
            $paramValue = $value;
            $paramMaxLength = -1;
            $bindKey = (strpos($key, ':') !== 0) ? ':' . $key : $key;

            if (is_array($value) && isset($value['type'])) {
                $paramValue = $value['value'];
                if ($value['type'] === OCI_B_CLOB) {
                    $lob = @oci_new_descriptor($this->conn, OCI_D_LOB);
                    if ($lob) {
                        if ($paramValue !== null) {
                            $lob->writeTemporary($paramValue, OCI_TEMP_CLOB);
                        }
                        $paramValue = $lob;
                        $lob_descriptors[] = $lob;
                    } else {
                        $this->handleOracleError(oci_error($this->conn), "Failed to create LOB descriptor for {$bindKey}");
                        foreach ($lob_descriptors as $ld) { @$ld->free(); }
                        @oci_free_statement($stid);
                        return false;
                    }
                    $paramType = OCI_B_CLOB;
                }
            } elseif (is_int($value)) {
                // Optional: handle explicit integer binding if needed
            } elseif (is_null($value)) {
                // PHP null binds as SQL NULL
            }

            if (!@oci_bind_by_name($stid, $bindKey, $paramValue, $paramMaxLength, $paramType)) {
                $error = oci_error($stid);
                $this->handleOracleError($error, "OCI Bind failed for parameter {$bindKey}");
                foreach ($lob_descriptors as $ld) { @$ld->free(); }
                @oci_free_statement($stid);
                return false;
            }
        }

        $execute_mode = OCI_DEFAULT;
        $is_tcl = preg_match('/^\s*(COMMIT|ROLLBACK)/i', $sql);
        $is_select = preg_match('/^\s*SELECT/i', $sql);

        if ($this->inTransaction && !$is_tcl) {
            $execute_mode = OCI_NO_AUTO_COMMIT;
        } elseif (!$is_select && !$is_tcl) {
            $execute_mode = OCI_COMMIT_ON_SUCCESS;
        }

        if (!@oci_execute($stid, $execute_mode)) {
            $error = oci_error($stid);
            $this->handleOracleError($error, "OCI Execute failed for prepared statement. SQL: " . substr($sql,0,200) . "...");
            foreach ($lob_descriptors as $ld) { @$ld->free(); }
            @oci_free_statement($stid);
            return false;
        }

        if (preg_match('/^\s*(INSERT|UPDATE|DELETE|MERGE)/i', $sql)) {
            $this->affectedRows = @oci_num_rows($stid);
        }

        foreach ($lob_descriptors as $ld) {
            @$ld->free();
        }

        if (!$is_select) {
            // Optionally free statement here if not needed further
        }

        return $stid;
    }

    public function fetchAll(string $sql, int $mode = OCI_ASSOC): array
    {
        $stid = $this->execute($sql);
        if (!$stid || !is_resource($stid)) {
            if (is_resource($stid)) @oci_free_statement($stid);
            return [];
        }

        $rows = [];
        while (($row = @oci_fetch_array($stid, $mode + OCI_RETURN_NULLS)) !== false) {
            $rows[] = $row;
        }

        @oci_free_statement($stid);
        return $rows;
    }

    public function fetchRow(string $sql, int $mode = OCI_ASSOC): ?array
    {
        $stid = $this->execute($sql);
        if (!$stid || !is_resource($stid)) {
            if (is_resource($stid)) @oci_free_statement($stid);
            return null;
        }

        $row = @oci_fetch_array($stid, $mode + OCI_RETURN_NULLS);
        @oci_free_statement($stid);

        return $row === false ? null : $row;
    }

    public function runScript(string $sqlScript): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Oracle database for script execution';
            return false;
        }
        $this->lastQuery = $sqlScript;

        $sqlScript = str_replace("\r\n", "\n", $sqlScript);
        $sqlScript = preg_replace('/--.*$/m', '', $sqlScript);
        $sqlScript = preg_replace('/\/\*.*?\*\//s', '', $sqlScript);

        $statements = explode(';', $sqlScript);
        $all_successful = true;

        $this->begin();

        foreach ($statements as $statement_idx => $statement) {
            $trimmed_statement = trim($statement);
            if (empty($trimmed_statement)) {
                continue;
            }

            $stid = @oci_parse($this->conn, $trimmed_statement);
            if (!$stid) {
                $error = oci_error($this->conn);
                $this->handleOracleError($error, "OCI Parse failed for script statement #{$statement_idx}: " . substr($trimmed_statement, 0, 100));
                $all_successful = false;
                break;
            }

            $is_ddl = preg_match('/^\s*(CREATE|ALTER|DROP|TRUNCATE)/i', $trimmed_statement);
            $is_tcl = preg_match('/^\s*(COMMIT|ROLLBACK)/i', $trimmed_statement);

            $execute_mode = OCI_NO_AUTO_COMMIT;
            if ($is_ddl || $is_tcl) {
                $execute_mode = OCI_DEFAULT;
            }

            if (!@oci_execute($stid, $execute_mode)) {
                $error = oci_error($stid);
                $this->handleOracleError($error, "OCI Execute failed for script statement #{$statement_idx}: " . substr($trimmed_statement, 0, 100));
                $all_successful = false;
                @oci_free_statement($stid);
                break;
            }
            @oci_free_statement($stid);
        }

        if ($all_successful) {
            $this->commit();
        } else {
            $this->rollback();
        }

        return $all_successful;
    }

    public function begin(): bool
    {
        if (!$this->isConnected()) return false;
        $this->inTransaction = true;
        return true;
    }

    public function commit(): bool
    {
        if (!$this->isConnected()) return false;
        $result = @oci_commit($this->conn);
        if (!$result) {
            $this->handleOracleError(oci_error($this->conn), 'OCI Commit failed');
        }
        $this->inTransaction = false;
        return $result;
    }

    public function rollback(): bool
    {
        if (!$this->isConnected()) return false;
        $result = @oci_rollback($this->conn);
        if (!$result) {
            $this->handleOracleError(oci_error($this->conn), 'OCI Rollback failed');
        }
        $this->inTransaction = false;
        return $result;
    }

    public function close(): void
    {
        if ($this->conn) {
            @oci_close($this->conn);
            $this->conn = null;
        }
    }

    private function handleOracleError($error, string $context = ''): void
    {
        if ($error && isset($error['message'])) {
            $this->lastError = "Code: {$error['code']} - Message: " . trim($error['message']);
            if (isset($error['sqltext']) && !empty($error['sqltext'])) {
                $this->lastError .= " (SQL Text: " . substr(trim($error['sqltext']), 0, 200) . "...)";
            }
            if (isset($error['offset'])) {
                $this->lastError .= " (Offset: {$error['offset']})";
            }
        } else {
            $this->lastError = "An unknown Oracle error occurred or no active connection for error reporting.";
        }
        $msg = "[DB-OCI8] {$context}: {$this->lastError}";
        error_log($msg);
    }

    public function __destruct()
    {
        $this->close();
    }
}