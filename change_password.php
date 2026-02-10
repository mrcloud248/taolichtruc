<?php
$pageTitle = "Đổi mật khẩu";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    } else {
        $conn = getDBConnection();
        $userId = $_SESSION['user_id'];
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!verifyPassword($currentPassword, $user['password'])) {
            $error = 'Mật khẩu hiện tại không đúng';
        } else {
            // Update password
            $hashedPassword = hashPassword($newPassword);
            $updateStmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $userId);
            
            if ($updateStmt->execute()) {
                $success = 'Đổi mật khẩu thành công!';
                $_POST = []; // Clear form
            } else {
                $error = 'Có lỗi xảy ra khi đổi mật khẩu';
            }
            
            $updateStmt->close();
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-key"></i> Đổi mật khẩu</h5>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="current_password" 
                                   placeholder="Nhập mật khẩu hiện tại" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" name="new_password" 
                                   placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)" 
                                   minlength="6" required>
                        </div>
                        <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                            <input type="password" class="form-control" name="confirm_password" 
                                   placeholder="Nhập lại mật khẩu mới" 
                                   minlength="6" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Đổi mật khẩu
                        </button>
                        <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="fas fa-info-circle text-info"></i> Lưu ý bảo mật</h6>
                <ul class="mb-0">
                    <li>Sử dụng mật khẩu mạnh, kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                    <li>Không chia sẻ mật khẩu với người khác</li>
                    <li>Thay đổi mật khẩu định kỳ để đảm bảo an toàn</li>
                    <li>Không sử dụng mật khẩu giống với các tài khoản khác</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
