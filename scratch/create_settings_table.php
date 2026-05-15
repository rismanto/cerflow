<?php
require_once 'app/Config/Database.php';

$database = new Database();
$db = $database->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

try {
    $db->exec($sql);
    // Initialize default values
    $stmt = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('allow_feedback_during_work', '1')");
    $stmt->execute();
    echo "Table 'settings' created and initialized.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
