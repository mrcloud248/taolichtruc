<?php
/**
 * Create Admin Account
 * Run this file once to create/reset admin account
 * DELETE THIS FILE after use for security!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

echo "<h2>Create/Reset Admin Account</h2>";

// Admin credentials
$username = 'admin';
$password = 'Admin@123';
$fullName = 'Administrator';
$role = 'admin';

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Account Details:</h3>";
echo "<ul>";
echo "<li><strong>Username:</strong> $username</li>";
echo "<li><strong>Password:</strong> $password</li>";
echo "<li><strong>Full Name:</strong> $fullName</li>";
echo "<li><strong>Role:</strong> $role</li>";
echo "<li><strong>Hashed Password:</strong> " . substr($hashedPassword, 0, 50) . "...</li>";
echo "</ul>";

try {
    $conn = getDBConnection();
    echo "<p>✓ Database connected</p>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        echo "<p style='color: red;'>✗ Users table does not exist!</p>";
        echo "<p>Please run the SQL from database/add_users_table.sql first</p>";
        exit;
    }
    
    echo "<p>✓ Users table exists</p>";
    
    // Check if admin exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing admin
        echo "<p>Admin user already exists. Updating password...</p>";
        
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, full_name = ?, role = ?, is_active = 1, updated_at = NOW() WHERE username = ?");
        $updateStmt->bind_param("ssss", $hashedPassword, $fullName, $role, $username);
        
        if ($updateStmt->execute()) {
            echo "<p style='color: green; font-weight: bold;'>✓ Admin account updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Error updating admin: " . $updateStmt->error . "</p>";
        }
        
        $updateStmt->close();
    } else {
        // Insert new admin
        echo "<p>Creating new admin user...</p>";
        
        $insertStmt = $conn->prepare("INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $insertStmt->bind_param("ssss", $username, $hashedPassword, $fullName, $role);
        
        if ($insertStmt->execute()) {
            echo "<p style='color: green; font-weight: bold;'>✓ Admin account created successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating admin: " . $insertStmt->error . "</p>";
        }
        
        $insertStmt->close();
    }
    
    $stmt->close();
    
    // Verify the account
    echo "<h3>Verification:</h3>";
    $verifyStmt = $conn->prepare("SELECT id, username, full_name, role, is_active, created_at FROM users WHERE username = ?");
    $verifyStmt->bind_param("s", $username);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($user = $verifyResult->fetch_assoc()) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>ID</td><td>" . $user['id'] . "</td></tr>";
        echo "<tr><td>Username</td><td>" . $user['username'] . "</td></tr>";
        echo "<tr><td>Full Name</td><td>" . $user['full_name'] . "</td></tr>";
        echo "<tr><td>Role</td><td>" . $user['role'] . "</td></tr>";
        echo "<tr><td>Active</td><td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td></tr>";
        echo "<tr><td>Created</td><td>" . $user['created_at'] . "</td></tr>";
        echo "</table>";
        
        echo "<p style='color: green; font-weight: bold;'>✓ Account verified successfully!</p>";
    }
    
    $verifyStmt->close();
    $conn->close();
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Test Login:</strong> <a href='login.php' target='_blank'>Go to Login Page</a></li>";
    echo "<li><strong>Login with:</strong>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>Admin@123</code></li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong style='color: red;'>IMPORTANT: Delete this file (create_admin.php) after successful login for security!</strong></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        color: #333;
        border-bottom: 2px solid #667eea;
        padding-bottom: 10px;
    }
    h3 {
        color: #555;
        margin-top: 20px;
    }
    code {
        background: #e0e0e0;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
    table {
        margin: 20px 0;
        background: white;
    }
    th {
        background: #667eea;
        color: white;
        text-align: left;
    }
</style>
