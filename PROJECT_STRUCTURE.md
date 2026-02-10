# Hệ Thống Quản Lý Lịch Trực - Phân Công Tự Động

## Tổng Quan Dự Án

Web application quản lý và tự động phân công lịch trực cho nhân sự theo rules được định nghĩa trước. Hệ thống cho phép nhập danh sách nhân viên, thiết lập quy tắc phân công, tự động generate lịch trực theo tháng, và export kết quả ra Excel.

## Shift Time Configuration

### Lịch Trực Ngày (Daily Schedule)
Áp dụng cho tất cả các ngày trong tháng:

| Ngày | Giờ trực |
|------|----------|
| Thứ 2 | 5:00 PM - 11:30 PM |
| Thứ 3 | 5:00 PM - 11:30 PM |
| Thứ 4 | 5:00 PM - 11:30 PM |
| Thứ 5 | 5:00 PM - 11:30 PM |
| Thứ 6 | 5:00 PM - 11:30 PM |
| Thứ 7 | 5:00 PM - 11:30 PM |
| Chủ nhật (sáng) | 8:00 AM - 5:00 PM |
| Chủ nhật (tối) | 5:00 PM - 11:30 PM |

**Rules mặc định cho Lịch Ngày:**
1. **Ngày đầu tiên của tháng**: Phải chọn cố định 1 nhân sự (admin chọn trước)
2. **Randomized Distribution**: 
   - Không chạy tuần tự kiểu vòng lặp dễ đoán
   - Sắp xếp ngẫu nhiên nhưng đảm bảo chia đều người trong 1 vòng
   - Tạo cảm giác ngẫu nhiên nhưng vẫn công bằng
3. **Phân bổ đều theo tháng**: 
   - Mỗi nhân viên có số ca gần bằng nhau
   - Tránh dồn ca liên tiếp cho 1 người
   - Đảm bảo khoảng cách hợp lý giữa các ca

### Lịch Trực Tuần (Weekly Schedule)
Áp dụng cho các ngày **Thứ 7** hàng tuần:

| Ngày | Giờ trực |
|------|----------|
| Thứ 7 (hàng tuần) | 8:00 AM - 5:00 PM |

**Rules mặc định cho Lịch Tuần:**
1. **Full List Random Assignment**: 
   - Admin nhập danh sách nhân sự cho lịch tuần (có thể khác với lịch ngày)
   - Hệ thống shuffle ngẫu nhiên danh sách
   - Assign tuần tự cho TẤT CẢ các thứ 7 tiếp theo cho đến khi hết danh sách
   - Kết quả có thể span qua nhiều tháng
   - Hiển thị dạng list (Ngày | Nhân viên), KHÔNG dạng calendar
   - Mỗi lần generate mới = shuffle lại và tạo lịch mới cho tất cả người

## Core Features

### 1. Quản Lý Nhân Sự
- CRUD nhân viên
- Thiết lập constraints cho từng nhân viên:
  - Ngày nghỉ cố định
  - Số ca tối đa/tuần, tháng
  - Ca ưu tiên/tránh
  - Thời gian nghỉ tối thiểu giữa các ca

### 2. Cấu Hình Rules
- Phân bổ đều số ca cho mỗi nhân viên
- Tránh ca liên tiếp quá nhiều ngày
- Đảm bảo thời gian nghỉ tối thiểu
- Ưu tiên phân công theo kinh nghiệm/vị trí
- Xử lý ngày lễ, cuối tuần đặc biệt

### 3. Generate Lịch Tự Động

