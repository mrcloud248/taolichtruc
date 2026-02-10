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
                <h6>1. Số ca liên tiếp tối đa</h6>
                <p>Giới hạn số ca trực liên tiếp mà một nhân viên có thể đảm nhận để tránh quá tải.</p>
                
                <h6>2. Thời gian nghỉ tối thiểu</h6>
                <p>Đảm bảo nhân viên có đủ thời gian nghỉ ngơi giữa các ca trực (tính bằng giờ).</p>
                
                <h6>3. Phân bổ đều theo tháng</h6>
                <p>Thuật toán sẽ cố gắng phân chia số ca trực đều cho tất cả nhân viên trong tháng.</p>
                
                <h6>4. Tránh ca cuối tuần liên tiếp</h6>
                <p>Tránh việc một nhân viên phải trực cuối tuần nhiều tuần liên tiếp.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
