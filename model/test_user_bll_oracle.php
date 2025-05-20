<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the UserBLL class
require_once 'bll/user_bll.php';

echo "<h1>Testing UserBLL with Oracle Database</h1>";

// Create an instance of UserBLL
$userBLL = new UserBLL();

// Test connection
if ($userBLL->isConnected()) {
    echo "<p style='color:green;'>Successfully connected to Oracle database!</p>";
} else {
    echo "<p style='color:red;'>Failed to connect to Oracle database.</p>";
    echo "<p>Error: " . htmlspecialchars($userBLL->getLastError()) . "</p>";
    exit;
}

// Test get_all_users
echo "<h2>Testing get_all_users()</h2>";
try {
    $users = $userBLL->get_all_users();
    echo "<p>Found " . count($users) . " users</p>";
    
    if (count($users) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>UserID</th><th>FirstName</th><th>LastName</th><th>Email</th><th>RoleID</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user->userID) . "</td>";
            echo "<td>" . htmlspecialchars($user->firstName) . "</td>";
            echo "<td>" . htmlspecialchars($user->lastName) . "</td>";
            echo "<td>" . htmlspecialchars($user->email) . "</td>";
            echo "<td>" . htmlspecialchars($user->roleID) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Test get_user_by_id with the first user
        $firstUser = $users[0];
        echo "<h2>Testing get_user_by_id('{$firstUser->userID}')</h2>";
        
        $user = $userBLL->get_user_by_id($firstUser->userID);
        if ($user) {
            echo "<p style='color:green;'>Successfully retrieved user by ID!</p>";
            echo "<p>User: " . htmlspecialchars($user->firstName) . " " . htmlspecialchars($user->lastName) . "</p>";
        } else {
            echo "<p style='color:red;'>Failed to retrieve user by ID.</p>";
            echo "<p>Error: " . htmlspecialchars($userBLL->getLastError()) . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p>Last SQL Query: " . htmlspecialchars($userBLL->getLastQuery()) . "</p>";
?>