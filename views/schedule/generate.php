<?php
$pageTitle = "Tạo lịch trực";
require_once '../../includes/header.php';
require_once '../../models/Staff.php';

$staffModel = new Staff();
$staffList = $staffModel->getAllActive();
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-calendar-plus"></i> Tạo lịch trực mới</h2>
    </div>
</div>

<div class="row">
    <!-- Daily Schedule -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Lịch Trực Ngày</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Tạo lịch trực cho các ngày trong tháng (Thứ 2-7 tối, Chủ nhật sáng & tối)</p>
                
                <form id="generateDailyForm">
                    <div class="mb-3">
                        <label class="form-label">Tháng <span class="text-danger">*</span></label>
                        <select class="form-select" name="month" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $m == date('n') ? 'selected' : ''; ?>>
                                    Tháng <?php echo $m; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Năm <span class="text-danger">*</span></label>
                        <select class="form-select" name="year" required>
                            <?php for ($y = date('Y'); $y <= date('Y') + 2; $y++): ?>
                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nhân viên trực ngày đầu tiên (tùy chọn)</label>
                        <select class="form-select" name="first_day_staff_id">
                            <option value="">-- Tự động chọn --</option>
                            <?php foreach ($staffList as $staff): ?>
                                <option value="<?php echo $staff['id']; ?>">
                                    <?php echo htmlspecialchars($staff['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Để trống để hệ thống tự động chọn ngẫu nhiên</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nhân viên tham gia <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllDaily">
                                <i class="fas fa-check-double"></i> Chọn tất cả
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllDaily">
                                <i class="fas fa-times"></i> Bỏ chọn tất cả
                            </button>
                        </div>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($staffList as $staff): ?>
                                <div class="form-check">
                                    <input class="form-check-input daily-staff" type="checkbox" 
                                           value="<?php echo $staff['id']; ?>" 
                                           id="daily_staff_<?php echo $staff['id']; ?>" checked>
                                    <label class="form-check-label" for="daily_staff_<?php echo $staff['id']; ?>">
                                        <?php echo htmlspecialchars($staff['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Chọn nhân viên tham gia lịch trực ngày</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Người tạo</label>
                        <input type="text" class="form-control" name="generated_by" value="Admin" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magic"></i> Tạo lịch ngày
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Weekly Schedule -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Lịch Trực Tuần</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Tạo lịch trực cho các ngày Thứ 7 (8:00 AM - 5:00 PM)</p>
                
                <form id="generateWeeklyForm">
                    <div class="mb-3">
                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                        <small class="text-muted">Chọn ngày Thứ 7 đầu tiên để bắt đầu lịch tuần</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Chọn nhân viên <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-success" id="selectAllWeekly">
                                <i class="fas fa-check-double"></i> Chọn tất cả
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllWeekly">
                                <i class="fas fa-times"></i> Bỏ chọn tất cả
                            </button>
                        </div>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($staffList as $staff): ?>
                                <div class="form-check">
                                    <input class="form-check-input weekly-staff" type="checkbox" 
                                           value="<?php echo $staff['id']; ?>" 
                                           id="staff_<?php echo $staff['id']; ?>">
                                    <label class="form-check-label" for="staff_<?php echo $staff['id']; ?>">
                                        <?php echo htmlspecialchars($staff['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted">Mỗi nhân viên sẽ được phân 1 Thứ 7. Số tuần = Số nhân viên được chọn</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Người tạo</label>
                        <input type="text" class="form-control" name="generated_by" value="Admin" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-magic"></i> Tạo lịch tuần
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
// Wait for jQuery to load
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
}

$(document).ready(function() {
    // Select/Deselect All for Daily Schedule
    $('#selectAllDaily').on('click', function() {
        $('.daily-staff').prop('checked', true);
    });
    
    $('#deselectAllDaily').on('click', function() {
        $('.daily-staff').prop('checked', false);
    });
    
    // Select/Deselect All for Weekly Schedule
    $('#selectAllWeekly').on('click', function() {
        $('.weekly-staff').prop('checked', true);
    });
    
    $('#deselectAllWeekly').on('click', function() {
        $('.weekly-staff').prop('checked', false);
    });
    
    // Generate Daily Schedule
    $('#generateDailyForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get selected staff
        const selectedStaff = [];
        $('.daily-staff:checked').each(function() {
            selectedStaff.push($(this).val());
        });
        
        if (selectedStaff.length === 0) {
            Swal.fire('Cảnh báo!', 'Vui lòng chọn ít nhất 1 nhân viên', 'warning');
            return;
        }
        
        // Check if first day staff is in selected list (if specified)
        const firstDayStaffId = $('select[name="first_day_staff_id"]').val();
        
        if (firstDayStaffId && !selectedStaff.includes(firstDayStaffId)) {
            Swal.fire('Cảnh báo!', 'Nhân viên trực ngày đầu tiên phải nằm trong danh sách nhân viên tham gia', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Đang tạo lịch...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const formData = $(this).serializeArray();
        formData.push({ name: 'action', value: 'generate_daily' });
        formData.push({ name: 'staff_ids', value: JSON.stringify(selectedStaff) });
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/controllers/schedule_controller.php',
            method: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        confirmButtonText: 'Xem lịch'
                    }).then(() => {
                        window.location.href = '<?php echo BASE_URL; ?>/views/schedule/daily_calendar.php?id=' + response.data.scheduleId;
                    });
                } else {
                    Swal.fire('Lỗi!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);
                
                // Try to extract error message from HTML
                let errorMsg = 'Có lỗi xảy ra khi tạo lịch';
                if (xhr.responseText) {
                    // Try to find error message in response
                    const match = xhr.responseText.match(/<b>(.+?)<\/b>/);
                    if (match) {
                        errorMsg = match[1];
                    }
                }
                
                Swal.fire('Lỗi!', errorMsg + '. Xem Console để biết chi tiết.', 'error');
            }
        });
    });

    // Generate Weekly Schedule
    $('#generateWeeklyForm').on('submit', function(e) {
        e.preventDefault();
        
        const selectedStaff = [];
        $('.weekly-staff:checked').each(function() {
            selectedStaff.push($(this).val());
        });
        
        if (selectedStaff.length === 0) {
            Swal.fire('Cảnh báo!', 'Vui lòng chọn ít nhất 1 nhân viên', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Đang tạo lịch...',
            html: `Tạo lịch cho <strong>${selectedStaff.length}</strong> nhân viên (${selectedStaff.length} tuần)`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const formData = $(this).serializeArray();
        formData.push({ name: 'action', value: 'generate_weekly' });
        formData.push({ name: 'staff_ids', value: JSON.stringify(selectedStaff) });
        formData.push({ name: 'num_weeks', value: selectedStaff.length }); // Auto calculate
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/controllers/schedule_controller.php',
            method: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        confirmButtonText: 'Xem lịch'
                    }).then(() => {
                        window.location.href = '<?php echo BASE_URL; ?>/views/schedule/weekly_list.php?id=' + response.data.scheduleId;
                    });
                } else {
                    Swal.fire('Lỗi!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);
                
                // Try to extract error message from HTML
                let errorMsg = 'Có lỗi xảy ra khi tạo lịch';
                if (xhr.responseText) {
                    // Try to find error message in response
                    const match = xhr.responseText.match(/<b>(.+?)<\/b>/);
                    if (match) {
                        errorMsg = match[1];
                    }
                }
                
                Swal.fire('Lỗi!', errorMsg + '. Xem Console để biết chi tiết.', 'error');
            }
        });
    });
});
</script>