#### Algorithm cho Lịch Ngày (Daily Schedule):
```
1. Lấy danh sách nhân viên active
2. Lấy rules và constraints
3. Xác định ngày đầu tiên của tháng:
   - Assign nhân viên đã được chọn cố định
   
4. Tạo danh sách các slot còn lại (dates × shift types)
   - Thứ 2-7: WEEKDAY_EVENING (5pm-11:30pm)
   - Chủ nhật: SUNDAY_MORNING (8am-5pm) + SUNDAY_EVENING (5pm-11:30pm)
   
5. Randomized Fair Distribution:
   a. Tính số ca mỗi người cần trực: total_slots / total_staff
   b. Tạo pool nhân viên với quota (mỗi người xuất hiện N lần)
   c. Shuffle pool ngẫu nhiên
   d. For each slot (theo thứ tự ngày):
      - Lấy nhân viên tiếp theo từ shuffled pool
      - Check constraints (ngày nghỉ, ca liên tiếp)
      - Nếu vi phạm: swap với người khác trong pool
      - Assign và remove khỏi pool
   e. Khi hết pool: tạo pool mới và shuffle lại
   
6. Anti-consecutive logic:
   - Nếu người A vừa trực: giảm priority trong 1-2 ngày
   - Tránh trực >2 buổi liên tiếp
   
7. Validate toàn bộ lịch
8. Lưu vào database
```

#### Algorithm cho Lịch Tuần (Weekly Schedule):
```
1. Admin nhập danh sách nhân viên cho lịch tuần (input riêng)
   Ví dụ: 10 nhân viên [A,B,C,D,E,F,G,H,I,J]

2. Shuffle danh sách ngẫu nhiên
   Ví dụ: [D,A,H,C,B,J,E,F,I,G]

3. Tìm tất cả các ngày Thứ 7 từ hiện tại cho đến khi hết danh sách:
   - Không giới hạn trong 1 tháng
   - Tiếp tục tìm các thứ 7 tiếp theo cho đến khi assign hết 10 người
   
4. Assign tuần tự:
   - Thứ 7 ngày 01/02/2026: D
   - Thứ 7 ngày 08/02/2026: A
   - Thứ 7 ngày 15/02/2026: H
   - Thứ 7 ngày 22/02/2026: C
   - Thứ 7 ngày 01/03/2026: B (sang tháng 3)
   - Thứ 7 ngày 08/03/2026: J
   - Thứ 7 ngày 15/03/2026: E
   - Thứ 7 ngày 22/03/2026: F
   - Thứ 7 ngày 29/03/2026: I
   - Thứ 7 ngày 05/04/2026: G (sang tháng 4)
   
5. Kết quả hiển thị:
   - KHÔNG dạng calendar
   - Chỉ là danh sách: Ngày | Nhân viên
   - Hiển thị đầy đủ tất cả 10 người (hoặc bao nhiêu người trong list)
   - Có thể span qua nhiều tháng
   
6. Lưu vào database:
   - Mỗi shift lưu với scheduleId, date, staffId
   - Có thể query theo tháng nhưng kết quả ban đầu là full list
```

**Ví dụ Output:**
```
Lịch Trực Tuần - Thứ 7 (8:00 AM - 5:00 PM)
Generated: 10/02/2026

Ngày          | Nhân viên
--------------|----------
01/02/2026    | Nguyễn Văn D
08/02/2026    | Trần Thị A
15/02/2026    | Lê Văn H
22/02/2026    | Phạm Thị C
01/03/2026    | Hoàng Văn B
08/03/2026    | Đỗ Thị J
15/03/2026    | Vũ Văn E
22/03/2026    | Mai Thị F
29/03/2026    | Bùi Văn I
05/04/2026    | Đinh Thị G
```

**Key Differences:**
- **Daily**: 
  - Phức tạp hơn, cần đảm bảo công bằng và tránh dồn ca trong tháng
  - Hiển thị dạng calendar view
  - Giới hạn trong 1 tháng
- **Weekly**: 
  - Rất đơn giản, hoàn toàn ngẫu nhiên
  - Hiển thị dạng list (table) đơn giản: Ngày | Nhân viên
  - Không giới hạn tháng, assign cho đến khi hết danh sách nhân viên
  - Mỗi lần generate = 1 lịch hoàn chỉnh cho tất cả người

### 4. Hiển Thị Lịch

#### Lịch Ngày (Daily Schedule):
- Calendar view (tháng/tuần/ngày)
- List view với filter
- Màu sắc phân biệt ca/nhân viên
- Tooltip hiển thị chi tiết
- Drag & drop để điều chỉnh thủ công
- Giới hạn trong 1 tháng

