<?php
/**
 * API Gateway
 * 
 * Routes incoming requests to appropriate model methods.
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/CERMap.php';
require_once 'app/Models/Score.php';
require_once 'app/Models/UserLog.php';
require_once 'app/Models/Setting.php';
require_once 'app/Services/AIService.php';

// Prevent errors from breaking JSON output
error_reporting(0);
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Auth check for certain actions
if (in_array($action, ['save_map', 'delete_map', 'get_map', 'get_users', 'save_user', 'delete_user', 'import_users', 'download_template', 'extract_cer'])) {
    if (!User::checkAuth('guru')) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}

if ($action == 'download_template') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="user_template.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['username', 'namalengkap', 'password', 'role']);
    fputcsv($output, ['guru_teladan', 'Guru Teladan, M.Pd', 'pass123', 'guru']);
    fputcsv($output, ['siswa_rajin', 'Siswa Rajin Sekali', 'siswa456', 'siswa']);
    fclose($output);
    exit;

} elseif ($action == 'import_users') {
    if (!isset($_FILES['csv_file'])) {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
        exit;
    }

    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    
    // Skip header
    fgetcsv($handle);
    
    $userModel = new User($db);
    $created = 0;
    $updated = 0;
    $errors = 0;

    while (($row = fgetcsv($handle)) !== FALSE) {
        if (count($row) < 4) continue;
        
        $username = trim($row[0]);
        $namalengkap = trim($row[1]);
        $password = trim($row[2]);
        $role = strtolower(trim($row[3]));

        if (empty($username)) {
            $errors++;
            continue;
        }

        if (!in_array($role, ['guru', 'siswa'])) {
            $errors++;
            continue;
        }

        $existingUser = $userModel->getByUsername($username);
        if ($existingUser) {
            $pass_to_update = !empty($password) ? $password : null;
            if ($userModel->update($existingUser['id'], $username, $namalengkap, $role, $pass_to_update, $existingUser['gemini_api_key'])) {
                $updated++;
            } else {
                $errors++;
            }
        } else {
            if ($userModel->create($username, $namalengkap, $password, $role)) {
                $created++;
            } else {
                $errors++;
            }
        }
    }
    
    fclose($handle);
    echo json_encode([
        'status' => 'success', 
        'message' => "Imported $created new users, updated $updated existing users. Errors: $errors"
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($action == 'save_map') {
    $cerMap = new CERMap($db);
    $allow_feedback = isset($data['allow_feedback']) ? intval($data['allow_feedback']) : 1;
    $reading_text = isset($data['reading_text']) ? $data['reading_text'] : null;
    $allow_reading = isset($data['allow_reading']) ? intval($data['allow_reading']) : 1;
    $id = $cerMap->save($data['title'], $data['triplets'], isset($data['map_id']) ? $data['map_id'] : null, $allow_feedback, $reading_text, $allow_reading);
    
    if ($id) {
        echo json_encode(['status' => 'ok', 'id' => $id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save map']);
    }

} elseif ($action == 'get_maps') {
    $cerMap = new CERMap($db);
    echo json_encode($cerMap->getAll());

} elseif ($action == 'get_map') {
    $cerMap = new CERMap($db);
    $map_id = isset($_GET['map_id']) ? intval($_GET['map_id']) : 0;
    $map = $map_id > 0 ? $cerMap->getById($map_id) : null;

    if ($map) {
        echo json_encode(['status' => 'success', 'data' => $map]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Map not found']);
    }

} elseif ($action == 'get_triplets') {
    $cerMap = new CERMap($db);
    $map_id = isset($_GET['map_id']) ? intval($_GET['map_id']) : 0;
    echo json_encode($cerMap->getTriplets($map_id));

} elseif ($action == 'save_score') {
    if (!User::checkAuth('siswa')) {
        echo json_encode(['status' => 'error', 'message' => 'Session expired']);
        exit;
    }

    $scoreModel = new Score($db);
    $user_id = $_SESSION['user_id'];
    $map_id = isset($data['map_id']) ? intval($data['map_id']) : 0;
    $score = isset($data['score']) ? floatval($data['score']) : 0;
    $session_id = isset($data['session_id']) ? intval($data['session_id']) : null;
    $map_data = isset($data['map_data']) ? $data['map_data'] : null;

    if ($map_id > 0) {
        if ($scoreModel->save($user_id, $map_id, $score, $session_id, $map_data)) {
            // Finalize log session
            if ($session_id) {
                $logModel = new UserLog($db);
                $logModel->submitSession($session_id);
            }
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save score']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Map ID']);
    }

} elseif ($action == 'start_session') {
    if (!User::checkAuth('siswa')) exit;
    $logModel = new UserLog($db);
    $user_id = $_SESSION['user_id'];
    $map_id = isset($data['map_id']) ? intval($data['map_id']) : 0;
    $id = $logModel->startSession($user_id, $map_id);
    echo json_encode(['status' => 'success', 'session_id' => $id]);

} elseif ($action == 'log_action') {
    if (!User::checkAuth('siswa')) exit;
    $logModel = new UserLog($db);
    $session_id = isset($data['session_id']) ? intval($data['session_id']) : 0;
    $type = isset($data['type']) ? $data['type'] : '';
    $info = isset($data['data']) ? json_encode($data['data']) : null;
    $logModel->logAction($session_id, $type, $info);
    echo json_encode(['status' => 'success']);

} elseif ($action == 'delete_map') {
    $cerMap = new CERMap($db);
    $map_id = isset($_GET['map_id']) ? intval($_GET['map_id']) : 0;
    
    if ($map_id > 0) {
        if ($cerMap->delete($map_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete map']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    }

} elseif ($action == 'get_users') {
    $userModel = new User($db);
    echo json_encode(['status' => 'success', 'data' => $userModel->getAll()]);

} elseif ($action == 'save_user') {
    $userModel = new User($db);
    $id = isset($data['id']) ? intval($data['id']) : null;
    $username = isset($data['username']) ? $data['username'] : '';
    $namalengkap = isset($data['namalengkap']) ? $data['namalengkap'] : '';
    $password = !empty($data['password']) ? $data['password'] : null;
    $role = isset($data['role']) ? $data['role'] : 'siswa';
    $gemini_api_key = isset($data['gemini_api_key']) ? $data['gemini_api_key'] : null;

    if ($id) {
        if ($userModel->update($id, $username, $namalengkap, $role, $password, $gemini_api_key)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user']);
        }
    } else {
        if (!$password) {
            echo json_encode(['status' => 'error', 'message' => 'Password is required for new user']);
            exit;
        }
        if ($userModel->create($username, $namalengkap, $password, $role, $gemini_api_key)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create user']);
        }
    }

} elseif ($action == 'extract_cer') {
    $userModel = new User($db);
    $apiKey = $userModel->getApiKey($_SESSION['user_id']);
    
    if (empty($apiKey)) {
        echo json_encode(['status' => 'error', 'message' => 'Gemini API Key belum diatur untuk akun Anda. Silakan atur API Key di menu User Management.']);
        exit;
    }

    if (empty($data['text'])) {
        echo json_encode(['status' => 'error', 'message' => 'Teks bacaan tidak boleh kosong.']);
        exit;
    }

    $aiService = new AIService($apiKey);
    $triplets = $aiService->extractCER($data['text']);

    if ($triplets) {
        echo json_encode(['status' => 'success', 'data' => $triplets]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengekstraksi komponen CER. Pastikan teks berisi informasi ilmiah yang cukup dan API Key valid.']);
    }

} elseif ($action == 'delete_user') {
    $userModel = new User($db);
    $id = isset($data['id']) ? intval($data['id']) : 0;
    if ($id > 0) {
        if ($userModel->delete($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
?>
