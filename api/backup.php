<?php
// api/backup.php — Full Backup, Project Backup, Preview and Restore Engine
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$action = $_GET['action'] ?? '';

try {
    if ($action === 'full_backup') {
        if (!verifyAdminAction()) { die('Unauthorized - Admin password required'); }
        $tables = [
            'app_categories',
            'app_contractors',
            'app_workers',
            'app_projects',
            'app_project_contractors',
            'app_supply_purchases',
            'app_contractor_advances',
            'app_contractor_bills',
            'app_thai_glass_bills',
            'app_attendance',
            'app_worker_payments',
            'app_client_payments',
            'app_expenses',
            'app_schedules',
            'app_notifications'
        ];
        
        $backupData = [
            'version'    => '2.0',
            'type'       => 'full',
            'created_at' => date('c'),
            'app'        => 'Lily Interiors Profix',
            'data'       => []
        ];
        
        foreach ($tables as $t) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$t]);
            if ($stmt->fetch()) {
                $rows = $pdo->query("SELECT * FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
                $backupData['data'][$t] = $rows;
            }
        }
        
        $filename = 'profix_full_backup_' . date('Y-m-d_H-i-s') . '.json';
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($action === 'project_backup') {
        if (!verifyAdminAction()) { die('Unauthorized - Admin password required'); }
        $pid = intval($_GET['project_id'] ?? 0);
        if (!$pid) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Project ID required']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM app_projects WHERE id=? AND is_deleted=0");
        $stmt->execute([$pid]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            exit;
        }
        
        $data = [
            'project'             => $project,
            'contractors'         => [],
            'purchases'           => [],
            'contractor_advances' => [],
            'contractor_bills'    => [],
            'thai_glass_bills'    => [],
            'attendance'          => [],
            'worker_payments'     => [],
            'client_payments'     => [],
            'expenses'            => [],
            'schedules'           => []
        ];
        
        // Project Contractors
        $q = $pdo->prepare("SELECT pc.*, c.name, c.phone, c.trade FROM app_project_contractors pc JOIN app_contractors c ON c.id=pc.contractor_id WHERE pc.project_id=?");
        $q->execute([$pid]);
        $data['contractors'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Purchases
        $q = $pdo->prepare("SELECT * FROM app_supply_purchases WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['purchases'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Contractor Advances
        $q = $pdo->prepare("SELECT * FROM app_contractor_advances WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['contractor_advances'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Contractor Bills
        $q = $pdo->prepare("SELECT * FROM app_contractor_bills WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['contractor_bills'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Thai Glass Bills
        $q = $pdo->prepare("SELECT * FROM app_thai_glass_bills WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['thai_glass_bills'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Attendance
        $q = $pdo->prepare("SELECT * FROM app_attendance WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['attendance'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Worker Payments
        $q = $pdo->prepare("SELECT * FROM app_worker_payments WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['worker_payments'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Client Payments
        $q = $pdo->prepare("SELECT * FROM app_client_payments WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['client_payments'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Expenses
        $q = $pdo->prepare("SELECT * FROM app_expenses WHERE project_id=? AND is_deleted=0");
        $q->execute([$pid]);
        $data['expenses'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // Schedules
        $q = $pdo->prepare("SELECT * FROM app_schedules WHERE project_id=?");
        $q->execute([$pid]);
        $data['schedules'] = $q->fetchAll(PDO::FETCH_ASSOC);
        
        $backupData = [
            'version'    => '2.0',
            'type'       => 'project',
            'created_at' => date('c'),
            'app'        => 'Lily Interiors Profix',
            'data'       => $data
        ];
        
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $project['name']);
        $filename = 'project_' . $pid . '_' . $safeName . '_backup_' . date('Y-m-d') . '.json';
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    header('Content-Type: application/json; charset=utf-8');
    
    if ($action === 'check_restore') {
        if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (!$json || !isset($json['version'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid backup format']);
            exit;
        }
        
        $conflicts = [];
        $newRecords = [];
        
        if (($json['type'] ?? '') === 'project') {
            $proj = $json['data']['project'] ?? null;
            if ($proj && !empty($proj['name'])) {
                $stmt = $pdo->prepare("SELECT id, name FROM app_projects WHERE name=? AND is_deleted=0");
                $stmt->execute([$proj['name']]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $conflicts[] = ['name' => $proj['name'] . ' (Project already exists)'];
                } else {
                    $newRecords[] = ['name' => $proj['name'] . ' (New Project)'];
                }
            }
        } elseif (($json['type'] ?? '') === 'full') {
            $projects = $json['data']['app_projects'] ?? [];
            foreach ($projects as $p) {
                if (empty($p['name'])) continue;
                $stmt = $pdo->prepare("SELECT id FROM app_projects WHERE name=? AND is_deleted=0");
                $stmt->execute([$p['name']]);
                if ($stmt->fetch()) {
                    $conflicts[] = ['name' => $p['name'] . ' (Project)'];
                } else {
                    $newRecords[] = ['name' => $p['name'] . ' (Project)'];
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'data'    => [
                'conflicts'   => $conflicts,
                'new_records' => $newRecords
            ]
        ]);
        exit;
    }
    
    if ($action === 'restore_project' || $action === 'restore_full') {
        if (!verifyAdminAction()) { echo json_encode(['success'=>false,'message'=>'Unauthorized - Admin password required']); exit; }
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (!$json || !isset($json['version'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid backup format']);
            exit;
        }

        $log = [];
        $pdo->beginTransaction();
        try {
            do_restore($json, $log, $pdo);
            $pdo->commit();
            $log[] = "Restore completed successfully.";
            echo json_encode(['success' => true, 'message' => 'Restore completed', 'log' => $log]);
        } catch (Exception $re) {
            $pdo->rollBack();
            $log[] = "Error: " . $re->getMessage();
            echo json_encode(['success' => false, 'message' => $re->getMessage(), 'log' => $log]);
        }
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// ============================================================
// do_restore — shared project-restore engine used by
// 'restore_project'/'restore_full'.
// Calls must wrap in beginTransaction/commit and handle rollback.
// ============================================================
function do_restore($json, &$log, $pdo) {
    if (($json['type'] ?? '') === 'project')
    {
            $pData = $json['data'] ?? [];
            $proj = $pData['project'] ?? null;
            if (!$proj) {
                throw new Exception('Missing project definition');
            }
            
            // Check if project already exists
            $stmt = $pdo->prepare("SELECT id FROM app_projects WHERE name=? AND is_deleted=0");
            $stmt->execute([$proj['name']]);
            $existingId = $stmt->fetchColumn();
            
            if ($existingId) {
                $newPid = $existingId;
                $log[] = "Project '{$proj['name']}' already exists. Merging records into Project ID: {$newPid}.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO app_projects
                    (name, address, client_name, client_phone, client_email, client_address, project_type, status, estimated_budget, start_date, end_date, project_image, notes, created_by, created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $proj['name'],
                    $proj['address'] ?? '',
                    $proj['client_name'] ?? '',
                    $proj['client_phone'] ?? '',
                    $proj['client_email'] ?? '',
                    $proj['client_address'] ?? '',
                    $proj['project_type'] ?? 'Residential',
                    $proj['status'] ?? 'Ongoing',
                    $proj['estimated_budget'] ?? 0,
                    $proj['start_date'] ?? date('Y-m-d'),
                    $proj['end_date'] ?? null,
                    $proj['project_image'] ?? null,
                    $proj['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $newPid = $pdo->lastInsertId();
                $log[] = "Created new project '{$proj['name']}' with ID: {$newPid}.";
            }

            // Map old contractor id -> new id within this project (safe merge)
            $contractorMap = [];

            // Import Project Contractors (and their contractor rows if needed)
            $contractors = $pData['contractors'] ?? [];
            $cCount = 0;
            foreach ($contractors as $pc) {
                $cId = $pc['contractor_id'] ?? null;
                if (!$cId) continue;
                // Ensure the contractor exists in app_contractors
                if (!isset($contractorMap[$cId])) {
                    $stmt = $pdo->prepare("SELECT id FROM app_contractors WHERE name=? AND trade=? LIMIT 1");
                    $stmt->execute([$pc['name'] ?? '', $pc['trade'] ?? '']);
                    $matchId = $stmt->fetchColumn();
                    if ($matchId) {
                        $contractorMap[$cId] = $matchId;
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO app_contractors (name, phone, address, trade, notes, is_active, created_at) VALUES (?,?,?,?,?,1,NOW())");
                        $stmt->execute([$pc['name'] ?? 'Contractor', $pc['phone'] ?? null, $pc['address'] ?? null, $pc['trade'] ?? '', $pc['notes'] ?? '']);
                        $contractorMap[$cId] = $pdo->lastInsertId();
                    }
                }
                // Check if assignment already exists
                $stmt = $pdo->prepare("SELECT id FROM app_project_contractors WHERE project_id=? AND contractor_id=?");
                $stmt->execute([$newPid, $contractorMap[$cId]]);
                if (!$stmt->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO app_project_contractors (project_id, contractor_id, category_id, start_date, notes, created_at) VALUES (?,?,?,?,?,NOW())");
                    $stmt->execute([$newPid, $contractorMap[$cId], $pc['category_id'] ?? null, $pc['start_date'] ?? null, $pc['notes'] ?? '']);
                    $cCount++;
                }
            }
            $log[] = "Imported {$cCount} project-contractor assignments.";

            $hasId = function($arr, $key) {
                return isset($arr[$key]) && $arr[$key] !== null && $arr[$key] !== '';
            };

            // Import Worker rows referenced by attendance/payments
            $workerMap = [];
            $workerCache = [];
            $ensureWorker = function($wid, $wname) use (&$workerMap, &$workerCache, $pdo) {
                if (!$wid) return null;
                if (isset($workerMap[$wid])) return $workerMap[$wid];
                $stmt = $pdo->prepare("SELECT id FROM app_workers WHERE name=? LIMIT 1");
                $stmt->execute([$wname]);
                $matchId = $stmt->fetchColumn();
                if ($matchId) {
                    $workerMap[$wid] = $matchId;
                    return $matchId;
                }
                $stmt = $pdo->prepare("INSERT INTO app_workers (name, trade, is_active, created_at) VALUES (?,?,1,NOW())");
                $stmt->execute([$wname ?: 'Worker', '']);
                $workerMap[$wid] = $pdo->lastInsertId();
                return $workerMap[$wid];
            };

            // Import Purchases
            $purchases = $pData['purchases'] ?? [];
            if (empty($purchases) && !empty($pData['app_supply_purchases'])) {
                $purchases = $pData['app_supply_purchases'];
            }
            $pCount = 0;
            foreach ($purchases as $item) {
                $cid = $item['contractor_id'] ?? null;
                $newCid = null;
                if ($hasId($item, 'contractor_id') && isset($contractorMap[$cid])) $newCid = $contractorMap[$cid];

                $stmt = $pdo->prepare("INSERT INTO app_supply_purchases
                    (project_id, contractor_id, item_name, supply_category, category_id, board_type, board_thickness, board_size, color_finish, size, quantity, unit, rate, total, supplier, purchased_by, purchase_date, notes, created_by, created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $newCid,
                    $item['item_name'] ?? 'Item',
                    $item['supply_category'] ?? 'Other',
                    $item['category_id'] ?? null,
                    $item['board_type'] ?? ($item['board_category'] ?? null),
                    $item['board_thickness'] ?? null,
                    $item['board_size'] ?? null,
                    $item['color_finish'] ?? ($item['color'] ?? null),
                    $item['size'] ?? null,
                    $item['quantity'] ?? 1,
                    $item['unit'] ?? 'pcs',
                    $item['rate'] ?? 0,
                    $item['total'] ?? 0,
                    $item['supplier'] ?? '',
                    $item['purchased_by'] ?? ($item['who_purchased'] ?? ''),
                    $item['purchase_date'] ?? date('Y-m-d'),
                    $item['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $pCount++;
            }
            $log[] = "Imported {$pCount} purchases.";
            
            // Import Contractor Advances
            $advances = $pData['contractor_advances'] ?? [];
            $aCount = 0;
            foreach ($advances as $adv) {
                $cid = $adv['contractor_id'] ?? null;
                $newCid = null;
                if ($hasId($adv, 'contractor_id') && isset($contractorMap[$cid])) $newCid = $contractorMap[$cid];
                $stmt = $pdo->prepare("INSERT INTO app_contractor_advances (project_id, contractor_id, amount, payment_method, who_paid, payment_date, notes, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $newCid,
                    $adv['amount'] ?? 0,
                    $adv['payment_method'] ?? 'Cash',
                    $adv['who_paid'] ?? 'Lily Interiors',
                    $adv['payment_date'] ?? date('Y-m-d'),
                    $adv['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $aCount++;
            }
            $log[] = "Imported {$aCount} contractor advances.";

            // Import Contractor Bills
            $bills = $pData['contractor_bills'] ?? [];
            $bCount = 0;
            foreach ($bills as $b) {
                $cid = $b['contractor_id'] ?? null;
                $newCid = null;
                if ($hasId($b, 'contractor_id') && isset($contractorMap[$cid])) $newCid = $contractorMap[$cid];
                $billData = isset($b['bill_data']) ? (is_string($b['bill_data']) ? $b['bill_data'] : json_encode($b['bill_data'])) : '[]';
                $stmt = $pdo->prepare("INSERT INTO app_contractor_bills (project_id, contractor_id, category_id, bill_data, grand_total, total_paid, balance_due, bill_date, bill_language, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $newCid,
                    $b['category_id'] ?? null,
                    $billData,
                    $b['grand_total'] ?? 0,
                    $b['total_paid'] ?? 0,
                    $b['balance_due'] ?? 0,
                    $b['bill_date'] ?? date('Y-m-d'),
                    $b['bill_language'] ?? 'en',
                    $_SESSION['user_id'] ?? 1
                ]);
                $bCount++;
            }
            $log[] = "Imported {$bCount} contractor bills.";
            
            // Import Attendance
            $att = $pData['attendance'] ?? [];
            $attCount = 0;
            foreach ($att as $rec) {
                $wid = $rec['worker_id'] ?? null;
                $newWid = $wid;
                if (isset($workerCache[$wid])) {
                    $newWid = $workerCache[$wid];
                } else {
                    $newWid = $ensureWorker($wid, $rec['worker_name'] ?? '');
                    $workerCache[$wid] = $newWid;
                }
                $stmt = $pdo->prepare("INSERT INTO app_attendance (project_id, worker_id, category_id, work_date, daily_rate, attendance_multiplier, earned, notes, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $newWid,
                    $rec['category_id'] ?? null,
                    $rec['work_date'] ?? date('Y-m-d'),
                    $rec['daily_rate'] ?? 0,
                    $rec['attendance_multiplier'] ?? ($rec['multiplier'] ?? 1),
                    $rec['earned'] ?? ($rec['earned_amount'] ?? 0),
                    $rec['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $attCount++;
            }
            $log[] = "Imported {$attCount} attendance records.";

            // Import Worker Payments
            $wp = $pData['worker_payments'] ?? [];
            $wCount = 0;
            foreach ($wp as $rec) {
                $wid = $rec['worker_id'] ?? null;
                $newWid = $wid;
                if (isset($workerCache[$wid])) {
                    $newWid = $workerCache[$wid];
                } else {
                    $newWid = $ensureWorker($wid, $rec['worker_name'] ?? '');
                    $workerCache[$wid] = $newWid;
                }
                $stmt = $pdo->prepare("INSERT INTO app_worker_payments (project_id, worker_id, amount, payment_date, payment_method, who_paid, who_received, notes, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $newWid,
                    $rec['amount'] ?? 0,
                    $rec['payment_date'] ?? date('Y-m-d'),
                    $rec['payment_method'] ?? 'Cash',
                    $rec['who_paid'] ?? 'Lily Interiors',
                    $rec['who_received'] ?? '',
                    $rec['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $wCount++;
            }
            $log[] = "Imported {$wCount} worker payments.";

            // Import Client Payments
            $cp = $pData['client_payments'] ?? [];
            $cpCount = 0;
            foreach ($cp as $rec) {
                $stmt = $pdo->prepare("INSERT INTO app_client_payments (project_id, amount, payment_date, payment_method, who_received, notes, created_by, created_at) VALUES (?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $rec['amount'] ?? 0,
                    $rec['payment_date'] ?? date('Y-m-d'),
                    $rec['payment_method'] ?? 'Cash',
                    $rec['who_received'] ?? '',
                    $rec['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $cpCount++;
            }
            $log[] = "Imported {$cpCount} client payments.";

            // Import Expenses
            $exp = $pData['expenses'] ?? [];
            $eCount = 0;
            foreach ($exp as $rec) {
                $stmt = $pdo->prepare("INSERT INTO app_expenses (project_id, category, description, vendor, amount, paid, payment_method, expense_date, notes, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $rec['category'] ?? null,
                    $rec['description'] ?? '',
                    $rec['vendor'] ?? '',
                    $rec['amount'] ?? 0,
                    $rec['paid'] ?? ($rec['amount'] ?? 0),
                    $rec['payment_method'] ?? 'Cash',
                    $rec['expense_date'] ?? date('Y-m-d'),
                    $rec['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $eCount++;
            }
            $log[] = "Imported {$eCount} expenses.";

            // Import Schedules
            $sch = $pData['schedules'] ?? [];
            $sCount = 0;
            foreach ($sch as $rec) {
                $stmt = $pdo->prepare("INSERT INTO app_schedules (project_id, schedule_date, category, description, is_done, created_by, created_at) VALUES (?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $rec['schedule_date'] ?? date('Y-m-d'),
                    $rec['category'] ?? null,
                    $rec['description'] ?? '',
                    $rec['is_done'] ?? 0,
                    $_SESSION['user_id'] ?? 1
                ]);
                $sCount++;
            }
            $log[] = "Imported {$sCount} schedules.";

            // Import Glass Advances (if present)
            $ga = $pData['glass_advances'] ?? [];
            $gCount = 0;
            foreach ($ga as $rec) {
                $cid = $rec['contractor_id'] ?? null;
                $newCid = null;
                if ($hasId($rec, 'contractor_id') && isset($contractorMap[$cid])) $newCid = $contractorMap[$cid];
                $stmt = $pdo->prepare("INSERT INTO app_glass_advances (project_id, contractor_id, amount, payment_date, payment_method, who_paid, who_received, notes, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())");
                $stmt->execute([
                    $newPid,
                    $newCid,
                    $rec['amount'] ?? 0,
                    $rec['payment_date'] ?? date('Y-m-d'),
                    $rec['payment_method'] ?? 'Cash',
                    $rec['who_paid'] ?? 'Lily Interiors',
                    $rec['who_received'] ?? '',
                    $rec['notes'] ?? '',
                    $_SESSION['user_id'] ?? 1
                ]);
                $gCount++;
            }
            $log[] = "Imported {$gCount} glass advances.";
    }
}