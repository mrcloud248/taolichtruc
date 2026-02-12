<?php
$pageTitle = "Cấu hình rules";
require_once '../../includes/header.php';
require_once '../../models/Rule.php';

$ruleModel = new Rule();
$rules = $ruleModel->getAll();
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-cog"></i> Cấu hình Rules</h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Danh sách Rules</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên rule</th>
                                <th>Loại</th>
                                <th>Giá trị</th>
                                <th>Mô tả</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rule['rule_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($rule['rule_type']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($rule['rule_value']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($rule['description']); ?></td>
                                    <td>
                                        <?php if ($rule['is_active']): ?>
                                            <span class="badge bg-success">Đang áp dụng</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tắt</span>
                                        <?php endif; ?>
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

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Giải thích Rules</h5>
            </div>
            <div class="card-body">
                <h6>1. Phân bổ đều theo tháng</h6>
                <p>Thuật toán ưu tiên phân chia số ca trực đều cho tất cả nhân viên. Mọi người sẽ có số ca gần bằng nhau trước khi ai đó nhận thêm ca.</p>
                
                <h6>2. Luân phiên theo thứ trong tuần</h6>
                <p>Mỗi nhân viên chỉ trực mỗi buổi (ví dụ: Tối thứ 2, Sáng CN) một lần cho đến khi tất cả mọi người đều đã trực buổi đó. Điều này đảm bảo công bằng và tránh một người phải trực cùng một thứ quá nhiều lần.</p>
                
                <h6>3. Tránh ca liên tiếp</h6>
                <p>Hệ thống tránh phân công cùng một người trực nhiều ca liên tiếp để đảm bảo sự đa dạng và tránh quá tải.</p>
                
                <h6>4. Số ca liên tiếp tối đa</h6>
                <p>Giới hạn số ca trực liên tiếp mà một nhân viên có thể đảm nhận (hiện tại: tối đa 2 ca liên tiếp).</p>
                
                <h6>5. Thời gian nghỉ tối thiểu</h6>
                <p>Đảm bảo nhân viên có đủ thời gian nghỉ ngơi giữa các ca trực (tính bằng giờ).</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
