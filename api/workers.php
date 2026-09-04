<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/workers.php — Extended: address field
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $q = $_GET['q'] ?? null;
            $sql = "SELECT w.*, c.name AS contractor_name, c.trade AS contractor_trade FROM app_workers w LEFT JOIN app_contractors c ON c.id=w.contractor_id WHERE w.is_active=1";
            $params = [];
            if ($q) { $sql .= " AND (w.name LIKE ? OR w.trade LIKE ? OR w.phone LIKE ?)"; $params = ["%$q%","%$q%","%$q%"]; }
            $sql .= " ORDER BY w.name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
            break;

        case 'create':
            $name    = trim($_POST['name'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $trade   = trim($_POST['trade'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $rate    = floatval($_POST['default_daily_rate'] ?? 0);
            $notes   = trim($_POST['notes'] ?? '');
            $contractor_id = intval($_POST['contractor_id'] ?? 0);
            $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
            if (empty($name)) { echo json_encode(['success'=>false,'message'=>'Name is required.']); exit; }
            if ($contractor_id > 0) { $c = $pdo->prepare("SELECT id FROM app_contractors WHERE id=? AND is_active=1"); $c->execute([$contractor_id]); if (!$c->fetch()) $contractor_id = 0; }
            $stmt = $pdo->prepare("INSERT INTO app_workers (name, phone, trade, address, default_daily_rate, notes, contractor_id, is_active) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$name,$phone,$trade,$address,$rate,$notes,$contractor_id?:null,$is_active]);
            echo json_encode(['success'=>true,'message'=>'Worker added.','id'=>$pdo->lastInsertId()]);
            break;

        case 'update':
            $id      = intval($_POST['id'] ?? 0);
            $name    = trim($_POST['name'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $trade   = trim($_POST['trade'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $rate    = floatval($_POST['default_daily_rate'] ?? 0);
            $notes   = trim($_POST['notes'] ?? '');
            $contractor_id = intval($_POST['contractor_id'] ?? 0);
            $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
            if (!$id || empty($name)) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            if ($contractor_id > 0) { $c = $pdo->prepare("SELECT id FROM app_contractors WHERE id=? AND is_active=1"); $c->execute([$contractor_id]); if (!$c->fetch()) $contractor_id = 0; }
            $stmt = $pdo->prepare("UPDATE app_workers SET name=?,phone=?,trade=?,address=?,default_daily_rate=?,notes=?,contractor_id=?,is_active=? WHERE id=?");
            $stmt->execute([$name,$phone,$trade,$address,$rate,$notes,$contractor_id?:null,$is_active,$id]);
            echo json_encode(['success'=>true,'message'=>'Worker updated.']);
            break;

        case 'delete':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? $_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_workers SET is_active=0 WHERE id=?")->execute([$id]);
            echo json_encode(['success'=>true,'message'=>'Worker deactivated.']);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}

