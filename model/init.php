<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("database.php");

class InitDatabase extends Database
{
    public function __construct(
        string $host = '',
        string $user = '',
        string $pass = '',
        string $dbService = '',
        int $port = 0,
        string $charset = ''
    ) {
        parent::__construct($host, $user, $pass, $dbService, $port, $charset);
    }

    /**
     * Executes an SQL file using the appropriate script runner based on type.
     *
     * @param string $filePath Path to the SQL file.
     * @param bool $isCli True if running from CLI, false otherwise.
     * @param string $scriptType Type of script: 'schema' for DDL/DML, 'plsql' for PL/SQL blocks.
     * @return bool True on success, false on failure.
     */
    private function executeSqlFile(string $filePath, bool $isCli, string $scriptType): bool
    {
        if (!file_exists($filePath)) {
            $errorMsg = "INIT FAILED: SQL file not found at {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            $errorMsg = "INIT FAILED: Could not read SQL file {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        echo $isCli ? "Attempting to execute SQL file: " . basename($filePath) . " (type: {$scriptType})...\n" : "<p>Attempting to execute SQL file: " . htmlspecialchars(basename($filePath)) . " (type: {$scriptType})...</p>";

        $success = false;
        if ($scriptType === 'schema') {
            $success = $this->runSchemaScript($sql);
        } elseif ($scriptType === 'plsql') {
            $success = $this->runPlSqlScript($sql);
        } else {
            $errorMsg = "INIT FAILED: Unknown script type '{$scriptType}' for file {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        if ($success) {
            $successMsg = "Successfully executed " . basename($filePath);
            echo $isCli ? $successMsg . "\n" : "<p style='color:green;'>" . htmlspecialchars($successMsg) . "</p>";
            return true;
        } else {
            $errorDetail = htmlspecialchars($this->getLastError() ?? 'Unknown error during script execution.');
            // getLastQuery() in Database class now stores the block/statement being executed by runPlSqlScript/runSchemaScript
            $lastQueryAttempted = htmlspecialchars(substr($this->getLastQuery() ?? '', 0, 1000));
            $failMsg = "FAILED to execute " . basename($filePath) . ". Last Error: " . $errorDetail;
            $queryInfo = "\nLast Query/Script Segment Attempted from " . basename($filePath) . ":\n" . $lastQueryAttempted;

            if ($isCli) {
                echo $failMsg . $queryInfo . "\n";
            } else {
                echo "<p style='color:red;'>" . $failMsg . "</p><pre>" . $queryInfo . "</pre>";
            }
            error_log($failMsg . " (Raw: " . $this->getLastError() . ")" . $queryInfo);
            return false;
        }
    }

    public function create_structure_and_procedures(): void
    {
        $isCli = php_sapi_name() === 'cli';

        if (!$this->isConnected()) {
            $errorMsg = "INIT FAILED: Not connected to the database. Last Error: " . htmlspecialchars($this->getLastError() ?? 'Unknown connection error. Check OCI8 setup and credentials.');
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg . " (Raw: " . $this->getLastError() . ")");
            return;
        }

        echo $isCli ? "Attempting to initialize database structure (schema.sql)...\n" : "<p>Attempting to initialize database structure (schema.sql)...</p>";
        $schemaFilePath = __DIR__ . '/schema.sql';
        // Use 'schema' type for schema.sql
        if (!$this->executeSqlFile($schemaFilePath, $isCli, 'schema')) {
            echo $isCli ? "Halting initialization due to error in schema.sql.\n" : "<p style='color:red;'>Halting initialization due to error in schema.sql.</p>";
            return;
        }
        echo $isCli ? "Database structure (schema.sql) initialized successfully.\n" : "<p style='color:green;'>Database structure (schema.sql) initialized successfully.</p>";

        $proceduresDir = __DIR__ . '/trigger_procedure/';
        echo $isCli ? "\nAttempting to execute trigger and procedure files from {$proceduresDir}...\n" : "<hr/><p>Attempting to execute trigger and procedure files from " . htmlspecialchars($proceduresDir) . "...</p>";

        if (!is_dir($proceduresDir)) {
            $errorMsg = "INIT WARNING: Directory not found: {$proceduresDir}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:orange;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
        } else {
            $sqlFiles = glob($proceduresDir . '*.sql');
            if (empty($sqlFiles)) {
                $infoMsg = "No .sql files found in {$proceduresDir}";
                echo $isCli ? $infoMsg . "\n" : "<p>" . htmlspecialchars($infoMsg) . "</p>";
            } else {
                $allProceduresSuccessful = true;
                foreach ($sqlFiles as $sqlFile) {
                    // Use 'plsql' type for files in trigger_procedure directory
                    if (!$this->executeSqlFile($sqlFile, $isCli, 'plsql')) {
                        $allProceduresSuccessful = false;
                        // Optionally break here if one PL/SQL script fails, or continue all
                        // break;
                    }
                }
                if ($allProceduresSuccessful) {
                    echo $isCli ? "All trigger and procedure files executed successfully.\n" : "<p style='color:green;'>All trigger and procedure files executed successfully.</p>";
                } else {
                    echo $isCli ? "Some trigger and procedure files failed to execute. Please check logs.\n" : "<p style='color:red;'>Some trigger and procedure files failed to execute. Please check logs.</p>";
                }
            }
        }

        $finalSuccessMsg = "INIT PROCESS COMPLETE. Schema created and procedures/triggers attempted.";
        echo $isCli ? $finalSuccessMsg . "\n" : "<p style='color:blue;'>" . $finalSuccessMsg . "</p>";

        if (!$isCli) {
            header("Location: user_initializer.php");
            echo "<p>Attempting to redirect to user_initializer.php...</p>";
            echo "<p>Redirect would occur here to user_initializer.php (commented out for safety during testing and to see all messages).</p>";
        }
    }
}

$db_host = getenv('DB_HOST_ORA') ?: 'localhost';
$db_user = getenv('DB_USER_ORA') ?: 'duy_admin';
$db_pass = getenv('DB_PASS_ORA') ?: 'duyadmin';
$db_service = getenv('DB_SERVICE_ORA') ?: 'QUANLYKHOAHOC';
$db_port = (int)(getenv('DB_PORT_ORA') ?: 1521);
$db_charset = getenv('DB_CHARSET_ORA') ?: 'AL32UTF8';

if ($db_user === 'your_oracle_user' || $db_pass === 'your_oracle_password') {
    $warningMsg = "WARNING: Using placeholder database credentials. Please set environment variables (DB_USER_ORA, DB_PASS_ORA, etc.) or update defaults in Database.php / InitDatabase constructor.";
    if (php_sapi_name() === 'cli') {
        echo $warningMsg . "\n";
    } else {
        echo "<p style='color:orange;'>" . htmlspecialchars($warningMsg) . "</p>";
    }
}

$myinit = new InitDatabase($db_host, $db_user, $db_pass, $db_service, $db_port, $db_charset);
$myinit->create_structure_and_procedures();
