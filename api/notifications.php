<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/notifications.php — List, count, mark read
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $limit = intval($_GET['limit'] ?? 20);
            $stmt = $pdo->prepare("SELECT n.*, p.name as project_name FROM app_notifications n LEFT JOIN app_projects p ON p.id=n.project_id WHERE (n.user_id=? OR n.user_id IS NULL) ORDER BY n.created_at DESC LIMIT ?");
            $stmt->execute([$_SESSION['user_id'],$limit]);
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
            break;

        case 'count_unread':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM app_notifications WHERE (user_id=? OR user_id IS NULL) AND is_read=0");
            $stmt->execute([$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'count'=>(int)$stmt->fetchColumn()]);
            break;

        case 'mark_read':
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_notifications SET is_read=1 WHERE id=?")->execute([$id]);
            echo json_encode(['success'=>true]);
            break;

        case 'mark_all_read':
            $pdo->prepare("UPDATE app_notifications SET is_read=1 WHERE user_id=? OR user_id IS NULL")->execute([$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'All notifications marked as read.']);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}

