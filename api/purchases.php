<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/purchases.php — Extended: update, autocomplete, list_categories, category_id, purchased_by optional
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action     = $_GET['action'] ?? '';
$project_id = intval($_GET['project_id'] ?? $_POST['project_id'] ?? 0);

// project_id not required for autocomplete/list_categories
$needsProject = !in_array($action, ['autocomplete','list_categories']);
if ($needsProject && empty($project_id)) {
    echo json_encode(['success' => false, 'message' => 'Project ID required.']); exit;
}

try {
    switch ($action) {

        case 'list':
            $from  = $_GET['from']     ?? null;
            $to    = $_GET['to']       ?? null;
            $cat   = $_GET['category'] ?? null;
            $q     = $_GET['q']        ?? null;
            $sql   = "SELECT sp.*, c.name as category_name FROM app_supply_purchases sp
                      LEFT JOIN app_categories c ON c.id = sp.category_id
                      WHERE sp.project_id = ? AND sp.is_deleted = 0";
            $params = [$project_id];
            if ($from)  { $sql .= " AND sp.purchase_date >= ?"; $params[] = $from; }
            if ($to)    { $sql .= " AND sp.purchase_date <= ?"; $params[] = $to; }
            if ($cat)   { $sql .= " AND (sp.category_id = ? OR sp.supply_category = ?)"; $params[] = $cat; $params[] = $cat; }
            if ($q)     { $sql .= " AND (sp.item_name LIKE ? OR c.name LIKE ?)"; $params[] = '%'.$q.'%'; $params[] = '%'.$q.'%'; }
            $sql .= " ORDER BY sp.purchase_date DESC, sp.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $total = array_sum(array_column($rows, 'total'));
            echo json_encode(['success' => true, 'data' => $rows, 'total' => $total]);
            break;

        case 'create':
            $item_name       = trim($_POST['item_name'] ?? '');
            $supply_category = trim($_POST['supply_category'] ?? '');
            $category_id     = intval($_POST['category_id'] ?? 0) ?: null;
            $board_type      = trim($_POST['board_type'] ?? '') ?: null;
            $board_thickness = trim($_POST['board_thickness'] ?? '') ?: null;
            $board_size      = trim($_POST['board_size'] ?? '') ?: null;
            $color_finish    = trim($_POST['color_finish'] ?? '') ?: null;
            $size            = trim($_POST['size'] ?? '') ?: null;
            $contractor_id   = intval($_POST['contractor_id'] ?? 0) ?: null;
            $quantity        = floatval($_POST['quantity'] ?? 0);
            $unit            = trim($_POST['unit'] ?? 'pcs');
            $rate            = floatval($_POST['rate'] ?? 0);
            $supplier        = trim($_POST['supplier'] ?? '') ?: null;
            $purchase_date   = trim($_POST['purchase_date'] ?? date('Y-m-d'));
            $notes           = trim($_POST['notes'] ?? '') ?: null;
            // purchased_by is now optional — default to logged-in user
            $purchased_by    = trim($_POST['purchased_by'] ?? $_SESSION['username'] ?? 'Admin');

            if (empty($item_name) || $quantity <= 0 || $rate <= 0) {
                echo json_encode(['success' => false, 'message' => 'Item name, quantity and rate are required.']); exit;
            }
            // Non-board categories: clear board fields
            if (strtolower($supply_category) !== 'board' && strtolower($supply_category) !== 'board & wood') {
                $board_type = $board_thickness = $board_size = null;
            }
            $total = round($quantity * $rate, 2);
            $stmt = $pdo->prepare("INSERT INTO app_supply_purchases
                (project_id, contractor_id, item_name, supply_category, category_id, board_type, board_thickness, board_size, color_finish, size, quantity, unit, rate, total, supplier, purchased_by, purchase_date, notes, created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$project_id,$contractor_id,$item_name,$supply_category,$category_id,$board_type,$board_thickness,$board_size,$color_finish,$size,$quantity,$unit,$rate,$total,$supplier,$purchased_by,$purchase_date,$notes,$_SESSION['user_id']]);
            $newId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Purchase added successfully.', 'id' => $newId, 'total' => $total]);
            break;

        case 'update':
            $id              = intval($_POST['id'] ?? 0);
            $item_name       = trim($_POST['item_name'] ?? '');
            $supply_category = trim($_POST['supply_category'] ?? '');
            $category_id     = intval($_POST['category_id'] ?? 0) ?: null;
            $board_type      = trim($_POST['board_type'] ?? '') ?: null;
            $board_thickness = trim($_POST['board_thickness'] ?? '') ?: null;
            $board_size      = trim($_POST['board_size'] ?? '') ?: null;
            $color_finish    = trim($_POST['color_finish'] ?? '') ?: null;
            $size            = trim($_POST['size'] ?? '') ?: null;
            $contractor_id   = intval($_POST['contractor_id'] ?? 0) ?: null;
            $quantity        = floatval($_POST['quantity'] ?? 0);
            $unit            = trim($_POST['unit'] ?? 'pcs');
            $rate            = floatval($_POST['rate'] ?? 0);
            $supplier        = trim($_POST['supplier'] ?? '') ?: null;
            $purchase_date   = trim($_POST['purchase_date'] ?? date('Y-m-d'));
            $notes           = trim($_POST['notes'] ?? '') ?: null;
            $purchased_by    = trim($_POST['purchased_by'] ?? $_SESSION['username'] ?? 'Admin');
            if (!$id || empty($item_name) || $quantity <= 0 || $rate <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid data.']); exit;
            }
            if (strtolower($supply_category) !== 'board' && strtolower($supply_category) !== 'board & wood') {
                $board_type = $board_thickness = $board_size = null;
            }
            $total = round($quantity * $rate, 2);
            $stmt = $pdo->prepare("UPDATE app_supply_purchases SET
                contractor_id=?,item_name=?,supply_category=?,category_id=?,board_type=?,board_thickness=?,board_size=?,color_finish=?,size=?,quantity=?,unit=?,rate=?,total=?,supplier=?,purchased_by=?,purchase_date=?,notes=?,updated_at=NOW()
                WHERE id=? AND project_id=? AND is_deleted=0");
            $stmt->execute([$contractor_id,$item_name,$supply_category,$category_id,$board_type,$board_thickness,$board_size,$color_finish,$size,$quantity,$unit,$rate,$total,$supplier,$purchased_by,$purchase_date,$notes,$id,$project_id]);
            echo json_encode(['success' => true, 'message' => 'Purchase updated.', 'total' => $total]);
            break;

        case 'delete':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? $_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false, 'message' => 'ID required.']); exit; }
            $pdo->prepare("UPDATE app_supply_purchases SET is_deleted=1 WHERE id=? AND project_id=?")->execute([$id,$project_id]);
            echo json_encode(['success' => true, 'message' => 'Deleted successfully.']);
            break;

        case 'autocomplete':
            $field = $_GET['field'] ?? 'item_name';
            $term  = '%' . trim($_GET['term'] ?? '') . '%';
            $allowed = ['item_name','board_thickness','board_size','color_finish','size','supply_category','supplier'];
            if (!in_array($field, $allowed)) { echo json_encode(['success'=>false,'message'=>'Invalid field']); exit; }
            $stmt = $pdo->prepare("SELECT DISTINCT `$field` as value FROM app_supply_purchases WHERE `$field` LIKE ? AND `$field` IS NOT NULL AND `$field` != '' ORDER BY `$field` LIMIT 10");
            $stmt->execute([$term]);
            echo json_encode(['success' => true, 'data' => array_column($stmt->fetchAll(), 'value')]);
            break;

        case 'list_categories':
            $cats = $pdo->query("SELECT id, name FROM app_categories ORDER BY name")->fetchAll();
            echo json_encode(['success' => true, 'data' => $cats]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

