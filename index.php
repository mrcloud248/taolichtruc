<?php
$pageTitle = "Trang chủ - Hệ Thống Quản Lý Lịch Trực";
require_once 'includes/header.php';
require_once 'models/Staff.php';
require_once 'models/Schedule.php';

$staffModel = new Staff();
$scheduleModel = new Schedule();

// Get statistics
$totalStaff = count($staffModel->getAll());
$activeStaff = count($staffModel->getAllActive());
$allSchedules = $scheduleModel->getAll();
$totalSchedules = count($allSchedules);

// Get recent schedules
$recentSchedules = array_slice($allSchedules, 0, 5);
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4"><i class="fas fa-home"></i> Dashboard</h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Nhân viên</h6>
                        <h2 class="mb-0"><?php echo $activeStaff; ?> / <?php echo $totalStaff; ?></h2>
                        <small>Đang hoạt động</small>
                    </div>
                    <div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-primary bg-opacity-75">
                <a href="views/staff/list.php" class="text-white text-decoration-none">
                    Xem chi tiết <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Lịch trực</h6>
                        <h2 class="mb-0"><?php echo $totalSchedules; ?></h2>
                        <small>Tổng số lịch</small>
                    </div>
                    <div>
                        <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success bg-opacity-75">
                <a href="views/schedule/history.php" class="text-white text-decoration-none">
                    Xem lịch sử <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Tháng hiện tại</h6>
                        <h2 class="mb-0"><?php echo date('m/Y'); ?></h2>
                        <small><?php echo date('d/m/Y'); ?></small>
                    </div>
                    <div>
                        <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-info bg-opacity-75">
                <a href="views/schedule/generate.php" class="text-white text-decoration-none">
                    Tạo lịch mới <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Thao tác nhanh</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="views/schedule/generate.php" class="btn btn-primary w-100 py-3">
                            <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                            Tạo lịch trực mới
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="views/schedule/daily_calendar.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-calendar-alt fa-2x d-block mb-2"></i>
                            Xem lịch ngày
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="views/schedule/weekly_list.php" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-list fa-2x d-block mb-2"></i>
                            Xem lịch tuần
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="views/staff/add.php" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-user-plus fa-2x d-block mb-2"></i>
                            Thêm nhân viên
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Schedules -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-history"></i> Lịch trực gần đây</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentSchedules)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Chưa có lịch trực nào. 
                        <a href="views/schedule/generate.php">Tạo lịch mới</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loại</th>
                                    <th>Tháng/Năm</th>
                                    <th>Trạng thái</th>
                                    <th>Người tạo</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSchedules as $schedule): ?>
                                    <tr>
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
                                            $statusClass = [
                                                'draft' => 'warning',
                                                'published' => 'success',
                                                'archived' => 'secondary'
                                            ];
                                            $statusText = [
                                                'draft' => 'Nháp',
                                                'published' => 'Đã xuất bản',
                                                'archived' => 'Lưu trữ'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass[$schedule['status']]; ?>">
                                                <?php echo $statusText[$schedule['status']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($schedule['generated_by']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($schedule['generated_at'])); ?></td>
                                        <td>
                                            <?php if ($schedule['schedule_type'] == 'daily'): ?>
                                                <a href="views/schedule/daily_calendar.php?id=<?php echo $schedule['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Xem
                                                </a>
                                            <?php else: ?>
                                                <a href="views/schedule/weekly_list.php?id=<?php echo $schedule['id']; ?>" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-eye"></i> Xem
                                                </a>
                                            <?php endif; ?>
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

<?php require_once 'includes/footer.php'; ?>
