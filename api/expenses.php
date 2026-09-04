<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/expenses.php — Project Expense management (list/create/update/delete + aggregations)
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action     = $_GET['action'] ?? '';
$project_id = intval($_GET['project_id'] ?? $_POST['project_id'] ?? 0);

try {
    if (empty($action) || empty($project_id)) {
        echo json_encode(['success'=>false,'message'=>'Project ID required.']);
        exit;
    }

    switch ($action) {
        case 'list':
            $w  = ["e.project_id=? AND e.is_deleted=0"];
            $p  = [$project_id];
            if (!empty($_GET['from']) && !empty($_GET['to'])) {
                $w[]  = "e.expense_date BETWEEN ? AND ?";
                $p[]  = $_GET['from'];
                $p[]  = $_GET['to'];
            }
            if (!empty($_GET['category']))  { $w[] = "e.category=?";  $p[] = $_GET['category']; }
            if (!empty($_GET['method']))    { $w[] = "e.payment_method=?"; $p[] = $_GET['method']; }
            if (!empty($_GET['q']))         { $w[] = "(e.description LIKE ? OR e.vendor LIKE ? OR e.category LIKE ?)"; $q = '%'.$_GET['q'].'%'; $p[] = $q; $p[] = $q; $p[] = $q; }
            if (isset($_GET['status']) && $_GET['status'] !== '') {
                $s = $_GET['status'];
                if ($s === 'paid')   $w[] = "e.paid>=e.amount";
                if ($s === 'partial') $w[] = "e.paid>0 AND e.paid<e.amount";
                if ($s === 'unpaid') $w[] = "e.paid=0 AND e.amount>0";
            }
            $where = implode(' AND ', $w);

            $stmt = $pdo->prepare("SELECT e.*, u.username AS created_by_name FROM app_expenses e LEFT JOIN app_users u ON u.id=e.created_by WHERE $where ORDER BY e.expense_date DESC, e.id DESC");
            $stmt->execute($p);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $agg = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total, COALESCE(SUM(paid),0) AS paid, COALESCE(SUM(amount-paid),0) AS due, COALESCE(SUM(CASE WHEN paid>=amount THEN 1 ELSE 0 END),0) AS paid_cnt, COUNT(DISTINCT category) AS categories, COUNT(DISTINCT vendor) AS vendors FROM app_expenses e WHERE $where");
            $agg->execute($p);
            $totals = $agg->fetch(PDO::FETCH_ASSOC);

            $catStmt = $pdo->prepare("SELECT e.category AS name, COUNT(*) AS cnt, COALESCE(SUM(e.amount),0) AS total FROM app_expenses e WHERE $where GROUP BY e.category ORDER BY total DESC");
            $catStmt->execute($p);
            $by_category = $catStmt->fetchAll(PDO::FETCH_ASSOC);

            $monStmt = $pdo->prepare("SELECT DATE_FORMAT(e.expense_date,'%Y-%m') AS ym, COALESCE(SUM(e.amount),0) AS total FROM app_expenses e WHERE $where GROUP BY ym ORDER BY ym");
            $monStmt->execute($p);
            $monthly = $monStmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data'    => $rows,
                'totals'  => $totals,
                'by_category' => $by_category,
                'monthly' => $monthly
            ]);
            break;

        case 'create':
            $amount   = floatval($_POST['amount'] ?? 0);
            $paid     = isset($_POST['paid']) && $_POST['paid'] !== '' ? floatval($_POST['paid']) : $amount;
            $date     = $_POST['expense_date'] ?? date('Y-m-d');
            $cat      = trim($_POST['category'] ?? 'Other');
            $desc     = trim($_POST['description'] ?? '');
            $vendor   = trim($_POST['vendor'] ?? '');
            $method   = $_POST['payment_method'] ?? 'Cash';
            $notes    = trim($_POST['notes'] ?? '');
            if ($amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid amount.']); exit; }
            if ($cat === '') { $cat = 'Other'; }
            $paid = min($paid, $amount);
            $stmt = $pdo->prepare("INSERT INTO app_expenses (project_id,category,description,vendor,amount,paid,payment_method,expense_date,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$project_id,$cat,$desc,$vendor,$amount,$paid,$method,$date,$notes,$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Expense added.','id'=>$pdo->lastInsertId()]);
            break;

        case 'update':
            $id      = intval($_POST['id'] ?? 0);
            $amount  = floatval($_POST['amount'] ?? 0);
            $paid    = isset($_POST['paid']) && $_POST['paid'] !== '' ? floatval($_POST['paid']) : $amount;
            $date    = $_POST['expense_date'] ?? date('Y-m-d');
            $cat     = trim($_POST['category'] ?? 'Other');
            $desc    = trim($_POST['description'] ?? '');
            $vendor  = trim($_POST['vendor'] ?? '');
            $method  = $_POST['payment_method'] ?? 'Cash';
            $notes   = trim($_POST['notes'] ?? '');
            if (!$id || $amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            if ($cat === '') { $cat = 'Other'; }
            $paid = min($paid, $amount);
            $stmt = $pdo->prepare("UPDATE app_expenses SET category=?,description=?,vendor=?,amount=?,paid=?,payment_method=?,expense_date=?,notes=?,updated_at=NOW() WHERE id=? AND project_id=? AND is_deleted=0");
            $stmt->execute([$cat,$desc,$vendor,$amount,$paid,$method,$date,$notes,$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Expense updated.']);
            break;

        case 'delete':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_expenses SET is_deleted=1, updated_at=NOW() WHERE id=? AND project_id=?")->execute([$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Expense deleted.']);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}