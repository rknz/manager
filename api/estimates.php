<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

requireLogin();

// --- ENSURE DATABASE TABLES & SEED CATALOG ---
function ensureEstimateTables($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    // 1. Catalog table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `app_estimate_catalog` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `category` VARCHAR(50) NOT NULL,
      `item_name` VARCHAR(150) NOT NULL,
      `specifications` TEXT NOT NULL,
      `unit` VARCHAR(20) NOT NULL DEFAULT 'S.ft',
      `default_rate` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Estimates master table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `app_estimates` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `estimate_no` VARCHAR(50) NOT NULL UNIQUE,
      `client_name` VARCHAR(150) NOT NULL,
      `client_designation` VARCHAR(100) DEFAULT NULL,
      `client_company` VARCHAR(150) DEFAULT NULL,
      `client_address` TEXT DEFAULT NULL,
      `client_phone` VARCHAR(50) DEFAULT NULL,
      `subject` VARCHAR(255) NOT NULL,
      `project_type` ENUM('commercial','residential') DEFAULT 'commercial',
      `group_by` ENUM('category','room') DEFAULT 'category',
      `working_days` VARCHAR(50) DEFAULT '25-30 working days',
      `advance_pct` DECIMAL(5,2) DEFAULT 60.00,
      `running_pct` DECIMAL(5,2) DEFAULT 30.00,
      `final_pct` DECIMAL(5,2) DEFAULT 10.00,
      `total_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      `discount_amount` DECIMAL(14,2) DEFAULT 0.00,
      `grand_total` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      `status` ENUM('draft','sent','approved','converted') DEFAULT 'draft',
      `converted_project_id` INT DEFAULT NULL,
      `notes` TEXT DEFAULT NULL,
      `prepared_by` VARCHAR(100) DEFAULT 'Md. Rukonuzzaman',
      `approved_by` VARCHAR(100) DEFAULT 'Md. Mustafizur Rahman',
      `created_by` INT NOT NULL DEFAULT 1,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Estimate items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `app_estimate_items` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `estimate_id` INT NOT NULL,
      `section_name` VARCHAR(100) NOT NULL,
      `section_order` INT NOT NULL DEFAULT 1,
      `sl_no` INT NOT NULL DEFAULT 1,
      `description` TEXT NOT NULL,
      `unit` VARCHAR(20) NOT NULL,
      `quantity` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      `amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      INDEX(`estimate_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    try {
        $pdo->exec("ALTER TABLE `app_estimates` ADD COLUMN `prepared_by_designation` VARCHAR(100) DEFAULT 'Interior Designer' AFTER `prepared_by`");
    } catch (\Throwable $e) {}
    try {
        $pdo->exec("ALTER TABLE `app_estimates` ADD COLUMN `approved_by_designation` VARCHAR(100) DEFAULT 'Managing Director' AFTER `approved_by`");
    } catch (\Throwable $e) {}

    // 4. Estimate categories table (persistent)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `app_estimate_categories` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL UNIQUE,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Auto-seed standard categories if empty
    $catCount = (int)$pdo->query("SELECT COUNT(*) FROM `app_estimate_categories`")->fetchColumn();
    if ($catCount === 0) {
        $pdo->exec("INSERT IGNORE INTO `app_estimate_categories` (`name`) VALUES 
            ('Ceiling'), ('Furniture'), ('Wall & Floor'), ('Paint'), ('Electrical')");
    }

    // Auto-seed standard Lily Interiors Catalog if empty
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `app_estimate_catalog`")->fetchColumn();
    if ($count === 0) {
        $seed = [
            // Ceiling
            ['Ceiling', 'Plain Particle Ceiling', 'Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with matt enamel/plastic paint finish as per design.', 'S.ft', 520.00],
            ['Ceiling', 'Drop Beam Ceiling', 'Drop Beam: Supply fitting/fixing of drop ceiling over 7\'-0" height plain particle board on garjan wooden frame all complete plastic paint as per design.', 'S.ft', 540.00],
            ['Ceiling', 'Gypsum Ceiling', 'Gypsum Ceiling: Supply fitting/fixing of gypsum ceiling over 8\'-6" height 24"x24" board on garjan wooden frame all complete.', 'S.ft', 80.00],
            ['Ceiling', 'Duco Finish Particle Ceiling', 'Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with doccu paint finish.', 'S.ft', 600.00],
            
            // Furniture
            ['Furniture', 'Full Height Wardrobe Cabinet', 'Cabinet: Making, supplying and fixing of full height cabinet made of 18mm HPL pasting board shutter & outer side with inside shelf by 18mm G.ply(Akij) with booth side .5mm waterproof indian formica auto machine pasting on almunium T-Bit bordering and big size groove handrail, made Length VariableX 102"(H) X16"/18"(D) including all accessories matching edging with backside extra damp protection all complete as per design.', 'S.ft', 2150.00],
            ['Furniture', 'Low Height Cabinet (LHC)', 'Low Height Cabinet (LHC): Making, supplying and fixing of LHC cabinet made Length VariableX 30"(H) X24"(D) with 18mm melamine / HPL board & matching edging all complete.', 'S.ft', 1700.00],
            ['Furniture', 'Kitchen Overhead Cabinet (OHC)', 'Kitchen (O.H.C): Making, supplying and fixing of kitchen cabinet made of 18mm garjan ply(Akij) with HPL pasting and aluminium profile handle bar made with including all accessories matching edging all complete.', 'S.ft', 2250.00],
            ['Furniture', 'Kitchen Middle Cabinet (MHC)', 'Kitchen (M.H.C): Making, supplying and fixing of kitchen cabinet made of 3/4" garjan ply(Akij) with HPL sheet pasting and aluminium profile handle bar made with including all accessories matching edging all complete.', 'S.ft', 3100.00],
            ['Furniture', 'Kitchen Lower Cabinet (LHC)', 'Kitchen (L.H.C): Making, supplying and fixing of kitchen cabinet made of 3/4" marine ply(Akij) with HPL sheet pasting aluminium profile handle bar including all SS accessories matching edging all complete.', 'S.ft', 2850.00],
            ['Furniture', 'Dinner Wagon Cabinet', 'Dinner Wagon: Making, supplying and fixing of dinner wagon cabinet made of 18mm HPL with Akij G.Plywood(main body) & 18mm glass profile (shutter) with shutter front side are mercury glass finishing & middle space design glass on marble top made Length VariableX 96"(H) X16"(D) with including all accessories matching edging all complete.', 'S.ft', 2450.00],
            ['Furniture', 'TV Cabinet with Charcoal Finish', 'TV Cabinet: Making, supplying and fixing of TV cabinet made of 18mm HPL with Akij G.ply board on Garjan framing and booth side imported charcoal finishing & including all accessories matching edging all complete as per design.', 'S.ft', 1350.00],
            ['Furniture', 'Dressing Unit with Touch LED Mirror', 'Dressing: Making, supplying and fixing of dressing cabinet for bedroom made of 18mm HPL Pasting Akij Plywood boards and inside shelf by white/gray formica on ahmed g.ply including best quality mirror with touch lighting and imported cabinet glass frame, making best quality dressing stool/seater all complete as per design.', 'S.ft', 1450.00],
            ['Furniture', 'Curtain Pelmet Box', 'Palmet Box & Paneling: Making, supplying and fixing of curtain pelmet box made of 18mm G.Ply(Akij) on BT.Garjan wooden frame with matching edging including all accessories all complete with doccu paint as per design.', 'S.ft', 1050.00],
            ['Furniture', 'King Size Sleeping Bed (5\'-6"x7\'-0")', 'Sleeping Bed: Making decorative king size sleeping bed for master bedroom(size:5\'-6"x7\'-0") supplying, fixing and made of 18mm Garjan Plywood(Akij) board and Top side & down side are solid wood and original indian fabrics,foaming finishing with Eurasia mattress (10year warranty) including Eurasia mattress topper matching edging complete.', 'nos', 115000.00],
            ['Furniture', 'Modern Reception Table', 'Reception Table: Making, supplying and fixing of modern reception table made Length VariableX 40"(H) x96"(L)x18"D with 18mm HPL board with matching edging all complete accessories.', 'nos', 44000.00],
            ['Furniture', 'Meeting Table 8\' x 3\'', 'Meeting Table: Supply fitting/fixing of meeting table 8\'-0"x3\'-0" HPL board with metal leg all complete as per design.', 'nos', 30500.00],
            ['Furniture', 'Workstation 6-Person (Clear Glass Divider)', 'Workstation 6p: Making workstation made Length Variable 32"X30"(Height) X24"(depth) with keyboard tray with upper divider by 10mm clear glass(1FT height) and frosted paper design on lower part 18mm melamine board & matching edging. All complete as per design.', 'per.', 8000.00],
            ['Furniture', 'Workstation 3-Person', 'Workstation 3p: Making workstation table made Length Variable 48"X30"(Height) X24"(depth) with keyboard tray and drawer unit Table Top (HPL) lower part 18mm melamine board & matching edging. All complete as per design.', 'per.', 12500.00],
            ['Furniture', 'Counselor Table (4\'x2\')', 'Counselor Table: Making fitting/fixing of counselor table 4\'-0"x2\'-0" HPL board(top) with lower part 18mm melamine board all complete.', 'nos', 12500.00],
            ['Furniture', 'Hanging Common Basin Set', 'Common Basin set: Making, supplying and fixing of Hanging common basin with big size design round mirror & basin top granite (lower part 18mm Akij Marine.Ply with doccu) including all accessories all complete.', 'nos', 42300.00],
            ['Furniture', 'Solid Segun/Chambol Wood Folding Door', 'Folding Door (2 pcs): Making, supplying and fixing of folding door made of ctg segun/teakchambol solid wood with frosted glass and doccu paint frame made Length VariableX 84"(H) X16" including all accessories matching edging with all complete as per design.', 'S.ft', 1850.00],

            // Wall & Floor
            ['Wall & Floor', '10mm Frameless Tempered Glass Partition', 'Glass Partition: Supply fitting/fixing frameless glass partition made of 10mm thick glass with all hardware/accessories such as stainless steel handles, protector bit and VVP auto-closer,stopper lock etc. All complete as per design.', 'S.ft', 290.00],
            ['Wall & Floor', 'Tempered Glass Door 3\'x7\'', 'Tempered Glass Door: Supply fitting/fixing frameless glass door (size: 3\'-0"x7\'-0") made of 10mm thick tempered glass with all hardware/accessories such as stainless steel handles and VVP auto-closer,stopper lock etc. All complete as per design.', 'nos', 11500.00],
            ['Wall & Floor', 'Tempered Glass Door 2\'-6"x7\'', 'Tempered Glass Door: Supply fitting/fixing frameless glass door (size: 2\'-6"x7\'-0") made of 10mm thick tempered glass with all hardware/accessories such as stainless steel handles and VVP auto-closer,stopper lock etc. All complete as per design.', 'nos', 11000.00],
            ['Wall & Floor', 'Wall Partition 12mm Garjan Ply HPL', 'Wall Partition Work: Making, Supplying, Fitting fixing of wall partition 12mm garjan ply board with garjan wood framing and HPL pasting all complete as per design.', 'S.ft', 800.00],
            ['Wall & Floor', 'Wall Paneling / Sofa Back Decoration', 'Wall Decoration (sofa back): Making, supplying and fixing of wall paneling made of 18mm G.Ply(Akij) with matching edging including all accessories all complete with doccu paint as per design.', 'S.ft', 1050.00],
            ['Wall & Floor', 'PVC CNC Bit Cut Wall Paneling', 'Wall Paneling(washroom wall): Making, supplying and fixing of master bedroom toilet side wall paneling made of 18/12mm pvc board CNC bit cutting all complete as per design.', 'S.ft', 460.00],
            ['Wall & Floor', 'Imported Charcoal Louver Paneling', 'Counter Table paneling work: Making, supplying and fixing of kitchen counter / wall paneling made of imported charcoal louver all complete as per design.', 'S.ft', 750.00],
            ['Wall & Floor', 'Imported PVC Floor Carpet', 'PVC Floor Carpet: Supply fitting/ fixing of pvc floor carpet(imported) for lift / kitchen area all complete.', 'S.ft', 165.00],
            ['Wall & Floor', 'Imported Floor Carpet (Rooms)', 'Floor Carpet: Supply fitting/ fixing of floor carpet(imported) all room & common space all complete.', 'S.ft', 107.00],
            ['Wall & Floor', '35mm Green Grass Carpet', 'Veranda Floor Grass Carpet: Supply fitting/ fixing of 35mm green grass floor carpet(imported) all complete.', 'S.ft', 130.00],
            ['Wall & Floor', 'SS Sliding Security Gate', 'SS Security Gate: Supply fitting/fixing of 6\'-2"x7\'-4" sliding ss security gate all complete.', 'S.ft', 1050.00],

            // Paint
            ['Paint', 'Berger Plastic Paint (ECE Roller Finish)', 'Plastic paint (roller finish): Plastic paint (Berger/Asian brand) of approved color to on plaster and others surfaces (1coat sealer+2coat putty+2coat ECE) Easy Clean Emulsion finish after necessary sealer, lime putty,cleaning,sand papering the base surfaces as per standard.', 'S.ft', 45.00],
            ['Paint', 'Berger Luxury BEE Paint (Foam Finish)', 'BEE paint (foam finish): Luxury paint (Berger brand) of approved color to on plaster surfaces (1coat sealer+3coat putty+1coat sealer+2coat BEE) Breathe easy Emulsion finish after necessary sealer, lime putty,cleaning,sand papering the base surfaces as per standard.', 'S.ft', 70.00],
            ['Paint', 'Berger Enamel Paint (Window / Grill)', 'Enamel Paint: All bed rooms window & verandah grill enamel paint (Berger brand) of approved color to on metal surfaces (1coat RFLPR+2coat Thinner T-6+2coat RSE) after necessary cleaning,sand papering the base surfaces as per direction.', 'S.ft', 50.00],

            // Electrical
            ['Electrical', 'LED Office Hang Light 4FT 72w', 'LED Office Hang Light 72w: Supplying fitting and fixing of complete set of 4FT length LED ceiling Light (Energy+) with all accessories.', 'nos', 2550.00],
            ['Electrical', 'LED Office Hang Light 8FT 120w', 'LED Office Hang Light 120w: Supplying fitting and fixing of complete set of 8FT length LED ceiling Light (Energy+) with all accessories.', 'nos', 5150.00],
            ['Electrical', 'LED Panel Light 12w Round', 'LED Panel Light 12w: Supplying fitting and fixing of LED panel Light (Energy+) with all accessories etc. all complete as per design.', 'nos', 650.00],
            ['Electrical', 'LED Conceal Panel Light 2\'x2\' 48w', 'LED Panel Light 48w: Supplying fitting and fixing of 2\'x2\' LED conceal panel Light (Energy+) with all accessories etc. all complete as per design.', 'nos', 2800.00],
            ['Electrical', 'LED Exclusive 8-Ring Chandelier', 'LED Exclusive hanging Light 8 ring: Supplying fitting and fixing of LED exclusive hanging Light (Energy+) with all accessories etc. all complete as per design.', 'nos', 31240.00],
            ['Electrical', 'Wall Surface Down Spot Light 12w', 'Wall Surface down Spot Light 12w: Supplying fitting and fixing of LED spot Light (Energy+) 12watt with all accessories etc. all complete as per direction.', 'nos', 1790.00],
            ['Electrical', 'Heavy Duty 3-Faces Strip Light', 'Three faces Strip Light: Supply, fitting & fixing imported Heavy duty Energy+ copper strip light with warm color best quality of approved color with all other accessories etc.', 'mtr.', 220.00],
            ['Electrical', 'Heavy Duty Profile Light with Diffuser', 'Profile Light: Supply, fitting & fixing imported Heavy duty profile light best quality of approved color with all other accessories etc.', 'RFT', 255.00],
            ['Electrical', 'BRB BYA Cable 1C x 1.5 rm', 'Electric cabling work (BRB): 1C X 1.5 rm BYA cable.', 'coil', 4800.00],
            ['Electrical', 'BRB BYA Cable 1C x 2.5 rm', 'Electric cabling work (BRB): 1C X 2.5 rm BYA cable.', 'coil', 7659.00],
            ['Electrical', 'BRB BYA Cable 1C x 4.0 rm', 'Electric cabling work (BRB): 1C X 4.0 rm BYA cable.', 'coil', 11923.00],
            ['Electrical', 'MK / ART-DNA 13A/15A Multi Socket', 'M.K / ART-DNA SOCKET (Deluxe/Premium): 13A/15A, multi socket with gang box.', 'nos', 464.00],
            ['Electrical', 'MK / ART-DNA Switch 2-Gang / 3-Gang', 'M.K / ART-DNA SWITCH (Deluxe/Premium): 2-Gang / 3-Gang (1 way) switch.', 'nos', 395.00],
            ['Electrical', 'AC Power Circuit Breaker', 'Circuit Breaker: AC Power circuit breaker.', 'nos', 580.00],
            ['Electrical', 'Full Project Electrical Wiring & Fitting Labor', 'Electric work wiring and fitting charges for full Project (Single Unit).', 'Job', 42000.00]
        ];

        $stmt = $pdo->prepare("INSERT INTO `app_estimate_catalog` (`category`, `item_name`, `specifications`, `unit`, `default_rate`) VALUES (?, ?, ?, ?, ?)");
        foreach ($seed as $row) {
            $stmt->execute($row);
        }
    }
}

ensureEstimateTables($pdo);

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// -------------------------------------------------------------
// 1. LIST ESTIMATES
// -------------------------------------------------------------
if ($action === 'list') {
    $q = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? '');

    $sql = "SELECT e.*, 
            (SELECT COUNT(*) FROM app_estimate_items WHERE estimate_id = e.id) as item_count,
            (SELECT COUNT(DISTINCT section_name) FROM app_estimate_items WHERE estimate_id = e.id) as section_count
            FROM app_estimates e WHERE 1=1";
    $params = [];

    if ($status !== '') {
        $sql .= " AND e.status = ?";
        $params[] = $status;
    }
    if ($q !== '') {
        $sql .= " AND (e.estimate_no LIKE ? OR e.client_name LIKE ? OR e.client_company LIKE ? OR e.subject LIKE ?)";
        $like = "%$q%";
        $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    }

    $sql .= " ORDER BY e.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats = [
        'total' => (int)$pdo->query("SELECT COUNT(*) FROM app_estimates")->fetchColumn(),
        'draft' => (int)$pdo->query("SELECT COUNT(*) FROM app_estimates WHERE status = 'draft'")->fetchColumn(),
        'sent' => (int)$pdo->query("SELECT COUNT(*) FROM app_estimates WHERE status = 'sent'")->fetchColumn(),
        'approved' => (int)$pdo->query("SELECT COUNT(*) FROM app_estimates WHERE status = 'approved'")->fetchColumn(),
        'converted' => (int)$pdo->query("SELECT COUNT(*) FROM app_estimates WHERE status = 'converted'")->fetchColumn(),
    ];

    echo json_encode(['success' => true, 'data' => $rows, 'stats' => $stats]);
    exit;
}

// -------------------------------------------------------------
// 2. GET SINGLE ESTIMATE WITH ITEMS & SECTIONS
// -------------------------------------------------------------
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM app_estimates WHERE id = ?");
    $stmt->execute([$id]);
    $estimate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estimate) {
        echo json_encode(['success' => false, 'message' => 'Estimate not found.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM app_estimate_items WHERE estimate_id = ? ORDER BY section_order ASC, sl_no ASC, id ASC");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group items by section (keyed by section_order + section_name to preserve exact order and allow duplicates)
    $sections = [];
    foreach ($items as $it) {
        $secKey = $it['section_order'] . '_' . $it['section_name'];
        if (!isset($sections[$secKey])) {
            $sections[$secKey] = [
                'name' => $it['section_name'],
                'order' => (int)$it['section_order'],
                'items' => [],
                'subtotal' => 0.0
            ];
        }
        if (($it['description'] ?? '') === '__SECTION_PLACEHOLDER__') {
            continue;
        }
        $it['quantity'] = (float)$it['quantity'];
        $it['unit_price'] = (float)$it['unit_price'];
        $it['amount'] = (float)$it['amount'];
        $sections[$secKey]['items'][] = $it;
        $sections[$secKey]['subtotal'] += $it['amount'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'estimate' => $estimate,
            'sections' => array_values($sections),
            'items' => $items,
            'id' => (int)$estimate['id'],
            'estimate_no' => $estimate['estimate_no'],
            'client_name' => $estimate['client_name'],
            'client_company' => $estimate['client_company'],
            'client_address' => $estimate['client_address'],
            'client_phone' => $estimate['client_phone'],
            'subject' => $estimate['subject'],
            'status' => $estimate['status']
        ]
    ]);
    exit;
}

// -------------------------------------------------------------
// 3. SAVE / AUTOSAVE ESTIMATE (CREATE OR UPDATE)
// -------------------------------------------------------------
if ($action === 'save') {
    $raw = file_get_contents('php://input');
    $post = json_decode($raw, true) ?: $_POST;

    $id                = (int)($post['id'] ?? 0);
    $client_name       = trim($post['client_name'] ?? '');
    $client_designation= trim($post['client_designation'] ?? '');
    $client_company    = trim($post['client_company'] ?? '');
    $client_address    = trim($post['client_address'] ?? '');
    $client_phone      = trim($post['client_phone'] ?? '');
    $subject           = trim($post['subject'] ?? '');
    $project_type      = in_array($post['project_type'] ?? '', ['commercial','residential']) ? $post['project_type'] : 'commercial';
    $group_by          = in_array($post['group_by'] ?? '', ['category','room']) ? $post['group_by'] : 'category';
    $working_days      = trim($post['working_days'] ?? '25-30 working days');
    $advance_pct       = (float)($post['advance_pct'] ?? 60.0);
    $running_pct       = (float)($post['running_pct'] ?? 30.0);
    $final_pct         = (float)($post['final_pct'] ?? 10.0);
    $discount_amount   = (float)($post['discount_amount'] ?? 0.0);
    $status            = in_array($post['status'] ?? '', ['draft','sent','approved','converted']) ? $post['status'] : 'draft';
    $notes             = trim($post['notes'] ?? '');
    $prepared_by       = trim($post['prepared_by'] ?? 'Md. Rukonuzzaman');
    $approved_by       = trim($post['approved_by'] ?? 'Md. Mustafizur Rahman');
    $prepared_by_desig = trim($post['prepared_by_designation'] ?? 'Interior Designer');
    $approved_by_desig = trim($post['approved_by_designation'] ?? 'Managing Director');
    $sectionsData      = $post['sections'] ?? [];

    if (empty($client_name)) {
        echo json_encode(['success' => false, 'message' => 'Client name is required.']);
        exit;
    }
    if (empty($subject)) {
        $subject = "Agreement for " . ($project_type === 'commercial' ? 'Commercial' : 'Residential') . " Space Interior works";
    }

    $pdo->beginTransaction();
    try {
        if ($id <= 0) {
            // Generate unique estimate number e.g. EST-2026-001
            $year = date('Y');
            $stmt = $pdo->query("SELECT MAX(id) FROM app_estimates");
            $nextId = ((int)$stmt->fetchColumn()) + 1;
            $estimate_no = 'EST-' . $year . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("INSERT INTO app_estimates 
                (estimate_no, client_name, client_designation, client_company, client_address, client_phone, subject, project_type, group_by, working_days, advance_pct, running_pct, final_pct, discount_amount, status, notes, prepared_by, approved_by, prepared_by_designation, approved_by_designation, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $estimate_no, $client_name, $client_designation, $client_company, $client_address, $client_phone, $subject, $project_type, $group_by, $working_days, $advance_pct, $running_pct, $final_pct, $discount_amount, $status, $notes, $prepared_by, $approved_by, $prepared_by_desig, $approved_by_desig, ($_SESSION['user_id'] ?? 1)
            ]);
            $id = (int)$pdo->lastInsertId();
        } else {
            $stmt = $pdo->prepare("UPDATE app_estimates SET
                client_name=?, client_designation=?, client_company=?, client_address=?, client_phone=?, subject=?, project_type=?, group_by=?, working_days=?, advance_pct=?, running_pct=?, final_pct=?, discount_amount=?, status=?, notes=?, prepared_by=?, approved_by=?, prepared_by_designation=?, approved_by_designation=?
                WHERE id=?");
            $stmt->execute([
                $client_name, $client_designation, $client_company, $client_address, $client_phone, $subject, $project_type, $group_by, $working_days, $advance_pct, $running_pct, $final_pct, $discount_amount, $status, $notes, $prepared_by, $approved_by, $prepared_by_desig, $approved_by_desig, $id
            ]);
            $stmtNo = $pdo->prepare("SELECT estimate_no FROM app_estimates WHERE id = ?");
            $stmtNo->execute([$id]);
            $estimate_no = $stmtNo->fetchColumn();
        }

        // Replace all items
        $pdo->prepare("DELETE FROM app_estimate_items WHERE estimate_id = ?")->execute([$id]);

        $total_amount = 0.0;
        $insertItem = $pdo->prepare("INSERT INTO app_estimate_items 
            (estimate_id, section_name, section_order, sl_no, description, unit, quantity, unit_price, amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $secOrder = 1;
        foreach ($sectionsData as $sec) {
            $secName = trim($sec['name'] ?? 'General Work');
            $items = $sec['items'] ?? [];
            $validItemsCount = 0;
            $sl = 1;
            foreach ($items as $it) {
                $desc = trim($it['description'] ?? '');
                if ($desc === '' || $desc === '__SECTION_PLACEHOLDER__') continue;
                $unit = trim($it['unit'] ?? 'S.ft');
                $qty = (float)($it['quantity'] ?? 0.0);
                $rate = (float)($it['unit_price'] ?? 0.0);
                $amt = round($qty * $rate);
                $total_amount += $amt;

                $insertItem->execute([$id, $secName, $secOrder, $sl, $desc, $unit, $qty, $rate, $amt]);
                $sl++;
                $validItemsCount++;
            }

            // If section has no valid items, preserve section via placeholder
            if ($validItemsCount === 0) {
                $insertItem->execute([$id, $secName, $secOrder, 1, '__SECTION_PLACEHOLDER__', 'S.ft', 0, 0, 0]);
            }
            $secOrder++;
        }

        $grand_total = max(0.0, $total_amount - $discount_amount);
        $pdo->prepare("UPDATE app_estimates SET total_amount = ?, grand_total = ? WHERE id = ?")
            ->execute([$total_amount, $grand_total, $id]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Estimate saved successfully.',
            'data' => [
                'id' => $id,
                'estimate_no' => $estimate_no,
                'total_amount' => $total_amount,
                'grand_total' => $grand_total
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 4. DUPLICATE ESTIMATE
// -------------------------------------------------------------
if ($action === 'duplicate') {
    $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
    $stmt = $pdo->prepare("SELECT * FROM app_estimates WHERE id = ?");
    $stmt->execute([$id]);
    $src = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$src) {
        echo json_encode(['success' => false, 'message' => 'Estimate not found.']);
        exit;
    }

    $pdo->beginTransaction();
    try {
        $year = date('Y');
        $stmt = $pdo->query("SELECT MAX(id) FROM app_estimates");
        $nextId = ((int)$stmt->fetchColumn()) + 1;
        $newNo = 'EST-' . $year . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO app_estimates 
            (estimate_no, client_name, client_designation, client_company, client_address, client_phone, subject, project_type, group_by, working_days, advance_pct, running_pct, final_pct, total_amount, discount_amount, grand_total, status, notes, prepared_by, approved_by, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?, ?, ?)");
        $stmt->execute([
            $newNo, $src['client_name'] . ' (Copy)', $src['client_designation'], $src['client_company'], $src['client_address'], $src['client_phone'], $src['subject'], $src['project_type'], $src['group_by'], $src['working_days'], $src['advance_pct'], $src['running_pct'], $src['final_pct'], $src['total_amount'], $src['discount_amount'], $src['grand_total'], $src['notes'], $src['prepared_by'], $src['approved_by'], ($_SESSION['user_id'] ?? 1)
        ]);
        $newId = (int)$pdo->lastInsertId();

        $items = $pdo->prepare("SELECT * FROM app_estimate_items WHERE estimate_id = ?");
        $items->execute([$id]);
        $ins = $pdo->prepare("INSERT INTO app_estimate_items (estimate_id, section_name, section_order, sl_no, description, unit, quantity, unit_price, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        while ($it = $items->fetch(PDO::FETCH_ASSOC)) {
            $ins->execute([$newId, $it['section_name'], $it['section_order'], $it['sl_no'], $it['description'], $it['unit'], $it['quantity'], $it['unit_price'], $it['amount']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Estimate duplicated.', 'new_id' => $newId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to duplicate: ' . $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 5. DELETE ESTIMATE
// -------------------------------------------------------------
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
    $pdo->prepare("DELETE FROM app_estimate_items WHERE estimate_id = ?")->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM app_estimates WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Estimate deleted.']);
    exit;
}

// -------------------------------------------------------------
// 6. CONVERT ESTIMATE TO ACTIVE PROJECT
// -------------------------------------------------------------
if ($action === 'convert_to_project') {
    $raw = file_get_contents('php://input');
    $post = json_decode($raw, true) ?: $_POST;
    $id = (int)($post['id'] ?? ($_GET['id'] ?? 0));

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid estimate ID.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM app_estimates WHERE id = ?");
    $stmt->execute([$id]);
    $est = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$est) {
        echo json_encode(['success' => false, 'message' => 'Estimate not found.']);
        exit;
    }

    if (!empty($est['converted_project_id'])) {
        echo json_encode(['success' => true, 'message' => 'Estimate is already converted.', 'project_id' => $est['converted_project_id']]);
        exit;
    }

    $pdo->beginTransaction();
    try {
        $projName    = $est['subject'] ?: ($est['client_company'] ? ($est['client_company'] . ' - ' . $est['client_name']) : $est['client_name'] . ' Interior Project');
        $clientName  = $est['client_name'] . ($est['client_company'] ? ' (' . $est['client_company'] . ')' : '');
        $clientPhone = $est['client_phone'] ?? '-';
        $address     = $est['client_address'] ?: 'Site Location';
        $budget      = (float)($est['grand_total'] ?? 0.0);
        $today       = date('Y-m-d');

        $ins = $pdo->prepare("INSERT INTO app_projects (name, address, client_name, client_phone, client_address, project_type, estimated_budget, status, start_date) VALUES (?, ?, ?, ?, ?, 'Commercial', ?, 'Ongoing', ?)");
        $ins->execute([$projName, $address, $clientName, $clientPhone, $address, $budget, $today]);
        $projectId = (int)$pdo->lastInsertId();

        $pdo->prepare("UPDATE app_estimates SET status = 'converted', converted_project_id = ? WHERE id = ?")
            ->execute([$projectId, $id]);

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Estimate converted to active project successfully!',
            'project_id' => $projectId
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Conversion failed: ' . $e->getMessage()]);
    }
    exit;
}

// -------------------------------------------------------------
// 7. GET CATALOG TEMPLATES (GROUPED OR SEARCH)
// -------------------------------------------------------------
if ($action === 'catalog') {
    $cat = trim($_GET['category'] ?? '');
    $q = trim($_GET['q'] ?? '');

    $sql = "SELECT * FROM app_estimate_catalog WHERE 1=1";
    $params = [];
    if ($cat !== '' && $cat !== 'All') {
        $sql .= " AND category = ?";
        $params[] = $cat;
    }
    if ($q !== '') {
        $sql .= " AND (item_name LIKE ? OR specifications LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }
    $sql .= " ORDER BY category ASC, item_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $items]);
    exit;
}

// -------------------------------------------------------------
// 8. GET PERSISTENT CATEGORIES
// -------------------------------------------------------------
if ($action === 'categories') {
    $cats = $pdo->query("
        SELECT name FROM `app_estimate_categories`
        UNION
        SELECT DISTINCT category AS name FROM `app_estimate_catalog` WHERE category IS NOT NULL AND category != ''
        ORDER BY name ASC
    ")->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success' => true, 'data' => array_values(array_filter($cats))]);
    exit;
}

// -------------------------------------------------------------
// 8.1 ADD NEW PERSISTENT CATEGORY
// -------------------------------------------------------------
if ($action === 'add_category') {
    $raw = file_get_contents('php://input');
    $post = json_decode($raw, true) ?: $_POST;
    $cat = trim($post['name'] ?? ($post['category'] ?? ''));

    if (empty($cat)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT IGNORE INTO `app_estimate_categories` (`name`) VALUES (?)");
    $stmt->execute([$cat]);

    echo json_encode(['success' => true, 'message' => 'Category created successfully.', 'category' => $cat]);
    exit;
}

// -------------------------------------------------------------
// 8.2 DELETE PERSISTENT CATEGORY
// -------------------------------------------------------------
if ($action === 'delete_category') {
    $raw = file_get_contents('php://input');
    $post = json_decode($raw, true) ?: $_POST;
    $cat = trim($post['name'] ?? ($post['category'] ?? ''));

    if (empty($cat)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM `app_estimate_categories` WHERE `name` = ?");
    $stmt->execute([$cat]);

    echo json_encode(['success' => true, 'message' => 'Category deleted.']);
    exit;
}

// -------------------------------------------------------------
// 9. SAVE CATALOG ITEM (CREATE OR UPDATE)
// -------------------------------------------------------------
if ($action === 'save_catalog_item') {
    $raw = file_get_contents('php://input');
    $post = json_decode($raw, true) ?: $_POST;

    $id       = (int)($post['id'] ?? 0);
    $cat      = trim($post['category'] ?? '');
    $itemName = trim($post['item_name'] ?? '');
    $specs    = trim($post['specifications'] ?? ($post['description'] ?? ''));
    $unit     = trim($post['unit'] ?? 'S.ft');
    $rate     = (float)($post['default_rate'] ?? ($post['unit_price'] ?? 0.0));

    if (empty($cat) || empty($itemName)) {
        echo json_encode(['success' => false, 'message' => 'Category and Item name are required.']);
        exit;
    }

    if (empty($specs)) $specs = $itemName;

    // Ensure category is in persistent table
    $catStmt = $pdo->prepare("INSERT IGNORE INTO `app_estimate_categories` (`name`) VALUES (?)");
    $catStmt->execute([$cat]);

    if ($id <= 0) {
        $stmt = $pdo->prepare("INSERT INTO app_estimate_catalog (category, item_name, specifications, unit, default_rate) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cat, $itemName, $specs, $unit, $rate]);
        $id = (int)$pdo->lastInsertId();
    } else {
        $stmt = $pdo->prepare("UPDATE app_estimate_catalog SET category=?, item_name=?, specifications=?, unit=?, default_rate=? WHERE id=?");
        $stmt->execute([$cat, $itemName, $specs, $unit, $rate, $id]);
    }

    echo json_encode(['success' => true, 'message' => 'Catalog item saved.', 'id' => $id]);
    exit;
}

// -------------------------------------------------------------
// 10. DELETE CATALOG ITEM
// -------------------------------------------------------------
if ($action === 'delete_catalog_item') {
    $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
    $pdo->prepare("DELETE FROM app_estimate_catalog WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Item deleted from catalog.']);
    exit;
}



echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;

