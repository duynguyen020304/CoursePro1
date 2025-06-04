<?php

class DatabaseBackUpOracle
{
    private string $host         = 'localhost';
    private string $user         = 'your_oracle_user';
    private string $pass         = 'your_oracle_password';
    private string $serviceName  = 'ORCLPDB1';
    private string $charset      = 'AL32UTF8';
    private int $databasePort    = 1521;

    protected $conn = null;
    private ?string $lastError = null;
    private ?string $lastQuery = null;
    private int $affectedRows = 0;
    private bool $inTransaction = false;

    public const FETCH_ASSOC = OCI_ASSOC;
    public const FETCH_NUM   = OCI_NUM;
    public const FETCH_BOTH  = OCI_BOTH;

    public function __construct(
        string $host = 'localhost',
        string $user = 'your_oracle_user',
        string $pass = 'your_oracle_password',
        string $serviceName = 'ORCLPDB1',
        int $port = 1521,
        string $charset = 'AL32UTF8'
    ) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->serviceName = $serviceName;
        $this->databasePort = $port;
        $this->charset = $charset;

        if (!function_exists('oci_connect')) {
            $this->lastError = "PHP OCI8 extension is not enabled or not installed.";
            error_log("[DB] CRITICAL: " . $this->lastError);
            return;
        }

        $connectionString = "//{$this->host}:{$this->databasePort}/{$this->serviceName}";

        try {
            $this->conn = @oci_connect(
                $this->user,
                $this->pass,
                $connectionString,
                $this->charset
            );

            if (!$this->conn) {
                $this->handleOracleError(null, 'Kết nối database thất bại (oci_connect failed)');
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("[DB] Exception during connection: {$this->lastError}");
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

    public function execute(string $sql)
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to database';
            return null;
        }

        $this->lastQuery = $sql;
        $this->affectedRows = 0;

        $stmt = oci_parse($this->conn, $sql);

        if (!$stmt) {
            $this->handleOracleError($this->conn, 'Query parse failed');
            return false;
        }

        $isDML = preg_match('/^\s*(INSERT|UPDATE|DELETE|MERGE|CREATE|ALTER|DROP|TRUNCATE)\s/i', $sql);

        $executeMode = OCI_DEFAULT;
        if ($isDML) {
            $executeMode = $this->inTransaction ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS;
        }

        $result = @oci_execute($stmt, $executeMode);

        if (!$result) {
            $this->handleOracleError($stmt, 'Query execution failed');
            oci_free_statement($stmt);
            return false;
        }

        if ($isDML) {
            $this->affectedRows = oci_num_rows($stmt);
            oci_free_statement($stmt);
            return true;
        } else {
            return $stmt;
        }
    }

    public function fetchAll(string $sql, int $mode = self::FETCH_ASSOC): array
    {
        $stmtOrResult = $this->execute($sql);

        if ($stmtOrResult === false || $stmtOrResult === true || $stmtOrResult === null) {
            return [];
        }

        $stmt = $stmtOrResult;
        $rows = [];
        while (($row = oci_fetch_array($stmt, $mode | OCI_RETURN_NULLS | OCI_RETURN_LOBS)) !== false) {
            $rows[] = $row;
        }

        oci_free_statement($stmt);
        return $rows;
    }

    public function fetchRow(string $sql, int $mode = self::FETCH_ASSOC): ?array
    {
        $stmtOrResult = $this->execute($sql);

        if ($stmtOrResult === false || $stmtOrResult === true || $stmtOrResult === null) {
            return null;
        }
        $stmt = $stmtOrResult;
        $row = oci_fetch_array($stmt, $mode | OCI_RETURN_NULLS | OCI_RETURN_LOBS);
        oci_free_statement($stmt);

        return ($row === false) ? null : $row;
    }

    public function runScript(string $sql): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to database';
            return false;
        }
        $this->lastQuery = $sql;
        $this->affectedRows = 0;

        $stmt = oci_parse($this->conn, $sql);
        if (!$stmt) {
            $this->handleOracleError($this->conn, 'Script parse failed');
            return false;
        }

        $executeMode = $this->inTransaction ? OCI_NO_AUTO_COMMIT : OCI_COMMIT_ON_SUCCESS;

        $result = @oci_execute($stmt, $executeMode);
        if (!$result) {
            $this->handleOracleError($stmt, 'Script execution failed');
            oci_free_statement($stmt);
            return false;
        }

        $this->affectedRows = @oci_num_rows($stmt);
        oci_free_statement($stmt);
        return true;
    }

    public function begin(): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to database';
            return false;
        }
        if ($this->inTransaction) {
            return true;
        }
        $this->inTransaction = true;
        return true;
    }

    public function commit(): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to database';
            return false;
        }
        if (!$this->inTransaction) {
            $this->lastError = 'No active transaction to commit.';
            return false;
        }

        $committed = @oci_commit($this->conn);
        if (!$committed) {
            $this->handleOracleError($this->conn, 'Commit failed');
            return false;
        }
        $this->inTransaction = false;
        return true;
    }

    public function rollback(): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to database';
            return false;
        }
        if (!$this->inTransaction) {
            $this->lastError = 'No active transaction to rollback.';
            return false;
        }

        $rolledBack = @oci_rollback($this->conn);
        if (!$rolledBack) {
            $this->handleOracleError($this->conn, 'Rollback failed');
            return false;
        }
        $this->inTransaction = false;
        return true;
    }

    public function close(): void
    {
        if ($this->conn) {
            if ($this->inTransaction) {
                error_log("[DB] WARN: Closing connection with an active transaction. Rolling back.");
                @oci_rollback($this->conn);
                $this->inTransaction = false;
            }
            @oci_close($this->conn);
            $this->conn = null;
        }
    }

    private function handleOracleError($resource = null, string $context = ''): void
    {
        $error = $resource ? @oci_error($resource) : @oci_error();
        if ($error && is_array($error)) {
            $this->lastError = "Oracle Error Code: {$error['code']}, Message: {$error['message']}";
            if (!empty($error['sqltext'])) {
                $this->lastError .= ", SQL: " . substr($error['sqltext'], 0, 200) . (strlen($error['sqltext']) > 200 ? "..." : "");
            }
            if (!empty($error['offset'])) {
                $this->lastError .= ", Offset: {$error['offset']}";
            }
        } elseif (is_string($error) && !empty($error)) {
            $this->lastError = $error;
        } else {
            $this->lastError = 'An unknown OCI error occurred or no error information available.';
        }
        $msg = "[DB] {$context}: {$this->lastError}";
        error_log($msg);
    }

    public function __destruct()
    {
        $this->close();
    }
}
