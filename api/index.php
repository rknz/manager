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
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, photo, is_active FROM app_users WHERE username = ? OR email = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_active'] == 0) { echo json_encode(['success' => false, 'message' => 'Account is inactive.']); exit; }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['photo'] = $user['photo'] ?? null;
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
        $stmtRP = $pdo->query("SELECT id, name, client_name, client_address, status, estimated_budget, project_image, start_date, end_date, created_at FROM app_projects WHERE is_deleted=0 ORDER BY id DESC LIMIT 4");
        $recent_projects = $stmtRP->fetchAll(PDO::FETCH_ASSOC);

        // Add spend and timeline progress per project
        $todayTs = strtotime(date('Y-m-d'));
        foreach ($recent_projects as &$p) {
            $pid = $p['id'];
            $sMat = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0");
            $sMat->execute([$pid]); $matSpent = (float)$sMat->fetchColumn();
            
            $sAdv = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND is_deleted=0");
            $sAdv->execute([$pid]); $advSpent = (float)$sAdv->fetchColumn();
            
            $sLab = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND is_deleted=0");
            $sLab->execute([$pid]); $labSpent = (float)$sLab->fetchColumn();

            $spent = $matSpent + $advSpent + $labSpent;
            $p['spent'] = $spent;

            // Date-to-date timeline progress
            $pct = 0;
            if (strtolower($p['status'] ?? '') === 'completed') {
                $pct = 100;
            } else {
                $startStr = !empty($p['start_date']) ? substr($p['start_date'], 0, 10) : (!empty($p['created_at']) ? substr($p['created_at'], 0, 10) : null);
                $endStr   = !empty($p['end_date']) ? substr($p['end_date'], 0, 10) : null;
                if ($startStr && $endStr) {
                    $startTs = strtotime($startStr);
                    $endTs   = strtotime($endStr);
                    if ($startTs && $endTs) {
                        if ($endTs <= $startTs) {
                            $pct = $todayTs >= $endTs ? 100 : 0;
                        } elseif ($todayTs <= $startTs) {
                            $pct = 0;
                        } elseif ($todayTs >= $endTs) {
                            $pct = 100;
                        } else {
                            $pct = (int)round((($todayTs - $startTs) / ($endTs - $startTs)) * 100);
                            $pct = max(0, min(100, $pct));
                        }
                    }
                }
            }
            $p['progress'] = $pct;
        }
        unset($p);

        // Recent transactions (last 10: purchases + payments + labor)
        $txnSQL = "
        (SELECT 'purchase' as type, id, item_name as title, project_id,
                total as amount, purchase_date as tx_date, created_at
         FROM app_supply_purchases WHERE is_deleted=0)
        UNION ALL
        (SELECT 'contractor_payment' as type, ca.id,
                COALESCE(NULLIF(c.name, ''), NULLIF(ca.who_received, ''), 'Contractor Payment') as title,
                ca.project_id, ca.amount, ca.payment_date as tx_date, ca.created_at
         FROM app_contractor_advances ca
         LEFT JOIN app_contractors c ON ca.contractor_id = c.id
         WHERE ca.is_deleted=0)
        UNION ALL
        (SELECT 'labor_payment' as type, wp.id,
                COALESCE(NULLIF(w.name, ''), NULLIF(wp.who_received, ''), 'Labor Payment') as title,
                wp.project_id, wp.amount, wp.payment_date as tx_date, wp.created_at
         FROM app_worker_payments wp
         LEFT JOIN app_workers w ON wp.worker_id = w.id
         WHERE wp.is_deleted=0)
        UNION ALL
        (SELECT 'client_payment' as type, id, CONCAT('Client Payment') as title, project_id,
                amount, payment_date as tx_date, created_at
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

// --- ALL RECENT ACTIVITY (Today & Past Days) ---
if ($action === 'get_all_recent_activity') {
    requireLogin();
    $days = intval($_GET['days'] ?? 30);
    if ($days <= 0 || $days > 90) $days = 30;
    
    $sinceDate = date('Y-m-d', strtotime("-$days days"));
    
    $allTxnSQL = "
    (SELECT 'purchase' as type, id, item_name as title, project_id,
            total as amount, purchase_date as tx_date, created_at
     FROM app_supply_purchases WHERE is_deleted=0 AND purchase_date >= :since1)
    UNION ALL
    (SELECT 'contractor_payment' as type, ca.id,
            COALESCE(NULLIF(c.name, ''), NULLIF(ca.who_received, ''), 'Contractor Payment') as title,
            ca.project_id, ca.amount, ca.payment_date as tx_date, ca.created_at
     FROM app_contractor_advances ca
     LEFT JOIN app_contractors c ON ca.contractor_id = c.id
     WHERE ca.is_deleted=0 AND ca.payment_date >= :since2)
    UNION ALL
    (SELECT 'labor_payment' as type, wp.id,
            COALESCE(NULLIF(w.name, ''), NULLIF(wp.who_received, ''), 'Labor Payment') as title,
            wp.project_id, wp.amount, wp.payment_date as tx_date, wp.created_at
     FROM app_worker_payments wp
     LEFT JOIN app_workers w ON wp.worker_id = w.id
     WHERE wp.is_deleted=0 AND wp.payment_date >= :since3)
    UNION ALL
    (SELECT 'client_payment' as type, id, CONCAT('Client Payment') as title,
            project_id, amount, payment_date as tx_date, created_at
     FROM app_client_payments WHERE is_deleted=0 AND payment_date >= :since4)
    ORDER BY tx_date DESC, created_at DESC LIMIT 200";
    
    $stmt = $pdo->prepare($allTxnSQL);
    $stmt->execute([
        ':since1' => $sinceDate,
        ':since2' => $sinceDate,
        ':since3' => $sinceDate,
        ':since4' => $sinceDate,
    ]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If few activities in date window, load most recent 60 across all time
    if (count($activities) < 10) {
        $fallbackSQL = "
        (SELECT 'purchase' as type, id, item_name as title, project_id,
                total as amount, purchase_date as tx_date, created_at
         FROM app_supply_purchases WHERE is_deleted=0)
        UNION ALL
        (SELECT 'contractor_payment' as type, ca.id,
                COALESCE(NULLIF(c.name, ''), NULLIF(ca.who_received, ''), 'Contractor Payment') as title,
                ca.project_id, ca.amount, ca.payment_date as tx_date, ca.created_at
         FROM app_contractor_advances ca
         LEFT JOIN app_contractors c ON ca.contractor_id = c.id
         WHERE ca.is_deleted=0)
        UNION ALL
        (SELECT 'labor_payment' as type, wp.id,
                COALESCE(NULLIF(w.name, ''), NULLIF(wp.who_received, ''), 'Labor Payment') as title,
                wp.project_id, wp.amount, wp.payment_date as tx_date, wp.created_at
         FROM app_worker_payments wp
         LEFT JOIN app_workers w ON wp.worker_id = w.id
         WHERE wp.is_deleted=0)
        UNION ALL
        (SELECT 'client_payment' as type, id, CONCAT('Client Payment') as title, project_id,
                amount, payment_date as tx_date, created_at
         FROM app_client_payments WHERE is_deleted=0)
        ORDER BY tx_date DESC, created_at DESC LIMIT 60";
        $activities = $pdo->query($fallbackSQL)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Attach project names
    $projCache = [];
    foreach ($activities as &$tx) {
        $pid = $tx['project_id'];
        if ($pid && !isset($projCache[$pid])) {
            $pStmt = $pdo->prepare("SELECT name FROM app_projects WHERE id=?");
            $pStmt->execute([$pid]);
            $projCache[$pid] = $pStmt->fetchColumn() ?: '';
        }
        $tx['project_name'] = $projCache[$pid] ?? '';
    }
    unset($tx);
    
    echo json_encode(['success' => true, 'data' => $activities, 'since' => $sinceDate]);
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

/**
 * Compress user photo/avatar to a lightweight size (~20-40KB)
 * Scales down to maxDim (e.g. 320px) and compresses with 80% quality.
 */
function compressAndSaveUserPhoto($tmpPath, $targetPath, $maxDim = 320, $quality = 80) {
    $dir = dirname($targetPath);
    if (!is_dir($dir)) @mkdir($dir, 0777, true);

    if (!extension_loaded('gd') || !file_exists($tmpPath)) {
        return move_uploaded_file($tmpPath, $targetPath) || copy($tmpPath, $targetPath);
    }

    $imgInfo = @getimagesize($tmpPath);
    if (!$imgInfo) {
        return move_uploaded_file($tmpPath, $targetPath) || copy($tmpPath, $targetPath);
    }

    $mime = $imgInfo['mime'] ?? '';
    $srcImg = null;
    switch ($mime) {
        case 'image/jpeg':
            $srcImg = @imagecreatefromjpeg($tmpPath);
            if ($srcImg && function_exists('exif_read_data')) {
                $exif = @exif_read_data($tmpPath);
                if ($exif && !empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3: $srcImg = imagerotate($srcImg, 180, 0); break;
                        case 6: $srcImg = imagerotate($srcImg, -90, 0); break;
                        case 8: $srcImg = imagerotate($srcImg, 90, 0); break;
                    }
                }
            }
            break;
        case 'image/png':
            $srcImg = @imagecreatefrompng($tmpPath);
            break;
        case 'image/webp':
            $srcImg = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : null;
            break;
        case 'image/gif':
            $srcImg = @imagecreatefromgif($tmpPath);
            break;
    }

    if (!$srcImg) {
        return move_uploaded_file($tmpPath, $targetPath) || copy($tmpPath, $targetPath);
    }

    $origW = imagesx($srcImg);
    $origH = imagesy($srcImg);

    $scale = min(1.0, (float)$maxDim / (float)max($origW, $origH));
    $dstW = max(1, (int)round($origW * $scale));
    $dstH = max(1, (int)round($origH * $scale));

    $dstImg = imagecreatetruecolor($dstW, $dstH);

    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($dstImg, false);
        imagesavealpha($dstImg, true);
        $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
        imagefilledrectangle($dstImg, 0, 0, $dstW, $dstH, $transparent);
    } else {
        $bg = imagecolorallocate($dstImg, 255, 255, 255);
        imagefilledrectangle($dstImg, 0, 0, $dstW, $dstH, $bg);
    }

    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $origW, $origH);
    imagedestroy($srcImg);

    $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    $success = false;

    if ($ext === 'png') {
        $success = imagepng($dstImg, $targetPath, 7);
    } elseif ($ext === 'webp' && function_exists('imagewebp')) {
        $success = imagewebp($dstImg, $targetPath, $quality);
    } else {
        $success = imagejpeg($dstImg, $targetPath, $quality);
    }

    imagedestroy($dstImg);
    return $success;
}

if ($action === 'create_user') {
    requireLogin();

    // Strict Owner check
    $currentRole = strtolower($_SESSION['role'] ?? '');
    if ($currentRole !== 'owner' && $currentRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Only users with the Owner role can create new users.']);
        exit;
    }

    // Owner password verification
    $ownerPass = $_POST['owner_password'] ?? '';
    if (empty($ownerPass)) {
        echo json_encode(['success' => false, 'message' => 'Owner password confirmation is required.']);
        exit;
    }
    $stmtOwner = $pdo->prepare("SELECT password_hash FROM app_users WHERE id = ?");
    $stmtOwner->execute([$_SESSION['user_id']]);
    $ownerHash = $stmtOwner->fetchColumn();
    if (!$ownerHash || !password_verify($ownerPass, $ownerHash)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Owner password confirmation.']);
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? ($username . '@lilyinteriorsbd.com'));
    $password = $_POST['password'] ?? '';
    $rawRole  = trim($_POST['role'] ?? 'manager');
    if ($rawRole === 'admin') $role = 'owner';
    elseif ($rawRole === 'user') $role = 'manager';
    elseif (in_array($rawRole, ['owner', 'manager'])) $role = $rawRole;
    else $role = 'manager';
    if (!$username || !$password) { echo json_encode(['success'=>false,'message'=>'Username and password required.']); exit; }

    // Auto-clean any legacy soft-deleted record with this username or email to avoid unique key conflict
    $pdo->prepare("DELETE FROM app_users WHERE (username=? OR email=?) AND is_deleted=1")->execute([$username, $email]);

    $dup = $pdo->prepare("SELECT id FROM app_users WHERE username=? OR email=?"); $dup->execute([$username, $email]);
    if ($dup->fetch()) { echo json_encode(['success'=>false,'message'=>'Username or email already exists.']); exit; }
    
    // Photo upload handling with compression
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        $mime_map = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp', 'image/gif'=>'gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (isset($mime_map[$mime])) {
            $dir = __DIR__ . '/../uploads/users/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $ext = $mime_map[$mime];
            $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target = $dir . $filename;
            if (compressAndSaveUserPhoto($file['tmp_name'], $target, 320, 80)) {
                $photoPath = 'uploads/users/' . $filename;
            }
        }
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO app_users (username,email,password_hash,role,photo,is_active,is_deleted,created_at) VALUES (?,?,?,?,?,1,0,NOW())")->execute([$username,$email,$hash,$role,$photoPath]);
    echo json_encode(['success'=>true,'message'=>'User created successfully.']); exit;
}
if ($action === 'delete_user') {
    requireLogin();
    
    // Strict Owner check
    $currentRole = strtolower($_SESSION['role'] ?? '');
    if ($currentRole !== 'owner' && $currentRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Only users with the Owner role can remove users.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $ownerPass = $data['owner_password'] ?? $_POST['owner_password'] ?? '';
    
    // Verify Owner password directly or recent admin_auth_time within 120s
    $isOwnerVerified = false;
    if (!empty($ownerPass)) {
        $stmtOwner = $pdo->prepare("SELECT password_hash FROM app_users WHERE id = ?");
        $stmtOwner->execute([$_SESSION['user_id']]);
        $ownerHash = $stmtOwner->fetchColumn();
        if ($ownerHash && password_verify($ownerPass, $ownerHash)) {
            $isOwnerVerified = true;
        }
    } elseif (isset($_SESSION['admin_auth_time']) && (time() - $_SESSION['admin_auth_time'] <= 120)) {
        $isOwnerVerified = true;
    }

    if (!$isOwnerVerified) {
        echo json_encode(['success' => false, 'message' => 'Owner password confirmation is required.']);
        exit;
    }

    $id = intval($data['id'] ?? $_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'User ID required.']);
        exit;
    }
    if ($id == ($_SESSION['user_id'] ?? 0)) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id, username, photo FROM app_users WHERE id = ?");
    $stmt->execute([$id]);
    $userToDelete = $stmt->fetch();
    if (!$userToDelete) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }
    if (!empty($userToDelete['photo'])) {
        $photoFile = __DIR__ . '/../' . ltrim($userToDelete['photo'], '/');
        if (file_exists($photoFile)) {
            @unlink($photoFile);
        }
    }
    $pdo->prepare("DELETE FROM app_users WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'User "' . $userToDelete['username'] . '" deleted successfully.']);
    exit;
}
if ($action === 'toggle_user') {
    requireLogin();
    $currentRole = strtolower($_SESSION['role'] ?? '');
    if ($currentRole !== 'owner' && $currentRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Only users with the Owner role can change user status.']);
        exit;
    }
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
if ($action === 'upload_avatar') {
    requireLogin();
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error.']);
        exit;
    }
    $file = $_FILES['photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, or WEBP images allowed.']);
        exit;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!$ext) $ext = 'jpg';
    $dir = __DIR__ . '/../uploads/users';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $dir . '/' . $filename;
    if (compressAndSaveUserPhoto($file['tmp_name'], $target, 320, 80)) {
        $photoPath = 'uploads/users/' . $filename;
        $userId = $_SESSION['user_id'] ?? 0;
        if ($userId) {
            $pdo->prepare("UPDATE app_users SET photo = ?, updated_at = NOW() WHERE id = ?")->execute([$photoPath, $userId]);
            $_SESSION['photo'] = $photoPath;
            echo json_encode(['success' => true, 'photo' => $photoPath, 'message' => 'Avatar updated successfully.']);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Failed to save avatar image.']);
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'API route not found.']);
