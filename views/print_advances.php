<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if (!isset($basePath)) {
    $bp = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = ($bp === '\\' || $bp === '/') ? '' : $bp;
}

$project_id = intval($_POST['project_id'] ?? 0);
$contractor_id = intval($_POST['contractor_id'] ?? 0);

if (!$project_id || !$contractor_id) { die("Project ID and Contractor ID required"); }

// Project
$stmt = $pdo->prepare("SELECT * FROM app_projects WHERE id=?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();
if (!$project) { die("Project not found"); }

// Contractor
$stmt = $pdo->prepare("SELECT * FROM app_contractors WHERE id=?");
$stmt->execute([$contractor_id]);
$contractor = $stmt->fetch();
if (!$contractor) { die("Contractor not found"); }

// Rows posted from editable preview: {group, date, name, person_type, amount}
// Only payment rows (group 'pay') are relevant for this advance statement.
$rows = json_decode($_POST['rows_json'] ?? '[]', true) ?: [];
$rows = is_array($rows) ? $rows : [];

$payments = [];
foreach ($rows as $r) {
    if (!is_array($r)) continue;
    if (($r['group'] ?? '') !== 'pay') continue;
    $date = trim((string)($r['date'] ?? ''));
    $name = trim((string)($r['name'] ?? ''));
    if ($name === '') continue;
    $pt = ((string)($r['person_type'] ?? '')) === 'contractor' ? 'contractor' : 'worker';
    $amount = floatval($r['amount'] ?? 0);
    $payments[] = ['date'=>$date, 'name'=>$name, 'person_type'=>$pt, 'amount'=>$amount];
}
function sortByDate($a,$b){ return strcmp($a['date'], $b['date']); }
usort($payments, 'sortByDate');

$totalPaid = array_sum(array_column($payments, 'amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advance Payment Statement - <?= htmlspecialchars($contractor['name']) ?></title>
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
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 26px; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .info-label { font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-bottom: 5px; }
        .info-value { font-size: 14px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f3f4f6; padding: 9px 10px; text-align: left; font-size: 12px; border: 1px solid #e5e7eb; }
        td { padding: 9px 10px; border: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .badge { padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .badge-primary { background: #fce8e8; color: var(--primary); }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .totals-box { width: 320px; margin-left: auto; border: 1px solid var(--primary); border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 15px; border-bottom: 1px solid #e5e7eb; }
        .total-row.grand { background: var(--primary); color: #fff; font-weight: 700; font-size: 15px; border-bottom: none; }
        .controls { text-align: center; margin-bottom: 20px; }
        .btn { background: var(--primary); color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-family: inherit; font-weight: 600; cursor: pointer; }
        @media print {
            body { background: #fff; padding: 0; }
            .pad-container { box-shadow: none; border-radius: 0; padding: 0 12mm; max-width: 100%; }
            .controls { display: none !important; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; page-break-inside: avoid; }
            table { break-inside: auto; }
            .no-break, .totals-box, .section-title, .header, .info-grid { break-inside: avoid; page-break-inside: avoid; }
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
            <div style="font-size: 20px; font-weight: 700; text-transform: uppercase; color: var(--text);">Advance Payment Statement</div>
            <div style="font-size:12px; margin-top:4px;">Payment Detail</div>
            <div>Date: <?= date('d M, Y') ?></div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Project Details</div>
            <div class="info-value"><?= htmlspecialchars($project['name']) ?></div>
            <div style="font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($project['client_name']) ?></div>
            <div style="font-size: 12px;"><?= htmlspecialchars($project['client_address'] ?? '') ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Contractor Details</div>
            <div class="info-value"><?= htmlspecialchars($contractor['name']) ?></div>
            <div style="font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($contractor['trade']) ?></div>
            <div style="font-size: 12px;"><?= htmlspecialchars($contractor['phone']) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:110px;">Date</th>
                <th>Paid To</th>
                <th style="width:130px;">Role</th>
                <th class="text-right" style="width:130px;">Amount (Tk)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($payments)): ?>
            <tr><td colspan="4" style="text-align:center;">No payments found.</td></tr>
            <?php else: ?>
            <?php foreach($payments as $p): ?>
            <tr>
                <td><?= $p['date'] ? date('d M Y', strtotime($p['date'])) : '-' ?></td>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                <td><?= $p['person_type']==='contractor' ? '<span class="badge badge-primary">Contractor</span>' : '<span class="badge badge-info">Worker</span>' ?></td>
                <td class="text-right"><?= number_format($p['amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="no-break">
    <div class="totals-box">
        <div class="total-row grand"><span>Total Paid</span><span><?= number_format($totalPaid, 2) ?></span></div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; text-align: center; margin-top: 80px;">
        <div>
            <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto 10px auto;"></div>
            <div style="font-size: 12px; font-weight: 600;">Contractor Signature</div>
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
