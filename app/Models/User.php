<?php
/**
 * User Model Class
 * 
 * Handles user authentication and session management.
 */
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $namalengkap;
    public $password;
    public $role;

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Login user and start session
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login($username, $password) {
        $query = "SELECT id, username, namalengkap, password, role FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->namalengkap = $row['namalengkap'];
                $this->role = $row['role'];

                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $this->id;
                $_SESSION['username'] = $this->username;
                $_SESSION['namalengkap'] = $this->namalengkap;
                $_SESSION['role'] = $this->role;

                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is logged in and has the required role
     * 
     * @param string|null $required_role
     * @return bool
     */
    public static function checkAuth($required_role = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        if ($required_role && $_SESSION['role'] !== $required_role) {
            return false;
        }

        return true;
    }

    /**
     * Logout user and destroy session
     */
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
    }


    /**
     * Get all users
     * 
     * @return array
     */
    public function getAll() {
        $query = "SELECT id, username, namalengkap, role FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user by ID
     * 
     * @param int $id
     * @return array|bool
     */
    public function getById($id) {
        $query = "SELECT id, username, namalengkap, role FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new user
     * 
     * @param string $username
     * @param string $password
     * @param string $role
     * @return bool
     */
    public function create($username, $namalengkap, $password, $role) {
        $query = "INSERT INTO " . $this->table_name . " (username, namalengkap, password, role) VALUES (:username, :namalengkap, :password, :role)";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $namalengkap = htmlspecialchars(strip_tags($namalengkap));
        $password = password_hash($password, PASSWORD_DEFAULT);
        $role = htmlspecialchars(strip_tags($role));

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':namalengkap', $namalengkap);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);

        return $stmt->execute();
    }

    /**
     * Update an existing user
     * 
     * @param int $id
     * @param string $username
     * @param string|null $password
     * @param string $role
     * @return bool
     */
    public function update($id, $username, $namalengkap, $role, $password = null) {
        if ($password) {
            $query = "UPDATE " . $this->table_name . " SET username = :username, namalengkap = :namalengkap, password = :password, role = :role WHERE id = :id";
        } else {
            $query = "UPDATE " . $this->table_name . " SET username = :username, namalengkap = :namalengkap, role = :role WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($query);

        $id = intval($id);
        $username = htmlspecialchars(strip_tags($username));
        $namalengkap = htmlspecialchars(strip_tags($namalengkap));
        $role = htmlspecialchars(strip_tags($role));

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':namalengkap', $namalengkap);
        $stmt->bindParam(':role', $role);

        if ($password) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $password);
        }

        return $stmt->execute();
    }

    /**
     * Delete a user
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
