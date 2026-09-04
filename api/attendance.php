<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action     = $_GET['action'] ?? '';
$project_id = intval($_GET['project_id'] ?? $_POST['project_id'] ?? 0);

if (empty($project_id)) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }

try {
    switch ($action) {
        case 'add_attendance':
            $worker_id  = intval($_POST['worker_id'] ?? 0);
            $date       = $_POST['work_date'] ?? date('Y-m-d');
            $multiplier = floatval($_POST['attendance_multiplier'] ?? 1.0);
            $daily_rate = floatval($_POST['daily_rate'] ?? 0);
            $notes      = trim($_POST['notes'] ?? '');
            $days       = intval($_POST['days'] ?? 1);
            
            $allowed_mults = [0, 0.5, 1, 1.5, 2];
            if (!in_array($multiplier, $allowed_mults)) {
                echo json_encode(['success'=>false,'message'=>'Invalid attendance multiplier.']); exit;
            }

            if (!$worker_id || $daily_rate <= 0 || $multiplier < 0) {
                echo json_encode(['success'=>false,'message'=>'Invalid worker, rate or multiplier.']); exit;
            }
            if ($days < 1 || $days > 366) {
                echo json_encode(['success'=>false,'message'=>'Invalid number of days.']); exit;
            }
            $earned = round($daily_rate * $multiplier, 2);
            $wcat = $pdo->prepare("SELECT w.trade, c.id as cat_id FROM app_workers w LEFT JOIN app_categories c ON c.name LIKE CONCAT('%', w.trade, '%') WHERE w.id=? LIMIT 1");
            $wcat->execute([$worker_id]); $wrow = $wcat->fetch();
            $cat_id = $wrow['cat_id'] ?? null;
            $ins = $pdo->prepare("INSERT INTO app_attendance (project_id,worker_id,work_date,daily_rate,attendance_multiplier,earned,notes,category_id,created_by) VALUES (?,?,?,?,?,?,?,?,?)");
            $dup = $pdo->prepare("SELECT id FROM app_attendance WHERE project_id=? AND worker_id=? AND work_date=? AND is_deleted=0");
            $created = 0; $skipped = 0; $lastId = 0; $totalEarned = 0;
            $d = new DateTime($date);
            for ($i = 0; $i < $days; $i++) {
                $cur = $d->format('Y-m-d');
                $dup->execute([$project_id, $worker_id, $cur]);
                if ($dup->fetch()) { $skipped++; $d->modify('+1 day'); continue; }
                $ins->execute([$project_id,$worker_id,$cur,$daily_rate,$multiplier,$earned,$notes,$cat_id,$_SESSION['user_id']]);
                $lastId = $pdo->lastInsertId(); $created++; $totalEarned += $earned;
                $d->modify('+1 day');
            }
            echo json_encode([
                'success'=>true,
                'message'=>'Attendance recorded.',
                'created'=>$created,
                'skipped'=>$skipped,
                'earned'=>$earned,
                'total_earned'=>$totalEarned,
                'id'=>$lastId
            ]);
            break;

        case 'edit_attendance':
            $id         = intval($_POST['id'] ?? 0);
            $worker_id  = intval($_POST['worker_id'] ?? 0);
            $date       = $_POST['work_date'] ?? date('Y-m-d');
            $multiplier = floatval($_POST['attendance_multiplier'] ?? 1.0);
            $daily_rate = floatval($_POST['daily_rate'] ?? 0);
            $notes      = trim($_POST['notes'] ?? '');
            if (!$id || !$worker_id || $daily_rate <= 0 || $multiplier < 0) {
                echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit;
            }
            $earned = round($daily_rate * $multiplier, 2);
            $stmt = $pdo->prepare("UPDATE app_attendance SET worker_id=?,work_date=?,attendance_multiplier=?,daily_rate=?,earned=?,notes=?,updated_at=NOW() WHERE id=? AND project_id=? AND is_deleted=0");
            $stmt->execute([$worker_id,$date,$multiplier,$daily_rate,$earned,$notes,$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Attendance updated.','earned'=>$earned]);
            break;

        case 'list_attendance':
            $from = $_GET['from'] ?? null; $to = $_GET['to'] ?? null; $wid = intval($_GET['worker_id'] ?? 0);
            $sql = "SELECT a.*, w.name as worker_name, w.trade FROM app_attendance a JOIN app_workers w ON a.worker_id=w.id WHERE a.project_id=? AND a.is_deleted=0";
            $params = [$project_id];
            if ($from)  { $sql .= " AND a.work_date>=?"; $params[] = $from; }
            if ($to)    { $sql .= " AND a.work_date<=?"; $params[] = $to; }
            if ($wid)   { $sql .= " AND a.worker_id=?";  $params[] = $wid; }
            $sql .= " ORDER BY a.work_date DESC, a.id DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params);
            $rows = $stmt->fetchAll(); $total_earned = array_sum(array_column($rows,'earned'));
            echo json_encode(['success'=>true,'data'=>$rows,'total_earned'=>$total_earned]);
            break;

        case 'delete_attendance':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_attendance SET is_deleted=1 WHERE id=? AND project_id=?")->execute([$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Attendance deleted.']);
            break;

        case 'add_payment':
            $wid      = intval($_POST['worker_id'] ?? 0);
            $amount   = floatval($_POST['amount'] ?? 0);
            $date     = $_POST['payment_date'] ?? date('Y-m-d');
            $method   = $_POST['payment_method'] ?? 'Cash';
            $who_paid = trim($_POST['who_paid'] ?? $_SESSION['username'] ?? '');
            $who_recv = trim($_POST['who_received'] ?? '');
            $notes    = trim($_POST['notes'] ?? '');
            if (!$wid || $amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid worker or amount.']); exit; }
            $stmt = $pdo->prepare("INSERT INTO app_worker_payments (project_id,worker_id,amount,payment_date,payment_method,who_paid,who_received,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$project_id,$wid,$amount,$date,$method,$who_paid,$who_recv,$notes,$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Payment recorded.','id'=>$pdo->lastInsertId()]);
            break;

        case 'edit_payment':
            $id     = intval($_POST['id'] ?? 0);
            $wid    = intval($_POST['worker_id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $date   = $_POST['payment_date'] ?? date('Y-m-d');
            $method = $_POST['payment_method'] ?? 'Cash';
            $who_paid = trim($_POST['who_paid'] ?? '');
            $who_recv = trim($_POST['who_received'] ?? '');
            $notes  = trim($_POST['notes'] ?? '');
            if (!$id || !$wid || $amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            $stmt = $pdo->prepare("UPDATE app_worker_payments SET worker_id=?,amount=?,payment_date=?,payment_method=?,who_paid=?,who_received=?,notes=?,updated_at=NOW() WHERE id=? AND project_id=? AND is_deleted=0");
            $stmt->execute([$wid,$amount,$date,$method,$who_paid,$who_recv,$notes,$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Payment updated.']);
            break;

        case 'list_payments':
            $wid  = intval($_GET['worker_id'] ?? 0);
            $from = $_GET['from'] ?? null; $to = $_GET['to'] ?? null;
            $sql = "SELECT p.*, w.name as worker_name FROM app_worker_payments p JOIN app_workers w ON p.worker_id=w.id WHERE p.project_id=? AND p.is_deleted=0";
            $params = [$project_id];
            if ($wid)   { $sql .= " AND p.worker_id=?";  $params[] = $wid; }
            if ($from)  { $sql .= " AND p.payment_date>=?"; $params[] = $from; }
            if ($to)    { $sql .= " AND p.payment_date<=?"; $params[] = $to; }
            $sql .= " ORDER BY p.payment_date DESC, p.id DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params);
            $rows = $stmt->fetchAll(); $total = array_sum(array_column($rows,'amount'));
            echo json_encode(['success'=>true,'data'=>$rows,'total'=>$total]);
            break;

        case 'delete_payment':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id   = intval($data['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_worker_payments SET is_deleted=1 WHERE id=? AND project_id=?")->execute([$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Payment deleted.']);
            break;

        case 'get_summary':
            $stmtE = $pdo->prepare("SELECT worker_id, SUM(earned) as total_earned, SUM(attendance_multiplier) as total_days FROM app_attendance WHERE project_id=? AND is_deleted=0 GROUP BY worker_id");
            $stmtE->execute([$project_id]); $earnedData = $stmtE->fetchAll();
            $stmtP = $pdo->prepare("SELECT worker_id, SUM(amount) as total_paid FROM app_worker_payments WHERE project_id=? AND is_deleted=0 GROUP BY worker_id");
            $stmtP->execute([$project_id]); $paidData = $stmtP->fetchAll();
            $stmtW = $pdo->query("SELECT id, name, trade, default_daily_rate FROM app_workers WHERE is_active=1");
            $workers = []; foreach ($stmtW->fetchAll() as $w) $workers[$w['id']] = $w;
            $all_ids = array_unique(array_merge(array_column($earnedData,'worker_id'), array_column($paidData,'worker_id')));
            $summary = []; $grand_earned = 0; $grand_paid = 0;
            foreach ($all_ids as $wid) {
                $earned = 0; $days = 0; $paid = 0;
                foreach ($earnedData as $e) if ($e['worker_id']==$wid) { $earned=(float)$e['total_earned']; $days=(float)$e['total_days']; break; }
                foreach ($paidData as $p) if ($p['worker_id']==$wid) { $paid=(float)$p['total_paid']; break; }
                $summary[] = ['worker_id'=>$wid,'worker_name'=>$workers[$wid]['name']??'Unknown','trade'=>$workers[$wid]['trade']??'','total_days'=>$days,'total_earned'=>$earned,'total_paid'=>$paid,'balance_due'=>$earned-$paid];
                $grand_earned += $earned; $grand_paid += $paid;
            }
            echo json_encode(['success'=>true,'data'=>$summary,'totals'=>['earned'=>$grand_earned,'paid'=>$grand_paid,'due'=>$grand_earned-$grand_paid]]);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}