#### Lịch Tuần (Weekly Schedule):
- **Simple Table/List View** (KHÔNG dùng calendar)
- Columns: STT | Ngày | Thứ | Nhân viên | Giờ trực
- Hiển thị đầy đủ tất cả assignments (có thể nhiều tháng)
- Sort by date
- Search/filter by staff name
- Highlight current week
- Export to Excel

### 5. Export Excel
- Format theo template chuẩn
- Bao gồm: Ngày, Ca, Nhân viên, Giờ trực
- Thống kê: Tổng ca/người, phân bổ
- Multiple sheets: Overview, Detail, Statistics

### 6. Lưu Trữ Theo Tháng
- Lưu lịch draft/published
- Version history
- So sánh giữa các tháng
- Archive lịch cũ

---

## Cấu Trúc Project (PHP - Hosting Friendly)

```
schedule-management/
│
├── database/
│   └── schedule_db.sql              # File SQL để import vào phpMyAdmin
│
├── config/
│   ├── database.php                 # Kết nối database
│   └── constants.php                # Các hằng số (shift types, rules)
│
├── includes/
│   ├── header.php                   # Header chung
│   ├── footer.php                   # Footer chung
│   └── functions.php                # Các hàm tiện ích chung
│
├── models/
│   ├── Staff.php                    # Model quản lý nhân viên
│   ├── Schedule.php                 # Model quản lý lịch trực
│   ├── Rule.php                     # Model quản lý rules
│   └── Export.php                   # Model export Excel
│
├── controllers/
│   ├── staff_controller.php         # CRUD nhân viên
│   ├── schedule_controller.php      # Generate & quản lý lịch
│   ├── rule_controller.php          # Cấu hình rules
│   └── export_controller.php        # Export Excel
│
├── views/
│   ├── dashboard.php                # Trang chủ/dashboard
│   ├── staff/
│   │   ├── list.php                 # Danh sách nhân viên
│   │   ├── add.php                  # Thêm nhân viên
│   │   └── edit.php                 # Sửa nhân viên
│   ├── schedule/
│   │   ├── daily_calendar.php       # Lịch ngày (calendar view)
│   │   ├── weekly_list.php          # Lịch tuần (table view)
│   │   ├── generate.php             # Form generate lịch
│   │   └── history.php              # Lịch sử các tháng
│   └── rules/
│       └── config.php               # Cấu hình rules
│
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css        # Bootstrap 5
│   │   ├── fullcalendar.min.css     # FullCalendar cho daily view
│   │   └── style.css                # Custom CSS
│   ├── js/
│   │   ├── jquery.min.js            # jQuery
│   │   ├── bootstrap.bundle.min.js  # Bootstrap JS
│   │   ├── fullcalendar.min.js      # FullCalendar JS
│   │   └── app.js                   # Custom JS
│   └── images/
│       └── logo.png                 # Logo
│
├── exports/                         # Thư mục chứa file Excel export
│   └── .htaccess                    # Bảo vệ thư mục
│
├── .htaccess                        # URL rewriting (optional)
├── index.php                        # Trang chủ
└── README.md                        # Hướng dẫn cài đặt

```

## Database Schema

### Bảng: `staff` (Nhân viên)
```sql
- id (INT, PK, AUTO_INCREMENT)
- name (VARCHAR 100)
- email (VARCHAR 100, UNIQUE)
- phone (VARCHAR 20)
- position (VARCHAR 50)
- is_active (TINYINT, default 1)
- max_shifts_per_week (INT, default 5)
- max_shifts_per_month (INT, default 20)
- created_at (DATETIME)
- updated_at (DATETIME)
```

### Bảng: `staff_constraints` (Ràng buộc nhân viên)
```sql
- id (INT, PK, AUTO_INCREMENT)
- staff_id (INT, FK -> staff.id)
- constraint_type (ENUM: 'day_off', 'avoid_shift', 'prefer_shift')
- constraint_value (VARCHAR 50) # Ví dụ: '2026-02-15', 'SUNDAY_MORNING'
- start_date (DATE, nullable)
- end_date (DATE, nullable)
- created_at (DATETIME)
```

