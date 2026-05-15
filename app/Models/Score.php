<?php
/**
 * Score Model Class
 * 
 * Handles management of student scores and reports.
 */
class Score {
    private $conn;
    private $table_name = "scores";

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Save student score
     * 
     * @param int $user_id
     * @param int $map_id
     * @param float $score
     * @param int|null $session_id
     * @param string|null $map_data JSON string
     * @return bool
     */
    public function save($user_id, $map_id, $score, $session_id = null, $map_data = null) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, map_id, score, session_id, map_data, submitted_at) VALUES (:user_id, :map_id, :score, :session_id, :map_data, NOW())";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':map_id', $map_id);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':map_data', $map_data);

        return $stmt->execute();
    }

    /**
     * Get all scores for reporting (admin view)
     * 
     * @return array
     */
    public function getAllReports() {
        $query = "SELECT s.id as score_id, s.map_id, u.username, u.namalengkap, m.title, s.score, s.submitted_at, s.session_id, s.map_data
                  FROM " . $this->table_name . " s 
                  JOIN users u ON s.user_id = u.id 
                  JOIN cer_maps m ON s.map_id = m.id 
                  ORDER BY s.submitted_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get score by ID with details
     * 
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $query = "SELECT s.*, u.username, u.namalengkap, m.title as map_title 
                  FROM " . $this->table_name . " s 
                  JOIN users u ON s.user_id = u.id 
                  JOIN cer_maps m ON s.map_id = m.id 
                  WHERE s.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    /**
     * Get all scores for a specific user
     * 
     * @param int $user_id
     * @return array
     */
    public function getByUserId($user_id) {
        $query = "SELECT s.id as score_id, s.session_id, s.score, s.submitted_at, s.map_data, m.title as map_title 
                  FROM " . $this->table_name . " s 
                  JOIN cer_maps m ON s.map_id = m.id 
                  WHERE s.user_id = :user_id 
                  ORDER BY s.submitted_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
