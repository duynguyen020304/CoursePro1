<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
class Database
{
    private string $host = 'localhost';
    private string $user = 'duy_admin';
    private string $pass = 'duyadmin';
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
            if ($this->conn) {
                $alterSessionSQL = "ALTER SESSION SET TIME_ZONE = 'Asia/Ho_Chi_Minh'";
                $stid_alter = oci_parse($this->conn, $alterSessionSQL);

                if (!$stid_alter) {
                    $error = oci_error($this->conn);
                    $this->handleOracleError($error, "OCI Parse failed for ALTER SESSION TIME_ZONE. SQL: " . $alterSessionSQL);
                } else {
                    if (!@oci_execute($stid_alter, OCI_DEFAULT)) { // OCI_DEFAULT thường là auto-commit cho DDL
                        $error = oci_error($stid_alter);
                        $this->handleOracleError($error, "OCI Execute failed for ALTER SESSION TIME_ZONE. SQL: " . $alterSessionSQL);
                    } else {
                        error_log("[DB-OCI8] Successfully executed: " . $alterSessionSQL);
                        // Xác minh ngay lập tức
                        $tz_check_sql = "SELECT SESSIONTIMEZONE FROM DUAL";
                        $stid_check = @oci_parse($this->conn, $tz_check_sql);
                        if ($stid_check && @oci_execute($stid_check)) {
                            if (($row_tz = @oci_fetch_assoc($stid_check)) !== false) {
                                error_log("[DB-OCI8] Verified SESSIONTIMEZONE: " . $row_tz['SESSIONTIMEZONE']);
                                // Oracle có thể trả về +07:00 thay vì tên vùng
                                if (strcasecmp($row_tz['SESSIONTIMEZONE'], 'Asia/Ho_Chi_Minh') != 0 && strcasecmp($row_tz['SESSIONTIMEZONE'], '+07:00') != 0) {
                                    error_log("[DB-OCI8] WARNING: SESSIONTIMEZONE after ALTER is '" . $row_tz['SESSIONTIMEZONE'] . "', expected 'Asia/Ho_Chi_Minh' or '+07:00'.");
                                }
                            } else {
                                error_log("[DB-OCI8] Could not fetch SESSIONTIMEZONE for verification.");
                            }
                            @oci_free_statement($stid_check);
                        } else {
                            $error_check = $stid_check ? oci_error($stid_check) : oci_error($this->conn);
                            $this->handleOracleError($error_check, "Failed to execute/parse SESSIONTIMEZONE check.");
                        }
                    }
                    @oci_free_statement($stid_alter);
                }
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
            $this->handleOracleError($error, 'OCI Parse failed for prepared statement. SQL: ' . substr($sql,0,200) . '...');
            return false;
        }

        $lob_descriptors = [];
        $final_bind_values_map = [];

        foreach ($bindParams as $key => $original_value) {
            $placeholder_name = (strpos($key, ':') !== 0) ? ':' . $key : $key;

            if (is_array($original_value) && isset($original_value['type'])) {
                if ($original_value['type'] === OCI_B_CLOB) {
                    $lob_data = $original_value['value'];
                    $lob = @oci_new_descriptor($this->conn, OCI_D_LOB);
                    if ($lob) {
                        if ($lob_data !== null) {
                            if (!$lob->writeTemporary((string)$lob_data, OCI_TEMP_CLOB)) {
                                $this->handleOracleError(oci_error($lob), "Failed to write temporary LOB for {$placeholder_name}");
                                foreach ($lob_descriptors as $ld) { @$ld->free(); }
                                @oci_free_statement($stid);
                                return false;
                            }
                        }
                        $final_bind_values_map[$placeholder_name] = $lob;
                        $lob_descriptors[] = $lob;
                    } else {
                        $this->handleOracleError(oci_error($this->conn), "Failed to create LOB descriptor for {$placeholder_name}");
                        @oci_free_statement($stid);
                        return false;
                    }
                }
            } else {
                $final_bind_values_map[$placeholder_name] = $original_value;
            }
        }

