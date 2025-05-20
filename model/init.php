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

    public function create_structure(): void
    {
        $sqlFilePath = __DIR__ . '/schema.sql';

        if (!file_exists($sqlFilePath)) {
            $errorMsg = "INIT FAILED: schema.sql not found at {$sqlFilePath}";
            echo $errorMsg;
            error_log($errorMsg);
            return;
        }

        $sql = file_get_contents($sqlFilePath);
        if ($sql === false) {
            $errorMsg = "INIT FAILED: Could not read schema.sql";
            echo $errorMsg;
            error_log($errorMsg);
            return;
        }

        if (!$this->isConnected()) {
            $errorMsg = "INIT FAILED: Not connected to the database. Last Error: " . htmlspecialchars($this->getLastError() ?? 'Unknown connection error. Check OCI8 setup and credentials.');
            echo $errorMsg;
            error_log($errorMsg . " (Raw: " . $this->getLastError() . ")");
            return;
        }

        $isCli = php_sapi_name() === 'cli';
        echo $isCli ? "Attempting to initialize database structure...\n" : "<p>Attempting to initialize database structure...</p>";

        if ($this->runScript($sql)) {
            $successMsg = "INIT COMPLETE";
            echo $isCli ? $successMsg . "\n" : "<p>" . $successMsg . "</p>";
            if (!$isCli) {
                header("Location: user_initializer.php");
                echo "<p>Redirect would occur here to user_initializer.php (commented out for safety during testing).</p>";

            }
        } else {
            $errorDetail = htmlspecialchars($this->getLastError() ?? 'Unknown error during script execution.');
            $lastQueryAttempted = htmlspecialchars(substr($this->getLastQuery() ?? '', 0, 1000));
            $failMsg = "INIT FAILED. Last Error: " . $errorDetail;
            $queryInfo = "\nLast Query/Script Segment Attempted:\n" . $lastQueryAttempted;

            if ($isCli) {
                echo $failMsg . $queryInfo . "\n";
            } else {
                echo "<p style='color:red;'>" . $failMsg . "</p><pre>" . $queryInfo . "</pre>";
            }
            error_log($failMsg . " (Raw: " . $this->getLastError() . ")" . $queryInfo);
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
$myinit->create_structure();

?>