<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// --- LOGIN ---
if ($action === 'login' && $method === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($usernameOrEmail) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']); exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM app_users WHERE username = ? OR email = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_active'] == 0) { echo json_encode(['success' => false, 'message' => 'Account is inactive.']); exit; }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $bp = dirname($_SERVER['SCRIPT_NAME'], 2);
            if ($bp === '\\' || $bp === '/' || $bp === '.') $bp = '';
            echo json_encode(['success' => true, 'redirect' => $bp . '/dashboard']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// --- LOGOUT ---
if ($action === 'logout') {
    session_unset(); session_destroy();
    $bp = dirname($_SERVER['SCRIPT_NAME'], 2);
    if ($bp === '\\' || $bp === '/' || $bp === '.') $bp = '';
    echo json_encode(['success' => true, 'redirect' => $bp . '/login']); exit;
}

// --- AUTH GUARD FOR PROTECTED ACTIONS ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

// --- VERIFY PASSWORD ---
if ($action === 'verify_password' && $method === 'POST') {
    $password = $_POST['password'] ?? '';
    if (empty($password)) { echo json_encode(['success' => false, 'message' => 'Password required.']); exit; }
    try {
        $stmt = $pdo->query("SELECT password_hash FROM app_users WHERE role IN ('admin', 'owner') AND is_active = 1");
        $admins = $stmt->fetchAll();
        $verified = false;
        foreach ($admins as $admin) {
            if (password_verify($password, $admin['password_hash'])) {
                $verified = true;
                break;
            }
        }
        
        // Also check if current user matches (in case current user is admin, they are caught above)
        if (!$verified && isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT password_hash FROM app_users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                $verified = true;
            }
        }

        if ($verified) {
            $_SESSION['admin_auth_time'] = time();
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// --- GLOBAL SEARCH ---
if ($action === 'global_search' && $method === 'GET') {
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    if (strlen($q) <= 2) { echo json_encode(['success' => true, 'data' => []]); exit; }
    try {
        $results = [];
        // Projects
        $s = $pdo->prepare("SELECT id, name, client_name, status FROM app_projects WHERE is_deleted=0 AND (name LIKE ? OR client_name LIKE ?) LIMIT 5");
        $s->execute([$q, $q]);
        foreach ($s->fetchAll() as $r) $results[] = ['type'=>'project','id'=>$r['id'],'label'=>$r['name'],'sub'=>$r['client_name'],'status'=>$r['status']];
        // Contractors
        $s = $pdo->prepare("SELECT id, name, trade FROM app_contractors WHERE is_active=1 AND (name LIKE ? OR phone LIKE ?) LIMIT 5");
        $s->execute([$q, $q]);
        foreach ($s->fetchAll() as $r) $results[] = ['type'=>'contractor','id'=>$r['id'],'label'=>$r['name'],'sub'=>$r['trade']];
        // Purchases
        $s = $pdo->prepare("SELECT sp.id, sp.item_name, p.name as project_name FROM app_supply_purchases sp JOIN app_projects p ON p.id=sp.project_id WHERE sp.is_deleted=0 AND sp.item_name LIKE ? LIMIT 5");
        $s->execute([$q]);
        foreach ($s->fetchAll() as $r) $results[] = ['type'=>'purchase','id'=>$r['id'],'label'=>$r['item_name'],'sub'=>$r['project_name']];
        echo json_encode(['success' => true, 'data' => $results]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// --- DASHBOARD STATS ---
if ($action === 'get_dashboard_stats' && $method === 'GET') {
    try {
        $now   = date('Y-m-d');
        $month = date('Y-m');
        $prevMonth = date('Y-m', strtotime('-1 month'));

        // Project counts
        $total_projects = (int)$pdo->query("SELECT COUNT(*) FROM app_projects WHERE is_deleted=0")->fetchColumn();
        $ongoing   = (int)$pdo->query("SELECT COUNT(*) FROM app_projects WHERE is_deleted=0 AND status='Ongoing'")->fetchColumn();
        $completed = (int)$pdo->query("SELECT COUNT(*) FROM app_projects WHERE is_deleted=0 AND status='Completed'")->fetchColumn();
        $onhold    = (int)$pdo->query("SELECT COUNT(*) FROM app_projects WHERE is_deleted=0 AND status='On Hold'")->fetchColumn();

        // Expenses this month vs last month
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM app_supply_purchases WHERE is_deleted=0 AND DATE_FORMAT(purchase_date,'%Y-%m')=?");
        $stmt->execute([$month]); $mat_cur = (float)$stmt->fetchColumn();
        $stmt->execute([$prevMonth]); $mat_prev = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE is_deleted=0 AND DATE_FORMAT(payment_date,'%Y-%m')=?");
        $stmt->execute([$month]); $adv_cur = (float)$stmt->fetchColumn();
        $stmt->execute([$prevMonth]); $adv_prev = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE is_deleted=0 AND DATE_FORMAT(payment_date,'%Y-%m')=?");
        $stmt->execute([$month]); $lab_cur = (float)$stmt->fetchColumn();
        $stmt->execute([$prevMonth]); $lab_prev = (float)$stmt->fetchColumn();

        $exp_cur  = $mat_cur + $adv_cur + $lab_cur;
        $exp_prev = $mat_prev + $adv_prev + $lab_prev;
        $exp_growth = $exp_prev > 0 ? round((($exp_cur - $exp_prev) / $exp_prev) * 100, 1) : 0;

        // Client payments this month vs last month
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_client_payments WHERE is_deleted=0 AND DATE_FORMAT(payment_date,'%Y-%m')=?");
        $stmt->execute([$month]); $pay_cur = (float)$stmt->fetchColumn();
        $stmt->execute([$prevMonth]); $pay_prev = (float)$stmt->fetchColumn();
        $pay_growth = $pay_prev > 0 ? round((($pay_cur - $pay_prev) / $pay_prev) * 100, 1) : 0;

        // Total lifetime client payments
        $total_client_payments = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM app_client_payments WHERE is_deleted=0")->fetchColumn();

        // Contractors count
        $total_contractors  = (int)$pdo->query("SELECT COUNT(*) FROM app_contractors")->fetchColumn();
        $active_contractors = (int)$pdo->query("SELECT COUNT(*) FROM app_contractors WHERE is_active=1")->fetchColumn();

        // Daily labor today
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT worker_id) FROM app_attendance WHERE work_date=? AND is_deleted=0");
        $stmt->execute([$now]);
        $labor_today = (int)$stmt->fetchColumn();
        $total_workers = (int)$pdo->query("SELECT COUNT(*) FROM app_workers WHERE is_active=1")->fetchColumn();

        // Total contractor due
        $total_billed   = (float)$pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM app_contractor_bills WHERE is_deleted=0")->fetchColumn();
        $total_advances = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE is_deleted=0")->fetchColumn();
        $total_due = max(0, $total_billed - $total_advances);

        // Recent projects (4, with image)
        $stmtRP = $pdo->query("SELECT id, name, client_name, client_address, status, estimated_budget, project_image, start_date FROM app_projects WHERE is_deleted=0 ORDER BY id DESC LIMIT 4");
        $recent_projects = $stmtRP->fetchAll(PDO::FETCH_ASSOC);

        // Add spend % per project
        foreach ($recent_projects as &$p) {
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

        // Recent transactions (last 10: purchases + payments + labor)
        $txnSQL = "
        (SELECT 'purchase' as type, id, item_name as title, project_id,
                total as amount, purchase_date as tx_date, created_at
         FROM app_supply_purchases WHERE is_deleted=0)
        UNION ALL
        (SELECT 'contractor_payment', id, CONCAT('Payment - Advance') as title, project_id,
                amount, payment_date, created_at
         FROM app_contractor_advances WHERE is_deleted=0)
        UNION ALL
        (SELECT 'labor_payment', id, CONCAT('Labor Payment') as title, project_id,
                amount, payment_date, created_at
         FROM app_worker_payments WHERE is_deleted=0)
        UNION ALL
        (SELECT 'client_payment', id, CONCAT('Client Payment') as title, project_id,
                amount, payment_date, created_at
         FROM app_client_payments WHERE is_deleted=0)
        ORDER BY tx_date DESC, created_at DESC LIMIT 10";
        $recent_txns = $pdo->query($txnSQL)->fetchAll(PDO::FETCH_ASSOC);

        // Add project name to transactions
        $projCache = [];
        foreach ($recent_txns as &$tx) {
            $pid = $tx['project_id'];
            if (!isset($projCache[$pid])) {
                $ps = $pdo->prepare("SELECT name FROM app_projects WHERE id=?");
                $ps->execute([$pid]);
                $projCache[$pid] = $ps->fetchColumn() ?: 'Unknown Project';
            }
            $tx['project_name'] = $projCache[$pid];
        }
        unset($tx);

        // Today's schedules
        $stmt = $pdo->prepare("SELECT s.*, p.name as project_name FROM app_schedules s LEFT JOIN app_projects p ON p.id=s.project_id WHERE s.schedule_date=? AND s.is_done=0 ORDER BY s.id ASC");
        $stmt->execute([$now]);
        $today_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Quick summary today
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM app_supply_purchases WHERE is_deleted=0 AND purchase_date=?");
        $stmt->execute([$now]); $today_expenses = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_client_payments WHERE is_deleted=0 AND payment_date=?");
        $stmt->execute([$now]); $today_payments = (float)$stmt->fetchColumn();

        echo json_encode(['success' => true, 'data' => [
            'total_projects'       => $total_projects,
            'ongoing'              => $ongoing,
            'completed'            => $completed,
            'onhold'               => $onhold,
            'total_expenses_month' => $exp_cur,
            'expenses_growth'      => $exp_growth,
            'total_payments_month' => $pay_cur,
            'payments_growth'      => $pay_growth,
            'total_client_payments'=> $total_client_payments,
            'total_contractors'    => $total_contractors,
            'active_contractors'   => $active_contractors,
            'daily_labor_today'    => $labor_today,
            'total_workers'        => $total_workers,
            'total_due'            => $total_due,
            'recent_projects'      => $recent_projects,
            'recent_transactions'  => $recent_txns,
            'today_schedules'      => $today_schedules,
            'quick_summary'        => [
                'today_expenses' => $today_expenses,
                'today_payments' => $today_payments,
                'labor_present'  => $labor_today,
                'labor_total'    => $total_workers,
            ]
        ]]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
    exit;
}

// --- SETTINGS ACTIONS ---
if ($action === 'save_settings') {
    requireLogin();
    $allowed = ['company_name','company_phone','company_address','currency_symbol','session_timeout'];
    foreach ($allowed as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?");
            $stmt->execute([$key,$val,$val]);
        }
    }
    echo json_encode(['success'=>true,'message'=>'Settings saved.']); exit;
}
if ($action === 'create_user') {
    requireLogin();
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? ($username . '@lilyinteriorsbd.com'));
    $password = $_POST['password'] ?? '';
    $role     = in_array($_POST['role']??'user',['admin','user','owner','manager']) ? $_POST['role'] : 'user';
    if (!$username || !$password) { echo json_encode(['success'=>false,'message'=>'Username and password required.']); exit; }
    $dup = $pdo->prepare("SELECT id FROM app_users WHERE username=? OR email=?"); $dup->execute([$username, $email]);
    if ($dup->fetch()) { echo json_encode(['success'=>false,'message'=>'Username or email already exists.']); exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO app_users (username,email,password_hash,role,is_active,created_at) VALUES (?,?,?,?,1,NOW())")->execute([$username,$email,$hash,$role]);
    echo json_encode(['success'=>true,'message'=>'User created.']); exit;
}
if ($action === 'toggle_user') {
    requireLogin();
    $data = json_decode(file_get_contents('php://input'),true) ?? [];
    $id = intval($data['id'] ?? $_POST['id'] ?? 0);
    $active = intval($data['is_active'] ?? $_POST['is_active'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
    if ($id == ($_SESSION['user_id']??0)) { echo json_encode(['success'=>false,'message'=>'Cannot deactivate yourself.']); exit; }
    $pdo->prepare("UPDATE app_users SET is_active=? WHERE id=?")->execute([$active,$id]);
    echo json_encode(['success'=>true,'message'=>'User updated.']); exit;
}
if ($action === 'create_category') {
    requireLogin();
    $name  = trim($_POST['name'] ?? '');
    $rawType = trim($_POST['type'] ?? 'purchase');
    $billingType = ($rawType === 'work' || $rawType === 'attendance') ? 'attendance' : 'purchase_contractor';
    $order = intval($_POST['sort_order'] ?? 0);
    if (!$name) { echo json_encode(['success'=>false,'message'=>'Name required.']); exit; }
    $pdo->prepare("INSERT INTO app_categories (name,billing_type,sort_order,is_active,created_at) VALUES (?,?,?,1,NOW())")->execute([$name,$billingType,$order]);
    echo json_encode(['success'=>true,'message'=>'Category created.','id'=>$pdo->lastInsertId()]); exit;
}
if ($action === 'delete_category') {
    requireLogin();
    $data = json_decode(file_get_contents('php://input'),true) ?? [];
    $id   = intval($data['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
    try {
        $pdo->prepare("UPDATE app_categories SET is_active=0 WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true,'message'=>'Category deactivated.']); exit;
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Cannot delete category: ' . $e->getMessage()]); exit;
    }
}
if ($action === 'change_password') {
    requireLogin();
    $cur = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    if (!$cur || !$new || strlen($new) < 6) { echo json_encode(['success'=>false,'message'=>'New password must be at least 6 characters.']); exit; }
    $stmt = $pdo->prepare("SELECT password_hash FROM app_users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]); $user = $stmt->fetch();
    if (!$user || !password_verify($cur, $user['password_hash'])) { echo json_encode(['success'=>false,'message'=>'Current password is incorrect.']); exit; }
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE app_users SET password_hash=? WHERE id=?")->execute([$hash,$_SESSION['user_id']]);
    echo json_encode(['success'=>true,'message'=>'Password changed successfully.']); exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'API route not found.']);
