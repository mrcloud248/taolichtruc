<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Export.php';

$exportModel = new Export();

$action = $_GET['action'] ?? '';

if ($action == 'csv') {
    $scheduleId = intval($_GET['schedule_id']);
    
    $filename = $exportModel->exportToCSV($scheduleId);
    
    if ($filename) {
        $filepath = EXPORT_DIR . $filename;
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        
        // Delete file after download
        unlink($filepath);
        exit;
    } else {
        die('Không thể export file');
    }
}
?>
