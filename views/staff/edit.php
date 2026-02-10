<?php
$pageTitle = "Sửa nhân viên";
require_once '../../includes/header.php';
require_once '../../models/Staff.php';

$staffModel = new Staff();
$id = intval($_GET['id'] ?? 0);
$staff = $staffModel->getById($id);

if (!$staff) {
    echo '<div class="alert alert-danger">Không tìm thấy nhân viên</div>';
    exit;
}

$constraints = $staffModel->getConstraints($id);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-edit"></i> Sửa thông tin nhân viên</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin cơ bản</h5>
            </div>
            <div class="card-body">
                <form id="editStaffForm">
                    <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" 
                               value="<?php echo htmlspecialchars($staff['name']); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số ca tối đa/tuần</label>
                            <input type="number" class="form-control" name="max_shifts_per_week" 
                                   value="<?php echo $staff['max_shifts_per_week']; ?>" min="1">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số ca tối đa/tháng</label>
                            <input type="number" class="form-control" name="max_shifts_per_month" 
                                   value="<?php echo $staff['max_shifts_per_month']; ?>" min="1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="is_active">
                            <option value="1" <?php echo $staff['is_active'] ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="0" <?php echo !$staff['is_active'] ? 'selected' : ''; ?>>Không hoạt động</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ràng buộc</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button class="btn btn-sm btn-success w-100" data-bs-toggle="modal" data-bs-target="#addConstraintModal">
                        <i class="fas fa-plus"></i> Thêm ràng buộc
                    </button>
                </div>
                
                <?php if (empty($constraints)): ?>
                    <p class="text-muted">Chưa có ràng buộc nào</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($constraints as $constraint): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo $constraint['constraint_type']; ?></strong><br>
                                        <small><?php echo $constraint['constraint_value']; ?></small>
                                    </div>
                                    <button onclick="deleteConstraint(<?php echo $constraint['id']; ?>)" 
                                            class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Constraint Modal -->
<div class="modal fade" id="addConstraintModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm ràng buộc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addConstraintForm">
                    <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Loại ràng buộc</label>
                        <select class="form-select" name="type" required>
                            <option value="day_off">Ngày nghỉ</option>
                            <option value="avoid_shift">Tránh ca</option>
                            <option value="prefer_shift">Ưu tiên ca</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Giá trị</label>
                        <input type="text" class="form-control" name="value" 
                               placeholder="VD: 2026-02-15 hoặc SUNDAY_MORNING" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Thêm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Edit staff form handler
    $('#editStaffForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/controllers/staff_controller.php',
            method: 'POST',
            data: $(this).serialize() + '&action=update',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Thành công!', response.message, 'success')
                        .then(() => window.location.href = 'list.php');
                } else {
                    Swal.fire('Lỗi!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Response:', xhr.responseText);
                Swal.fire('Lỗi!', 'Có lỗi xảy ra: ' + error, 'error');
            }
        });
    });
    
    // Add constraint form handler
    $('#addConstraintForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/controllers/staff_controller.php',
            method: 'POST',
            data: $(this).serialize() + '&action=add_constraint',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Thành công!', response.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Lỗi!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire('Lỗi!', 'Có lỗi xảy ra: ' + error, 'error');
            }
        });
    });
    
    // Delete constraint function
    window.deleteConstraint = function(id) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/controllers/staff_controller.php',
                    method: 'POST',
                    data: { action: 'delete_constraint', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Thành công!', response.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Lỗi!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Lỗi!', 'Có lỗi xảy ra: ' + error, 'error');
                    }
                });
            }
        });
    };
});
</script>
