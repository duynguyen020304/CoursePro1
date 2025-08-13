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
    // ... (Các hằng số và thuộc tính không thay đổi)
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

    /**
     * @var bool Flag to ensure HTML header is printed only once.
     */
    private bool $htmlHeaderPrinted = false;


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
        // ... (bootServices không thay đổi)
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

    /**
     * Logs a message to either the CLI or a formatted HTML view.
     *
     * @param string $message The message to log.
     * @param string $type    The type of message (info, success, error, warning, title, pre).
     * @param bool   $isCli   Whether the current environment is Command Line Interface.
     */
    public function log(string $message, string $type = 'info', bool $isCli = false): void
    {
        // For web view, ensure the header is printed once.
        if (!$isCli && !$this->htmlHeaderPrinted) {
            echo <<<HTML
            <!DOCTYPE html>
            <html lang="vi">
            <head>
                <meta charset="UTF-8">
                <title>Database Initialization Log</title>
                <style>
                    body {
                        background-color: #1a1a1a;
                        color: #e0e0e0;
                        font-family: 'Menlo', 'Monaco', 'Consolas', "Courier New", monospace;
                        font-size: 14px;
                        line-height: 1.6;
                        padding: 20px;
                        margin: 0;
                    }
                    div {
                        padding: 2px 0;
                        border-left: 3px solid transparent;
                        padding-left: 10px;
                        word-wrap: break-word;
                    }
                    .info { border-left-color: #6c757d; color: #ced4da; }
                    .success { border-left-color: #28a745; color: #a3d9a5; }
                    .error { border-left-color: #dc3545; color: #f8d7da; }
                    .warning { border-left-color: #ffc107; color: #ffeeba; }
                    .title {
                        font-size: 1.2em;
                        font-weight: bold;
                        color: #17a2b8;
                        margin-top: 15px;
                        margin-bottom: 5px;
                        border-left: none;
                        padding-left: 0;
                    }
                    pre {
                        background-color: #343a40;
                        color: #f8d7da;
                        padding: 10px;
                        border-radius: 5px;
                        border: 1px solid #495057;
                        white-space: pre-wrap;
                        word-wrap: break-word;
                        margin-left: 13px;
                    }
                </style>
            </head>
            <body>
            HTML;
            $this->htmlHeaderPrinted = true;
        }

        $timestamp = date('H:i:s');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        if ($isCli) {
            echo "[$timestamp] " . strtoupper($type) . ": " . $message . "\n";
        } else {
            // Web output with classes
            if ($type === 'pre') {
                echo "<pre>$safeMessage</pre>";
            } else {
                echo "<div class='{$type}'>[$timestamp] {$safeMessage}</div>";
            }
        }
    }

    /**
     * Prints the closing HTML tags if the header was printed.
     */
    public function printHtmlFooter(): void
    {
        if ($this->htmlHeaderPrinted) {
            echo '</body></html>';
        }
    }

    private function executeSqlFile(string $filePath, bool $isCli = true): bool
    {
        if (!file_exists($filePath)) {
            $this->log("INIT FAILED: SQL file not found at {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: SQL file not found at {$filePath}");
            return false;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            $this->log("INIT FAILED: Could not read SQL file {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: Could not read SQL file {$filePath}");
            return false;
        }

        $this->log("Attempting to execute SQL file: " . basename($filePath) . "...", 'info', $isCli);
        $success = $this->runScript($sql);

        if ($success) {
            $this->log("Successfully executed " . basename($filePath), 'success', $isCli);
            return true;
        } else {
            $errorDetail = $this->getLastError() ?? 'Unknown error during script execution.';
            $lastQueryAttempted = substr($this->getLastQuery() ?? 'N/A', 0, 1000);
            $failMsg = "FAILED to execute " . basename($filePath) . ". Last Error: " . $errorDetail;
            $queryInfo = "Last Statement Attempted from " . basename($filePath) . ":\n" . $lastQueryAttempted;

            $this->log($failMsg, 'error', $isCli);
            $this->log($queryInfo, 'pre', $isCli); // Use 'pre' for formatted code block

            error_log($failMsg . " (Raw: " . $this->getLastError() . ")" . $queryInfo);
            return false;
        }
    }

    private function handleJsonErrors(bool $isCli): bool
    {
        $errorMessages = [
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded.',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch.',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found.',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON.',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.'
        ];

        $jsonError = json_last_error();
        if ($jsonError !== JSON_ERROR_NONE) {
            $errorMsg = 'INIT FAILED: ' . ($errorMessages[$jsonError] ?? 'Unknown JSON error occurred.');
            $this->log($errorMsg, 'error', $isCli);
            error_log($errorMsg);
            return false;
        }
        return true;
    }

    public function init_student(string $filePath, bool $isCli = true): bool
    {
        if (!file_exists($filePath)) {
            $this->log("INIT FAILED: JSON file not found at {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: JSON file not found at {$filePath}");
            return false;
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            $this->log("INIT FAILED: Could not read JSON file {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: Could not read JSON file {$filePath}");
            return false;
        }

        $this->log("Attempting to execute JSON file: " . basename($filePath) . "...", 'title', $isCli);

        $studentData = json_decode($jsonData, true);

        if (!$this->handleJsonErrors($isCli)) {
            return false;
        }

        $allSuccess = true;
        foreach ($studentData as $student) {
            $biography = $student['biography'] ?? "NOT_SET";
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
                $this->log("Created student: {$student['firstName']} {$student['lastName']} ({$student['email']})", 'info', $isCli);
            } else {
                $this->log("Failed to create student {$student['email']}: {$response->message}", 'error', $isCli);
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            $this->log("Successfully processed all students in " . basename($filePath), 'success', $isCli);
            return true;
        } else {
            $this->log("Some students failed to be created from " . basename($filePath), 'warning', $isCli);
            return false;
        }
    }

    public function init_instructor(string $filePath, bool $isCli = true): bool
    {
        if (!file_exists($filePath)) {
            $this->log("INIT FAILED: Instructor Dataset JSON file not found at {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: Instructor Dataset JSON file not found at {$filePath}");
            return false;
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            $this->log("INIT FAILED: Could not read JSON file {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: Could not read JSON file {$filePath}");
            return false;
        }

        $this->log("Attempting to execute JSON file: " . basename($filePath) . "...", 'title', $isCli);

        $instructorData = json_decode($jsonData, true);

        if (!$this->handleJsonErrors($isCli)) {
            return false;
        }

        $allSuccess = true;
        $this->log("Creating instructor accounts...", 'info', $isCli);
        foreach ($instructorData as $instructor) {
            $biography = $instructor['biography'] ?? "NOT_SET";
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
                $this->log("Created instructor: {$instructor['firstName']} {$instructor['lastName']} ({$instructor['email']})", 'info', $isCli);
            } else {
                $this->log("Failed to create instructor {$instructor['email']}: {$response->message}", 'error', $isCli);
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            $this->log("Successfully processed all instructors in " . basename($filePath), 'success', $isCli);
            return true;
        } else {
            $this->log("Some instructors failed to be created from " . basename($filePath), 'warning', $isCli);
            return false;
        }
    }

    public function initialize_course($coursesToInitialize, bool $isCli = true): array
    {
        $this->log("Starting course initialization...", 'title', $isCli);
        $createdCourses = [];
        $admin_id = $_GET['admin_id'] ?? "duynguyen";

        $allInstructorsResponse = $this->instructorService->get_all_instructors();
        $availableInstructorIDs = [];
        if ($allInstructorsResponse->success && !empty($allInstructorsResponse->data)) {
            foreach ($allInstructorsResponse->data as $instructorData) {
                $availableInstructorIDs[] = is_array($instructorData) ? $instructorData['instructorID'] : $instructorData->instructorID;
            }
            $this->log(count($availableInstructorIDs) . " instructors fetched successfully.", 'info', $isCli);
        } else {
            $this->log("Failed to fetch instructors or no instructors found. (Message: {$allInstructorsResponse->message})", 'warning', $isCli);
        }

        if (empty(self::ADMIN_USER_ID_CREATED_BY)) {
            $this->log("ADMIN_USER_ID_CREATED_BY is not configured. Aborting.", 'error', $isCli);
            return [];
        }

        foreach ($coursesToInitialize as $courseData) {
            $this->log("Initializing course: '{$courseData['title']}'...", 'info', $isCli);

            if (empty($availableInstructorIDs)) {
                $this->log("No instructors available. Skipping course '{$courseData['title']}'.", 'warning', $isCli);
                continue;
            }

            $numInstructorsToAssign = rand(1, min(3, count($availableInstructorIDs)));
            $randomInstructorKeys = (array)array_rand($availableInstructorIDs, $numInstructorsToAssign);
            $currentCourseInstructorIDs = array_map(fn($key) => $availableInstructorIDs[$key], $randomInstructorKeys);
            $this->log("Assigned " . count($currentCourseInstructorIDs) . " instructor(s): " . implode(', ', $currentCourseInstructorIDs), 'info', $isCli);

            $currentCourseCategoryIDs = [];
            foreach ($courseData['categoryIds'] as $categoryId) {
                if (isset(self::CATEGORIES_DATA[$categoryId])) {
                    $currentCourseCategoryIDs[] = (string)$categoryId;
                } else {
                    $this->log("Category ID {$categoryId} not found. Skipping.", 'warning', $isCli);
                }
            }

            if (empty($currentCourseCategoryIDs) && !empty($courseData['categoryIds'])) {
                $this->log("No valid categories for course '{$courseData['title']}'. Skipping.", 'error', $isCli);
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
                $this->log("Failed to create course '{$courseData['title']}': {$courseResponse->message}", 'error', $isCli);
                continue;
            }

            $courseId = $courseResponse->data;
            $this->log("Course '{$courseData['title']}' created successfully with ID: {$courseId}", 'success', $isCli);
            $createdCourses[] = ['id' => $courseId, 'title' => $courseData['title'], 'language' => $courseData['language']];
            $this->add_course_details($courseId, $courseData, $isCli);
        }

        $this->log("Course data initialization finished.", 'success', $isCli);
        return $createdCourses;
    }

    public function add_course_details(string $courseId, array $courseData, bool $isCli = true): void
    {
        if (!empty($courseData['requirements'])) {
            foreach ($courseData['requirements'] as $requirementText) {
                $this->courseRequirementService->create($courseId, $requirementText);
            }
            $this->log("Added " . count($courseData['requirements']) . " requirements.", 'info', $isCli);
        }

        if (!empty($courseData['objectives'])) {
            foreach ($courseData['objectives'] as $objectiveText) {
                $this->courseObjectiveService->create($courseId, $objectiveText);
            }
            $this->log("Added " . count($courseData['objectives']) . " objectives.", 'info', $isCli);
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
                    $this->log("Chapter '{$chapterData['title']}' created with ID: {$chapterId}", 'info', $isCli);

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
                        $this->log("Added " . count($chapterData['lessons']) . " lessons to chapter '{$chapterData['title']}'.", 'info', $isCli);
                    }
                } else {
                    $this->log("Failed to create chapter '{$chapterData['title']}'.", 'error', $isCli);
                }
            }
        }
    }


    public function init_courses(string $filePath, bool $isCli = true): bool
    {
        if (!file_exists($filePath)) {
            $this->log("INIT FAILED: Course Dataset JSON file not found at {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: Course Dataset JSON file not found at {$filePath}");
            return false;
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            $this->log("INIT FAILED: Could not read JSON file {$filePath}", 'error', $isCli);
            error_log("INIT FAILED: Could not read JSON file {$filePath}");
            return false;
        }

        $this->log("Attempting to execute Course Dataset JSON file: " . basename($filePath) . "...", 'title', $isCli);

        $courseData = json_decode($jsonData, true);

        if (!$this->handleJsonErrors($isCli)) {
            return false;
        }

        $this->log("Creating courses from dataset...", 'info', $isCli);
        $createdCourses = $this->initialize_course($courseData, $isCli);

        if (!empty($createdCourses)) {
            // Assuming assign_images_in_parallel is defined and works as intended
            $this->assign_images_in_parallel($createdCourses, $isCli);
            // $this->log("Skipping image assignment for now.", 'warning', $isCli);
        } else {
            $this->log("No courses were created from the dataset.", 'warning', $isCli);
            return false;
        }

        $this->log("Successfully processed course dataset file: " . basename($filePath), 'success', $isCli);
        return true;
    }


    public function create_structure_and_procedures(): void
    {
        $isCli = php_sapi_name() === 'cli';

        if (!$this->isConnected()) {
            $errorMsg = "INIT FAILED: Not connected to the database. Last Error: " . ($this->getLastError() ?? 'Unknown connection error. Check credentials and database server status.');
            $this->log($errorMsg, 'error', $isCli);
            error_log($errorMsg . " (Raw: " . $this->getLastError() . ")");
            return;
        }

        $this->log("Attempting to initialize database structure (schema.sql)...", 'title', $isCli);
        $schemaFilePath = __DIR__ . '/schema.sql';

        if (!$this->executeSqlFile($schemaFilePath, $isCli)) {
            $this->log("Halting initialization due to error in schema.sql.", 'error', $isCli);
            return;
        }
        $this->log("Database structure (schema.sql) initialized successfully.", 'success', $isCli);

        $proceduresDir = __DIR__ . '/trigger_procedure/';
        $this->log("Attempting to execute additional SQL scripts from {$proceduresDir}...", 'title', $isCli);

        if (!is_dir($proceduresDir)) {
            $this->log("Directory not found: {$proceduresDir}", 'warning', $isCli);
            error_log("INIT WARNING: Directory not found: {$proceduresDir}");
        } else {
            $sqlFiles = glob($proceduresDir . '*.sql');
            if (empty($sqlFiles)) {
                $this->log("No .sql files found in {$proceduresDir}", 'info', $isCli);
            } else {
                $allProceduresSuccessful = true;
                foreach ($sqlFiles as $sqlFile) {
                    if (!$this->executeSqlFile($sqlFile, $isCli)) {
                        $allProceduresSuccessful = false;
                    }
                }
                if ($allProceduresSuccessful) {
                    $this->log("All additional SQL scripts executed successfully.", 'success', $isCli);
                } else {
                    $this->log("Some additional SQL scripts failed to execute. Please check logs.", 'error', $isCli);
                }
            }
        }

        $this->log("INIT PROCESS COMPLETE. Schema created and additional scripts attempted.", 'success', $isCli);
    }
    public function assign_images_in_parallel(array $courses, bool $isCli = true): void
    {
        if (empty($courses)) {
            $this->log("\nNo courses to assign images to.\n", 'warning');
            return;
        }

        $maxConcurrentRequests = 40;

        $totalCourses = count($courses);
        $this->log("\nStarting parallel image assignment for " . $totalCourses . " courses (batch size: {$maxConcurrentRequests})...", 'title');

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
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600000);
                curl_setopt($ch, CURLOPT_TIMEOUT, 600000);

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
                    $this->log("\n[{$processedCount}/{$totalCourses}]", 'info');

                    if ($httpCode !== 200 || $curlError) {
                        $this->log("\nERROR fetching image for '{$courseTitle}' (ID: {$courseId}): HTTP {$httpCode} - {$curlError}", 'error');
                    } else {
                        $this->save_and_update_image($courseId, $course['title'], $imageData);
                        $this->log("\nSUCCESS for '{$courseTitle}' (ID: {$courseId})", 'success');
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
        $this->log("\nParallel image assignment finished.\n", 'success');
    }


    private function save_and_update_image(string $courseId, string $courseTitle, string $imageData, bool $isCli = false): void
    {
        $safeCourseId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $courseId);
        $projectRoot = dirname(__DIR__) . "/";
        $relativeUploadPath = 'uploads/' . $safeCourseId . '/';
        $absoluteUploadDir = $projectRoot . $relativeUploadPath;

        if (!$this->ensure_upload_directory($absoluteUploadDir)) {
            $this->log("\nERROR: Could not create or write to upload directory: {$absoluteUploadDir}", 'error');
            return;
        }

        $imageId = str_replace('.', '_', uniqid('img_', true));
        $imageFileName = $imageId . ".webp";
        $destinationPath = $absoluteUploadDir . $imageFileName;

        if (file_put_contents($destinationPath, $imageData) === false) {
            $this->log("\nERROR: Failed to save fetched image to {$destinationPath}", 'error');
            return;
        }

        $safeTitle = htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8');
        $this->log("\nImage for '{$safeTitle}' saved to: {$destinationPath}", 'success');

        $imageResponse = $this->courseImageService->add_image($imageId, $courseId, $imageFileName, null, 0);

        if ($imageResponse->success) {
            $this->log("\nSuccessfully linked image in database for course ID {$courseId}.", 'success');
        } else {
            $errorMessage = htmlspecialchars($imageResponse->message ?? 'Unknown error', ENT_QUOTES, 'UTF-8');
            $this->log("\nERROR: Failed to save image path to database for course ID {$courseId}: {$errorMessage}", 'error');
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
}

// --- Main Execution ---

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '30112004';
$db_name = getenv('DB_NAME') ?: 'ecourse';
$db_port = (int)(getenv('DB_PORT') ?: 3306);
$db_charset = getenv('DB_CHARSET') ?: 'utf8mb4';
$isCli = php_sapi_name() === 'cli';

$myinit = new InitDatabase($db_host, $db_user, $db_pass, $db_name, $db_port, $db_charset);

// Register the shutdown function to ensure the HTML footer is always printed
register_shutdown_function([$myinit, 'printHtmlFooter']);

if ($db_user === 'root' && $db_pass === '') {
    $warningMsg = "Using default MySQL credentials (root with no password). Please set environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME, etc.) for a production environment.";
    $myinit->log($warningMsg, 'warning', $isCli);
}

$student_data_path = "students-20250810_105322.json";
$instructor_data_path = "instructors-20250810_101621.json";
$course_data_path = "courses_5.json";

$myinit->create_structure_and_procedures();
$myinit->init_instructor(__DIR__ . "/" . $instructor_data_path, $isCli);
$myinit->init_student(__DIR__ . "/" . $student_data_path, $isCli);
$myinit->init_courses(__DIR__ . "/" . $course_data_path, $isCli);
