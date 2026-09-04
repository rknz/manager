<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/schedules.php — NEW: Schedule CRUD + mark done
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'list':
            $date       = $_GET['date']       ?? null;
            $project_id = intval($_GET['project_id'] ?? 0);
            $upcoming   = $_GET['upcoming']   ?? null;
            $sql = "SELECT s.*, p.name as project_name FROM app_schedules s LEFT JOIN app_projects p ON p.id=s.project_id WHERE 1=1";
            $params = [];
            if ($date)       { $sql .= " AND s.schedule_date=?"; $params[] = $date; }
            if ($project_id) { $sql .= " AND s.project_id=?"; $params[] = $project_id; }
            if ($upcoming)   { $sql .= " AND s.schedule_date >= CURDATE() AND s.is_done=0"; }
            $sql .= " ORDER BY s.schedule_date ASC, s.id ASC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params);
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
            break;

        case 'list_by_month':
            $month = $_GET['month'] ?? date('Y-m');
            $project_id = intval($_GET['project_id'] ?? 0);
            $sql = "SELECT s.*, p.name as project_name FROM app_schedules s LEFT JOIN app_projects p ON p.id=s.project_id WHERE DATE_FORMAT(s.schedule_date,'%Y-%m')=?";
            $params = [$month];
            if ($project_id) { $sql .= " AND s.project_id=?"; $params[] = $project_id; }
            $sql .= " ORDER BY s.schedule_date ASC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params);
            echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
            break;

        case 'create':
            $project_id   = intval($_POST['project_id'] ?? 0) ?: null;
            $schedule_date= trim($_POST['schedule_date'] ?? date('Y-m-d'));
            $category     = trim($_POST['category'] ?? '') ?: null;
            $description  = trim($_POST['description'] ?? '');
            if (empty($description) || empty($schedule_date)) {
                echo json_encode(['success'=>false,'message'=>'Date and description are required.']); exit;
            }
            $allowed_cats = ['Board','Paint','Glass','Electric','Payment'];
            if ($category && !in_array($category, $allowed_cats)) $category = null;
            $stmt = $pdo->prepare("INSERT INTO app_schedules (project_id,schedule_date,category,description,created_by) VALUES (?,?,?,?,?)");
            $stmt->execute([$project_id,$schedule_date,$category,$description,$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Schedule created.','id'=>$pdo->lastInsertId()]);
            break;

        case 'update':
            $id           = intval($_POST['id'] ?? 0);
            $schedule_date= trim($_POST['schedule_date'] ?? '');
            $category     = trim($_POST['category'] ?? '') ?: null;
            $description  = trim($_POST['description'] ?? '');
            $project_id   = intval($_POST['project_id'] ?? 0) ?: null;
            if (!$id || empty($description)) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            $allowed_cats = ['Board','Paint','Glass','Electric','Payment'];
            if ($category && !in_array($category, $allowed_cats)) $category = null;
            $stmt = $pdo->prepare("UPDATE app_schedules SET project_id=?,schedule_date=?,category=?,description=?,updated_at=NOW() WHERE id=? AND created_by=?");
            $stmt->execute([$project_id,$schedule_date,$category,$description,$id,$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Schedule updated.']);
            break;

        case 'mark_done':
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? $_POST['id'] ?? 0);
            $done = intval($data['is_done'] ?? 1);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_schedules SET is_done=?, updated_at=NOW() WHERE id=?")->execute([$done,$id]);
            echo json_encode(['success'=>true,'message'=>$done ? 'Marked as done.' : 'Marked as pending.']);
            break;

        case 'delete':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? $_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("DELETE FROM app_schedules WHERE id=?")->execute([$id]);
            echo json_encode(['success'=>true,'message'=>'Schedule deleted.']);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}

