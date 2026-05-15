<?php
require_once 'app/Config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'namalengkap'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add column after username
        $db->exec("ALTER TABLE users ADD COLUMN namalengkap VARCHAR(100) NOT NULL AFTER username");
        
        // Fill namalengkap with username for existing users
        $db->exec("UPDATE users SET namalengkap = username WHERE namalengkap = '' OR namalengkap IS NULL");
        
        echo "<h1>Migration Successful!</h1>";
        echo "<p>Column 'namalengkap' added to 'users' table.</p>";
    } else {
        echo "<h1>Migration Skipped</h1>";
        echo "<p>Column 'namalengkap' already exists.</p>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
