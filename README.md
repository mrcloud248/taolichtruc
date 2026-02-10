# Hệ Thống Quản Lý Lịch Trực - Schedule Management System

Web application quản lý và tự động phân công lịch trực cho nhân sự theo rules được định nghĩa trước.

## Tính năng chính

- ✅ Quản lý nhân viên (CRUD)
- ✅ Tự động phân công lịch trực ngày (Thứ 2-7 tối, Chủ nhật sáng & tối)
- ✅ Tự động phân công lịch trực tuần (Thứ 7 sáng)
- ✅ Hiển thị lịch dạng Calendar (cho lịch ngày)
- ✅ Hiển thị lịch dạng Table (cho lịch tuần)
- ✅ Export lịch ra file CSV/Excel
- ✅ Quản lý ràng buộc nhân viên (ngày nghỉ, ca ưu tiên)
- ✅ Lịch sử và quản lý các lịch trực đã tạo

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 hoặc MariaDB 10.2 trở lên
- Web server: Apache/Nginx
- Extension PHP: mysqli, json

## Cài đặt

### 1. Upload lên hosting

#### Cách 1: Upload file ZIP
1. Nén file zip, rồi upload lên hosting
2. Đăng nhập vào cPanel/Hosting Control Panel
3. Vào File Manager
4. Upload file ZIP vào thư mục `public_html` (hoặc `www`, `htdocs`)
5. Extract file ZIP

#### Cách 2: Clone từ GitHub
```bash
cd public_html
git clone https://github.com/mrcloud248/taolichtruc.git
cd schedule-management
```

### 2. Tạo database

1. Đăng nhập vào phpMyAdmin
2. Tạo database mới (ví dụ: `schedule_management`)
3. Import file `database/demo_taolich.sql`

### 3. Cấu hình database

1. Copy file `config/database.example.php` thành `config/database.php`
2. Mở file `config/database.php` và cập nhật thông tin:

```php
define('DB_HOST', 'localhost');          // Host database
define('DB_USER', 'your_username');      // Username database
define('DB_PASS', 'your_password');      // Password database
define('DB_NAME', 'schedule_management'); // Tên database
```

### 4. Phân quyền thư mục

Đảm bảo thư mục `exports/` có quyền ghi (chmod 777):

```bash
chmod 777 exports/
```

Hoặc qua cPanel File Manager: Click phải vào thư mục `exports` → Change Permissions → 777

### 5. Truy cập website

Mở trình duyệt và truy cập:
```
http://your-domain.com
```

## Cấu trúc thư mục

```
schedule-management/
├── assets/              # CSS, JS, images
├── config/              # Cấu hình database, constants
├── controllers/         # Xử lý logic
├── database/            # File SQL
├── exports/             # Thư mục chứa file export
├── includes/            # Header, footer, functions
├── models/              # Models (Staff, Schedule, Rule, Export)
├── views/               # Giao diện
│   ├── staff/          # Quản lý nhân viên
│   ├── schedule/       # Quản lý lịch trực
│   └── rules/          # Cấu hình rules
└── index.php           # Trang chủ
```

## Hướng dẫn sử dụng

### 1. Thêm nhân viên
- Vào menu **Nhân viên** → **Thêm mới**
- Điền thông tin: Họ tên, Email, Điện thoại, Chức vụ
- Cấu hình số ca tối đa/tuần, tháng
- Lưu

### 2. Tạo lịch trực ngày
- Vào menu **Lịch trực** → **Tạo lịch mới**
- Chọn tab **Lịch Trực Ngày**
- Chọn tháng, năm
- Chọn nhân viên trực ngày đầu tiên
- Click **Tạo lịch ngày**
- Hệ thống sẽ tự động phân công công bằng cho tất cả nhân viên

### 3. Tạo lịch trực tuần
- Vào menu **Lịch trực** → **Tạo lịch mới**
- Chọn tab **Lịch Trực Tuần**
- Chọn năm
- Tick chọn các nhân viên tham gia
- Click **Tạo lịch tuần**
- Hệ thống sẽ shuffle ngẫu nhiên và assign cho các thứ 7 tiếp theo

### 4. Xem lịch
- **Lịch ngày**: Menu **Lịch trực** → **Lịch ngày** (hiển thị dạng calendar)
- **Lịch tuần**: Menu **Lịch trực** → **Lịch tuần** (hiển thị dạng table)

### 5. Export lịch
- Vào trang xem lịch (ngày hoặc tuần)
- Click nút **Export CSV**
- File sẽ được tải về máy

## Thuật toán phân công

### Lịch ngày (Daily Schedule)
1. Ngày đầu tiên: Assign nhân viên được chọn cố định
2. Tạo pool nhân viên với quota công bằng
3. Shuffle pool ngẫu nhiên
4. Assign tuần tự, tránh ca liên tiếp
5. Khi hết pool: tạo pool mới và shuffle lại

### Lịch tuần (Weekly Schedule)
1. Shuffle danh sách nhân viên ngẫu nhiên
2. Tìm tất cả các thứ 7 từ hiện tại
3. Assign tuần tự cho đến khi hết danh sách
4. Kết quả có thể span qua nhiều tháng

## Troubleshooting

### Lỗi kết nối database
- Kiểm tra thông tin trong `config/database.php`
- Đảm bảo database đã được tạo và import SQL
- Kiểm tra user có quyền truy cập database

### Không export được file
- Kiểm tra quyền thư mục `exports/` (chmod 777)
- Kiểm tra PHP có quyền ghi file

### Lịch không hiển thị
- Kiểm tra đã tạo lịch chưa
- Xem console browser có lỗi JavaScript không
- Kiểm tra file FullCalendar đã load đúng chưa

## Công nghệ sử dụng

- **Backend**: PHP 7.4+ (Pure PHP, không framework)
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5, jQuery, FullCalendar.js
- **Icons**: Font Awesome 6
- **Notifications**: SweetAlert2

## Bảo mật

- Sanitize tất cả input từ user
- Sử dụng Prepared Statements để tránh SQL Injection
- File `config/database.php` không được commit lên Git
- Thư mục `exports/` được bảo vệ bằng `.htaccess`

## Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra phần Troubleshooting
2. Xem log lỗi PHP (error_log)
3. Kiểm tra console browser (F12)

## License

MIT License - Free to use and modify

## Tác giả

Phát triển bởi [DucViet]
Version 1.0.0 - 2026
