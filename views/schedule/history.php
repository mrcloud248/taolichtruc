<?php
$pageTitle = "Lịch sử lịch trực";
require_once '../../includes/header.php';
require_once '../../models/Schedule.php';

$scheduleModel = new Schedule();
$schedules = $scheduleModel->getAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-history"></i> Lịch sử lịch trực</h2>
            <a href="generate.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo lịch mới
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($schedules)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Chưa có lịch trực nào. 
                        <a href="generate.php">Tạo lịch mới</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Loại</th>
                                    <th>Tháng/Năm</th>
                                    <th>Trạng thái</th>
                                    <th>Người tạo</th>
                                    <th>Ngày tạo</th>
                                    <th>Ghi chú</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td><?php echo $schedule['id']; ?></td>
                                        <td>
                                            <?php if ($schedule['schedule_type'] == 'daily'): ?>
                                                <span class="badge bg-primary">Lịch ngày</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Lịch tuần</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $schedule['month'] . '/' . $schedule['year']; ?></td>
                                        <td>
                                            <?php
                                            $statusClass = ['draft' => 'warning', 'published' => 'success', 'archived' => 'secondary'];
                                            $statusText = ['draft' => 'Nháp', 'published' => 'Đã xuất bản', 'archived' => 'Lưu trữ'];
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass[$schedule['status']]; ?>">
                                                <?php echo $statusText[$schedule['status']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($schedule['generated_by']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($schedule['generated_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($schedule['notes'] ?? ''); ?></td>
                                        <td>
                                            <?php if ($schedule['schedule_type'] == 'daily'): ?>
                                                <a href="daily_calendar.php?id=<?php echo $schedule['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Xem">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="weekly_list.php?id=<?php echo $schedule['id']; ?>" 
                                                   class="btn btn-sm btn-success" title="Xem">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?php echo BASE_URL; ?>/controllers/export_controller.php?action=csv&schedule_id=<?php echo $schedule['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Export">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            
                                            <button onclick="deleteSchedule(<?php echo $schedule['id']; ?>)" 
                                                    class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    window.deleteSchedule = function(id) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            text: "Bạn có chắc muốn xóa lịch trực này?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/controllers/schedule_controller.php',
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
