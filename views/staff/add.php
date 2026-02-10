<?php
$pageTitle = "Thêm nhân viên";
require_once '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-plus"></i> Thêm nhân viên mới</h2>
            <a href="list.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <form id="addStaffForm">
                    <div class="mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="VD: Nguyễn Văn A">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số ca tối đa/tuần</label>
                            <input type="number" class="form-control" name="max_shifts_per_week" value="5" min="1">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số ca tối đa/tháng</label>
                            <input type="number" class="form-control" name="max_shifts_per_month" value="20" min="1">
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#addStaffForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/controllers/staff_controller.php',
            method: 'POST',
            data: $(this).serialize() + '&action=create',
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
                Swal.fire('Lỗi!', 'Có lỗi xảy ra: ' + error, 'error');
            }
        });
    });
});
</script>
