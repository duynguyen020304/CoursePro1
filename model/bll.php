<?php

require_once("database.php");
require_once("dto.php");

// Business Logic Layer for each entity

class RoleBLL extends Database
{
    public function create_role(RoleDTO $role)
    {
        $sql = "INSERT INTO `Role` (RoleID, RoleName) VALUES ('{$role->roleID}', '{$role->roleName}')";
        $this->execute($sql);
        $this->close();
    }

    public function delete_role(string $roleID)
    {
        $sql = "DELETE FROM `Role` WHERE RoleID = '{$roleID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_role(RoleDTO $role)
    {
        $sql = "UPDATE `Role` SET RoleName = '{$role->roleName}' WHERE RoleID = '{$role->roleID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_role(string $roleID): ?RoleDTO
    {
        $sql = "SELECT * FROM `Role` WHERE RoleID = '{$roleID}'";
        $result = $this->query($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new RoleDTO($row['RoleID'], $row['RoleName']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_roles(): array
    {
        $sql = "SELECT * FROM `Role`";
        $result = $this->query($sql);
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = new RoleDTO($row['RoleID'], $row['RoleName']);
        }
        $this->close();
        return $roles;
    }
}

class UserBLL extends Database
{
    public function create_user(UserDTO $user)
    {
        $sql = "INSERT INTO `Users` (UserID, Name, Email, Password, RoleID) VALUES ('{$user->userID}', '{$user->name}', '{$user->email}', '{$user->password}', '{$user->roleID}')";
        $this->execute($sql);
        $this->close();
    }

    public function delete_user(string $userID)
    {
        $sql = "DELETE FROM `Users` WHERE UserID = '{$userID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_user(UserDTO $user)
    {
        $sql = "UPDATE `Users` SET Name = '{$user->name}', Email = '{$user->email}', Password = '{$user->password}', RoleID = '{$user->roleID}' WHERE UserID = '{$user->userID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_user(string $userID): ?UserDTO
    {
        $sql = "SELECT * FROM `Users` WHERE UserID = '{$userID}'";
        $result = $this->query($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new UserDTO($row['UserID'], $row['Name'], $row['Email'], $row['Password'], $row['RoleID']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_users(): array
    {
        $sql = "SELECT * FROM `Users`";
        $result = $this->query($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new UserDTO($row['UserID'], $row['Name'], $row['Email'], $row['Password'], $row['RoleID']);
        }
        $this->close();
        return $users;
    }
}

class InstructorBLL extends Database
{
    public function create_instructor(InstructorDTO $inst)
    {
        $bio = $inst->biography ? "'{$inst->biography}'" : 'NULL';
        $img = $inst->profileImage ? "'{$inst->profileImage}'" : 'NULL';
        $sql = "INSERT INTO `Instructor` (InstructorID, UserID, Biography, ProfileImage) VALUES ('{$inst->instructorID}', '{$inst->userID}', {$bio}, {$img})";
        $this->execute($sql);
        $this->close();
    }

    public function delete_instructor(string $instID)
    {
        $sql = "DELETE FROM `Instructor` WHERE InstructorID = '{$instID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_instructor(InstructorDTO $inst)
    {
        $bio = $inst->biography ? "Biography = '{$inst->biography}'," : '';
        $img = $inst->profileImage ? "ProfileImage = '{$inst->profileImage}'," : '';
        $sql = "UPDATE `Instructor` SET {$bio} {$img} UserID = '{$inst->userID}' WHERE InstructorID = '{$inst->instructorID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_instructor(string $instID): ?InstructorDTO
    {
        $sql = "SELECT * FROM `Instructor` WHERE InstructorID = '{$instID}'";
        $result = $this->query($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new InstructorDTO($row['InstructorID'], $row['UserID'], $row['Biography'], $row['ProfileImage']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_instructors(): array
    {
        $sql = "SELECT * FROM `Instructor`";
        $result = $this->query($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new InstructorDTO($row['InstructorID'], $row['UserID'], $row['Biography'], $row['ProfileImage']);
        }
        $this->close();
        return $list;
    }
}

class StudentBLL extends Database
{
    public function create_student(StudentDTO $stu)
    {
        $courses = $stu->completedCourses ? "'{$stu->completedCourses}'" : "''";
        $date = $stu->enrollmentDate->format('Y-m-d H:i:s');
        $sql = "INSERT INTO `Student` (StudentID, UserID, EnrollmentDate, CompletedCourses) VALUES ('{$stu->studentID}', '{$stu->userID}', '{$date}', {$courses})";
        $this->execute($sql);
        $this->close();
    }

    public function delete_student(string $stuID)
    {
        $sql = "DELETE FROM `Student` WHERE StudentID = '{$stuID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_student(StudentDTO $stu)
    {
        $courses = $stu->completedCourses ? "CompletedCourses = '{$stu->completedCourses}'," : '';
        $date = $stu->enrollmentDate->format('Y-m-d H:i:s');
        $sql = "UPDATE `Student` SET UserID = '{$stu->userID}', EnrollmentDate = '{$date}', {$courses} WHERE StudentID = '{$stu->studentID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_student(string $stuID): ?StudentDTO
    {
        $sql = "SELECT * FROM `Student` WHERE StudentID = '{$stuID}'";
        $result = $this->query($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new StudentDTO($row['StudentID'], $row['UserID'], new DateTime($row['EnrollmentDate']), $row['CompletedCourses']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_students(): array
    {
        $sql = "SELECT * FROM `Student`";
        $result = $this->query($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new StudentDTO($row['StudentID'], $row['UserID'], new DateTime($row['EnrollmentDate']), $row['CompletedCourses']);
        }
        $this->close();
        return $list;
    }
}

class CategoryBLL extends Database
{
    public function create_category(CategoryDTO $cat)
    {
        $sql = "INSERT INTO `Category` (CategoryID, Name) VALUES ('{$cat->categoryID}', '{$cat->name}')";
        $this->execute($sql);
        $this->close();
    }

    public function delete_category(string $catID)
    {
        $sql = "DELETE FROM `Category` WHERE CategoryID = '{$catID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_category(CategoryDTO $cat)
    {
        $sql = "UPDATE `Category` SET Name = '{$cat->name}' WHERE CategoryID = '{$cat->categoryID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_category(string $catID): ?CategoryDTO
    {
        $sql = "SELECT * FROM `Category` WHERE CategoryID = '{$catID}'";
        $result = $this->query($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new CategoryDTO($row['CategoryID'], $row['Name']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_categories(): array
    {
        $sql = "SELECT * FROM `Category`";
        $result = $this->query($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CategoryDTO($row['CategoryID'], $row['Name']);
        }
        $this->close();
        return $list;
    }
}

class CourseBLL extends Database
{
    public function create_course(CourseDTO $c)
    {
        $desc = $c->description ? "'{$c->description}'" : 'NULL';
        $sql = "INSERT INTO `Course` (CourseID, Title, Description, Price, CreatedBy) VALUES ('{$c->courseID}', '{$c->title}', {$desc}, {$c->price}, '{$c->createdBy}')";
        $this->execute($sql);
        $this->close();
    }

    public function delete_course(string $courseID)
    {
        $sql = "DELETE FROM `Course` WHERE CourseID = '{$courseID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_course(CourseDTO $c)
    {
        $desc = $c->description ? "Description = '{$c->description}'," : '';
        $sql = "UPDATE `Course` SET Title = '{$c->title}', {$desc} Price = {$c->price}, CreatedBy = '{$c->createdBy}' WHERE CourseID = '{$c->courseID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_course(string $courseID): ?CourseDTO
    {
        $sql = "SELECT * FROM `Course` WHERE CourseID = '{$courseID}'";
        $result = $this->query($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new CourseDTO($row['CourseID'], $row['Title'], $row['Description'], (float)$row['Price'], $row['CreatedBy']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_courses(): array
    {
        $sql = "SELECT * FROM `Course`";
        $result = $this->query($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CourseDTO($row['CourseID'], $row['Title'], $row['Description'], (float)$row['Price'], $row['CreatedBy']);
        }
        $this->close();
        return $list;
    }
}

class CourseCategoryBLL extends Database
{
    public function link_course_category(CourseCategoryDTO $cc)
    {
        $sql = "INSERT INTO `CourseCategory` (CourseID, CategoryID) VALUES ('{$cc->courseID}', '{$cc->categoryID}')";
        $this->execute($sql);
        $this->close();
    }

    public function unlink_course_category(string $courseID, string $categoryID)
    {
        $sql = "DELETE FROM `CourseCategory` WHERE CourseID = '{$courseID}' AND CategoryID = '{$categoryID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_categories_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM `CourseCategory` WHERE CourseID = '{$courseID}'";
        $result = $this->query($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CourseCategoryDTO($row['CourseID'], $row['CategoryID']);
        }
        $this->close();
        return $list;
    }
}

class LessonBLL extends Database
{
    public function create_lesson(LessonDTO $l)
    {
        $content = $l->content ? "'{$l->content}'" : 'NULL';
        $sql = "INSERT INTO Lesson (LessonID, CourseID, ChapterID, Title, Content, SortOrder) VALUES ('{$l->lessonID}', '{$l->courseID}', '{$l->chapterID}', '{$l->title}', {$content}, {$l->sortOrder})";
        $this->execute($sql);
        $this->close();
    }

    public function delete_lesson(string $lid)
    {
        $sql = "DELETE FROM Lesson WHERE LessonID = '{$lid}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_lesson(LessonDTO $l)
    {
        $content = $l->content ? "Content = '{$l->content}'," : '';
        $sql = "UPDATE Lesson SET Title = '{$l->title}', {$content} SortOrder = {$l->sortOrder} WHERE LessonID = '{$l->lessonID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_lesson(string $lid): ?LessonDTO
    {
        $sql = "SELECT * FROM Lesson WHERE LessonID = '{$lid}'";
        $result = $this->query($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new LessonDTO($row['LessonID'], $row['CourseID'], $row['ChapterID'], $row['Title'], $row['Content'], (int)$row['SortOrder']);
        }
        $this->close();
        return $dto;
    }

    public function get_lessons_by_chapter(string $chapterID): array
    {
        $sql = "SELECT * FROM Lesson WHERE ChapterID = '{$chapterID}' ORDER BY SortOrder";
        $result = $this->query($sql);
        $lessons = [];
        while ($row = $result->fetch_assoc()) {
            $lessons[] = new LessonDTO($row['LessonID'], $row['CourseID'], $row['ChapterID'], $row['Title'], $row['Content'], (int)$row['SortOrder']);
        }
        $this->close();
        return $lessons;
    }
}
