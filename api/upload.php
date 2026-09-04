<?php
error_reporting(0);
ini_set('display_errors', 0);
// api/upload.php â€” Project image upload
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'project_image';

try {
    switch ($action) {
        case 'project_image':
            $project_id = intval($_POST['project_id'] ?? 0);
            if (!$project_id) { echo json_encode(['success'=>false,'message'=>'Project ID required.']); exit; }
            if (!isset($_FILES['image'])) { echo json_encode(['success'=>false,'message'=>'No file uploaded.']); exit; }
            $file = $_FILES['image'];
                        if ($file['error'] !== UPLOAD_ERR_OK) { 
                echo json_encode(['success'=>false,'message'=>'Upload error code: ' . $file['error']]); exit; 
            }
            $mime_map = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'
            ];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo);
            if (!isset($mime_map[$mime])) { echo json_encode(['success'=>false,'message'=>'Invalid file type. Use JPG, PNG or WebP.']); exit; }
            if (!@getimagesize($file['tmp_name']) && $mime !== 'image/webp') { echo json_encode(['success'=>false,'message'=>'Corrupt or invalid image file.']); exit; }
            if ($file['size'] > 3 * 1024 * 1024) { echo json_encode(['success'=>false,'message'=>'Image too large (max 3MB).']); exit; }
            $dir = __DIR__ . '/../uploads/projects/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $ext      = $mime_map[$mime];
            $filename = 'proj_' . $project_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest     = $dir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                echo json_encode(['success'=>false,'message'=>'Failed to save image.']); exit;
            }
            $web_path = 'uploads/projects/' . $filename;
            // Update app_projects.project_image
            $pdo->prepare("UPDATE app_projects SET project_image=? WHERE id=?")->execute([$web_path,$project_id]);
            // Insert into app_project_images
            $is_primary = intval($_POST['is_primary'] ?? 0);
            if ($is_primary) {
                // Clear existing primary
                $pdo->prepare("UPDATE app_project_images SET is_primary=0 WHERE project_id=?")->execute([$project_id]);
            }
            $pdo->prepare("INSERT INTO app_project_images (project_id,image_path,is_primary,created_at) VALUES (?,?,?,NOW())")->execute([$project_id,$web_path,$is_primary]);
            echo json_encode(['success'=>true,'message'=>'Image uploaded.','path'=>$web_path]);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}

