<?php
/**
 * Database Configuration Class
 * 
 * Handles the connection to the MySQL database using PDO.
 */
class Database {
    private $host = "localhost";
    private $db_name = "cer_flow_db";
    private $username = "root";
    private $password = "";
    public $conn;

    /**
     * Get the database connection
     * 
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
