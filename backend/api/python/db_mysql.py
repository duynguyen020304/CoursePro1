"""
MySQL Database Connection Module for AI Recommendation Service
Replaces Oracle connection with MySQL connector
"""

import os
import mysql.connector
from mysql.connector import pooling
from typing import Optional

# MySQL connection configuration from environment
DB_HOST = os.environ.get("DB_HOST", "localhost")
DB_PORT = int(os.environ.get("DB_PORT", 3306))
DB_NAME = os.environ.get("DB_NAME", "coursepro")
DB_USER = os.environ.get("DB_USER", "root")
DB_PASSWORD = os.environ.get("DB_PASSWORD", "")

# Connection pool
_connection_pool: Optional[pooling.MySQLConnectionPool] = None


def init_connection_pool(pool_size: int = 5) -> pooling.MySQLConnectionPool:
    """Initialize MySQL connection pool."""
    global _connection_pool

    if _connection_pool is not None:
        return _connection_pool

    try:
        _connection_pool = pooling.MySQLConnectionPool(
            pool_name="ai_service_pool",
            pool_size=pool_size,
            host=DB_HOST,
            port=DB_PORT,
            database=DB_NAME,
            user=DB_USER,
            password=DB_PASSWORD,
            charset='utf8mb4',
            collation='utf8mb4_unicode_ci',
            autocommit=True
        )
        print(f"MySQL connection pool initialized: {DB_HOST}:{DB_PORT}/{DB_NAME}")
        return _connection_pool
    except mysql.connector.Error as e:
        print(f"Failed to create MySQL connection pool: {e}")
        raise


def get_connection():
    """Get a connection from the pool."""
    global _connection_pool

    if _connection_pool is None:
        init_connection_pool()

    try:
        return _connection_pool.get_connection()
    except mysql.connector.Error as e:
        print(f"Failed to get connection from pool: {e}")
        raise


def test_connection() -> bool:
    """Test MySQL connection."""
    try:
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT 1")
        cursor.close()
        conn.close()
        print("MySQL connection test successful!")
        return True
    except Exception as e:
        print(f"MySQL connection test failed: {e}")
        return False


def close_pool():
    """Close all connections in the pool."""
    global _connection_pool
    if _connection_pool:
        _connection_pool.closeall()
        _connection_pool = None
        print("MySQL connection pool closed.")