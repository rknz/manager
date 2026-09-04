<?php
// includes/trades.php — Shared master list of trades/categories used for
// worker & contractor trade dropdowns across the app.
if (!defined('TRADES_INCLUDED')) {
    define('TRADES_INCLUDED', true);

    $TRADES = [
        'Carpenter',
        'Electrician',
        'Mason',
        'Painter',
        'Thai Glass',
        'Glass Fitter',
        'Plumber',
        'Welder',
        'Tile Fitter',
        'Steel / Rod Binder',
        'Helper',
        'Other',
    ];

    // Prints <option> tags for a trade <select>. $selected is the current value.
    function tradeOptions($selected = null) {
        global $TRADES;
        $html = '<option value="">-- Select Trade --</option>';
        $found = false;
        foreach ($TRADES as $t) {
            $sel = ($selected !== null && (string)$selected === (string)$t) ? ' selected' : '';
            if ($sel) $found = true;
            $html .= '<option value="' . htmlspecialchars($t) . '"' . $sel . '>' . htmlspecialchars($t) . '</option>';
        }
        // Keep legacy/unknown stored trades selectable so editing a worker never
        // silently drops their existing trade.
        if ($selected !== null && $selected !== '' && !$found) {
            $html .= '<option value="' . htmlspecialchars($selected) . '" selected>' . htmlspecialchars($selected) . '</option>';
        }
        return $html;
    }
}
