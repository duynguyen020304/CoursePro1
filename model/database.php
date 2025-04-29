<?php

/**
 * Lớp Database – quản lý kết nối và thao tác MySQLi
 * PHP ≥ 8.1
 */
class Database
{
    /* ---------- Cấu hình ---------- */
    private string $host   = 'localhost';
    private string $user   = 'root';
    private string $pass   = '';
    private string $dbname = 'ecourse';
    private string $charset = 'utf8mb4';

    /* ---------- Thuộc tính runtime ---------- */
    private ?mysqli $conn = null;
    private ?string $lastError = null;
    private ?string $lastQuery = null;

    /* ---------- Khởi tạo ---------- */
    public function __construct()
    {
        /* Buộc mysqli ném ngoại lệ khi lỗi */
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            $this->conn->set_charset($this->charset);
        } catch (mysqli_sql_exception $e) {
            $this->handleException($e, 'Kết nối database thất bại');
            // Giữ $this->conn = null để lớp ngoài có thể kiểm tra
        }
    }

    /* ---------- Kiểm tra kết nối ---------- */
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

    /* ---------- Thực thi truy vấn đơn ---------- */
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

    /* ---------- Lấy toàn bộ kết quả ---------- */
    public function fetchAll(string $sql, int $mode = MYSQLI_ASSOC): array
    {
        $result = $this->execute($sql);
        return $result ? $result->fetch_all($mode) : [];
    }

    /* ---------- Lấy một dòng ---------- */
    public function fetchRow(string $sql, int $mode = MYSQLI_ASSOC): ?array
    {
        $result = $this->execute($sql);
        return $result ? $result->fetch_array($mode) : null;
    }

    /* ---------- Thực thi script nhiều câu lệnh ---------- */
    public function runScript(string $sql): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        $this->lastQuery = $sql;

        try {
            $this->conn->multi_query($sql);
            do {
                /* Lấy (và bỏ qua) mọi result set để dọn bộ nhớ */
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

    /* ---------- Giao dịch ---------- */
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

    /* ---------- Đóng kết nối ---------- */
    public function close(): void
    {
        if ($this->conn) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    /* ---------- Xử lý ngoại lệ chung ---------- */
    private function handleException(mysqli_sql_exception $e, string $context = ''): void
    {
        $this->lastError = $e->getMessage();
        $msg = "[DB] {$context}: {$this->lastError}";

        /* Ghi log server */
        error_log($msg);

        /* Tuỳ chọn: In ra console (ẩn chi tiết) */
        echo "<script>console.error('{$context}');</script>";
    }

    /* ---------- Huỷ đối tượng ---------- */
    public function __destruct()
    {
        $this->close();
    }
}
?>
