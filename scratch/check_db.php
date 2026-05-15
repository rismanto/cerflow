<?php
require_once __DIR__ . '/../app/Config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query('SHOW COLUMNS FROM user_sessions');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
