<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("database.php");
require_once __DIR__ . '/../service/service_course.php';
require_once __DIR__ . '/../service/service_instructor.php';
require_once __DIR__ . '/../service/service_student.php';
require_once __DIR__ . '/../service/service_user.php';
require_once __DIR__ . '/../service/service_course_instructor.php';
require_once __DIR__ . '/../service/service_category.php';
require_once __DIR__ . '/../service/service_chapter.php';
require_once __DIR__ . '/../service/service_course_requirement.php';
require_once __DIR__ . '/../service/service_course_objective.php';
require_once __DIR__ . '/../service/service_lesson.php';
require_once __DIR__ . '/../service/service_course_image.php';

class InitDatabase extends Database
{
    private const ADMIN_USER_ID_CREATED_BY = 'user_admin_001';
    private const CATEGORIES_DATA = [
        1 => 'Phát triển',
        33 => 'Kinh doanh',
        41 => 'CNTT & Phần mềm',
        49 => 'Thiết kế',
        56 => 'Marketing',
        63 => 'Phát triển cá nhân',
        69 => 'Âm nhạc',
        73 => 'Sức khỏe & Thể hình',
        78 => 'Giảng dạy & Học thuật',
        2 => 'Lập trình Web',
        14 => 'Lập trình Mobile',
        20 => 'Lập trình Game',
        24 => 'Phát triển phần mềm',
        30 => 'Lập trình nhúng / IoT',
        31 => 'Blockchain',
        32 => 'No-Code Development',
        3 => 'HTML & CSS',
        4 => 'JavaScript',
        5 => 'ReactJS',
        6 => 'VueJS',
        7 => 'Angular',
        8 => 'PHP',
        9 => 'Laravel',
        10 => 'ASP.NET',
        11 => 'Django',
        12 => 'NodeJS',
        13 => 'Web APIs',
        15 => 'Android Development',
        16 => 'iOS Development',
        17 => 'React Native',
        18 => 'Flutter',
        19 => 'Xamarin',
        21 => 'Unity',
        22 => 'Unreal Engine',
        23 => 'Godot',
        25 => 'Python',
        26 => 'Java',
        27 => 'C++',
        28 => 'C#',
        29 => 'Rust',
        34 => 'Quản trị kinh doanh',
        35 => 'Doanh nghiệp khởi nghiệp',
        36 => 'Quản lý dự án',
        37 => 'Agile & Scrum',
        38 => 'Tài chính & Kế toán',
        39 => 'Phân tích kinh doanh (Business Analytics)',
        40 => 'Nhân sự (HR)',
        42 => 'Mạng máy tính & Bảo mật',
        43 => 'Ethical Hacking',
        44 => 'Khoa học dữ liệu (Data Science)',
        45 => 'Trí tuệ nhân tạo (AI)',
        46 => 'Hệ điều hành (Linux, Windows Server)',
        47 => 'DevOps',
        48 => 'Kiểm thử phần mềm (Software Testing)',
        50 => 'Thiết kế Web',
        51 => 'Thiết kế UI/UX',
        52 => 'Adobe Photoshop',
        53 => 'Illustrator',
        54 => 'Thiết kế đồ họa 2D/3D',
        55 => 'Thiết kế sản phẩm',
        57 => 'Digital Marketing',
        58 => 'SEO',
        59 => 'Google Ads / Facebook Ads',
        60 => 'Content Marketing',
        61 => 'Email Marketing',
        62 => 'Affiliate Marketing',
        64 => 'Kỹ năng giao tiếp',
        65 => 'Lãnh đạo',
        66 => 'Quản lý thời gian',
        67 => 'Tư duy phản biện',
        68 => 'Đọc nhanh & Ghi nhớ',
        70 => 'Nhạc cụ (Piano, Guitar, v.v.)',
        71 => 'Sản xuất âm nhạc',
        72 => 'DJ & Âm thanh điện tử',
        74 => 'Yoga',
        75 => 'Thiền',
        76 => 'Dinh dưỡng',
        77 => 'Tập luyện thể hình',
        79 => 'Toán học',
        80 => 'Vật lý',
        81 => 'Lập trình cho trẻ em',
        82 => 'Khoa học máy tính',
        83 => 'IELTS, TOEIC, TOEFL',
        84 => 'Ngoại ngữ',
        85 => 'Tiếng Anh',
        86 => 'Tiếng Nhật',
        87 => 'Tiếng Hàn',
        88 => 'Tiếng Trung',
        89 => 'Tiếng Pháp',
        90 => 'Tiếng Đức',
        91 => 'Cloud Computing (AWS, Azure, GCP)',
        92 => 'Data Engineering',
        93 => 'E-commerce',
        94 => 'Đầu tư (Chứng khoán, Bất động sản)',
        95 => 'Machine Learning',
        96 => 'Database Management (SQL, NoSQL)',
        97 => 'Cybersecurity Chuyên sâu',
        98 => 'Animation (2D/3D)',
        99 => 'Video Editing (Premiere, Final Cut)',
        100 => 'Figma',
        101 => 'Social Media Marketing',
        102 => 'Branding & Xây dựng thương hiệu',
        103 => 'Năng suất & Tổ chức công việc',
        104 => 'Trí tuệ cảm xúc (EQ)',
        105 => 'Lý thuyết âm nhạc',
        106 => 'Thanh nhạc',
        107 => 'Sơ cứu & Chăm sóc sức khỏe cơ bản',
        108 => 'Mindfulness & Giảm căng thẳng',
        109 => 'Hóa học',
        110 => 'Sinh học',
        111 => 'Lịch sử',
        112 => 'Địa lý',
        113 => 'Văn học',
    ];
    private CourseService $courseService;
    private CategoryService $categoryService;
    private ChapterService $chapterService;
    private LessonService $lessonService;
    private CourseRequirementService $courseRequirementService;
    private CourseObjectiveService $courseObjectiveService;
    private InstructorService $instructorService;
    private CourseImageService $courseImageService;
    private UserService $userService;
    

