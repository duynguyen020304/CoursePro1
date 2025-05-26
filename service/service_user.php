<?php

require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/bll/instructor_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../model/dto/instructor_dto.php';
require_once __DIR__ . '/../model/dto/student_dto.php';
require_once __DIR__ . '/../model/bll/student_bll.php';
require_once __DIR__ . '/service_response.php';

class UserService
{
    private UserBLL $userBll;
    private InstructorBLL $instructorBll;
    private StudentBLL $studentBll;

    public function __construct()
    {
        $this->userBll = new UserBLL();
        $this->instructorBll = new InstructorBLL();
        $this->studentBll = new StudentBLL();
    }

    public function authenticate(string $email, string $password): ServiceResponse
    {
        $user = $this->userBll->authenticate($email, $password);
        if (!$user) {
            return new ServiceResponse(false, 'Email hoặc mật khẩu không đúng');
        }
        return new ServiceResponse(true, 'Đăng nhập thành công', $user);
    }

    public function create_user(string $email, string $password, string $firstName, string $lastName, string $roleID, ?string $biography="NOT_SET", ?string $profileImage = null): ServiceResponse
    {
        if ($roleID === "admin") {
            return new ServiceResponse(false, "Không cho phép tạo tài khoản có vai trò admin qua chức năng này.");
        }
        if ($roleID !== "instructor" && $roleID !== "student") {
        }

        $existing = $this->userBll->get_user_by_email($email);
        if ($existing) {
            return new ServiceResponse(false, "Email đã được sử dụng");
        }

        $userID = str_replace('.', '_', uniqid('user_', true));

        $dto = new UserDTO($userID, $firstName, $lastName, $email, $password, $roleID, $profileImage);
        $userCreated = $this->userBll->create_user($dto);

        if ($userCreated) {
            $roleSpecificSuccess = true;
            $roleSpecificMessage = "";

            if ($roleID === "instructor") {

                $instructorDto = new InstructorDTO(
                    str_replace('.', '_', uniqid('instructor_', true)),
                    $userID,
                    $biography
                );
                if (!$this->instructorBll->create_instructor($instructorDto)) {
                    $roleSpecificSuccess = false;
                    $roleSpecificMessage = "Tạo người dùng thành công nhưng tạo hồ sơ giảng viên thất bại.";
                }
            } elseif ($roleID === "student") {
                $studentDto = new StudentDTO(
                    str_replace('.', '_', uniqid('student_', true)),
                    $userID
                );
                if (!$this->studentBll->create_student($studentDto)) {
                    $roleSpecificSuccess = false;
                    $roleSpecificMessage = "Tạo người dùng thành công nhưng tạo hồ sơ sinh viên thất bại.";
                }
            }

            if ($roleSpecificSuccess) {
                return new ServiceResponse(true, "Tạo tài khoản thành công", $dto);
            } else {
                return new ServiceResponse(true, $roleSpecificMessage ?: "Tạo người dùng thành công, không có hồ sơ vai trò cụ thể được tạo.", $dto);
            }
        }
        return new ServiceResponse(false, "Tạo tài khoản thất bại ở bước lưu người dùng.");
    }

