<?php

use DateTime;

require_once("database.php");
require_once("dto.php");


class RoleBLL extends Database
{
    public function create_role(RoleDTO $role)
    {
        $sql = "INSERT INTO Role(RoleID, RoleName)
                VALUES('{$role->roleID}', '{$role->roleName}')";
        $this->execute($sql);
        $this->close();
    }
    public function delete_role($roleID)
    {
        $sql = "DELETE Role WHERE RoleID = '{$roleID}'";
        $this->execute($sql);
        $this->close();
    }
    public function update_role(RoleDTO $role)
    {
        $sql = "UPDATE Role Set RoleName = '{$role->roleName}' WHERE RoleID = '{$role->roleID}'";
        $this->close();
    }
}

// Data Transfer Object for User
class UserDTO
{
    public string $userID;
    public string $name;
    public string $email;
    public string $password;
    public string $roleID;

    public function __construct(string $userID, string $name, string $email, string $password, string $roleID)
    {
        $this->userID   = $userID;
        $this->name     = $name;
        $this->email    = $email;
        $this->password = $password;
        $this->roleID   = $roleID;
    }
}

// Data Transfer Object for Instructor
class InstructorDTO
{
    public string $instructorID;
    public string $userID;
    public ?string $biography;
    public ?string $profileImage;

    public function __construct(string $instructorID, string $userID, ?string $biography = null, ?string $profileImage = null)
    {
        $this->instructorID = $instructorID;
        $this->userID       = $userID;
        $this->biography    = $biography;
        $this->profileImage = $profileImage;
    }
}

// Data Transfer Object for Student
class StudentDTO
{
    public string $studentID;
    public string $userID;
    public DateTime $enrollmentDate;
    public string $completedCourses;

    public function __construct(string $studentID, string $userID, DateTime $enrollmentDate, string $completedCourses = '')
    {
        $this->studentID        = $studentID;
        $this->userID           = $userID;
        $this->enrollmentDate   = $enrollmentDate;
        $this->completedCourses = $completedCourses;
    }
}

// Data Transfer Object for Category
class CategoryDTO
{
    public string $categoryID;
    public string $name;

    public function __construct(string $categoryID, string $name)
    {
        $this->categoryID = $categoryID;
        $this->name       = $name;
    }
}

// Data Transfer Object for Course
class CourseDTO
{
    public string $courseID;
    public string $title;
    public ?string $description;
    public float $price;
    public string $createdBy;

    public function __construct(string $courseID, string $title, ?string $description, float $price, string $createdBy)
    {
        $this->courseID    = $courseID;
        $this->title       = $title;
        $this->description = $description;
        $this->price       = $price;
        $this->createdBy   = $createdBy;
    }
}

// Data Transfer Object for CourseCategory
class CourseCategoryDTO
{
    public string $courseID;
    public string $categoryID;

    public function __construct(string $courseID, string $categoryID)
    {
        $this->courseID   = $courseID;
        $this->categoryID = $categoryID;
    }
}

// Data Transfer Object for Chapter
class ChapterDTO
{
    public string $chapterID;
    public string $courseID;
    public string $title;
    public ?string $description;
    public int $sortOrder;

    public function __construct(string $chapterID, string $courseID, string $title, ?string $description, int $sortOrder)
    {
        $this->chapterID   = $chapterID;
        $this->courseID    = $courseID;
        $this->title       = $title;
        $this->description = $description;
        $this->sortOrder   = $sortOrder;
    }
}

// Data Transfer Object for Lesson
class LessonDTO
{
    public string $lessonID;
    public string $courseID;
    public string $chapterID;
    public string $title;
    public ?string $content;
    public int $sortOrder;

    public function __construct(string $lessonID, string $courseID, string $chapterID, string $title, ?string $content, int $sortOrder)
    {
        $this->lessonID  = $lessonID;
        $this->courseID  = $courseID;
        $this->chapterID = $chapterID;
        $this->title     = $title;
        $this->content   = $content;
        $this->sortOrder = $sortOrder;
    }
}

// Data Transfer Object for Video
class VideoDTO
{
    public string $videoID;
    public string $lessonID;
    public string $url;
    public ?string $title;
    public int $sortOrder;

    public function __construct(string $videoID, string $lessonID, string $url, ?string $title, int $sortOrder)
    {
        $this->videoID   = $videoID;
        $this->lessonID  = $lessonID;
        $this->url       = $url;
        $this->title     = $title;
        $this->sortOrder = $sortOrder;
    }
}

// Data Transfer Object for CourseImage
class CourseImageDTO
{
    public string $imageID;
    public string $courseID;
    public string $imagePath;
    public ?string $caption;
    public int $sortOrder;

    public function __construct(string $imageID, string $courseID, string $imagePath, ?string $caption, int $sortOrder)
    {
        $this->imageID    = $imageID;
        $this->courseID   = $courseID;
        $this->imagePath  = $imagePath;
        $this->caption    = $caption;
        $this->sortOrder  = $sortOrder;
    }
}

// Data Transfer Object for Cart
class CartDTO
{
    public string $cartID;
    public string $userID;

    public function __construct(string $cartID, string $userID)
    {
        $this->cartID = $cartID;
        $this->userID = $userID;
    }
}

// Data Transfer Object for CartItem
class CartItemDTO
{
    public string $cartItemID;
    public string $cartID;
    public string $courseID;
    public int $quantity;

    public function __construct(string $cartItemID, string $cartID, string $courseID, int $quantity)
    {
        $this->cartItemID = $cartItemID;
        $this->cartID     = $cartID;
        $this->courseID   = $courseID;
        $this->quantity   = $quantity;
    }
}

// Data Transfer Object for Orders
class OrderDTO
{
    public string $orderID;
    public string $userID;
    public DateTime $orderDate;
    public float $totalAmount;

    public function __construct(string $orderID, string $userID, DateTime $orderDate, float $totalAmount)
    {
        $this->orderID     = $orderID;
        $this->userID      = $userID;
        $this->orderDate   = $orderDate;
        $this->totalAmount = $totalAmount;
    }
}

// Data Transfer Object for OrderDetail
class OrderDetailDTO
{
    public string $orderID;
    public string $courseID;
    public float $price;

    public function __construct(string $orderID, string $courseID, float $price)
    {
        $this->orderID  = $orderID;
        $this->courseID = $courseID;
        $this->price    = $price;
    }
}

// Data Transfer Object for Review
class ReviewDTO
{
    public string $reviewID;
    public string $userID;
    public string $courseID;
    public int $rating;
    public ?string $comment;

    public function __construct(string $reviewID, string $userID, string $courseID, int $rating, ?string $comment)
    {
        $this->reviewID = $reviewID;
        $this->userID   = $userID;
        $this->courseID = $courseID;
        $this->rating   = $rating;
        $this->comment  = $comment;
    }
}

// Data Transfer Object for Payment
class PaymentDTO
{
    public string $paymentID;
    public string $orderID;
    public DateTime $paymentDate;
    public ?string $paymentMethod;
    public ?string $paymentStatus;
    public float $amount;

    public function __construct(string $paymentID, string $orderID, DateTime $paymentDate, ?string $paymentMethod, ?string $paymentStatus, float $amount)
    {
        $this->paymentID      = $paymentID;
        $this->orderID        = $orderID;
        $this->paymentDate    = $paymentDate;
        $this->paymentMethod  = $paymentMethod;
        $this->paymentStatus  = $paymentStatus;
        $this->amount         = $amount;
    }
}
