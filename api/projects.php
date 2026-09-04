<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/projects.php — Extended: new fields, get_single, upload_image
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'list':
            $status = $_GET['status'] ?? 'all';
            $q      = $_GET['q']      ?? null;
            $sql    = "SELECT id, name, client_name, client_phone, client_email, client_address,
                              project_type, status, estimated_budget, start_date, end_date, project_image, address
                       FROM app_projects WHERE is_deleted=0";
            $params = [];
            if ($status !== 'all') { $sql .= " AND status=?"; $params[] = ucfirst($status); }
            if ($q) { $sql .= " AND (name LIKE ? OR client_name LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
            $sql .= " ORDER BY id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add progress calculation
            foreach ($projects as &$p) {
                $pid = $p['id'];
                $sMat = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0");
                $sMat->execute([$pid]); $matSpent = (float)$sMat->fetchColumn();
                
                $sAdv = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND is_deleted=0");
                $sAdv->execute([$pid]); $advSpent = (float)$sAdv->fetchColumn();
                
                $sLab = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND is_deleted=0");
                $sLab->execute([$pid]); $labSpent = (float)$sLab->fetchColumn();

                $spent = $matSpent + $advSpent + $labSpent;
                $budget = (float)($p['estimated_budget'] ?? 0);
                $p['progress'] = $budget > 0 ? min(100, round(($spent / $budget) * 100)) : 0;
                $p['spent'] = $spent;
            }
            unset($p);

            echo json_encode(['success' => true, 'data' => $projects]);
            break;

        case 'get_single':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); exit; }
            $stmt = $pdo->prepare("SELECT * FROM app_projects WHERE id=? AND is_deleted=0");
            $stmt->execute([$id]);
            $p = $stmt->fetch();
            if (!$p) { echo json_encode(['success'=>false,'message'=>'Project not found']); exit; }
            // Attach images
            $imgs = $pdo->prepare("SELECT * FROM app_project_images WHERE project_id=? ORDER BY is_primary DESC, sort_order ASC");
            $imgs->execute([$id]);
            $p['images'] = $imgs->fetchAll();
            echo json_encode(['success' => true, 'data' => $p]);
            break;

        case 'create':
            $name          = trim($_POST['name'] ?? '');
            $address       = trim($_POST['address'] ?? '');
            $client_name   = trim($_POST['client_name'] ?? '');
            $client_phone  = trim($_POST['client_phone'] ?? '');
            $client_email  = trim($_POST['client_email'] ?? '');
            $client_address= trim($_POST['client_address'] ?? '');
            $project_type  = $_POST['project_type'] ?? 'Residential';
            $status        = $_POST['status'] ?? 'Ongoing';
            $budget        = floatval($_POST['estimated_budget'] ?? 0);
            $start_date    = trim($_POST['start_date'] ?? '');
            if ($start_date === '') $start_date = date('Y-m-d');
            $end_date      = trim($_POST['end_date'] ?? '');
            if ($end_date === '') $end_date = null;
            $notes         = trim($_POST['notes'] ?? '');
            $project_image = trim($_POST['project_image'] ?? '') ?: null;
            if (empty($name) || empty($client_name)) {
                echo json_encode(['success'=>false,'message'=>'Project Name and Client Name are required.']); exit;
            }
            $stmt = $pdo->prepare("INSERT INTO app_projects
                (name, address, client_name, client_phone, client_email, client_address, project_type, status, estimated_budget, start_date, end_date, notes, project_image, created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name,$address,$client_name,$client_phone,$client_email,$client_address,$project_type,$status,$budget,$start_date,$end_date,$notes,$project_image,$_SESSION['user_id']]);
            $newId = $pdo->lastInsertId();
            echo json_encode(['success'=>true,'message'=>'Project created.','id'=>$newId]);
            break;

        case 'update':
            $id            = intval($_POST['id'] ?? 0);
            $name          = trim($_POST['name'] ?? '');
            $address       = trim($_POST['address'] ?? '');
            $client_name   = trim($_POST['client_name'] ?? '');
            $client_phone  = trim($_POST['client_phone'] ?? '');
            $client_email  = trim($_POST['client_email'] ?? '');
            $client_address= trim($_POST['client_address'] ?? '');
            $project_type  = $_POST['project_type'] ?? 'Residential';
            $status        = $_POST['status'] ?? 'Ongoing';
            $budget        = floatval($_POST['estimated_budget'] ?? 0);
            $start_date    = trim($_POST['start_date'] ?? '');
            if ($start_date === '') $start_date = date('Y-m-d');
            $end_date      = trim($_POST['end_date'] ?? '');
            if ($end_date === '') $end_date = null;
            $notes         = trim($_POST['notes'] ?? '');
            $project_image = trim($_POST['project_image'] ?? '') ?: null;
            if (!$id || empty($name)) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            $sql = "UPDATE app_projects SET name=?,address=?,client_name=?,client_phone=?,client_email=?,client_address=?,project_type=?,status=?,estimated_budget=?,start_date=?,end_date=?,notes=?,updated_at=NOW()";
            $params = [$name,$address,$client_name,$client_phone,$client_email,$client_address,$project_type,$status,$budget,$start_date,$end_date,$notes];
            if ($project_image !== null) { $sql .= ",project_image=?"; $params[] = $project_image; }
            $sql .= " WHERE id=?"; $params[] = $id;
            $pdo->prepare($sql)->execute($params);
            echo json_encode(['success'=>true,'message'=>'Project updated.']);
            break;

        case 'delete':
            if (!verifyAdminAction()) {
                echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit;
            }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? $_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }

            // Load project record first
            $stmt = $pdo->prepare("SELECT * FROM app_projects WHERE id=? AND is_deleted=0");
            $stmt->execute([$id]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$project) { echo json_encode(['success'=>false,'message'=>'Project not found.']); exit; }

            $pdo->beginTransaction();
            try {
                // Delete printout files
                $stmt = $pdo->prepare("SELECT file_path FROM app_printouts WHERE project_id=?");
                $stmt->execute([$id]);
                while ($file = $stmt->fetchColumn()) {
                    if ($file && file_exists(__DIR__ . '/../' . $file)) @unlink(__DIR__ . '/../' . $file);
                }
                
                // Delete project images
                $stmt = $pdo->prepare("SELECT image_path FROM app_project_images WHERE project_id=?");
                $stmt->execute([$id]);
                while ($img = $stmt->fetchColumn()) {
                    if ($img && file_exists(__DIR__ . '/../' . $img)) @unlink(__DIR__ . '/../' . $img);
                }
                
                // Delete related records
                $pdo->prepare("DELETE FROM app_printouts WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_project_images WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_attendance WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_client_payments WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_contractor_advances WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_contractor_bills WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_schedules WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_project_contractors WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_supply_purchases WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_worker_payments WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_glass_advances WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_notifications WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_purchases WHERE project_id=?")->execute([$id]);
                $pdo->prepare("DELETE FROM app_thai_glass_bills WHERE project_id=?")->execute([$id]);
                
                // Finally, delete the project
                $pdo->prepare("DELETE FROM app_projects WHERE id=?")->execute([$id]);
                
                $pdo->commit();
                echo json_encode([
                    'success'     => true,
                    'message'     => 'Project deleted.'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success'=>false,'message'=>'Error deleting project: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

