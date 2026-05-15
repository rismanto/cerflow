<?php
/**
 * Utility Script - Reset Users
 * 
 * Resets the users table with default accounts.
 */
require_once 'app/Config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $db->exec("SET FOREIGN_KEY_CHECKS=0;");
    $db->exec("DROP TABLE IF EXISTS users");
    $db->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        namalengkap VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('guru', 'siswa') NOT NULL
    )");

    // Prepare password hashes
    $pass_guru = password_hash("admin123", PASSWORD_DEFAULT);
    $pass_siswa = password_hash("siswa123", PASSWORD_DEFAULT);

    // Insert default accounts
    $stmt = $db->prepare("INSERT INTO users (username, namalengkap, password, role) VALUES (:username, :namalengkap, :password, :role)");

    // Guru
    $stmt->execute([':username' => 'admin', ':namalengkap' => 'Administrator', ':password' => $pass_guru, ':role' => 'guru']);
    
    // Siswa
    $stmt->execute([':username' => 'siswa', ':namalengkap' => 'Siswa Percobaan', ':password' => $pass_siswa, ':role' => 'siswa']);

    echo "<h1>USER BERHASIL DIRESET!</h1>";
    echo "<p>Login Guru: <strong>admin</strong> | <strong>admin123</strong></p>";
    echo "<p>Login Siswa: <strong>siswa</strong> | <strong>siswa123</strong></p>";
    echo "<a href='index.php'>Klik di sini untuk login</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>