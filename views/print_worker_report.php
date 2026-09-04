<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if (!isset($basePath)) {
    $bp = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = ($bp === '\\' || $bp === '/') ? '' : $bp;
}

$project_id = intval($_GET['project_id'] ?? 0);
$worker_id = intval($_GET['worker_id'] ?? 0);
$type = $_GET['type'] ?? 'statement'; // statement | attendance | payments
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

if (!$worker_id) { die("Worker ID required"); }

// Fetch Worker & Contractor info
$stmt = $pdo->prepare("SELECT w.*, c.name AS contractor_name, c.trade AS contractor_trade, c.phone AS contractor_phone 
                       FROM app_workers w 
                       LEFT JOIN app_contractors c ON w.contractor_id=c.id 
                       WHERE w.id=?");
$stmt->execute([$worker_id]);
$worker = $stmt->fetch();
if (!$worker) { die("Worker not found"); }

// Fetch Project (if specific)
$project = null;
if ($project_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM app_projects WHERE id=?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
}

$attendanceData = [];
$paymentData = [];
$totalEarned = 0;
$totalHajira = 0;
$totalPaid = 0;

if ($type === 'attendance' || $type === 'statement') {
    $sql = "SELECT a.*, p.name AS project_name 
            FROM app_attendance a 
            LEFT JOIN app_projects p ON a.project_id=p.id 
            WHERE a.worker_id=? AND a.is_deleted=0";
    $params = [$worker_id];
    if ($project_id > 0) { $sql .= " AND a.project_id=?"; $params[] = $project_id; }
    if ($from) { $sql .= " AND a.work_date >= ?"; $params[] = $from; }
    if ($to) { $sql .= " AND a.work_date <= ?"; $params[] = $to; }
    $sql .= " ORDER BY a.work_date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $attendanceData = $stmt->fetchAll();
    foreach ($attendanceData as $row) {
        $totalEarned += floatval($row['earned']);
        $totalHajira += floatval($row['attendance_multiplier']);
    }
}

if ($type === 'payments' || $type === 'statement') {
    $sql = "SELECT wp.*, p.name AS project_name 
            FROM app_worker_payments wp 
            LEFT JOIN app_projects p ON wp.project_id=p.id 
            WHERE wp.worker_id=? AND wp.is_deleted=0";
    $params = [$worker_id];
    if ($project_id > 0) { $sql .= " AND wp.project_id=?"; $params[] = $project_id; }
    if ($from) { $sql .= " AND wp.payment_date >= ?"; $params[] = $from; }
    if ($to) { $sql .= " AND wp.payment_date <= ?"; $params[] = $to; }
    $sql .= " ORDER BY wp.payment_date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $paymentData = $stmt->fetchAll();
    foreach ($paymentData as $row) {
        $totalPaid += floatval($row['amount']);
    }
}

$balanceDue = max(0, $totalEarned - $totalPaid);
$titleMap = [
    'statement' => 'Worker Account Statement',
    'attendance' => 'Worker Attendance Report',
    'payments' => 'Worker Payment Report'
];
$docTitle = $titleMap[$type] ?? 'Worker Statement';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($docTitle) ?> - <?= htmlspecialchars($worker['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@100..900&family=Hind+Siliguri:wght@400;600;700&family=Potta+One&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Noto Sans Bengali';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url('<?= $basePath ?>/assets/fonts/noto-sans-bengali.woff2') format('woff2');
        }
        :root { --primary: #9C1F24; --text: #1f2937; }
        .brand-text { font-family: 'Potta One','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda',cursive !important; text-transform: uppercase !important; letter-spacing: 1px; }
        body { font-family: 'Poppins','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda','Shonar Bangla',sans-serif; color: var(--text); background: #f3f4f6; margin: 0; padding: 20px; font-size: 13px; }
        .pad-container { background: #fff; max-width: 820px; margin: 0 auto; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 8px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: 800; color: var(--primary); margin: 0; line-height: 1; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .info-label { font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-bottom: 5px; }
        .info-value { font-size: 14px; font-weight: 600; }
        
        .section-heading { font-size: 14px; font-weight: 700; color: var(--primary); margin: 18px 0 8px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th { background: #f3f4f6; padding: 8px 10px; text-align: left; font-size: 12px; border: 1px solid #e5e7eb; }
        td { padding: 8px 10px; border: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        
        .totals-box { width: 320px; margin-left: auto; border: 1px solid var(--primary); border-radius: 8px; overflow: hidden; margin-bottom: 24px; }
        .total-row { display: flex; justify-content: space-between; padding: 9px 14px; border-bottom: 1px solid #e5e7eb; }
        .total-row.grand { background: var(--primary); color: #fff; font-weight: 700; font-size: 15px; border-bottom: none; }
        
        .controls { text-align: center; margin-bottom: 20px; }
        .btn { background: var(--primary); color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-family: inherit; font-weight: 600; cursor: pointer; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .pad-container { box-shadow: none; border-radius: 0; padding: 0 12mm; max-width: 100%; }
            .controls, .no-print { display: none !important; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; page-break-inside: avoid; }
            table { break-inside: auto; }
            .no-break, .totals-box, .section-heading, .header, .info-grid { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
    <style id="pageRules">@media print { @page { size: A4; margin: 0; } .pad-container { padding: 2.17in 12mm 1in 12mm !important; } }</style>
</head>
<body>
<div class="controls">
    <div style="margin-bottom:15px; padding:10px; background:#e5e7eb; border-radius:8px; display:inline-block;">
        <label style="margin-right:15px; font-weight:600; font-size:12px; color:var(--text);">Top Margin (in): <input type="number" id="marginTop" value="2.17" step="0.01" style="width:70px; padding:4px; border:1px solid #ccc; border-radius:4px;" oninput="updateMargins()"></label>
        <label style="font-weight:600; font-size:12px; color:var(--text);">Bottom Margin (in): <input type="number" id="marginBottom" value="1" step="0.01" style="width:70px; padding:4px; border:1px solid #ccc; border-radius:4px;" oninput="updateMargins()"></label>
    </div><br>
    <button class="btn" onclick="window.print()">&#128438; Print</button>
</div>

<div class="pad-container">
    <div class="header">
        <div>
            <img src="<?= $basePath ?>/assets/img/logo-wide.png" alt="Lily Interiors" style="height:48px; width:auto; object-fit:contain;">
            <div style="font-size: 11px; color: #6b7280; margin-top:2px;">Project Management</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size: 18px; font-weight: 700; text-transform: uppercase; color: var(--text);">
                <?= htmlspecialchars($docTitle) ?>
            </div>
            <div>Date: <?= date('d M, Y') ?></div>
            <?php if($from || $to): ?>
            <div style="font-size:11px; margin-top:4px;">
                Period: <?= $from ? date('d M Y', strtotime($from)) : 'Start' ?> to <?= $to ? date('d M Y', strtotime($to)) : 'End' ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Project Details</div>
            <?php if($project): ?>
                <div class="info-value"><?= htmlspecialchars($project['name']) ?></div>
                <div style="font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($project['client_name'] ?? '') ?></div>
                <div style="font-size: 12px;"><?= htmlspecialchars($project['client_address'] ?? '') ?></div>
            <?php else: ?>
                <div class="info-value">All Projects (Consolidated)</div>
                <div style="font-size: 12px; color:#6b7280; margin-top: 4px;">Overall statement across all assigned projects</div>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <div class="info-label">Worker Information</div>
            <div class="info-value"><?= htmlspecialchars($worker['name']) ?></div>
            <div style="font-size: 12px; margin-top: 4px;"><strong>Trade:</strong> <?= htmlspecialchars($worker['trade'] ?? 'General') ?> | <strong>Rate:</strong> Tk. <?= number_format($worker['default_daily_rate'] ?? 0) ?>/day</div>
            <?php if(!empty($worker['contractor_name'])): ?>
                <div style="font-size: 12px; color:var(--primary); font-weight:600; margin-top:3px;">
                    &#128736; Works Under: <?= htmlspecialchars($worker['contractor_name']) ?> (<?= htmlspecialchars($worker['contractor_trade'] ?? 'Contractor') ?>)
                </div>
            <?php endif; ?>
            <?php if(!empty($worker['phone'])): ?>
                <div style="font-size: 12px; color:#6b7280;">Phone: <?= htmlspecialchars($worker['phone']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($type === 'attendance' || $type === 'statement'): ?>
    <div class="section-heading">&#128203; Attendance & Work Records</div>
    <table>
        <thead>
            <tr>
                <th style="width:90px;">Date</th>
                <?php if(!$project): ?><th>Project</th><?php endif; ?>
                <th>Daily Rate</th>
                <th>Hajira</th>
                <th>Notes</th>
                <th class="text-right" style="width:110px;">Earned (Tk)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($attendanceData as $row): ?>
            <tr>
                <td><?= date('d M Y', strtotime($row['work_date'])) ?></td>
                <?php if(!$project): ?><td><?= htmlspecialchars($row['project_name'] ?? '-') ?></td><?php endif; ?>
                <td><?= number_format($row['daily_rate'], 2) ?></td>
                <td><?= floatval($row['attendance_multiplier']) ?>x</td>
                <td><?= htmlspecialchars($row['notes'] ?? '-') ?></td>
                <td class="text-right"><?= number_format($row['earned'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($attendanceData)): ?>
            <tr><td colspan="<?= !$project ? '6' : '5' ?>" style="text-align:center; color:#9ca3af; padding:12px;">No attendance records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if($type === 'payments' || $type === 'statement'): ?>
    <div class="section-heading">&#128176; Payments Received</div>
    <table>
        <thead>
            <tr>
                <th style="width:90px;">Date</th>
                <?php if(!$project): ?><th>Project</th><?php endif; ?>
                <th>Method</th>
                <th>Who Paid</th>
                <th>Notes</th>
                <th class="text-right" style="width:110px;">Amount (Tk)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($paymentData as $row): ?>
            <tr>
                <td><?= date('d M Y', strtotime($row['payment_date'])) ?></td>
                <?php if(!$project): ?><td><?= htmlspecialchars($row['project_name'] ?? '-') ?></td><?php endif; ?>
                <td><?= htmlspecialchars($row['payment_method'] ?? 'Cash') ?></td>
                <td><?= htmlspecialchars($row['who_paid'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['notes'] ?? '-') ?></td>
                <td class="text-right"><?= number_format($row['amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($paymentData)): ?>
            <tr><td colspan="<?= !$project ? '6' : '5' ?>" style="text-align:center; color:#9ca3af; padding:12px;">No payments recorded.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="no-break">
    <div class="totals-box">
        <?php if($type === 'attendance' || $type === 'statement'): ?>
        <div class="total-row">
            <span>Total Work Days (Hajira)</span>
            <span><strong><?= number_format($totalHajira, 2) ?> days</strong></span>
        </div>
        <div class="total-row">
            <span>Total Earned</span>
            <span>Tk. <?= number_format($totalEarned, 2) ?></span>
        </div>
        <?php endif; ?>
        <?php if($type === 'payments' || $type === 'statement'): ?>
        <div class="total-row">
            <span>Total Paid</span>
            <span>Tk. <?= number_format($totalPaid, 2) ?></span>
        </div>
        <?php endif; ?>
        <?php if($type === 'statement'): ?>
        <div class="total-row grand">
            <span>Balance Due</span>
            <span>Tk. <?= number_format($balanceDue, 2) ?></span>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; text-align: center; margin-top: 60px;">
        <div>
            <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto 10px auto;"></div>
            <div style="font-size: 12px; font-weight: 600;">Worker Signature</div>
        </div>
        <div>
            <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto 10px auto;"></div>
            <div style="font-size: 12px; font-weight: 600;">Authorized By</div>
        </div>
    </div>
    </div>
</div>

<script>
function updateMargins() {
    let top = document.getElementById('marginTop').value || 0;
    let bottom = document.getElementById('marginBottom').value || 0;
    document.getElementById('pageRules').textContent = '@media print { @page { size: A4; margin: 0; } .pad-container { padding: ' + (parseFloat(top)||0) + 'in 12mm ' + (parseFloat(bottom)||0) + 'in 12mm !important; } }';
}
window.onload = function() {
    updateMargins();
};
</script>
</body>
</html>
