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
        string $dbname = '',
        int $port = 0,
        string $charset = ''
    ) {
        parent::__construct($host, $user, $pass, $dbname, $port, $charset);
    }

    private function executeSqlFile(string $filePath, bool $isCli): bool
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

        echo $isCli ? "Attempting to execute SQL file: " . basename($filePath) . "...\n" : "<p>Attempting to execute SQL file: " . htmlspecialchars(basename($filePath)) . "...</p>";
        $success = $this->runScript($sql);

        if ($success) {
            $successMsg = "Successfully executed " . basename($filePath);
            echo $isCli ? $successMsg . "\n" : "<p style='color:green;'>" . htmlspecialchars($successMsg) . "</p>";
            return true;
        } else {
            $errorDetail = htmlspecialchars($this->getLastError() ?? 'Unknown error during script execution.');
            $lastQueryAttempted = htmlspecialchars(substr($this->getLastQuery() ?? 'N/A', 0, 1000));
            $failMsg = "FAILED to execute " . basename($filePath) . ". Last Error: " . $errorDetail;
            $queryInfo = "\nLast Statement Attempted from " . basename($filePath) . ":\n" . $lastQueryAttempted;

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

        // Apply a dark-theme wrapper for web output (non-CLI)
        if (!$isCli) {
            // Minimal dark-theme CSS injected into the page (inside body)
            echo "<style>
                .dark-theme { background-color: #121212; color: #e0e0e0; padding: 1rem; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
                .dark-theme p { color: inherit; margin: 0.25rem 0; }
                .dark-theme .warn { color: #f6c04a; }
                .dark-theme .err { color: #e57373; }
                .dark-theme .ok { color: #8bd57a; }
            </style>";
            echo "<div class='dark-theme'>";
        }

        if (!$this->isConnected()) {
            $errorMsg = "INIT FAILED: Not connected to the database. Last Error: " . htmlspecialchars($this->getLastError() ?? 'Unknown connection error. Check credentials and database server status.');
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg . " (Raw: " . $this->getLastError() . ")");
            if (!$isCli) echo "</div>";
            return;
        }

        echo $isCli ? "Attempting to initialize database structure (schema.sql)...\n" : "<p>Attempting to initialize database structure (schema.sql)...</p>";
        $schemaFilePath = __DIR__ . '/schema.sql';

        if (!$this->executeSqlFile($schemaFilePath, $isCli)) {
            echo $isCli ? "Halting initialization due to error in schema.sql.\n" : "<p style='color:red;'>Halting initialization due to error in schema.sql.</p>";
            if (!$isCli) echo "</div>";
            return;
        }
        echo $isCli ? "Database structure (schema.sql) initialized successfully.\n" : "<p style='color:green;'>Database structure (schema.sql) initialized successfully.</p>";

        $proceduresDir = __DIR__ . '/trigger_procedure/';
        echo $isCli ? "\nAttempting to execute additional SQL scripts from {$proceduresDir}...\n" : "<hr/><p>Attempting to execute additional SQL scripts from " . htmlspecialchars($proceduresDir) . "...</p>";

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
                    if (!$this->executeSqlFile($sqlFile, $isCli)) {
                        $allProceduresSuccessful = false;
                    }
                }
                if ($allProceduresSuccessful) {
                    echo $isCli ? "All additional SQL scripts executed successfully.\n" : "<p style='color:green;'>All additional SQL scripts executed successfully.</p>";
                } else {
                    echo $isCli ? "Some additional SQL scripts failed to execute. Please check logs.\n" : "<p style='color:red;'>Some additional SQL scripts failed to execute. Please check logs.</p>";
                }
            }
        }

        $finalSuccessMsg = "INIT PROCESS COMPLETE. Schema created and additional scripts attempted.";
        echo $isCli ? $finalSuccessMsg . "\n" : "<p style='color:blue;'>" . $finalSuccessMsg . "</p>";

        if (!$isCli) {
            echo "<p>Initialization complete. Redirect to user_initializer.php is disabled for review.</p>";
            echo "</div>"; // close the dark-theme wrapper
        }
    }
}

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '30112004';
$db_name = getenv('DB_NAME') ?: 'ecourse';
$db_port = (int)(getenv('DB_PORT') ?: 3306);
$db_charset = getenv('DB_CHARSET') ?: 'utf8mb4';

if ($db_user === 'root' && $db_pass === '') {
    $warningMsg = "WARNING: Using default MySQL credentials (root with no password). Please set environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME, etc.) for a production environment.";
    if (php_sapi_name() === 'cli') {
        echo $warningMsg . "\n";
    } else {
        echo "<p style='color:orange;'>" . htmlspecialchars($warningMsg) . "</p>";
    }
}

$myinit = new InitDatabase($db_host, $db_user, $db_pass, $db_name, $db_port, $db_charset);
$myinit->create_structure_and_procedures();