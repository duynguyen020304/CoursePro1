<?php

class Database
{
    // Default Oracle connection parameters - CHANGE THESE to your actual Oracle settings
    private string $host   = 'localhost';        // Oracle host, or TNS alias, or Easy Connect string part
    private string $user   = 'your_oracle_user'; // Your Oracle username
    private string $pass   = 'your_oracle_password'; // Your Oracle password
    private string $dbService = 'XE';            // Oracle Service Name or SID (e.g., 'XE', 'ORCLPDB1')
    private string $charset = 'AL32UTF8';        // Client character set for OCI8 connection
    private int $databasePort = 1521;           // Default Oracle listener port

    protected mixed $conn = null; // OCI8 Connection resource
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

    /**
     * Executes a prepared SQL statement with bind variables.
     *
     * @param string $sql The SQL query string with placeholders (e.g., :name).
     * @param array $bindParams An associative array of bind parameters (e.g., [':name' => $value]).
     * The key is the placeholder name (should start with a colon, e.g., ':id').
     * The value is the value to bind.
     * For CLOBs, pass an array ['value' => $clob_data, 'type' => OCI_B_CLOB].
     * @return mixed The OCI8 statement handle on success (for SELECT or DML), false on failure.
     */
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
        // This array will hold the actual values/LOB descriptors to be bound.
        // Keys will be the placeholder names (e.g., ':userID').
        $final_bind_values_map = [];

        // First pass: prepare all values, especially LOBs, and store them in $final_bind_values_map
        foreach ($bindParams as $key => $original_value) {
            $placeholder_name = (strpos($key, ':') !== 0) ? ':' . $key : $key;

            if (is_array($original_value) && isset($original_value['type'])) {
                if ($original_value['type'] === OCI_B_CLOB) {
                    $lob_data = $original_value['value'];
                    $lob = @oci_new_descriptor($this->conn, OCI_D_LOB);
                    if ($lob) {
                        if ($lob_data !== null) {
                            if (!$lob->writeTemporary((string)$lob_data, OCI_TEMP_CLOB)) { // Ensure $lob_data is string
                                $this->handleOracleError(oci_error($lob), "Failed to write temporary LOB for {$placeholder_name}");
                                foreach ($lob_descriptors as $ld) { @$ld->free(); } // Free any already created LOBs
                                @oci_free_statement($stid);
                                return false;
                            }
                        }
                        $final_bind_values_map[$placeholder_name] = $lob; // Store the LOB descriptor
                        $lob_descriptors[] = $lob; // Keep track to free all LOBs at the end
                    } else {
                        $this->handleOracleError(oci_error($this->conn), "Failed to create LOB descriptor for {$placeholder_name}");
                        // No need to free $lob_descriptors here as they are freed globally later or on statement free
                        @oci_free_statement($stid);
                        return false;
                    }
                }
                // Add handling for other special array types (e.g., OCI_B_BLOB) here if needed
                // else { $final_bind_values_map[$placeholder_name] = $original_value; } // Or decide how to handle unexpected arrays
            } else {
                // For scalar values (string, int, null, float, bool)
                $final_bind_values_map[$placeholder_name] = $original_value;
            }
        }

        // Second pass: bind the prepared values from $final_bind_values_map
        // Iterate over $final_bind_values_map using its keys (which are placeholder names)
        // and bind its values by reference.
        foreach ($final_bind_values_map as $placeholder_name => &$value_to_bind) { // IMPORTANT: Use reference for $value_to_bind
            $paramType = SQLT_CHR; // Default type for strings, numbers, dates (Oracle converts)
            $paramMaxLength = -1;  // OCI8 handles length for SQLT_CHR

            if (is_object($value_to_bind) && $value_to_bind instanceof OCILob) {
                $paramType = OCI_B_CLOB; // Or OCI_B_BLOB if you support it based on LOB type
            } elseif (is_int($value_to_bind)) {
                // SQLT_CHR is generally fine. For very large integers, consider SQLT_INT or specific numeric types.
            } elseif (is_null($value_to_bind)) {
                // oci_bind_by_name with SQLT_CHR handles PHP null correctly.
            } elseif (is_bool($value_to_bind)) {
                $value_to_bind = (int)$value_to_bind; // Convert boolean to 0 or 1 for DB
            }
            // Add other type checks if necessary (e.g., for floats)

            // $placeholder_name is already correctly formatted (e.g., ':userID')
            if (!@oci_bind_by_name($stid, $placeholder_name, $value_to_bind, $paramMaxLength, $paramType)) {
                $error = oci_error($stid);
                $logValue = is_object($value_to_bind) ? get_class($value_to_bind) : (is_array($value_to_bind) ? 'Array' : $value_to_bind);
                $this->handleOracleError($error, "OCI Bind failed for parameter {$placeholder_name}. Value type: " . gettype($value_to_bind) . ", Value: " . $logValue);
                foreach ($lob_descriptors as $ld) { @$ld->free(); }
                @oci_free_statement($stid);
                return false;
            }
        }
        unset($value_to_bind); // Crucial: break the reference from the last loop iteration


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

        // Free LOB descriptors after execution
        // oci_free_statement should also free temporary LOBs associated with the statement,
        // but explicit freeing is safer for descriptors we allocated.
        foreach ($lob_descriptors as $ld) {
            @$ld->free();
        }

        return $stid;
    }

    // ... (fetchAll, fetchRow, runScript, begin, commit, rollback, close, handleOracleError, __destruct methods remain as before) ...
    // ... Ensure these methods (fetchAll, fetchRow, runScript) are using the simple execute() if they are for
    // non-prepared SQL, or adapt them if they should also use prepared statements.
    // Based on previous context, execute() is for simple, non-parameterized queries or when user handles escaping.
    // For BLLs, we should be using executePrepared.

    public function execute(string $sql): mixed // This is the simplified execute, kept for compatibility or specific uses
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
            @oci_free_statement($stid); // Free DML statement handle after getting affected rows
            return true; // Return true for successful DML
        }

        return $stid; // Return statement handle for SELECTs or other successful statements
    }


    public function fetchAll(string $sql, int $mode = OCI_ASSOC): array
    {
        // This method uses the simple execute(). If it needs to handle prepared statements, it should be adapted.
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
        // This method uses the simple execute().
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

    public function runScript(string $sqlScript): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Not connected to Oracle database for script execution';
            return false; // Changed from null to false for consistency with other method failures
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
                @oci_rollback($this->conn); // Rollback any pending transaction on close
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
            $conn_error = @oci_error($this->conn); // Try to get error from connection
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
?>