    public function get_user_by_user_id(string $userID): ServiceResponse
    {
        try {
            $user = $this->userBll->get_user_by_user_id($userID);
            if (!$user) {
                return new ServiceResponse(false, 'Người dùng không tồn tại');
            }
            return new ServiceResponse(true, 'Lấy thông tin người dùng thành công', $user);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    public function get_all_users(): ServiceResponse
    {
        try {
            $users = $this->userBll->get_all_users();
            return new ServiceResponse(true, 'Lấy danh sách người dùng thành công', $users);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    public function update_user_partial(array $data, ?string $performingUserRoleID=null): ServiceResponse
    {
        try {
            if (empty($data['userID'])) {
                return new ServiceResponse(false, 'Thiếu userID để cập nhật.');
            }
            $userIDToUpdate = $data['userID'];

            $existingUser = $this->userBll->get_user_by_user_id($userIDToUpdate, "update");
            if (!$existingUser) {
                return new ServiceResponse(false, 'Người dùng không tồn tại.');
            }

            $currentRoleID = $existingUser->roleID;
            $newRoleID = $currentRoleID;

            if (isset($data['roleID'])) {
                $newRoleID = trim($data['roleID']);

                if ($newRoleID === 'admin' && $performingUserRoleID !== 'admin') {
                    return new ServiceResponse(false, 'Không có quyền thay đổi vai trò thành Admin.');
                }
                if ($currentRoleID === 'admin' && $performingUserRoleID !== 'admin' && $newRoleID !== 'admin') {
                    return new ServiceResponse(false, 'Không có quyền thay đổi vai trò của Admin.');
                }

                if ($newRoleID !== $currentRoleID) {
                    if ($currentRoleID === 'instructor') {
                        $instructorProfile = $this->instructorBll->get_instructor_by_user_id($userIDToUpdate);
                        if ($instructorProfile) {
                            $this->instructorBll->delete_instructor($instructorProfile->instructorID);
                        }
                    } elseif ($currentRoleID === 'student') {
                        $studentProfile = $this->studentBll->get_student_by_user_id($userIDToUpdate);
                        if ($studentProfile) {
                            $this->studentBll->delete_student($studentProfile->studentID);
                        }
                    }

                    if ($newRoleID === 'instructor') {
                        $existingInstructor = $this->instructorBll->get_instructor_by_user_id($userIDToUpdate);
                        if (!$existingInstructor) {
                            $newInstructorID = str_replace('.', '_', uniqid('instructor_', true));
                            $instructorDto = new InstructorDTO($newInstructorID, $userIDToUpdate);
                            if (!$this->instructorBll->create_instructor($instructorDto)) {
                                error_log("Failed to create instructor profile for user {$userIDToUpdate} during role change.");
                            }
                        }
                    } elseif ($newRoleID === 'student') {
                        $existingStudent = $this->studentBll->get_student_by_user_id($userIDToUpdate);
                        if (!$existingStudent) {
                            $newStudentID = str_replace('.', '_', uniqid('student_', true));
                            $studentDto = new StudentDTO($newStudentID, $userIDToUpdate);
                            if (!$this->studentBll->create_student($studentDto)) {
                                error_log("Failed to create student profile for user {$userIDToUpdate} during role change.");
                            }
                        }
                    }
                }
            }

            $newPasswordHash = $existingUser->password;
            if (isset($data['isChangePassword']) && $data['isChangePassword'] === true) {
                if (!isset($data['currentPassword'], $data['newPassword'])) {
                    return new ServiceResponse(false, 'Thiếu mật khẩu hiện tại hoặc mật khẩu mới để thay đổi.');
                }
                if (!password_verify($data['currentPassword'], $existingUser->password)) {
                    return new ServiceResponse(false, 'Mật khẩu hiện tại không đúng.');
                }
                if (empty(trim($data['newPassword']))) {
                    return new ServiceResponse(false, 'Mật khẩu mới không được để trống.');
                }
                $newPasswordHash = password_hash($data['newPassword'], PASSWORD_DEFAULT);
            } elseif (isset($data['password']) && !empty($data['password'])) {
                $newPasswordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $updatedUserDto = new UserDTO(
                $existingUser->userID,
                $data['firstName'] ?? $existingUser->firstName,
                $data['lastName'] ?? $existingUser->lastName,
                $existingUser->email,
                $newPasswordHash,
                $newRoleID,
                $data['profileImage'] ?? $existingUser->profileImage
            );

            if ($this->userBll->update_user($updatedUserDto)) {
                return new ServiceResponse(true, 'Cập nhật người dùng thành công.');
            }
            return new ServiceResponse(false, 'Cập nhật thông tin người dùng thất bại ở bước lưu.');
        } catch (Exception $e) {
            error_log("UserService Error in update_user_partial: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return new ServiceResponse(false, 'Lỗi máy chủ khi cập nhật người dùng: ' . $e->getMessage());
        }
    }

    public function delete_user(string $userID, string $performingUserRoleID): ServiceResponse
    {
        try {
            $userToDelete = $this->userBll->get_user_by_user_id($userID);
            if (!$userToDelete) {
                return new ServiceResponse(false, 'Người dùng không tồn tại.');
            }

            if ($userToDelete->roleID === 'admin' && $performingUserRoleID !== 'admin') {
                return new ServiceResponse(false, 'Không có quyền xóa tài khoản Admin.');
            }

            if ($userToDelete->roleID === 'instructor') {
                $instructorProfile = $this->instructorBll->get_instructor_by_user_id($userID);
                if ($instructorProfile) {
                    $this->instructorBll->delete_instructor($instructorProfile->instructorID);
                }
            } elseif ($userToDelete->roleID === 'student') {
                $studentProfile = $this->studentBll->get_student_by_user_id($userID);
                if ($studentProfile) {
                    $this->studentBll->delete_student($studentProfile->studentID);
                }
            }

            if ($this->userBll->delete_user($userID)) {
                return new ServiceResponse(true, 'Xóa người dùng thành công.');
            }
            return new ServiceResponse(false, 'Xóa người dùng thất bại ở bước lưu.');
        } catch (Exception $e) {
            error_log("UserService Error in delete_user: " . $e->getMessage());
            return new ServiceResponse(false, 'Lỗi máy chủ khi xóa người dùng: ' . $e->getMessage());
        }
    }
}