        foreach ($final_bind_values_map as $placeholder_name => &$value_to_bind) {
            $paramType = SQLT_CHR;
            $paramMaxLength = -1;

            if (is_object($value_to_bind) && $value_to_bind instanceof OCILob) {
                $paramType = OCI_B_CLOB;
            } elseif (is_bool($value_to_bind)) {
                $value_to_bind = (int)$value_to_bind;
            }

            if (!@oci_bind_by_name($stid, $placeholder_name, $value_to_bind, $paramMaxLength, $paramType)) {
                $error = oci_error($stid);
                $logValue = is_object($value_to_bind) ? get_class($value_to_bind) : (is_array($value_to_bind) ? 'Array' : $value_to_bind);
                $this->handleOracleError($error, "OCI Bind failed for parameter {$placeholder_name}. Value type: " . gettype($value_to_bind) . ", Value: " . $logValue);
                foreach ($lob_descriptors as $ld) { @$ld->free(); }
                @oci_free_statement($stid);
                return false;
            }
        }
        unset($value_to_bind);


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

        return $stid;
    }

    public function execute(string $sql): mixed
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Oracle database';
            return null;
        }

        $this->lastQuery = $sql;
        $this->affectedRows = 0;

        $stid = @oci_parse($this->conn, $sql);
        if (!$stid) {
            $error = oci_error($this->conn);
            $this->handleOracleError($error, 'OCI Parse failed. SQL: ' . substr($sql, 0, 200) . '...');
            return false;
        }

        $execute_mode = OCI_DEFAULT;
        $is_tcl = preg_match('/^\s*(COMMIT|ROLLBACK)/i', $sql);
        $is_select = preg_match('/^\s*SELECT/i', $sql);
        $is_dml = preg_match('/^\s*(INSERT|UPDATE|DELETE|MERGE)/i', $sql);

        if ($this->inTransaction && !$is_tcl) {
            $execute_mode = OCI_NO_AUTO_COMMIT;
        } elseif (($is_dml || $is_tcl) && !$is_select) {
            if ($is_dml) {
                $execute_mode = OCI_COMMIT_ON_SUCCESS;
            }
        }

        if (!@oci_execute($stid, $execute_mode)) {
            $error = oci_error($stid);
            $this->handleOracleError($error, 'OCI Execute failed. SQL: ' . substr($sql, 0, 200) . '...');
            @oci_free_statement($stid);
            return false;
        }

        if ($is_dml) {
            $this->affectedRows = @oci_num_rows($stid);
            @oci_free_statement($stid);
            return true;
        }

        return $stid;
    }


    public function fetchAll(string $sql, int $mode = OCI_ASSOC): array
    {
        $stid = $this->execute($sql);
        if (!$stid || !is_resource($stid)) {
            if (is_resource($stid)) @oci_free_statement($stid);
            if ($stid === true) {
                $this->lastError = "fetchAll called on a non-SELECT query or successful DML.";
                error_log("[DB-OCI8] " . $this->lastError . " SQL: " . $sql);
            }
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
            if ($stid === true) {
                $this->lastError = "fetchRow called on a non-SELECT query or successful DML.";
                error_log("[DB-OCI8] " . $this->lastError . " SQL: " . $sql);
            }
            return null;
        }
        $row = @oci_fetch_array($stid, $mode + OCI_RETURN_NULLS);
        @oci_free_statement($stid);
        return $row === false ? null : $row;
    }

    public function runPlSqlScript(string $sqlScript): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Oracle database for script execution';
            error_log('[DB-OCI8] runPlSqlScript: Not connected.');
            return false;
        }

        $sqlScript = str_replace(["\r\n", "\r"], "\n", $sqlScript);
        // Remove comments AFTER splitting by / to preserve comments within PL/SQL blocks if any
        // However, for simplicity and to avoid issues with / in comments, removing them first is safer.
        $sqlScript = preg_replace('/--[^\n]*\n?/s', '', $sqlScript);
        $sqlScript = preg_replace('/\/\*.*?\*\//s', '', $sqlScript);
        $sqlScript = trim($sqlScript);

        if (empty($sqlScript)) {
            return true;
        }

        // Split script by '/' on its own line
        $blocks = preg_split('/^\s*\/\s*($|\n)/m', $sqlScript);
        $all_successful = true;

        foreach ($blocks as $block_idx => $block_content) {
            $trimmed_block = trim($block_content);
            if (empty($trimmed_block)) {
                continue;
            }

            $this->lastQuery = "PL/SQL Script Block #{$block_idx}: " . substr($trimmed_block, 0, 200) . "...";
            error_log("[DB-OCI8] Executing PL/SQL Script Block #{$block_idx}: " . substr($trimmed_block, 0, 100) . "...");

            $result = $this->execute($trimmed_block);

            if ($result === false) {
                $this->lastError = "PL/SQL Script block #{$block_idx} failed: " . ($this->getLastError() ?? 'Unknown error during script execution.');
                error_log("[DB-OCI8] " . $this->lastError . " Block content (first 200 chars): " . substr($trimmed_block, 0, 200));
                $all_successful = false;
                break;
            }
        }
        return $all_successful;
    }

    public function runSchemaScript(string $sqlScript): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Oracle database for script execution';
            error_log('[DB-OCI8] runSchemaScript: Not connected.');
            return false;
        }

        $sqlScript = str_replace(["\r\n", "\r"], "\n", $sqlScript);
        $sqlScript = preg_replace('/--[^\n]*\n?/s', '', $sqlScript);
        $sqlScript = preg_replace('/\/\*.*?\*\//s', '', $sqlScript);
        $sqlScript = trim($sqlScript);

        if (empty($sqlScript)) {
            return true;
        }

        // Remove a trailing semicolon from the whole script to avoid an empty last statement after explode
        if (substr($sqlScript, -1) === ';') {
            $sqlScript = substr($sqlScript, 0, -1);
        }

        $statements = explode(';', $sqlScript);
        $all_successful = true;

        foreach ($statements as $statement_idx => $statement_text) {
            $trimmed_statement = trim($statement_text);
            if (empty($trimmed_statement)) {
                continue;
            }

            $this->lastQuery = "Schema Script Statement #{$statement_idx}: " . substr($trimmed_statement, 0, 200) . "...";
            error_log("[DB-OCI8] Executing Schema Statement #{$statement_idx}: " . substr($trimmed_statement, 0, 100) . "...");

            $result = $this->execute($trimmed_statement);

            if ($result === false) {
                $this->lastError = "Schema Script statement #{$statement_idx} failed: " . ($this->getLastError() ?? 'Unknown error during script execution.');
                error_log("[DB-OCI8] " . $this->lastError . " Statement: " . substr($trimmed_statement, 0, 100));
                $all_successful = false;
                break;
            }
        }
        return $all_successful;
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

        foreach ($statements as $statement_idx => $statement) {
            $trimmed_statement = trim($statement);
            if (empty($trimmed_statement)) {
                continue;
            }

            $result = $this->execute($trimmed_statement);
            if ($result === false || $result === null) {
                $this->lastError = "Script statement #{$statement_idx} failed: " . ($this->getLastError() ?? 'Unknown error during script execution.');
                error_log("[DB-OCI8] " . $this->lastError . " Statement: " . substr($trimmed_statement, 0, 100));
                $all_successful = false;
                break;
            }
            if (is_resource($result)) {
                @oci_free_statement($result);
            }
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
        if (!$this->isConnected() || !$this->inTransaction) return false;
        $result = @oci_commit($this->conn);
        if (!$result) {
            $this->handleOracleError(oci_error($this->conn), 'OCI Commit failed');
        }
        $this->inTransaction = false;
        return $result;
    }

    public function rollback(): bool
    {
        if (!$this->isConnected() || !$this->inTransaction) return false;
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
            if ($this->inTransaction) {
                @oci_rollback($this->conn);
                $this->inTransaction = false;
            }
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
        } elseif ($this->conn === null || $this->conn === false) {
            $this->lastError = "Oracle connection is not active or failed.";
        } else {
            $conn_error = @oci_error($this->conn);
            if ($conn_error && isset($conn_error['message'])) {
                $this->lastError = "Code: {$conn_error['code']} - Message: " . trim($conn_error['message']);
            } else {
                $this->lastError = "An unknown Oracle error occurred or no active connection for error reporting.";
            }
        }
        $msg = "[DB-OCI8] {$context}: {$this->lastError}";
        error_log($msg);
    }

    public function __destruct()
    {
        $this->close();
    }
}