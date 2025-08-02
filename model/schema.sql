SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS Role (
    RoleID VARCHAR(20) PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Role (RoleID, RoleName) VALUES ('student', 'Học sinh')
    ON DUPLICATE KEY UPDATE RoleName = VALUES(RoleName);

INSERT INTO Role (RoleID, RoleName) VALUES ('instructor', 'Giảng viên')
    ON DUPLICATE KEY UPDATE RoleName = VALUES(RoleName);

INSERT INTO Role (RoleID, RoleName) VALUES ('admin', 'Quản trị viên')
    ON DUPLICATE KEY UPDATE RoleName = VALUES(RoleName);

CREATE TABLE IF NOT EXISTS Role_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    RoleID VARCHAR(20),
    RoleName VARCHAR(50),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Role_AI
AFTER INSERT ON Role
FOR EACH ROW
BEGIN
    INSERT INTO Role_History (RoleID, RoleName, created_at_original, DML_Type, DML_User)
    VALUES (NEW.RoleID, NEW.RoleName, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Role_AU
AFTER UPDATE ON Role
FOR EACH ROW
BEGIN
    INSERT INTO Role_History (RoleID, RoleName, created_at_original, DML_Type, DML_User)
    VALUES (OLD.RoleID, OLD.RoleName, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Role_AD
AFTER DELETE ON Role
FOR EACH ROW
BEGIN
    INSERT INTO Role_History (RoleID, RoleName, created_at_original, DML_Type, DML_User)
    VALUES (OLD.RoleID, OLD.RoleName, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Users (
    UserID VARCHAR(40) PRIMARY KEY,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    RoleID VARCHAR(20) NOT NULL,
    ProfileImage VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (RoleID) REFERENCES Role(RoleID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Users_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    UserID VARCHAR(40),
    FirstName VARCHAR(100),
    LastName VARCHAR(100),
    Email VARCHAR(100),
    Password VARCHAR(255),
    RoleID VARCHAR(20),
    ProfileImage VARCHAR(255),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Users_AI
AFTER INSERT ON Users
FOR EACH ROW
BEGIN
    INSERT INTO Users_History (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at_original, DML_Type, DML_User)
    VALUES (NEW.UserID, NEW.FirstName, NEW.LastName, NEW.Email, NEW.Password, NEW.RoleID, NEW.ProfileImage, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Users_AU
AFTER UPDATE ON Users
FOR EACH ROW
BEGIN
    INSERT INTO Users_History (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at_original, DML_Type, DML_User)
    VALUES (OLD.UserID, OLD.FirstName, OLD.LastName, OLD.Email, OLD.Password, OLD.RoleID, OLD.ProfileImage, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Users_AD
AFTER DELETE ON Users
FOR EACH ROW
BEGIN
    INSERT INTO Users_History (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at_original, DML_Type, DML_User)
    VALUES (OLD.UserID, OLD.FirstName, OLD.LastName, OLD.Email, OLD.Password, OLD.RoleID, OLD.ProfileImage, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Instructor (
    InstructorID VARCHAR(40) PRIMARY KEY,
    UserID VARCHAR(40) NOT NULL UNIQUE,
    Biography TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Instructor_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    InstructorID VARCHAR(40),
    UserID VARCHAR(40),
    Biography TEXT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Instructor_AI
AFTER INSERT ON Instructor
FOR EACH ROW
BEGIN
    INSERT INTO Instructor_History (InstructorID, UserID, Biography, created_at_original, DML_Type, DML_User)
    VALUES (NEW.InstructorID, NEW.UserID, NEW.Biography, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Instructor_AU
AFTER UPDATE ON Instructor
FOR EACH ROW
BEGIN
    INSERT INTO Instructor_History (InstructorID, UserID, Biography, created_at_original, DML_Type, DML_User)
    VALUES (OLD.InstructorID, OLD.UserID, OLD.Biography, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Instructor_AD
AFTER DELETE ON Instructor
FOR EACH ROW
BEGIN
    INSERT INTO Instructor_History (InstructorID, UserID, Biography, created_at_original, DML_Type, DML_User)
    VALUES (OLD.InstructorID, OLD.UserID, OLD.Biography, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Student (
    StudentID VARCHAR(40) PRIMARY KEY,
    UserID VARCHAR(40) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Student_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID VARCHAR(40),
    UserID VARCHAR(40),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Student_AI
AFTER INSERT ON Student
FOR EACH ROW
BEGIN
    INSERT INTO Student_History (StudentID, UserID, created_at_original, DML_Type, DML_User)
    VALUES (NEW.StudentID, NEW.UserID, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Student_AU
AFTER UPDATE ON Student
FOR EACH ROW
BEGIN
    INSERT INTO Student_History (StudentID, UserID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.StudentID, OLD.UserID, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Student_AD
AFTER DELETE ON Student
FOR EACH ROW
BEGIN
    INSERT INTO Student_History (StudentID, UserID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.StudentID, OLD.UserID, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (1, 'Phát triển', NULL, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (33, 'Kinh doanh', NULL, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (41, 'CNTT & Phần mềm', NULL, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (49, 'Thiết kế', NULL, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (56, 'Marketing', NULL, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (63, 'Phát triển cá nhân', NULL, 6) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (69, 'Âm nhạc', NULL, 7) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (73, 'Sức khỏe & Thể hình', NULL, 8) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (78, 'Giảng dạy & Học thuật', NULL, 9) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (2, 'Lập trình Web', 1, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (14, 'Lập trình Mobile', 1, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (20, 'Lập trình Game', 1, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (24, 'Phát triển phần mềm', 1, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (30, 'Lập trình nhúng / IoT', 1, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (31, 'Blockchain', 1, 6) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (32, 'No-Code Development', 1, 7) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (3, 'HTML & CSS', 2, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (4, 'JavaScript', 2, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (5, 'ReactJS', 2, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (6, 'VueJS', 2, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (7, 'Angular', 2, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (8, 'PHP', 2, 6) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (9, 'Laravel', 2, 7) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (10, 'ASP.NET', 2, 8) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (11, 'Django', 2, 9) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (12, 'NodeJS', 2, 10) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (13, 'Web APIs', 2, 11) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (15, 'Android Development', 14, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (16, 'iOS Development', 14, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (17, 'React Native', 14, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (18, 'Flutter', 14, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (19, 'Xamarin', 14, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (21, 'Unity', 20, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (22, 'Unreal Engine', 20, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (23, 'Godot', 20, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (25, 'Python', 24, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (26, 'Java', 24, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (27, 'C++', 24, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (28, 'C#', 24, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (29, 'Rust', 24, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (34, 'Quản trị kinh doanh', 33, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (35, 'Doanh nghiệp khởi nghiệp', 33, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (36, 'Quản lý dự án', 33, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (37, 'Agile & Scrum', 33, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (38, 'Tài chính & Kế toán', 33, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (39, 'Phân tích kinh doanh (Business Analytics)', 33, 6) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (40, 'Nhân sự (HR)', 33, 7) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (42, 'Mạng máy tính & Bảo mật', 41, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (43, 'Ethical Hacking', 41, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (44, 'Khoa học dữ liệu (Data Science)', 41, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (45, 'Trí tuệ nhân tạo (AI)', 41, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (46, 'Hệ điều hành (Linux, Windows Server)', 41, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (47, 'DevOps', 41, 6) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (48, 'Kiểm thử phần mềm (Software Testing)', 41, 7) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (50, 'Thiết kế Web', 49, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (51, 'Thiết kế UI/UX', 49, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (52, 'Adobe Photoshop', 49, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (53, 'Illustrator', 49, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (54, 'Thiết kế đồ họa 2D/3D', 49, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (55, 'Thiết kế sản phẩm', 49, 6) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (57, 'Digital Marketing', 56, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (58, 'SEO', 56, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (59, 'Google Ads / Facebook Ads', 56, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (60, 'Content Marketing', 56, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (61, 'Email Marketing', 56, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (62, 'Affiliate Marketing', 56, 6) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (64, 'Kỹ năng giao tiếp', 63, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (65, 'Lãnh đạo', 63, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (66, 'Quản lý thời gian', 63, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (67, 'Tư duy phản biện', 63, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (68, 'Đọc nhanh & Ghi nhớ', 63, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (70, 'Nhạc cụ (Piano, Guitar, v.v.)', 69, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (71, 'Sản xuất âm nhạc', 69, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (72, 'DJ & Âm thanh điện tử', 69, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (74, 'Yoga', 73, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (75, 'Thiền', 73, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (76, 'Dinh dưỡng', 73, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (77, 'Tập luyện thể hình', 73, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

INSERT INTO categories (id, name, parent_id, sort_order) VALUES (79, 'Toán học', 78, 1) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (80, 'Vật lý', 78, 2) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (81, 'Lập trình cho trẻ em', 78, 3) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (82, 'Khoa học máy tính', 78, 4) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);
INSERT INTO categories (id, name, parent_id, sort_order) VALUES (83, 'IELTS, TOEIC, TOEFL', 78, 5) ON DUPLICATE KEY UPDATE name=VALUES(name), parent_id=VALUES(parent_id), sort_order=VALUES(sort_order);

CREATE TABLE IF NOT EXISTS categories_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    id INT,
    name VARCHAR(255),
    parent_id INT,
    sort_order INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_categories_AI
AFTER INSERT ON categories
FOR EACH ROW
BEGIN
    INSERT INTO categories_History (id, name, parent_id, sort_order, created_at_original, DML_Type, DML_User)
    VALUES (NEW.id, NEW.name, NEW.parent_id, NEW.sort_order, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_categories_AU
AFTER UPDATE ON categories
FOR EACH ROW
BEGIN
    INSERT INTO categories_History (id, name, parent_id, sort_order, created_at_original, DML_Type, DML_User)
    VALUES (OLD.id, OLD.name, OLD.parent_id, OLD.sort_order, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_categories_AD
AFTER DELETE ON categories
FOR EACH ROW
BEGIN
    INSERT INTO categories_History (id, name, parent_id, sort_order, created_at_original, DML_Type, DML_User)
    VALUES (OLD.id, OLD.name, OLD.parent_id, OLD.sort_order, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Course (
    CourseID VARCHAR(40) PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    Price DECIMAL(10,2) NOT NULL,
    Difficulty VARCHAR(40),
    Language VARCHAR(40),
    CreatedBy VARCHAR(40) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Instructor(InstructorID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Course_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    CourseID VARCHAR(40),
    Title VARCHAR(255),
    Description TEXT,
    Price DECIMAL(10,2),
    Difficulty VARCHAR(40),
    Language VARCHAR(40),
    CreatedBy VARCHAR(40),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Course_AI
AFTER INSERT ON Course
FOR EACH ROW
BEGIN
    INSERT INTO Course_History (CourseID, Title, Description, Price, Difficulty, Language, CreatedBy, created_at_original, DML_Type, DML_User)
    VALUES (NEW.CourseID, NEW.Title, NEW.Description, NEW.Price, NEW.Difficulty, NEW.Language, NEW.CreatedBy, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Course_AU
AFTER UPDATE ON Course
FOR EACH ROW
BEGIN
    INSERT INTO Course_History (CourseID, Title, Description, Price, Difficulty, Language, CreatedBy, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CourseID, OLD.Title, OLD.Description, OLD.Price, OLD.Difficulty, OLD.Language, OLD.CreatedBy, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Course_AD
AFTER DELETE ON Course
FOR EACH ROW
BEGIN
    INSERT INTO Course_History (CourseID, Title, Description, Price, Difficulty, Language, CreatedBy, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CourseID, OLD.Title, OLD.Description, OLD.Price, OLD.Difficulty, OLD.Language, OLD.CreatedBy, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseInstructor (
    CourseID VARCHAR(40),
    InstructorID VARCHAR(40),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (CourseID, InstructorID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE,
    FOREIGN KEY (InstructorID) REFERENCES Instructor(InstructorID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseInstructor_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    CourseID VARCHAR(40),
    InstructorID VARCHAR(40),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseInstructor_AI
AFTER INSERT ON CourseInstructor
FOR EACH ROW
BEGIN
    INSERT INTO CourseInstructor_History (CourseID, InstructorID, created_at_original, DML_Type, DML_User)
    VALUES (NEW.CourseID, NEW.InstructorID, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseInstructor_AU
AFTER UPDATE ON CourseInstructor
FOR EACH ROW
BEGIN
    INSERT INTO CourseInstructor_History (CourseID, InstructorID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CourseID, OLD.InstructorID, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseInstructor_AD
AFTER DELETE ON CourseInstructor
FOR EACH ROW
BEGIN
    INSERT INTO CourseInstructor_History (CourseID, InstructorID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CourseID, OLD.InstructorID, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseCategory (
    CourseID VARCHAR(40) NOT NULL,
    CategoryID INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (CourseID, CategoryID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE,
    FOREIGN KEY (CategoryID) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseCategory_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    CourseID VARCHAR(40),
    CategoryID INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseCategory_AI
AFTER INSERT ON CourseCategory
FOR EACH ROW
BEGIN
    INSERT INTO CourseCategory_History (CourseID, CategoryID, created_at_original, DML_Type, DML_User)
    VALUES (NEW.CourseID, NEW.CategoryID, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseCategory_AU
AFTER UPDATE ON CourseCategory
FOR EACH ROW
BEGIN
    INSERT INTO CourseCategory_History (CourseID, CategoryID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CourseID, OLD.CategoryID, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseCategory_AD
AFTER DELETE ON CourseCategory
FOR EACH ROW
BEGIN
    INSERT INTO CourseCategory_History (CourseID, CategoryID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CourseID, OLD.CategoryID, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseChapter (
    ChapterID VARCHAR(40) PRIMARY KEY,
    CourseID VARCHAR(40) NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    SortOrder INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseChapter_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    ChapterID VARCHAR(40),
    CourseID VARCHAR(40),
    Title VARCHAR(255),
    Description TEXT,
    SortOrder INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseChapter_AI
AFTER INSERT ON CourseChapter
FOR EACH ROW
BEGIN
    INSERT INTO CourseChapter_History (ChapterID, CourseID, Title, Description, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (NEW.ChapterID, NEW.CourseID, NEW.Title, NEW.Description, NEW.SortOrder, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseChapter_AU
AFTER UPDATE ON CourseChapter
FOR EACH ROW
BEGIN
    INSERT INTO CourseChapter_History (ChapterID, CourseID, Title, Description, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ChapterID, OLD.CourseID, OLD.Title, OLD.Description, OLD.SortOrder, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseChapter_AD
AFTER DELETE ON CourseChapter
FOR EACH ROW
BEGIN
    INSERT INTO CourseChapter_History (ChapterID, CourseID, Title, Description, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ChapterID, OLD.CourseID, OLD.Title, OLD.Description, OLD.SortOrder, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseLesson (
    LessonID VARCHAR(40) PRIMARY KEY,
    CourseID VARCHAR(40) NOT NULL,
    ChapterID VARCHAR(40) NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Content TEXT,
    SortOrder INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE,
    FOREIGN KEY (ChapterID) REFERENCES CourseChapter(ChapterID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseLesson_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    LessonID VARCHAR(40),
    CourseID VARCHAR(40),
    ChapterID VARCHAR(40),
    Title VARCHAR(255),
    Content TEXT,
    SortOrder INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseLesson_AI
AFTER INSERT ON CourseLesson
FOR EACH ROW
BEGIN
    INSERT INTO CourseLesson_History (LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (NEW.LessonID, NEW.CourseID, NEW.ChapterID, NEW.Title, NEW.Content, NEW.SortOrder, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseLesson_AU
AFTER UPDATE ON CourseLesson
FOR EACH ROW
BEGIN
    INSERT INTO CourseLesson_History (LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.LessonID, OLD.CourseID, OLD.ChapterID, OLD.Title, OLD.Content, OLD.SortOrder, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseLesson_AD
AFTER DELETE ON CourseLesson
FOR EACH ROW
BEGIN
    INSERT INTO CourseLesson_History (LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.LessonID, OLD.CourseID, OLD.ChapterID, OLD.Title, OLD.Content, OLD.SortOrder, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseVideo (
    VideoID VARCHAR(40) PRIMARY KEY,
    LessonID VARCHAR(40) NOT NULL,
    Url VARCHAR(255) NOT NULL,
    Title VARCHAR(255),
    Duration INT NOT NULL DEFAULT 0,
    SortOrder INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (LessonID) REFERENCES CourseLesson(LessonID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseVideo_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    VideoID VARCHAR(40),
    LessonID VARCHAR(40),
    Url VARCHAR(255),
    Title VARCHAR(255),
    Duration INT,
    SortOrder INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseVideo_AI
AFTER INSERT ON CourseVideo
FOR EACH ROW
BEGIN
    INSERT INTO CourseVideo_History (VideoID, LessonID, Url, Title, Duration, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (NEW.VideoID, NEW.LessonID, NEW.Url, NEW.Title, NEW.Duration, NEW.SortOrder, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseVideo_AU
AFTER UPDATE ON CourseVideo
FOR EACH ROW
BEGIN
    INSERT INTO CourseVideo_History (VideoID, LessonID, Url, Title, Duration, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.VideoID, OLD.LessonID, OLD.Url, OLD.Title, OLD.Duration, OLD.SortOrder, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseVideo_AD
AFTER DELETE ON CourseVideo
FOR EACH ROW
BEGIN
    INSERT INTO CourseVideo_History (VideoID, LessonID, Url, Title, Duration, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.VideoID, OLD.LessonID, OLD.Url, OLD.Title, OLD.Duration, OLD.SortOrder, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseResource (
    ResourceID VARCHAR(40) PRIMARY KEY,
    LessonID VARCHAR(40) NOT NULL,
    ResourcePath VARCHAR(255) NOT NULL,
    Title VARCHAR(255),
    SortOrder INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (LessonID) REFERENCES CourseLesson(LessonID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseResource_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    ResourceID VARCHAR(40),
    LessonID VARCHAR(40),
    ResourcePath VARCHAR(255),
    Title VARCHAR(255),
    SortOrder INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseResource_AI
AFTER INSERT ON CourseResource
FOR EACH ROW
BEGIN
    INSERT INTO CourseResource_History (ResourceID, LessonID, ResourcePath, Title, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (NEW.ResourceID, NEW.LessonID, NEW.ResourcePath, NEW.Title, NEW.SortOrder, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseResource_AU
AFTER UPDATE ON CourseResource
FOR EACH ROW
BEGIN
    INSERT INTO CourseResource_History (ResourceID, LessonID, ResourcePath, Title, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ResourceID, OLD.LessonID, OLD.ResourcePath, OLD.Title, OLD.SortOrder, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseResource_AD
AFTER DELETE ON CourseResource
FOR EACH ROW
BEGIN
    INSERT INTO CourseResource_History (ResourceID, LessonID, ResourcePath, Title, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ResourceID, OLD.LessonID, OLD.ResourcePath, OLD.Title, OLD.SortOrder, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseImage (
    ImageID VARCHAR(40) PRIMARY KEY,
    CourseID VARCHAR(40) NOT NULL,
    ImagePath VARCHAR(255) NOT NULL,
    Caption VARCHAR(255),
    SortOrder INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseImage_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    ImageID VARCHAR(40),
    CourseID VARCHAR(40),
    ImagePath VARCHAR(255),
    Caption VARCHAR(255),
    SortOrder INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseImage_AI
AFTER INSERT ON CourseImage
FOR EACH ROW
BEGIN
    INSERT INTO CourseImage_History (ImageID, CourseID, ImagePath, Caption, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (NEW.ImageID, NEW.CourseID, NEW.ImagePath, NEW.Caption, NEW.SortOrder, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseImage_AU
AFTER UPDATE ON CourseImage
FOR EACH ROW
BEGIN
    INSERT INTO CourseImage_History (ImageID, CourseID, ImagePath, Caption, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ImageID, OLD.CourseID, OLD.ImagePath, OLD.Caption, OLD.SortOrder, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseImage_AD
AFTER DELETE ON CourseImage
FOR EACH ROW
BEGIN
    INSERT INTO CourseImage_History (ImageID, CourseID, ImagePath, Caption, SortOrder, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ImageID, OLD.CourseID, OLD.ImagePath, OLD.Caption, OLD.SortOrder, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseObjective (
    ObjectiveID VARCHAR(40) NOT NULL,
    CourseID VARCHAR(40) NOT NULL,
    Objective VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (CourseID, ObjectiveID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseObjective_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    ObjectiveID VARCHAR(40),
    CourseID VARCHAR(40),
    Objective VARCHAR(255),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseObjective_AI
AFTER INSERT ON CourseObjective
FOR EACH ROW
BEGIN
    INSERT INTO CourseObjective_History (ObjectiveID, CourseID, Objective, created_at_original, DML_Type, DML_User)
    VALUES (NEW.ObjectiveID, NEW.CourseID, NEW.Objective, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseObjective_AU
AFTER UPDATE ON CourseObjective
FOR EACH ROW
BEGIN
    INSERT INTO CourseObjective_History (ObjectiveID, CourseID, Objective, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ObjectiveID, OLD.CourseID, OLD.Objective, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseObjective_AD
AFTER DELETE ON CourseObjective
FOR EACH ROW
BEGIN
    INSERT INTO CourseObjective_History (ObjectiveID, CourseID, Objective, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ObjectiveID, OLD.CourseID, OLD.Objective, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CourseRequirement (
    RequirementID VARCHAR(40) NOT NULL,
    CourseID VARCHAR(40) NOT NULL,
    Requirement VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (CourseID, RequirementID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CourseRequirement_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    RequirementID VARCHAR(40),
    CourseID VARCHAR(40),
    Requirement VARCHAR(255),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CourseRequirement_AI
AFTER INSERT ON CourseRequirement
FOR EACH ROW
BEGIN
    INSERT INTO CourseRequirement_History (RequirementID, CourseID, Requirement, created_at_original, DML_Type, DML_User)
    VALUES (NEW.RequirementID, NEW.CourseID, NEW.Requirement, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseRequirement_AU
AFTER UPDATE ON CourseRequirement
FOR EACH ROW
BEGIN
    INSERT INTO CourseRequirement_History (RequirementID, CourseID, Requirement, created_at_original, DML_Type, DML_User)
    VALUES (OLD.RequirementID, OLD.CourseID, OLD.Requirement, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CourseRequirement_AD
AFTER DELETE ON CourseRequirement
FOR EACH ROW
BEGIN
    INSERT INTO CourseRequirement_History (RequirementID, CourseID, Requirement, created_at_original, DML_Type, DML_User)
    VALUES (OLD.RequirementID, OLD.CourseID, OLD.Requirement, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Cart (
    CartID VARCHAR(40) PRIMARY KEY,
    UserID VARCHAR(40) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Cart_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    CartID VARCHAR(40),
    UserID VARCHAR(40),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Cart_AI
AFTER INSERT ON Cart
FOR EACH ROW
BEGIN
    INSERT INTO Cart_History (CartID, UserID, created_at_original, DML_Type, DML_User)
    VALUES (NEW.CartID, NEW.UserID, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Cart_AU
AFTER UPDATE ON Cart
FOR EACH ROW
BEGIN
    INSERT INTO Cart_History (CartID, UserID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CartID, OLD.UserID, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Cart_AD
AFTER DELETE ON Cart
FOR EACH ROW
BEGIN
    INSERT INTO Cart_History (CartID, UserID, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CartID, OLD.UserID, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS CartItem (
    CartItemID VARCHAR(40) PRIMARY KEY,
    CartID VARCHAR(40) NOT NULL,
    CourseID VARCHAR(40) NOT NULL,
    Quantity INT NOT NULL CHECK (Quantity > 0),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CartID) REFERENCES Cart(CartID) ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CartItem_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    CartItemID VARCHAR(40),
    CartID VARCHAR(40),
    CourseID VARCHAR(40),
    Quantity INT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_CartItem_AI
AFTER INSERT ON CartItem
FOR EACH ROW
BEGIN
    INSERT INTO CartItem_History (CartItemID, CartID, CourseID, Quantity, created_at_original, DML_Type, DML_User)
    VALUES (NEW.CartItemID, NEW.CartID, NEW.CourseID, NEW.Quantity, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CartItem_AU
AFTER UPDATE ON CartItem
FOR EACH ROW
BEGIN
    INSERT INTO CartItem_History (CartItemID, CartID, CourseID, Quantity, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CartItemID, OLD.CartID, OLD.CourseID, OLD.Quantity, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_CartItem_AD
AFTER DELETE ON CartItem
FOR EACH ROW
BEGIN
    INSERT INTO CartItem_History (CartItemID, CartID, CourseID, Quantity, created_at_original, DML_Type, DML_User)
    VALUES (OLD.CartItemID, OLD.CartID, OLD.CourseID, OLD.Quantity, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Orders (
    OrderID VARCHAR(40) PRIMARY KEY,
    UserID VARCHAR(40) NOT NULL,
    OrderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    TotalAmount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Orders_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID VARCHAR(40),
    UserID VARCHAR(40),
    OrderDate DATETIME,
    TotalAmount DECIMAL(10,2),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Orders_AI
AFTER INSERT ON Orders
FOR EACH ROW
BEGIN
    INSERT INTO Orders_History (OrderID, UserID, OrderDate, TotalAmount, created_at_original, DML_Type, DML_User)
    VALUES (NEW.OrderID, NEW.UserID, NEW.OrderDate, NEW.TotalAmount, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Orders_AU
AFTER UPDATE ON Orders
FOR EACH ROW
BEGIN
    INSERT INTO Orders_History (OrderID, UserID, OrderDate, TotalAmount, created_at_original, DML_Type, DML_User)
    VALUES (OLD.OrderID, OLD.UserID, OLD.OrderDate, OLD.TotalAmount, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Orders_AD
AFTER DELETE ON Orders
FOR EACH ROW
BEGIN
    INSERT INTO Orders_History (OrderID, UserID, OrderDate, TotalAmount, created_at_original, DML_Type, DML_User)
    VALUES (OLD.OrderID, OLD.UserID, OLD.OrderDate, OLD.TotalAmount, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS OrderDetail (
    OrderID VARCHAR(40) NOT NULL,
    CourseID VARCHAR(40) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (OrderID, CourseID),
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID) ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS OrderDetail_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID VARCHAR(40),
    CourseID VARCHAR(40),
    Price DECIMAL(10,2),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_OrderDetail_AI
AFTER INSERT ON OrderDetail
FOR EACH ROW
BEGIN
    INSERT INTO OrderDetail_History (OrderID, CourseID, Price, created_at_original, DML_Type, DML_User)
    VALUES (NEW.OrderID, NEW.CourseID, NEW.Price, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_OrderDetail_AU
AFTER UPDATE ON OrderDetail
FOR EACH ROW
BEGIN
    INSERT INTO OrderDetail_History (OrderID, CourseID, Price, created_at_original, DML_Type, DML_User)
    VALUES (OLD.OrderID, OLD.CourseID, OLD.Price, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_OrderDetail_AD
AFTER DELETE ON OrderDetail
FOR EACH ROW
BEGIN
    INSERT INTO OrderDetail_History (OrderID, CourseID, Price, created_at_original, DML_Type, DML_User)
    VALUES (OLD.OrderID, OLD.CourseID, OLD.Price, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Review (
    ReviewID VARCHAR(40) PRIMARY KEY,
    UserID VARCHAR(40) NOT NULL,
    CourseID VARCHAR(40) NOT NULL,
    Rating INT NOT NULL CHECK (Rating BETWEEN 1 AND 5),
    ReviewText TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Review_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    ReviewID VARCHAR(40),
    UserID VARCHAR(40),
    CourseID VARCHAR(40),
    Rating INT,
    ReviewText TEXT,
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Review_AI
AFTER INSERT ON Review
FOR EACH ROW
BEGIN
    INSERT INTO Review_History (ReviewID, UserID, CourseID, Rating, ReviewText, created_at_original, DML_Type, DML_User)
    VALUES (NEW.ReviewID, NEW.UserID, NEW.CourseID, NEW.Rating, NEW.ReviewText, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Review_AU
AFTER UPDATE ON Review
FOR EACH ROW
BEGIN
    INSERT INTO Review_History (ReviewID, UserID, CourseID, Rating, ReviewText, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ReviewID, OLD.UserID, OLD.CourseID, OLD.Rating, OLD.ReviewText, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Review_AD
AFTER DELETE ON Review
FOR EACH ROW
BEGIN
    INSERT INTO Review_History (ReviewID, UserID, CourseID, Rating, ReviewText, created_at_original, DML_Type, DML_User)
    VALUES (OLD.ReviewID, OLD.UserID, OLD.CourseID, OLD.Rating, OLD.ReviewText, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS Payment (
    PaymentID VARCHAR(40) PRIMARY KEY,
    OrderID VARCHAR(40) NOT NULL,
    PaymentDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    PaymentMethod VARCHAR(50),
    PaymentStatus VARCHAR(50),
    Amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Payment_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    PaymentID VARCHAR(40),
    OrderID VARCHAR(40),
    PaymentDate DATETIME,
    PaymentMethod VARCHAR(50),
    PaymentStatus VARCHAR(50),
    Amount DECIMAL(10,2),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_Payment_AI
AFTER INSERT ON Payment
FOR EACH ROW
BEGIN
    INSERT INTO Payment_History (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at_original, DML_Type, DML_User)
    VALUES (NEW.PaymentID, NEW.OrderID, NEW.PaymentDate, NEW.PaymentMethod, NEW.PaymentStatus, NEW.Amount, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Payment_AU
AFTER UPDATE ON Payment
FOR EACH ROW
BEGIN
    INSERT INTO Payment_History (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at_original, DML_Type, DML_User)
    VALUES (OLD.PaymentID, OLD.OrderID, OLD.PaymentDate, OLD.PaymentMethod, OLD.PaymentStatus, OLD.Amount, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_Payment_AD
AFTER DELETE ON Payment
FOR EACH ROW
BEGIN
    INSERT INTO Payment_History (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at_original, DML_Type, DML_User)
    VALUES (OLD.PaymentID, OLD.OrderID, OLD.PaymentDate, OLD.PaymentMethod, OLD.PaymentStatus, OLD.Amount, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets_History (
    HistoryID INT AUTO_INCREMENT PRIMARY KEY,
    id INT,
    email VARCHAR(100),
    token VARCHAR(255),
    created_at_original DATETIME,
    DML_Type VARCHAR(10) NOT NULL,
    DML_Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DML_User VARCHAR(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER trg_password_resets_AI
AFTER INSERT ON password_resets
FOR EACH ROW
BEGIN
    INSERT INTO password_resets_History (id, email, token, created_at_original, DML_Type, DML_User)
    VALUES (NEW.id, NEW.email, NEW.token, NEW.created_at, 'INSERT', CURRENT_USER());
END;
//

CREATE TRIGGER trg_password_resets_AU
AFTER UPDATE ON password_resets
FOR EACH ROW
BEGIN
    INSERT INTO password_resets_History (id, email, token, created_at_original, DML_Type, DML_User)
    VALUES (OLD.id, OLD.email, OLD.token, OLD.created_at, 'UPDATE', CURRENT_USER());
END;
//

CREATE TRIGGER trg_password_resets_AD
AFTER DELETE ON password_resets
FOR EACH ROW
BEGIN
    INSERT INTO password_resets_History (id, email, token, created_at_original, DML_Type, DML_User)
    VALUES (OLD.id, OLD.email, OLD.token, OLD.created_at, 'DELETE', CURRENT_USER());
END;
//

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;