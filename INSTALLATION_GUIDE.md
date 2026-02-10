# Hướng Dẫn Cài Đặt Chi Tiết

## Bước 1: Tải các thư viện JavaScript/CSS

Do project không bao gồm các file thư viện lớn, anh cần tải các file sau:

### 1.1. Bootstrap 5
Tải từ: https://getbootstrap.com/docs/5.3/getting-started/download/
- Giải nén và copy:
  - `bootstrap.min.css` → `assets/css/`
  - `bootstrap.bundle.min.js` → `assets/js/`

### 1.2. jQuery
Tải từ: https://jquery.com/download/
- Download "compressed, production jQuery 3.7.1"
- Đổi tên thành `jquery.min.js` → `assets/js/`

### 1.3. FullCalendar
Tải từ: https://fullcalendar.io/docs/initialize-globals
- Download FullCalendar v6
- Copy:
  - `fullcalendar.min.css` → `assets/css/`
  - `fullcalendar.min.js` → `assets/js/`

**HOẶC** sử dụng CDN (đã có sẵn trong code):
- Các file sẽ load từ CDN, không cần tải về

## Bước 2: Upload lên Hosting

### Cách 1: Upload qua cPanel File Manager
1. Đăng nhập cPanel
2. Mở File Manager
3. Vào thư mục `public_html` (hoặc `www`, `htdocs`)
4. Upload toàn bộ thư mục project
5. Giải nén nếu upload dạng ZIP

### Cách 2: Upload qua FTP
1. Mở FileZilla (hoặc FTP client khác)
2. Kết nối đến hosting
3. Upload toàn bộ thư mục vào `public_html`

## Bước 3: Tạo Database

1. Đăng nhập cPanel → phpMyAdmin
2. Click "New" để tạo database mới
3. Đặt tên: `schedule_management`
4. Chọn Collation: `utf8mb4_unicode_ci`
5. Click "Create"

## Bước 4: Import Database

1. Trong phpMyAdmin, chọn database `schedule_management`
2. Click tab "Import"
3. Click "Choose File" → chọn file `database/schedule_db.sql`
4. Click "Go" để import
5. Đợi import hoàn tất

## Bước 5: Cấu Hình Database

1. Mở file `config/database.php`
2. Sửa thông tin:

```php
define('DB_HOST', 'localhost');              // Thường là localhost
define('DB_USER', 'cpanel_username_dbuser'); // Username database
define('DB_PASS', 'your_password');          // Password database
define('DB_NAME', 'cpanel_username_schedule_management'); // Tên database
```

**Lưu ý**: Trên hosting thường tên database có prefix là username cPanel
Ví dụ: `username_schedule_management`

## Bước 6: Phân Quyền Thư Mục

### Qua cPanel File Manager:
1. Click phải vào thư mục `exports`
2. Chọn "Change Permissions"
3. Tick tất cả các ô (Read, Write, Execute cho Owner, Group, World)
4. Hoặc nhập: 777
5. Click "Change Permissions"

### Qua SSH (nếu có):
```bash
chmod 777 exports/
```

## Bước 7: Kiểm Tra

1. Mở trình duyệt
2. Truy cập: `http://your-domain.com`
3. Nếu thấy trang chủ → Thành công!

## Bước 8: Sử Dụng

### Thêm nhân viên mẫu:
1. Menu "Nhân viên" → "Thêm mới"
2. Thêm ít nhất 5-10 nhân viên

### Tạo lịch trực:
1. Menu "Lịch trực" → "Tạo lịch mới"
2. Chọn loại lịch (Ngày hoặc Tuần)
3. Điền thông tin và click "Tạo lịch"

## Troubleshooting

### Lỗi jQuery không load / Nút không hoạt động
**Triệu chứng**: Click "Tạo lịch" không có tác dụng, Console báo `jQuery is not defined`

**Giải pháp**:
1. Truy cập `debug.php` để kiểm tra BASE_URL và jQuery
2. Nếu jQuery không load từ CDN:
   - Tải jQuery về: https://code.jquery.com/jquery-3.7.1.min.js
   - Lưu vào `assets/js/jquery-3.7.1.min.js`
   - Sửa `includes/footer.php` để dùng file local thay vì CDN
3. Clear cache trình duyệt (Ctrl + F5)

**Xem chi tiết**: Đọc file `TROUBLESHOOTING.md`

### Lỗi 404 - File không tìm thấy
**Triệu chứng**: CSS/JS không load, trang không có style

**Giải pháp**:
1. Truy cập `debug.php` để kiểm tra BASE_URL
2. Đảm bảo BASE_URL khớp với đường dẫn thực tế
3. Kiểm tra các file tồn tại trong thư mục `assets/`

### Lỗi "Connection failed"
- Kiểm tra lại thông tin database trong `config/database.php`
- Đảm bảo database đã được tạo và import SQL
- Kiểm tra username/password database

### Lỗi "Permission denied" khi export
- Kiểm tra quyền thư mục `exports/` (phải là 777)
- Kiểm tra PHP có quyền ghi file

### Trang trắng (blank page)
- Bật display_errors trong PHP
- Xem file error_log trong cPanel
- Kiểm tra file `config/database.php` có đúng không

### CSS/JS không load
- Kiểm tra đường dẫn file trong `includes/header.php`
- Đảm bảo các file CSS/JS đã được upload
- Xóa cache trình duyệt (Ctrl + F5)

## Cấu Trúc File Cần Có

```
public_html/
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── fullcalendar.min.css
│   │   └── style.css
│   └── js/
│       ├── jquery.min.js
│       ├── bootstrap.bundle.min.js
│       ├── fullcalendar.min.js
│       └── app.js
├── config/
│   ├── database.php (CẦN CẤU HÌNH)
│   └── constants.php
├── database/
│   └── schedule_db.sql
├── exports/ (CHMOD 777)
├── index.php
└── ... (các file khác)
```

## Liên Hệ Hỗ Trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra lại từng bước
2. Xem phần Troubleshooting
3. Kiểm tra log lỗi PHP
