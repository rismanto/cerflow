<?php
/**
 * Logout Page
 */
require_once 'app/Models/User.php';

User::logout();
header("Location: index.php");
exit;
?>