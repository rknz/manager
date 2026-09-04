<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$project_id = intval($_GET['project_id'] ?? $_POST['project_id'] ?? 0);

$noProjectActions = ['get_all_contractors'];
if (empty($project_id) && !in_array($action, $noProjectActions)) {
    echo json_encode(['success' => false, 'message' => 'Project ID required.']); exit;
}

try {
    switch ($action) {
        case 'get_all_contractors':
            $stmt = $pdo->query("SELECT id, name, trade, phone FROM app_contractors WHERE is_active=1 ORDER BY name ASC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'assign_contractor':
            $cid = intval($_POST['contractor_id'] ?? 0);
            if (!$cid) { echo json_encode(['success'=>false,'message'=>'Contractor ID required.']); exit; }
            $stmt = $pdo->prepare("SELECT id FROM app_project_contractors WHERE project_id=? AND contractor_id=?");
            $stmt->execute([$project_id, $cid]);
            if ($stmt->fetch()) { echo json_encode(['success'=>false,'message'=>'Contractor already assigned.']); exit; }
            $pdo->prepare("INSERT INTO app_project_contractors (project_id, contractor_id) VALUES (?,?)")->execute([$project_id,$cid]);
            echo json_encode(['success'=>true,'message'=>'Contractor assigned.']);
            break;

        case 'list_project_contractors':
            $stmt = $pdo->prepare("SELECT pc.id as pc_id, c.id as contractor_id, c.name, c.trade, c.phone, c.address FROM app_project_contractors pc JOIN app_contractors c ON pc.contractor_id=c.id WHERE pc.project_id=?");
            $stmt->execute([$project_id]);
            $contractors = $stmt->fetchAll();
            foreach ($contractors as &$con) {
                $cid = $con['contractor_id'];
                $adv = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND contractor_id=? AND is_deleted=0");
                $adv->execute([$project_id,$cid]); $con['total_paid'] = (float)$adv->fetchColumn();
                $bill = $pdo->prepare("SELECT COALESCE(SUM(grand_total),0) FROM app_contractor_bills WHERE project_id=? AND contractor_id=? AND is_deleted=0");
                $bill->execute([$project_id,$cid]); $con['total_billed'] = (float)$bill->fetchColumn();
                $con['balance_due'] = max(0, $con['total_billed'] - $con['total_paid']);
            }
            unset($con);
            echo json_encode(['success'=>true,'data'=>$contractors]);
            break;

        case 'add_advance':
            $cid = intval($_POST['contractor_id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $date = $_POST['payment_date'] ?? date('Y-m-d');
            $method = $_POST['payment_method'] ?? 'Cash';
            $who_paid = trim($_POST['who_paid'] ?? $_SESSION['username'] ?? '');
            $who_recv = trim($_POST['who_received'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            if (!$cid || $amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            $stmt = $pdo->prepare("INSERT INTO app_contractor_advances (project_id,contractor_id,amount,payment_date,payment_method,who_paid,who_received,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$project_id,$cid,$amount,$date,$method,$who_paid,$who_recv,$notes,$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Advance recorded.','id'=>$pdo->lastInsertId()]);
            break;

        case 'edit_advance':
            $id = intval($_POST['id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $date = $_POST['payment_date'] ?? date('Y-m-d');
            $method = $_POST['payment_method'] ?? 'Cash';
            $who_paid = trim($_POST['who_paid'] ?? '');
            $who_recv = trim($_POST['who_received'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            if (!$id || $amount <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit; }
            $stmt = $pdo->prepare("UPDATE app_contractor_advances SET amount=?,payment_date=?,payment_method=?,who_paid=?,who_received=?,notes=?,updated_at=NOW() WHERE id=? AND project_id=? AND is_deleted=0");
            $stmt->execute([$amount,$date,$method,$who_paid,$who_recv,$notes,$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Advance updated.']);
            break;

        case 'delete_advance':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = intval($data['id'] ?? $_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_contractor_advances SET is_deleted=1 WHERE id=? AND project_id=?")->execute([$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Advance deleted.']);
            break;

        case 'list_advances':
            $cid = intval($_GET['contractor_id'] ?? 0);
            $sql = "SELECT a.*, c.name as contractor_name FROM app_contractor_advances a LEFT JOIN app_contractors c ON a.contractor_id=c.id WHERE a.project_id=? AND a.is_deleted=0";
            $params = [$project_id];
            if ($cid) {
                $sql .= " AND a.contractor_id=?";
                $params[] = $cid;
            }
            $sql .= " ORDER BY a.payment_date DESC, a.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $total = 0;
            foreach ($rows as $r) { $total += (float)$r['amount']; }
            echo json_encode(['success'=>true,'data'=>$rows, 'total'=>$total]);
            break;

        case 'list_advances_range':
            $cid = intval($_GET['contractor_id'] ?? 0);
            $from = $_GET['from'] ?? '1900-01-01';
            $to = $_GET['to'] ?? '2099-12-31';
            $sql = "SELECT a.*, c.name as contractor_name FROM app_contractor_advances a LEFT JOIN app_contractors c ON a.contractor_id=c.id WHERE a.project_id=? AND a.payment_date BETWEEN ? AND ? AND a.is_deleted=0";
            $params = [$project_id, $from, $to];
            if ($cid) {
                $sql .= " AND a.contractor_id=?";
                $params[] = $cid;
            }
            $sql .= " ORDER BY a.payment_date DESC, a.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $total = 0;
            foreach ($rows as $r) { $total += (float)$r['amount']; }
            echo json_encode(['success'=>true,'data'=>$rows, 'total'=>$total]);
            break;

        case 'list_bills':
            $cid = intval($_GET['contractor_id'] ?? 0);
            $sql = "SELECT b.*, c.name as contractor_name FROM app_contractor_bills b LEFT JOIN app_contractors c ON b.contractor_id=c.id WHERE b.project_id=? AND b.is_deleted=0";
            $params = [$project_id];
            if ($cid) {
                $sql .= " AND b.contractor_id=?";
                $params[] = $cid;
            }
            $sql .= " ORDER BY b.bill_date DESC, b.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $total = 0;
            foreach ($rows as $r) { $total += (float)$r['grand_total']; }
            echo json_encode(['success'=>true,'data'=>$rows, 'total'=>$total]);
            break;

        case 'delete_bill':
            if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = intval($data['id'] ?? $_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
            $pdo->prepare("UPDATE app_contractor_bills SET is_deleted=1 WHERE id=? AND project_id=?")->execute([$id,$project_id]);
            echo json_encode(['success'=>true,'message'=>'Bill deleted.']);
            break;

        case 'add_bill':
        case 'generate_bill':
            $cid          = intval($_POST['contractor_id'] ?? 0);
            $date         = $_POST['bill_date'] ?? date('Y-m-d');
            $bill_data    = $_POST['bill_data'] ?? $_POST['items_json'] ?? '[]';
            $sub_total    = floatval($_POST['sub_total'] ?? 0);
            $labour_charge= floatval($_POST['labour_charge'] ?? 0);
            $other_charge = floatval($_POST['other_charge'] ?? 0);
            $grand_total  = floatval($_POST['grand_total'] ?? ($sub_total + $labour_charge + $other_charge));
            $lang         = $_POST['bill_language'] ?? 'en';
            if (!$cid) { echo json_encode(['success'=>false,'message'=>'Contractor ID required.']); exit; }
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND contractor_id=? AND is_deleted=0");
            $stmt->execute([$project_id,$cid]); $total_paid = (float)$stmt->fetchColumn();
            $balance = $grand_total - $total_paid;
            $stmt = $pdo->prepare("INSERT INTO app_contractor_bills (project_id,contractor_id,sub_total,labour_charge,other_charge,bill_data,grand_total,total_paid,balance_due,bill_date,bill_language,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$project_id,$cid,$sub_total,$labour_charge,$other_charge,$bill_data,$grand_total,$total_paid,$balance,$date,$lang,$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Bill saved successfully.','id'=>$pdo->lastInsertId(),'balance_due'=>$balance]);
            break;

        case 'get_board_summary':
            $stmt = $pdo->prepare("SELECT board_type, board_thickness, board_size, item_name, SUM(quantity) as total_qty, MAX(unit) as unit FROM app_supply_purchases WHERE project_id=? AND (supply_category='Board' OR supply_category='Board & Wood') AND is_deleted=0 GROUP BY board_type, board_thickness, board_size, item_name ORDER BY board_type, board_thickness");
            $stmt->execute([$project_id]);
            $rows = $stmt->fetchAll();
            $grouped = [];
            foreach ($rows as $r) {
                $parts = [];
                if (!empty($r['board_type'])) $parts[] = trim($r['board_type']);
                if (!empty($r['board_thickness'])) $parts[] = trim($r['board_thickness']);
                if (!empty($r['board_size'])) $parts[] = trim($r['board_size']);
                $key = !empty($parts) ? implode(' - ', $parts) : trim($r['item_name']);
                if (!isset($grouped[$key])) {
                    $grouped[$key] = ['description' => $key, 'qty' => 0, 'unit' => $r['unit']];
                }
                $grouped[$key]['qty'] += floatval($r['total_qty']);
            }
            echo json_encode(['success'=>true,'data'=>array_values($grouped)]);
            break;

        case 'get_category_summary':
            $cid = intval($_GET['contractor_id'] ?? 0);
            if (!$cid) { echo json_encode(['success'=>false,'message'=>'Contractor ID required.']); exit; }

            $trade = '';
            $st = $pdo->prepare("SELECT trade FROM app_contractors WHERE id=?");
            $st->execute([$cid]);
            if ($row = $st->fetch()) $trade = (string)$row['trade'];
            $tradeLower = strtolower(trim($trade));

            $catName = null;
            $catId = null;
            if (strpos($tradeLower,'carpenter') !== false)      $catName = 'Board & Wood';
            elseif (strpos($tradeLower,'thai') !== false)        $catName = 'Thai & Glass';
            elseif (strpos($tradeLower,'glass') !== false)       $catName = 'Thai & Glass';
            elseif (strpos($tradeLower,'paint') !== false || strpos($tradeLower,'painter') !== false) $catName = 'Paint';
            elseif (strpos($tradeLower,'elec') !== false)        $catName = 'Electrical & Sanitary';
            else {
                $st = $pdo->prepare("SELECT category_id FROM app_project_contractors WHERE project_id=? AND contractor_id=?");
                $st->execute([$project_id, $cid]);
                if ($row = $st->fetch()) $catId = intval($row['category_id']);
            }

            $items = [];
            if ($catName || $catId) {
                $sql = "SELECT item_name, supply_category, category_id, board_type, board_thickness, board_size,
                               color_finish, size, unit, SUM(quantity) AS total_qty, SUM(total) AS total_amount
                        FROM app_supply_purchases
                        WHERE project_id=? AND is_deleted=0";
                $params = [$project_id];
                if ($catId) { $sql .= " AND category_id=?"; $params[] = $catId; }
                else { $sql .= " AND (supply_category=? OR category_id=(SELECT id FROM app_categories WHERE name=? LIMIT 1))"; $params[] = $catName; $params[] = $catName; }
                $sql .= " GROUP BY item_name, supply_category, category_id, board_type, board_thickness, board_size, color_finish, size, unit ORDER BY item_name";
                $st = $pdo->prepare($sql);
                $st->execute($params);
                foreach ($st->fetchAll() as $r) {
                    $parts = [];
                    if (!empty($r['board_type'])) $parts[] = trim($r['board_type']);
                    if (!empty($r['board_thickness'])) $parts[] = trim($r['board_thickness']);
                    if (!empty($r['board_size'])) $parts[] = trim($r['board_size']);
                    if (!empty($r['color_finish'])) $parts[] = trim($r['color_finish']);
                    if (!empty($r['size'])) $parts[] = trim($r['size']);
                    $desc = !empty($parts) ? implode(' - ', $parts) : trim($r['item_name']);
                    $qty = floatval($r['total_qty']);
                    $rate = $qty > 0 && floatval($r['total_amount']) > 0 ? round(floatval($r['total_amount']) / $qty, 2) : 0;
                    $items[] = ['description' => $desc, 'qty' => $qty, 'rate' => $rate];
                }
            }

            $att = false;
            if ($catName) {
                $st = $pdo->prepare("SELECT billing_type FROM app_categories WHERE name=? LIMIT 1");
                $st->execute([$catName]);
                if ($row = $st->fetch()) $att = ($row['billing_type'] === 'attendance');
            }

            $contractorName = '';
            $st = $pdo->prepare("SELECT name FROM app_contractors WHERE id=?");
            $st->execute([$cid]);
            if ($row = $st->fetch()) $contractorName = (string)$row['name'];

            $attendance = [];
            $payments = ['contractor_paid' => 0.0, 'crew' => []];
            
            $st = $pdo->prepare("SELECT w.id AS worker_id, w.name AS worker_name, w.trade,
                                        COALESCE(SUM(a.attendance_multiplier),0) AS days,
                                        COALESCE(SUM(a.earned),0) AS earned
                                 FROM app_workers w
                                 LEFT JOIN app_attendance a ON a.worker_id=w.id AND a.project_id=? AND a.is_deleted=0
                                 WHERE w.contractor_id=? AND w.is_active=1
                                 GROUP BY w.id, w.name, w.trade
                                 ORDER BY w.name");
            $st->execute([$project_id, $cid]);
            foreach ($st->fetchAll() as $w) {
                $paid = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND worker_id=? AND is_deleted=0");
                $paid->execute([$project_id, $w['worker_id']]);
                $wpaid = (float)$paid->fetchColumn();
                $isContractor = (mb_strtolower(trim($w['worker_name'])) === mb_strtolower(trim($contractorName)));
                $days = floatval($w['days']);
                $earned = floatval($w['earned']);
                $rate = $days > 0 ? round($earned / $days, 2) : 0;
                if ($days > 0) {
                    $attendance[] = [
                        'name' => $w['worker_name'],
                        'person_type' => $isContractor ? 'contractor' : 'worker',
                        'days' => $days,
                        'rate' => $rate,
                        'earned' => $earned,
                        'paid' => $wpaid
                    ];
                }
                $payments['crew'][] = ['name'=>$w['worker_name'], 'paid'=>$wpaid, 'earned'=>$earned, 'person_type'=>$isContractor?'contractor':'worker'];
            }
            $st = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND contractor_id=? AND is_deleted=0");
            $st->execute([$project_id, $cid]);
            $payments['contractor_paid'] = (float)$st->fetchColumn();

            echo json_encode(['success'=>true, 'data'=>$items, 'category'=>$catName, 'attendance'=>$attendance, 'payments'=>$payments]);
            break;

        case 'get_worker_bill_data':
            $wid = intval($_GET['worker_id'] ?? $_POST['worker_id'] ?? 0);
            if (!$wid) { echo json_encode(['success'=>false,'message'=>'Worker ID required.']); exit; }

            $st = $pdo->prepare("SELECT id, name, phone, trade, address, default_daily_rate FROM app_workers WHERE id=?");
            $st->execute([$wid]);
            $worker = $st->fetch();
            if (!$worker) { echo json_encode(['success'=>false,'message'=>'Worker not found.']); exit; }

            // Fetch attendance records for this worker in this project
            $st = $pdo->prepare("SELECT work_date, attendance_multiplier, daily_rate, earned, notes FROM app_attendance WHERE project_id=? AND worker_id=? AND is_deleted=0 ORDER BY work_date ASC");
            $st->execute([$project_id, $wid]);
            $attRows = $st->fetchAll(PDO::FETCH_ASSOC);

            $items = [];
            $totalDays = 0;
            $totalEarned = 0;
            foreach ($attRows as $a) {
                $days = floatval($a['attendance_multiplier']);
                $rate = floatval($a['daily_rate']);
                $earned = floatval($a['earned']);
                $dateFormatted = date('d M Y', strtotime($a['work_date']));
                $desc = "Work on " . $dateFormatted . (!empty($a['notes']) ? " (" . $a['notes'] . ")" : "");
                $items[] = [
                    'description' => $desc,
                    'qty' => $days,
                    'rate' => $rate,
                    'total' => $earned
                ];
                $totalDays += $days;
                $totalEarned += $earned;
            }

            // Total paid to this worker in this project
            $st = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND worker_id=? AND is_deleted=0");
            $st->execute([$project_id, $wid]);
            $totalPaid = (float)$st->fetchColumn();

            echo json_encode([
                'success' => true,
                'worker' => $worker,
                'items' => $items,
                'total_days' => $totalDays,
                'total_earned' => $totalEarned,
                'total_paid' => $totalPaid,
                'balance_due' => max(0, $totalEarned - $totalPaid)
            ]);
            break;

        case 'get_advance_preview':
            $cid = intval($_GET['contractor_id'] ?? 0);
            if (!$cid) { echo json_encode(['success'=>false,'message'=>'Contractor ID required.']); exit; }

            $contractor = null;
            $st = $pdo->prepare("SELECT id, name, trade, phone FROM app_contractors WHERE id=?");
            $st->execute([$cid]);
            $contractor = $st->fetch();
            if (!$contractor) { echo json_encode(['success'=>false,'message'=>'Contractor not found.']); exit; }
            $contractorName = (string)$contractor['name'];

            // Date-sorted payments: contractor advances (paid to officer) + crew worker payments (paid to workers)
            $payments = [];
            $contractor_paid = 0.0;
            $worker_paid = 0.0;

            $st = $pdo->prepare("SELECT payment_date, amount FROM app_contractor_advances WHERE project_id=? AND contractor_id=? AND is_deleted=0 ORDER BY payment_date ASC");
            $st->execute([$project_id, $cid]);
            foreach ($st->fetchAll() as $a) {
                $amt = (float)$a['amount'];
                $contractor_paid += $amt;
                $payments[] = ['date'=>$a['payment_date'], 'name'=>$contractorName, 'person_type'=>'contractor', 'amount'=>$amt, 'source'=>'advance'];
            }

            $st = $pdo->prepare("SELECT p.id, p.amount, p.payment_date, w.name AS worker_name
                                 FROM app_worker_payments p
                                 JOIN app_workers w ON w.id=p.worker_id
                                 WHERE p.project_id=? AND w.contractor_id=? AND p.is_deleted=0
                                 ORDER BY p.payment_date ASC");
            $st->execute([$project_id, $cid]);
            foreach ($st->fetchAll() as $p) {
                $amt = (float)$p['amount'];
                $worker_paid += $amt;
                $isContractor = (mb_strtolower(trim($p['worker_name'])) === mb_strtolower(trim($contractorName)));
                $payments[] = ['date'=>$p['payment_date'], 'name'=>$p['worker_name'], 'person_type'=>$isContractor?'contractor':'worker', 'amount'=>$amt, 'source'=>'worker_payment'];
            }
            usort($payments, function($a,$b){ return strcmp($a['date'], $b['date']); });

            // Date-sorted attendance for contractor + crew
            $attendance = [];
            $st = $pdo->prepare("SELECT a.work_date, a.attendance_multiplier, a.daily_rate, a.earned, w.name AS worker_name
                                 FROM app_attendance a
                                 JOIN app_workers w ON w.id=a.worker_id
                                 WHERE a.project_id=? AND w.contractor_id=? AND a.is_deleted=0
                                 ORDER BY a.work_date ASC");
            $st->execute([$project_id, $cid]);
            foreach ($st->fetchAll() as $at) {
                $isContractor = (mb_strtolower(trim($at['worker_name'])) === mb_strtolower(trim($contractorName)));
                $attendance[] = [
                    'date'=>$at['work_date'],
                    'name'=>$at['worker_name'],
                    'person_type'=>$isContractor?'contractor':'worker',
                    'mult'=>floatval($at['attendance_multiplier']),
                    'rate'=>floatval($at['daily_rate']),
                    'earned'=>floatval($at['earned'])
                ];
            }

            // Per-person attendance summary
            $personSummary = [];
            $st = $pdo->prepare("SELECT w.id AS wid, w.name AS worker_name,
                                        COALESCE(SUM(a.attendance_multiplier),0) AS days,
                                        COALESCE(SUM(a.earned),0) AS earned,
                                        COALESCE((SELECT SUM(amount) FROM app_worker_payments p WHERE p.worker_id=w.id AND p.project_id=? AND p.is_deleted=0),0) AS paid
                                 FROM app_workers w
                                 LEFT JOIN app_attendance a ON a.worker_id=w.id AND a.project_id=? AND a.is_deleted=0
                                 WHERE w.contractor_id=? AND w.is_active=1
                                 GROUP BY w.id, w.name ORDER BY w.name");
            $st->execute([$project_id, $project_id, $cid]);
            foreach ($st->fetchAll() as $s) {
                $isContractor = (mb_strtolower(trim($s['worker_name'])) === mb_strtolower(trim($contractorName)));
                $personSummary[] = [
                    'name'=>$s['worker_name'],
                    'person_type'=>$isContractor?'contractor':'worker',
                    'days'=>floatval($s['days']),
                    'earned'=>floatval($s['earned']),
                    'paid'=>floatval($s['paid'])
                ];
            }

            echo json_encode([
                'success'=>true,
                'contractor'=>$contractor,
                'payments'=>$payments,
                'attendance'=>$attendance,
                'personSummary'=>$personSummary,
                'summary'=>[
                    'contractor_paid'=>$contractor_paid,
                    'worker_paid'=>$worker_paid,
                    'total_paid'=>$contractor_paid+$worker_paid,
                    'total_earned'=>array_sum(array_column($personSummary,'earned'))
                ]
            ]);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}