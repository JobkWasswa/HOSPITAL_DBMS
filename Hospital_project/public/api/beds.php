<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/helpers/Auth.php';
require_once __DIR__ . '/../../src/models/Admission.php';

header('Content-Type: application/json');

try {
    $auth = new Auth($pdo);
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([]);
        exit;
    }

    $roomId = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
    if ($roomId <= 0) {
        echo json_encode([]);
        exit;
    }

    $adm = new Admission($pdo);
    $beds = $adm->getAvailableBedsByRoom($roomId);
    echo json_encode($beds);
} catch (Throwable $e) {
    error_log('beds.php error: ' . $e->getMessage());
    echo json_encode([]);
}
?>