    public function __construct(
        string $host = '',
        string $user = '',
        string $pass = '',
        string $dbname = '',
        int $port = 0,
        string $charset = ''
        
    ) {
        parent::__construct($host, $user, $pass, $dbname, $port, $charset);
        $this->bootServices();
    }

    private function bootServices(): void
    {
        // Điều chỉnh nếu service cần DB handle/Container...
        $this->courseService = new CourseService();
        $this->categoryService = new CategoryService();
        $this->chapterService = new ChapterService();
        $this->lessonService = new LessonService();
        $this->courseRequirementService = new CourseRequirementService();
        $this->courseObjectiveService = new CourseObjectiveService();
        $this->instructorService = new InstructorService();
        $this->courseImageService = new CourseImageService();
        $this->userService = new UserService();
    }
    private function executeSqlFile(string $filePath, bool $isCli): bool
    {
        if (!file_exists($filePath)) {
            $errorMsg = "INIT FAILED: SQL file not found at {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            $errorMsg = "INIT FAILED: Could not read SQL file {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        echo $isCli ? "Attempting to execute SQL file: " . basename($filePath) . "...\n" : "<p>Attempting to execute SQL file: " . htmlspecialchars(basename($filePath)) . "...</p>";
        $success = $this->runScript($sql);

        if ($success) {
            $successMsg = "Successfully executed " . basename($filePath);
            echo $isCli ? $successMsg . "\n" : "<p style='color:green;'>" . htmlspecialchars($successMsg) . "</p>";
            return true;
        } else {
            $errorDetail = htmlspecialchars($this->getLastError() ?? 'Unknown error during script execution.');
            $lastQueryAttempted = htmlspecialchars(substr($this->getLastQuery() ?? 'N/A', 0, 1000));
            $failMsg = "FAILED to execute " . basename($filePath) . ". Last Error: " . $errorDetail;
            $queryInfo = "\nLast Statement Attempted from " . basename($filePath) . ":\n" . $lastQueryAttempted;

            if ($isCli) {
                echo $failMsg . $queryInfo . "\n";
            } else {
                echo "<p style='color:red;'>" . $failMsg . "</p><pre>" . $queryInfo . "</pre>";
            }
            error_log($failMsg . " (Raw: " . $this->getLastError() . ")" . $queryInfo);
            return false;
        }
    }

    public function init_student(string $filePath, bool $isCli): bool {
        if (!file_exists($filePath)) {
            $errorMsg = "INIT FAILED: JSON file not found at {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            $errorMsg = "INIT FAILED: Could not read JSON file {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        echo $isCli ? "Attempting to execute JSON file: " . basename($filePath) . "...\n" : "<p>Attempting to execute JSON file: " . htmlspecialchars(basename($filePath)) . "...</p>";

        $studentData = json_decode($jsonData, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                foreach ($studentData as $student) {
                    $biography = "NOT_SET";
                    if (isset($student['biography'])) {
                        $biography = $student['biography'];
                    }
                    $response = $this->userService->create_user(
                        $student['email'],
                        $student['password'],
                        $student['firstName'],
                        $student['lastName'],
                        $student['role'],
                        $biography,
                        $student['profileImage']
                    );

                    if ($response->success) {
                        echo "Created student: {$student['firstName']} {$student['lastName']} ({$student['email']})\n";
                    } else {
                        echo "Failed to create student {$student['email']}: {$response->message}\n";
                        $isGenerateStudentSuccess = false;
                    }
                }
                break;
            case JSON_ERROR_DEPTH:
                $errorMsg = 'INIT FAILED: Maximum stack depth exceeded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_STATE_MISMATCH:
                $errorMsg = 'INIT FAILED: Underflow or the modes mismatch.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_CTRL_CHAR:
                $errorMsg = 'INIT FAILED: Unexpected control character found.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_SYNTAX:
                $errorMsg = 'INIT FAILED: Syntax error, malformed JSON.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_UTF8:
                $errorMsg = 'INIT FAILED: Malformed UTF-8 characters, possibly incorrectly encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_RECURSION:
                $errorMsg = 'INIT FAILED: One or more recursive references in the value to be encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_INF_OR_NAN:
                $errorMsg = 'INIT FAILED: One or more NAN or INF values in the value to be encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $errorMsg = 'INIT FAILED: A value of a type that cannot be encoded was given.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            default:
                $errorMsg = 'INIT FAILED: Unknown JSON error occurred.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
        }

        $success = true;

        if ($success) {
            $successMsg = "Successfully executed " . basename($filePath);
            echo $isCli ? $successMsg . "\n" : "<p style='color:green;'>" . htmlspecialchars($successMsg) . "</p>";
            return true;
        } else {
            $errorDetail = htmlspecialchars($this->getLastError() ?? 'Unknown error during script execution.');
            $lastQueryAttempted = htmlspecialchars(substr($this->getLastQuery() ?? 'N/A', 0, 1000));
            $failMsg = "FAILED to execute " . basename($filePath) . ". Last Error: " . $errorDetail;
            $queryInfo = "\nLast Statement Attempted from " . basename($filePath) . ":\n" . $lastQueryAttempted;

            if ($isCli) {
                echo $failMsg . $queryInfo . "\n";
            } else {
                echo "<p style='color:red;'>" . $failMsg . "</p><pre>" . $queryInfo . "</pre>";
            }
            error_log($failMsg . " (Raw: " . $this->getLastError() . ")" . $queryInfo);
            return false;
        }
    }

    public function init_instructor(string $filePath, bool $isCli = true): bool {
        if (!file_exists($filePath)) {
            $errorMsg = "INIT FAILED: Instructor Dataset JSON file not found at {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            $errorMsg = "INIT FAILED: Could not read JSON file {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        echo $isCli ? "Attempting to execute JSON file: " . basename($filePath) . "...\n" : "<p>Attempting to execute JSON file: " . htmlspecialchars(basename($filePath)) . "...</p>";

        $instructorData = json_decode($jsonData, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                echo "Creating instructor accounts...\n";
                foreach ($instructorData as $instructor) {
                    $biography = "NOT_SET";
                    if (isset($instructor['biography'])) {
                        $biography = $instructor['biography'];
                    }
                    $response = $this->userService->create_user(
                        $instructor['email'],
                        $instructor['password'],
                        $instructor['firstName'],
                        $instructor['lastName'],
                        $instructor['role'],
                        $biography,
                        $instructor['profileImage']
                    );

                    if ($response->success) {
                        echo "Created instructor: {$instructor['firstName']} {$instructor['lastName']} ({$instructor['email']})\n";
                    } else {
                        echo "Failed to create instructor {$instructor['email']}: {$response->message}\n";
                        $isGenerateInstructorSuccess = false;
                    }
                }
                break;
            case JSON_ERROR_DEPTH:
                $errorMsg = 'INIT FAILED: Maximum stack depth exceeded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_STATE_MISMATCH:
                $errorMsg = 'INIT FAILED: Underflow or the modes mismatch.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_CTRL_CHAR:
                $errorMsg = 'INIT FAILED: Unexpected control character found.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_SYNTAX:
                $errorMsg = 'INIT FAILED: Syntax error, malformed JSON.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_UTF8:
                $errorMsg = 'INIT FAILED: Malformed UTF-8 characters, possibly incorrectly encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_RECURSION:
                $errorMsg = 'INIT FAILED: One or more recursive references in the value to be encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_INF_OR_NAN:
                $errorMsg = 'INIT FAILED: One or more NAN or INF values in the value to be encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $errorMsg = 'INIT FAILED: A value of a type that cannot be encoded was given.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            default:
                $errorMsg = 'INIT FAILED: Unknown JSON error occurred.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
        }

        $success = true;

        if ($success) {
            $successMsg = "Successfully executed " . basename($filePath);
            echo $isCli ? $successMsg . "\n" : "<p style='color:green;'>" . htmlspecialchars($successMsg) . "</p>";
            return true;
        } else {
            $errorDetail = htmlspecialchars($this->getLastError() ?? 'Unknown error during script execution.');
            $lastQueryAttempted = htmlspecialchars(substr($this->getLastQuery() ?? 'N/A', 0, 1000));
            $failMsg = "FAILED to execute " . basename($filePath) . ". Last Error: " . $errorDetail;
            $queryInfo = "\nLast Statement Attempted from " . basename($filePath) . ":\n" . $lastQueryAttempted;

            if ($isCli) {
                echo $failMsg . $queryInfo . "\n";
            } else {
                echo "<p style='color:red;'>" . $failMsg . "</p><pre>" . $queryInfo . "</pre>";
            }
            error_log($failMsg . " (Raw: " . $this->getLastError() . ")" . $queryInfo);
            return false;
        }
    }

    public function initialize_course($coursesToInitialize): array
    {
        echo "Starting course initialization...\n";
        $createdCourses = [];
        $admin_id = $_GET['admin_id'] ?? null;

        $allInstructorsResponse = $this->instructorService->get_all_instructors();
        $availableInstructorIDs = [];
        if ($allInstructorsResponse->success && !empty($allInstructorsResponse->data)) {
            foreach ($allInstructorsResponse->data as $instructorData) {
                if (is_array($instructorData) && isset($instructorData['instructorID'])) {
                    $availableInstructorIDs[] = $instructorData['instructorID'];
                } elseif (is_object($instructorData) && isset($instructorData->instructorID)) {
                    $availableInstructorIDs[] = $instructorData->instructorID;
                }
            }
            echo count($availableInstructorIDs) . " instructors fetched successfully.\n";
        } else {
            $errorMessage = htmlspecialchars($allInstructorsResponse->message ?? "N/A", ENT_QUOTES, 'UTF-8');
            echo "WARNING: Failed to fetch instructors or no instructors found. (Message: {$errorMessage}).\n";
        }

        if (empty(self::ADMIN_USER_ID_CREATED_BY)) {
            echo "ERROR: ADMIN_USER_ID_CREATED_BY is not configured. Aborting.\n";
            return [];
        }

        foreach ($coursesToInitialize as $courseData) {
            $courseTitle = htmlspecialchars($courseData['title'], ENT_QUOTES, 'UTF-8');
            echo "\nInitializing course: '{$courseTitle}'...\n";

            if (empty($availableInstructorIDs)) {
                echo " WARNING: No instructors available. Skipping course '{$courseTitle}'.\n";
                continue;
            }

            $numInstructorsToAssign = rand(1, min(3, count($availableInstructorIDs)));
            $randomInstructorKeys = (array)array_rand($availableInstructorIDs, $numInstructorsToAssign);
            $currentCourseInstructorIDs = array_map(fn($key) => $availableInstructorIDs[$key], $randomInstructorKeys);
            echo " Assigned " . count($currentCourseInstructorIDs) . " instructor(s): " . implode(', ', $currentCourseInstructorIDs) . "\n";

            $currentCourseCategoryIDs = [];
            foreach ($courseData['categoryIds'] as $categoryId) {
                if (isset(self::CATEGORIES_DATA[$categoryId])) {
                    $currentCourseCategoryIDs[] = (string)$categoryId;
                    $categoryName = htmlspecialchars(self::CATEGORIES_DATA[$categoryId], ENT_QUOTES, 'UTF-8');
                    echo " Assigned category: '{$categoryName}' (ID: {$categoryId})\n";
                } else {
                    echo " WARNING: Category ID {$categoryId} not found. Skipping.\n";
                }
            }

            if (empty($currentCourseCategoryIDs) && !empty($courseData['categoryIds'])) {
                echo " ERROR: No valid categories for course '{$courseTitle}'. Skipping.\n";
                continue;
            }

            $courseResponse = $this->courseService->create_course(
                $courseData['title'],
                $courseData['description'],
                $courseData['price'],
                $currentCourseInstructorIDs,
                $currentCourseCategoryIDs,
                ucfirst(strtolower($courseData['difficulty'])),
                strtolower($courseData['language']),
                $admin_id
            );

            if (!$courseResponse->success) {
                $responseMessage = htmlspecialchars($courseResponse->message, ENT_QUOTES, 'UTF-8');
                echo " Failed to create course '{$courseTitle}': {$responseMessage}\n";
                continue;
            }

            $courseId = $courseResponse->data;
            echo " Course '{$courseTitle}' created successfully with ID: {$courseId}\n";
            $createdCourses[] = ['id' => $courseId, 'title' => $courseData['title'], 'language' => $courseData['language']];
            $this->add_course_details($courseId, $courseData);
        }

        echo "\nCourse data initialization finished.\n";
        return $createdCourses;
    }

    private function save_and_update_image(string $courseId, string $courseTitle, string $imageData): void
    {
        $safeCourseId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $courseId);
        $projectRoot = dirname(__DIR__) . "/";
        $relativeUploadPath = 'uploads/' . $safeCourseId . '/';
        $absoluteUploadDir = $projectRoot . $relativeUploadPath;

        if (!$this->ensure_upload_directory($absoluteUploadDir)) {
            echo " ERROR: Could not create or write to upload directory: {$absoluteUploadDir}\n";
            return;
        }

        $imageId = str_replace('.', '_', uniqid('img_', true));
        $imageFileName = $imageId . ".webp";
        $destinationPath = $absoluteUploadDir . $imageFileName;

        if (file_put_contents($destinationPath, $imageData) === false) {
            echo " ERROR: Failed to save fetched image to {$destinationPath}\n";
            return;
        }

        $safeTitle = htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8');
        echo " Image for '{$safeTitle}' saved to: {$destinationPath}\n";

        $imageResponse = $this->courseImageService->add_image($imageId, $courseId, $imageFileName, null, 0);

        if ($imageResponse->success) {
            echo " Successfully linked image in database for course ID {$courseId}.\n";
        } else {
            $errorMessage = htmlspecialchars($imageResponse->message ?? 'Unknown error', ENT_QUOTES, 'UTF-8');
            echo " ERROR: Failed to save image path to database for course ID {$courseId}: {$errorMessage}\n";
        }
    }

    private function add_course_details(string $courseId, array $courseData): void
    {
        if (!empty($courseData['requirements'])) {
            foreach ($courseData['requirements'] as $requirementText) {
                $this->courseRequirementService->create($courseId, $requirementText);
            }
            echo " Added " . count($courseData['requirements']) . " requirements.\n";
        }

        if (!empty($courseData['objectives'])) {
            foreach ($courseData['objectives'] as $objectiveText) {
                $this->courseObjectiveService->create($courseId, $objectiveText);
            }
            echo " Added " . count($courseData['objectives']) . " objectives.\n";
        }

        if (!empty($courseData['chapters'])) {
            foreach ($courseData['chapters'] as $chapterData) {
                $chapterResponse = $this->chapterService->create_chapter(
                    $courseId,
                    $chapterData['title'],
                    $chapterData['description'],
                    $chapterData['sortOrder']
                );

                if ($chapterResponse->success && isset($chapterResponse->data->chapterID)) {
                    $chapterId = $chapterResponse->data->chapterID;
                    echo "  Chapter '{$chapterData['title']}' created with ID: {$chapterId}\n";

                    if (!empty($chapterData['lessons'])) {
                        foreach ($chapterData['lessons'] as $lessonData) {
                            $this->lessonService->create_lesson(
                                "null",
                                $courseId,
                                $chapterId,
                                $lessonData['title'],
                                $lessonData['content'],
                                $lessonData['sortOrder']
                            );
                        }
                        echo "   Added " . count($chapterData['lessons']) . " lessons.\n";
                    }
                } else {
                    echo "  Failed to create chapter '{$chapterData['title']}'.\n";
                }
            }
        }
    }

    private function ensure_upload_directory(string $absoluteDirectoryPath): bool
    {
        if (!is_dir($absoluteDirectoryPath)) {
            if (!mkdir($absoluteDirectoryPath, 0755, true)) {
                error_log("UPLOAD_ERROR: Cannot create directory: " . $absoluteDirectoryPath);
                return false;
            }
        }
        if (!is_writable($absoluteDirectoryPath)) {
            error_log("UPLOAD_ERROR: Directory is not writable: " . $absoluteDirectoryPath);
            return false;
        }
        return true;
    }

    public function assign_images_in_parallel(array $courses): void
    {
        if (empty($courses)) {
            echo "\nNo courses to assign images to.\n";
            return;
        }

        $maxConcurrentRequests = 40;

        $totalCourses = count($courses);
        echo "\nStarting parallel image assignment for " . $totalCourses . " courses (batch size: {$maxConcurrentRequests})...\n";

        $mh = curl_multi_init();
        $courseMap = [];
        $courseQueue = $courses; 

        $activeRequests = 0;
        $processedCount = 0;

        while ($activeRequests > 0 || !empty($courseQueue)) {
            while ($activeRequests < $maxConcurrentRequests && !empty($courseQueue)) {
                $course = array_shift($courseQueue); 
                
                $courseId = $course['id'];
                $courseTitle = $course['title'];
                $courseLanguage = $course['language'];
                $apiUrl = "http://localhost:5000/get-course-image?title=" . urlencode($courseTitle) . "&language=" . urlencode($courseLanguage);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000000000); 
                curl_setopt($ch, CURLOPT_TIMEOUT, 6000000000);      

                curl_multi_add_handle($mh, $ch);
                
                $courseMap[(int)$ch] = ['id' => $courseId, 'title' => $courseTitle];
                $activeRequests++;
            }

            $status = curl_multi_exec($mh, $running);
            
            if ($running < $activeRequests) {
                while ($done = curl_multi_info_read($mh)) {
                    $ch = $done['handle'];
                    $ch_id = (int)$ch;

                    $imageData = curl_multi_getcontent($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    $course = $courseMap[$ch_id];
                    $courseId = $course['id'];
                    $courseTitle = htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8');

                    $processedCount++;
                    echo " [{$processedCount}/{$totalCourses}]"; // In tiến trình

                    if ($httpCode !== 200 || $curlError) {
                        echo " ERROR fetching image for '{$courseTitle}' (ID: {$courseId}): HTTP {$httpCode} - {$curlError}\n";
                    } else {
                        $this->save_and_update_image($courseId, $course['title'], $imageData);
                        echo " SUCCESS for '{$courseTitle}' (ID: {$courseId})\n";
                    }

                    // Xóa handle đã hoàn thành
                    curl_multi_remove_handle($mh, $ch);
                    curl_close($ch);
                    unset($courseMap[$ch_id]);
                    $activeRequests--;
                }
            }
            
            if ($running > 0) {
                curl_multi_select($mh, 0.1); 
            }
        }

        curl_multi_close($mh);
        echo "\nParallel image assignment finished.\n";
    }

    public function init_courses(string $filePath, bool $isCli = true): bool {
        if (!file_exists($filePath)) {
            $errorMsg = "INIT FAILED: Course Dataset JSON file not found at {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            $errorMsg = "INIT FAILED: Could not read JSON file {$filePath}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
            return false;
        }

        echo $isCli ? "Attempting to execute Course Dataset JSON file: " . basename($filePath) . "...\n" : "<p>Attempting to execute Course Dataset JSON file: " . htmlspecialchars(basename($filePath)) . "...</p>";

        $courseData = json_decode($jsonData, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                echo "Creating instructor accounts...\n";
                initialize_course($courseData);
                break;
            case JSON_ERROR_DEPTH:
                $errorMsg = 'INIT FAILED: Maximum stack depth exceeded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_STATE_MISMATCH:
                $errorMsg = 'INIT FAILED: Underflow or the modes mismatch.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_CTRL_CHAR:
                $errorMsg = 'INIT FAILED: Unexpected control character found.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_SYNTAX:
                $errorMsg = 'INIT FAILED: Syntax error, malformed JSON.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_UTF8:
                $errorMsg = 'INIT FAILED: Malformed UTF-8 characters, possibly incorrectly encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_RECURSION:
                $errorMsg = 'INIT FAILED: One or more recursive references in the value to be encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_INF_OR_NAN:
                $errorMsg = 'INIT FAILED: One or more NAN or INF values in the value to be encoded.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $errorMsg = 'INIT FAILED: A value of a type that cannot be encoded was given.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
            default:
                $errorMsg = 'INIT FAILED: Unknown JSON error occurred.';
                echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
                error_log($errorMsg);
                return false;
        }

        $success = true;

        if ($success) {
            $successMsg = "Successfully executed " . basename($filePath);
            echo $isCli ? $successMsg . "\n" : "<p style='color:green;'>" . htmlspecialchars($successMsg) . "</p>";
            return true;
        } else {
            $errorDetail = htmlspecialchars($this->getLastError() ?? 'Unknown error during script execution.');
            $lastQueryAttempted = htmlspecialchars(substr($this->getLastQuery() ?? 'N/A', 0, 1000));
            $failMsg = "FAILED to execute " . basename($filePath) . ". Last Error: " . $errorDetail;
            $queryInfo = "\nLast Statement Attempted from " . basename($filePath) . ":\n" . $lastQueryAttempted;

            if ($isCli) {
                echo $failMsg . $queryInfo . "\n";
            } else {
                echo "<p style='color:red;'>" . $failMsg . "</p><pre>" . $queryInfo . "</pre>";
            }
            error_log($failMsg . " (Raw: " . $this->getLastError() . ")" . $queryInfo);
            return false;
        }
    }


    public function create_structure_and_procedures(): void
    {
        $isCli = php_sapi_name() === 'cli';

        // Apply a dark-theme wrapper for web output (non-CLI)
        if (!$isCli) {
            // Minimal dark-theme CSS injected into the page (inside body)
            echo "<style>
                .dark-theme { background-color: #121212; color: #e0e0e0; padding: 1rem; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
                .dark-theme p { color: inherit; margin: 0.25rem 0; }
                .dark-theme .warn { color: #f6c04a; }
                .dark-theme .err { color: #e57373; }
                .dark-theme .ok { color: #8bd57a; }
            </style>";
            echo "<div class='dark-theme'>";
        }

        if (!$this->isConnected()) {
            $errorMsg = "INIT FAILED: Not connected to the database. Last Error: " . htmlspecialchars($this->getLastError() ?? 'Unknown connection error. Check credentials and database server status.');
            echo $isCli ? $errorMsg . "\n" : "<p style='color:red;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg . " (Raw: " . $this->getLastError() . ")");
            if (!$isCli) echo "</div>";
            return;
        }

        echo $isCli ? "Attempting to initialize database structure (schema.sql)...\n" : "<p>Attempting to initialize database structure (schema.sql)...</p>";
        $schemaFilePath = __DIR__ . '/schema.sql';

        if (!$this->executeSqlFile($schemaFilePath, $isCli)) {
            echo $isCli ? "Halting initialization due to error in schema.sql.\n" : "<p style='color:red;'>Halting initialization due to error in schema.sql.</p>";
            if (!$isCli) echo "</div>";
            return;
        }
        echo $isCli ? "Database structure (schema.sql) initialized successfully.\n" : "<p style='color:green;'>Database structure (schema.sql) initialized successfully.</p>";

        $proceduresDir = __DIR__ . '/trigger_procedure/';
        echo $isCli ? "\nAttempting to execute additional SQL scripts from {$proceduresDir}...\n" : "<hr/><p>Attempting to execute additional SQL scripts from " . htmlspecialchars($proceduresDir) . "...</p>";

        if (!is_dir($proceduresDir)) {
            $errorMsg = "INIT WARNING: Directory not found: {$proceduresDir}";
            echo $isCli ? $errorMsg . "\n" : "<p style='color:orange;'>" . htmlspecialchars($errorMsg) . "</p>";
            error_log($errorMsg);
        } else {
            $sqlFiles = glob($proceduresDir . '*.sql');
            if (empty($sqlFiles)) {
                $infoMsg = "No .sql files found in {$proceduresDir}";
                echo $isCli ? $infoMsg . "\n" : "<p>" . htmlspecialchars($infoMsg) . "</p>";
            } else {
                $allProceduresSuccessful = true;
                foreach ($sqlFiles as $sqlFile) {
                    if (!$this->executeSqlFile($sqlFile, $isCli)) {
                        $allProceduresSuccessful = false;
                    }
                }
                if ($allProceduresSuccessful) {
                    echo $isCli ? "All additional SQL scripts executed successfully.\n" : "<p style='color:green;'>All additional SQL scripts executed successfully.</p>";
                } else {
                    echo $isCli ? "Some additional SQL scripts failed to execute. Please check logs.\n" : "<p style='color:red;'>Some additional SQL scripts failed to execute. Please check logs.</p>";
                }
            }
        }

        $finalSuccessMsg = "INIT PROCESS COMPLETE. Schema created and additional scripts attempted.";
        echo $isCli ? $finalSuccessMsg . "\n" : "<p style='color:blue;'>" . $finalSuccessMsg . "</p>";

        if (!$isCli) {
            echo "<p>Initialization complete. Redirect to user_initializer.php is disabled for review.</p>";
            echo "</div>"; // close the dark-theme wrapper
        }
    }
}

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '30112004';
$db_name = getenv('DB_NAME') ?: 'ecourse';
$db_port = (int)(getenv('DB_PORT') ?: 3306);
$db_charset = getenv('DB_CHARSET') ?: 'utf8mb4';

if ($db_user === 'root' && $db_pass === '') {
    $warningMsg = "WARNING: Using default MySQL credentials (root with no password). Please set environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME, etc.) for a production environment.";
    if (php_sapi_name() === 'cli') {
        echo $warningMsg . "\n";
    } else {
        echo "<p style='color:orange;'>" . htmlspecialchars($warningMsg) . "</p>";
    }
}

$myinit = new InitDatabase($db_host, $db_user, $db_pass, $db_name, $db_port, $db_charset);
$student_data_path = "students-20250810_105322.json";
$instructor_data_path = "instructors-20250810_101621.json";
$myinit->create_structure_and_procedures();
$myinit->init_instructor(__DIR__ . "/" . $instructor_data_path);
$myinit->init_student(__DIR__ . "/" . $student_data_path);
