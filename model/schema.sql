SET FOREIGN_KEY_CHECKS = 0;

-- 1. Role
CREATE TABLE IF NOT EXISTS Role (
    RoleID      VARCHAR(20) PRIMARY KEY,
    RoleName    VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Role (RoleID, RoleName) VALUES ('student', 'Học sinh')
    ON DUPLICATE KEY UPDATE RoleName = RoleName;

INSERT INTO Role (RoleID, RoleName) VALUES ('instructor', 'Giảng viên')
    ON DUPLICATE KEY UPDATE RoleName = RoleName;

INSERT INTO Role (RoleID, RoleName) VALUES ('admin', 'Quản trị viên')
    ON DUPLICATE KEY UPDATE RoleName = RoleName;

-- 2. Users
CREATE TABLE IF NOT EXISTS Users (
    UserID   VARCHAR(40) PRIMARY KEY,
    FirstName     VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Email    VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    RoleID   VARCHAR(36) NOT NULL,
    ProfileImage  VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (RoleID) REFERENCES Role(RoleID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Instructor
CREATE TABLE IF NOT EXISTS Instructor (
    InstructorID  VARCHAR(40) PRIMARY KEY,
    UserID        VARCHAR(40) NOT NULL UNIQUE,
    Biography     TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Student
CREATE TABLE IF NOT EXISTS Student (
    StudentID       VARCHAR(40) PRIMARY KEY,
    UserID          VARCHAR(40) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Category
CREATE TABLE  categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,  -- NULL nếu là danh mục gốc
    sort_order INT DEFAULT 0,    -- (Tùy chọn) để sắp xếp
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE -- (Tùy chọn) Tự động xóa con khi xóa cha
);
-- Insert root categories
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (1, 'Phát triển', NULL, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (33, 'Kinh doanh', NULL, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (41, 'CNTT & Phần mềm', NULL, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (49, 'Thiết kế', NULL, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (56, 'Marketing', NULL, 5);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (63, 'Phát triển cá nhân', NULL, 6);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (69, 'Âm nhạc', NULL, 7);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (73, 'Sức khỏe & Thể hình', NULL, 8);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (78, 'Giảng dạy & Học thuật', NULL, 9);

-- Insert sub-categories for Phát triển (ID: 1)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (2, 'Lập trình Web', 1, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (14, 'Lập trình Mobile', 1, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (20, 'Lập trình Game', 1, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (24, 'Phát triển phần mềm', 1, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (30, 'Lập trình nhúng / IoT', 1, 5);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (31, 'Blockchain', 1, 6);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (32, 'No-Code Development', 1, 7);

-- Insert sub-categories for Lập trình Web (ID: 2)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (3, 'HTML & CSS', 2, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (4, 'JavaScript', 2, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (5, 'ReactJS', 2, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (6, 'VueJS', 2, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (7, 'Angular', 2, 5);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (8, 'PHP', 2, 6);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (9, 'Laravel', 2, 7);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (10, 'ASP.NET', 2, 8);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (11, 'Django', 2, 9);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (12, 'NodeJS', 2, 10);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (13, 'Web APIs', 2, 11);

-- Insert sub-categories for Lập trình Mobile (ID: 14)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (15, 'Android Development', 14, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (16, 'iOS Development', 14, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (17, 'React Native', 14, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (18, 'Flutter', 14, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (19, 'Xamarin', 14, 5);

-- Insert sub-categories for Lập trình Game (ID: 20)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (21, 'Unity', 20, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (22, 'Unreal Engine', 20, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (23, 'Godot', 20, 3);

-- Insert sub-categories for Phát triển phần mềm (ID: 24)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (25, 'Python', 24, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (26, 'Java', 24, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (27, 'C++', 24, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (28, 'C#', 24, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (29, 'Rust', 24, 5);

-- Insert sub-categories for Kinh doanh (ID: 33)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (34, 'Quản trị kinh doanh', 33, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (35, 'Doanh nghiệp khởi nghiệp', 33, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (36, 'Quản lý dự án', 33, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (37, 'Agile & Scrum', 33, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (38, 'Tài chính & Kế toán', 33, 5);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (39, 'Phân tích kinh doanh (Business Analytics)', 33, 6);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (40, 'Nhân sự (HR)', 33, 7);

-- Insert sub-categories for CNTT & Phần mềm (ID: 41)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (42, 'Mạng máy tính & Bảo mật', 41, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (43, 'Ethical Hacking', 41, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (44, 'Khoa học dữ liệu (Data Science)', 41, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (45, 'Trí tuệ nhân tạo (AI)', 41, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (46, 'Hệ điều hành (Linux, Windows Server)', 41, 5);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (47, 'DevOps', 41, 6);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (48, 'Kiểm thử phần mềm (Software Testing)', 41, 7);

-- Insert sub-categories for Thiết kế (ID: 49)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (50, 'Thiết kế Web', 49, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (51, 'Thiết kế UI/UX', 49, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (52, 'Adobe Photoshop', 49, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (53, 'Illustrator', 49, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (54, 'Thiết kế đồ họa 2D/3D', 49, 5);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (55, 'Thiết kế sản phẩm', 49, 6);

-- Insert sub-categories for Marketing (ID: 56)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (57, 'Digital Marketing', 56, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (58, 'SEO', 56, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (59, 'Google Ads / Facebook Ads', 56, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (60, 'Content Marketing', 56, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (61, 'Email Marketing', 56, 5);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (62, 'Affiliate Marketing', 56, 6);

-- Insert sub-categories for Phát triển cá nhân (ID: 63)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (64, 'Kỹ năng giao tiếp', 63, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (65, 'Lãnh đạo', 63, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (66, 'Quản lý thời gian', 63, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (67, 'Tư duy phản biện', 63, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (68, 'Đọc nhanh & Ghi nhớ', 63, 5);

-- Insert sub-categories for Âm nhạc (ID: 69)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (70, 'Nhạc cụ (Piano, Guitar, v.v.)', 69, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (71, 'Sản xuất âm nhạc', 69, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (72, 'DJ & Âm thanh điện tử', 69, 3);

-- Insert sub-categories for Sức khỏe & Thể hình (ID: 73)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (74, 'Yoga', 73, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (75, 'Thiền', 73, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (76, 'Dinh dưỡng', 73, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (77, 'Tập luyện thể hình', 73, 4);

-- Insert sub-categories for Giảng dạy & Học thuật (ID: 78)
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (79, 'Toán học', 78, 1);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (80, 'Vật lý', 78, 2);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (81, 'Lập trình cho trẻ em', 78, 3);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (82, 'Khoa học máy tính', 78, 4);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (83, 'IELTS, TOEIC, TOEFL', 78, 5);

-- 6. Course
CREATE TABLE IF NOT EXISTS Course (
    CourseID    VARCHAR(40) PRIMARY KEY,
    Title       VARCHAR(255) NOT NULL,
    Description TEXT,
    Price       DECIMAL(10,2) NOT NULL,
    CreatedBy   VARCHAR(40) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseInstructor (
    CourseID      VARCHAR(40),
    InstructorID  VARCHAR(40),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (CourseID, InstructorID), 
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE,
    FOREIGN KEY (InstructorID) REFERENCES Instructor(InstructorID) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. CourseCategory (liên kết nhiều-nhiều)
CREATE TABLE IF NOT EXISTS CourseCategory (
    CourseID   VARCHAR(40) NOT NULL,
    CategoryID INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (CourseID, CategoryID),
    FOREIGN KEY (CourseID)   REFERENCES Course(CourseID),
    FOREIGN KEY (CategoryID) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 8. Chapter (các chương của mỗi course)
CREATE TABLE IF NOT EXISTS Chapter (
    ChapterID    VARCHAR(40) PRIMARY KEY,
    CourseID     VARCHAR(40) NOT NULL,
    Title        VARCHAR(255) NOT NULL,
    Description  TEXT,
    SortOrder    INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Lesson (thuộc về một chương và một course)
CREATE TABLE IF NOT EXISTS Lesson (
    LessonID    VARCHAR(40) PRIMARY KEY,
    CourseID    VARCHAR(40) NOT NULL,
    ChapterID   VARCHAR(40) NOT NULL,
    Title       VARCHAR(255) NOT NULL,
    Content     TEXT,
    SortOrder   INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CourseID)  REFERENCES Course(CourseID),
    FOREIGN KEY (ChapterID) REFERENCES Chapter(ChapterID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Video (thuộc về một lesson)
CREATE TABLE IF NOT EXISTS Video (
    VideoID    VARCHAR(40) PRIMARY KEY,
    LessonID   VARCHAR(40) NOT NULL,
    Url        VARCHAR(255) NOT NULL,
    Title      VARCHAR(255),
    SortOrder  INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (LessonID) REFERENCES Lesson(LessonID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. CourseImage (hình ảnh minh họa cho course)
CREATE TABLE IF NOT EXISTS CourseImage (
    ImageID    VARCHAR(40) PRIMARY KEY,
    CourseID   VARCHAR(40) NOT NULL,
    ImagePath  VARCHAR(255) NOT NULL,
    Caption    VARCHAR(255),
    SortOrder  INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Cart (giỏ hàng của user)
CREATE TABLE IF NOT EXISTS Cart (
    CartID VARCHAR(40) PRIMARY KEY,
    UserID VARCHAR(40) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. CartItem (item trong cart)
CREATE TABLE IF NOT EXISTS CartItem (
    CartItemID VARCHAR(40) PRIMARY KEY,
    CartID     VARCHAR(40) NOT NULL,
    CourseID   VARCHAR(40) NOT NULL,
    Quantity   INT NOT NULL CHECK (Quantity > 0),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CartID)   REFERENCES Cart(CartID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Orders
CREATE TABLE IF NOT EXISTS Orders (
    OrderID     VARCHAR(40) PRIMARY KEY,
    UserID      VARCHAR(40) NOT NULL,
    OrderDate   DATETIME DEFAULT CURRENT_TIMESTAMP,
    TotalAmount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. OrderDetail (chi tiết từng course trong order)
CREATE TABLE IF NOT EXISTS OrderDetail (
    OrderID  VARCHAR(40) NOT NULL,
    CourseID VARCHAR(40) NOT NULL,
    Price    DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (OrderID, CourseID),
    FOREIGN KEY (OrderID)  REFERENCES Orders(OrderID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Review
CREATE TABLE IF NOT EXISTS Review (
    ReviewID VARCHAR(40) PRIMARY KEY,
    UserID   VARCHAR(40) NOT NULL,
    CourseID VARCHAR(40) NOT NULL,
    Rating   INT NOT NULL CHECK (Rating BETWEEN 1 AND 5),
    Comment  TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID)   REFERENCES Users(UserID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Payment
CREATE TABLE IF NOT EXISTS Payment (
    PaymentID      VARCHAR(40) PRIMARY KEY,
    OrderID        VARCHAR(40) NOT NULL,
    PaymentDate    DATETIME DEFAULT CURRENT_TIMESTAMP,
    PaymentMethod  VARCHAR(50),
    PaymentStatus  VARCHAR(50),
    Amount         DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE password_resets
MODIFY created_at TIMESTAMP NOT NULL
DEFAULT CURRENT_TIMESTAMP;
