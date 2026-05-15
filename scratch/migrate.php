<?php
require_once __DIR__ . '/../app/Config/Database.php';

try {
    $db = (new Database())->getConnection();
    
    // Check if column exists first
    $stmt = $db->query("SHOW COLUMNS FROM `scores` LIKE 'map_data'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE `scores` ADD `map_data` LONGTEXT NULL");
        echo "Column 'map_data' successfully added to 'scores' table.\n";
    } else {
        echo "Column 'map_data' already exists in 'scores' table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
