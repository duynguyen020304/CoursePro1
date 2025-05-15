<?php
// File: service/service_instructor.php

require_once __DIR__ . '/../model/bll/instructor_bll.php';
require_once __DIR__ . '/../model/dto/instructor_dto.php';
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/service_response.php';
class InstructorService
{
    private InstructorBLL $bll;
    private UserBLL $userBLL;

    public function __construct()
    {
        $this->bll = new InstructorBLL();
        $this->userBLL = new UserBLL();
    }

    /**
     * Tạo mới giảng viên
     *
     * @param string $instructorID
     * @param string $userID
     * @param string|null $biography
     * @param string|null $profileImage
     * @return ServiceResponse
     */
    public function create_instructor(string $instructorID, string $userID, ?string $biography): ServiceResponse
    {
        $dto = new InstructorDTO($instructorID, $userID, $biography);
        $ok = $this->bll->create_instructor($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo giảng viên thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo giảng viên thất bại');
    }

    /**
     * Lấy giảng viên theo ID
     *
     * @param string $instructorID
     * @return ServiceResponse
     */
    public function get_instructor(string $instructorID): ServiceResponse
    {
        echo "Đang ở service";
        $instructorDto = $this->bll->get_instructor($instructorID);

        if (!$instructorDto) {
            return new ServiceResponse(false, 'Giảng viên không tồn tại');
        }

        if (empty($instructorDto->userID)) {
            return new ServiceResponse(false, 'Dữ liệu giảng viên không hợp lệ (Thiếu UserID)');
        }

        $userDto = $this->userBLL->get_user_by_id($instructorDto->userID);

        if (!$userDto) {
            return new ServiceResponse(false, 'Không tìm thấy thông tin người dùng liên kết với giảng viên');
        }

        try {
            $combinedData = [];
            $combinedData['userID'] = $userDto->userID;
            $combinedData['firstName']   = $userDto->firstName;
            $combinedData['lastName'] = $userDto->lastName;
            $combinedData['email']  = $userDto->email;
            $combinedData['roleID'] = $userDto->roleID;
            $combinedData['profileImage'] = $userDto->profileImage;
            $combinedData['instructorID'] = $instructorDto->instructorID;
            $combinedData['biography']    = $instructorDto->biography;
            

            return new ServiceResponse(true, 'Lấy thông tin giảng viên thành công', $combinedData);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Đã xảy ra lỗi không mong muốn khi xử lý dữ liệu.');
        }
    }

    /**
     * Lấy danh sách giảng viên
     *
     * @return ServiceResponse
     */
    public function get_all_instructors(): ServiceResponse
    {
        $list_instructor = $this->bll->get_all_instructors();

        if ($list_instructor === null || $list_instructor === false) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách giảng viên.');
        }
        if (empty($list_instructor)) {
            return new ServiceResponse(true, 'Không có giảng viên nào.', []);
        }

        $list_user = $this->userBLL->get_all_users();

        if ($list_user === null || $list_user === false) {
            error_log("Error fetching all users in get_all_instructors.");
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách người dùng.');
        }

        $userMap = [];
        foreach ($list_user as $userDto) {
            if ($userDto && isset($userDto->userID)) {
                $userMap[$userDto->userID] = $userDto;
            }
        }

        $combined_list = [];

        foreach ($list_instructor as $instructorDto) {
            if (!$instructorDto || empty($instructorDto->userID)) {
                error_log("Skipping invalid instructor DTO: " . print_r($instructorDto, true));
                continue;
            }

            $userDto = $userMap[$instructorDto->userID] ?? null;

            if ($userDto) {
                $combinedData = [
                    'userID'       => $userDto->userID,
                    'firstName'    => $userDto->firstName,
                    'lastName'     => $userDto->lastName,
                    'email'        => $userDto->email,
                    'roleID'       => $userDto->roleID,
                    'instructorID' => $instructorDto->instructorID,
                    'biography'    => $instructorDto->biography,
                    'profileImage' => $userDto->profileImage,
                ];
                $combined_list[] = $combinedData;
            } else {
                error_log("User not found for instructorID: {$instructorDto->instructorID} (UserID: {$instructorDto->userID})");
            }
        }

        return new ServiceResponse(true, 'Lấy danh sách giảng viên thành công', $combined_list);
    }

    /**
     * Cập nhật giảng viên
     *
     * @param string $instructorID
     * @param string $userID
     * @param string|null $biography
     * @param string|null $profileImage
     * @return ServiceResponse
     */
    public function update_instructor(string $instructorID, string $userID, ?string $biography, ?string $profileImage): ServiceResponse
    {
        $dto = new InstructorDTO($instructorID, $userID, $biography, $profileImage);
        $ok = $this->bll->update_instructor($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật giảng viên thành công');
        }
        return new ServiceResponse(false, 'Cập nhật giảng viên thất bại');
    }

    /**
     * Xóa giảng viên
     *
     * @param string $instructorID
     * @return ServiceResponse
     */
    public function delete_instructor(string $instructorID): ServiceResponse
    {
        $ok = $this->bll->delete_instructor($instructorID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa giảng viên thành công');
        }
        return new ServiceResponse(false, 'Xóa giảng viên thất bại hoặc không tồn tại');
    }
}
