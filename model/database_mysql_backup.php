<?php

class DatabaseBackupMysql
{
    private string $host   = 'localhost';
    private string $user   = 'root';
    private string $pass   = '';
    private string $dbname = 'ecourse';
    private string $charset = 'utf8mb4';

    protected ?mysqli $conn = null;
    private ?string $lastError = null;
    private ?string $lastQuery = null;
    private int $affectedRows = 0;
    private int $databasePort = 3306;

    public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli(
                $this->host,
                $this->user,
                $this->pass,
                $this->dbname,
                $this->databasePort
            );
            $this->conn->set_charset($this->charset);
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1049) {
                try {
                    $tmp = new mysqli(
                        $this->host,
                        $this->user,
                        $this->pass,
                        '',
                        $this->databasePort
                    );
                    $tmp->query(
                        "CREATE DATABASE IF NOT EXISTS `{$this->dbname}` 
                    CHARACTER SET {$this->charset} 
                    COLLATE {$this->charset}_unicode_ci"
                    );
                    $tmp->close();

                    $this->conn = new mysqli(
                        $this->host,
                        $this->user,
                        $this->pass,
                        $this->dbname,
                        $this->databasePort
                    );
                    $this->conn->set_charset($this->charset);
                } catch (mysqli_sql_exception $e2) {
                    $this->handleException($e2, 'Tạo database thất bại');
                }
            } else {
                $this->handleException($e, 'Kết nối database thất bại');
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
            $this->lastError = 'Not connected to database';
            return null;
        }

        $this->lastQuery = $sql;
        $this->affectedRows = 0;

        try {
            $result = $this->conn->query($sql);

            if ($result instanceof mysqli_result) {
                return $result;
            }

            $this->affectedRows = $this->conn->affected_rows;
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Query failed');
            return false;
        }
    }

    public function fetchAll(string $sql, int $mode = MYSQLI_ASSOC): array
    {
        $result = $this->execute($sql);
        return $result ? $result->fetch_all($mode) : [];
    }

    public function fetchRow(string $sql, int $mode = MYSQLI_ASSOC): ?array
    {
        $result = $this->execute($sql);
        return $result ? $result->fetch_array($mode) : null;
    }

    public function runScript(string $sql): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        $this->lastQuery = $sql;

        try {
            $this->conn->multi_query($sql);
            do {
                if ($res = $this->conn->store_result()) {
                    $res->free();
                }
            } while ($this->conn->more_results() && $this->conn->next_result());
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Multi-query failed');
            return false;
        }
    }

    public function begin(): bool
    {
        return $this->isConnected() && $this->conn->begin_transaction();
    }

    public function commit(): bool
    {
        return $this->isConnected() && $this->conn->commit();
    }

    public function rollback(): bool
    {
        return $this->isConnected() && $this->conn->rollback();
    }

    public function close(): void
    {
        if ($this->conn) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    private function handleException(mysqli_sql_exception $e, string $context = ''): void
    {
        $this->lastError = $e->getMessage();
        $msg = "[DB] {$context}: {$this->lastError}";
        error_log($msg);
    }

    public function __destruct()
    {
        $this->close();
    }
}
