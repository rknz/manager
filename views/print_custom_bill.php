<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if (!isset($basePath)) {
    $bp = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = ($bp === '\\' || $bp === '/') ? '' : $bp;
}
$project_id = intval($_POST['project_id'] ?? 0);
$type = $_POST['bill_type'] ?? 'contractor';
$target_id = intval($_POST['target_id'] ?? 0);
$date = $_POST['bill_date'] ?? date('Y-m-d');

if (!$project_id || !$target_id) { die("Invalid request data"); }

$stmt = $pdo->prepare("SELECT * FROM app_projects WHERE id=?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

$targetName = "";
$targetTrade = "";
$targetPhone = "";

if ($type === 'contractor') {
    $stmt = $pdo->prepare("SELECT * FROM app_contractors WHERE id=?");
    $stmt->execute([$target_id]);
    $c = $stmt->fetch();
    if ($c) { $targetName = $c['name']; $targetTrade = $c['trade']; $targetPhone = $c['phone']; }
    
    // Fetch total paid for advances
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND contractor_id=? AND is_deleted=0");
    $stmt->execute([$project_id, $target_id]);
    $contractor_paid = (float)$stmt->fetchColumn();

    // Crew (workers working under this contractor): attendance + payments shown together
    $crewRows = [];
    $crewPaid = 0.0;
    $crewEarned = 0.0;
    $stmt = $pdo->prepare("SELECT w.id, w.name, w.trade
        FROM app_workers w
        WHERE w.contractor_id=? AND w.is_active=1 ORDER BY w.name");
    $stmt->execute([$target_id]);
    foreach ($stmt->fetchAll() as $wk) {
        $att = $pdo->prepare("SELECT COALESCE(SUM(a.attendance_multiplier),0) AS days, COALESCE(SUM(a.earned),0) AS earned
                              FROM app_attendance a WHERE a.project_id=? AND a.worker_id=? AND a.is_deleted=0");
        $att->execute([$project_id, $wk['id']]);
        $a = $att->fetch();
        $paid = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND worker_id=? AND is_deleted=0");
        $paid->execute([$project_id, $wk['id']]);
        $p = (float)$paid->fetchColumn();
        $crewPaid += $p;
        $crewEarned += floatval($a['earned']);
        $isContractor = (mb_strtolower(trim($wk['name'])) === mb_strtolower(trim($targetName)));
        $crewRows[] = ['name'=>$wk['name'], 'trade'=>$wk['trade'], 'days'=>floatval($a['days']), 'earned'=>floatval($a['earned']), 'paid'=>$p, 'is_contractor'=>$isContractor];
    }
    $total_paid = $contractor_paid + $crewPaid;
} else {
    $stmt = $pdo->prepare("SELECT * FROM app_workers WHERE id=?");
    $stmt->execute([$target_id]);
    $w = $stmt->fetch();
    if ($w) { $targetName = $w['name']; $targetTrade = $w['trade']; $targetPhone = $w['phone']; }
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND worker_id=? AND is_deleted=0");
    $stmt->execute([$project_id, $target_id]);
    $total_paid = (float)$stmt->fetchColumn();
}

$items = json_decode($_POST['items_json'] ?? '[]', true) ?: [];
$labour_charge = floatval($_POST['labour_charge'] ?? 0);
$other_charge = floatval($_POST['other_charge'] ?? 0);

$sub_total = 0;
foreach ($items as $item) {
    $sub_total += floatval($item['total']);
}
$grand_total = $sub_total + $labour_charge + $other_charge;
$balance_due = $grand_total - $total_paid;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Final Bill - <?= htmlspecialchars($targetName) ?></title>
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
        .pad-container { background: #fff; max-width: 800px; margin: 0 auto; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 8px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: 800; color: var(--primary); margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f3f4f6; padding: 10px; text-align: left; font-size: 12px; border: 1px solid #e5e7eb; }
        td { padding: 10px; border: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .totals-box { width: 300px; margin-left: auto; border: 1px solid var(--primary); border-radius: 8px; overflow: hidden; margin-bottom: 30px; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 15px; border-bottom: 1px solid #e5e7eb; }
        .total-row.grand { background: var(--primary); color: #fff; font-weight: 700; font-size: 16px; border-bottom: none; }
        .controls { text-align: center; margin-bottom: 20px; }
        .btn { background: var(--primary); color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-family: inherit; font-weight: 600; cursor: pointer; }
        @media print {
            body { background: #fff; padding: 0; }
            .pad-container { box-shadow: none; border-radius: 0; padding: 0 12mm; max-width: 100%; }
            .controls { display: none !important; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; page-break-inside: avoid; }
            table { break-inside: auto; }
            .no-break, .totals-box, .section-title, .header, .info-grid, .ctable { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
    <style id="pageRules">@media print { @page { size: A4; margin: 0; } .pad-container { padding: 2.17in 12mm 1in 12mm !important; } }</style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>const BASE_PATH = '<?= dirname($_SERVER['SCRIPT_NAME']) === "\\" || dirname($_SERVER['SCRIPT_NAME']) === "/" ? "" : dirname($_SERVER['SCRIPT_NAME']) ?>';</script>
</head>
<body>
<div class="controls">
    <div style="margin-bottom:15px; padding:10px; background:#e5e7eb; border-radius:8px; display:inline-block;">
        <label style="margin-right:15px; font-weight:600; font-size:12px; color:var(--text);">Top Margin (in): <input type="number" id="marginTop" value="2.17" step="0.01" style="width:70px; padding:4px; border:1px solid #ccc; border-radius:4px;" oninput="updateMargins()"></label>
        <label style="font-weight:600; font-size:12px; color:var(--text);">Bottom Margin (in): <input type="number" id="marginBottom" value="1" step="0.01" style="width:70px; padding:4px; border:1px solid #ccc; border-radius:4px;" oninput="updateMargins()"></label>
    </div><br>
    <button class="btn" onclick="window.print()">&#128438; Print Bill</button>
    <button class="btn" id="btnSave" onclick="saveBillPdf()" style="background:#10b981; margin-left:10px;">&#128190; Save Bill</button>
</div>
<div class="pad-container">
    <div class="header">
        <div>
            <img src="<?= $basePath ?>/assets/img/logo-wide.png" alt="Lily Interiors" style="height:48px; width:auto; object-fit:contain;">
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">Project Management</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size: 20px; font-weight: 700; color: var(--text);">FINAL BILL</div>
            <div>Date: <?= date('d M, Y', strtotime($date)) ?></div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-bottom: 5px;">Project Details</div>
            <div style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($project['name']) ?></div>
            <div style="font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($project['client_name']) ?></div>
            <div style="font-size: 12px;"><?= htmlspecialchars($project['client_address'] ?? '') ?></div>
        </div>
        <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-bottom: 5px;">Billed To (<?= htmlspecialchars(ucfirst($type)) ?>)</div>
            <div style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($targetName) ?></div>
            <div style="font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($targetTrade) ?></div>
            <div style="font-size: 12px;"><?= htmlspecialchars($targetPhone) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Rate (Tk)</th>
                <th class="text-right">Amount (Tk)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['description']) ?></td>
                <td class="text-right"><?= $item['quantity'] ?></td>
                <td class="text-right"><?= number_format($item['rate'], 2) ?></td>
                <td class="text-right"><?= number_format($item['total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(!empty($items)): ?>
            <tr><td colspan="4" style="text-align:center;">No items provided.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($type === 'contractor' && !empty($crewRows)): ?>
    <?php
        $contractorHimselfPaid = 0.0;
        foreach ($crewRows as $cr) if ($cr['is_contractor']) $contractorHimselfPaid += $cr['paid'];
        $crewOnlyPaid = $crewPaid - $contractorHimselfPaid;
    ?>
    <div style="margin-bottom:20px;">
        <div style="font-size:16px; font-weight:700; color:var(--primary); margin-bottom:10px;">Money Distribution</div>
        <table>
            <thead>
                <tr><th>Recipient</th><th class="text-right">Paid (Tk)</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($targetName) ?> <span style="color:var(--primary);font-weight:600;">(Contractor)</span><?= $contractorHimselfPaid > 0 ? ' — incl. own labor paid' : '' ?></td>
                    <td class="text-right"><?= number_format($contractor_paid + $contractorHimselfPaid, 2) ?></td>
                </tr>
                <?php foreach($crewRows as $cr): if($cr['is_contractor']) continue; ?>
                <tr>
                    <td><?= htmlspecialchars($cr['name']) ?> <span style="color:#6b7280;">(hired worker)</span></td>
                    <td class="text-right"><?= number_format($cr['paid'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="font-weight:700; background:#f3f4f6;">
                    <td>Total Paid to All (Advances + Workers)</td>
                    <td class="text-right"><?= number_format($total_paid, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <div class="no-break">
    <div class="totals-box">
        <div class="total-row"><span>Sub Total</span><span><?= number_format($sub_total, 2) ?></span></div>
        <?php if($labour_charge > 0): ?>
        <div class="total-row"><span>Labour Charge</span><span><?= number_format($labour_charge, 2) ?></span></div>
        <?php endif; ?>
        <?php if($other_charge > 0): ?>
        <div class="total-row"><span>Other Charge</span><span><?= number_format($other_charge, 2) ?></span></div>
        <?php endif; ?>
        <div class="total-row grand" style="background:#e5e7eb;color:var(--text);"><span>Grand Total</span><span><?= number_format($grand_total, 2) ?></span></div>
        <div class="total-row"><span>Total Paid</span><span><?= number_format($total_paid, 2) ?></span></div>
        <div class="total-row grand"><span>Balance Due</span><span><?= number_format($balance_due, 2) ?></span></div>
    </div>
    <div style="font-size:12px; font-weight:600; margin-top:10px;">In Words: <span id="wordsOut" style="text-transform: capitalize;"></span></div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; text-align: center; margin-top: 80px;">
        <div>
            <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto 10px auto;"></div>
            <div style="font-size: 12px; font-weight: 600;">Receiver Signature</div>
        </div>
        <div>
            <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto 10px auto;"></div>
            <div style="font-size: 12px; font-weight: 600;">Authorized By</div>
        </div>
    </div>
    </div>
</div>
<script>
const numToWords = (num) => {
    const a = ['','One ','Two ','Three ','Four ', 'Five ','Six ','Seven ','Eight ','Nine ','Ten ','Eleven ','Twelve ','Thirteen ','Fourteen ','Fifteen ','Sixteen ','Seventeen ','Eighteen ','Nineteen '];
    const b = ['', '', 'Twenty','Thirty','Forty','Fifty', 'Sixty','Seventy','Eighty','Ninety'];
    if ((num = num.toString()).length > 9) return 'overflow';
    let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
    if (!n) return; let str = '';
    str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'Crore ' : '';
    str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'Lakh ' : '';
    str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'Thousand ' : '';
    str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'Hundred ' : '';
    str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) : '';
    return str.trim() ? str.trim() + ' Taka Only' : 'Zero Taka Only';
};
function updateMargins() {
    let top = document.getElementById('marginTop').value || 0;
    let bottom = document.getElementById('marginBottom').value || 0;
    document.getElementById('pageRules').textContent = '@media print { @page { size: A4; margin: 0; } .pad-container { padding: ' + (parseFloat(top)||0) + 'in 12mm ' + (parseFloat(bottom)||0) + 'in 12mm !important; } }';
}
window.onload = function() {
    updateMargins();
    document.getElementById('wordsOut').textContent = numToWords(Math.round(<?= $grand_total ?>));
};

function saveBillPdf() {
    const btn = document.getElementById('btnSave');
    btn.textContent = 'Saving...';
    btn.disabled = true;
    const element = document.querySelector('.pad-container');
    const topMm = (parseFloat(document.getElementById('marginTop').value)||0)*25.4;
    const bottomMm = (parseFloat(document.getElementById('marginBottom').value)||0)*25.4;
    const opt = {
        margin:       [topMm, 10, bottomMm, 10],
        filename:     'bill.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:    { mode: ['css', 'legacy'] }
    };
    html2pdf().set(opt).from(element).toPdf().get('pdf').then(async function (pdf) {
        var totalPages = pdf.internal.getNumberOfPages();
        var pageW = pdf.internal.pageSize.getWidth();
        for (var i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(9);
            pdf.setTextColor(120);
            pdf.text(String(i), pageW / 2, pdf.internal.pageSize.getHeight() - 5, { align: 'center' });
        }
        var pdfBase64 = pdf.output('datauristring');
        const fd = new FormData();
        fd.append('project_id', <?= $project_id ?>);
        fd.append('title', 'Final Bill - <?= addslashes($targetName) ?>');
        fd.append('pdf_data', pdfBase64);
        try {
            const r = await fetch(BASE_PATH + '/api/printouts.php?action=save_base64', {method:'POST', body:fd});
            const d = await r.json();
            if(d.success) {
                btn.textContent = 'Saved!';
                btn.style.background = '#059669';
            } else {
                btn.textContent = 'Error';
                btn.disabled = false;
                alert(d.message || 'Failed to save');
            }
        } catch(e) {
            btn.textContent = 'Error';
            btn.disabled = false;
            alert('Failed to save bill.');
        }
    });
}
</script>
</body>
</html>