### Bảng: `schedules` (Lịch trực chính)
```sql
- id (INT, PK, AUTO_INCREMENT)
- schedule_type (ENUM: 'daily', 'weekly')
- month (INT) # 1-12
- year (INT)
- status (ENUM: 'draft', 'published', 'archived')
- generated_at (DATETIME)
- generated_by (VARCHAR 100) # Admin name
- notes (TEXT, nullable)
- created_at (DATETIME)
```

### Bảng: `schedule_shifts` (Chi tiết ca trực)
```sql
- id (INT, PK, AUTO_INCREMENT)
- schedule_id (INT, FK -> schedules.id)
- staff_id (INT, FK -> staff.id)
- shift_date (DATE)
- shift_type (ENUM: 'WEEKDAY_EVENING', 'SUNDAY_MORNING', 'SUNDAY_EVENING', 'SATURDAY_MORNING')
- start_time (TIME)
- end_time (TIME)
- is_manual_override (TINYINT, default 0) # Đánh dấu nếu admin sửa tay
- notes (TEXT, nullable)
- created_at (DATETIME)
```

### Bảng: `rules` (Cấu hình rules)
```sql
- id (INT, PK, AUTO_INCREMENT)
- rule_name (VARCHAR 100)
- rule_type (VARCHAR 50) # 'max_consecutive', 'min_rest_hours', etc.
- rule_value (VARCHAR 100)
- is_active (TINYINT, default 1)
- description (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)
```

### Bảng: `schedule_history` (Lịch sử thay đổi)
```sql
- id (INT, PK, AUTO_INCREMENT)
- schedule_id (INT, FK -> schedules.id)
- action (VARCHAR 50) # 'created', 'modified', 'published', 'archived'
- changed_by (VARCHAR 100)
- change_details (TEXT) # JSON format
- created_at (DATETIME)
```

## Tech Stack

### Backend
- **PHP 7.4+** (Pure PHP, không dùng framework để dễ deploy)
- **MySQL 5.7+** / MariaDB
- **PHPSpreadsheet** (cho export Excel)

### Frontend
- **Bootstrap 5** (responsive UI)
- **jQuery** (DOM manipulation)
- **FullCalendar.js** (cho daily schedule calendar view)
- **DataTables** (cho weekly schedule table view)
- **SweetAlert2** (notifications)

### Deployment
- Upload toàn bộ code lên hosting qua FTP/cPanel
- Import file `schedule_db.sql` vào phpMyAdmin
- Cấu hình `config/database.php` với thông tin DB
- Chmod 777 cho thư mục `exports/`
- Truy cập qua domain

## File Deliverables

1. **schedule-management.zip** - Toàn bộ source code (để upload hosting)
2. **schedule_db.sql** - Database schema + sample data
3. **README.md** - Hướng dẫn cài đặt chi tiết
4. **.gitignore** - File ignore cho Git (config, exports, cache)

## Git Repository Structure

```
.gitignore                    # Ignore sensitive files
README.md                     # Hướng dẫn đầy đủ
LICENSE                       # MIT License (optional)
database/
  └── schedule_db.sql         # Database schema
config/
  └── database.example.php    # Example config (không commit file thật)
[... các thư mục khác ...]
```

### .gitignore Content
```
# Config files với thông tin nhạy cảm
config/database.php

# Export files
exports/*.xlsx
exports/*.xls
!exports/.htaccess

# Cache & temp
*.log
*.tmp
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.sublime-*
```

### Git Workflow
```bash
# 1. Khởi tạo repo
git init
git add .
git commit -m "Initial commit: Schedule Management System"

# 2. Kết nối với GitHub
git remote add origin https://github.com/[username]/schedule-management.git
git branch -M main
git push -u origin main

# 3. Cập nhật sau này
git add .
git commit -m "Update: [mô tả thay đổi]"
git push
```

### Deployment Options

**Option 1: Upload lên Hosting (Production)**
- Download ZIP từ GitHub hoặc dùng file schedule-management.zip
- Upload qua FTP/cPanel File Manager
- Import database qua phpMyAdmin
- Cấu hình config/database.php

**Option 2: Clone từ GitHub**
```bash
# Trên hosting (nếu có SSH access)
git clone https://github.com/[username]/schedule-management.git
cd schedule-management
cp config/database.example.php config/database.php
# Edit database.php với thông tin DB
```
