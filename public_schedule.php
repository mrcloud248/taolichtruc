<?php
/**
 * Public Schedule View - No login required
 * Accessible via share link with token
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Schedule.php';

// Define BASE_URL
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptName);
    $basePath = rtrim($scriptDir, '/');
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}

$scheduleModel = new Schedule();
$scheduleId = intval($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

// Verify token (simple MD5 hash of schedule ID + secret)
$secret = 'schedule_share_secret_2026'; // Change this to a random string
$expectedToken = md5($scheduleId . $secret);

if ($token !== $expectedToken) {
    die('Invalid share link');
}

$schedule = $scheduleModel->getById($scheduleId);

if (!$schedule) {
    die('Schedule not found');
}

$pageTitle = $schedule['schedule_type'] === 'daily' ? 'Lịch trực ngày' : 'Lịch trực tuần';
$isDaily = $schedule['schedule_type'] === 'daily';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <?php if ($isDaily): ?>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .public-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .public-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="public-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1"><i class="fas fa-calendar-alt"></i> <?php echo $pageTitle; ?></h3>
                    <span class="public-badge"><i class="fas fa-share-alt"></i> Xem công khai</span>
                </div>
                <div class="text-end">
                    <div><strong>Tháng/Năm:</strong> <?php echo $schedule['month'] . '/' . $schedule['year']; ?></div>
                    <div><small>Tạo bởi: <?php echo htmlspecialchars($schedule['generated_by']); ?></small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($isDaily): ?>
            <!-- Daily Schedule - Calendar View -->
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
                                    <tbody></tbody>
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
        <?php else: ?>
            <!-- Weekly Schedule - Table View -->
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
                                        $shifts = $scheduleModel->getShifts($scheduleId);
                                        $stt = 1;
                                        foreach ($shifts as $shift): 
                                        ?>
                                            <tr>
                                                <td><?php echo $stt++; ?></td>
                                                <td><strong><?php echo date('d/m/Y', strtotime($shift['shift_date'])); ?></strong></td>
                                                <td>Thứ 7</td>
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
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($isDaily): ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/vi.global.min.js"></script>
    
    <script>
    let calendar;
    let allEvents = [];
    
    document.addEventListener('DOMContentLoaded', function() {
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
    </script>
    <?php endif; ?>
</body>
</html>
