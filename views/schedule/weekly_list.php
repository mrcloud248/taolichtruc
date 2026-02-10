<?php
$pageTitle = "Lịch trực tuần";
require_once '../../includes/header.php';
require_once '../../models/Schedule.php';

$scheduleModel = new Schedule();
$scheduleId = intval($_GET['id'] ?? 0);

if ($scheduleId > 0) {
    $schedule = $scheduleModel->getById($scheduleId);
    $shifts = $scheduleModel->getShifts($scheduleId);
} else {
    // Get latest weekly schedule
    $schedules = $scheduleModel->getByTypeAndDate('weekly', date('n'), date('Y'));
    $schedule = $schedules[0] ?? null;
    $scheduleId = $schedule['id'] ?? 0;
    $shifts = $schedule ? $scheduleModel->getShifts($scheduleId) : [];
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list"></i> Lịch Trực Tuần (Thứ 7)</h2>
            <div>
                <?php if ($schedule): ?>
                    <?php
                    $secret = 'schedule_share_secret_2026';
                    $shareToken = md5($scheduleId . $secret);
                    $shareUrl = BASE_URL . '/public_schedule.php?id=' . $scheduleId . '&token=' . $shareToken;
                    ?>
                    <button class="btn btn-info" onclick="copyShareLink('<?php echo $shareUrl; ?>')">
                        <i class="fas fa-share-alt"></i> Chia sẻ
                    </button>
                    <a href="<?php echo BASE_URL; ?>/controllers/export_controller.php?action=csv&schedule_id=<?php echo $scheduleId; ?>" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export CSV
                    </a>
                <?php endif; ?>
                <a href="generate.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo lịch mới
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!$schedule): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Chưa có lịch trực tuần nào. 
        <a href="generate.php">Tạo lịch mới</a>
    </div>
<?php else: ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Loại lịch:</strong> <span class="badge bg-success">Lịch tuần</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Số nhân viên:</strong> <?php echo count($shifts); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Người tạo:</strong> <?php echo htmlspecialchars($schedule['generated_by']); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Ngày tạo:</strong> <?php echo date('d/m/Y', strtotime($schedule['generated_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Lịch Trực Thứ 7 (8:00 AM - 5:00 PM)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="80">STT</th>
                                    <th>Ngày</th>
                                    <th>Thứ</th>
                                    <th>Nhân viên</th>
                                    <th>Giờ trực</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stt = 1;
                                $currentWeek = null;
                                foreach ($shifts as $shift): 
                                    $weekNum = date('W', strtotime($shift['shift_date']));
                                    if ($weekNum != $currentWeek) {
                                        $currentWeek = $weekNum;
                                        $isCurrentWeek = (date('Y-m-d') >= $shift['shift_date'] && 
                                                         date('Y-m-d') <= date('Y-m-d', strtotime($shift['shift_date'] . ' +6 days')));
                                        $rowClass = $isCurrentWeek ? 'table-info' : '';
                                    }
                                ?>
                                    <tr class="<?php echo $rowClass ?? ''; ?>">
                                        <td><?php echo $stt++; ?></td>
                                        <td>
                                            <strong><?php echo date('d/m/Y', strtotime($shift['shift_date'])); ?></strong>
                                            <?php if ($isCurrentWeek ?? false): ?>
                                                <span class="badge bg-info ms-2">Tuần này</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $days = ['Monday' => 'Thứ 2', 'Tuesday' => 'Thứ 3', 'Wednesday' => 'Thứ 4',
                                                    'Thursday' => 'Thứ 5', 'Friday' => 'Thứ 6', 'Saturday' => 'Thứ 7', 'Sunday' => 'Chủ nhật'];
                                            echo $days[date('l', strtotime($shift['shift_date']))];
                                            ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-user"></i> 
                                            <strong><?php echo htmlspecialchars($shift['staff_name']); ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('H:i', strtotime($shift['start_time'])); ?> - 
                                            <?php echo date('H:i', strtotime($shift['end_time'])); ?>
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

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tổng số ca:</strong> <?php echo count($shifts); ?></p>
                    <p><strong>Từ ngày:</strong> <?php echo date('d/m/Y', strtotime($shifts[0]['shift_date'])); ?></p>
                    <p><strong>Đến ngày:</strong> <?php echo date('d/m/Y', strtotime(end($shifts)['shift_date'])); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function copyShareLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        Swal.fire({
            icon: 'success',
            title: 'Đã sao chép!',
            html: `Link chia sẻ đã được sao chép:<br><small>${url}</small>`,
            timer: 3000
        });
    }, function() {
        Swal.fire({
            icon: 'info',
            title: 'Link chia sẻ',
            html: `<input type="text" class="form-control" value="${url}" readonly onclick="this.select()">`,
            confirmButtonText: 'Đóng'
        });
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>
