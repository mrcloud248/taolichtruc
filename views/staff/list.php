<?php
$pageTitle = "Danh sách nhân viên";
require_once '../../includes/header.php';
require_once '../../models/Staff.php';

$staffModel = new Staff();
$staffList = $staffModel->getAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users"></i> Danh sách nhân viên</h2>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm nhân viên
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="staffTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Ca/tuần</th>
                                <th>Ca/tháng</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staffList as $staff): ?>
                                <tr>
                                    <td><?php echo $staff['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($staff['name']); ?></strong></td>
                                    <td><?php echo $staff['max_shifts_per_week']; ?></td>
                                    <td><?php echo $staff['max_shifts_per_month']; ?></td>
                                    <td>
                                        <?php if ($staff['is_active']): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Không hoạt động</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $staff['id']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteStaff(<?php echo $staff['id']; ?>)" 
                                                class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    window.deleteStaff = function(id) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            text: "Bạn có chắc muốn xóa nhân viên này?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/controllers/staff_controller.php',
                    method: 'POST',
                    data: { action: 'delete', id: id },
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
