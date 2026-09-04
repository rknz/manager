<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
if (!isset($basePath)) {
    $bp = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = ($bp === '\\' || $bp === '/') ? '' : $bp;
}
$id = intval($_GET['id'] ?? 0);
if (!$id) die("Bill ID required");

$stmt = $pdo->prepare("SELECT b.*, p.name as project_name, p.client_name, p.client_address, c.name as contractor_name, c.trade, c.phone 
    FROM app_contractor_bills b 
    JOIN app_projects p ON b.project_id=p.id 
    JOIN app_contractors c ON b.contractor_id=c.id 
    WHERE b.id=? AND b.is_deleted=0");
$stmt->execute([$id]);
$bill = $stmt->fetch();
if (!$bill) die("Bill not found");

$items = json_decode($bill['bill_data'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill - #<?= $id ?></title>
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
    <button class="btn" onclick="window.print()">&#128438; Print Bill</button>
</div>
<div class="pad-container">
    <div class="header">
        <div>
            <img src="<?= $basePath ?>/assets/img/logo-wide.png" alt="Lily Interiors" style="height:48px; width:auto; object-fit:contain;">
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">Project Management</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size: 20px; font-weight: 700; color: var(--text);">CONTRACTOR BILL</div>
            <div>Date: <?= date('d M, Y', strtotime($bill['bill_date'])) ?></div>
            <div>Bill #<?= $id ?></div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-bottom: 5px;">Project Details</div>
            <div style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($bill['project_name']) ?></div>
            <div style="font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($bill['client_name']) ?></div>
            <div style="font-size: 12px;"><?= htmlspecialchars($bill['client_address']) ?></div>
        </div>
        <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; margin-bottom: 5px;">Billed To</div>
            <div style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($bill['contractor_name']) ?></div>
            <div style="font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($bill['trade']) ?></div>
            <div style="font-size: 12px;"><?= htmlspecialchars($bill['phone']) ?></div>
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
            <?php if(empty($items)): ?>
            <tr><td colspan="4" style="text-align:center;">No items found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="no-break">
    <div class="totals-box">
        <div class="total-row"><span>Sub Total</span><span><?= number_format($bill['sub_total'], 2) ?></span></div>
        <?php if($bill['labour_charge'] > 0): ?>
        <div class="total-row"><span>Labour Charge</span><span><?= number_format($bill['labour_charge'], 2) ?></span></div>
        <?php endif; ?>
        <?php if($bill['other_charge'] > 0): ?>
        <div class="total-row"><span>Other Charge</span><span><?= number_format($bill['other_charge'], 2) ?></span></div>
        <?php endif; ?>
        <div class="total-row grand" style="background:#e5e7eb;color:var(--text);"><span>Grand Total</span><span><?= number_format($bill['grand_total'], 2) ?></span></div>
        <div class="total-row"><span>Previous Paid/Advances</span><span><?= number_format($bill['total_paid'], 2) ?></span></div>
        <div class="total-row grand"><span>Balance Due</span><span><?= number_format($bill['balance_due'], 2) ?></span></div>
    </div>
    
    <div style="font-size:12px; font-weight:600; margin-top:10px;">In Words: <span id="wordsOut" style="text-transform: capitalize;"></span></div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; text-align: center; margin-top: 80px;">
        <div>
            <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto 10px auto;"></div>
            <div style="font-size: 12px; font-weight: 600;">Contractor / Receiver</div>
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
    document.getElementById('wordsOut').textContent = numToWords(Math.round(<?= $bill['grand_total'] ?>));
};
</script>
</body>
</html>
