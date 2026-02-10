<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../includes/functions.php';

$staffModel = new Staff();

// Handle AJAX requests
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $data = [
                'name' => sanitize($_POST['name']),
                'max_shifts_per_week' => intval($_POST['max_shifts_per_week']),
                'max_shifts_per_month' => intval($_POST['max_shifts_per_month'])
            ];
            
            if ($staffModel->create($data)) {
                jsonResponse(true, 'Thêm nhân viên thành công');
            } else {
                jsonResponse(false, 'Có lỗi xảy ra');
            }
            break;
            
        case 'update':
            $id = intval($_POST['id']);
            $data = [
                'name' => sanitize($_POST['name']),
                'max_shifts_per_week' => intval($_POST['max_shifts_per_week']),
                'max_shifts_per_month' => intval($_POST['max_shifts_per_month']),
                'is_active' => intval($_POST['is_active'])
            ];
            
            if ($staffModel->update($id, $data)) {
                jsonResponse(true, 'Cập nhật thành công');
            } else {
                jsonResponse(false, 'Có lỗi xảy ra');
            }
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            
            if ($staffModel->delete($id)) {
                jsonResponse(true, 'Xóa nhân viên thành công');
            } else {
                jsonResponse(false, 'Có lỗi xảy ra');
            }
            break;
            
        case 'get':
            $id = intval($_GET['id']);
            $staff = $staffModel->getById($id);
            
            if ($staff) {
                jsonResponse(true, 'Success', $staff);
            } else {
                jsonResponse(false, 'Không tìm thấy nhân viên');
            }
            break;
            
        case 'add_constraint':
            $data = [
                'staff_id' => intval($_POST['staff_id']),
                'type' => sanitize($_POST['type']),
                'value' => sanitize($_POST['value']),
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null
            ];
            
            if ($staffModel->addConstraint($data['staff_id'], $data['type'], $data['value'], 
                                          $data['start_date'], $data['end_date'])) {
                jsonResponse(true, 'Thêm ràng buộc thành công');
            } else {
                jsonResponse(false, 'Có lỗi xảy ra');
            }
            break;
            
        case 'delete_constraint':
            $id = intval($_POST['id']);
            
            if ($staffModel->deleteConstraint($id)) {
                jsonResponse(true, 'Xóa ràng buộc thành công');
            } else {
                jsonResponse(false, 'Có lỗi xảy ra');
            }
            break;
            
        default:
            jsonResponse(false, 'Invalid action');
    }
}
?>
