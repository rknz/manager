<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $q = $_GET['q'] ?? null;
            $sql = "SELECT * FROM app_contractors WHERE is_active=1";
            $params = [];
            if ($q) { $sql .= " AND (name LIKE ? OR phone LIKE ? OR trade LIKE ?)"; $params = ["%$q%","%$q%","%$q%"]; }
            $sql .= " ORDER BY name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
            break;

        case 'check_duplicate':
            $phone = trim($_GET['phone'] ?? '');
            $name  = trim($_GET['name']  ?? '');
            if (empty($phone) && empty($name)) { echo json_encode(['success'=>true,'exists'=>false]); exit; }
            $stmt = $pdo->prepare("SELECT id,name,phone,trade FROM app_contractors WHERE (phone=? AND phone!='') OR (name=? AND name!='') LIMIT 1");
            $stmt->execute([$phone, $name]);
            $existing = $stmt->fetch();
            if ($existing) {
                echo json_encode(['success'=>true,'exists'=>true,'contractor'=>$existing]);
            } else {
                echo json_encode(['success'=>true,'exists'=>false]);
            }
            break;

        case 'create':
            $name     = trim($_POST['name'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $trade    = trim($_POST['trade'] ?? '');
            $address  = trim($_POST['address'] ?? '');
            $notes    = trim($_POST['notes'] ?? '');
            $is_active= isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
            if (empty($name) || empty($trade)) {
                echo json_encode(['success'=>false,'message'=>'Name and Trade are required.']); exit;
            }
            
            // Duplicate check
            if (!empty($phone)) {
                $stmt = $pdo->prepare("SELECT id FROM app_contractors WHERE phone=? LIMIT 1");
                $stmt->execute([$phone]);
                $existing = $stmt->fetchColumn();
                if ($existing) {
                    echo json_encode(['success'=>true,'message'=>'Contractor exists.','id'=>$existing,'is_duplicate'=>true]); exit;
                }
            } else {
                $stmt = $pdo->prepare("SELECT id FROM app_contractors WHERE LOWER(name)=LOWER(?) LIMIT 1");
                $stmt->execute([$name]);
                $existing = $stmt->fetchColumn();
                if ($existing) {
                    echo json_encode(['success'=>true,'message'=>'Contractor exists.','id'=>$existing,'is_duplicate'=>true]); exit;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO app_contractors (name, phone, trade, address, notes, is_active) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name,$phone,$trade,$address,$notes,$is_active]);
            echo json_encode(['success'=>true,'message'=>'Contractor added.','id'=>$pdo->lastInsertId(),'is_duplicate'=>false]);
            break;

        case 'update':
            $id      = intval($_POST['id'] ?? 0);
            $name    = trim($_POST['name'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $trade   = trim($_POST['trade'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $notes   = trim($_POST['notes'] ?? '');
            $is_active= isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
            if (!$id || empty($name) || empty($trade)) {
                echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit;
            }
            $stmt = $pdo->prepare("UPDATE app_contractors SET name=?,phone=?,trade=?,address=?,notes=?,is_active=? WHERE id=?");
            $stmt->execute([$name,$phone,$trade,$address,$notes,$is_active,$id]);
            echo json_encode(['success'=>true,'message'=>'Contractor updated.']);
            break;

        case 'delete':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? $_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_contractors SET is_active=0 WHERE id=?")->execute([$id]);
            echo json_encode(['success'=>true,'message'=>'Contractor deactivated.']);
            break;

        case 'assign_to_project':
            $pid = intval($_POST['project_id'] ?? 0);
            $cid = intval($_POST['contractor_id'] ?? 0);
            if (!$pid || !$cid) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            $dup = $pdo->prepare("SELECT id FROM app_project_contractors WHERE project_id=? AND contractor_id=?");
            $dup->execute([$pid, $cid]);
            if ($dup->fetch()) { echo json_encode(['success'=>true,'message'=>'Already assigned.']); exit; }
            $pdo->prepare("INSERT INTO app_project_contractors (project_id, contractor_id, created_at) VALUES (?,?,NOW())")->execute([$pid, $cid]);
            echo json_encode(['success'=>true,'message'=>'Assigned successfully.']);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}