<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/reports.php — Financial Reports & Analytics: project summaries, purchases, labor, attendance, payments, expenses, financial overview
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'project_purchases':
            $pid  = intval($_GET['project_id'] ?? 0);
            $from = $_GET['from'] ?? null;
            $to   = $_GET['to']   ?? null;
            $cat  = $_GET['category'] ?? null;
            $q    = $_GET['q'] ?? null;
            $sup  = $_GET['supplier'] ?? null;
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $sql = "SELECT sp.*, c.name as cat_name FROM app_supply_purchases sp
                    LEFT JOIN app_categories c ON c.id=sp.category_id
                    WHERE sp.project_id=? AND sp.is_deleted=0";
            $params = [$pid];
            if ($from) { $sql .= " AND sp.purchase_date>=?"; $params[] = $from; }
            if ($to)   { $sql .= " AND sp.purchase_date<=?"; $params[] = $to; }
            if ($cat)  { $sql .= " AND (sp.category_id=? OR sp.supply_category=?)"; $params[] = $cat; $params[] = $cat; }
            if ($sup)  { $sql .= " AND sp.supplier=?"; $params[] = $sup; }
            if ($q)    { $sql .= " AND (sp.item_name LIKE ? OR sp.supplier LIKE ? OR sp.notes LIKE ?)"; $like = '%'.$q.'%'; $params[] = $like; $params[] = $like; $params[] = $like; }
            $sql .= " ORDER BY sp.purchase_date ASC, sp.id DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $by_cat = [];
            $by_sup = [];
            $monthly = [];
            foreach ($rows as $r) {
                $cname = $r['cat_name'] ?: $r['supply_category'] ?: 'Uncategorized';
                $by_cat[$cname] = ($by_cat[$cname] ?? 0) + (float)$r['total'];
                $sname = $r['supplier'] ?: 'Unknown';
                $by_sup[$sname] = ($by_sup[$sname] ?? 0) + (float)$r['total'];
                $ym = date('Y-m', strtotime($r['purchase_date']));
                $monthly[$ym] = ($monthly[$ym] ?? 0) + (float)$r['total'];
            }
            arsort($by_cat); arsort($by_sup); ksort($monthly);
            echo json_encode([
                'success' => true,
                'data'     => $rows,
                'total'    => array_sum(array_column($rows,'total')),
                'count'    => count($rows),
                'total_qty'=> array_sum(array_map(function($r){ return (float)$r['quantity']; }, $rows)),
                'suppliers'=> count(array_filter(array_unique(array_column($rows,'supplier')), function($s){ return $s !== null && $s !== ''; })),
                'by_category' => $by_cat,
                'by_supplier' => $by_sup,
                'monthly'     => $monthly
            ]);
            break;

        case 'contractor_payments_report':
            $pid  = intval($_GET['project_id'] ?? 0);
            $cid  = intval($_GET['contractor_id'] ?? 0);
            $from = $_GET['from'] ?? null; $to = $_GET['to'] ?? null;
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $sql = "SELECT ca.*, c.name as contractor_name, c.trade FROM app_contractor_advances ca JOIN app_contractors c ON c.id=ca.contractor_id WHERE ca.project_id=? AND ca.is_deleted=0";
            $params = [$pid];
            if ($cid) { $sql .= " AND ca.contractor_id=?"; $params[] = $cid; }
            if ($from){ $sql .= " AND ca.payment_date>=?"; $params[] = $from; }
            if ($to)  { $sql .= " AND ca.payment_date<=?"; $params[] = $to; }
            $sql .= " ORDER BY ca.payment_date ASC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
            echo json_encode(['success'=>true,'data'=>$rows,'total'=>array_sum(array_column($rows,'amount'))]);
            break;

        case 'labor_payments_report':
            $pid  = intval($_GET['project_id'] ?? 0);
            $wid  = intval($_GET['worker_id'] ?? 0);
            $from = $_GET['from'] ?? null; $to = $_GET['to'] ?? null;
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $sql = "SELECT wp.*, w.name as worker_name, w.trade FROM app_worker_payments wp JOIN app_workers w ON w.id=wp.worker_id WHERE wp.project_id=? AND wp.is_deleted=0";
            $params = [$pid];
            if ($wid) { $sql .= " AND wp.worker_id=?"; $params[] = $wid; }
            if ($from){ $sql .= " AND wp.payment_date>=?"; $params[] = $from; }
            if ($to)  { $sql .= " AND wp.payment_date<=?"; $params[] = $to; }
            $sql .= " ORDER BY wp.payment_date ASC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
            echo json_encode(['success'=>true,'data'=>$rows,'total'=>array_sum(array_column($rows,'amount'))]);
            break;

        case 'project_summary':
            $pid = intval($_GET['project_id'] ?? 0);
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $m = $pdo->prepare("SELECT * FROM app_projects WHERE id=? AND is_deleted=0"); $m->execute([$pid]); $project = $m->fetch();
            if (!$project) { echo json_encode(['success'=>false,'message'=>'Project not found.']); exit; }
            $s = function($sql, $p=null) use ($pdo) {
                $stmt = $pdo->prepare($sql); $stmt->execute($p ?? []); return (float)$stmt->fetchColumn();
            };
            $monthSeries = function($sql, $p) use ($pdo) {
                $stmt = $pdo->prepare($sql); $stmt->execute($p);
                $out = [];
                foreach ($stmt->fetchAll() as $r) { $out[$r['ym']] = (float)$r['total']; }
                return $out;
            };
            echo json_encode(['success'=>true,'data'=>[
                'project' => $project,
                'total_purchases'        => $s("SELECT COALESCE(SUM(total),0) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0",[$pid]),
                'total_contractor_paid'  => $s("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND is_deleted=0",[$pid]),
                'total_contractor_billed'=> $s("SELECT COALESCE(SUM(grand_total),0) FROM app_contractor_bills WHERE project_id=? AND is_deleted=0",[$pid]),
                'total_labor_earned'     => $s("SELECT COALESCE(SUM(earned),0) FROM app_attendance WHERE project_id=? AND is_deleted=0",[$pid]),
                'total_labor_paid'       => $s("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND is_deleted=0",[$pid]),
                'total_client_payments'  => $s("SELECT COALESCE(SUM(amount),0) FROM app_client_payments WHERE project_id=? AND is_deleted=0",[$pid]),
                'total_expenses'         => $s("SELECT COALESCE(SUM(amount),0) FROM app_expenses WHERE project_id=? AND is_deleted=0",[$pid]),
                'purchase_count'         => $s("SELECT COUNT(*) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0",[$pid]),
                'total_qty'              => $s("SELECT COALESCE(SUM(quantity),0) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0",[$pid]),
                'suppliers'              => $s("SELECT COUNT(DISTINCT supplier) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0 AND supplier IS NOT NULL AND supplier!=''",[$pid]),
                'workers_count'          => $s("SELECT COUNT(DISTINCT worker_id) FROM app_attendance WHERE project_id=? AND is_deleted=0",[$pid]),
                'working_days'           => $s("SELECT COALESCE(SUM(attendance_multiplier),0) FROM app_attendance WHERE project_id=? AND is_deleted=0",[$pid]),
                'client_payments_count'  => $s("SELECT COUNT(*) FROM app_client_payments WHERE project_id=? AND is_deleted=0",[$pid]),
                'monthly_purchases'      => $monthSeries("SELECT DATE_FORMAT(purchase_date,'%Y-%m') ym, COALESCE(SUM(total),0) total FROM app_supply_purchases WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]),
                'monthly_advances'       => $monthSeries("SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_contractor_advances WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]),
                'monthly_labor'          => $monthSeries("SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_worker_payments WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]),
                'monthly_client'         => $monthSeries("SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_client_payments WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]),
                'monthly_expenses'       => $monthSeries("SELECT DATE_FORMAT(expense_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_expenses WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]),
            ]]);
            break;

case 'labor_report':
            $pid  = intval($_GET['project_id'] ?? 0);
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $from = $_GET['from'] ?? null;
            $to   = $_GET['to'] ?? null;
            $trade = $_GET['trade'] ?? null;
            $q     = $_GET['q'] ?? null;
            $w = ["a.project_id=? AND a.is_deleted=0"]; $p = [$pid];
            if ($from) { $w[] = "a.work_date>=?"; $p[] = $from; }
            if ($to)   { $w[] = "a.work_date<=?"; $p[] = $to; }
            if ($trade){ $w[] = "w.trade=?"; $p[] = $trade; }
            if ($q)    { $w[] = "w.name LIKE ?"; $p[] = '%'.$q.'%'; }
            $where = implode(' AND ', $w);

            $stmt = $pdo->prepare("SELECT a.worker_id, w.name as worker_name, w.trade, w.phone, w.default_daily_rate,
                    COUNT(a.id) records,
                    COALESCE(SUM(a.attendance_multiplier),0) days,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier>=1 THEN 1 ELSE 0 END),0) present,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier=0.5 THEN 1 ELSE 0 END),0) half,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier=0 THEN 1 ELSE 0 END),0) leaves,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier>=1.5 THEN 1 ELSE 0 END),0) overtime,
                    COALESCE(SUM(a.earned),0) earned
                    FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id
                    WHERE $where
                    GROUP BY a.worker_id, w.name, w.trade, w.phone, w.default_daily_rate
                    ORDER BY earned DESC");
            $stmt->execute($p);
            $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $catStmt = $pdo->prepare("SELECT COALESCE(w.trade,'Other') name, COUNT(DISTINCT a.worker_id) workers, COALESCE(SUM(a.earned),0) earned
                    FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id
                    WHERE $where GROUP BY w.trade ORDER BY earned DESC");
            $catStmt->execute($p);
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

            $kpi = $pdo->prepare("SELECT COUNT(DISTINCT a.worker_id) workers, COUNT(a.id) records, COALESCE(SUM(a.attendance_multiplier),0) days,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier>=1 THEN 1 ELSE 0 END),0) present,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier=0.5 THEN 1 ELSE 0 END),0) half,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier=0 THEN 1 ELSE 0 END),0) leaves,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier>=1.5 THEN 1 ELSE 0 END),0) overtime,
                    COALESCE(SUM(a.earned),0) earned FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE $where");
            $kpi->execute($p);
            $k = $kpi->fetch(PDO::FETCH_ASSOC);

            $pwp = " SELECT worker_id, COALESCE(SUM(amount),0) total FROM app_worker_payments WHERE project_id=? AND is_deleted=0";
            $ppw = [$pid];
            if ($from) { $pwp .= " AND payment_date>=?"; $ppw[] = $from; }
            if ($to)   { $pwp .= " AND payment_date<=?"; $ppw[] = $to; }
            $pwp .= " GROUP BY worker_id";
            $wpStmt = $pdo->prepare($pwp); $wpStmt->execute($ppw);
            $paidMap = [];
            foreach ($wpStmt->fetchAll() as $r) { $paidMap[$r['worker_id']] = (float)$r['total']; }

            $pwp2 = " SELECT COALESCE(w.trade,'Other') trade, COALESCE(SUM(wp.amount),0) total FROM app_worker_payments wp JOIN app_workers w ON w.id=wp.worker_id WHERE wp.project_id=? AND wp.is_deleted=0";
            $ppw2 = [$pid];
            if ($from) { $pwp2 .= " AND wp.payment_date>=?"; $ppw2[] = $from; }
            if ($to)   { $pwp2 .= " AND wp.payment_date<=?"; $ppw2[] = $to; }
            $pwp2 .= " GROUP BY w.trade";
            $catPaidStmt = $pdo->prepare($pwp2); $catPaidStmt->execute($ppw2);
            $catPaidMap = [];
            foreach ($catPaidStmt->fetchAll() as $r) { $catPaidMap[$r['trade']] = (float)$r['total']; }

            foreach ($workers as &$wr) { $wr['paid'] = $paidMap[$wr['worker_id']] ?? 0; $wr['balance'] = (float)$wr['earned'] - $wr['paid']; }
            unset($wr);
            foreach ($categories as &$c) { $c['paid'] = $catPaidMap[$c['name']] ?? 0; $c['balance'] = (float)$c['earned'] - $c['paid']; }
            unset($c);

            $k['paid'] = round(array_sum($paidMap), 2);

            $monStmt = $pdo->prepare("SELECT DATE_FORMAT(a.work_date,'%Y-%m') ym, COALESCE(SUM(a.earned),0) earned, COALESCE(SUM(a.attendance_multiplier),0) days, COUNT(a.id) records FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE $where GROUP BY ym ORDER BY ym");
            $monStmt->execute($p);
            $monthly = $monStmt->fetchAll(PDO::FETCH_ASSOC);

            $tradesOpt = $pdo->prepare("SELECT DISTINCT COALESCE(w.trade,'') trade FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE a.project_id=? AND a.is_deleted=0");
            $tradesOpt->execute([$pid]);
            $trade_options = array_column($tradesOpt->fetchAll(PDO::FETCH_ASSOC), 'trade');

            echo json_encode(['success'=>true,'workers'=>$workers,'categories'=>$categories,'kpis'=>$k,'monthly'=>$monthly,'trade_options'=>$trade_options]);
            break;

        case 'attendance_report':
            $pid  = intval($_GET['project_id'] ?? 0);
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $from = $_GET['from'] ?? null; $to = $_GET['to'] ?? null;
            $worker = intval($_GET['worker_id'] ?? 0);
            $trade = $_GET['trade'] ?? null;
            $status = $_GET['status'] ?? null;
            $w = ["a.project_id=? AND a.is_deleted=0"]; $p = [$pid];
            if ($from)  { $w[] = "a.work_date>=?"; $p[] = $from; }
            if ($to)    { $w[] = "a.work_date<=?"; $p[] = $to; }
            if ($worker){ $w[] = "a.worker_id=?"; $p[] = $worker; }
            if ($trade) { $w[] = "w.trade=?"; $p[] = $trade; }
            if ($status !== null && $status !== '') {
                if ($status === 'present')  $w[] = "a.attendance_multiplier>=1";
                elseif ($status === 'half') $w[] = "a.attendance_multiplier=0.5";
                elseif ($status === 'leave'){ $w[] = "a.attendance_multiplier=0"; }
                elseif ($status === 'overtime') $w[] = "a.attendance_multiplier>=1.5";
            }
            $where = implode(' AND ', $w);

            $kpi = $pdo->prepare("SELECT COUNT(a.id) records, COUNT(DISTINCT a.worker_id) workers, COALESCE(SUM(a.attendance_multiplier),0) days, COALESCE(SUM(a.earned),0) earned,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier>=1 THEN 1 ELSE 0 END),0) present,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier=0.5 THEN 1 ELSE 0 END),0) half,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier=0 THEN 1 ELSE 0 END),0) leaves,
                    COALESCE(SUM(CASE WHEN a.attendance_multiplier>=1.5 THEN 1 ELSE 0 END),0) overtime
                    FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE $where");
            $kpi->execute($p);
            $k = $kpi->fetch(PDO::FETCH_ASSOC);
            $k['rate'] = ($k['records'] > 0) ? round(100 * $k['days'] / $k['records'], 1) : 0;

            $recs = $pdo->prepare("SELECT a.*, w.name worker_name, w.trade, w.phone FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE $where ORDER BY a.work_date DESC, a.id DESC");
            $recs->execute($p);
            $records = $recs->fetchAll(PDO::FETCH_ASSOC);

            $dayStmt = $pdo->prepare("SELECT a.work_date d, COUNT(a.id) records, COALESCE(SUM(a.attendance_multiplier),0) days, COALESCE(SUM(a.earned),0) earned FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE $where GROUP BY a.work_date ORDER BY a.work_date ASC");
            $dayStmt->execute($p);
            $daily = $dayStmt->fetchAll(PDO::FETCH_ASSOC);

            $monStmt = $pdo->prepare("SELECT DATE_FORMAT(a.work_date,'%Y-%m') ym, COUNT(a.id) records, COALESCE(SUM(a.attendance_multiplier),0) days, COALESCE(SUM(a.earned),0) earned FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE $where GROUP BY ym ORDER BY ym");
            $monStmt->execute($p);
            $monthly = $monStmt->fetchAll(PDO::FETCH_ASSOC);

            $wkOpt = $pdo->prepare("SELECT a.worker_id id, w.name name, w.trade FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE a.project_id=? AND a.is_deleted=0 GROUP BY a.worker_id, w.name, w.trade ORDER BY w.name");
            $wkOpt->execute([$pid]);
            $worker_options = $wkOpt->fetchAll(PDO::FETCH_ASSOC);

            $trOpt = $pdo->prepare("SELECT DISTINCT COALESCE(w.trade,'') trade FROM app_attendance a JOIN app_workers w ON w.id=a.worker_id WHERE a.project_id=? AND a.is_deleted=0");
            $trOpt->execute([$pid]);
            $trade_options = array_column($trOpt->fetchAll(PDO::FETCH_ASSOC), 'trade');

            echo json_encode(['success'=>true,'kpis'=>$k,'records'=>$records,'daily'=>$daily,'monthly'=>$monthly,'worker_options'=>$worker_options,'trade_options'=>$trade_options]);
            break;

        case 'payment_report':
            $pid  = intval($_GET['project_id'] ?? 0);
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $m = $pdo->prepare("SELECT id,name,estimated_budget FROM app_projects WHERE id=? AND is_deleted=0"); $m->execute([$pid]); $project = $m->fetch();
            if (!$project) { echo json_encode(['success'=>false,'message'=>'Project not found.']); exit; }

            $cpStmt = $pdo->prepare("SELECT cp.*, u.username created_by_name FROM app_client_payments cp LEFT JOIN app_users u ON u.id=cp.created_by WHERE cp.project_id=? AND cp.is_deleted=0 ORDER BY cp.payment_date DESC, cp.id DESC");
            $cpStmt->execute([$pid]); $client_rows = $cpStmt->fetchAll(PDO::FETCH_ASSOC);

            $advStmt = $pdo->prepare("SELECT ca.*, c.name contractor_name, c.trade FROM app_contractor_advances ca JOIN app_contractors c ON c.id=ca.contractor_id WHERE ca.project_id=? AND ca.is_deleted=0 ORDER BY ca.payment_date DESC, ca.id DESC");
            $advStmt->execute([$pid]); $adv_rows = $advStmt->fetchAll(PDO::FETCH_ASSOC);

            $labStmt = $pdo->prepare("SELECT wp.*, w.name worker_name, w.trade FROM app_worker_payments wp JOIN app_workers w ON w.id=wp.worker_id WHERE wp.project_id=? AND wp.is_deleted=0 ORDER BY wp.payment_date DESC, wp.id DESC");
            $labStmt->execute([$pid]); $lab_rows = $labStmt->fetchAll(PDO::FETCH_ASSOC);

            $expStmt = $pdo->prepare("SELECT e.* FROM app_expenses e WHERE e.project_id=? AND e.is_deleted=0 ORDER BY e.expense_date DESC, e.id DESC");
            $expStmt->execute([$pid]); $exp_rows = $expStmt->fetchAll(PDO::FETCH_ASSOC);

            $received   = array_sum(array_column($client_rows,'amount'));
            $adv_paid   = array_sum(array_column($adv_rows,'amount'));
            $lab_paid   = array_sum(array_column($lab_rows,'amount'));
            $exp_paid   = array_sum(array_column($exp_rows,'amount'));
            $paid_out   = $adv_paid + $lab_paid + $exp_paid;
            $contract   = (float)$project['estimated_budget'];
            $has_budget = $contract > 0;
            $receivable = $has_budget ? max(0, $contract - $received) : null;

            $series = function($sql, $p) use ($pdo) {
                $stmt = $pdo->prepare($sql); $stmt->execute($p); $out = [];
                foreach ($stmt->fetchAll() as $r) { $out[$r['ym']] = (float)$r['total']; }
                return $out;
            };
            $in  = $series("SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_client_payments WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]);
            $out = $series("SELECT ym, SUM(t) total FROM (
                                SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) t FROM app_contractor_advances WHERE project_id=? AND is_deleted=0 GROUP BY ym
                                UNION ALL SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) t FROM app_worker_payments WHERE project_id=? AND is_deleted=0 GROUP BY ym
                                UNION ALL SELECT DATE_FORMAT(expense_date,'%Y-%m') ym, COALESCE(SUM(amount),0) t FROM app_expenses WHERE project_id=? AND is_deleted=0 GROUP BY ym
                            ) z GROUP BY ym", [$pid,$pid,$pid]);

            ksort($in); ksort($out);
            echo json_encode(['success'=>true,
                'project'       => ['id'=>$project['id'],'name'=>$project['name'],'estimated_budget'=>$contract,'has_budget'=>$has_budget],
                'client_payments'=>$client_rows,
                'advances'      => $adv_rows,
                'labor_payments'=> $lab_rows,
                'expenses'      => $exp_rows,
                'totals' => [
                    'received'   => $received,
                    'receivable' => $receivable,
                    'adv_paid'   => $adv_paid,
                    'lab_paid'   => $lab_paid,
                    'exp_paid'   => $exp_paid,
                    'paid_out'   => $paid_out,
                    'net'        => $received - $paid_out,
                    'client_count' => count($client_rows),
                    'adv_count'    => count($adv_rows),
                    'lab_count'    => count($lab_rows),
                    'exp_count'    => count($exp_rows)
                ],
                'monthly_in'  => $in,
                'monthly_out' => $out
            ]);
            break;

        case 'financial_report':
            $pid  = intval($_GET['project_id'] ?? 0);
            if (!$pid) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            $m = $pdo->prepare("SELECT * FROM app_projects WHERE id=? AND is_deleted=0"); $m->execute([$pid]); $project = $m->fetch();
            if (!$project) { echo json_encode(['success'=>false,'message'=>'Project not found.']); exit; }
            $s = function($sql, $p=null) use ($pdo) {
                $stmt = $pdo->prepare($sql); $stmt->execute($p ?? []); return (float)$stmt->fetchColumn();
            };
            $purchases = $s("SELECT COALESCE(SUM(total),0) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0",[$pid]);
            $adv_paid  = $s("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND is_deleted=0",[$pid]);
            $billed    = $s("SELECT COALESCE(SUM(grand_total),0) FROM app_contractor_bills WHERE project_id=? AND is_deleted=0",[$pid]);
            $labor_earned = $s("SELECT COALESCE(SUM(earned),0) FROM app_attendance WHERE project_id=? AND is_deleted=0",[$pid]);
            $labor_paid   = $s("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND is_deleted=0",[$pid]);
            $expenses  = $s("SELECT COALESCE(SUM(amount),0) FROM app_expenses WHERE project_id=? AND is_deleted=0",[$pid]);
            $received  = $s("SELECT COALESCE(SUM(amount),0) FROM app_client_payments WHERE project_id=? AND is_deleted=0",[$pid]);
            $contract  = (float)$project['estimated_budget'];
            $has_budget = $contract > 0;

            $total_cost      = $purchases + $adv_paid + $labor_paid + $expenses;
            $total_committed = $purchases + $billed + $labor_earned + $expenses;
            $receivable      = $has_budget ? max(0, $contract - $received) : null;
            $profit          = $received - $total_cost;
            $margin          = $received > 0 ? round(100 * $profit / $received, 1) : null;
            $cost_pct_of_budget = $has_budget && $contract > 0 ? round(100 * $total_cost / $contract, 1) : null;

            if (!$has_budget) {
                $health = ['status'=>'no_budget','label'=>'No Contract Value Set','detail'=>'Set an estimated budget on the project to compare spend.','tone'=>'muted'];
            } elseif ($total_cost > $contract) {
                $health = ['status'=>'over_budget','label'=>'Over Budget','detail'=>'Project spend has exceeded the contract value.','tone'=>'danger'];
            } elseif ($cost_pct_of_budget >= 80) {
                $health = ['status'=>'attention','label'=>'Needs Attention','detail'=>'Spend has reached '.$cost_pct_of_budget.'% of the contract value.','tone'=>'warn'];
            } else {
                $health = ['status'=>'healthy','label'=>'On Track','detail'=>'Spend is '.$cost_pct_of_budget.'% of the contract value.','tone'=>'success'];
            }

            $series = function($sql, $p) use ($pdo) {
                $stmt = $pdo->prepare($sql); $stmt->execute($p); $out = [];
                foreach ($stmt->fetchAll() as $r) { $out[$r['ym']] = (float)$r['total']; }
                return $out;
            };
            $monthly_in  = $series("SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_client_payments WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]);
            $monthly_pur = $series("SELECT DATE_FORMAT(purchase_date,'%Y-%m') ym, COALESCE(SUM(total),0) total FROM app_supply_purchases WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]);
            $monthly_adv = $series("SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_contractor_advances WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]);
            $monthly_lab = $series("SELECT DATE_FORMAT(payment_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_worker_payments WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]);
            $monthly_exp = $series("SELECT DATE_FORMAT(expense_date,'%Y-%m') ym, COALESCE(SUM(amount),0) total FROM app_expenses WHERE project_id=? AND is_deleted=0 GROUP BY ym", [$pid]);
            $months = array_unique(array_merge(array_keys($monthly_in), array_keys($monthly_pur), array_keys($monthly_adv), array_keys($monthly_lab), array_keys($monthly_exp)));
            sort($months);

            echo json_encode(['success'=>true,
                'project' => $project,
                'revenue' => [
                    'contract'  => $contract,
                    'has_budget'=> $has_budget,
                    'received'  => $received,
                    'receivable'=> $receivable
                ],
                'costs' => [
                    'purchases'        => $purchases,
                    'contractor_paid'  => $adv_paid,
                    'contractor_billed'=> $billed,
                    'labor_earned'     => $labor_earned,
                    'labor_paid'       => $labor_paid,
                    'expenses'         => $expenses,
                    'total_cost'       => $total_cost,
                    'total_committed'  => $total_committed
                ],
                'profit' => ['net'=>$profit,'margin'=>$margin],
                'health' => $health,
                'budget_pct' => $cost_pct_of_budget,
                'months' => $months,
                'monthly' => [
                    'in'=> $monthly_in,
                    'purchases'=>$monthly_pur,
                    'advances'=>$monthly_adv,
                    'labor'=>$monthly_lab,
                    'expenses'=>$monthly_exp
                ]
            ]);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}