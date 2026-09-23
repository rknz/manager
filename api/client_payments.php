<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/client_payments.php — Extended: update action
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action     = $_GET['action'] ?? '';
$project_id = intval($_GET['project_id'] ?? $_POST['project_id'] ?? 0);

if (empty($project_id) && $action !== 'list') { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }

try {
    switch ($action) {
        case 'list':
            if ($project_id) {
                $stmt = $pdo->prepare("SELECT cp.*, p.name as project_name FROM app_client_payments cp JOIN app_projects p ON cp.project_id=p.id WHERE cp.project_id=? AND cp.is_deleted=0 ORDER BY cp.payment_date DESC, cp.id DESC");
                $stmt->execute([$project_id]);
            } else {
                $stmt = $pdo->query("SELECT cp.*, p.name as project_name FROM app_client_payments cp JOIN app_projects p ON cp.project_id=p.id WHERE cp.is_deleted=0 ORDER BY cp.payment_date DESC, cp.id DESC");
            }
            $rows  = $stmt->fetchAll();
            $total = array_sum(array_column($rows, 'amount'));
            echo json_encode(['success'=>true,'data'=>$rows,'total'=>$total]);
            break;

        case 'create':
            $amount  = floatval($_POST['amount'] ?? 0);
            $date    = $_POST['payment_date'] ?? date('Y-m-d');
            $method  = $_POST['payment_method'] ?? 'Cash';
            $notes   = trim($_POST['notes'] ?? '');
            if ($amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid amount.']); exit; }
            $stmt = $pdo->prepare("INSERT INTO app_client_payments (project_id,amount,payment_date,payment_method,notes,created_by) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$project_id,$amount,$date,$method,$notes,$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Client payment added.','id'=>$pdo->lastInsertId()]);
            break;

        case 'update':
            $id     = intval($_POST['id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $date   = $_POST['payment_date'] ?? date('Y-m-d');
            $method = $_POST['payment_method'] ?? 'Cash';
            $notes  = trim($_POST['notes'] ?? '');
            $new_pid = intval($_POST['new_project_id'] ?? $_POST['project_id'] ?? $project_id);
            if (!$id || $amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            if ($new_pid > 0) {
                $stmt = $pdo->prepare("UPDATE app_client_payments SET project_id=?,amount=?,payment_date=?,payment_method=?,notes=?,updated_at=NOW() WHERE id=? AND is_deleted=0");
                $stmt->execute([$new_pid,$amount,$date,$method,$notes,$id]);
            } else {
                $stmt = $pdo->prepare("UPDATE app_client_payments SET amount=?,payment_date=?,payment_method=?,notes=?,updated_at=NOW() WHERE id=? AND is_deleted=0");
                $stmt->execute([$amount,$date,$method,$notes,$id]);
            }
            echo json_encode(['success'=>true,'message'=>'Payment updated.']);
            break;

        case 'delete':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            if ($project_id) {
                $pdo->prepare("UPDATE app_client_payments SET is_deleted=1 WHERE id=? AND project_id=?")->execute([$id,$project_id]);
            } else {
                $pdo->prepare("UPDATE app_client_payments SET is_deleted=1 WHERE id=?")->execute([$id]);
            }
            echo json_encode(['success'=>true,'message'=>'Payment deleted.']);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}

