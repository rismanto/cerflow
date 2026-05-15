<?php
/**
 * CERMap Model Class
 * 
 * Handles management of Claim-Evidence-Reasoning maps and triplets.
 */
class CERMap {
    private $conn;
    private $table_maps = "cer_maps";
    private $table_triplets = "triplets";

    public $id;
    public $title;

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all maps
     * 
     * @return array
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_maps . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single map by ID with its triplets
     * 
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_maps . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $map = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($map) {
            $map['triplets'] = $this->getTriplets($id);
        }

        return $map;
    }

    /**
     * Get triplets for a specific map
     * 
     * @param int $map_id
     * @return array
     */
    public function getTriplets($map_id) {
        $query = "SELECT * FROM " . $this->table_triplets . " WHERE map_id = :map_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':map_id', $map_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save or update a map and its triplets
     * 
     * @param string $title
     * @param array $triplets
     * @param int|null $map_id
     * @param int $allow_feedback
     * @return int|bool
     */
    public function save($title, $triplets, $map_id = null, $allow_feedback = 1) {
        try {
            $this->conn->beginTransaction();

            if ($map_id) {
                // Update existing map
                $query = "UPDATE " . $this->table_maps . " SET title = :title, allow_feedback = :allow_feedback WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':allow_feedback', $allow_feedback);
                $stmt->bindParam(':id', $map_id);
                $stmt->execute();

                $id = $map_id;
            } else {
                // Insert new map
                $query = "INSERT INTO " . $this->table_maps . " (title, allow_feedback) VALUES (:title, :allow_feedback)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':allow_feedback', $allow_feedback);
                $stmt->execute();
                $id = $this->conn->lastInsertId();
            }

            $existingIds = [];
            if ($map_id) {
                $query = "SELECT id FROM " . $this->table_triplets . " WHERE map_id = :map_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':map_id', $id);
                $stmt->execute();
                $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            $existingIdSet = array_map('intval', $existingIds);
            $keptIds = [];
            $updateQuery = "UPDATE " . $this->table_triplets . " SET claim = :claim, evidence = :evidence, reasoning = :reasoning WHERE id = :id AND map_id = :map_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $insertQuery = "INSERT INTO " . $this->table_triplets . " (map_id, claim, evidence, reasoning) VALUES (:map_id, :claim, :evidence, :reasoning)";
            $insertStmt = $this->conn->prepare($insertQuery);

            foreach ($triplets as $t) {
                $tripletId = isset($t['id']) && $t['id'] !== '' ? intval($t['id']) : null;
                $claim = isset($t['claim']) ? $t['claim'] : '';
                $evidence = isset($t['evidence']) ? $t['evidence'] : '';
                $reasoning = isset($t['reasoning']) ? $t['reasoning'] : '';

                if ($tripletId && in_array($tripletId, $existingIdSet, true)) {
                    $updateStmt->bindParam(':claim', $claim);
                    $updateStmt->bindParam(':evidence', $evidence);
                    $updateStmt->bindParam(':reasoning', $reasoning);
                    $updateStmt->bindParam(':id', $tripletId);
                    $updateStmt->bindParam(':map_id', $id);
                    $updateStmt->execute();
                    $keptIds[] = $tripletId;
                } else {
                    $insertStmt->bindParam(':map_id', $id);
                    $insertStmt->bindParam(':claim', $claim);
                    $insertStmt->bindParam(':evidence', $evidence);
                    $insertStmt->bindParam(':reasoning', $reasoning);
                    $insertStmt->execute();
                    $keptIds[] = intval($this->conn->lastInsertId());
                }
            }

            if ($map_id) {
                $idsToDelete = array_diff(array_map('intval', $existingIds), $keptIds);
                if (!empty($idsToDelete)) {
                    $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                    $deleteQuery = "DELETE FROM " . $this->table_triplets . " WHERE map_id = ? AND id IN ($placeholders)";
                    $deleteStmt = $this->conn->prepare($deleteQuery);
                    $deleteStmt->execute(array_merge([$id], array_values($idsToDelete)));
                }
            }

            $this->conn->commit();
            return $id;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Delete a map and its associated triplets and scores
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            // Triplets and Scores should be deleted via ON DELETE CASCADE in SQL, 
            // but we'll do it manually here for safety if the schema doesn't support it fully.
            
            $query = "DELETE FROM " . $this->table_triplets . " WHERE map_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $query = "DELETE FROM scores WHERE map_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $query = "DELETE FROM " . $this->table_maps . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
?>
