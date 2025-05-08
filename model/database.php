<?php

class Database
{
    private string $host   = 'localhost';
    private string $user   = 'root';
    private string $pass   = '';
    private string $dbname = 'ecourse';
    private string $charset = 'utf8mb4';

    private ?mysqli $conn = null;
    private ?string $lastError = null;
    private ?string $lastQuery = null;

    public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            $this->conn->set_charset($this->charset);
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Kết nối database thất bại');
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

    public function execute(string $sql): ?mysqli_result
    {
        if (!$this->isConnected()) {
            return null;
        }
        $this->lastQuery = $sql;

        try {
            return $this->conn->query($sql);
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Query failed');
            return null;
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
        echo "<script>console.error('{$context}');</script>";
    }

    public function __destruct()
    {
        $this->close();
    }
    // Trong database.php
    // Thay đổi execute hoặc thêm một phương thức mới như executePreparedStatement
    public function executePreparedStatement(string $sql, array $params, string $types): bool
    {
        echo "Executing prepared statement: $sql with params: " . json_encode($params) . "\n";
        if (!$this->isConnected()) {
            $this->lastError = "Not connected to database.";
            error_log("[DB] Error: " . $this->lastError . " SQL: " . $sql);
            return false;
        }
        $this->lastQuery = $sql;
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                $this->lastError = "Prepare failed: " . $this->conn->error;
                error_log("[DB] Error: " . $this->lastError . " SQL: " . $sql);
                return false;
            }

            // bind_param cần truyền biến theo tham chiếu, nên cần xử lý $params
            $bindableParams = [];
            foreach ($params as $key => $value) {
                $bindableParams[$key] = &$params[$key];
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$bindableParams);
            }

            $success = $stmt->execute();
            if (!$success) {
                $this->lastError = "Execute failed: " . $stmt->error;
                error_log("[DB] Error: " . $this->lastError . " SQL: " . $sql . " Params: " . json_encode($params));
                $stmt->close();
                return false;
            }

            // Quan trọng: Đối với INSERT, UPDATE, DELETE, kiểm tra affected_rows
            if ($stmt->affected_rows > 0) {
                echo $stmt->affected_rows . " rows affected.";
                $stmt->close();
                return true;
            } else {
                // 0 rows affected có thể là lỗi logic hoặc không (vd: update không thay đổi gì)
                // Đối với INSERT, 0 rows affected chắc chắn là vấn đề nếu không có lỗi nào khác.
                $this->lastError = "Query executed, but no rows affected (affected_rows: " . $stmt->affected_rows . "). SQL: " . $sql . " Params: " . json_encode($params) . " Error: " . $stmt->error;
                error_log("[DB] Warning: " . $this->lastError);
                $stmt->close();
                return $stmt->affected_rows === 0 && $stmt->errno === 0; // Có thể coi là thành công nếu không có lỗi SQL, nhưng không có hàng nào bị ảnh hưởng
            }
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Prepared statement query failed. SQL: ' . $sql . " Params: " . json_encode($params));
            return false;
        }
    }
}
