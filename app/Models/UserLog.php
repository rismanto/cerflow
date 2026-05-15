<?php
/**
 * UserLog Model Class
 * 
 * Handles recording of student interactions and session management.
 */
class UserLog {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Start a new session
     */
    public function startSession($user_id, $map_id) {
        $query = "INSERT INTO user_sessions (user_id, map_id) VALUES (:user_id, :map_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':map_id', $map_id);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Log an action
     */
    public function logAction($session_id, $action_type, $action_data = null) {
        $query = "INSERT INTO user_logs (session_id, action_type, action_data) VALUES (:session_id, :action_type, :action_data)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':action_type', $action_type);
        $stmt->bindParam(':action_data', $action_data);
        return $stmt->execute();
    }

    /**
     * Finalize session (submit)
     */
    public function submitSession($session_id) {
        $query = "UPDATE user_sessions SET is_submitted = 1 WHERE id = :session_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_id', $session_id);
        return $stmt->execute();
    }

    /**
     * Get all completed sessions for evaluation
     */
    public function getCompletedSessions($filters = []) {
        $query = "SELECT s.id as session_id, u.username, u.namalengkap, m.title, s.start_time,
                         (SELECT COUNT(*) FROM user_logs WHERE session_id = s.id) as total_actions,
                         (SELECT COUNT(*) FROM user_logs WHERE session_id = s.id AND action_type = 'connect') as count_connect,
                         (SELECT COUNT(*) FROM user_logs WHERE session_id = s.id AND action_type = 'disconnect') as count_disconnect,
                         (SELECT COUNT(*) FROM user_logs WHERE session_id = s.id AND action_type = 'move') as count_move,
                         (SELECT COUNT(*) FROM user_logs WHERE session_id = s.id AND action_type = 'auto_arrange') as count_auto_arrange,
                         (SELECT COUNT(*) FROM user_logs WHERE session_id = s.id AND action_type = 'feedback') as count_feedback,
                         (SELECT MIN(created_at) FROM user_logs WHERE session_id = s.id) as first_action,
                         (SELECT MAX(created_at) FROM user_logs WHERE session_id = s.id) as last_action,
                         (SELECT score FROM scores WHERE session_id = s.id LIMIT 1) as final_score
                  FROM user_sessions s
                  JOIN users u ON s.user_id = u.id
                  JOIN cer_maps m ON s.map_id = m.id
                  WHERE s.is_submitted = 1";

        $having = [];
        $params = [];

        if (!empty($filters['materi'])) {
            $query .= " AND m.title LIKE :materi";
            $params[':materi'] = '%' . $filters['materi'] . '%';
        }

        if (!empty($filters['siswa'])) {
            $query .= " AND u.username LIKE :siswa";
            $params[':siswa'] = '%' . $filters['siswa'] . '%';
        }

        if (!empty($filters['start'])) {
            $having[] = "first_action >= :start";
            $params[':start'] = $filters['start'];
        }

        if (!empty($filters['end'])) {
            $having[] = "last_action <= :end";
            $params[':end'] = $filters['end'];
        }

        if (!empty($having)) {
            $query .= " HAVING " . implode(" AND ", $having);
        }

        $query .= " ORDER BY s.start_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get distinct maps that have completed sessions
     */
    public function getLoggedMaps() {
        $query = "SELECT DISTINCT m.id, m.title 
                  FROM user_sessions s
                  JOIN cer_maps m ON s.map_id = m.id
                  WHERE s.is_submitted = 1
                  ORDER BY m.title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
