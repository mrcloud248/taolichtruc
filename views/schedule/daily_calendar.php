<?php
$pageTitle = "Lịch trực ngày";
require_once '../../includes/header.php';
require_once '../../models/Schedule.php';

$scheduleModel = new Schedule();
$scheduleId = intval($_GET['id'] ?? 0);

if ($scheduleId > 0) {
    $schedule = $scheduleModel->getById($scheduleId);
} else {
    // Get latest daily schedule
    $schedules = $scheduleModel->getByTypeAndDate('daily', date('n'), date('Y'));
    $schedule = $schedules[0] ?? null;
    $scheduleId = $schedule['id'] ?? 0;
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt"></i> Lịch Trực Ngày</h2>
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
        <i class="fas fa-info-circle"></i> Chưa có lịch trực nào. 
        <a href="generate.php">Tạo lịch mới</a>
    </div>
<?php else: ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Tháng/Năm:</strong> <?php echo $schedule['month'] . '/' . $schedule['year']; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Trạng thái:</strong>
                            <?php
                            $statusClass = ['draft' => 'warning', 'published' => 'success', 'archived' => 'secondary'];
                            $statusText = ['draft' => 'Nháp', 'published' => 'Đã xuất bản', 'archived' => 'Lưu trữ'];
                            ?>
                            <span class="badge bg-<?php echo $statusClass[$schedule['status']]; ?>">
                                <?php echo $statusText[$schedule['status']]; ?>
                            </span>
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

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê số buổi trực</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="statsTable">
                            <thead>
                                <tr>
                                    <th width="80">STT</th>
                                    <th>Tên nhân viên</th>
                                    <th class="text-center" width="150">Số buổi trực</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Lọc theo nhân viên</h5>
                        <button class="btn btn-sm btn-outline-secondary" id="clearFilter">
                            <i class="fas fa-times"></i> Xóa lọc
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <select class="form-select" id="staffFilter">
                        <option value="">-- Tất cả nhân viên --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
<?php if ($schedule): ?>
let calendar;
let allEvents = [];

document.addEventListener('DOMContentLoaded', function() {
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar is not loaded!');
        return;
    }
    
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '<?php echo $schedule['year'] . '-' . sprintf('%02d', $schedule['month']) . '-01'; ?>',
        locale: 'vi',
        height: 'auto',
        contentHeight: 600,
        aspectRatio: 1.8,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        buttonText: {
            today: 'Hôm nay',
            month: 'Tháng'
        },
        dayMaxEvents: 3,
        eventDisplay: 'block',
        displayEventTime: false,
        events: function(info, successCallback, failureCallback) {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded!');
                failureCallback();
                return;
            }
            
            $.ajax({
                url: '<?php echo BASE_URL; ?>/controllers/schedule_controller.php',
                method: 'GET',
                data: {
                    action: 'get_shifts_calendar',
                    schedule_id: <?php echo $scheduleId; ?>
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        allEvents = response.data;
                        
                        // Populate staff filter
                        const staffSet = new Set();
                        allEvents.forEach(event => {
                            const staffName = event.title.split(' - ')[0];
                            staffSet.add(staffName);
                        });
                        
                        const staffFilter = $('#staffFilter');
                        Array.from(staffSet).sort().forEach(name => {
                            staffFilter.append(`<option value="${name}">${name}</option>`);
                        });
                        
                        successCallback(allEvents);
                        calculateStats(allEvents);
                    } else {
                        failureCallback();
                    }
                },
                error: failureCallback
            });
        },
        eventClick: function(info) {
            Swal.fire({
                title: info.event.title,
                html: `
                    <p><strong>Ngày:</strong> ${info.event.start.toLocaleDateString('vi-VN')}</p>
                    <p><strong>Giờ:</strong> ${info.event.start.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})} - 
                    ${info.event.end.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</p>
                    <p><strong>Ca:</strong> ${info.event.extendedProps.shift_label}</p>
                `,
                icon: 'info'
            });
        }
    });
    
    calendar.render();
    
    // Staff filter
    $('#staffFilter').on('change', function() {
        const selectedStaff = $(this).val();
        filterEvents(selectedStaff);
    });
    
    $('#clearFilter').on('click', function() {
        $('#staffFilter').val('');
        filterEvents('');
    });
});

function filterEvents(staffName) {
    let filteredEvents = allEvents;
    
    if (staffName) {
        filteredEvents = allEvents.filter(event => {
            return event.title.startsWith(staffName + ' -');
        });
    }
    
    calendar.removeAllEvents();
    calendar.addEventSource(filteredEvents);
    calculateStats(filteredEvents);
}

function calculateStats(events) {
    const stats = {};
    
    events.forEach(event => {
        const staffName = event.title.split(' - ')[0];
        const shiftType = event.extendedProps.shift_type;
        
        if (!stats[staffName]) {
            stats[staffName] = {
                total: 0
            };
        }
        
        stats[staffName].total++;
    });
    
    const sortedStats = Object.keys(stats).sort().map(name => ({
        name: name,
        ...stats[name]
    }));
    
    const tbody = document.querySelector('#statsTable tbody');
    tbody.innerHTML = '';
    
    sortedStats.forEach((stat, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">${index + 1}</td>
            <td><strong>${stat.name}</strong></td>
            <td class="text-center"><span class="badge bg-primary">${stat.total}</span></td>
        `;
        tbody.appendChild(row);
    });
    
    const totalRow = document.createElement('tr');
    totalRow.className = 'table-active fw-bold';
    const totalShifts = sortedStats.reduce((sum, stat) => sum + stat.total, 0);
    
    totalRow.innerHTML = `
        <td colspan="2" class="text-end">TỔNG CỘNG:</td>
        <td class="text-center"><span class="badge bg-dark">${totalShifts}</span></td>
    `;
    tbody.appendChild(totalRow);
}

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
<?php endif; ?>
</script>

<?php require_once '../../includes/footer.php'; ?>
