SET FOREIGN_KEY_CHECKS = 0;

-- 1. Role
CREATE TABLE IF NOT EXISTS Role (
    RoleID      VARCHAR(20) PRIMARY KEY,
    RoleName    VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Role (RoleID, RoleName) VALUES ('student', 'Học sinh')
    ON DUPLICATE KEY UPDATE RoleName = RoleName;

INSERT INTO Role (RoleID, RoleName) VALUES ('instructor', 'Giảng viên')
    ON DUPLICATE KEY UPDATE RoleName = RoleName;

INSERT INTO Role (RoleID, RoleName) VALUES ('admin', 'Quản trị viên')
    ON DUPLICATE KEY UPDATE RoleName = RoleName;

-- 2. Users
CREATE TABLE IF NOT EXISTS Users (
    UserID   VARCHAR(20) PRIMARY KEY,
    Name     VARCHAR(100) NOT NULL,
    Email    VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    RoleID   VARCHAR(20) NOT NULL,
    FOREIGN KEY (RoleID) REFERENCES Role(RoleID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Instructor
CREATE TABLE IF NOT EXISTS Instructor (
    InstructorID  VARCHAR(20) PRIMARY KEY,
    UserID        VARCHAR(20) NOT NULL UNIQUE,
    Biography     TEXT,
    ProfileImage  VARCHAR(255),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Student
CREATE TABLE IF NOT EXISTS Student (
    StudentID       VARCHAR(20) PRIMARY KEY,
    UserID          VARCHAR(20) NOT NULL UNIQUE,
    EnrollmentDate  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CompletedCourses TEXT,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Category
CREATE TABLE  categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,  -- NULL nếu là danh mục gốc
    sort_order INT DEFAULT 0,    -- (Tùy chọn) để sắp xếp
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE -- (Tùy chọn) Tự động xóa con khi xóa cha
);

-- 6. Course
CREATE TABLE IF NOT EXISTS Course (
    CourseID    VARCHAR(20) PRIMARY KEY,
    Title       VARCHAR(255) NOT NULL,
    Description TEXT,
    Price       DECIMAL(10,2) NOT NULL,
    CreatedBy   VARCHAR(20) NOT NULL,
    FOREIGN KEY (CreatedBy) REFERENCES Instructor(InstructorID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. CourseCategory (liên kết nhiều-nhiều)
CREATE TABLE IF NOT EXISTS CourseCategory (
    CourseID   VARCHAR(20) NOT NULL,
    CategoryID VARCHAR(20) NOT NULL,
    PRIMARY KEY (CourseID, CategoryID),
    FOREIGN KEY (CourseID)   REFERENCES Course(CourseID),
    FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Chapter (các chương của mỗi course)
CREATE TABLE IF NOT EXISTS Chapter (
    ChapterID    VARCHAR(20) PRIMARY KEY,
    CourseID     VARCHAR(20) NOT NULL,
    Title        VARCHAR(255) NOT NULL,
    Description  TEXT,
    SortOrder    INT NOT NULL DEFAULT 0,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Lesson (thuộc về một chương và một course)
CREATE TABLE IF NOT EXISTS Lesson (
    LessonID    VARCHAR(20) PRIMARY KEY,
    CourseID    VARCHAR(20) NOT NULL,
    ChapterID   VARCHAR(20) NOT NULL,
    Title       VARCHAR(255) NOT NULL,
    Content     TEXT,
    SortOrder   INT NOT NULL DEFAULT 0,
    FOREIGN KEY (CourseID)  REFERENCES Course(CourseID),
    FOREIGN KEY (ChapterID) REFERENCES Chapter(ChapterID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Video (thuộc về một lesson)
CREATE TABLE IF NOT EXISTS Video (
    VideoID    VARCHAR(20) PRIMARY KEY,
    LessonID   VARCHAR(20) NOT NULL,
    Url        VARCHAR(255) NOT NULL,
    Title      VARCHAR(255),
    SortOrder  INT NOT NULL DEFAULT 0,
    FOREIGN KEY (LessonID) REFERENCES Lesson(LessonID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. CourseImage (hình ảnh minh họa cho course)
CREATE TABLE IF NOT EXISTS CourseImage (
    ImageID    VARCHAR(20) PRIMARY KEY,
    CourseID   VARCHAR(20) NOT NULL,
    ImagePath  VARCHAR(255) NOT NULL,
    Caption    VARCHAR(255),
    SortOrder  INT NOT NULL DEFAULT 0,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Cart (giỏ hàng của user)
CREATE TABLE IF NOT EXISTS Cart (
    CartID VARCHAR(20) PRIMARY KEY,
    UserID VARCHAR(20) NOT NULL UNIQUE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. CartItem (item trong cart)
CREATE TABLE IF NOT EXISTS CartItem (
    CartItemID VARCHAR(20) PRIMARY KEY,
    CartID     VARCHAR(20) NOT NULL,
    CourseID   VARCHAR(20) NOT NULL,
    Quantity   INT NOT NULL CHECK (Quantity > 0),
    FOREIGN KEY (CartID)   REFERENCES Cart(CartID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Orders
CREATE TABLE IF NOT EXISTS Orders (
    OrderID     VARCHAR(20) PRIMARY KEY,
    UserID      VARCHAR(20) NOT NULL,
    OrderDate   DATETIME DEFAULT CURRENT_TIMESTAMP,
    TotalAmount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. OrderDetail (chi tiết từng course trong order)
CREATE TABLE IF NOT EXISTS OrderDetail (
    OrderID  VARCHAR(20) NOT NULL,
    CourseID VARCHAR(20) NOT NULL,
    Price    DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (OrderID, CourseID),
    FOREIGN KEY (OrderID)  REFERENCES Orders(OrderID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Review
CREATE TABLE IF NOT EXISTS Review (
    ReviewID VARCHAR(20) PRIMARY KEY,
    UserID   VARCHAR(20) NOT NULL,
    CourseID VARCHAR(20) NOT NULL,
    Rating   INT NOT NULL CHECK (Rating BETWEEN 1 AND 5),
    Comment  TEXT,
    FOREIGN KEY (UserID)   REFERENCES Users(UserID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Payment
CREATE TABLE IF NOT EXISTS Payment (
    PaymentID      VARCHAR(20) PRIMARY KEY,
    OrderID        VARCHAR(20) NOT NULL,
    PaymentDate    DATETIME DEFAULT CURRENT_TIMESTAMP,
    PaymentMethod  VARCHAR(50),
    PaymentStatus  VARCHAR(50),
    Amount         DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
