<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $pid = intval($_GET['project_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM app_printouts WHERE project_id=? ORDER BY id DESC");
            $stmt->execute([$pid]);
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
            break;

        case 'save_base64':
            $project_id = intval($_POST['project_id'] ?? 0);
            $title      = trim($_POST['title'] ?? 'Generated Bill');
            $pdf_data   = $_POST['pdf_data'] ?? '';
            if (!$project_id || empty($pdf_data)) {
                echo json_encode(['success'=>false,'message'=>'Project ID and PDF data required.']); exit;
            }
            $parts = explode(',', $pdf_data);
            if (count($parts) < 2) {
                echo json_encode(['success'=>false,'message'=>'Invalid PDF data.']); exit;
            }
            $pdf_decoded = base64_decode($parts[1]);
            
            $dir = __DIR__ . '/../uploads/printouts/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = 'bill_' . $project_id . '_' . time() . '.pdf';
            file_put_contents($dir . $filename, $pdf_decoded);
            $web_path = 'uploads/printouts/' . $filename;
            $size = filesize($dir . $filename);
            
            $stmt = $pdo->prepare("INSERT INTO app_printouts (project_id, title, file_path, file_size, created_by) VALUES (?,?,?,?,?)");
            $stmt->execute([$project_id, $title, $web_path, $size, $_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Saved successfully.']);
            break;

        case 'delete':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? $_POST['id'] ?? 0);
            $project_id = intval($_GET['project_id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            
            // Fetch file path to delete file
            $stmt = $pdo->prepare("SELECT file_path FROM app_printouts WHERE id=? AND project_id=?");
            $stmt->execute([$id, $project_id]);
            $file = $stmt->fetchColumn();
            if ($file && file_exists(__DIR__ . '/../' . $file)) {
                @unlink(__DIR__ . '/../' . $file);
            }
            $stmt = $pdo->prepare("DELETE FROM app_printouts WHERE id=? AND project_id=?");
            $res = $stmt->execute([$id, $project_id]);
            
            if ($res && $stmt->rowCount() > 0) {
                echo json_encode(['success'=>true,'message'=>'Printout deleted.']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Failed to delete printout or not found.']);
            }
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}