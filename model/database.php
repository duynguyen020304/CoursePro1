<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');

class Database
{
    private string $host = 'localhost';
    private string $user = 'root';
    private string $pass = '30112004';
    private string $dbname = 'ecourse';
    private string $charset = 'utf8mb4';
    private int $databasePort = 3306;

    protected ?mysqli $conn = null;
    private ?string $lastError = null;
    private ?string $lastQuery = null;
    private int $affectedRows = 0;
    protected bool $inTransaction = false;

    public function __construct(
        string $host = '',
        string $user = '',
        string $pass = '',
        string $dbname = '',
        int $port = 0,
        string $charset = ''
    ) {
        $this->host = !empty($host) ? $host : $this->host;
        $this->user = !empty($user) ? $user : $this->user;
        $this->pass = !empty($pass) ? $pass : $this->pass;
        $this->dbname = !empty($dbname) ? $dbname : $this->dbname;
        $this->databasePort = $port ?: $this->databasePort;
        $this->charset = !empty($charset) ? $charset : $this->charset;

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->databasePort);
            $this->conn->set_charset($this->charset);
            $this->setTimezone();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1049) {
                $this->createDatabase();
            } else {
                $this->handleException($e, 'Kết nối database thất bại');
            }
        }
    }

    private function createDatabase(): void
    {
        try {
            $tmp_conn = new mysqli($this->host, $this->user, $this->pass, '', $this->databasePort);
            $create_sql = "CREATE DATABASE IF NOT EXISTS `{$this->dbname}` CHARACTER SET {$this->charset} COLLATE {$this->charset}_unicode_ci";
            $tmp_conn->query($create_sql);
            $tmp_conn->close();

            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->databasePort);
            $this->conn->set_charset($this->charset);
            $this->setTimezone();
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Tạo database thất bại');
        }
    }

    private function setTimezone(): void
    {
        if ($this->isConnected()) {
            try {
                $this->execute("SET time_zone = 'Asia/Ho_Chi_Minh'");
                error_log("[DB-MySQL] Session timezone set to 'Asia/Ho_Chi_Minh'");
            } catch (mysqli_sql_exception $e) {
                $this->handleException($e, 'Không thể đặt múi giờ cho session');
            }
        }
    }

    public function isConnected(): bool
    {
        return $this->conn !== null;
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

    public function execute(string $sql): bool|mysqli_result|null
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Không có kết nối tới database';
            return null;
        }

        $this->lastQuery = $sql;
        $this->affectedRows = 0;
        $this->lastError = null;

        try {
            $result = $this->conn->query($sql);

            if ($result instanceof mysqli_result) {
                return $result;
            }
            $this->affectedRows = $this->conn->affected_rows;
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Query failed', $sql);
            return false;
        }
    }

    public function executePrepared(string $sql, array $bindParams = []): bool|mysqli_result|null
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Không có kết nối tới database';
            return null;
        }

        $this->lastQuery = $sql;
        $this->affectedRows = 0;
        $this->lastError = null;

        try {
            $stmt = $this->conn->prepare($sql);

            if (!empty($bindParams)) {
                $types = str_repeat('s', count($bindParams));
                $stmt->bind_param($types, ...$bindParams);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result instanceof mysqli_result) {
                return $result;
            }
            $this->affectedRows = $stmt->affected_rows;
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Prepared statement failed', $sql);
            return false;
        }
    }

    public function fetchAll(string $sql, int $mode = MYSQLI_ASSOC): array
    {
        $result = $this->execute($sql);
        if ($result instanceof mysqli_result) {
            $data = $result->fetch_all($mode);
            $result->free();
            return $data;
        }
        if ($result === false) {
            error_log("[DB-MySQL] fetchAll thất bại vì query lỗi. SQL: $sql");
        } else {
            error_log("[DB-MySQL] fetchAll được gọi trên một query không phải SELECT. SQL: $sql");
        }
        return [];
    }

    public function fetchRow(string $sql, int $mode = MYSQLI_ASSOC): ?array
    {
        $result = $this->execute($sql);
        if ($result instanceof mysqli_result) {
            $row = $result->fetch_array($mode);
            $result->free();
            return $row;
        }
        if ($result === false) {
            error_log("[DB-MySQL] fetchRow thất bại vì query lỗi. SQL: $sql");
        } else {
            error_log("[DB-MySQL] fetchRow được gọi trên một query không phải SELECT. SQL: $sql");
        }
        return null;
    }

    public function runScript(string $sqlScript): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = 'Không có kết nối tới database';
            return false;
        }

        // Clean up comments and whitespace
        $sqlScript = preg_replace('/--[^\n]*\n?/s', '', $sqlScript);
        $sqlScript = preg_replace('/\/\*.*?\*\//s', '', $sqlScript);
        $sqlScript = trim($sqlScript);

        if (empty($sqlScript)) {
            return true;
        }

        $delimiter = ';';
        $statements = preg_split('/^DELIMITER\s+/im', $sqlScript);

        foreach ($statements as $i => $block) {
            if (empty(trim($block))) {
                continue;
            }

            if ($i > 0) {
                // New delimiter is at the start of the block
                $parts = preg_split('/\n/m', $block, 2);
                $delimiter = trim($parts[0]);
                $block = $parts[1] ?? '';
            }

            // Split the block into individual statements using the current delimiter
            $queries = explode($delimiter, $block);

            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) {
                    continue;
                }

                if ($this->execute($query) === false) {
                    // Error is already set and logged by execute()
                    $this->lastError = "Script statement failed: " . $this->getLastError();
                    error_log("[DB-MySQL] " . $this->lastError . " Statement: " . substr($query, 0, 250));
                    return false; // Halt on first error
                }
            }
        }

        return true;
    }

    public function begin(): bool
    {
        if (!$this->isConnected() || $this->inTransaction) return false;
        $this->inTransaction = $this->conn->begin_transaction();
        return $this->inTransaction;
    }

    public function commit(): bool
    {
        if (!$this->isConnected() || !$this->inTransaction) return false;
        $result = $this->conn->commit();
        $this->inTransaction = false;
        return $result;
    }

    public function rollback(): bool
    {
        if (!$this->isConnected() || !$this->inTransaction) return false;
        $result = $this->conn->rollback();
        $this->inTransaction = false;
        return $result;
    }

    public function close(): void
    {
        if ($this->conn) {
            if ($this->inTransaction) {
                $this->rollback();
            }
            $this->conn->close();
            $this->conn = null;
        }
    }

    private function handleException(Exception $e, string $context = '', ?string $sql = null): void
    {
        $this->lastError = $e->getMessage();
        $query_info = $sql ?? $this->lastQuery;
        $msg = "[DB-MySQL] {$context}: {$this->lastError}";
        if ($query_info) {
            $msg .= " | Query: " . substr($query_info, 0, 200);
        }
        error_log($msg);
    }

    public function __destruct()
    {
        $this->close();
    }
}
