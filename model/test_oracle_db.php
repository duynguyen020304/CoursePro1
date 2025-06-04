<?php

require_once 'database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h1>Oracle Database Class Test</h1>";
$dbHost = 'localhost';
$dbPort = 1521;
$dbServiceName = 'QUANLYKHOAHOC';
$dbUser = 'duy_admin';
$dbPass = 'duyadmin';
$dbCharset = 'AL32UTF8';

function test_section(string $title)
{
    echo "<h2>{$title}</h2>";
}
echo "hello";
function print_result($result, ?Database $dbInstance = null)
{
    echo "<pre>";
    if ($result === false) {
        echo "Result: bool(false)\n";
    } elseif ($result === true) {
        echo "Result: bool(true)\n";
    } elseif (is_resource($result) && get_resource_type($result) === 'oci8 statement') {
        echo "Result: OCI8 Statement Resource (typically from SELECT)\n";
    } else {
        print_r($result);
    }
    echo "</pre>";
    if ($dbInstance) {
        if ($dbInstance->getLastError()) {
            echo "<p style='color:red;'><strong>DB Last Error:</strong> " . htmlspecialchars($dbInstance->getLastError()) . "</p>";
        }
        if ($dbInstance->getLastQuery()) {
            echo "<p><strong>DB Last Query:</strong> " . htmlspecialchars($dbInstance->getLastQuery()) . "</p>";
        }
        echo "<p><strong>DB Affected Rows:</strong> " . $dbInstance->getAffectedRows() . "</p>";
    }
    echo "<hr>";
}

test_section("1. Database Instantiation & Connection Test");
$db = new Database($dbHost, $dbUser, $dbPass, $dbServiceName, $dbPort, $dbCharset);

if ($db->isConnected()) {
    echo "<p style='color:green;'>Successfully connected to Oracle database!</p>";
} else {
    echo "<p style='color:red;'>Failed to connect to Oracle database.</p>";
    print_result(null, $db);
    echo "<p><strong>Stopping tests due to connection failure.</strong></p>";
    exit;
}
print_result(null, $db);