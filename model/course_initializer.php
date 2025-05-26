<?php
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
require_once __DIR__ . '/../model/database.php';

class CourseInitializer
{
    private CourseService $courseService;
    private CategoryService $categoryService;
    private ChapterService $chapterService;
    private LessonService $lessonService;
    private CourseRequirementService $courseRequirementService;
    private CourseObjectiveService $courseObjectiveService;
    private InstructorService $instructorService;
    private Database $db;

    private const ADMIN_USER_ID_CREATED_BY = 'user_admin_001';

    // Ánh xạ trực tiếp các category vào code
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

    private array $coursesToInitialize = [
        [
            'title' => 'Khóa học Lập trình Python Cơ bản đến Nâng cao',
            'description' => 'Khóa học toàn diện này sẽ đưa bạn từ những khái niệm cơ bản nhất của Python đến các chủ đề nâng cao như lập trình hướng đối tượng, xử lý file, và làm việc với thư viện phổ biến.',
            'price' => 799000.00,
            'categoryIds' => [25, 24], // Python, Phát triển phần mềm
            'requirements' => [
                'Không yêu cầu kinh nghiệm lập trình trước.',
                'Máy tính cá nhân có kết nối internet.'
            ],
            'objectives' => [
                'Nắm vững cú pháp cơ bản của Python.',
                'Hiểu và áp dụng lập trình hướng đối tượng trong Python.',
                'Thao tác với file và xử lý ngoại lệ.',
                'Làm việc hiệu quả với các thư viện NumPy, Pandas.',
                'Viết các chương trình Python có cấu trúc tốt và dễ bảo trì.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Python và Cài đặt Môi trường',
                    'description' => 'Làm quen với Python và thiết lập môi trường phát triển.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Python là gì? Lịch sử và ứng dụng', 'content' => 'Tổng quan về Python và các lĩnh vực ứng dụng của nó.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt Python và Pip', 'content' => 'Hướng dẫn cài đặt Python trên các hệ điều hành và quản lý gói Pip.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Sử dụng IDE (VS Code) và Jupyter Notebook', 'content' => 'Thiết lập và làm quen với các công cụ phát triển phổ biến.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Viết chương trình Python đầu tiên', 'content' => 'Chương trình "Hello World" và cách chạy mã Python.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Cú pháp cơ bản và Kiểu dữ liệu',
                    'description' => 'Tìm hiểu các thành phần cơ bản của ngôn ngữ Python.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Biến, toán tử và biểu thức', 'content' => 'Khai báo biến, các loại toán tử số học, so sánh, logic.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Kiểu dữ liệu số (int, float, complex)', 'content' => 'Làm việc với các loại số và các phép toán liên quan.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Kiểu dữ liệu chuỗi (string) và các phương thức', 'content' => 'Tạo chuỗi, nối chuỗi, định dạng chuỗi và các phương thức xử lý chuỗi.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Kiểu dữ liệu Boolean và None', 'content' => 'Sử dụng giá trị True/False và khái niệm None.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Cấu trúc điều khiển và Vòng lặp',
                    'description' => 'Điều khiển luồng chương trình bằng các cấu trúc điều kiện và vòng lặp.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Câu lệnh if, elif, else', 'content' => 'Thực hiện các khối mã dựa trên điều kiện.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Vòng lặp for và range()', 'content' => 'Lặp qua các dãy và sử dụng hàm range().', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Vòng lặp while', 'content' => 'Lặp lại một khối mã cho đến khi điều kiện sai.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: break, continue và pass', 'content' => 'Kiểm soát luồng vòng lặp với các câu lệnh này.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Cấu trúc dữ liệu trong Python',
                    'description' => 'Tìm hiểu và sử dụng các cấu trúc dữ liệu cơ bản của Python.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: List: Tạo, truy cập và sửa đổi', 'content' => 'Làm việc với danh sách các phần tử.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Tuple: Tạo và các đặc điểm', 'content' => 'Sử dụng tuple cho các tập hợp bất biến.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Set: Tập hợp các phần tử duy nhất', 'content' => 'Thao tác với tập hợp và các phép toán tập hợp.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Dictionary: Lưu trữ dữ liệu theo cặp key-value', 'content' => 'Tạo và truy cập dữ liệu trong dictionary.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: List Comprehensions và Dictionary Comprehensions', 'content' => 'Cách tạo list và dictionary một cách ngắn gọn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Hàm và Module',
                    'description' => 'Tổ chức mã nguồn bằng hàm và module.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Định nghĩa và gọi hàm', 'content' => 'Tạo các hàm tùy chỉnh và truyền đối số.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Đối số mặc định, đối số từ khóa và *args, **kwargs', 'content' => 'Các cách truyền đối số linh hoạt.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Phạm vi biến (Scope) và Closure', 'content' => 'Hiểu về phạm vi của biến trong hàm và closure.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Module và Package: Tổ chức mã nguồn', 'content' => 'Cách tạo và sử dụng module, package.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Sử dụng các module built-in phổ biến', 'content' => 'Làm quen với `math`, `random`, `datetime`.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Lập trình Hướng Đối Tượng (OOP) trong Python',
                    'description' => 'Áp dụng các nguyên lý OOP để viết mã rõ ràng và tái sử dụng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Class và Object', 'content' => 'Định nghĩa class, tạo object và thuộc tính/phương thức.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Thuộc tính và phương thức', 'content' => 'Cách truy cập và sửa đổi thuộc tính, gọi phương thức.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Kế thừa (Inheritance)', 'content' => 'Tạo các class con kế thừa từ class cha.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Đa hình (Polymorphism) và Đóng gói (Encapsulation)', 'content' => 'Các nguyên lý OOP nâng cao.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Decorator và Generator', 'content' => 'Các tính năng nâng cao của Python.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 7: Xử lý File và Ngoại lệ',
                    'description' => 'Đọc, ghi file và xử lý lỗi trong chương trình.',
                    'sortOrder' => 7,
                    'lessons' => [
                        ['title' => 'Bài 7.1: Đọc và ghi file văn bản', 'content' => 'Sử dụng `open()`, `read()`, `write()`, `close()`.', 'sortOrder' => 1],
                        ['title' => 'Bài 7.2: Xử lý file CSV và JSON', 'content' => 'Đọc và ghi dữ liệu có cấu trúc.', 'sortOrder' => 2],
                        ['title' => 'Bài 7.3: Xử lý ngoại lệ với try-except-finally', 'content' => 'Bắt và xử lý các lỗi trong chương trình.', 'sortOrder' => 3],
                        ['title' => 'Bài 7.4: Tạo ngoại lệ tùy chỉnh', 'content' => 'Định nghĩa và raise các lỗi của riêng bạn.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Phát triển Ứng dụng Di động với Flutter',
            'description' => 'Học cách xây dựng ứng dụng di động đa nền tảng đẹp mắt và hiệu suất cao cho iOS và Android chỉ với một codebase duy nhất sử dụng Flutter và Dart.',
            'price' => 950000.00,
            'categoryIds' => [18, 14], // Flutter, Lập trình Mobile
            'requirements' => [
                'Kiến thức cơ bản về lập trình (ưu tiên OOP).',
                'Máy tính có kết nối internet và đủ cấu hình để chạy Android Studio/VS Code và Flutter SDK.'
            ],
            'objectives' => [
                'Nắm vững ngôn ngữ Dart và các khái niệm cốt lõi của Flutter.',
                'Xây dựng giao diện người dùng đẹp mắt và responsive với các Widget của Flutter.',
                'Quản lý trạng thái ứng dụng hiệu quả (Provider, BLoC/Cubit).',
                'Tương tác với API backend và lưu trữ dữ liệu cục bộ.',
                'Triển khai ứng dụng lên cả iOS và Android.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Flutter và Dart',
                    'description' => 'Tổng quan về Flutter, Dart và thiết lập môi trường phát triển.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Flutter là gì? Tại sao nên học Flutter?', 'content' => 'Giới thiệu về Flutter, ưu điểm và các ứng dụng thực tế.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt Flutter SDK và Android Studio/VS Code', 'content' => 'Hướng dẫn chi tiết cài đặt môi trường phát triển trên Windows/macOS.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Ngôn ngữ Dart cơ bản', 'content' => 'Cú pháp Dart, biến, kiểu dữ liệu, hàm, điều kiện, vòng lặp.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Lập trình hướng đối tượng trong Dart', 'content' => 'Class, Object, Kế thừa, Đa hình, Trừu tượng trong Dart.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các Widget cơ bản của Flutter',
                    'description' => 'Xây dựng giao diện người dùng bằng các Widget có sẵn.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Stateless và Stateful Widgets', 'content' => 'Phân biệt và sử dụng hai loại Widget chính.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Layout Widgets: Row, Column, Container', 'content' => 'Sắp xếp các Widget trên màn hình.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Text, Image, Icon Widgets', 'content' => 'Hiển thị văn bản, hình ảnh và biểu tượng.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Button Widgets và Xử lý sự kiện', 'content' => 'Tạo các loại nút và xử lý sự kiện người dùng.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Scaffold, AppBar, FloatingActionButton', 'content' => 'Cấu trúc cơ bản của một màn hình ứng dụng.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Quản lý trạng thái trong Flutter',
                    'description' => 'Các phương pháp quản lý dữ liệu và trạng thái ứng dụng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: setState() và State Management cơ bản', 'content' => 'Cập nhật trạng thái cục bộ của Widget.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Giới thiệu Provider Package', 'content' => 'Quản lý trạng thái đơn giản và hiệu quả với Provider.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: BLoC/Cubit Pattern', 'content' => 'Quản lý trạng thái phức tạp và tách biệt logic.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: GetX và Riverpod (tùy chọn)', 'content' => 'Giới thiệu các giải pháp quản lý trạng thái khác.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Điều hướng và Routing',
                    'description' => 'Chuyển đổi giữa các màn hình và truyền dữ liệu.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Navigator 1.0 và push/pop', 'content' => 'Điều hướng cơ bản giữa các màn hình.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Truyền dữ liệu giữa các màn hình', 'content' => 'Sử dụng arguments và callbacks.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Named Routes', 'content' => 'Điều hướng bằng tên route.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: GoRouter (Navigator 2.0)', 'content' => 'Điều hướng phức tạp và deep linking.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Tương tác với API và Dữ liệu',
                    'description' => 'Kết nối ứng dụng Flutter với các dịch vụ backend.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Thực hiện HTTP Requests với package http/Dio', 'content' => 'Gửi yêu cầu GET, POST, PUT, DELETE.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Xử lý JSON Data', 'content' => 'Parse JSON và chuyển đổi sang Dart objects.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Lưu trữ dữ liệu cục bộ với Shared Preferences/SQLite', 'content' => 'Lưu trữ dữ liệu nhỏ và lớn trên thiết bị.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Firebase Integration (Authentication, Firestore)', 'content' => 'Tích hợp Firebase cho xác thực và cơ sở dữ liệu thời gian thực.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Tối ưu hiệu suất và Triển khai ứng dụng',
                    'description' => 'Cải thiện hiệu suất và đưa ứng dụng lên App Store/Google Play.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Tối ưu hóa hiệu suất ứng dụng Flutter', 'content' => 'Giảm thiểu rebuild, sử dụng const, lazy loading.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Xử lý lỗi và Debugging', 'content' => 'Sử dụng DevTools và các kỹ thuật debug.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chuẩn bị ứng dụng để Release', 'content' => 'Cấu hình icon, splash screen, signing.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Triển khai lên Google Play Store và Apple App Store', 'content' => 'Các bước để publish ứng dụng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 7: Các chủ đề nâng cao và Best Practices',
                    'description' => 'Khám phá các kỹ thuật nâng cao và các thực hành tốt nhất trong Flutter.',
                    'sortOrder' => 7,
                    'lessons' => [
                        ['title' => 'Bài 7.1: Animation và Custom Painter', 'content' => 'Tạo hiệu ứng động và vẽ tùy chỉnh.', 'sortOrder' => 1],
                        ['title' => 'Bài 7.2: Internationalization và Localization', 'content' => 'Hỗ trợ đa ngôn ngữ cho ứng dụng.', 'sortOrder' => 2],
                        ['title' => 'Bài 7.3: Testing trong Flutter (Unit, Widget, Integration)', 'content' => 'Viết các loại test cho ứng dụng Flutter.', 'sortOrder' => 3],
                        ['title' => 'Bài 7.4: Clean Architecture và Tách biệt Concerns', 'content' => 'Thiết kế ứng dụng Flutter theo kiến trúc sạch.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Quản lý Dự án Agile và Scrum',
            'description' => 'Tìm hiểu sâu về phương pháp Agile và khung làm việc Scrum để quản lý dự án hiệu quả, tăng cường sự linh hoạt và khả năng thích ứng của đội nhóm.',
            'price' => 650000.00,
            'categoryIds' => [37, 36], // Agile & Scrum, Quản lý dự án
            'requirements' => [
                'Mong muốn học hỏi về quản lý dự án linh hoạt.',
                'Không yêu cầu kinh nghiệm trước.'
            ],
            'objectives' => [
                'Hiểu rõ các giá trị và nguyên tắc của Agile Manifesto.',
                'Nắm vững các vai trò, sự kiện và tạo phẩm trong Scrum.',
                'Thực hành các kỹ thuật lập kế hoạch Sprint, Daily Scrum, Sprint Review và Retrospective.',
                'Áp dụng Scrum vào các dự án thực tế để cải thiện hiệu suất và chất lượng.',
                'Sử dụng các công cụ hỗ trợ quản lý dự án Agile/Scrum.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Agile và Tư duy Linh hoạt',
                    'description' => 'Khám phá nền tảng của phương pháp Agile và tại sao nó lại quan trọng.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Giới thiệu về Agile và lịch sử phát triển', 'content' => 'Agile là gì? Sự ra đời và các phương pháp khác nhau.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Agile Manifesto: 4 Giá trị và 12 Nguyên tắc', 'content' => 'Phân tích sâu các giá trị và nguyên tắc cốt lõi của Agile.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: So sánh Agile với Waterfall (Thác nước)', 'content' => 'Điểm khác biệt chính và ưu nhược điểm của từng phương pháp.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tư duy và Văn hóa Agile trong tổ chức', 'content' => 'Cách xây dựng môi trường làm việc linh hoạt và thích ứng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Khung làm việc Scrum: Tổng quan',
                    'description' => 'Nắm bắt các thành phần cốt lõi của Scrum.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Scrum là gì? Tại sao Scrum lại phổ biến?', 'content' => 'Giới thiệu Scrum như một khung làm việc Agile.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Các Vai trò trong Scrum (Product Owner, Scrum Master, Development Team)', 'content' => 'Trách nhiệm và quyền hạn của từng vai trò.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Các Sự kiện trong Scrum (Sprint, Daily Scrum, Review, Retrospective)', 'content' => 'Mục đích và cách thực hiện các sự kiện Scrum.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Các Tạo phẩm trong Scrum (Product Backlog, Sprint Backlog, Increment)', 'content' => 'Hiểu về các tài liệu và kết quả của Scrum.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Product Backlog và Lập kế hoạch Sprint',
                    'description' => 'Cách tạo và quản lý Product Backlog, cùng với việc lập kế hoạch cho Sprint.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Product Backlog: Tạo và Ưu tiên', 'content' => 'Cách viết User Story, Epic, Theme và sắp xếp ưu tiên.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Ước lượng công việc (Estimation) với Story Points', 'content' => 'Các kỹ thuật ước lượng như Planning Poker.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Sprint Planning: Mục tiêu và Nội dung Sprint', 'content' => 'Cách tổ chức buổi lập kế hoạch Sprint hiệu quả.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Sprint Backlog và Definition of Done (DoD)', 'content' => 'Xây dựng Sprint Backlog và định nghĩa "Hoàn thành".', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Thực thi Sprint và Daily Scrum',
                    'description' => 'Quản lý công việc hàng ngày trong một Sprint.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Daily Scrum: Mục đích và Cách thực hiện', 'content' => 'Tổ chức buổi họp Daily Scrum 15 phút hiệu quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Theo dõi tiến độ Sprint với Burndown Chart', 'content' => 'Sử dụng Burndown Chart để theo dõi tiến độ.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Xử lý trở ngại (Impediments) và Đảm bảo dòng chảy', 'content' => 'Cách Scrum Master hỗ trợ đội nhóm vượt qua khó khăn.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Tầm quan trọng của sự tự tổ chức và liên chức năng', 'content' => 'Vai trò của đội nhóm tự quản lý trong Scrum.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Sprint Review và Sprint Retrospective',
                    'description' => 'Đánh giá kết quả và cải tiến liên tục.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Sprint Review: Trình bày và Nhận phản hồi', 'content' => 'Cách tổ chức buổi Sprint Review với các bên liên quan.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Sprint Retrospective: Cải tiến liên tục', 'content' => 'Tìm kiếm các cách để cải thiện quy trình và hiệu suất.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Kỹ thuật Facilitation cho Retrospective', 'content' => 'Các kỹ thuật giúp buổi Retrospective hiệu quả.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Áp dụng các hành động cải tiến', 'content' => 'Biến các ý tưởng cải tiến thành hành động cụ thể.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Mở rộng Scrum và Công cụ',
                    'description' => 'Các khái niệm nâng cao và công cụ hỗ trợ Scrum.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Scaled Agile Frameworks (SAFe, LeSS, Scrum@Scale)', 'content' => 'Cách áp dụng Agile/Scrum cho các tổ chức lớn.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Kanban và sự kết hợp với Scrum', 'content' => 'Giới thiệu Kanban và cách sử dụng cùng Scrum.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Các công cụ quản lý dự án Agile (Jira, Trello, Asana)', 'content' => 'Hướng dẫn sử dụng các công cụ phổ biến.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Chứng chỉ Scrum và lộ trình phát triển sự nghiệp', 'content' => 'Các chứng chỉ Scrum phổ biến và lợi ích của chúng.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Adobe Photoshop Toàn tập cho Thiết kế Đồ họa',
            'description' => 'Trở thành chuyên gia Photoshop với khóa học toàn diện này, từ các công cụ cơ bản đến các kỹ thuật chỉnh sửa ảnh, thiết kế đồ họa và hiệu ứng nâng cao.',
            'price' => 720000.00,
            'categoryIds' => [52, 54], // Adobe Photoshop, Thiết kế đồ họa 2D/3D
            'requirements' => [
                'Máy tính cài đặt Adobe Photoshop (phiên bản CC 2018 trở lên).',
                'Không yêu cầu kinh nghiệm thiết kế trước.'
            ],
            'objectives' => [
                'Làm chủ giao diện và các công cụ cơ bản của Photoshop.',
                'Thực hiện các kỹ thuật chỉnh sửa ảnh chuyên nghiệp (retouch, color correction).',
                'Tạo và thao tác với các lớp (layers), mặt nạ (masks), và chế độ hòa trộn (blending modes).',
                'Thiết kế các ấn phẩm đồ họa (poster, banner, social media graphics).',
                'Áp dụng các hiệu ứng đặc biệt và tối ưu hóa hình ảnh cho web/in ấn.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Photoshop và Giao diện làm việc',
                    'description' => 'Làm quen với môi trường Photoshop và các khái niệm cơ bản.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Photoshop là gì? Ứng dụng trong thiết kế đồ họa', 'content' => 'Tổng quan về Photoshop và vai trò của nó.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt và cấu hình Photoshop', 'content' => 'Hướng dẫn cài đặt và tối ưu hóa hiệu suất.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giao diện làm việc: Panels, Tools, Options Bar', 'content' => 'Làm quen với các khu vực chính của giao diện.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tạo và quản lý tài liệu mới', 'content' => 'Thiết lập kích thước, độ phân giải, chế độ màu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Làm việc với Lớp (Layers)',
                    'description' => 'Nền tảng của mọi thiết kế trong Photoshop.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Khái niệm Layers và Bảng Layers', 'content' => 'Hiểu về cách các lớp hoạt động và quản lý chúng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Tạo, sắp xếp và nhóm Layers', 'content' => 'Các thao tác cơ bản với lớp.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Chế độ hòa trộn (Blending Modes)', 'content' => 'Sử dụng blending modes để tạo hiệu ứng.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Layer Styles (Blending Options)', 'content' => 'Áp dụng các hiệu ứng như đổ bóng, viền, dập nổi.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Vùng chọn (Selections) và Mặt nạ (Masks)',
                    'description' => 'Các kỹ thuật chọn vùng và che giấu phần ảnh.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các công cụ tạo vùng chọn cơ bản (Marquee, Lasso, Magic Wand)', 'content' => 'Sử dụng các công cụ để chọn vùng ảnh.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Công cụ chọn nâng cao (Quick Selection, Object Selection)', 'content' => 'Các công cụ thông minh giúp chọn nhanh.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Refine Edge và Select and Mask', 'content' => 'Tinh chỉnh vùng chọn phức tạp, đặc biệt là tóc.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Layer Masks: Che giấu và hiển thị phần ảnh', 'content' => 'Sử dụng mặt nạ để chỉnh sửa không phá hủy.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Clipping Masks', 'content' => 'Sử dụng một lớp để cắt các lớp khác.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Chỉnh sửa ảnh (Retouching) và Điều chỉnh màu sắc',
                    'description' => 'Biến những bức ảnh bình thường thành tác phẩm nghệ thuật.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Công cụ Healing Brush, Clone Stamp, Patch Tool', 'content' => 'Xóa bỏ khuyết điểm trên ảnh chân dung.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Làm mịn da và làm trắng răng', 'content' => 'Các kỹ thuật chỉnh sửa ảnh chân dung chuyên nghiệp.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Adjustment Layers: Điều chỉnh độ sáng, độ tương phản', 'content' => 'Sử dụng các lớp điều chỉnh để chỉnh sửa màu sắc.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Color Balance, Hue/Saturation, Selective Color', 'content' => 'Điều chỉnh màu sắc tổng thể và từng màu cụ thể.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Camera Raw Filter', 'content' => 'Chỉnh sửa ảnh RAW và JPEG với Camera Raw.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Làm việc với Văn bản (Text) và Hình dạng (Shapes)',
                    'description' => 'Tạo và tùy chỉnh văn bản, hình dạng trong thiết kế.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Công cụ Type Tool và Bảng Character/Paragraph', 'content' => 'Tạo văn bản, định dạng font, kích thước, màu sắc.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Text on Path và Text Warp', 'content' => 'Đặt văn bản theo đường dẫn và uốn cong văn bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Công cụ Shape Tool và Custom Shapes', 'content' => 'Vẽ các hình dạng cơ bản và sử dụng hình dạng tùy chỉnh.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Path Operations và Pen Tool cơ bản', 'content' => 'Thao tác với đường Path và công cụ Pen Tool.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Bộ lọc (Filters) và Hiệu ứng đặc biệt',
                    'description' => 'Áp dụng các bộ lọc để tạo ra hiệu ứng ấn tượng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Giới thiệu Filter Gallery', 'content' => 'Khám phá các bộ lọc nghệ thuật trong Filter Gallery.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Blur Filters (Gaussian Blur, Motion Blur, Radial Blur)', 'content' => 'Tạo hiệu ứng làm mờ và chuyển động.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Sharpen Filters và Noise Filters', 'content' => 'Làm sắc nét ảnh và xử lý nhiễu.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Liquify Filter', 'content' => 'Biến dạng hình ảnh một cách sáng tạo.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Smart Objects và Smart Filters', 'content' => 'Sử dụng Smart Objects để chỉnh sửa không phá hủy.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 7: Tối ưu hóa và Xuất file',
                    'description' => 'Chuẩn bị thiết kế cho các mục đích sử dụng khác nhau.',
                    'sortOrder' => 7,
                    'lessons' => [
                        ['title' => 'Bài 7.1: Tối ưu hóa hình ảnh cho Web (Save for Web)', 'content' => 'Giảm kích thước file mà vẫn giữ chất lượng.', 'sortOrder' => 1],
                        ['title' => 'Bài 7.2: Chuẩn bị file cho in ấn (CMYK, DPI)', 'content' => 'Cấu hình file cho in ấn chuyên nghiệp.', 'sortOrder' => 2],
                        ['title' => 'Bài 7.3: Xuất file dưới các định dạng khác nhau (JPG, PNG, GIF, PDF)', 'content' => 'Lưu file với các định dạng phù hợp.', 'sortOrder' => 3],
                        ['title' => 'Bài 7.4: Action và Batch Processing', 'content' => 'Tự động hóa các tác vụ lặp đi lặp lại.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học SEO Chuyên sâu và Chiến lược Content Marketing',
            'description' => 'Nắm vững các kỹ thuật SEO từ cơ bản đến nâng cao và xây dựng chiến lược nội dung hấp dẫn để tăng cường thứ hạng website và thu hút khách hàng tiềm năng.',
            'price' => 850000.00,
            'categoryIds' => [58, 60], // SEO, Content Marketing
            'requirements' => [
                'Có kiến thức cơ bản về internet và website.',
                'Sự kiên nhẫn và khả năng phân tích.'
            ],
            'objectives' => [
                'Thực hiện nghiên cứu từ khóa và phân tích đối thủ cạnh tranh hiệu quả.',
                'Tối ưu hóa SEO On-page và Off-page cho website.',
                'Xây dựng chiến lược Content Marketing từ ý tưởng đến triển khai.',
                'Sử dụng các công cụ SEO và Content Marketing để theo dõi và đánh giá hiệu suất.',
                'Nâng cao thứ hạng website trên các công cụ tìm kiếm và tăng lượng truy cập tự nhiên.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu SEO và Content Marketing',
                    'description' => 'Tổng quan về hai lĩnh vực quan trọng trong Digital Marketing.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: SEO là gì? Vai trò và tầm quan trọng', 'content' => 'Khái niệm SEO, cách hoạt động của công cụ tìm kiếm.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Content Marketing là gì? Mối liên hệ với SEO', 'content' => 'Định nghĩa Content Marketing và cách nó hỗ trợ SEO.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các yếu tố xếp hạng của Google', 'content' => 'Tìm hiểu các yếu tố mà Google sử dụng để xếp hạng website.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Xu hướng SEO và Content Marketing mới nhất', 'content' => 'Cập nhật các xu hướng và thay đổi trong ngành.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Nghiên cứu từ khóa chuyên sâu',
                    'description' => 'Tìm kiếm và phân tích các từ khóa tiềm năng.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Các loại từ khóa và ý định tìm kiếm (Search Intent)', 'content' => 'Phân loại từ khóa và hiểu mục đích người dùng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Quy trình nghiên cứu từ khóa hiệu quả', 'content' => 'Các bước để tìm kiếm từ khóa phù hợp với doanh nghiệp.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Công cụ nghiên cứu từ khóa (Google Keyword Planner, Ahrefs, SEMrush)', 'content' => 'Hướng dẫn sử dụng các công cụ phổ biến.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Phân tích đối thủ cạnh tranh về từ khóa', 'content' => 'Cách xem đối thủ đang xếp hạng cho từ khóa nào.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Xây dựng Keyword Map và cấu trúc website', 'content' => 'Sắp xếp từ khóa vào cấu trúc website hợp lý.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: SEO On-page: Tối ưu nội dung và kỹ thuật',
                    'description' => 'Cải thiện các yếu tố trên website để thân thiện với công cụ tìm kiếm.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Tối ưu tiêu đề (Title Tag) và mô tả (Meta Description)', 'content' => 'Viết tiêu đề và mô tả hấp dẫn, chứa từ khóa.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tối ưu thẻ Heading (H1, H2, H3...)', 'content' => 'Cấu trúc nội dung với các thẻ heading.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Tối ưu nội dung: Độ dài, mật độ từ khóa, LSI Keywords', 'content' => 'Viết nội dung chất lượng, tối ưu cho SEO.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Tối ưu hình ảnh (Alt Text, kích thước, nén)', 'content' => 'Giúp hình ảnh được tìm thấy trên Google Images.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Internal Linking và External Linking', 'content' => 'Xây dựng liên kết nội bộ và liên kết ra ngoài.', 'sortOrder' => 5],
                        ['title' => 'Bài 3.6: Tối ưu tốc độ tải trang và Mobile-friendliness', 'content' => 'Các yếu tố kỹ thuật ảnh hưởng đến SEO.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Chương 4: SEO Off-page: Xây dựng Backlinks và Authority',
                    'description' => 'Tăng cường uy tín và quyền hạn của website thông qua các liên kết bên ngoài.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Backlinks là gì? Tầm quan trọng của Backlinks chất lượng', 'content' => 'Hiểu về Backlinks và cách chúng ảnh hưởng đến SEO.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các chiến lược xây dựng Backlinks (Guest Posting, Broken Link Building)', 'content' => 'Các phương pháp để có được Backlinks tự nhiên và chất lượng.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Phân tích và từ chối Backlinks xấu (Disavow Tool)', 'content' => 'Cách nhận diện và loại bỏ các Backlinks có hại.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Social Signals và Brand Mentions', 'content' => 'Tầm quan trọng của tín hiệu mạng xã hội và nhắc đến thương hiệu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Xây dựng chiến lược Content Marketing',
                    'description' => 'Lên kế hoạch và thực hiện các loại nội dung hấp dẫn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Xác định đối tượng mục tiêu và Buyer Persona', 'content' => 'Hiểu rõ khách hàng để tạo nội dung phù hợp.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Các loại hình Content phổ biến (Blog Post, Video, Infographic...)', 'content' => 'Khám phá các định dạng nội dung khác nhau.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Quy trình tạo nội dung: Từ ý tưởng đến xuất bản', 'content' => 'Các bước để sản xuất nội dung chất lượng.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Lập Content Calendar và phân phối nội dung', 'content' => 'Lên lịch và quảng bá nội dung hiệu quả.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Content Repurposing và Content Atomization', 'content' => 'Tái sử dụng và chia nhỏ nội dung để tối đa hóa hiệu quả.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Đo lường và Phân tích hiệu suất SEO & Content',
                    'description' => 'Sử dụng dữ liệu để đánh giá và cải thiện chiến lược.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Google Analytics: Theo dõi truy cập và hành vi người dùng', 'content' => 'Các chỉ số quan trọng và cách đọc báo cáo.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Google Search Console: Theo dõi hiệu suất tìm kiếm', 'content' => 'Kiểm tra thứ hạng từ khóa, lỗi thu thập dữ liệu.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Các chỉ số KPIs cho SEO và Content Marketing', 'content' => 'Xác định các chỉ số đo lường thành công.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: A/B Testing và Tối ưu hóa chuyển đổi (CRO)', 'content' => 'Thử nghiệm để cải thiện hiệu quả.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Báo cáo hiệu suất và đề xuất cải tiến', 'content' => 'Tổng hợp báo cáo và đưa ra khuyến nghị.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Kỹ năng Lãnh đạo và Quản lý Đội nhóm Hiệu quả',
            'description' => 'Phát triển các kỹ năng lãnh đạo cần thiết để truyền cảm hứng, động viên và quản lý đội nhóm của bạn đạt được mục tiêu chung một cách hiệu quả.',
            'price' => 580000.00,
            'categoryIds' => [65, 63], // Lãnh đạo, Phát triển cá nhân
            'requirements' => [
                'Mong muốn phát triển bản thân và nâng cao kỹ năng lãnh đạo.',
                'Không yêu cầu kinh nghiệm quản lý trước.'
            ],
            'objectives' => [
                'Hiểu các phong cách lãnh đạo khác nhau và khi nào nên áp dụng.',
                'Phát triển kỹ năng giao tiếp hiệu quả để truyền đạt tầm nhìn và động viên đội nhóm.',
                'Xây dựng đội nhóm gắn kết, có động lực và hiệu suất cao.',
                'Giải quyết xung đột và quản lý thay đổi trong tổ chức.',
                'Đánh giá và phát triển năng lực của các thành viên trong đội nhóm.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Khái niệm về Lãnh đạo và Các phong cách',
                    'description' => 'Tìm hiểu về bản chất của lãnh đạo và các cách tiếp cận khác nhau.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Lãnh đạo là gì? Phân biệt lãnh đạo và quản lý', 'content' => 'Định nghĩa lãnh đạo, vai trò và sự khác biệt với quản lý.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các phong cách lãnh đạo phổ biến (Chuyên quyền, Dân chủ, Chuyển đổi, Phục vụ)', 'content' => 'Khám phá các phong cách và ưu nhược điểm.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Phát triển phong cách lãnh đạo cá nhân', 'content' => 'Xác định phong cách phù hợp với bản thân và tình huống.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tầm quan trọng của EQ trong lãnh đạo', 'content' => 'Vai trò của trí tuệ cảm xúc trong việc lãnh đạo đội nhóm.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Kỹ năng Giao tiếp Hiệu quả cho Lãnh đạo',
                    'description' => 'Truyền đạt thông điệp rõ ràng và lắng nghe tích cực.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Giao tiếp phi ngôn ngữ và ngôn ngữ cơ thể', 'content' => 'Hiểu và sử dụng ngôn ngữ cơ thể để giao tiếp hiệu quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Kỹ năng lắng nghe tích cực và đặt câu hỏi', 'content' => 'Lắng nghe để thấu hiểu và đặt câu hỏi khai thác thông tin.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Truyền đạt tầm nhìn và mục tiêu rõ ràng', 'content' => 'Cách trình bày tầm nhìn để truyền cảm hứng cho đội nhóm.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Phản hồi (Feedback) mang tính xây dựng', 'content' => 'Cách đưa ra và nhận phản hồi một cách hiệu quả.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Giao tiếp trong các tình huống khó khăn (xung đột, khủng hoảng)', 'content' => 'Xử lý các cuộc trò chuyện nhạy cảm.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Xây dựng và Phát triển Đội nhóm',
                    'description' => 'Tạo ra một đội nhóm gắn kết, có động lực và hiệu suất cao.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các giai đoạn phát triển đội nhóm (Forming, Storming, Norming, Performing)', 'content' => 'Hiểu các giai đoạn để hỗ trợ đội nhóm.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Xây dựng sự tin tưởng và gắn kết trong đội nhóm', 'content' => 'Các hoạt động và nguyên tắc để tăng cường sự gắn kết.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phân công vai trò, trách nhiệm và ủy quyền', 'content' => 'Cách giao việc hiệu quả và trao quyền cho thành viên.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Động viên và công nhận thành tích của đội nhóm', 'content' => 'Các phương pháp tạo động lực và khen thưởng.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Quản lý hiệu suất và đánh giá nhân viên', 'content' => 'Thiết lập mục tiêu, theo dõi và đánh giá hiệu suất.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giải quyết xung đột và Quản lý thay đổi',
                    'description' => 'Đối phó với các thách thức trong quản lý đội nhóm.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các nguyên nhân gây xung đột trong đội nhóm', 'content' => 'Nhận diện nguồn gốc của xung đột.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Kỹ năng giải quyết xung đột và đàm phán', 'content' => 'Các chiến lược để giải quyết mâu thuẫn.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Quản lý thay đổi trong tổ chức', 'content' => 'Dẫn dắt đội nhóm vượt qua các giai đoạn thay đổi.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Xây dựng khả năng phục hồi (Resilience) cho đội nhóm', 'content' => 'Giúp đội nhóm đối mặt và vượt qua khó khăn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Lãnh đạo bằng ví dụ và Phát triển bản thân',
                    'description' => 'Trở thành một nhà lãnh đạo truyền cảm hứng và không ngừng học hỏi.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tầm quan trọng của sự chính trực và đạo đức trong lãnh đạo', 'content' => 'Xây dựng lòng tin thông qua hành động.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Lãnh đạo bằng ví dụ (Leading by Example)', 'content' => 'Thể hiện những giá trị mà bạn muốn đội nhóm noi theo.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Học hỏi liên tục và phát triển kỹ năng lãnh đạo', 'content' => 'Các phương pháp tự học và phát triển.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Mentoring và Coaching cho thành viên đội nhóm', 'content' => 'Hỗ trợ và phát triển năng lực cá nhân.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Lãnh đạo từ xa và trong môi trường đa văn hóa',
                    'description' => 'Thách thức và cơ hội của lãnh đạo trong môi trường làm việc hiện đại.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Lãnh đạo đội nhóm từ xa (Remote Leadership)', 'content' => 'Các chiến lược để quản lý đội nhóm phân tán.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Giao tiếp và hợp tác trong môi trường ảo', 'content' => 'Sử dụng công cụ và kỹ thuật để duy trì kết nối.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Lãnh đạo trong môi trường đa văn hóa', 'content' => 'Hiểu và tôn trọng sự khác biệt văn hóa.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xây dựng đội nhóm đa dạng và hòa nhập', 'content' => 'Tận dụng sức mạnh của sự đa dạng.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Học Guitar Từ A đến Z cho Người Mới Bắt Đầu',
            'description' => 'Khóa học này được thiết kế dành riêng cho những người chưa từng chơi guitar. Bạn sẽ học từ cách cầm đàn, các hợp âm cơ bản đến việc chơi những bài hát yêu thích.',
            'price' => 399000.00,
            'categoryIds' => [70, 69], // Nhạc cụ (Piano, Guitar, v.v.), Âm nhạc
            'requirements' => [
                'Một cây đàn guitar (acoustic hoặc classic).',
                'Sự kiên trì và niềm đam mê âm nhạc.'
            ],
            'objectives' => [
                'Cầm đàn và điều chỉnh tư thế đúng cách.',
                'Làm quen với các bộ phận của đàn guitar.',
                'Học các hợp âm cơ bản (Major, Minor) và cách chuyển đổi hợp âm mượt mà.',
                'Thực hành các điệu đệm cơ bản (quạt chả, rải).',
                'Chơi được các bài hát đơn giản và yêu thích.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Làm quen với Đàn Guitar',
                    'description' => 'Những kiến thức đầu tiên về cây đàn của bạn.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Các loại đàn guitar và lựa chọn đàn phù hợp', 'content' => 'Phân biệt guitar acoustic, classic, điện và lời khuyên chọn đàn.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cấu tạo của đàn guitar và chức năng từng bộ phận', 'content' => 'Tìm hiểu về thùng đàn, cần đàn, phím, dây đàn, khóa đàn.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Cách cầm đàn guitar đúng tư thế', 'content' => 'Tư thế ngồi, đặt đàn, vị trí tay trái, tay phải.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Cách lên dây đàn (Tuning) bằng Tuner và App', 'content' => 'Hướng dẫn lên dây đàn chuẩn xác cho người mới bắt đầu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các kỹ thuật cơ bản của Tay phải',
                    'description' => 'Học cách tạo ra âm thanh từ dây đàn.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Kỹ thuật gảy dây đơn (Single String Picking)', 'content' => 'Tập gảy từng dây một để làm quen với âm thanh.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Kỹ thuật quạt chả cơ bản', 'content' => 'Tập quạt xuống và lên với các nhịp điệu đơn giản.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Kỹ thuật rải dây (Arpeggio) cơ bản', 'content' => 'Tập rải từng nốt của hợp âm.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Luyện tập tiết tấu và giữ nhịp', 'content' => 'Sử dụng Metronome để luyện tập nhịp điệu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Hợp âm cơ bản và Chuyển đổi hợp âm',
                    'description' => 'Nắm vững các hợp âm đầu tiên và cách chuyển đổi chúng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Hợp âm Major (Trưởng): C, G, D, E, A', 'content' => 'Cách bấm và luyện tập các hợp âm trưởng cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Hợp âm Minor (Thứ): Am, Em, Dm', 'content' => 'Cách bấm và luyện tập các hợp âm thứ cơ bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Luyện tập chuyển đổi hợp âm mượt mà', 'content' => 'Các bài tập chuyển đổi giữa các hợp âm.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Hợp âm 7 (Seventh Chord) cơ bản: G7, C7, D7', 'content' => 'Giới thiệu các hợp âm 7 và cách bấm.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Các điệu đệm phổ biến',
                    'description' => 'Học các điệu đệm để chơi các bài hát.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Điệu Ballad cơ bản (Quạt chả chậm)', 'content' => 'Thực hành điệu ballad cho các bài hát nhẹ nhàng.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Điệu Slow Rock', 'content' => 'Học điệu Slow Rock và ứng dụng.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Điệu Disco/Pop', 'content' => 'Điệu đệm nhanh và sôi động.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Điệu Valse (3/4)', 'content' => 'Học điệu Valse cho các bài hát có nhịp 3.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Thực hành chơi bài hát đơn giản',
                    'description' => 'Áp dụng những gì đã học vào các bài hát cụ thể.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Chơi bài "Happy Birthday"', 'content' => 'Thực hành với một bài hát quen thuộc.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chơi bài "Twinkle Twinkle Little Star"', 'content' => 'Bài tập đơn giản để luyện ngón.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Chơi một bài hát tiếng Việt yêu thích (ví dụ: "Bèo Dạt Mây Trôi")', 'content' => 'Áp dụng hợp âm và điệu đệm vào bài hát Việt.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đọc Tablature (Tab) cơ bản', 'content' => 'Làm quen với cách đọc tab để chơi các giai điệu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Các kỹ thuật nâng cao và Luyện tập tiếp theo',
                    'description' => 'Mở rộng kỹ năng và định hướng học tập.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Kỹ thuật Bar Chords (Hợp âm chặn)', 'content' => 'Học cách bấm các hợp âm chặn.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Giới thiệu về Scales (Gam) cơ bản', 'content' => 'Làm quen với các gam cơ bản (Major Scale).', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Luyện tập ngón tay và tốc độ', 'content' => 'Các bài tập tăng cường sức mạnh và sự linh hoạt của ngón tay.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Cách tự học và tìm kiếm tài liệu', 'content' => 'Lời khuyên để tiếp tục hành trình học guitar.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Yoga và Thiền: Cải thiện Sức khỏe Thể chất và Tinh thần',
            'description' => 'Khám phá sức mạnh của Yoga và Thiền để tăng cường sự dẻo dai, giảm căng thẳng, cải thiện sự tập trung và đạt được sự bình an nội tâm.',
            'price' => 420000.00,
            'categoryIds' => [74, 75], // Yoga, Thiền
            'requirements' => [
                'Một tấm thảm yoga.',
                'Không yêu cầu kinh nghiệm trước về yoga hoặc thiền.'
            ],
            'objectives' => [
                'Thực hiện các tư thế Yoga cơ bản đúng kỹ thuật.',
                'Hiểu các nguyên tắc thở trong Yoga (Pranayama).',
                'Thực hành các kỹ thuật thiền định để giảm căng thẳng.',
                'Cải thiện sự linh hoạt, sức mạnh và cân bằng của cơ thể.',
                'Nâng cao nhận thức về bản thân và đạt được trạng thái thư giãn sâu.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Yoga và Thiền',
                    'description' => 'Khám phá nguồn gốc và lợi ích của Yoga và Thiền.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Yoga là gì? Lịch sử và các trường phái chính', 'content' => 'Tổng quan về Yoga và sự phát triển của nó.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Thiền là gì? Các loại hình thiền định', 'content' => 'Định nghĩa thiền, thiền chánh niệm, thiền định.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Lợi ích của Yoga và Thiền đối với sức khỏe thể chất và tinh thần', 'content' => 'Tác động tích cực lên cơ thể và tâm trí.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Chuẩn bị cho buổi tập: Trang phục, không gian, dụng cụ', 'content' => 'Những điều cần chuẩn bị trước khi bắt đầu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các tư thế Yoga cơ bản (Asanas)',
                    'description' => 'Học và thực hành các tư thế Yoga nền tảng.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tư thế khởi động và làm ấm cơ thể', 'content' => 'Các động tác nhẹ nhàng chuẩn bị cho buổi tập.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Tư thế đứng: Mountain Pose, Warrior I & II', 'content' => 'Các tư thế đứng giúp tăng cường sức mạnh và cân bằng.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Tư thế ngồi: Easy Pose, Lotus Pose, Cat-Cow', 'content' => 'Các tư thế ngồi và động tác linh hoạt cột sống.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Tư thế nằm: Corpse Pose, Bridge Pose', 'content' => 'Các tư thế thư giãn và tăng cường cơ lưng.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Chuỗi Chào Mặt Trời (Sun Salutation A & B)', 'content' => 'Thực hành chuỗi động tác liên hoàn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Kỹ thuật thở Yoga (Pranayama)',
                    'description' => 'Kiểm soát hơi thở để điều hòa năng lượng và tâm trí.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Hơi thở bụng (Diaphragmatic Breathing)', 'content' => 'Kỹ thuật thở sâu bằng bụng.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Hơi thở Ujjayi (Ocean Breath)', 'content' => 'Kỹ thuật thở tạo ra âm thanh nhẹ nhàng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Hơi thở luân phiên mũi (Nadi Shodhana)', 'content' => 'Cân bằng năng lượng trong cơ thể.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Hơi thở Kapalabhati (Skull Shining Breath)', 'content' => 'Kỹ thuật thở làm sạch và tăng cường năng lượng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Thiền định cho Người mới bắt đầu',
                    'description' => 'Các bước để bắt đầu hành trình thiền định của bạn.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Tư thế ngồi thiền và môi trường thiền định', 'content' => 'Cách ngồi thoải mái và tạo không gian yên tĩnh.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Thiền chánh niệm hơi thở (Mindful Breathing)', 'content' => 'Tập trung vào hơi thở để giữ tâm trí ở hiện tại.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Thiền quét cơ thể (Body Scan Meditation)', 'content' => 'Quét qua các bộ phận cơ thể để nhận biết cảm giác.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Thiền từ bi (Metta Meditation)', 'content' => 'Phát triển lòng từ bi cho bản thân và người khác.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Yoga và Thiền trong cuộc sống hàng ngày',
                    'description' => 'Áp dụng các nguyên tắc vào đời sống để có cuộc sống cân bằng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Yoga cho dân văn phòng và giảm đau lưng', 'content' => 'Các tư thế Yoga đơn giản có thể thực hiện tại bàn làm việc.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Thiền chánh niệm trong các hoạt động hàng ngày', 'content' => 'Thực hành chánh niệm khi ăn, đi bộ, làm việc nhà.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Xây dựng thói quen Yoga và Thiền hàng ngày', 'content' => 'Lời khuyên để duy trì luyện tập đều đặn.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Lợi ích của việc kết hợp Yoga và Thiền', 'content' => 'Tối đa hóa hiệu quả khi thực hành cả hai.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Các chủ đề nâng cao và Luyện tập tiếp theo',
                    'description' => 'Mở rộng kiến thức và kỹ năng trong Yoga và Thiền.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Giới thiệu về Yoga Nidra (Yoga Ngủ)', 'content' => 'Kỹ thuật thư giãn sâu và phục hồi năng lượng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Thiền đi bộ (Walking Meditation)', 'content' => 'Thực hành thiền trong khi di chuyển.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Các nguyên tắc đạo đức trong Yoga (Yamas và Niyamas)', 'content' => 'Hiểu về các nguyên tắc sống của Yoga.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Tìm kiếm cộng đồng và giáo viên Yoga/Thiền', 'content' => 'Lời khuyên để tiếp tục hành trình của bạn.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Lập trình Game với Unity 3D',
            'description' => 'Học cách phát triển game 2D và 3D chuyên nghiệp bằng Unity Engine và ngôn ngữ C#. Khóa học này sẽ đưa bạn từ cơ bản đến xây dựng các dự án game hoàn chỉnh.',
            'price' => 1100000.00,
            'categoryIds' => [21, 20], // Unity, Lập trình Game
            'requirements' => [
                'Kiến thức cơ bản về lập trình (ưu tiên C#).',
                'Máy tính có cấu hình đủ để chạy Unity Editor.'
            ],
            'objectives' => [
                'Làm chủ giao diện và các công cụ của Unity Editor.',
                'Viết script C# để điều khiển game objects và logic game.',
                'Tạo và quản lý các tài sản (assets) trong Unity.',
                'Xây dựng hệ thống vật lý, va chạm và tương tác trong game.',
                'Thiết kế giao diện người dùng (UI) và hiệu ứng hình ảnh/âm thanh.',
                'Triển khai game lên các nền tảng khác nhau.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Unity và Cài đặt Môi trường',
                    'description' => 'Tổng quan về Unity và thiết lập để bắt đầu phát triển game.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Unity là gì? Tại sao Unity phổ biến?', 'content' => 'Khái niệm Unity, các loại game có thể tạo và ưu điểm.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt Unity Hub và Unity Editor', 'content' => 'Hướng dẫn cài đặt các công cụ cần thiết.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giao diện Unity Editor: Scene, Game, Hierarchy, Project, Inspector', 'content' => 'Làm quen với các cửa sổ chính của Unity.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tạo dự án mới và quản lý Scene', 'content' => 'Các bước tạo dự án và quản lý các màn chơi.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Cơ bản về C# trong Unity',
                    'description' => 'Học ngôn ngữ lập trình C# trong ngữ cảnh phát triển game.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Biến, kiểu dữ liệu và toán tử trong C#', 'content' => 'Các khái niệm cơ bản của ngôn ngữ C#.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Cấu trúc điều khiển và vòng lặp', 'content' => 'Sử dụng if/else, for, while để điều khiển logic.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Hàm (Methods) và tham số', 'content' => 'Tạo và gọi các hàm để tổ chức mã.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Lập trình Hướng Đối Tượng (OOP) cơ bản trong C#', 'content' => 'Class, Object, Kế thừa, Đa hình.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Script trong Unity: MonoBehaviour, Start, Update', 'content' => 'Cách viết và gắn script vào game objects.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Game Objects và Components',
                    'description' => 'Xây dựng thế giới game bằng cách kết hợp các thành phần.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Game Objects: Tạo, di chuyển, xoay, phóng to/thu nhỏ', 'content' => 'Các thao tác cơ bản với game objects.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Components: Transform, Mesh Renderer, Collider', 'content' => 'Hiểu về các thành phần và chức năng của chúng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Prefabs: Tái sử dụng game objects', 'content' => 'Tạo và sử dụng prefabs để quản lý tài sản.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Làm việc với Material và Texture', 'content' => 'Tạo vật liệu và áp dụng texture cho game objects.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Light và Camera trong Unity', 'content' => 'Thiết lập ánh sáng và góc nhìn cho game.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Vật lý và Va chạm trong Game',
                    'description' => 'Tạo ra các tương tác vật lý thực tế trong game.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Rigidbody: Thêm vật lý cho game objects', 'content' => 'Sử dụng Rigidbody để mô phỏng trọng lực, lực đẩy.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Collider: Phát hiện va chạm', 'content' => 'Các loại Collider và cách chúng hoạt động.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Xử lý va chạm và Trigger với C# Script', 'content' => 'Viết mã để phản ứng khi va chạm xảy ra.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Physics Materials', 'content' => 'Tạo vật liệu vật lý để điều chỉnh độ ma sát, độ nảy.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Giao diện người dùng (UI) và Âm thanh',
                    'description' => 'Thiết kế UI và thêm âm thanh để nâng cao trải nghiệm người chơi.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Canvas, Rect Transform và UI Elements (Text, Image, Button)', 'content' => 'Xây dựng giao diện người dùng trong Unity.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Xử lý sự kiện UI (OnClick, OnPointerDown)', 'content' => 'Làm cho các phần tử UI tương tác.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Slider, Toggle, Input Field', 'content' => 'Các phần tử UI nâng cao.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Thêm nhạc nền và hiệu ứng âm thanh (Audio Source, Audio Listener)', 'content' => 'Tích hợp âm thanh vào game.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Animation và Hệ thống hạt (Particle System)',
                    'description' => 'Tạo hiệu ứng động và các hiệu ứng đặc biệt trong game.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Animation: Tạo hoạt ảnh cho game objects', 'content' => 'Sử dụng cửa sổ Animation để tạo keyframe animation.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Animator Controller: Quản lý các trạng thái hoạt ảnh', 'content' => 'Thiết lập chuyển đổi giữa các hoạt ảnh.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Hệ thống hạt (Particle System): Tạo hiệu ứng khói, lửa, nổ', 'content' => 'Sử dụng Particle System để tạo hiệu ứng hình ảnh.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Post-processing Effects', 'content' => 'Thêm các hiệu ứng hậu kỳ để cải thiện đồ họa.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 7: Triển khai game và Tối ưu hiệu suất',
                    'description' => 'Đưa game của bạn đến người chơi và đảm bảo game chạy mượt mà.',
                    'sortOrder' => 7,
                    'lessons' => [
                        ['title' => 'Bài 7.1: Tối ưu hóa hiệu suất game trong Unity', 'content' => 'Các kỹ thuật để cải thiện tốc độ khung hình và tài nguyên.', 'sortOrder' => 1],
                        ['title' => 'Bài 7.2: Debugging và Profiling trong Unity', 'content' => 'Tìm và sửa lỗi, theo dõi hiệu suất game.', 'sortOrder' => 2],
                        ['title' => 'Bài 7.3: Triển khai game lên PC, WebGL, Mobile', 'content' => 'Các bước để xuất bản game lên các nền tảng.', 'sortOrder' => 3],
                        ['title' => 'Bài 7.4: Giới thiệu Asset Store và cộng đồng Unity', 'content' => 'Sử dụng Asset Store để tìm tài nguyên và học hỏi từ cộng đồng.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Phân tích Dữ liệu với Excel và Power BI',
            'description' => 'Học cách sử dụng Excel và Power BI để thu thập, làm sạch, phân tích và trực quan hóa dữ liệu, giúp đưa ra các quyết định kinh doanh sáng suốt.',
            'price' => 780000.00,
            'categoryIds' => [39, 92], // Phân tích kinh doanh (Business Analytics), Data Engineering
            'requirements' => [
                'Máy tính cài đặt Microsoft Excel (phiên bản 2016 trở lên) và Power BI Desktop.',
                'Không yêu cầu kinh nghiệm phân tích dữ liệu trước.'
            ],
            'objectives' => [
                'Làm chủ các hàm và công cụ phân tích dữ liệu trong Excel.',
                'Sử dụng Power Query để làm sạch và biến đổi dữ liệu.',
                'Xây dựng các mô hình dữ liệu quan hệ trong Power BI.',
                'Tạo các báo cáo và dashboard tương tác với Power BI.',
                'Trực quan hóa dữ liệu hiệu quả để kể câu chuyện từ dữ liệu.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Phân tích Dữ liệu và Excel',
                    'description' => 'Tổng quan về phân tích dữ liệu và vai trò của Excel.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Phân tích Dữ liệu là gì? Vai trò trong kinh doanh', 'content' => 'Định nghĩa, các giai đoạn và tầm quan trọng.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Excel như một công cụ phân tích dữ liệu', 'content' => 'Các tính năng mạnh mẽ của Excel cho phân tích.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Cấu trúc dữ liệu trong Excel và các lỗi thường gặp', 'content' => 'Cách tổ chức dữ liệu để dễ phân tích.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các hàm Excel cơ bản cho phân tích (SUM, AVERAGE, COUNT, IF)', 'content' => 'Thực hành các hàm tính toán và điều kiện.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Xử lý và Làm sạch dữ liệu trong Excel',
                    'description' => 'Biến dữ liệu thô thành dữ liệu sẵn sàng để phân tích.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Sắp xếp, Lọc và Định dạng có điều kiện', 'content' => 'Các kỹ thuật cơ bản để tổ chức và làm nổi bật dữ liệu.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Remove Duplicates, Text to Columns, Flash Fill', 'content' => 'Làm sạch dữ liệu tự động và tách cột.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Hàm VLOOKUP, HLOOKUP, INDEX-MATCH', 'content' => 'Tìm kiếm và kết hợp dữ liệu từ các bảng khác nhau.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Data Validation và Conditional Formatting nâng cao', 'content' => 'Kiểm soát nhập liệu và trực quan hóa dữ liệu.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Power Query trong Excel: Giới thiệu và kết nối dữ liệu', 'content' => 'Sử dụng Power Query để nhập và biến đổi dữ liệu.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Phân tích Dữ liệu với PivotTables và Charts trong Excel',
                    'description' => 'Tóm tắt và trực quan hóa dữ liệu lớn một cách dễ dàng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: PivotTable: Tạo và tùy chỉnh báo cáo tổng hợp', 'content' => 'Sử dụng PivotTable để tóm tắt dữ liệu.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Slicers và Timelines trong PivotTable', 'content' => 'Lọc dữ liệu tương tác trong PivotTable.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: PivotChart: Trực quan hóa dữ liệu từ PivotTable', 'content' => 'Tạo biểu đồ động từ PivotTable.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Các loại biểu đồ phổ biến và khi nào sử dụng', 'content' => 'Biểu đồ cột, đường, tròn, phân tán.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Tạo Dashboard tương tác trong Excel', 'content' => 'Kết hợp các PivotTable và biểu đồ để tạo dashboard.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giới thiệu Power BI Desktop',
                    'description' => 'Làm quen với Power BI và các thành phần chính.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Power BI là gì? So sánh với Excel', 'content' => 'Tổng quan về Power BI và lợi ích của nó.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Cài đặt Power BI Desktop và Giao diện làm việc', 'content' => 'Hướng dẫn cài đặt và các cửa sổ chính.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Kết nối dữ liệu từ các nguồn khác nhau (Excel, SQL, Web)', 'content' => 'Nhập dữ liệu vào Power BI từ nhiều nguồn.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Power Query Editor trong Power BI', 'content' => 'Làm sạch và biến đổi dữ liệu tương tự Excel Power Query.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Mô hình hóa dữ liệu (Data Modeling) trong Power BI',
                    'description' => 'Xây dựng các mối quan hệ giữa các bảng dữ liệu.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Khái niệm về Data Model và Star Schema', 'content' => 'Hiểu về cách tổ chức dữ liệu trong Power BI.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Tạo và quản lý Relationships giữa các bảng', 'content' => 'Thiết lập mối quan hệ 1-nhiều, nhiều-nhiều.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: DAX (Data Analysis Expressions) cơ bản', 'content' => 'Giới thiệu ngôn ngữ DAX để tạo các phép đo và cột tính toán.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tạo Measures và Calculated Columns với DAX', 'content' => 'Thực hành viết các công thức DAX đơn giản.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Trực quan hóa dữ liệu với Power BI',
                    'description' => 'Thiết kế các báo cáo và dashboard tương tác và hấp dẫn.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Các loại Visualizations trong Power BI', 'content' => 'Biểu đồ cột, đường, tròn, ma trận, bản đồ.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Thiết kế báo cáo và Dashboard hiệu quả', 'content' => 'Nguyên tắc thiết kế để báo cáo dễ hiểu.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Slicers, Filters và Drill-through', 'content' => 'Tạo tương tác cho báo cáo.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Conditional Formatting trong Power BI', 'content' => 'Làm nổi bật dữ liệu quan trọng.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Xuất bản báo cáo lên Power BI Service', 'content' => 'Chia sẻ báo cáo với người khác.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Lập trình Mobile với React Native',
            'description' => 'Xây dựng ứng dụng di động đa nền tảng cho iOS và Android chỉ với JavaScript và React Native. Khóa học này sẽ hướng dẫn bạn từ cơ bản đến nâng cao.',
            'price' => 920000.00,
            'categoryIds' => [17, 14], // React Native, Lập trình Mobile
            'requirements' => [
                'Kiến thức cơ bản về JavaScript (ES6+).',
                'Hiểu biết về ReactJS là một lợi thế.',
                'Máy tính có kết nối internet và đủ cấu hình để chạy Node.js, Expo CLI/React Native CLI.'
            ],
            'objectives' => [
                'Nắm vững các thành phần cơ bản của React Native.',
                'Xây dựng giao diện người dùng responsive cho cả iOS và Android.',
                'Quản lý trạng thái ứng dụng với Context API hoặc Redux.',
                'Tương tác với API backend và lưu trữ dữ liệu cục bộ.',
                'Triển khai ứng dụng lên các kho ứng dụng.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu React Native và Thiết lập Môi trường',
                    'description' => 'Tổng quan về React Native và các bước chuẩn bị để bắt đầu.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: React Native là gì? Ưu nhược điểm', 'content' => 'Khái niệm, lợi ích của việc phát triển đa nền tảng.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: So sánh Expo CLI và React Native CLI', 'content' => 'Lựa chọn công cụ phù hợp cho dự án của bạn.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Cài đặt Node.js, Watchman, CocoaPods (nếu dùng RN CLI)', 'content' => 'Hướng dẫn chi tiết cài đặt các dependency.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tạo dự án React Native đầu tiên', 'content' => 'Chạy ứng dụng "Hello World" trên emulator/thiết bị thật.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các Components cơ bản và Styling',
                    'description' => 'Xây dựng giao diện người dùng với các thành phần của React Native.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: View, Text, Image, Button', 'content' => 'Các components cơ bản để hiển thị nội dung và tương tác.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Styling với StyleSheet và Flexbox', 'content' => 'Sử dụng Flexbox để tạo layout responsive.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: ScrollView và FlatList/SectionList', 'content' => 'Hiển thị danh sách dữ liệu hiệu quả.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: TouchableOpacity, Pressable, TextInput', 'content' => 'Các components tương tác và nhập liệu.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Platform-specific code', 'content' => 'Viết mã riêng cho iOS và Android.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Quản lý trạng thái và Điều hướng',
                    'description' => 'Quản lý dữ liệu và chuyển đổi giữa các màn hình.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: State và Props trong React Native', 'content' => 'Cập nhật trạng thái và truyền dữ liệu giữa components.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: React Hooks (useState, useEffect, useContext)', 'content' => 'Sử dụng Hooks để quản lý trạng thái và side effects.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Context API để quản lý trạng thái toàn cục', 'content' => 'Chia sẻ dữ liệu giữa các components mà không cần prop drilling.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Giới thiệu React Navigation', 'content' => 'Cài đặt và sử dụng thư viện điều hướng phổ biến.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Stack Navigator, Tab Navigator, Drawer Navigator', 'content' => 'Các loại điều hướng khác nhau trong React Navigation.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Tương tác với API và Dữ liệu',
                    'description' => 'Kết nối ứng dụng React Native với các dịch vụ web.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Fetch API và Axios để gửi HTTP Requests', 'content' => 'Gửi yêu cầu GET, POST, PUT, DELETE đến API.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Xử lý JSON Data và hiển thị', 'content' => 'Parse dữ liệu JSON và hiển thị lên UI.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Async Storage: Lưu trữ dữ liệu cục bộ', 'content' => 'Lưu trữ dữ liệu đơn giản trên thiết bị.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Firebase Integration (Authentication, Firestore)', 'content' => 'Tích hợp Firebase cho xác thực và cơ sở dữ liệu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Xử lý Forms và Validation',
                    'description' => 'Tạo các biểu mẫu nhập liệu và kiểm tra tính hợp lệ.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Controlled Components và Uncontrolled Components', 'content' => 'Các cách quản lý giá trị của TextInput.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Formik và Yup để quản lý form và validation', 'content' => 'Sử dụng thư viện bên thứ ba để đơn giản hóa form.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Hiển thị thông báo lỗi và phản hồi người dùng', 'content' => 'Cách thông báo cho người dùng về lỗi nhập liệu.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tải lên hình ảnh và file', 'content' => 'Sử dụng ImagePicker và các thư viện khác để tải file.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Tối ưu hiệu suất và Triển khai ứng dụng',
                    'description' => 'Cải thiện hiệu suất ứng dụng và đưa lên kho ứng dụng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Debugging trong React Native', 'content' => 'Sử dụng Chrome Debugger và React Native Debugger.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Tối ưu hóa hiệu suất ứng dụng', 'content' => 'Giảm thiểu re-renders, sử dụng memo, PureComponent.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chuẩn bị ứng dụng để Release (Expo Build/Bare Workflow)', 'content' => 'Các bước để đóng gói ứng dụng.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Triển khai lên Google Play Store và Apple App Store', 'content' => 'Các bước để publish ứng dụng di động.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Thiết kế UI/UX với Figma Chuyên sâu',
            'description' => 'Nâng cao kỹ năng thiết kế giao diện người dùng và trải nghiệm người dùng với công cụ Figma, từ wireframing đến prototyping và hệ thống thiết kế.',
            'price' => 680000.00,
            'categoryIds' => [100, 51], // Figma, Thiết kế UI/UX
            'requirements' => [
                'Kiến thức cơ bản về thiết kế UI/UX hoặc đã hoàn thành khóa học cơ bản.',
                'Máy tính có kết nối internet và trình duyệt web.'
            ],
            'objectives' => [
                'Làm chủ các tính năng nâng cao của Figma (Auto Layout, Components, Variants).',
                'Xây dựng hệ thống thiết kế (Design System) mạnh mẽ trong Figma.',
                'Tạo các prototype tương tác phức tạp và kiểm thử người dùng.',
                'Hợp tác hiệu quả với các thành viên trong đội nhóm và nhà phát triển.',
                'Áp dụng các nguyên tắc thiết kế UI/UX nâng cao vào dự án thực tế.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Figma Nâng cao và Quy trình làm việc',
                    'description' => 'Khám phá các tính năng mạnh mẽ của Figma và tối ưu hóa quy trình.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Tổng quan về Figma và các tính năng mới nhất', 'content' => 'Cập nhật các tính năng và lợi ích của Figma.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Tổ chức file và project trong Figma', 'content' => 'Cách quản lý dự án lớn và phức tạp.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Sử dụng Plugins và Widgets trong Figma', 'content' => 'Mở rộng chức năng của Figma với các plugin.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Hợp tác trong Figma: Chia sẻ, comment, version history', 'content' => 'Làm việc nhóm hiệu quả trên Figma.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Auto Layout và Responsive Design',
                    'description' => 'Thiết kế giao diện linh hoạt và thích ứng với mọi kích thước màn hình.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Giới thiệu Auto Layout và các thuộc tính', 'content' => 'Tạo các thành phần tự động co giãn và sắp xếp.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Xây dựng Components với Auto Layout', 'content' => 'Tạo các thành phần UI có thể tái sử dụng.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Responsive Design trong Figma', 'content' => 'Thiết kế cho các kích thước màn hình khác nhau.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Constraints và Fixed Positions', 'content' => 'Kiểm soát vị trí các phần tử khi thay đổi kích thước.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Components, Variants và Design Systems',
                    'description' => 'Xây dựng một hệ thống thiết kế mạnh mẽ và nhất quán.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Master Components và Instances', 'content' => 'Tạo và quản lý các thành phần chính.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Variants: Quản lý các trạng thái của Component', 'content' => 'Tạo các biến thể của cùng một component.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Interactive Components', 'content' => 'Tạo các component có tương tác trực tiếp trong Design System.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Xây dựng Design System trong Figma', 'content' => 'Các bước để tạo một thư viện thành phần và style.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Style Guides và Documentation', 'content' => 'Tài liệu hóa Design System của bạn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Prototyping nâng cao và Kiểm thử người dùng',
                    'description' => 'Tạo các prototype tương tác phức tạp và thu thập phản hồi.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Smart Animate và Interactive Overlays', 'content' => 'Tạo các hiệu ứng chuyển động mượt mà.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Conditional Logic và Variables trong Prototype', 'content' => 'Tạo các luồng tương tác phức tạp hơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: User Testing: Lập kế hoạch và thực hiện', 'content' => 'Các bước để kiểm thử prototype với người dùng thực.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Phân tích kết quả User Testing và cải tiến thiết kế', 'content' => 'Sử dụng phản hồi để tối ưu hóa thiết kế.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Handoff cho Developer và Quy trình làm việc',
                    'description' => 'Đảm bảo quá trình chuyển giao thiết kế diễn ra suôn sẻ.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Inspection Panel trong Figma', 'content' => 'Cách nhà phát triển lấy thông số thiết kế.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Export Assets và Code Snippets', 'content' => 'Xuất các tài sản và mã CSS/Swift/Android XML.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Quy trình Handoff hiệu quả giữa Designer và Developer', 'content' => 'Các bước để đảm bảo sự phối hợp tốt.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Version Control và Branching trong Figma', 'content' => 'Quản lý các phiên bản thiết kế và làm việc song song.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Các nguyên tắc UI/UX nâng cao và Xu hướng mới',
                    'description' => 'Cập nhật kiến thức và kỹ năng để luôn dẫn đầu trong ngành.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Microinteractions và Animation trong UI', 'content' => 'Tạo các tương tác nhỏ để cải thiện trải nghiệm.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Thiết kế cho Accessibility (Khả năng tiếp cận)', 'content' => 'Đảm bảo thiết kế thân thiện với mọi đối tượng người dùng.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Dark Mode và Light Mode', 'content' => 'Thiết kế cho cả hai chế độ hiển thị.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xu hướng thiết kế UI/UX hiện đại (Neumorphism, Glassmorphism)', 'content' => 'Khám phá các phong cách thiết kế mới.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Digital Marketing Toàn Diện: Từ Chiến Lược đến Triển Khai',
            'description' => 'Nắm vững các kiến thức và kỹ năng cần thiết để xây dựng và triển khai các chiến dịch Digital Marketing hiệu quả, từ SEO, quảng cáo trả phí đến email marketing và phân tích dữ liệu.',
            'price' => 890000.00,
            'categoryIds' => [57, 56], // Digital Marketing, Marketing
            'requirements' => [
                'Không yêu cầu kinh nghiệm trước về marketing.',
                'Máy tính có kết nối internet.'
            ],
            'objectives' => [
                'Hiểu rõ các kênh Digital Marketing chính và cách chúng hoạt động.',
                'Xây dựng chiến lược Digital Marketing tổng thể phù hợp với mục tiêu kinh doanh.',
                'Thực hiện tối ưu hóa công cụ tìm kiếm (SEO) và quảng cáo Google Ads/Facebook Ads.',
                'Phát triển chiến lược Content Marketing và Email Marketing hiệu quả.',
                'Phân tích dữ liệu và tối ưu hóa hiệu suất chiến dịch.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tổng quan về Digital Marketing và Chiến lược',
                    'description' => 'Cái nhìn tổng thể về Digital Marketing và cách xây dựng chiến lược.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Digital Marketing là gì? Vai trò và xu hướng', 'content' => 'Định nghĩa, tầm quan trọng và các xu hướng mới.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các kênh Digital Marketing chính: Tổng quan', 'content' => 'Giới thiệu SEO, SEM, Social Media, Email, Content, Affiliate.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Xây dựng chiến lược Digital Marketing tổng thể (SMART Goals, STP, Marketing Mix)', 'content' => 'Các bước lập kế hoạch chiến lược hiệu quả.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Xác định đối tượng mục tiêu và Buyer Persona', 'content' => 'Hiểu rõ khách hàng để định hướng chiến lược.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Tối ưu hóa Công cụ Tìm kiếm (SEO) và SEM',
                    'description' => 'Giúp website của bạn xuất hiện cao hơn trên Google và các công cụ tìm kiếm khác.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Nghiên cứu từ khóa và phân tích đối thủ', 'content' => 'Cách tìm từ khóa tiềm năng và theo dõi đối thủ.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: SEO On-page: Tối ưu nội dung và cấu trúc website', 'content' => 'Tối ưu tiêu đề, mô tả, thẻ heading, hình ảnh.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: SEO Off-page: Xây dựng liên kết và tín hiệu xã hội', 'content' => 'Các chiến lược xây dựng Backlinks chất lượng.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Google Search Console và Google Analytics cho SEO', 'content' => 'Sử dụng công cụ để theo dõi và cải thiện SEO.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Giới thiệu SEM (Search Engine Marketing) và Google Ads', 'content' => 'Tổng quan về quảng cáo tìm kiếm trả phí.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Quảng cáo trả phí (Google Ads và Facebook Ads)',
                    'description' => 'Thiết lập và quản lý các chiến dịch quảng cáo hiệu quả.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Google Ads: Các loại hình quảng cáo (Search, Display, Video, Shopping)', 'content' => 'Khám phá các định dạng quảng cáo trên Google.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Thiết lập và tối ưu chiến dịch Google Search Ads', 'content' => 'Chọn từ khóa, viết mẫu quảng cáo, đặt giá thầu.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Facebook Ads: Cấu trúc chiến dịch và mục tiêu quảng cáo', 'content' => 'Hiểu cách Facebook Ads hoạt động.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Nhắm mục tiêu đối tượng và tạo quảng cáo Facebook/Instagram', 'content' => 'Xác định audience, thiết kế creative hấp dẫn.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Đo lường và tối ưu hiệu quả quảng cáo (A/B Testing, Remarketing)', 'content' => 'Theo dõi KPIs và cải thiện chiến dịch.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Content Marketing và Social Media Marketing',
                    'description' => 'Tạo nội dung hấp dẫn và xây dựng cộng đồng trên mạng xã hội.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Xây dựng chiến lược Content Marketing', 'content' => 'Từ ý tưởng, sản xuất đến phân phối nội dung.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các loại hình Content phổ biến và cách tạo ra chúng', 'content' => 'Blog, video, infographic, ebook, podcast.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Social Media Marketing: Chọn kênh và xây dựng chiến lược', 'content' => 'Facebook, Instagram, LinkedIn, TikTok.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Quản lý cộng đồng và tương tác trên mạng xã hội', 'content' => 'Xây dựng mối quan hệ với khách hàng.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Social Media Analytics và đo lường hiệu quả', 'content' => 'Theo dõi các chỉ số trên mạng xã hội.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Email Marketing và Affiliate Marketing',
                    'description' => 'Sử dụng email và đối tác để tăng doanh số và xây dựng mối quan hệ.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Email Marketing: Xây dựng danh sách và thiết kế email', 'content' => 'Các bước để tạo chiến dịch email hiệu quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Các loại Email Marketing (Newsletter, Promotional, Transactional)', 'content' => 'Phân loại và ứng dụng từng loại email.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Tự động hóa Email Marketing và A/B Testing', 'content' => 'Sử dụng công cụ và tối ưu hóa email.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Giới thiệu Affiliate Marketing và cách hoạt động', 'content' => 'Tìm hiểu về mô hình tiếp thị liên kết.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Tìm kiếm và làm việc với Affiliate Partners', 'content' => 'Cách xây dựng mạng lưới đối tác.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Phân tích Dữ liệu và Báo cáo trong Digital Marketing',
                    'description' => 'Sử dụng dữ liệu để đưa ra quyết định thông minh và cải thiện hiệu suất.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Google Analytics 4 (GA4): Thiết lập và các báo cáo cơ bản', 'content' => 'Làm quen với GA4 và các chỉ số quan trọng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Theo dõi chuyển đổi (Conversion Tracking) và mục tiêu', 'content' => 'Thiết lập để đo lường hành động của người dùng.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Phân tích dữ liệu từ các kênh (Google Ads, Facebook Ads Reports)', 'content' => 'Đọc và hiểu các báo cáo từ nền tảng quảng cáo.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Các chỉ số KPIs quan trọng trong Digital Marketing', 'content' => 'CAC, LTV, ROI, CTR, Conversion Rate.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Xây dựng báo cáo hiệu suất Digital Marketing', 'content' => 'Trình bày kết quả và đề xuất cải tiến.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Quản trị Kinh doanh và Khởi nghiệp',
            'description' => 'Khóa học này cung cấp kiến thức toàn diện về quản trị kinh doanh, từ lập kế hoạch chiến lược, quản lý tài chính đến xây dựng đội ngũ và phát triển doanh nghiệp khởi nghiệp.',
            'price' => 700000.00,
            'categoryIds' => [34, 35], // Quản trị kinh doanh, Doanh nghiệp khởi nghiệp
            'requirements' => [
                'Mong muốn khởi nghiệp hoặc phát triển kỹ năng quản lý.',
                'Không yêu cầu kinh nghiệm kinh doanh trước.'
            ],
            'objectives' => [
                'Hiểu các nguyên tắc cơ bản của quản trị kinh doanh.',
                'Xây dựng kế hoạch kinh doanh và chiến lược phát triển.',
                'Quản lý tài chính, nhân sự và hoạt động vận hành.',
                'Nắm vững các bước để khởi nghiệp thành công.',
                'Phát triển tư duy lãnh đạo và giải quyết vấn đề trong kinh doanh.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tổng quan về Quản trị Kinh doanh',
                    'description' => 'Các khái niệm cơ bản và vai trò của quản trị trong doanh nghiệp.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Quản trị kinh doanh là gì? Các chức năng chính', 'content' => 'Lập kế hoạch, tổ chức, lãnh đạo, kiểm soát.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các loại hình doanh nghiệp và mô hình kinh doanh', 'content' => 'Doanh nghiệp tư nhân, công ty cổ phần, B2B, B2C.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Tầm quan trọng của tầm nhìn, sứ mệnh và giá trị cốt lõi', 'content' => 'Xây dựng nền tảng cho doanh nghiệp.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Phân tích môi trường kinh doanh (PESTEL, Porter\'s Five Forces)', 'content' => 'Đánh giá các yếu tố bên ngoài ảnh hưởng đến doanh nghiệp.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Lập kế hoạch chiến lược và Mô hình kinh doanh',
                    'description' => 'Xây dựng lộ trình phát triển cho doanh nghiệp.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Quy trình lập kế hoạch chiến lược (SWOT, OKRs)', 'content' => 'Phân tích điểm mạnh, yếu, cơ hội, thách thức và thiết lập mục tiêu.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Xây dựng Business Model Canvas', 'content' => 'Thiết kế mô hình kinh doanh trên một trang.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Chiến lược cạnh tranh (Cost Leadership, Differentiation, Focus)', 'content' => 'Các chiến lược để tạo lợi thế cạnh tranh.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Phân tích thị trường và đối thủ cạnh tranh', 'content' => 'Nghiên cứu thị trường và đánh giá đối thủ.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Định vị thương hiệu và Marketing Mix (4Ps)', 'content' => 'Xây dựng thương hiệu và chiến lược marketing.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Quản lý Tài chính cho Doanh nghiệp',
                    'description' => 'Nắm vững các nguyên tắc tài chính để đảm bảo sự bền vững.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các báo cáo tài chính cơ bản (Bảng cân đối kế toán, Báo cáo kết quả kinh doanh)', 'content' => 'Đọc và hiểu các báo cáo tài chính.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Quản lý dòng tiền (Cash Flow Management)', 'content' => 'Tầm quan trọng của dòng tiền và cách quản lý.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phân tích điểm hòa vốn (Break-even Analysis)', 'content' => 'Xác định điểm hòa vốn để lập kế hoạch lợi nhuận.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Nguồn vốn cho doanh nghiệp (Vốn tự có, Vay ngân hàng, Gọi vốn đầu tư)', 'content' => 'Các lựa chọn huy động vốn.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Đánh giá hiệu quả đầu tư (ROI, NPV, IRR)', 'content' => 'Các chỉ số để đánh giá dự án đầu tư.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Quản lý Nhân sự và Xây dựng Đội ngũ',
                    'description' => 'Tuyển dụng, phát triển và giữ chân nhân tài.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Quy trình tuyển dụng và phỏng vấn hiệu quả', 'content' => 'Các bước để tìm kiếm và chọn lọc ứng viên.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Đào tạo và phát triển nhân viên', 'content' => 'Các chương trình đào tạo để nâng cao năng lực.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Đánh giá hiệu suất và xây dựng hệ thống lương thưởng', 'content' => 'Động viên và giữ chân nhân tài.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Xây dựng văn hóa doanh nghiệp và gắn kết đội ngũ', 'content' => 'Tạo môi trường làm việc tích cực.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Pháp luật lao động cơ bản và các quy định', 'content' => 'Hiểu các quy định pháp lý về lao động.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Vận hành và Quản lý Chuỗi cung ứng',
                    'description' => 'Tối ưu hóa các hoạt động hàng ngày của doanh nghiệp.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Quản lý sản xuất và chất lượng', 'content' => 'Các phương pháp để đảm bảo chất lượng sản phẩm/dịch vụ.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Quản lý hàng tồn kho và logistics', 'content' => 'Tối ưu hóa chuỗi cung ứng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Quản lý quan hệ khách hàng (CRM)', 'content' => 'Xây dựng mối quan hệ bền vững với khách hàng.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tối ưu hóa quy trình kinh doanh', 'content' => 'Cải thiện hiệu quả hoạt động.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Ứng dụng công nghệ trong quản lý vận hành', 'content' => 'Sử dụng phần mềm ERP, CRM.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Khởi nghiệp: Từ ý tưởng đến thực tế',
                    'description' => 'Các bước cần thiết để biến ý tưởng kinh doanh thành một startup thành công.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Phát triển ý tưởng kinh doanh và xác định vấn đề', 'content' => 'Tìm kiếm ý tưởng và xác định thị trường ngách.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Nghiên cứu thị trường và xác thực ý tưởng (MVP)', 'content' => 'Kiểm tra tính khả thi của ý tưởng với sản phẩm tối thiểu khả thi.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Lập kế hoạch kinh doanh chi tiết cho Startup', 'content' => 'Xây dựng kế hoạch kinh doanh để gọi vốn và triển khai.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Huy động vốn cho Startup (Angel, Venture Capital, Crowdfunding)', 'content' => 'Các hình thức gọi vốn cho doanh nghiệp khởi nghiệp.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Xây dựng và phát triển đội ngũ sáng lập', 'content' => 'Tìm kiếm và hợp tác với những người đồng sáng lập.', 'sortOrder' => 5],
                        ['title' => 'Bài 6.6: Các thách thức và bài học từ Startup thất bại', 'content' => 'Học hỏi từ những sai lầm phổ biến.', 'sortOrder' => 6],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Tiếng Anh Giao Tiếp Nâng Cao cho Môi trường Quốc tế',
            'description' => 'Nâng cao khả năng giao tiếp tiếng Anh của bạn lên một tầm cao mới, tập trung vào các tình huống kinh doanh, thuyết trình và đàm phán trong môi trường quốc tế.',
            'price' => 600000.00,
            'categoryIds' => [85, 84], // Tiếng Anh, Ngoại ngữ
            'requirements' => [
                'Có kiến thức tiếng Anh trung cấp (B1-B2).',
                'Mong muốn cải thiện tiếng Anh trong môi trường chuyên nghiệp.'
            ],
            'objectives' => [
                'Tự tin tham gia các cuộc họp, thảo luận và thuyết trình bằng tiếng Anh.',
                'Nắm vững các cụm từ và thành ngữ tiếng Anh trong kinh doanh.',
                'Cải thiện kỹ năng đàm phán và giải quyết vấn đề bằng tiếng Anh.',
                'Viết báo cáo, email và tài liệu chuyên nghiệp bằng tiếng Anh.',
                'Phát triển khả năng nghe hiểu và nói lưu loát trong các tình huống phức tạp.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giao tiếp trong cuộc họp và Thảo luận',
                    'description' => 'Tham gia và dẫn dắt các cuộc họp bằng tiếng Anh một cách chuyên nghiệp.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Mở đầu và kết thúc cuộc họp', 'content' => 'Các cụm từ và cấu trúc để bắt đầu và kết thúc cuộc họp.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Đưa ra ý kiến, đồng ý và không đồng ý', 'content' => 'Cách thể hiện quan điểm một cách lịch sự và hiệu quả.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Ngắt lời, yêu cầu làm rõ và tóm tắt', 'content' => 'Các kỹ thuật để quản lý cuộc thảo luận.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Xử lý các tình huống khó khăn trong cuộc họp', 'content' => 'Giải quyết xung đột và giữ cho cuộc họp đi đúng hướng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Thuyết trình bằng tiếng Anh',
                    'description' => 'Xây dựng và trình bày các bài thuyết trình ấn tượng.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cấu trúc bài thuyết trình hiệu quả', 'content' => 'Mở đầu, nội dung chính, kết luận.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Ngôn ngữ và cụm từ dùng trong thuyết trình', 'content' => 'Các cụm từ để giới thiệu, chuyển ý, kết thúc.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Kỹ năng trình bày: Giọng điệu, ngôn ngữ cơ thể, tương tác', 'content' => 'Cách thu hút và giữ chân khán giả.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Xử lý câu hỏi và trả lời một cách tự tin', 'content' => 'Các chiến lược để trả lời câu hỏi khó.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Thiết kế slide thuyết trình chuyên nghiệp', 'content' => 'Sử dụng PowerPoint/Google Slides hiệu quả.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Đàm phán và Giải quyết vấn đề',
                    'description' => 'Phát triển kỹ năng đàm phán để đạt được kết quả mong muốn.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các giai đoạn của quá trình đàm phán', 'content' => 'Chuẩn bị, mở đầu, thăm dò, đề xuất, kết thúc.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Ngôn ngữ đàm phán: Đưa ra đề xuất, chấp nhận, từ chối', 'content' => 'Các cụm từ lịch sự và hiệu quả trong đàm phán.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Xử lý phản đối và tìm kiếm giải pháp', 'content' => 'Cách đối phó với sự phản đối và tìm kiếm điểm chung.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Đàm phán qua điện thoại và email', 'content' => 'Các kỹ thuật đàm phán trong các kênh khác nhau.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Viết email và báo cáo chuyên nghiệp',
                    'description' => 'Trau dồi kỹ năng viết để giao tiếp hiệu quả trong công việc.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Cấu trúc email công việc và các cụm từ trang trọng', 'content' => 'Viết email rõ ràng, súc tích và chuyên nghiệp.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Viết báo cáo ngắn gọn và memo', 'content' => 'Cách trình bày thông tin một cách có tổ chức.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Viết thư xin việc và CV bằng tiếng Anh', 'content' => 'Chuẩn bị tài liệu xin việc ấn tượng.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Các lỗi ngữ pháp và từ vựng thường gặp trong văn viết', 'content' => 'Tránh các lỗi phổ biến để viết đúng và hay.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Phát triển Từ vựng và Thành ngữ Kinh doanh',
                    'description' => 'Mở rộng vốn từ vựng chuyên ngành để giao tiếp tự tin hơn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Từ vựng về Tài chính và Kế toán', 'content' => 'Các thuật ngữ liên quan đến tài chính, ngân sách.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Từ vựng về Marketing và Bán hàng', 'content' => 'Các thuật ngữ trong lĩnh vực marketing, sales.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Thành ngữ (Idioms) và Cụm động từ (Phrasal Verbs) trong kinh doanh', 'content' => 'Sử dụng thành ngữ để giao tiếp tự nhiên hơn.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Luyện phát âm và ngữ điệu chuẩn', 'content' => 'Cải thiện cách phát âm để giao tiếp rõ ràng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Luyện nghe và Phản xạ nhanh',
                    'description' => 'Nâng cao khả năng nghe hiểu và phản ứng nhanh trong các tình huống giao tiếp.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Luyện nghe các bài nói chuyện, podcast kinh doanh', 'content' => 'Thực hành nghe các nội dung chuyên ngành.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Kỹ thuật ghi chú hiệu quả khi nghe', 'content' => 'Cách tóm tắt thông tin quan trọng.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Thực hành phản xạ nhanh trong hội thoại', 'content' => 'Các bài tập để trả lời nhanh và tự nhiên.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Thảo luận các chủ đề thời sự và kinh tế quốc tế', 'content' => 'Mở rộng kiến thức và luyện tập giao tiếp.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Trí tuệ Nhân tạo (AI) và Học máy (Machine Learning) cơ bản',
            'description' => 'Khóa học này cung cấp nền tảng vững chắc về Trí tuệ Nhân tạo và Học máy, từ các khái niệm cốt lõi đến các thuật toán cơ bản và ứng dụng thực tế.',
            'price' => 1250000.00,
            'categoryIds' => [45, 95, 41], // Trí tuệ nhân tạo (AI), Machine Learning, CNTT & Phần mềm
            'requirements' => [
                'Kiến thức cơ bản về lập trình (ưu tiên Python).',
                'Hiểu biết về đại số tuyến tính và xác suất thống kê cơ bản.',
                'Máy tính có kết nối internet và môi trường phát triển Python.'
            ],
            'objectives' => [
                'Hiểu các khái niệm cơ bản về AI và ML.',
                'Nắm vững các thuật toán học máy có giám sát và không giám sát.',
                'Sử dụng Python và các thư viện (NumPy, Pandas, Scikit-learn) để triển khai mô hình.',
                'Đánh giá hiệu suất của các mô hình học máy.',
                'Áp dụng kiến thức vào giải quyết các bài toán AI/ML đơn giản.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về AI và Machine Learning',
                    'description' => 'Tổng quan về lĩnh vực AI và ML, lịch sử và các ứng dụng.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: AI là gì? Lịch sử và các nhánh chính', 'content' => 'Định nghĩa AI, AI hẹp, AI tổng quát, siêu AI.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Machine Learning là gì? Phân loại ML', 'content' => 'Học có giám sát, không giám sát, học tăng cường.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Quy trình phát triển dự án ML', 'content' => 'Thu thập dữ liệu, tiền xử lý, huấn luyện, đánh giá, triển khai.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Công cụ và môi trường phát triển (Python, Jupyter)', 'content' => 'Cài đặt và làm quen với các công cụ cần thiết.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Tiền xử lý dữ liệu và Khám phá dữ liệu',
                    'description' => 'Chuẩn bị dữ liệu cho quá trình huấn luyện mô hình.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Làm sạch dữ liệu: Xử lý dữ liệu thiếu, nhiễu', 'content' => 'Các phương pháp điền dữ liệu, loại bỏ ngoại lai.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Chuyển đổi dữ liệu: Chuẩn hóa, mã hóa', 'content' => 'Scaling, One-Hot Encoding, Label Encoding.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Giảm chiều dữ liệu (PCA)', 'content' => 'Giảm số lượng thuộc tính để đơn giản hóa mô hình.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Khám phá dữ liệu (EDA) với Pandas và NumPy', 'content' => 'Phân tích thống kê mô tả, tương quan.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Trực quan hóa dữ liệu với Matplotlib và Seaborn', 'content' => 'Vẽ biểu đồ để hiểu rõ hơn về dữ liệu.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Học máy có giám sát: Hồi quy',
                    'description' => 'Xây dựng mô hình dự đoán giá trị liên tục.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Hồi quy tuyến tính đơn và đa biến', 'content' => 'Mô hình, phương pháp bình phương nhỏ nhất.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Đánh giá mô hình hồi quy (MSE, R-squared)', 'content' => 'Các chỉ số để đo lường độ chính xác của mô hình.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Hồi quy đa thức và Overfitting/Underfitting', 'content' => 'Mô hình phức tạp hơn và vấn đề khớp quá/khớp thiếu.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Ridge và Lasso Regression (Regularization)', 'content' => 'Kỹ thuật điều chuẩn để tránh overfitting.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Học máy có giám sát: Phân loại',
                    'description' => 'Xây dựng mô hình dự đoán nhãn phân loại.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Hồi quy Logistic cho bài toán phân loại nhị phân', 'content' => 'Mô hình, hàm sigmoid, ngưỡng phân loại.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Cây quyết định (Decision Trees)', 'content' => 'Cách cây quyết định phân chia dữ liệu.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Rừng ngẫu nhiên (Random Forest) và Boosting (Gradient Boosting)', 'content' => 'Các thuật toán ensemble để cải thiện hiệu suất.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Máy vector hỗ trợ (Support Vector Machines - SVM)', 'content' => 'Tìm siêu phẳng tối ưu để phân chia dữ liệu.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Đánh giá mô hình phân loại (Accuracy, Precision, Recall, F1-score)', 'content' => 'Ma trận nhầm lẫn, các chỉ số đánh giá.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Học máy không giám sát: Phân cụm và Giảm chiều',
                    'description' => 'Tìm kiếm cấu trúc ẩn trong dữ liệu không nhãn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Phân cụm K-Means', 'content' => 'Thuật toán K-Means, chọn số cụm K.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Phân cụm Hierarchical Clustering', 'content' => 'Phân cụm phân cấp, biểu đồ dendrogram.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Giới thiệu về Principal Component Analysis (PCA)', 'content' => 'Giảm chiều dữ liệu bằng cách tìm các thành phần chính.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Ứng dụng của học máy không giám sát', 'content' => 'Phân khúc khách hàng, phát hiện bất thường.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Giới thiệu về Mạng nơ-ron và Học sâu',
                    'description' => 'Bước đầu tiên vào thế giới của Học sâu.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Mạng nơ-ron nhân tạo (Artificial Neural Networks - ANN)', 'content' => 'Cấu trúc mạng nơ-ron, nơ-ron, lớp, trọng số.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Hàm kích hoạt (Activation Functions)', 'content' => 'ReLU, Sigmoid, Tanh và vai trò của chúng.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Huấn luyện mạng nơ-ron: Lan truyền ngược (Backpropagation)', 'content' => 'Cách mạng nơ-ron học từ dữ liệu.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Giới thiệu về TensorFlow/Keras', 'content' => 'Các thư viện phổ biến để xây dựng mạng nơ-ron.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Ứng dụng của Học sâu (Deep Learning)', 'content' => 'Nhận dạng hình ảnh, xử lý ngôn ngữ tự nhiên.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Thiết kế Web Responsive với HTML, CSS và JavaScript',
            'description' => 'Xây dựng các trang web hiện đại, đẹp mắt và có khả năng thích ứng trên mọi thiết bị, từ máy tính để bàn đến điện thoại di động, sử dụng HTML5, CSS3 và JavaScript.',
            'price' => 680000.00,
            'categoryIds' => [3, 4, 2], // HTML & CSS, JavaScript, Lập trình Web
            'requirements' => [
                'Máy tính có kết nối internet và trình duyệt web.',
                'Không yêu cầu kinh nghiệm lập trình web trước.'
            ],
            'objectives' => [
                'Nắm vững cấu trúc HTML5 và ý nghĩa của các thẻ.',
                'Sử dụng CSS3 để tạo kiểu và bố cục responsive.',
                'Thực hiện các tương tác động với JavaScript cơ bản.',
                'Thiết kế trang web thân thiện với thiết bị di động (mobile-first).',
                'Triển khai các dự án web nhỏ hoàn chỉnh.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: HTML5 - Cấu trúc và Nội dung Web',
                    'description' => 'Tìm hiểu về xương sống của mọi trang web.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: HTML là gì? Cấu trúc tài liệu HTML', 'content' => 'Khái niệm HTML, thẻ, thuộc tính, phần tử.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các thẻ HTML cơ bản (Headings, Paragraphs, Links, Images)', 'content' => 'Sử dụng các thẻ để tạo nội dung văn bản và đa phương tiện.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Danh sách (Lists) và Bảng (Tables)', 'content' => 'Tổ chức dữ liệu dạng danh sách và bảng.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Form và các loại Input', 'content' => 'Tạo các biểu mẫu để thu thập thông tin người dùng.', 'sortOrder' => 4],
                        ['title' => 'Bài 1.5: HTML Semantics (Header, Footer, Nav, Article, Section)', 'content' => 'Sử dụng các thẻ ngữ nghĩa để cấu trúc trang tốt hơn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 2: CSS3 - Tạo kiểu cho Web',
                    'description' => 'Biến trang web từ thô sơ thành đẹp mắt.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: CSS là gì? Cách nhúng CSS vào HTML', 'content' => 'Internal, External, Inline CSS.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Selectors (Element, Class, ID) và Specificity', 'content' => 'Chọn các phần tử để áp dụng kiểu.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Thuộc tính màu sắc, font chữ và kích thước', 'content' => 'Thay đổi màu nền, màu chữ, kiểu font, cỡ chữ.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Box Model (Margin, Border, Padding, Content)', 'content' => 'Hiểu về mô hình hộp của các phần tử HTML.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Display (block, inline, inline-block) và Position (static, relative, absolute, fixed)', 'content' => 'Kiểm soát cách hiển thị và vị trí của các phần tử.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: CSS Layout - Bố cục Responsive',
                    'description' => 'Thiết kế trang web thích ứng trên mọi kích thước màn hình.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Flexbox: Tạo bố cục linh hoạt', 'content' => 'Sử dụng Flexbox để sắp xếp các phần tử theo hàng/cột.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: CSS Grid: Tạo bố cục hai chiều', 'content' => 'Sử dụng Grid để tạo layout phức tạp hơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Media Queries: Thiết kế cho các thiết bị khác nhau', 'content' => 'Điều chỉnh kiểu dáng dựa trên kích thước màn hình.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Mobile-First Design Principle', 'content' => 'Thiết kế từ thiết bị di động trước rồi mở rộng lên desktop.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Đơn vị đo lường responsive (%, vw, vh, rem, em)', 'content' => 'Sử dụng các đơn vị tương đối để thiết kế linh hoạt.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: JavaScript Cơ bản - Thêm tương tác',
                    'description' => 'Làm cho trang web của bạn trở nên sống động hơn.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: JavaScript là gì? Cách nhúng JS vào HTML', 'content' => 'Sử dụng thẻ script, vị trí đặt script.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Biến, kiểu dữ liệu và toán tử', 'content' => 'Khai báo biến, các loại dữ liệu cơ bản, phép toán.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Cấu trúc điều khiển (if/else, switch) và Vòng lặp (for, while)', 'content' => 'Điều khiển luồng chương trình.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Hàm (Functions) và Phạm vi biến (Scope)', 'content' => 'Tạo và sử dụng hàm, hiểu về phạm vi của biến.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: DOM Manipulation: Tương tác với HTML/CSS bằng JS', 'content' => 'Thay đổi nội dung, kiểu dáng, thuộc tính của phần tử.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Xử lý Sự kiện và Tương tác Người dùng',
                    'description' => 'Phản ứng với hành động của người dùng trên trang web.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Xử lý sự kiện (Event Handling): click, mouseover, keydown', 'content' => 'Gắn sự kiện vào các phần tử HTML.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Form Events và Validation', 'content' => 'Kiểm tra tính hợp lệ của dữ liệu nhập vào form.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Thao tác với Class và Style bằng JavaScript', 'content' => 'Thêm/xóa class, thay đổi style trực tiếp.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tạo hiệu ứng Toggle (hiện/ẩn) và Carousel đơn giản', 'content' => 'Xây dựng các thành phần tương tác phổ biến.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Dự án thực hành: Xây dựng Website Responsive',
                    'description' => 'Áp dụng tất cả kiến thức đã học để xây dựng một dự án hoàn chỉnh.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Lập kế hoạch dự án và thiết kế Wireframe', 'content' => 'Phác thảo cấu trúc và bố cục trang web.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Xây dựng cấu trúc HTML cho dự án', 'content' => 'Viết mã HTML cho các trang chính.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Áp dụng CSS để tạo kiểu và bố cục responsive', 'content' => 'Sử dụng Flexbox, Grid, Media Queries.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Thêm tương tác JavaScript cho dự án', 'content' => 'Tạo menu responsive, gallery ảnh.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Tối ưu hóa hình ảnh và hiệu suất tải trang', 'content' => 'Nén ảnh, lazy loading.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Quản lý Thời gian và Năng suất Cá nhân',
            'description' => 'Nâng cao hiệu suất làm việc và cân bằng cuộc sống bằng cách học các kỹ thuật quản lý thời gian, thiết lập mục tiêu và tối ưu hóa năng suất cá nhân.',
            'price' => 480000.00,
            'categoryIds' => [66, 103, 63], // Quản lý thời gian, Năng suất & Tổ chức công việc, Phát triển cá nhân
            'requirements' => [
                'Mong muốn cải thiện kỹ năng quản lý thời gian và năng suất.',
                'Sự sẵn lòng thử nghiệm các phương pháp mới.'
            ],
            'objectives' => [
                'Xác định và ưu tiên các mục tiêu cá nhân và công việc.',
                'Áp dụng các kỹ thuật quản lý thời gian hiệu quả (Pomodoro, Ma trận Eisenhower).',
                'Giảm thiểu sự trì hoãn và tăng cường sự tập trung.',
                'Xây dựng thói quen tốt và tối ưu hóa quy trình làm việc hàng ngày.',
                'Cân bằng giữa công việc, cuộc sống và thời gian nghỉ ngơi.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Hiểu về Thời gian và Năng suất',
                    'description' => 'Khám phá cách chúng ta sử dụng thời gian và những yếu tố ảnh hưởng đến năng suất.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Thời gian là tài nguyên quý giá nhất', 'content' => 'Tầm quan trọng của việc quản lý thời gian hiệu quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các lầm tưởng về năng suất và đa nhiệm', 'content' => 'Phá bỏ những quan niệm sai lầm về làm việc hiệu quả.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Xác định "Kẻ cắp thời gian" của bạn', 'content' => 'Nhận diện những yếu tố gây lãng phí thời gian cá nhân.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Mục tiêu SMART: Thiết lập mục tiêu rõ ràng và khả thi', 'content' => 'Specific, Measurable, Achievable, Relevant, Time-bound.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các Kỹ thuật Quản lý Thời gian Phổ biến',
                    'description' => 'Học các phương pháp đã được chứng minh để tối ưu hóa lịch trình.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Phương pháp Pomodoro: Tập trung sâu và nghỉ ngơi hợp lý', 'content' => 'Làm việc 25 phút, nghỉ 5 phút.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Ma trận Eisenhower: Ưu tiên công việc theo cấp độ', 'content' => 'Quan trọng/Khẩn cấp, Quan trọng/Không khẩn cấp...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Quy tắc 2 phút và Batching Tasks', 'content' => 'Hoàn thành ngay các việc nhỏ và nhóm các công việc tương tự.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Lập kế hoạch hàng ngày/tuần với To-do List hiệu quả', 'content' => 'Cách tạo danh sách việc cần làm có thể thực hiện được.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Time Blocking: Lên lịch cụ thể cho từng hoạt động', 'content' => 'Phân bổ thời gian cố định cho các nhiệm vụ.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Tăng cường Sự tập trung và Giảm Trì hoãn',
                    'description' => 'Loại bỏ phiền nhiễu và xây dựng thói quen tập trung.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Môi trường làm việc tối ưu: Sắp xếp và loại bỏ phiền nhiễu', 'content' => 'Tạo không gian làm việc hiệu quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Kỹ thuật Deep Work: Tập trung cao độ vào một nhiệm vụ', 'content' => 'Làm việc không bị gián đoạn.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Đối phó với sự trì hoãn: Nguyên nhân và giải pháp', 'content' => 'Hiểu tâm lý trì hoãn và cách vượt qua.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Quản lý thông báo và sử dụng công nghệ một cách thông minh', 'content' => 'Kiểm soát điện thoại, email, mạng xã hội.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Xây dựng Thói quen và Tối ưu hóa Quy trình',
                    'description' => 'Tạo dựng những thói quen tích cực để duy trì năng suất cao.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Sức mạnh của thói quen: Vòng lặp thói quen (Cue-Routine-Reward)', 'content' => 'Cách hình thành và duy trì thói quen tốt.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Morning Routine và Evening Routine hiệu quả', 'content' => 'Thiết lập lịch trình buổi sáng và tối để khởi đầu/kết thúc ngày tốt.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Tối ưu hóa quy trình làm việc và tự động hóa', 'content' => 'Tìm kiếm các cách để làm việc thông minh hơn, không phải chăm chỉ hơn.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Sử dụng công cụ và ứng dụng hỗ trợ năng suất (Notion, Trello, Calendar)', 'content' => 'Khám phá các công cụ giúp bạn tổ chức công việc.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Cân bằng Cuộc sống và Tránh Kiệt sức',
                    'description' => 'Duy trì sự cân bằng để có năng lượng và động lực lâu dài.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tầm quan trọng của nghỉ ngơi và phục hồi', 'content' => 'Ngủ đủ giấc, thư giãn, giải trí.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Thiết lập ranh giới giữa công việc và cuộc sống cá nhân', 'content' => 'Tránh làm việc quá sức và burnout.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Quản lý căng thẳng và thực hành Mindfulness', 'content' => 'Các kỹ thuật giảm stress và sống chánh niệm.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đánh giá và điều chỉnh chiến lược năng suất của bạn', 'content' => 'Liên tục cải thiện và thích nghi.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Áp dụng vào Thực tế và Duy trì',
                    'description' => 'Biến lý thuyết thành hành động và duy trì sự tiến bộ.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Vượt qua các rào cản và duy trì động lực', 'content' => 'Các chiến lược để không bỏ cuộc.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Tìm kiếm sự hỗ trợ và cộng đồng', 'content' => 'Làm việc cùng người khác để đạt mục tiêu.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Đánh giá và điều chỉnh định kỳ', 'content' => 'Kiểm tra lại phương pháp và điều chỉnh khi cần.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Hành trình phát triển năng suất cá nhân không ngừng', 'content' => 'Liên tục học hỏi và cải thiện bản thân.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Sản xuất Âm nhạc với FL Studio Từ A đến Z',
            'description' => 'Học cách tạo ra các bản nhạc chuyên nghiệp từ đầu đến cuối bằng phần mềm FL Studio. Khóa học này bao gồm từ lý thuyết âm nhạc cơ bản đến các kỹ thuật hòa âm, phối khí và mixing/mastering.',
            'price' => 820000.00,
            'categoryIds' => [71, 69], // Sản xuất âm nhạc, Âm nhạc
            'requirements' => [
                'Máy tính cài đặt FL Studio (phiên bản 20 trở lên).',
                'Tai nghe hoặc loa chất lượng tốt.',
                'Không yêu cầu kiến thức âm nhạc trước (nhưng là một lợi thế).'
            ],
            'objectives' => [
                'Làm chủ giao diện và các công cụ chính của FL Studio.',
                'Nắm vững các nguyên tắc lý thuyết âm nhạc cơ bản (nhịp điệu, giai điệu, hợp âm).',
                'Tạo các bản beat, melody và bassline hấp dẫn.',
                'Hòa âm và phối khí các nhạc cụ ảo và mẫu âm thanh.',
                'Thực hiện các kỹ thuật mixing và mastering cơ bản để hoàn thiện bản nhạc.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu FL Studio và Thiết lập Cơ bản',
                    'description' => 'Làm quen với môi trường làm việc của FL Studio.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: FL Studio là gì? Tổng quan và các tính năng', 'content' => 'Giới thiệu về Digital Audio Workstation (DAW) và FL Studio.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt và cấu hình Audio Settings', 'content' => 'Thiết lập ASIO driver để có độ trễ thấp.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giao diện FL Studio: Browser, Channel Rack, Mixer, Playlist', 'content' => 'Làm quen với các cửa sổ chính và chức năng.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Quản lý Project và Lưu/Xuất file', 'content' => 'Cách lưu dự án và xuất bản nhạc thành MP3/WAV.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Lý thuyết Âm nhạc Cơ bản cho Producer',
                    'description' => 'Nền tảng âm nhạc để sáng tạo các giai điệu và hòa âm.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Nhịp điệu, Phách và Tempo', 'content' => 'Hiểu về nhịp độ và cấu trúc nhịp điệu.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Giai điệu và Scales (Gam)', 'content' => 'Cách tạo giai điệu hấp dẫn và các gam cơ bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Hợp âm (Chords): Major, Minor và các loại khác', 'content' => 'Cấu tạo hợp âm và cách sử dụng chúng.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Vòng hòa âm (Chord Progressions) cơ bản', 'content' => 'Cách các hợp âm kết nối với nhau để tạo ra cảm xúc.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: MIDI và Piano Roll trong FL Studio', 'content' => 'Nhập và chỉnh sửa nốt nhạc bằng Piano Roll.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Tạo Beat và Drum Programming',
                    'description' => 'Xây dựng nền tảng nhịp điệu cho bản nhạc của bạn.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các loại nhạc cụ trống (Kick, Snare, Hi-hat, Percussion)', 'content' => 'Tìm hiểu âm thanh và vai trò của từng loại trống.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Sử dụng Channel Rack và Step Sequencer', 'content' => 'Tạo các mẫu trống đơn giản.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Lập trình Beat cho các thể loại (Hip-hop, EDM, Pop)', 'content' => 'Các mẫu beat phổ biến cho từng thể loại.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Thêm sự biến đổi và groove cho Beat', 'content' => 'Sử dụng velocity, swing, quantization.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Sáng tạo Giai điệu và Hòa âm',
                    'description' => 'Phát triển các phần melody và đệm cho bản nhạc.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Tạo Melody với Piano Roll và nhạc cụ ảo (VSTi)', 'content' => 'Sử dụng các plugin nhạc cụ của FL Studio.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Xây dựng Bassline hấp dẫn', 'content' => 'Các kỹ thuật tạo bassline phù hợp với thể loại.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Sử dụng các mẫu âm thanh (Samples) và Loop', 'content' => 'Nhập và chỉnh sửa các mẫu âm thanh.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Automation Clips: Tạo sự thay đổi động cho âm thanh', 'content' => 'Tự động hóa các thông số như âm lượng, pan, filter.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Sắp xếp các phần nhạc trong Playlist', 'content' => 'Xây dựng cấu trúc bài hát (Intro, Verse, Chorus, Bridge).', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Mixing (Phối trộn) cơ bản',
                    'description' => 'Cân bằng âm lượng và tần số của các nhạc cụ.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Giới thiệu Mixer trong FL Studio', 'content' => 'Các kênh mixer, fader, pan, effect slots.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Cân bằng âm lượng (Gain Staging) và Pan', 'content' => 'Điều chỉnh âm lượng và vị trí âm thanh trong không gian stereo.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Equalization (EQ): Cắt/tăng tần số', 'content' => 'Sử dụng EQ để làm rõ âm thanh và tránh xung đột tần số.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Compression: Kiểm soát dải động', 'content' => 'Sử dụng Compressor để làm cho âm thanh chặt chẽ hơn.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Reverb và Delay: Tạo không gian và chiều sâu', 'content' => 'Thêm hiệu ứng không gian cho nhạc cụ.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Mastering (Hoàn thiện) và Xuất bản',
                    'description' => 'Làm cho bản nhạc của bạn sẵn sàng để phát hành.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Mastering là gì? Mục tiêu của Mastering', 'content' => 'Tối ưu hóa âm lượng và chất lượng âm thanh tổng thể.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Sử dụng Limiter và Maximizer', 'content' => 'Tăng âm lượng mà không bị méo tiếng.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Kiểm tra và sửa lỗi cuối cùng', 'content' => 'Nghe lại bản nhạc trên các hệ thống khác nhau.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xuất bản nhạc dưới các định dạng (MP3, WAV) và chia sẻ', 'content' => 'Chuẩn bị file cho các nền tảng streaming.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Bản quyền và phân phối nhạc trực tuyến', 'content' => 'Các bước để đưa nhạc của bạn đến khán giả.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Dinh dưỡng và Lối sống Khỏe mạnh Toàn diện',
            'description' => 'Khám phá các nguyên tắc dinh dưỡng khoa học, xây dựng thói quen ăn uống lành mạnh và áp dụng lối sống tích cực để cải thiện sức khỏe tổng thể và chất lượng cuộc sống.',
            'price' => 520000.00,
            'categoryIds' => [76, 77, 73], // Dinh dưỡng, Tập luyện thể hình, Sức khỏe & Thể hình
            'requirements' => [
                'Mong muốn cải thiện sức khỏe và lối sống.',
                'Sự sẵn lòng thay đổi thói quen ăn uống và sinh hoạt.'
            ],
            'objectives' => [
                'Hiểu về các nhóm chất dinh dưỡng đa lượng và vi lượng.',
                'Xây dựng kế hoạch ăn uống cân bằng và phù hợp với mục tiêu cá nhân.',
                'Phân biệt thực phẩm lành mạnh và thực phẩm chế biến.',
                'Áp dụng các thói quen sống tích cực (vận động, ngủ, quản lý stress).',
                'Duy trì lối sống khỏe mạnh bền vững.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Nền tảng Dinh dưỡng Cơ bản',
                    'description' => 'Tìm hiểu về các chất dinh dưỡng thiết yếu và vai trò của chúng.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Dinh dưỡng là gì? Tầm quan trọng của dinh dưỡng hợp lý', 'content' => 'Định nghĩa dinh dưỡng và ảnh hưởng đến sức khỏe.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các chất dinh dưỡng đa lượng (Macronutrients): Carbohydrate, Protein, Fat', 'content' => 'Vai trò, nguồn cung cấp và nhu cầu của từng chất.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các chất dinh dưỡng vi lượng (Micronutrients): Vitamin và Khoáng chất', 'content' => 'Tầm quan trọng của vitamin và khoáng chất, nguồn cung cấp.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Nước và chất xơ: Hai thành phần thiết yếu', 'content' => 'Vai trò của nước và chất xơ trong cơ thể.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Xây dựng Chế độ Ăn uống Lành mạnh',
                    'description' => 'Lên kế hoạch bữa ăn và lựa chọn thực phẩm thông minh.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tháp dinh dưỡng và Nguyên tắc ăn uống cân bằng', 'content' => 'Hiểu về tỷ lệ các nhóm thực phẩm cần thiết.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Đọc nhãn thực phẩm và phân biệt thực phẩm chế biến', 'content' => 'Cách đọc thông tin dinh dưỡng và nhận diện thực phẩm không lành mạnh.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Lên kế hoạch bữa ăn hàng ngày/tuần', 'content' => 'Cách chuẩn bị bữa ăn đầy đủ dinh dưỡng và tiết kiệm thời gian.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Dinh dưỡng cho các mục tiêu khác nhau (Giảm cân, Tăng cân, Duy trì)', 'content' => 'Điều chỉnh chế độ ăn phù hợp với mục tiêu cá nhân.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Thực phẩm hữu cơ, GMO và các xu hướng ăn uống', 'content' => 'Tìm hiểu về các khái niệm và xu hướng ăn uống hiện đại.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Quản lý Cân nặng và Chế độ ăn kiêng phổ biến',
                    'description' => 'Hiểu về quản lý cân nặng và các phương pháp ăn kiêng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Calo in - Calo out: Nguyên tắc cơ bản của quản lý cân nặng', 'content' => 'Hiểu về cân bằng năng lượng trong cơ thể.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Các chế độ ăn kiêng phổ biến (Keto, Low-carb, Intermittent Fasting)', 'content' => 'Tìm hiểu về các chế độ ăn kiêng và ưu nhược điểm.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Ăn uống theo trực giác (Intuitive Eating) và mối quan hệ với thực phẩm', 'content' => 'Phát triển mối quan hệ lành mạnh với thức ăn.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Vai trò của protein trong việc giảm cân và duy trì cơ bắp', 'content' => 'Tầm quan trọng của protein trong chế độ ăn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Vận động và Tập luyện Thể chất',
                    'description' => 'Tích hợp hoạt động thể chất vào lối sống hàng ngày.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Lợi ích của vận động thường xuyên đối với sức khỏe', 'content' => 'Tăng cường sức khỏe tim mạch, cơ bắp, tinh thần.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các loại hình vận động (Cardio, Sức mạnh, Linh hoạt)', 'content' => 'Phân biệt các loại hình tập luyện và lợi ích.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Xây dựng lịch trình tập luyện phù hợp với bản thân', 'content' => 'Thiết lập mục tiêu và kế hoạch tập luyện.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Luyện tập tại nhà và các bài tập cơ bản không cần dụng cụ', 'content' => 'Các bài tập dễ thực hiện tại nhà.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Phòng ngừa chấn thương và lắng nghe cơ thể', 'content' => 'Tập luyện an toàn và hiệu quả.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Sức khỏe Tinh thần và Quản lý Stress',
                    'description' => 'Chăm sóc sức khỏe tinh thần để có cuộc sống cân bằng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Mối liên hệ giữa dinh dưỡng, vận động và sức khỏe tinh thần', 'content' => 'Tác động của lối sống đến tâm trạng.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Nhận diện và quản lý stress hiệu quả', 'content' => 'Các kỹ thuật thư giãn, hít thở, thiền định.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Giấc ngủ: Tầm quan trọng và cách cải thiện chất lượng giấc ngủ', 'content' => 'Mẹo để có giấc ngủ sâu và đủ.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Xây dựng các mối quan hệ xã hội lành mạnh', 'content' => 'Tầm quan trọng của kết nối xã hội.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Duy trì Lối sống Khỏe mạnh Bền vững',
                    'description' => 'Biến các kiến thức thành thói quen lâu dài.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Vượt qua các rào cản và duy trì động lực', 'content' => 'Đối phó với những thách thức trong hành trình khỏe mạnh.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Lập kế hoạch cho những lúc "ăn gian" và sự kiện xã hội', 'content' => 'Cách ăn uống linh hoạt mà vẫn giữ mục tiêu.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Đánh giá tiến độ và điều chỉnh khi cần thiết', 'content' => 'Theo dõi sự thay đổi và điều chỉnh kế hoạch.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Phát triển tư duy tích cực và lòng biết ơn', 'content' => 'Thái độ sống ảnh hưởng đến sức khỏe.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học An toàn Thông tin và Bảo mật Mạng cho Người dùng',
            'description' => 'Trang bị kiến thức và kỹ năng cần thiết để bảo vệ thông tin cá nhân, thiết bị và dữ liệu của bạn khỏi các mối đe dọa trực tuyến và tấn công mạng.',
            'price' => 550000.00,
            'categoryIds' => [42, 97], // Mạng máy tính & Bảo mật, Cybersecurity Chuyên sâu
            'requirements' => [
                'Máy tính có kết nối internet.',
                'Không yêu cầu kiến thức chuyên sâu về công nghệ thông tin.'
            ],
            'objectives' => [
                'Hiểu các loại mối đe dọa an ninh mạng phổ biến.',
                'Bảo vệ tài khoản trực tuyến và thông tin cá nhân.',
                'Sử dụng internet và email một cách an toàn.',
                'Bảo mật thiết bị di động và máy tính cá nhân.',
                'Nhận biết và phòng tránh các cuộc tấn công lừa đảo (phishing, scam).'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về An toàn Thông tin và Các mối đe dọa',
                    'description' => 'Tổng quan về an ninh mạng và những rủi ro tiềm ẩn.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: An toàn thông tin là gì? Tại sao nó quan trọng?', 'content' => 'Định nghĩa, tầm quan trọng trong thời đại số.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các loại mối đe dọa an ninh mạng phổ biến', 'content' => 'Virus, Malware, Ransomware, Phishing, DDoS.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Hậu quả của việc mất an toàn thông tin', 'content' => 'Mất dữ liệu, mất tiền, lộ thông tin cá nhân.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Nguyên tắc cơ bản của bảo mật (Confidentiality, Integrity, Availability)', 'content' => 'Hiểu các trụ cột của an ninh thông tin.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Bảo vệ Tài khoản Trực tuyến và Thông tin Cá nhân',
                    'description' => 'Các biện pháp để giữ an toàn cho tài khoản và dữ liệu của bạn.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tạo mật khẩu mạnh và quản lý mật khẩu hiệu quả', 'content' => 'Sử dụng mật khẩu dài, phức tạp, không trùng lặp.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Xác thực hai yếu tố (2FA/MFA) và tầm quan trọng', 'content' => 'Thêm lớp bảo mật cho tài khoản của bạn.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Bảo mật tài khoản mạng xã hội và email', 'content' => 'Các cài đặt bảo mật trên Facebook, Gmail, Zalo.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Quản lý quyền riêng tư trên internet', 'content' => 'Kiểm soát thông tin cá nhân trên các trang web.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Xóa dữ liệu cá nhân khỏi các dịch vụ không còn sử dụng', 'content' => 'Giảm thiểu dấu vết trực tuyến.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Lướt Web và Sử dụng Email An toàn',
                    'description' => 'Nhận biết các dấu hiệu lừa đảo và tránh các trang web độc hại.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Nhận diện trang web giả mạo và liên kết độc hại', 'content' => 'Kiểm tra URL, chứng chỉ SSL.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tấn công Phishing và Spear Phishing', 'content' => 'Các loại lừa đảo qua email và cách phòng tránh.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Tải xuống file an toàn và kiểm tra virus', 'content' => 'Sử dụng phần mềm diệt virus, quét file trước khi mở.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Sử dụng Wi-Fi công cộng an toàn (VPN)', 'content' => 'Bảo vệ dữ liệu khi dùng mạng Wi-Fi không bảo mật.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Cảnh giác với các quảng cáo và pop-up đáng ngờ', 'content' => 'Tránh click vào các nội dung không an toàn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Bảo mật Thiết bị Cá nhân (Máy tính và Điện thoại)',
                    'description' => 'Các biện pháp để bảo vệ thiết bị của bạn khỏi phần mềm độc hại.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Cập nhật phần mềm và hệ điều hành thường xuyên', 'content' => 'Tầm quan trọng của việc vá lỗi bảo mật.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Sử dụng phần mềm diệt virus và tường lửa', 'content' => 'Bảo vệ máy tính khỏi các mối đe dọa.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Mã hóa dữ liệu trên thiết bị', 'content' => 'Bảo vệ thông tin ngay cả khi thiết bị bị mất.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Sao lưu dữ liệu thường xuyên', 'content' => 'Phòng tránh mất dữ liệu do lỗi hệ thống hoặc tấn công.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Bảo mật điện thoại thông minh (khóa màn hình, quản lý ứng dụng)', 'content' => 'Các cài đặt bảo mật cho điện thoại di động.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Các mối đe dọa nâng cao và Cách phòng tránh',
                    'description' => 'Tìm hiểu về các hình thức tấn công phức tạp hơn và cách đối phó.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tấn công Ransomware và cách phục hồi', 'content' => 'Mã độc tống tiền và các biện pháp đối phó.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Social Engineering (Kỹ thuật xã hội)', 'content' => 'Thao túng tâm lý để lấy thông tin.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Giới thiệu về Dark Web và Deep Web', 'content' => 'Hiểu về các phần ẩn của internet.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Bảo mật khi mua sắm và giao dịch trực tuyến', 'content' => 'Sử dụng cổng thanh toán an toàn, kiểm tra độ tin cậy.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Báo cáo các sự cố an ninh mạng', 'content' => 'Khi nào và cách báo cáo khi gặp sự cố.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Xây dựng Thói quen Bảo mật Cá nhân',
                    'description' => 'Áp dụng các nguyên tắc bảo mật vào cuộc sống hàng ngày.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Tư duy bảo mật: Luôn cảnh giác và đặt câu hỏi', 'content' => 'Phát triển tư duy chủ động trong bảo mật.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Kiểm tra định kỳ các cài đặt bảo mật', 'content' => 'Thường xuyên rà soát các thiết lập an toàn.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Học hỏi và cập nhật kiến thức về an ninh mạng', 'content' => 'Theo dõi các tin tức và xu hướng mới.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Chia sẻ kiến thức bảo mật với gia đình và bạn bè', 'content' => 'Giúp những người xung quanh an toàn hơn.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Phát triển Ứng dụng Web với NodeJS và ExpressJS',
            'description' => 'Học cách xây dựng các ứng dụng web mạnh mẽ và có khả năng mở rộng bằng NodeJS, ExpressJS và MongoDB. Khóa học này sẽ đưa bạn từ cơ bản đến nâng cao, bao gồm cả việc xây dựng API RESTful.',
            'price' => 880000.00,
            'categoryIds' => [12, 2], // NodeJS, Lập trình Web
            'requirements' => [
                'Kiến thức cơ bản về JavaScript (ES6+).',
                'Hiểu biết về HTML và CSS cơ bản.',
                'Máy tính có kết nối internet và Node.js đã cài đặt.'
            ],
            'objectives' => [
                'Nắm vững các khái niệm cốt lõi của NodeJS và cách hoạt động của nó.',
                'Sử dụng ExpressJS để xây dựng các ứng dụng web và API RESTful.',
                'Làm việc với cơ sở dữ liệu MongoDB và Mongoose.',
                'Thực hiện xác thực người dùng (Authentication) và ủy quyền (Authorization).',
                'Triển khai ứng dụng NodeJS lên môi trường production.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu NodeJS và Môi trường Phát triển',
                    'description' => 'Tổng quan về NodeJS và cách thiết lập môi trường để bắt đầu.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: NodeJS là gì? Tại sao nên dùng NodeJS?', 'content' => 'Khái niệm NodeJS, kiến trúc bất đồng bộ, non-blocking I/O.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt Node.js và npm (Node Package Manager)', 'content' => 'Hướng dẫn cài đặt trên các hệ điều hành.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Module System (CommonJS và ES Modules)', 'content' => 'Cách tổ chức mã nguồn với các module.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Asynchronous JavaScript (Callbacks, Promises, Async/Await)', 'content' => 'Xử lý các tác vụ bất đồng bộ trong NodeJS.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: ExpressJS - Xây dựng Ứng dụng Web',
                    'description' => 'Làm quen với framework ExpressJS để xây dựng ứng dụng web.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: ExpressJS là gì? Cài đặt và Project Setup', 'content' => 'Giới thiệu ExpressJS, tạo dự án Express đầu tiên.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Routing: Xử lý các yêu cầu HTTP', 'content' => 'Định tuyến các URL đến các hàm xử lý.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Middleware: Xử lý yêu cầu trước khi đến Route', 'content' => 'Sử dụng middleware cho logging, authentication.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Template Engines (EJS, Pug, Handlebars)', 'content' => 'Render các trang HTML động.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Xử lý Form Data (body-parser)', 'content' => 'Thu thập dữ liệu từ các biểu mẫu HTML.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Cơ sở dữ liệu MongoDB và Mongoose',
                    'description' => 'Lưu trữ và truy xuất dữ liệu với MongoDB và ODM Mongoose.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Giới thiệu MongoDB: Cơ sở dữ liệu NoSQL', 'content' => 'Khái niệm MongoDB, ưu điểm so với SQL.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Cài đặt MongoDB và MongoDB Compass', 'content' => 'Hướng dẫn cài đặt và công cụ quản lý.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Mongoose: ODM cho MongoDB', 'content' => 'Sử dụng Mongoose để tương tác với MongoDB trong NodeJS.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Tạo Schema và Model với Mongoose', 'content' => 'Định nghĩa cấu trúc dữ liệu.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Các thao tác CRUD (Create, Read, Update, Delete) với Mongoose', 'content' => 'Thực hiện các hoạt động cơ bản trên dữ liệu.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Xây dựng API RESTful với ExpressJS',
                    'description' => 'Thiết kế và triển khai các API để ứng dụng frontend giao tiếp.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Khái niệm API RESTful và HTTP Methods', 'content' => 'Hiểu về kiến trúc REST và các phương thức HTTP.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Thiết kế Endpoint cho API', 'content' => 'Cách đặt tên và cấu trúc các URL API.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Xây dựng API GET, POST, PUT, DELETE', 'content' => 'Triển khai các route cho từng thao tác CRUD.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Xử lý lỗi và trả về JSON Response', 'content' => 'Cách xử lý lỗi và định dạng phản hồi API.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Xác thực và Ủy quyền (Authentication & Authorization)',
                    'description' => 'Bảo mật ứng dụng và API của bạn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Authentication là gì? Các phương pháp Auth', 'content' => 'Đăng ký, đăng nhập, session, JWT.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Mã hóa mật khẩu với bcryptjs', 'content' => 'Bảo vệ mật khẩu người dùng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: JSON Web Tokens (JWT) cho Authentication', 'content' => 'Sử dụng JWT để xác thực người dùng không trạng thái.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Authorization: Phân quyền người dùng', 'content' => 'Kiểm soát quyền truy cập dựa trên vai trò.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Middleware bảo vệ Route', 'content' => 'Sử dụng middleware để kiểm tra JWT và quyền.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Triển khai và Tối ưu hóa',
                    'description' => 'Đưa ứng dụng của bạn lên môi trường production và cải thiện hiệu suất.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Environment Variables và Configuration', 'content' => 'Quản lý các biến môi trường cho ứng dụng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Triển khai ứng dụng NodeJS lên Heroku/Vercel', 'content' => 'Các bước để deploy ứng dụng lên cloud.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Logging và Monitoring', 'content' => 'Theo dõi hoạt động và lỗi của ứng dụng.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Tối ưu hiệu suất ứng dụng NodeJS', 'content' => 'Các kỹ thuật để cải thiện tốc độ và khả năng mở rộng.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Giới thiệu Docker cho NodeJS', 'content' => 'Đóng gói ứng dụng trong container.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Phân tích Tài chính và Kế toán cho Người không chuyên',
            'description' => 'Nắm vững các nguyên tắc cơ bản về tài chính và kế toán, cách đọc hiểu báo cáo tài chính và sử dụng chúng để đưa ra các quyết định kinh doanh thông minh.',
            'price' => 620000.00,
            'categoryIds' => [38, 33], // Tài chính & Kế toán, Kinh doanh
            'requirements' => [
                'Không yêu cầu kiến thức nền tảng về tài chính hay kế toán.',
                'Sự quan tâm đến việc hiểu các con số kinh doanh.'
            ],
            'objectives' => [
                'Hiểu các khái niệm cơ bản về tài chính và kế toán.',
                'Đọc và phân tích Bảng cân đối kế toán, Báo cáo kết quả kinh doanh và Báo cáo lưu chuyển tiền tệ.',
                'Tính toán và giải thích các chỉ số tài chính quan trọng.',
                'Đánh giá sức khỏe tài chính của một doanh nghiệp.',
                'Áp dụng kiến thức tài chính vào các quyết định cá nhân và kinh doanh.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Tài chính và Kế toán',
                    'description' => 'Khám phá vai trò của tài chính và kế toán trong kinh doanh và đời sống.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Tài chính là gì? Kế toán là gì? Mối quan hệ', 'content' => 'Định nghĩa, mục đích và sự khác biệt giữa hai lĩnh vực.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các nguyên tắc kế toán cơ bản (GAAP, IFRS)', 'content' => 'Hiểu về các chuẩn mực kế toán quốc tế.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Đối tượng sử dụng thông tin kế toán và tài chính', 'content' => 'Nhà đầu tư, quản lý, ngân hàng, chính phủ.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tầm quan trọng của đạo đức trong tài chính và kế toán', 'content' => 'Đảm bảo tính minh bạch và trung thực.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Bảng cân đối kế toán (Balance Sheet)',
                    'description' => 'Hiểu về tài sản, nợ phải trả và vốn chủ sở hữu của doanh nghiệp.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cấu trúc Bảng cân đối kế toán: Phương trình kế toán', 'content' => 'Tài sản = Nợ phải trả + Vốn chủ sở hữu.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Tài sản (Assets): Tài sản ngắn hạn và dài hạn', 'content' => 'Tiền mặt, khoản phải thu, hàng tồn kho, tài sản cố định.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Nợ phải trả (Liabilities): Nợ ngắn hạn và dài hạn', 'content' => 'Khoản phải trả, vay ngắn hạn, vay dài hạn.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Vốn chủ sở hữu (Equity): Vốn góp, lợi nhuận giữ lại', 'content' => 'Phần vốn thuộc về chủ sở hữu doanh nghiệp.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Phân tích Bảng cân đối kế toán: Đánh giá cơ cấu tài chính', 'content' => 'Hiểu về nguồn vốn và cách sử dụng vốn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Báo cáo kết quả kinh doanh (Income Statement)',
                    'description' => 'Đánh giá hiệu quả hoạt động kinh doanh của doanh nghiệp.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Cấu trúc Báo cáo kết quả kinh doanh: Doanh thu, Chi phí, Lợi nhuận', 'content' => 'Hiểu về các thành phần tạo nên lợi nhuận.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Doanh thu (Revenue) và Giá vốn hàng bán (COGS)', 'content' => 'Các nguồn thu và chi phí trực tiếp tạo ra sản phẩm/dịch vụ.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Chi phí hoạt động (Operating Expenses): Chi phí bán hàng, quản lý', 'content' => 'Các chi phí liên quan đến hoạt động kinh doanh thường xuyên.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Lợi nhuận gộp, Lợi nhuận từ hoạt động kinh doanh, Lợi nhuận sau thuế', 'content' => 'Các cấp độ lợi nhuận khác nhau.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Phân tích Báo cáo kết quả kinh doanh: Đánh giá khả năng sinh lời', 'content' => 'Hiểu về hiệu suất hoạt động của doanh nghiệp.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Báo cáo lưu chuyển tiền tệ (Cash Flow Statement)',
                    'description' => 'Theo dõi dòng tiền vào và ra của doanh nghiệp.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Mục đích của Báo cáo lưu chuyển tiền tệ', 'content' => 'Hiểu về khả năng tạo ra tiền mặt của doanh nghiệp.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Dòng tiền từ hoạt động kinh doanh (Operating Activities)', 'content' => 'Tiền mặt từ các hoạt động cốt lõi của doanh nghiệp.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Dòng tiền từ hoạt động đầu tư (Investing Activities)', 'content' => 'Tiền mặt liên quan đến mua/bán tài sản dài hạn.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Dòng tiền từ hoạt động tài chính (Financing Activities)', 'content' => 'Tiền mặt liên quan đến vay mượn, phát hành cổ phiếu.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Phân tích Báo cáo lưu chuyển tiền tệ: Đánh giá khả năng thanh khoản', 'content' => 'Hiểu về sức khỏe dòng tiền của doanh nghiệp.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Các chỉ số tài chính quan trọng',
                    'description' => 'Sử dụng các chỉ số để phân tích sâu hơn về hiệu quả tài chính.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Chỉ số khả năng thanh toán (Current Ratio, Quick Ratio)', 'content' => 'Đánh giá khả năng thanh toán nợ ngắn hạn.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chỉ số hiệu quả hoạt động (Inventory Turnover, Asset Turnover)', 'content' => 'Đánh giá mức độ hiệu quả sử dụng tài sản.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Chỉ số khả năng sinh lời (Gross Profit Margin, Net Profit Margin, ROE, ROA)', 'content' => 'Đo lường khả năng tạo ra lợi nhuận.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Chỉ số đòn bẩy tài chính (Debt-to-Equity Ratio)', 'content' => 'Đánh giá mức độ phụ thuộc vào nợ vay.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Phân tích xu hướng và so sánh ngành', 'content' => 'So sánh các chỉ số qua các kỳ và với đối thủ cạnh tranh.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Ứng dụng Tài chính và Kế toán trong Quyết định',
                    'description' => 'Sử dụng kiến thức để đưa ra các quyết định thông minh hơn.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Phân tích điểm hòa vốn (Break-even Analysis)', 'content' => 'Xác định doanh thu cần thiết để bù đắp chi phí.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Đánh giá dự án đầu tư cơ bản (Payback Period, NPV, IRR)', 'content' => 'Các phương pháp đánh giá tính khả thi của dự án.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Lập ngân sách cá nhân và gia đình', 'content' => 'Áp dụng nguyên tắc tài chính vào quản lý tiền bạc cá nhân.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Hiểu về thuế và các quy định cơ bản', 'content' => 'Các loại thuế và nghĩa vụ thuế của doanh nghiệp/cá nhân.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Lập trình Game với Unreal Engine 5 và C++',
            'description' => 'Học cách phát triển game 3D chất lượng cao với Unreal Engine 5, công cụ làm game hàng đầu trong ngành, sử dụng ngôn ngữ lập trình C++ để xây dựng logic game phức tạp.',
            'price' => 1500000.00,
            'categoryIds' => [22, 20, 27], // Unreal Engine, Lập trình Game, C++
            'requirements' => [
                'Kiến thức cơ bản về lập trình C++.',
                'Máy tính có cấu hình mạnh để chạy Unreal Engine 5.',
                'Sự đam mê với việc phát triển game 3D.'
            ],
            'objectives' => [
                'Làm chủ giao diện và các công cụ chính của Unreal Engine 5.',
                'Viết mã C++ để điều khiển nhân vật, AI và logic game.',
                'Sử dụng Blueprints để tạo prototype nhanh và mở rộng chức năng.',
                'Thiết kế môi trường 3D, ánh sáng và hiệu ứng hình ảnh sống động.',
                'Tạo hệ thống vật lý, va chạm và tương tác phức tạp.',
                'Triển khai game lên các nền tảng khác nhau.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Unreal Engine 5 và C++',
                    'description' => 'Tổng quan về Unreal Engine và cách bắt đầu với C++ trong game dev.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Unreal Engine 5 là gì? Ưu điểm và ứng dụng', 'content' => 'Khái niệm UE5, Lumen, Nanite, MetaHumans.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt Unreal Engine và Visual Studio', 'content' => 'Hướng dẫn cài đặt các công cụ cần thiết.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giao diện Unreal Editor: Viewport, Details, World Outliner, Content Browser', 'content' => 'Làm quen với các cửa sổ chính của UE5.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tạo dự án mới và các loại Project Template', 'content' => 'Các bước tạo dự án game đầu tiên.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Actor, Component và Blueprints',
                    'description' => 'Xây dựng thế giới game bằng cách kết hợp các thành phần và logic.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Actor và Component trong Unreal Engine', 'content' => 'Hiểu về các đối tượng cơ bản trong UE5.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Blueprints: Lập trình trực quan', 'content' => 'Sử dụng Blueprints để tạo logic game mà không cần code.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Tạo Actor và Component bằng C++', 'content' => 'Viết mã C++ để tạo các lớp Actor và Component tùy chỉnh.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Giao tiếp giữa C++ và Blueprints', 'content' => 'Cách kết nối logic từ C++ với Blueprints.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Input System: Xử lý đầu vào người dùng', 'content' => 'Cách xử lý bàn phím, chuột, gamepad.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Nhân vật và Điều khiển (Character & Movement)',
                    'description' => 'Xây dựng nhân vật người chơi và hệ thống điều khiển.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Character Class và Character Movement Component', 'content' => 'Tạo nhân vật có thể di chuyển, nhảy.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Thiết lập Camera và Góc nhìn', 'content' => 'Cấu hình camera cho góc nhìn thứ nhất/thứ ba.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Animation Blueprint và State Machine', 'content' => 'Quản lý các hoạt ảnh của nhân vật.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Xử lý va chạm và tương tác với môi trường', 'content' => 'Phát hiện va chạm và phản ứng.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Tạo AI cơ bản cho kẻ địch (Behavior Trees, EQS)', 'content' => 'Lập trình hành vi đơn giản cho AI.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Vật lý, Va chạm và Hệ thống Gameplay',
                    'description' => 'Tạo ra các tương tác vật lý thực tế và logic game phức tạp.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Physics System và Collision Responses', 'content' => 'Mô phỏng vật lý và các cài đặt va chạm.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Raycasting, Line Tracing và Overlaps', 'content' => 'Phát hiện đối tượng trong thế giới game.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Gameplay Framework: GameMode, GameState, PlayerState', 'content' => 'Các lớp cơ bản để quản lý trạng thái game.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Event Dispatchers và Delegates', 'content' => 'Thiết lập giao tiếp giữa các Actor và Component.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Đồ họa, Ánh sáng và Hiệu ứng',
                    'description' => 'Thiết kế môi trường game sống động và hấp dẫn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Lumen và Nanite: Ánh sáng và Hình học thế hệ mới', 'content' => 'Sử dụng các công nghệ đồ họa tiên tiến của UE5.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Material Editor: Tạo vật liệu PBR', 'content' => 'Thiết kế vật liệu chân thực cho các đối tượng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Lighting: Directional, Point, Spot Lights', 'content' => 'Thiết lập hệ thống chiếu sáng trong game.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Particle Systems (Niagara) và Visual Effects', 'content' => 'Tạo hiệu ứng khói, lửa, nổ, v.v.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Post-processing Effects: Color Grading, Bloom, Ambient Occlusion', 'content' => 'Nâng cao chất lượng hình ảnh cuối cùng.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Giao diện người dùng (UI) và Âm thanh',
                    'description' => 'Thiết kế UI/UX và thêm âm thanh để nâng cao trải nghiệm người chơi.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: UMG (Unreal Motion Graphics) UI Designer', 'content' => 'Sử dụng UMG để tạo các Widget UI.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Xử lý sự kiện UI và tương tác', 'content' => 'Làm cho các phần tử UI có thể tương tác.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Sound Cue và Audio Components', 'content' => 'Tích hợp nhạc nền, hiệu ứng âm thanh vào game.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Spatial Audio và Attenuation', 'content' => 'Tạo âm thanh 3D và điều chỉnh độ lớn theo khoảng cách.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 7: Tối ưu hiệu suất và Triển khai game',
                    'description' => 'Đảm bảo game chạy mượt mà và đưa đến tay người chơi.',
                    'sortOrder' => 7,
                    'lessons' => [
                        ['title' => 'Bài 7.1: Tối ưu hóa hiệu suất (Performance Optimization)', 'content' => 'Các kỹ thuật để cải thiện tốc độ khung hình.', 'sortOrder' => 1],
                        ['title' => 'Bài 7.2: Debugging và Profiling trong Unreal Engine', 'content' => 'Tìm và sửa lỗi, theo dõi hiệu suất game.', 'sortOrder' => 2],
                        ['title' => 'Bài 7.3: Triển khai game lên PC, Console, Mobile', 'content' => 'Các bước để xuất bản game lên các nền tảng.', 'sortOrder' => 3],
                        ['title' => 'Bài 7.4: Giới thiệu Marketplace và Cộng đồng Unreal Engine', 'content' => 'Sử dụng Marketplace để tìm tài nguyên và học hỏi.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Thiết kế Đồ họa 2D/3D với Blender',
            'description' => 'Học cách tạo ra các mô hình 3D, hoạt ảnh và render ảnh/video chất lượng cao bằng Blender, phần mềm đồ họa 3D mã nguồn mở mạnh mẽ.',
            'price' => 750000.00,
            'categoryIds' => [54, 49], // Thiết kế đồ họa 2D/3D, Thiết kế
            'requirements' => [
                'Máy tính có cấu hình đủ để chạy Blender.',
                'Sự sáng tạo và mong muốn học hỏi về đồ họa 3D.'
            ],
            'objectives' => [
                'Làm chủ giao diện và các công cụ cơ bản của Blender.',
                'Tạo các mô hình 3D từ cơ bản đến phức tạp (Modeling).',
                'Sử dụng vật liệu và texture để làm cho mô hình chân thực hơn (Shading & Texturing).',
                'Thiết lập ánh sáng và render ảnh/video chất lượng cao (Lighting & Rendering).',
                'Tạo hoạt ảnh đơn giản cho các đối tượng (Animation).',
                'Xuất file cho các mục đích sử dụng khác nhau.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Blender và Giao diện',
                    'description' => 'Làm quen với môi trường làm việc của Blender và các khái niệm cơ bản về 3D.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Blender là gì? Ứng dụng trong đồ họa 3D', 'content' => 'Tổng quan về Blender và các lĩnh vực ứng dụng.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt Blender và cấu hình ban đầu', 'content' => 'Hướng dẫn cài đặt và tối ưu hóa hiệu suất.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giao diện Blender: Viewport, Outliner, Properties, Toolbar', 'content' => 'Làm quen với các khu vực chính của giao diện.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Di chuyển, xoay, phóng to/thu nhỏ trong không gian 3D', 'content' => 'Các thao tác cơ bản để điều hướng trong Blender.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Modeling - Tạo Mô hình 3D',
                    'description' => 'Học các kỹ thuật để tạo ra các đối tượng 3D.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Các đối tượng cơ bản (Primitives): Cube, Sphere, Cylinder', 'content' => 'Tạo và chỉnh sửa các hình khối cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Edit Mode: Chỉnh sửa Vertex, Edge, Face', 'content' => 'Đi sâu vào chỉnh sửa chi tiết của mô hình.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Các công cụ Modeling cơ bản: Extrude, Inset, Bevel, Loop Cut', 'content' => 'Sử dụng các công cụ để tạo hình phức tạp.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Modifier: Subdivision Surface, Mirror, Solidify', 'content' => 'Áp dụng các modifier để thay đổi hình dạng mô hình.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Sculpting cơ bản', 'content' => 'Chạm khắc mô hình như đất sét ảo.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Shading và Texturing - Vật liệu và Kết cấu',
                    'description' => 'Làm cho mô hình của bạn trông chân thực và hấp dẫn hơn.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Material: Tạo và áp dụng vật liệu', 'content' => 'Sử dụng Principled BSDF Shader để tạo vật liệu.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: UV Unwrapping: Mở phẳng mô hình để áp dụng texture', 'content' => 'Chuẩn bị mô hình cho việc gắn texture.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Texturing: Áp dụng hình ảnh làm kết cấu', 'content' => 'Sử dụng hình ảnh để tạo bề mặt cho mô hình.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Node Editor: Xây dựng vật liệu phức tạp', 'content' => 'Sử dụng các node để tạo hiệu ứng vật liệu.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Procedural Textures', 'content' => 'Tạo texture bằng thuật toán mà không cần hình ảnh.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Lighting và Rendering - Ánh sáng và Kết xuất',
                    'description' => 'Tạo ra những bức ảnh và video 3D chất lượng cao.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các loại đèn trong Blender: Point, Sun, Spot, Area', 'content' => 'Sử dụng các nguồn sáng khác nhau.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: HDRIs (High Dynamic Range Images) cho ánh sáng môi trường', 'content' => 'Sử dụng hình ảnh 360 độ để chiếu sáng cảnh.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Camera: Thiết lập góc nhìn và thông số', 'content' => 'Cấu hình camera để chụp ảnh/quay video.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Render Engine: Cycles và Eevee', 'content' => 'So sánh hai engine render chính của Blender.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Render Settings và Output', 'content' => 'Cấu hình chất lượng render và định dạng xuất file.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Animation - Hoạt ảnh 3D',
                    'description' => 'Làm cho các đối tượng của bạn chuyển động.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Keyframe Animation cơ bản', 'content' => 'Tạo hoạt ảnh bằng cách đặt các keyframe.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Graph Editor và Dope Sheet', 'content' => 'Chỉnh sửa đường cong hoạt ảnh để tạo chuyển động mượt mà.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Rigging cơ bản (Armature)', 'content' => 'Tạo bộ xương để điều khiển mô hình nhân vật.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Walk Cycle đơn giản', 'content' => 'Tạo hoạt ảnh đi bộ lặp lại.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Render Animation và Xuất video', 'content' => 'Kết xuất hoạt ảnh thành chuỗi hình ảnh hoặc video.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Các chủ đề nâng cao và Quy trình làm việc',
                    'description' => 'Khám phá các kỹ thuật nâng cao và tối ưu hóa quy trình làm việc.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Compositing trong Blender', 'content' => 'Kết hợp các lớp render và thêm hiệu ứng hậu kỳ.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Giới thiệu Geometry Nodes', 'content' => 'Tạo hình học phức tạp bằng node-based system.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Add-ons và Scripting trong Blender', 'content' => 'Mở rộng chức năng của Blender với các add-on.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Tối ưu hóa hiệu suất cho Scene lớn', 'content' => 'Các kỹ thuật để làm việc với các dự án phức tạp.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Xuất file cho Game Engines (Unity, Unreal) và 3D Printing', 'content' => 'Chuẩn bị mô hình cho các mục đích khác nhau.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Tiếng Nhật Giao Tiếp Cơ Bản (N5)',
            'description' => 'Khóa học này dành cho người mới bắt đầu học tiếng Nhật, giúp bạn nắm vững bảng chữ cái, ngữ pháp cơ bản và các mẫu câu giao tiếp hàng ngày để đạt trình độ N5.',
            'price' => 500000.00,
            'categoryIds' => [86, 84], // Tiếng Nhật, Ngoại ngữ
            'requirements' => [
                'Không yêu cầu kiến thức tiếng Nhật trước.',
                'Sự kiên trì và thực hành thường xuyên.'
            ],
            'objectives' => [
                'Đọc và viết thành thạo bảng chữ cái Hiragana và Katakana.',
                'Nắm vững khoảng 100 chữ Kanji cơ bản.',
                'Hiểu và sử dụng các cấu trúc ngữ pháp N5.',
                'Tự tin giao tiếp trong các tình huống hàng ngày đơn giản.',
                'Có khả năng nghe hiểu các đoạn hội thoại ngắn và đơn giản.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Làm quen với Tiếng Nhật và Bảng chữ cái',
                    'description' => 'Những bước đầu tiên để khám phá ngôn ngữ Nhật Bản.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Giới thiệu về Tiếng Nhật và Hệ thống chữ viết', 'content' => 'Hiragana, Katakana, Kanji, Romaji.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Học bảng chữ cái Hiragana (Phần 1)', 'content' => 'Cách viết, cách đọc các chữ cái cơ bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Học bảng chữ cái Hiragana (Phần 2) và Trường âm, Âm ngắt', 'content' => 'Các quy tắc đặc biệt của Hiragana.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Học bảng chữ cái Katakana (Phần 1)', 'content' => 'Cách viết, cách đọc Katakana.', 'sortOrder' => 4],
                        ['title' => 'Bài 1.5: Học bảng chữ cái Katakana (Phần 2) và Âm ghép, Âm đục', 'content' => 'Các quy tắc đặc biệt của Katakana.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 2: Ngữ pháp N5 Cơ bản (Phần 1)',
                    'description' => 'Bắt đầu với các cấu trúc ngữ pháp thiết yếu nhất.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cấu trúc câu: N1 は N2 です (Giới thiệu bản thân)', 'content' => 'Cách giới thiệu tên, nghề nghiệp, quốc tịch.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Trợ từ の (no) và Câu hỏi か (ka)', 'content' => 'Sở hữu, giải thích và cách đặt câu hỏi.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Đại từ chỉ định: これ、それ、あれ、どれ', 'content' => 'Chỉ vật ở các khoảng cách khác nhau.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Đếm số và Đơn vị đếm (Counter)', 'content' => 'Cách đếm người, vật, thời gian.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Giới thiệu về Động từ và Chia thể Masu', 'content' => 'Các loại động từ và cách chia thể lịch sự.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Ngữ pháp N5 Cơ bản (Phần 2) và Từ vựng',
                    'description' => 'Mở rộng kiến thức ngữ pháp và vốn từ vựng hàng ngày.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Trợ từ に、へ、で (Chỉ địa điểm, phương tiện, mục đích)', 'content' => 'Sử dụng các trợ từ để diễn đạt hành động.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tính từ い và な (i-adjectives, na-adjectives)', 'content' => 'Cách sử dụng tính từ để mô tả.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Thể Te của Động từ và các ứng dụng', 'content' => 'Yêu cầu, cho phép, đang làm gì.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Số lượng và Tần suất (いつ、どこ、だれ、なに)', 'content' => 'Hỏi về thời gian, địa điểm, người, vật.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Từ vựng về Gia đình, Công việc, Sở thích', 'content' => 'Các từ vựng thông dụng trong đời sống.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Kanji và Luyện đọc, viết',
                    'description' => 'Bắt đầu làm quen với chữ Hán trong tiếng Nhật.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Giới thiệu Kanji và Cách học Kanji hiệu quả', 'content' => 'Nguồn gốc, bộ thủ, âm Hán Việt.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: 20 Kanji cơ bản đầu tiên (Số, Người, Ngày, Tháng, Năm)', 'content' => 'Học các chữ Kanji thông dụng nhất.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: 20 Kanji tiếp theo (Đất, Trời, Nước, Lửa, Gỗ)', 'content' => 'Tiếp tục học các chữ Kanji thiết yếu.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Luyện đọc các câu đơn giản có Kanji', 'content' => 'Thực hành đọc các đoạn văn ngắn.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Viết các chữ Kanji theo nét bút', 'content' => 'Luyện viết để nhớ chữ Kanji lâu hơn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Giao tiếp trong các Tình huống hàng ngày',
                    'description' => 'Thực hành các mẫu câu để giao tiếp tự tin hơn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Chào hỏi, giới thiệu và tạm biệt', 'content' => 'Các câu chào hỏi theo từng thời điểm trong ngày.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Hỏi đường và chỉ đường', 'content' => 'Các mẫu câu để hỏi và chỉ dẫn địa điểm.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Mua sắm và mặc cả', 'content' => 'Các câu giao tiếp khi đi mua sắm.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đặt món ăn trong nhà hàng', 'content' => 'Các mẫu câu để gọi món và thanh toán.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Nói về thời tiết và các hoạt động hàng ngày', 'content' => 'Giao tiếp về các chủ đề quen thuộc.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Luyện nghe, Nói và Ôn tập N5',
                    'description' => 'Củng cố kiến thức và chuẩn bị cho kỳ thi N5.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Luyện nghe các đoạn hội thoại ngắn và bài tập điền từ', 'content' => 'Nâng cao khả năng nghe hiểu.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Thực hành nói chuyện theo chủ đề', 'content' => 'Luyện phản xạ và tự tin khi nói.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Ôn tập tổng hợp ngữ pháp và từ vựng N5', 'content' => 'Hệ thống lại toàn bộ kiến thức.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Giải đề thi JLPT N5 mẫu', 'content' => 'Làm quen với cấu trúc đề thi và thời gian làm bài.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lộ trình học tiếng Nhật lên N4', 'content' => 'Định hướng cho hành trình học tiếp theo.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Cloud Computing Cơ bản với AWS',
            'description' => 'Tìm hiểu các khái niệm cốt lõi về điện toán đám mây và làm quen với các dịch vụ cơ bản của Amazon Web Services (AWS), nền tảng đám mây hàng đầu thế giới.',
            'price' => 980000.00,
            'categoryIds' => [91, 41], // Cloud Computing (AWS, Azure, GCP), CNTT & Phần mềm
            'requirements' => [
                'Kiến thức cơ bản về máy tính và internet.',
                'Tài khoản AWS (có thể sử dụng Free Tier).'
            ],
            'objectives' => [
                'Hiểu các mô hình điện toán đám mây (IaaS, PaaS, SaaS).',
                'Làm quen với giao diện quản lý AWS Console.',
                'Sử dụng các dịch vụ AWS cốt lõi như EC2, S3, RDS, VPC.',
                'Triển khai các ứng dụng đơn giản trên AWS.',
                'Hiểu về bảo mật và chi phí trên AWS.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Điện toán Đám mây và AWS',
                    'description' => 'Khám phá khái niệm về đám mây và nền tảng AWS.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Điện toán Đám mây là gì? Lợi ích và mô hình dịch vụ', 'content' => 'IaaS, PaaS, SaaS, Public, Private, Hybrid Cloud.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Giới thiệu Amazon Web Services (AWS) và các khu vực', 'content' => 'Tổng quan về AWS, Region, Availability Zone.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Tạo tài khoản AWS và sử dụng Free Tier', 'content' => 'Hướng dẫn đăng ký tài khoản và tận dụng Free Tier.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Giao diện AWS Management Console', 'content' => 'Làm quen với bảng điều khiển quản lý AWS.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Dịch vụ Compute - EC2 (Elastic Compute Cloud)',
                    'description' => 'Triển khai và quản lý máy chủ ảo trên đám mây.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: EC2 là gì? Các loại Instance và Ami', 'content' => 'Máy chủ ảo trên AWS, các loại cấu hình và Amazon Machine Image.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Khởi tạo và kết nối đến EC2 Instance', 'content' => 'Các bước để tạo và truy cập máy chủ EC2.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Security Groups và Key Pairs', 'content' => 'Cấu hình bảo mật và quản lý khóa truy cập.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Elastic IP và Elastic Load Balancer (ELB) cơ bản', 'content' => 'IP tĩnh và cân bằng tải cho ứng dụng.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Auto Scaling Groups', 'content' => 'Tự động điều chỉnh số lượng instance theo nhu cầu.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Dịch vụ Storage - S3 (Simple Storage Service)',
                    'description' => 'Lưu trữ dữ liệu đối tượng trên đám mây một cách an toàn và có khả năng mở rộng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: S3 là gì? Buckets và Objects', 'content' => 'Khái niệm S3, cách lưu trữ file và folder.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tạo và quản lý S3 Bucket', 'content' => 'Các bước để tạo và cấu hình bucket.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Upload, Download và Xóa Objects', 'content' => 'Các thao tác cơ bản với file trên S3.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: S3 Permissions và Public Access', 'content' => 'Cấu hình quyền truy cập cho bucket và object.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: S3 Static Website Hosting', 'content' => 'Sử dụng S3 để host website tĩnh.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Dịch vụ Database - RDS (Relational Database Service)',
                    'description' => 'Triển khai và quản lý cơ sở dữ liệu quan hệ trên đám mây.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: RDS là gì? Các Engine được hỗ trợ (MySQL, PostgreSQL)', 'content' => 'Dịch vụ cơ sở dữ liệu được quản lý hoàn toàn.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Khởi tạo và kết nối đến RDS Instance', 'content' => 'Các bước để tạo và truy cập cơ sở dữ liệu RDS.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Backup, Restore và Snapshots', 'content' => 'Quản lý sao lưu và phục hồi dữ liệu.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Read Replicas và Multi-AZ Deployments', 'content' => 'Tăng cường hiệu suất và khả năng sẵn sàng cao.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Mạng (Networking) - VPC (Virtual Private Cloud)',
                    'description' => 'Thiết lập mạng ảo riêng của bạn trong AWS.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: VPC là gì? Subnets, Route Tables, Internet Gateway', 'content' => 'Khái niệm VPC và các thành phần chính.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Tạo VPC và Subnets', 'content' => 'Thiết lập cấu trúc mạng cơ bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Network Access Control Lists (NACLs) và Security Groups', 'content' => 'Các lớp bảo mật mạng.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: VPN và Direct Connect (Giới thiệu)', 'content' => 'Kết nối mạng on-premise với VPC.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Bảo mật, Giám sát và Quản lý Chi phí trên AWS',
                    'description' => 'Đảm bảo an toàn và tối ưu chi phí khi sử dụng AWS.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: IAM (Identity and Access Management): Quản lý người dùng và quyền', 'content' => 'Tạo người dùng, nhóm, vai trò và chính sách.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: CloudWatch: Giám sát tài nguyên và ứng dụng', 'content' => 'Theo dõi metrics, logs và alarms.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Billing and Cost Management: Quản lý chi phí AWS', 'content' => 'Hiểu về hóa đơn AWS và các công cụ tối ưu chi phí.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: AWS Well-Architected Framework (Giới thiệu)', 'content' => 'Các trụ cột để xây dựng hệ thống tối ưu trên AWS.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Các chứng chỉ AWS và lộ trình học tập', 'content' => 'Định hướng cho sự nghiệp Cloud Computing.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Đệm Guitar Cơ bản cho Người Mới Bắt Đầu',
            'description' => 'Học cách chơi guitar đệm các bài hát yêu thích một cách dễ dàng, từ những hợp âm đầu tiên đến các điệu nhạc phổ biến.',
            'price' => 399000.00,
            'categoryIds' => [70, 69], // Nhạc cụ (Guitar), Âm nhạc
            'requirements' => [
                'Có một cây đàn guitar (acoustic hoặc classic).',
                'Sự kiên nhẫn và đam mê với âm nhạc.',
            ],
            'objectives' => [
                'Nắm vững các hợp âm cơ bản (Major, Minor) ở thế bấm mở.',
                'Chơi thành thạo các điệu đệm phổ biến (Slow Rock, Pop Ballad, Disco...).',
                'Đệm được các bài hát đơn giản và chuyển hợp âm mượt mà.',
                'Hiểu cấu tạo và chức năng cơ bản của đàn guitar.',
                'Đọc và hiểu các tablature (TAB) và biểu đồ hợp âm.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Làm quen với Guitar và Hợp âm cơ bản',
                    'description' => 'Giới thiệu về đàn guitar, cách giữ đàn và các hợp âm quan trọng nhất.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Cấu tạo và các bộ phận của Guitar', 'content' => 'Tìm hiểu về các bộ phận của đàn và chức năng của chúng.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Tư thế cầm đàn và cách bấm nốt cơ bản', 'content' => 'Hướng dẫn tư thế chuẩn và cách bấm các nốt trên cần đàn.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Hợp âm Am, C, G, Em và cách chuyển', 'content' => 'Học các hợp âm cơ bản và luyện tập chuyển đổi giữa chúng.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tập đệm điệu Slow Rock cơ bản', 'content' => 'Bắt đầu với điệu Slow Rock đơn giản để làm quen với nhịp điệu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Mở rộng Hợp âm và Điệu đệm',
                    'description' => 'Thêm các hợp âm mới và thực hành các điệu đệm phức tạp hơn.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Hợp âm D, E, F, Dm và Bm (barre cơ bản)', 'content' => 'Học thêm các hợp âm mới, bao gồm giới thiệu về barre chord.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Luyện tập chuyển đổi hợp âm nhanh', 'content' => 'Các bài tập để tăng tốc độ và độ mượt khi chuyển hợp âm.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Điệu Pop Ballad và các biến thể', 'content' => 'Học các kỹ thuật đệm cho điệu Pop Ballad.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Đệm các bài hát có nhiều hợp âm hơn', 'content' => 'Áp dụng các hợp âm và điệu đã học vào các bài hát thực tế.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Kỹ thuật quạt chả và cách ngắt tiếng', 'content' => 'Học các kỹ thuật quạt chả và cách tạo hiệu ứng ngắt tiếng.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Nâng cao kỹ năng và Ứng dụng',
                    'description' => 'Hoàn thiện kỹ năng đệm và tự tin chơi các bài hát phức tạp.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Hợp âm nâng cao (thứ 7, sus, add...)', 'content' => 'Giới thiệu các hợp âm mở rộng để làm phong phú âm thanh.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Điệu Fox, Disco và Rock cơ bản', 'content' => 'Học các điệu đệm năng động và sôi nổi hơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Cách tự tìm và đệm một bài hát mới', 'content' => 'Hướng dẫn phân tích bài hát và tự đệm.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Thực hành đệm theo ca sĩ và band nhạc', 'content' => 'Luyện tập đệm cùng với các bản nhạc có sẵn.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Bảo quản và điều chỉnh Guitar', 'content' => 'Cách vệ sinh, thay dây và điều chỉnh đàn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Lý thuyết âm nhạc ứng dụng cho Guitar',
                    'description' => 'Hiểu thêm về cấu trúc âm nhạc để chơi guitar hiệu quả hơn.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Giới thiệu về nốt nhạc và khuông nhạc', 'content' => 'Các ký hiệu cơ bản trong âm nhạc.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Âm giai (Scale) và cách ứng dụng', 'content' => 'Tìm hiểu các loại âm giai và cách sử dụng chúng.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Vòng hòa âm cơ bản và cách xây dựng', 'content' => 'Hiểu về các vòng hòa âm phổ biến.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Nhịp điệu và cách giữ nhịp chính xác', 'content' => 'Luyện tập với máy đếm nhịp và cảm nhận nhịp điệu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Kỹ thuật Fingerstyle cơ bản',
                    'description' => 'Giới thiệu kỹ thuật chơi fingerstyle để tạo ra âm thanh phong phú hơn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Giới thiệu Fingerstyle và tư thế tay phải', 'content' => 'Làm quen với cách chơi fingerstyle.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Các mẫu Fingerstyle cơ bản', 'content' => 'Học các mẫu fingerstyle đơn giản.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Ứng dụng Fingerstyle vào bài hát', 'content' => 'Thực hành fingerstyle trên các bài hát quen thuộc.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Kết hợp đệm và fingerstyle', 'content' => 'Cách chuyển đổi linh hoạt giữa đệm và fingerstyle.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Phát triển kỹ năng nghe và tự học',
                    'description' => 'Hướng dẫn cách tự học và phát triển kỹ năng nghe nhạc.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Luyện nghe để nhận biết hợp âm và điệu', 'content' => 'Các bài tập giúp tai nghe nhạy hơn.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Tìm tài liệu và học từ các nguồn online', 'content' => 'Hướng dẫn tìm kiếm hợp âm, tab và video hướng dẫn.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Lập kế hoạch luyện tập hiệu quả', 'content' => 'Cách xây dựng lịch trình luyện tập phù hợp.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Chơi và trình diễn trước đám đông', 'content' => 'Những lời khuyên để tự tin khi chơi nhạc.', 'sortOrder' => 4],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Sản xuất Âm nhạc Điện tử với Ableton Live',
            'description' => 'Khám phá thế giới sản xuất âm nhạc điện tử, từ ý tưởng đến hoàn thiện bản nhạc với phần mềm Ableton Live.',
            'price' => 750000.00,
            'categoryIds' => [71, 69], // Sản xuất âm nhạc, Âm nhạc
            'requirements' => [
                'Máy tính có cài đặt Ableton Live (phiên bản dùng thử hoặc đầy đủ).',
                'Tai nghe hoặc loa kiểm âm chất lượng tốt.',
                'Đam mê với âm nhạc điện tử và sự sáng tạo.',
            ],
            'objectives' => [
                'Làm quen với giao diện và các chức năng cơ bản của Ableton Live.',
                'Tạo ra các đoạn beat, bassline và melody cơ bản.',
                'Sử dụng hiệu ứng âm thanh và trộn nhạc (mixing) cơ bản.',
                'Sắp xếp các phần của bài nhạc thành một cấu trúc hoàn chỉnh.',
                'Xuất bản (export) bản nhạc của bạn ở định dạng chất lượng cao.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Ableton Live và Setup',
                    'description' => 'Tổng quan về Ableton Live và cách thiết lập môi trường làm việc.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Ableton Live là gì? Tổng quan giao diện', 'content' => 'Tìm hiểu các cửa sổ Session View, Arrangement View...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cài đặt Audio Interface và MIDI Controller', 'content' => 'Kết nối thiết bị âm thanh và MIDI với Ableton Live.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Quản lý dự án và lưu trữ file', 'content' => 'Cách tạo, lưu và quản lý các dự án trong Ableton.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các phím tắt cơ bản và quy trình làm việc', 'content' => 'Tối ưu hóa quy trình làm việc với các phím tắt.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Tạo Beat và Rhythmic Elements',
                    'description' => 'Học cách xây dựng nền tảng nhịp điệu cho bản nhạc của bạn.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Giới thiệu Drum Rack và các loại trống', 'content' => 'Sử dụng Drum Rack để tạo các bộ trống tùy chỉnh.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Lập trình Beat với MIDI Clip', 'content' => 'Tạo các mẫu trống bằng cách vẽ nốt MIDI.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Sử dụng Audio Loop và Warping', 'content' => 'Kéo thả các loop trống và điều chỉnh tempo.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Thêm Percussion và Hi-hat patterns', 'content' => 'Làm phong phú beat với các yếu tố gõ phụ và hi-hat.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Humanize và Groove Pool', 'content' => 'Làm cho beat tự nhiên và có cảm giác hơn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Bassline và Melody',
                    'description' => 'Phát triển các đường bass và giai điệu chính cho bài nhạc.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Giới thiệu Synthesizer và Sampler', 'content' => 'Các công cụ tạo ra âm thanh bass và melody.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tạo Bassline cơ bản với Operator/Analog', 'content' => 'Thiết kế các đường bass mạnh mẽ và phù hợp.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Viết Melody với MIDI Clip và Scale', 'content' => 'Sáng tạo giai điệu và giữ chúng trong một âm giai.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Lớp âm thanh (Layering) và Arpeggiator', 'content' => 'Kết hợp nhiều âm thanh và tạo ra các mẫu arpeggio.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Automation cho Bass và Melody', 'content' => 'Tạo chuyển động và sự phát triển cho các yếu tố này.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Hiệu ứng âm thanh và Mixing cơ bản',
                    'description' => 'Làm cho âm thanh của bạn chuyên nghiệp hơn với các hiệu ứng và kỹ thuật trộn.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Giới thiệu các loại hiệu ứng (Reverb, Delay, EQ, Compressor)', 'content' => 'Hiểu chức năng của từng loại hiệu ứng.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Sử dụng EQ để định hình âm thanh', 'content' => 'Cắt bỏ tần số không mong muốn và tăng cường tần số cần thiết.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Compressor để kiểm soát Dynamics', 'content' => 'Làm cho âm thanh đồng đều và mạnh mẽ hơn.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Reverb và Delay để tạo không gian', 'content' => 'Tạo cảm giác không gian và chiều sâu cho âm thanh.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Cân bằng âm lượng (Gain Staging) và Panning', 'content' => 'Thiết lập mức âm lượng phù hợp và định vị âm thanh trong không gian stereo.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Sắp xếp và Cấu trúc bài nhạc',
                    'description' => 'Biến các ý tưởng thành một bản nhạc hoàn chỉnh với cấu trúc rõ ràng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Cấu trúc bài nhạc điện tử phổ biến (Intro, Verse, Chorus, Break, Outro)', 'content' => 'Hiểu các phần của một bài nhạc điện tử.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Sắp xếp các Clip trong Arrangement View', 'content' => 'Kéo thả và sắp xếp các đoạn nhạc.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Chuyển đoạn (Transition) và Build-up', 'content' => 'Tạo sự chuyển tiếp mượt mà giữa các phần.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tạo sự đa dạng và phát triển cho bài nhạc', 'content' => 'Tránh sự đơn điệu bằng cách thêm các yếu tố mới.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Hoàn thiện và Xuất bản',
                    'description' => 'Các bước cuối cùng để hoàn thành và chia sẻ bản nhạc của bạn.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Mastering cơ bản trong Ableton Live', 'content' => 'Làm cho bản nhạc có âm lượng và chất lượng phù hợp để phát hành.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Xuất bản (Export) Audio và MIDI', 'content' => 'Các định dạng file và cài đặt xuất bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chia sẻ nhạc trên các nền tảng (SoundCloud, YouTube)', 'content' => 'Cách đưa nhạc của bạn đến với khán giả.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Bảo vệ bản quyền và tìm kiếm phản hồi', 'content' => 'Các vấn đề pháp lý cơ bản và cách nhận góp ý.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Luyện tập và phát triển phong cách cá nhân', 'content' => 'Lời khuyên để tiếp tục học hỏi và sáng tạo.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Tiếng Nhật Giao Tiếp cho Du Lịch và Công Việc',
            'description' => 'Học tiếng Nhật cơ bản để tự tin giao tiếp trong các tình huống du lịch và công việc hàng ngày tại Nhật Bản.',
            'price' => 550000.00,
            'categoryIds' => [86, 84], // Tiếng Nhật, Ngoại ngữ
            'requirements' => [
                'Không yêu cầu kiến thức tiếng Nhật trước.',
                'Sự hứng thú với văn hóa Nhật Bản.',
            ],
            'objectives' => [
                'Nắm vững Hiragana và Katakana.',
                'Giao tiếp cơ bản trong các tình huống như chào hỏi, giới thiệu, mua sắm, hỏi đường.',
                'Hiểu và sử dụng các cấu trúc ngữ pháp N5 cơ bản.',
                'Mở rộng vốn từ vựng liên quan đến du lịch và công việc.',
                'Hiểu biết về các phong tục, tập quán giao tiếp của người Nhật.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Bảng chữ cái và Chào hỏi cơ bản',
                    'description' => 'Làm quen với hệ thống chữ viết và các câu chào hỏi thông dụng.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Giới thiệu Hiragana (Phần 1)', 'content' => 'Học các hàng A, Ka, Sa, Ta, Na.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Giới thiệu Hiragana (Phần 2)', 'content' => 'Học các hàng Ha, Ma, Ya, Ra, Wa và các âm đục, bán đục.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Chào hỏi và giới thiệu bản thân', 'content' => 'Ohayou, Konnichiwa, Konbanwa, Hajimemashite...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Cảm ơn, xin lỗi và các câu xã giao', 'content' => 'Arigatou, Sumimasen, Onegaishimasu...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Katakana và Từ vựng du lịch',
                    'description' => 'Học Katakana để đọc các từ mượn và từ vựng cần thiết khi đi du lịch.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Giới thiệu Katakana (Phần 1)', 'content' => 'Học các hàng A, Ka, Sa, Ta, Na của Katakana.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Giới thiệu Katakana (Phần 2)', 'content' => 'Học các hàng Ha, Ma, Ya, Ra, Wa và các âm ghép, âm nhỏ.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Từ vựng về phương tiện giao thông', 'content' => 'Densha, Basu, Hikouki, Takushii...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Từ vựng về địa điểm du lịch và mua sắm', 'content' => 'Eki, Depaato, Konbini, Ginkou...', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Hỏi đường và chỉ đường cơ bản', 'content' => 'Doko desu ka? Massugu, Migi, Hidari...', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Ngữ pháp N5 cơ bản cho giao tiếp',
                    'description' => 'Nắm vững các cấu trúc ngữ pháp nền tảng để xây dựng câu.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Cấu trúc câu cơ bản: N1 wa N2 desu', 'content' => 'Giới thiệu về trợ từ wa và cấu trúc khẳng định.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Trợ từ no và các cách sử dụng', 'content' => 'Sở hữu, giải thích, định danh...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Động từ thể ます (Masu form)', 'content' => 'Cách chia và sử dụng động từ thể ます.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Câu hỏi với ka và các từ để hỏi', 'content' => 'Dare, Nani, Itsu, Doko...', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Tính từ い và な (I-adjective & Na-adjective)', 'content' => 'Cách sử dụng và biến đổi tính từ.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giao tiếp trong nhà hàng và mua sắm',
                    'description' => 'Thực hành các tình huống giao tiếp thực tế khi ăn uống và mua sắm.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Đặt món ăn và đồ uống', 'content' => 'Kore kudasai, Oishii desu...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Hỏi giá và thanh toán', 'content' => 'Ikura desu ka? Onegai shimasu...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Mua sắm quần áo và quà lưu niệm', 'content' => 'Kore wa nan desu ka? Kore o kudasai...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Trả giá và đổi trả hàng (cơ bản)', 'content' => 'Chotto takai desu, Koukan dekimasu ka...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Tiếng Nhật trong môi trường công việc',
                    'description' => 'Các cụm từ và tình huống giao tiếp cần thiết khi làm việc với người Nhật.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Giới thiệu bản thân và công việc', 'content' => 'Watashi wa [tên] desu. [Nghề nghiệp] desu.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chào hỏi đồng nghiệp và cấp trên', 'content' => 'Otsukaresama desu, Yoroshiku onegaishimasu...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Các câu lệnh và yêu cầu lịch sự', 'content' => '~te kudasai, ~te mo ii desu ka...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Thảo luận công việc đơn giản', 'content' => 'Shigoto wa dou desu ka? Wakarimashita.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Viết email công việc ngắn gọn', 'content' => 'Cấu trúc email cơ bản và các cụm từ thông dụng.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Văn hóa và Phong tục giao tiếp Nhật Bản',
                    'description' => 'Hiểu biết về văn hóa giúp bạn giao tiếp tự nhiên và tránh hiểu lầm.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Các nghi thức chào hỏi và cúi chào', 'content' => 'Cách cúi chào trong các tình huống khác nhau.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Văn hóa tặng quà và nhận quà', 'content' => 'Những điều nên và không nên khi tặng/nhận quà.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Giao tiếp phi ngôn ngữ và khoảng cách cá nhân', 'content' => 'Đọc hiểu ngôn ngữ cơ thể và giữ khoảng cách phù hợp.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Những điều cấm kỵ trong giao tiếp', 'content' => 'Các chủ đề nên tránh và hành vi không phù hợp.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Luyện tập giao tiếp với người bản xứ', 'content' => 'Tìm kiếm cơ hội thực hành và cải thiện.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Yoga Cơ Bản để Tăng Cường Linh Hoạt và Giảm Căng Thẳng',
            'description' => 'Khóa học này sẽ hướng dẫn bạn các tư thế Yoga cơ bản, kỹ thuật thở và thiền định để cải thiện sự linh hoạt, sức mạnh và sự bình yên trong tâm trí.',
            'price' => 299000.00,
            'categoryIds' => [74, 73], // Yoga, Sức khỏe & Thể hình
            'requirements' => [
                'Không yêu cầu kinh nghiệm Yoga trước đó.',
                'Một tấm thảm Yoga và không gian yên tĩnh để luyện tập.',
                'Quần áo thoải mái, co giãn.',
            ],
            'objectives' => [
                'Nắm vững các tư thế Yoga cơ bản (Asana) một cách an toàn và đúng kỹ thuật.',
                'Cải thiện sự linh hoạt và dẻo dai của cơ thể.',
                'Tăng cường sức mạnh cốt lõi và ổn định cơ thể.',
                'Học các kỹ thuật thở (Pranayama) để kiểm soát hơi thở và năng lượng.',
                'Thực hành thiền định để giảm căng thẳng và tăng cường sự tập trung.',
                'Xây dựng thói quen Yoga hàng ngày để duy trì sức khỏe thể chất và tinh thần.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Yoga và Nguyên tắc cơ bản',
                    'description' => 'Làm quen với Yoga, lợi ích và các nguyên tắc an toàn khi luyện tập.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Yoga là gì? Lịch sử và Triết lý cơ bản', 'content' => 'Tìm hiểu nguồn gốc và ý nghĩa của Yoga.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Lợi ích của Yoga cho cơ thể và tâm trí', 'content' => 'Khám phá những tác động tích cực của Yoga.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Nguyên tắc an toàn và lắng nghe cơ thể', 'content' => 'Hướng dẫn cách luyện tập an toàn, tránh chấn thương.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Chuẩn bị không gian và dụng cụ luyện tập', 'content' => 'Cách tạo môi trường lý tưởng cho buổi tập Yoga.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các tư thế khởi động và làm ấm cơ thể',
                    'description' => 'Các động tác nhẹ nhàng giúp chuẩn bị cơ thể cho buổi tập chính.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Hít thở sâu và thư giãn ban đầu', 'content' => 'Bắt đầu buổi tập với hơi thở và sự tĩnh lặng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Khởi động khớp cổ, vai và tay', 'content' => 'Các động tác xoay và kéo giãn nhẹ nhàng.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Khởi động khớp hông và chân', 'content' => 'Làm ấm các khớp ở phần dưới cơ thể.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Chuỗi mèo - bò (Cat-Cow Pose)', 'content' => 'Động tác giúp làm mềm cột sống và tăng cường linh hoạt.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Tư thế đứng cơ bản',
                    'description' => 'Học các tư thế đứng giúp tăng cường sức mạnh và sự thăng bằng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Tư thế ngọn núi (Mountain Pose - Tadasana)', 'content' => 'Tư thế nền tảng cho mọi tư thế đứng.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tư thế chiến binh I và II (Warrior I & II)', 'content' => 'Các tư thế mạnh mẽ giúp mở hông và tăng sức bền.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Tư thế tam giác (Triangle Pose - Trikonasana)', 'content' => 'Kéo giãn hông, đùi và cột sống.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Tư thế cái cây (Tree Pose - Vrksasana)', 'content' => 'Cải thiện sự thăng bằng và tập trung.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Tư thế gập người về phía trước (Forward Fold - Uttanasana)', 'content' => 'Kéo giãn gân kheo và thư giãn cột sống.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Tư thế ngồi và vặn xoắn',
                    'description' => 'Các tư thế giúp mở hông, kéo giãn lưng và giải độc cơ thể.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Tư thế ngồi dễ (Easy Pose - Sukhasana)', 'content' => 'Tư thế thiền định cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Tư thế gập người về phía trước khi ngồi (Seated Forward Fold - Paschimottanasana)', 'content' => 'Kéo giãn toàn bộ mặt sau cơ thể.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Tư thế vặn xoắn cột sống (Spinal Twist - Ardha Matsyendrasana)', 'content' => 'Giúp giải độc và tăng cường linh hoạt cột sống.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Tư thế bướm (Bound Angle Pose - Baddha Konasana)', 'content' => 'Mở khớp hông và kéo giãn đùi trong.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Kỹ thuật thở (Pranayama) và Thiền định',
                    'description' => 'Học cách kiểm soát hơi thở và thực hành thiền để đạt được sự bình yên.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Hơi thở bụng (Diaphragmatic Breathing)', 'content' => 'Kỹ thuật thở sâu giúp thư giãn và giảm căng thẳng.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Hơi thở luân phiên mũi (Nadi Shodhana Pranayama)', 'content' => 'Cân bằng hai bán cầu não và làm dịu tâm trí.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Thiền định cho người mới bắt đầu', 'content' => 'Hướng dẫn các bước cơ bản để bắt đầu thiền.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Thiền chánh niệm (Mindfulness Meditation)', 'content' => 'Tập trung vào hiện tại để giảm lo âu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Chuỗi chào mặt trời và Luyện tập hàng ngày',
                    'description' => 'Kết hợp các tư thế thành một chuỗi động tác linh hoạt và xây dựng thói quen luyện tập.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Chuỗi chào mặt trời (Sun Salutation A - Surya Namaskar A)', 'content' => 'Học chuỗi động tác cơ bản để làm ấm toàn thân.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Chuỗi chào mặt trời (Sun Salutation B - Surya Namaskar B)', 'content' => 'Biến thể nâng cao hơn của chuỗi chào mặt trời.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Xây dựng thói quen Yoga 15-30 phút hàng ngày', 'content' => 'Lập kế hoạch và duy trì luyện tập đều đặn.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Tư thế xác chết (Corpse Pose - Savasana) và thư giãn cuối buổi', 'content' => 'Tư thế thư giãn cuối cùng để hấp thụ lợi ích của buổi tập.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Giải đáp các câu hỏi thường gặp về Yoga', 'content' => 'Trả lời các thắc mắc phổ biến của người mới bắt đầu.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Nấu Ăn Gia Đình Việt Nam Truyền Thống',
            'description' => 'Học cách chế biến các món ăn Việt Nam quen thuộc, từ món khai vị đến món chính và tráng miệng, mang hương vị truyền thống vào bữa ăn gia đình bạn.',
            'price' => 499000.00,
            'categoryIds' => [63], // Phát triển cá nhân (kỹ năng sống)
            'requirements' => [
                'Có niềm đam mê với ẩm thực Việt Nam.',
                'Có bếp và các dụng cụ nấu ăn cơ bản.',
                'Sẵn sàng thử nghiệm và thực hành.',
            ],
            'objectives' => [
                'Nắm vững các kỹ thuật sơ chế nguyên liệu cơ bản của ẩm thực Việt.',
                'Chế biến thành thạo ít nhất 10 món ăn truyền thống Việt Nam.',
                'Hiểu cách cân bằng hương vị (chua, cay, mặn, ngọt) trong món ăn Việt.',
                'Tự tin chuẩn bị bữa ăn gia đình ấm cúng với các món ăn Việt.',
                'Bảo quản thực phẩm và an toàn vệ sinh trong nấu ăn.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Ẩm thực Việt và Nguyên liệu cơ bản',
                    'description' => 'Tổng quan về đặc trưng ẩm thực Việt và các nguyên liệu không thể thiếu.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Đặc trưng và vùng miền của ẩm thực Việt Nam', 'content' => 'Phân biệt ẩm thực Bắc, Trung, Nam.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các loại gia vị và nước chấm thiết yếu', 'content' => 'Nước mắm, mắm tôm, các loại rau thơm...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Kỹ thuật chọn và sơ chế rau củ, thịt cá', 'content' => 'Cách chọn nguyên liệu tươi ngon và sơ chế đúng cách.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: An toàn vệ sinh thực phẩm trong nấu ăn', 'content' => 'Các nguyên tắc đảm bảo vệ sinh khi chế biến.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Món khai vị và Món ăn nhẹ',
                    'description' => 'Học cách làm các món ăn nhẹ và khai vị hấp dẫn.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Gỏi cuốn tôm thịt và Nước chấm chua ngọt', 'content' => 'Hướng dẫn từng bước làm gỏi cuốn tươi ngon.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Chả giò (Nem rán) giòn rụm', 'content' => 'Bí quyết làm chả giò giòn lâu và không bị ngấm dầu.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Nộm gà xé phay chua cay', 'content' => 'Cách làm nộm gà thanh mát, đậm đà.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Bánh xèo miền Tây', 'content' => 'Công thức làm bánh xèo vỏ giòn, nhân thơm ngon.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Các món canh và món kho truyền thống',
                    'description' => 'Chế biến các món canh và món kho đặc trưng của bữa cơm Việt.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Canh chua cá lóc miền Tây', 'content' => 'Bí quyết nấu canh chua đúng điệu, cá không tanh.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Canh bí đao nấu sườn non', 'content' => 'Món canh thanh mát, bổ dưỡng cho gia đình.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Thịt kho tàu đậm đà, mềm rục', 'content' => 'Công thức thịt kho tàu chuẩn vị, màu đẹp.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Cá diêu hồng kho tộ', 'content' => 'Cách kho cá thơm ngon, thấm vị, không bị nát.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Đậu phụ sốt cà chua', 'content' => 'Món ăn chay đơn giản nhưng hấp dẫn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Các món xào và món chiên',
                    'description' => 'Học cách xào và chiên các món ăn nhanh gọn, ngon miệng.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Rau muống xào tỏi giòn xanh', 'content' => 'Bí quyết xào rau xanh, giòn mà không bị đen.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Mực xào sả ớt thơm lừng', 'content' => 'Cách xào mực không dai, thấm vị.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Trứng chiên thịt băm đơn giản', 'content' => 'Món ăn dễ làm, phù hợp cho mọi bữa ăn.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Đậu phụ chiên sả ớt', 'content' => 'Món ăn chay mặn mà, đưa cơm.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Món bún, phở và các món nước',
                    'description' => 'Khám phá hương vị của các món bún, phở và các món nước đặc trưng khác.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Phở bò tái lăn chuẩn vị', 'content' => 'Bí quyết nấu nước dùng phở bò thơm ngon.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Bún chả Hà Nội', 'content' => 'Cách làm chả nướng và nước chấm bún chả.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Bún riêu cua đồng', 'content' => 'Công thức nấu bún riêu đậm đà hương vị đồng quê.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Mì Quảng gà', 'content' => 'Cách làm mì Quảng đặc sản miền Trung.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Món tráng miệng và đồ uống truyền thống',
                    'description' => 'Hoàn thiện bữa ăn với các món tráng miệng và đồ uống Việt Nam.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Chè chuối cốt dừa', 'content' => 'Món chè ngọt ngào, béo ngậy.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Bánh flan mềm mịn', 'content' => 'Công thức bánh flan truyền thống.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Nước sâm và trà đá', 'content' => 'Cách pha chế các loại đồ uống giải khát.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Cà phê sữa đá chuẩn vị Việt', 'content' => 'Bí quyết pha cà phê sữa đá thơm ngon.', 'sortOrder' => 4],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Tập Luyện Tại Nhà để Xây Dựng Sức Mạnh Toàn Thân',
            'description' => 'Chương trình tập luyện toàn diện, không cần dụng cụ phức tạp, giúp bạn xây dựng sức mạnh cơ bắp, tăng cường sức bền và cải thiện vóc dáng ngay tại nhà.',
            'price' => 350000.00,
            'categoryIds' => [77, 73], // Tập luyện thể hình, Sức khỏe & Thể hình
            'requirements' => [
                'Không yêu cầu kinh nghiệm tập luyện trước đó.',
                'Không gian đủ rộng để thực hiện các động tác.',
                'Một tấm thảm tập (tùy chọn).',
            ],
            'objectives' => [
                'Nắm vững kỹ thuật thực hiện các bài tập bodyweight cơ bản một cách an toàn.',
                'Tăng cường sức mạnh cơ bắp ở các nhóm cơ chính (ngực, vai, tay, lưng, chân, bụng).',
                'Cải thiện sức bền tim mạch và khả năng chịu đựng của cơ thể.',
                'Xây dựng thói quen tập luyện đều đặn và duy trì lối sống năng động.',
                'Hiểu các nguyên tắc cơ bản về dinh dưỡng để hỗ trợ mục tiêu tập luyện.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu và Nguyên tắc tập luyện tại nhà',
                    'description' => 'Làm quen với chương trình, lợi ích và các nguyên tắc an toàn khi tập luyện.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Lợi ích của tập luyện tại nhà và cách bắt đầu', 'content' => 'Ưu điểm, cách sắp xếp thời gian.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Nguyên tắc an toàn và khởi động trước tập', 'content' => 'Tránh chấn thương, các bài khởi động cơ bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Hít thở đúng cách trong khi tập luyện', 'content' => 'Kỹ thuật thở giúp tối ưu hiệu suất và phục hồi.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các loại hình tập luyện (Sức mạnh, Cardio, Linh hoạt)', 'content' => 'Phân biệt và kết hợp các loại hình tập.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Bài tập cho phần thân trên',
                    'description' => 'Tập trung xây dựng sức mạnh cho ngực, vai và tay.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Chống đẩy (Push-ups) và các biến thể', 'content' => 'Kỹ thuật đúng, từ dễ đến khó.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Động tác kéo (Bodyweight Rows) với ghế/bàn', 'content' => 'Tăng cường sức mạnh lưng và bắp tay.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Nhúng xà kép (Dips) với ghế', 'content' => 'Phát triển cơ tay sau và vai.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Đẩy vai (Pike Push-ups) và các bài tập vai khác', 'content' => 'Xây dựng sức mạnh vai.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Bài tập tay không cần dụng cụ', 'content' => 'Tập bắp tay và cẳng tay.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Bài tập cho phần thân dưới',
                    'description' => 'Tăng cường sức mạnh và sức bền cho chân và mông.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Squats (Gánh tạ không tạ) và biến thể', 'content' => 'Kỹ thuật squats đúng, từ cơ bản đến nâng cao.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Lunges (Chùng chân) và các biến thể', 'content' => 'Tập luyện từng chân, cải thiện thăng bằng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Glute Bridges (Cầu mông) và Hip Thrusts', 'content' => 'Tập trung vào cơ mông và gân kheo.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Calf Raises (Nhón gót) và các bài tập bắp chân', 'content' => 'Phát triển cơ bắp chân.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Nhảy dây và các bài tập plyometrics cơ bản', 'content' => 'Tăng cường sức bật và sức bền.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Bài tập cho cơ bụng và cốt lõi',
                    'description' => 'Xây dựng cơ bụng săn chắc và tăng cường sức mạnh cốt lõi.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Plank và các biến thể (Side Plank, Elbow Plank)', 'content' => 'Tập luyện toàn diện cơ cốt lõi.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Crunches và Sit-ups', 'content' => 'Các bài tập cơ bụng trên.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Leg Raises và Reverse Crunches', 'content' => 'Tập trung vào cơ bụng dưới.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Russian Twists và Bicycle Crunches', 'content' => 'Tập luyện cơ bụng xiên.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Xây dựng lịch trình tập luyện và phục hồi',
                    'description' => 'Cách sắp xếp các buổi tập và đảm bảo cơ thể được phục hồi đầy đủ.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Lập kế hoạch tập luyện hàng tuần', 'content' => 'Phân chia các nhóm cơ, tần suất tập.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Phục hồi sau tập: Kéo giãn và lăn foam roller', 'content' => 'Giảm đau nhức cơ bắp, tăng linh hoạt.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Tầm quan trọng của giấc ngủ và nghỉ ngơi', 'content' => 'Giấc ngủ ảnh hưởng đến hiệu suất tập luyện.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Theo dõi tiến độ và điều chỉnh chương trình', 'content' => 'Ghi nhật ký tập luyện, điều chỉnh khi cần.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Dinh dưỡng và Lối sống lành mạnh',
                    'description' => 'Hiểu về dinh dưỡng để tối ưu hóa kết quả tập luyện và sức khỏe tổng thể.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Nguyên tắc dinh dưỡng cơ bản cho người tập luyện', 'content' => 'Macro và micro nutrients, tầm quan trọng của protein.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Lên kế hoạch bữa ăn đơn giản tại nhà', 'content' => 'Các công thức ăn uống lành mạnh, dễ làm.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Uống đủ nước và tầm quan trọng của hydrat hóa', 'content' => 'Lợi ích của việc uống đủ nước.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Quản lý căng thẳng và duy trì động lực', 'content' => 'Các phương pháp giảm stress và giữ vững tinh thần tập luyện.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để duy trì lối sống năng động lâu dài', 'content' => 'Biến tập luyện thành một phần của cuộc sống.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Thanh Nhạc Cơ Bản: Hát Đúng Cao Độ và Cảm Xúc',
            'description' => 'Khóa học dành cho những ai yêu thích ca hát, muốn cải thiện giọng hát, hát đúng cao độ, kiểm soát hơi thở và thể hiện cảm xúc qua bài hát.',
            'price' => 420000.00,
            'categoryIds' => [106, 69], // Thanh nhạc, Âm nhạc
            'requirements' => [
                'Đam mê ca hát và mong muốn cải thiện giọng hát.',
                'Không yêu cầu kinh nghiệm thanh nhạc trước đó.',
                'Có thiết bị ghi âm (điện thoại, máy tính) để tự luyện tập.',
            ],
            'objectives' => [
                'Hiểu cấu tạo và chức năng của bộ máy phát âm.',
                'Thực hiện các bài tập khởi động giọng và luyện thanh hiệu quả.',
                'Kiểm soát hơi thở khi hát (hơi thở bụng).',
                'Hát đúng cao độ và tiết tấu của bài hát.',
                'Phát âm rõ ràng và truyền cảm khi hát.',
                'Thể hiện cảm xúc và phong cách cá nhân qua bài hát.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Thanh nhạc và Cơ chế giọng hát',
                    'description' => 'Tìm hiểu về giọng hát con người và cách nó hoạt động.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Thanh nhạc là gì? Lợi ích của việc luyện thanh', 'content' => 'Tổng quan về bộ môn thanh nhạc và những giá trị mang lại.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cấu tạo bộ máy phát âm: Thanh quản, dây thanh, khoang cộng hưởng', 'content' => 'Hiểu về các bộ phận tạo ra âm thanh.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Phân loại giọng hát (Soprano, Alto, Tenor, Bass)', 'content' => 'Tìm hiểu về các loại giọng và cách xác định giọng của bạn.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Thói quen tốt và xấu ảnh hưởng đến giọng hát', 'content' => 'Những điều nên và không nên làm để bảo vệ giọng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Hơi thở trong ca hát',
                    'description' => 'Nền tảng quan trọng nhất của ca hát: kiểm soát hơi thở.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tầm quan trọng của hơi thở bụng (Diaphragmatic Breathing)', 'content' => 'Tại sao cần thở bụng khi hát và cách thực hiện.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Các bài tập luyện hơi thở sâu và đều', 'content' => 'Thực hành hít vào, giữ hơi, thở ra có kiểm soát.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Kiểm soát hơi thở khi hát nốt dài và câu dài', 'content' => 'Kỹ thuật giữ hơi và phân phối hơi hợp lý.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Luyện tập hơi thở với các bài hát đơn giản', 'content' => 'Áp dụng kỹ thuật hơi thở vào thực tế.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Khởi động giọng và Luyện thanh cơ bản',
                    'description' => 'Các bài tập giúp làm ấm và linh hoạt giọng hát.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Khởi động cơ mặt và môi', 'content' => 'Các động tác giúp thư giãn và làm ấm các cơ liên quan.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Bài tập ngân nga (Humming) và rung môi (Lip Trills)', 'content' => 'Giúp thư giãn dây thanh và cảm nhận rung động.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Luyện thanh với các nguyên âm (A, E, I, O, U)', 'content' => 'Tập trung vào độ vang và rõ ràng của nguyên âm.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Luyện thanh với các quãng cơ bản (quãng 3, quãng 5)', 'content' => 'Tập hát đúng cao độ các quãng nhạc.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Luyện thanh với các bài tập scale (âm giai)', 'content' => 'Tăng cường sự linh hoạt và độ chính xác cao độ.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Hát đúng cao độ và Tiết tấu',
                    'description' => 'Phát triển khả năng nghe và hát đúng nốt, đúng nhịp.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Luyện nghe cao độ với đàn Piano/Keyboard', 'content' => 'Tập nhận biết và hát lại các nốt nhạc.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Hát theo nhạc và giữ nhịp với Metronome', 'content' => 'Luyện tập sự ổn định về tiết tấu.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Hát các bài hát có giai điệu đơn giản', 'content' => 'Áp dụng kỹ năng cao độ và tiết tấu vào bài hát.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Xử lý các nốt cao và nốt thấp', 'content' => 'Kỹ thuật hát các nốt ở biên độ rộng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Phát âm và Truyền cảm khi hát',
                    'description' => 'Làm cho lời hát rõ ràng, dễ hiểu và chạm đến cảm xúc người nghe.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Phát âm rõ ràng các phụ âm và nguyên âm tiếng Việt', 'content' => 'Luyện tập để lời hát không bị nuốt chữ.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Nhả chữ và luyến láy trong ca hát', 'content' => 'Tạo điểm nhấn và sự mềm mại cho câu hát.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Thể hiện cảm xúc qua giọng hát', 'content' => 'Phân tích lời bài hát và truyền tải cảm xúc phù hợp.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Kỹ thuật rung (Vibrato) và lướt (Glissando) cơ bản', 'content' => 'Làm đẹp và tăng tính biểu cảm cho giọng hát.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Luyện tập bài hát hoàn chỉnh và Biểu diễn',
                    'description' => 'Tổng hợp các kỹ năng đã học để trình bày một bài hát hoàn chỉnh.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Chọn bài hát phù hợp với chất giọng và trình độ', 'content' => 'Hướng dẫn cách chọn bài để phát huy tối đa.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Phân tích bài hát: Cấu trúc, giai điệu, lời ca', 'content' => 'Hiểu sâu về bài hát trước khi thể hiện.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Luyện tập toàn bộ bài hát với nhạc nền', 'content' => 'Thực hành từ đầu đến cuối bài hát.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Tự thu âm và đánh giá giọng hát', 'content' => 'Sử dụng công cụ ghi âm để tự cải thiện.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên khi biểu diễn và duy trì giọng hát', 'content' => 'Tự tin trên sân khấu và chăm sóc giọng hát lâu dài.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Tiếng Pháp Cơ Bản cho Người Mới Bắt Đầu',
            'description' => 'Khóa học giới thiệu tiếng Pháp từ con số 0, giúp bạn nắm vững ngữ pháp, từ vựng và các tình huống giao tiếp cơ bản để tự tin trò chuyện.',
            'price' => 480000.00,
            'categoryIds' => [89, 84], // Tiếng Pháp, Ngoại ngữ
            'requirements' => [
                'Không yêu cầu kiến thức tiếng Pháp trước đó.',
                'Sự kiên trì và hứng thú với ngôn ngữ và văn hóa Pháp.',
            ],
            'objectives' => [
                'Nắm vững bảng chữ cái và cách phát âm tiếng Pháp cơ bản.',
                'Giới thiệu bản thân, hỏi và trả lời về thông tin cá nhân.',
                'Giao tiếp trong các tình huống hàng ngày như mua sắm, gọi món ăn, hỏi đường.',
                'Hiểu và sử dụng các cấu trúc ngữ pháp A1 cơ bản.',
                'Mở rộng vốn từ vựng về các chủ đề quen thuộc.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Bảng chữ cái, Phát âm và Chào hỏi',
                    'description' => 'Làm quen với âm thanh và các cụm từ giao tiếp đầu tiên.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Bảng chữ cái tiếng Pháp và các nguyên tắc phát âm', 'content' => 'Học cách đọc các chữ cái và các quy tắc phát âm cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các âm đặc biệt (phụ âm, nguyên âm kép)', 'content' => 'Luyện tập các âm khó như "r", "u", "eu", "ou".', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Chào hỏi và giới thiệu bản thân (Bonjour, Je m\'appelle...)', 'content' => 'Các câu chào hỏi, giới thiệu tên, quốc tịch.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các câu xã giao cơ bản (Merci, S\'il vous plaît...)', 'content' => 'Cảm ơn, xin lỗi, làm ơn, tạm biệt.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Đại từ nhân xưng và Động từ Être/Avoir',
                    'description' => 'Nắm vững các đại từ và hai động từ quan trọng nhất trong tiếng Pháp.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Đại từ nhân xưng (Je, Tu, Il, Elle, Nous, Vous, Ils, Elles)', 'content' => 'Cách sử dụng các đại từ nhân xưng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Động từ "être" (to be) và cách chia', 'content' => 'Chia động từ "être" ở thì hiện tại và các ví dụ.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Động từ "avoir" (to have) và cách chia', 'content' => 'Chia động từ "avoir" ở thì hiện tại và các ví dụ.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Sử dụng "être" và "avoir" trong câu giới thiệu', 'content' => 'Thực hành giới thiệu nghề nghiệp, tuổi tác, cảm xúc.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Hỏi và trả lời về thông tin cá nhân', 'content' => 'Comment vous appelez-vous? Quel âge avez-vous?', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Danh từ, Mạo từ và Tính từ',
                    'description' => 'Hiểu về giống, số của danh từ và cách sử dụng mạo từ, tính từ.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Giống (masculin/féminin) của danh từ', 'content' => 'Quy tắc xác định giống và các trường hợp đặc biệt.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Mạo từ xác định (le, la, l\', les) và không xác định (un, une, des)', 'content' => 'Cách sử dụng mạo từ trong các tình huống khác nhau.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Số nhiều của danh từ và tính từ', 'content' => 'Quy tắc chuyển danh từ và tính từ sang số nhiều.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Các tính từ miêu tả cơ bản (grand, petit, beau...)', 'content' => 'Học các tính từ phổ biến và vị trí của chúng trong câu.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Hỏi và trả lời về đồ vật, màu sắc', 'content' => 'Qu\'est-ce que c\'est? Quelle couleur est-ce?', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Động từ nhóm 1 (-er) và Thể phủ định/nghi vấn',
                    'description' => 'Học cách chia động từ nhóm 1 và đặt câu hỏi, câu phủ định.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Cách chia động từ nhóm 1 (-er) ở thì hiện tại', 'content' => 'Parler, Manger, Aimer...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Đặt câu phủ định với "ne...pas"', 'content' => 'Je ne parle pas français.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Đặt câu hỏi (Est-ce que, Inversion, Intonation)', 'content' => 'Các cách đặt câu hỏi trong tiếng Pháp.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Từ vựng về gia đình và bạn bè', 'content' => 'Mère, père, frère, sœur, ami...', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Nói về sở thích và hoạt động hàng ngày', 'content' => 'J\'aime lire, Je fais du sport...', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Giao tiếp trong các tình huống thực tế',
                    'description' => 'Thực hành tiếng Pháp trong các tình huống hàng ngày.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Mua sắm tại cửa hàng (hỏi giá, số lượng)', 'content' => 'Combien ça coûte? Je voudrais...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Gọi món ăn tại nhà hàng và quán cà phê', 'content' => 'La carte, Je prends..., L\'addition s\'il vous plaît.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Hỏi đường và chỉ đường', 'content' => 'Où est...? Tournez à droite/gauche.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đặt phòng khách sạn và các dịch vụ du lịch', 'content' => 'Je voudrais réserver une chambre...', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Nói về thời tiết và các mùa', 'content' => 'Il fait beau, Il pleut...', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Văn hóa Pháp và Lời khuyên học tập',
                    'description' => 'Tìm hiểu về văn hóa Pháp và các mẹo để học tiếng Pháp hiệu quả.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Giới thiệu về Pháp và các thành phố lớn', 'content' => 'Paris, Lyon, Marseille...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Các ngày lễ và phong tục truyền thống của Pháp', 'content' => 'Noël, Fête Nationale...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Ẩm thực và rượu vang Pháp', 'content' => 'Các món ăn nổi tiếng và văn hóa rượu vang.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Các nguồn tài liệu học tiếng Pháp hiệu quả', 'content' => 'Sách, ứng dụng, phim ảnh, âm nhạc.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để duy trì và phát triển tiếng Pháp', 'content' => 'Thực hành thường xuyên, tìm bạn bè bản xứ.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Chạy Bộ Cơ Bản cho Sức Khỏe và Giảm Cân',
            'description' => 'Khóa học toàn diện dành cho người mới bắt đầu chạy bộ, hướng dẫn từ những bước đi đầu tiên đến việc hoàn thành cự ly 5km một cách an toàn và hiệu quả.',
            'price' => 250000.00,
            'categoryIds' => [77, 73], // Tập luyện thể hình, Sức khỏe & Thể hình
            'requirements' => [
                'Sức khỏe tổng quát tốt, không có vấn đề về tim mạch hoặc xương khớp nghiêm trọng.',
                'Một đôi giày chạy bộ phù hợp.',
                'Sự kiên trì và quyết tâm.',
            ],
            'objectives' => [
                'Nắm vững kỹ thuật chạy bộ đúng cách để tránh chấn thương.',
                'Xây dựng sức bền và khả năng chịu đựng của cơ thể.',
                'Hoàn thành cự ly 5km một cách thoải mái và an toàn.',
                'Đốt cháy calo hiệu quả và hỗ trợ quá trình giảm cân.',
                'Xây dựng thói quen chạy bộ đều đặn và duy trì lối sống năng động.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Chạy bộ và Chuẩn bị',
                    'description' => 'Tổng quan về chạy bộ, lợi ích và những điều cần chuẩn bị trước khi bắt đầu.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Chạy bộ là gì? Lợi ích sức khỏe và tinh thần', 'content' => 'Khám phá những giá trị mà chạy bộ mang lại.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Chọn giày chạy bộ phù hợp và trang phục', 'content' => 'Hướng dẫn chọn giày đúng loại, quần áo thoải mái.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Kiểm tra sức khỏe và lắng nghe cơ thể', 'content' => 'Tầm quan trọng của việc kiểm tra y tế và nhận biết tín hiệu cơ thể.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Lập mục tiêu và nhật ký chạy bộ', 'content' => 'Cách đặt mục tiêu SMART và theo dõi tiến độ.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Kỹ thuật chạy bộ đúng cách',
                    'description' => 'Nền tảng để chạy bộ hiệu quả và tránh chấn thương.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tư thế chạy bộ chuẩn (đầu, vai, hông, chân)', 'content' => 'Hướng dẫn chi tiết từng bộ phận cơ thể khi chạy.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Tiếp đất đúng cách (giữa bàn chân)', 'content' => 'Tránh tiếp đất bằng gót chân hoặc mũi chân.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Đánh tay và nhịp điệu bước chân', 'content' => 'Phối hợp tay và chân để chạy hiệu quả.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Hít thở khi chạy bộ (hít sâu, thở đều)', 'content' => 'Kỹ thuật thở giúp duy trì năng lượng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Chương trình tập luyện từ đi bộ đến chạy bộ',
                    'description' => 'Chương trình 8 tuần giúp bạn chuyển từ đi bộ sang chạy bộ liên tục.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Tuần 1-2: Đi bộ nhanh và xen kẽ chạy bộ ngắn', 'content' => 'Làm quen với cường độ, xây dựng nền tảng.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tuần 3-4: Tăng dần thời gian chạy, giảm thời gian đi bộ', 'content' => 'Thử thách bản thân với quãng đường dài hơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Tuần 5-6: Chạy liên tục và tăng tốc độ', 'content' => 'Duy trì tốc độ ổn định và tăng cường sức bền.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Tuần 7-8: Chạy 5km liên tục và chuẩn bị cho mục tiêu mới', 'content' => 'Hoàn thành mục tiêu 5km và lên kế hoạch tiếp theo.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Lịch trình tập luyện mẫu hàng tuần', 'content' => 'Gợi ý lịch tập cho các ngày trong tuần.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Khởi động, Giãn cơ và Phục hồi',
                    'description' => 'Các bài tập giúp cơ thể sẵn sàng trước và phục hồi sau khi chạy.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các bài tập khởi động động (Dynamic Warm-up) trước chạy', 'content' => 'Làm nóng cơ bắp và khớp.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các bài tập giãn cơ tĩnh (Static Stretching) sau chạy', 'content' => 'Giảm căng cơ và tăng linh hoạt.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Phục hồi tích cực và nghỉ ngơi', 'content' => 'Đi bộ nhẹ nhàng, ngủ đủ giấc.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Xử lý các cơn đau thường gặp (đau bắp chân, đau đầu gối)', 'content' => 'Cách phòng ngừa và điều trị các chấn thương nhẹ.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Dinh dưỡng và Hydrat hóa cho người chạy bộ',
                    'description' => 'Chế độ ăn uống và lượng nước cần thiết để tối ưu hiệu suất chạy bộ.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Các nhóm thực phẩm cần thiết cho người chạy bộ', 'content' => 'Carbs, protein, chất béo, vitamin và khoáng chất.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Bữa ăn trước và sau khi chạy', 'content' => 'Nên ăn gì và khi nào để có năng lượng và phục hồi.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Tầm quan trọng của nước và điện giải', 'content' => 'Uống đủ nước và bổ sung điện giải khi cần.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Lên kế hoạch bữa ăn đơn giản cho người bận rộn', 'content' => 'Các công thức nhanh gọn, bổ dưỡng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Duy trì động lực và Chạy bộ đường dài',
                    'description' => 'Giữ vững tinh thần và chuẩn bị cho những thử thách chạy bộ lớn hơn.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Vượt qua sự nản lòng và duy trì động lực', 'content' => 'Các mẹo để không bỏ cuộc.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Chạy bộ cùng bạn bè hoặc tham gia câu lạc bộ', 'content' => 'Lợi ích của việc chạy theo nhóm.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chuẩn bị cho các giải chạy 5km/10km', 'content' => 'Các bước cần thiết trước khi tham gia giải đấu.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Chạy bộ trong các điều kiện thời tiết khác nhau', 'content' => 'Mẹo chạy trong mưa, nắng nóng, lạnh.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để biến chạy bộ thành lối sống', 'content' => 'Duy trì niềm vui và đam mê với chạy bộ.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Làm Bánh Ngọt Cơ Bản Tại Nhà',
            'description' => 'Khóa học hướng dẫn bạn các kỹ thuật làm bánh ngọt cơ bản, từ bánh bông lan, cupcake đến các loại kem trang trí, giúp bạn tự tay tạo ra những chiếc bánh thơm ngon.',
            'price' => 380000.00,
            'categoryIds' => [63], // Phát triển cá nhân (kỹ năng sống)
            'requirements' => [
                'Có lò nướng gia đình.',
                'Các dụng cụ làm bánh cơ bản (cân, bát, phới lồng, khuôn...).',
                'Sự tỉ mỉ và kiên nhẫn.',
            ],
            'objectives' => [
                'Nắm vững các nguyên liệu cơ bản và vai trò của chúng trong làm bánh.',
                'Thực hiện thành công các công thức bánh bông lan, cupcake, bánh quy.',
                'Học cách làm các loại kem trang trí đơn giản (kem bơ, kem tươi).',
                'Hiểu các kỹ thuật đánh trứng, trộn bột, nướng bánh đúng cách.',
                'Tự tin sáng tạo và trang trí bánh theo ý muốn.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Làm bánh và Nguyên liệu cơ bản',
                    'description' => 'Làm quen với thế giới làm bánh và các thành phần không thể thiếu.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Làm bánh là gì? Các loại bánh cơ bản', 'content' => 'Phân loại bánh ngọt, bánh mì, bánh mặn.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các nguyên liệu thiết yếu (bột mì, trứng, đường, bơ, sữa)', 'content' => 'Vai trò và cách chọn lựa từng nguyên liệu.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Dụng cụ làm bánh cần thiết cho người mới bắt đầu', 'content' => 'Giới thiệu các dụng cụ cơ bản và cách sử dụng.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: An toàn vệ sinh và bảo quản nguyên liệu', 'content' => 'Đảm bảo vệ sinh trong quá trình làm bánh.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Kỹ thuật cơ bản trong làm bánh',
                    'description' => 'Nắm vững các kỹ thuật nền tảng để làm bánh thành công.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Kỹ thuật cân đong nguyên liệu chính xác', 'content' => 'Tầm quan trọng của độ chính xác trong làm bánh.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Kỹ thuật đánh trứng (đánh bông, đánh tan)', 'content' => 'Cách đánh trứng đạt độ bông cần thiết.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Kỹ thuật trộn bột (fold, cut and fold)', 'content' => 'Trộn bột đúng cách để bánh không bị chai.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Điều chỉnh nhiệt độ lò nướng và thời gian nướng', 'content' => 'Kiểm soát nhiệt độ để bánh chín đều.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Kiểm tra bánh chín và cách làm nguội bánh', 'content' => 'Dấu hiệu bánh chín và cách làm nguội bánh đúng cách.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Bánh bông lan và Cupcake',
                    'description' => 'Học cách làm bánh bông lan mềm xốp và các loại cupcake hấp dẫn.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Bánh bông lan cơ bản (Sponge Cake)', 'content' => 'Công thức bánh bông lan truyền thống, mềm xốp.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Các lỗi thường gặp khi làm bánh bông lan và cách khắc phục', 'content' => 'Bánh xẹp, chai, khô...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Cupcake vani và chocolate', 'content' => 'Công thức làm cupcake đơn giản, dễ thành công.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Trang trí cupcake cơ bản với kem bơ', 'content' => 'Cách làm kem bơ và bắt kem đơn giản.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Bánh quy và Cookies',
                    'description' => 'Tự tay làm các loại bánh quy giòn tan, thơm ngon.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Bánh quy bơ giòn tan', 'content' => 'Công thức bánh quy bơ truyền thống.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Cookies chocolate chip mềm dẻo', 'content' => 'Bí quyết làm cookies mềm và có độ dẻo.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Bánh quy gừng (Gingerbread Cookies)', 'content' => 'Công thức bánh quy gừng thơm lừng.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Trang trí bánh quy với icing đường', 'content' => 'Cách làm icing và trang trí bánh quy.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Các loại kem và Kỹ thuật trang trí',
                    'description' => 'Học cách làm các loại kem phổ biến và kỹ thuật trang trí bánh.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Kem tươi (Whipped Cream) và cách đánh bông', 'content' => 'Bí quyết đánh kem tươi bông và giữ form.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Kem bơ (Buttercream) và các biến thể', 'content' => 'Cách làm kem bơ mịn màng, dễ bắt.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Kem phô mai (Cream Cheese Frosting)', 'content' => 'Công thức kem phô mai chua ngọt.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Kỹ thuật trét kem cơ bản lên bánh', 'content' => 'Cách trét kem đều và mịn.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Sử dụng đui bắt kem và các kiểu bắt đơn giản', 'content' => 'Làm quen với các loại đui và tạo hình cơ bản.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Sáng tạo và Phát triển kỹ năng làm bánh',
                    'description' => 'Khuyến khích sự sáng tạo và hướng dẫn cách tự học hỏi thêm.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Kết hợp hương vị và nguyên liệu mới', 'content' => 'Thử nghiệm các hương vị độc đáo.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Chuyển đổi công thức và điều chỉnh định lượng', 'content' => 'Cách điều chỉnh công thức cho phù hợp.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tìm kiếm nguồn cảm hứng và học hỏi từ cộng đồng', 'content' => 'Các trang web, sách, hội nhóm làm bánh.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Bảo quản bánh và xử lý các lỗi thường gặp', 'content' => 'Cách bảo quản bánh tươi lâu và sửa chữa lỗi.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để trở thành một thợ làm bánh giỏi', 'content' => 'Sự kiên trì, đam mê và thực hành.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Dinh Dưỡng Cân Bằng và Lên Kế Hoạch Bữa Ăn Khỏe Mạnh',
            'description' => 'Khóa học này cung cấp kiến thức nền tảng về dinh dưỡng, giúp bạn hiểu rõ các nhóm chất, cách đọc nhãn mác thực phẩm và lên kế hoạch bữa ăn phù hợp với mục tiêu sức khỏe cá nhân.',
            'price' => 320000.00,
            'categoryIds' => [76, 73], // Dinh dưỡng, Sức khỏe & Thể hình
            'requirements' => [
                'Không yêu cầu kiến thức y tế hoặc dinh dưỡng chuyên sâu.',
                'Sự quan tâm đến sức khỏe và lối sống lành mạnh.',
            ],
            'objectives' => [
                'Hiểu rõ vai trò của các nhóm chất dinh dưỡng đa lượng và vi lượng.',
                'Biết cách đọc và phân tích nhãn mác thực phẩm một cách thông minh.',
                'Xây dựng kế hoạch bữa ăn cân bằng, phù hợp với nhu cầu calo và mục tiêu cá nhân (giảm cân, tăng cân, duy trì).',
                'Phân biệt thực phẩm tốt và không tốt cho sức khỏe.',
                'Nắm vững các nguyên tắc ăn uống lành mạnh và bền vững.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Dinh dưỡng và Nền tảng sức khỏe',
                    'description' => 'Tổng quan về dinh dưỡng, tầm quan trọng và các khái niệm cơ bản.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Dinh dưỡng là gì? Vai trò của dinh dưỡng với sức khỏe', 'content' => 'Định nghĩa, tầm quan trọng của dinh dưỡng toàn diện.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các yếu tố ảnh hưởng đến nhu cầu dinh dưỡng cá nhân', 'content' => 'Tuổi tác, giới tính, mức độ hoạt động, tình trạng sức khỏe.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Tháp dinh dưỡng và khẩu phần ăn khuyến nghị', 'content' => 'Hướng dẫn về các nhóm thực phẩm và lượng ăn phù hợp.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Mối liên hệ giữa dinh dưỡng và bệnh tật', 'content' => 'Hiểu về các bệnh liên quan đến chế độ ăn uống.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các nhóm chất dinh dưỡng đa lượng (Macronutrients)',
                    'description' => 'Tìm hiểu chi tiết về Carbohydrate, Protein và Chất béo.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Carbohydrate: Nguồn năng lượng chính', 'content' => 'Carbs tốt và xấu, chất xơ và vai trò của chúng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Protein: Xây dựng và sửa chữa cơ thể', 'content' => 'Nguồn protein động vật và thực vật, lượng protein cần thiết.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Chất béo: Năng lượng và chức năng quan trọng', 'content' => 'Chất béo tốt và xấu, Omega-3, Omega-6.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Tính toán nhu cầu calo và tỷ lệ Macronutrients', 'content' => 'Cách ước tính calo cần thiết và phân bổ các chất.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Nước: Chất dinh dưỡng bị lãng quên', 'content' => 'Tầm quan trọng của việc uống đủ nước và hydrat hóa.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Các nhóm chất dinh dưỡng vi lượng (Micronutrients) và Vitamin',
                    'description' => 'Khám phá vai trò của Vitamin và Khoáng chất đối với cơ thể.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Vitamin tan trong dầu (A, D, E, K)', 'content' => 'Nguồn thực phẩm và chức năng của từng loại vitamin.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Vitamin tan trong nước (B, C)', 'content' => 'Nguồn thực phẩm và chức năng của từng loại vitamin.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Các khoáng chất thiết yếu (Canxi, Sắt, Kẽm, Magie...)', 'content' => 'Vai trò và nguồn cung cấp các khoáng chất quan trọng.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Thực phẩm bổ sung: Khi nào cần và lưu ý', 'content' => 'Đánh giá sự cần thiết của việc bổ sung vitamin/khoáng chất.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Đọc nhãn mác thực phẩm và Lựa chọn thông minh',
                    'description' => 'Trang bị kỹ năng để đưa ra quyết định mua sắm thực phẩm tốt hơn.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các thuật ngữ trên nhãn mác (serving size, calo, đường, chất béo)', 'content' => 'Hiểu ý nghĩa của từng thông tin trên nhãn.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Phân biệt đường tự nhiên và đường thêm vào', 'content' => 'Nhận biết các loại đường ẩn trong thực phẩm.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Chất béo chuyển hóa (Trans Fat) và chất béo bão hòa', 'content' => 'Tác hại và cách tránh các loại chất béo không lành mạnh.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Lựa chọn thực phẩm tươi sống và thực phẩm chế biến', 'content' => 'Ưu tiên thực phẩm nguyên chất, hạn chế đồ ăn đóng gói.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Lên kế hoạch bữa ăn và Chuẩn bị thực phẩm',
                    'description' => 'Hướng dẫn cách xây dựng lịch trình bữa ăn và chuẩn bị đồ ăn hiệu quả.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Các bước lập kế hoạch bữa ăn hàng tuần', 'content' => 'Từ lên ý tưởng đến mua sắm và chuẩn bị.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chuẩn bị thực phẩm (Meal Prep) hiệu quả', 'content' => 'Các mẹo để tiết kiệm thời gian và công sức.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Các công thức bữa ăn sáng, trưa, tối cân bằng', 'content' => 'Gợi ý các món ăn dinh dưỡng, dễ làm.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Bữa ăn nhẹ (Snacks) lành mạnh và kiểm soát cơn đói', 'content' => 'Chọn lựa snack phù hợp để tránh ăn vặt không kiểm soát.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Ăn uống ngoài nhà và đi du lịch', 'content' => 'Cách duy trì chế độ ăn lành mạnh khi không ở nhà.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Dinh dưỡng cho các mục tiêu đặc biệt và Lối sống bền vững',
                    'description' => 'Áp dụng kiến thức dinh dưỡng cho các mục tiêu cụ thể và duy trì lâu dài.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Dinh dưỡng để giảm cân an toàn và hiệu quả', 'content' => 'Nguyên tắc thâm hụt calo, lựa chọn thực phẩm.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Dinh dưỡng để tăng cân và xây dựng cơ bắp', 'content' => 'Nguyên tắc thặng dư calo, protein và tập luyện.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Dinh dưỡng cho người ăn chay/thuần chay', 'content' => 'Đảm bảo đủ chất dinh dưỡng khi ăn chay.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Quản lý căng thẳng và ảnh hưởng đến ăn uống', 'content' => 'Mối liên hệ giữa stress và thói quen ăn uống.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Xây dựng lối sống dinh dưỡng lành mạnh bền vững', 'content' => 'Biến dinh dưỡng thành một phần tự nhiên của cuộc sống.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Piano Cơ Bản cho Người Lớn',
            'description' => 'Khóa học này được thiết kế đặc biệt cho người lớn muốn học piano từ đầu, tập trung vào việc đọc nốt, hợp âm và chơi các bài hát đơn giản một cách nhanh chóng.',
            'price' => 450000.00,
            'categoryIds' => [70, 69], // Nhạc cụ (Piano), Âm nhạc
            'requirements' => [
                'Có một cây đàn piano hoặc keyboard điện tử.',
                'Sự kiên nhẫn và mong muốn học hỏi.',
            ],
            'objectives' => [
                'Làm quen với bàn phím piano và vị trí các nốt nhạc.',
                'Đọc và hiểu các ký hiệu âm nhạc cơ bản trên khuông nhạc.',
                'Chơi thành thạo các hợp âm cơ bản (Major, Minor) và chuyển đổi giữa chúng.',
                'Chơi được các bài hát đơn giản bằng cả hai tay.',
                'Hiểu về nhịp điệu và cách giữ nhịp khi chơi.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Làm quen với Đàn Piano và Nốt nhạc',
                    'description' => 'Giới thiệu về đàn piano, vị trí các nốt và cách ngồi đúng.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Cấu tạo bàn phím Piano và các nhóm phím', 'content' => 'Tìm hiểu về các phím trắng, đen và cách chúng được sắp xếp.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Vị trí nốt Đô giữa (Middle C) và các nốt xung quanh', 'content' => 'Xác định nốt Đô giữa và làm quen với các nốt lân cận.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Tư thế ngồi và đặt tay đúng khi chơi Piano', 'content' => 'Hướng dẫn tư thế giúp chơi thoải mái và hiệu quả.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Làm quen với khuông nhạc và khóa Sol, khóa Fa', 'content' => 'Tìm hiểu về các ký hiệu trên khuông nhạc.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Đọc nốt và Nhịp điệu cơ bản',
                    'description' => 'Học cách đọc các nốt nhạc trên khuông và hiểu về nhịp điệu.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Các nốt trên khóa Sol (Tay phải)', 'content' => 'Đọc và chơi các nốt từ Đô đến Sol trên khóa Sol.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Các nốt trên khóa Fa (Tay trái)', 'content' => 'Đọc và chơi các nốt từ Đô đến Fa trên khóa Fa.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Giá trị nốt nhạc (tròn, trắng, đen, móc đơn)', 'content' => 'Hiểu về độ dài của các nốt nhạc.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Nhịp 4/4 và cách đếm nhịp', 'content' => 'Luyện tập đếm nhịp và giữ nhịp với Metronome.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Các bài tập đọc nốt và chơi tay phải/tay trái riêng biệt', 'content' => 'Thực hành đọc và chơi từng tay.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Hợp âm cơ bản và Chuyển hợp âm',
                    'description' => 'Nắm vững các hợp âm quan trọng để đệm hát hoặc chơi các bài đơn giản.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Hợp âm Trưởng (Major Chords): C, G, D, F, A, E', 'content' => 'Cách hình thành và chơi các hợp âm trưởng cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Hợp âm Thứ (Minor Chords): Am, Em, Dm', 'content' => 'Cách hình thành và chơi các hợp âm thứ cơ bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Luyện tập chuyển đổi hợp âm mượt mà', 'content' => 'Các bài tập giúp chuyển đổi hợp âm nhanh và không bị ngắt quãng.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Đệm các bài hát đơn giản với hợp âm tay trái', 'content' => 'Áp dụng hợp âm vào việc đệm các bài hát quen thuộc.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Vòng hòa âm cơ bản (I-IV-V-I)', 'content' => 'Hiểu về các vòng hòa âm phổ biến trong âm nhạc.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Chơi các bài hát đơn giản bằng hai tay',
                    'description' => 'Kết hợp tay phải chơi giai điệu và tay trái chơi hợp âm.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Bài tập phối hợp hai tay cơ bản', 'content' => 'Các bài tập giúp tay phải và tay trái phối hợp nhịp nhàng.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Chơi bài "Mary Had a Little Lamb" bằng hai tay', 'content' => 'Thực hành bài hát đầu tiên với cả hai tay.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Chơi bài "Ode to Joy" (Beethoven) bằng hai tay', 'content' => 'Một bài kinh điển khác để luyện tập.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Giới thiệu về Pedal (bàn đạp sustain)', 'content' => 'Cách sử dụng pedal để tạo hiệu ứng âm thanh.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Mở rộng kiến thức và kỹ thuật',
                    'description' => 'Khám phá thêm các khái niệm âm nhạc và kỹ thuật chơi.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Các dấu hóa (thăng, giáng, bình) và khóa nhạc', 'content' => 'Hiểu về các dấu hóa và cách chúng ảnh hưởng đến nốt nhạc.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Nhịp 3/4 và các bài hát có nhịp 3/4', 'content' => 'Làm quen với nhịp điệu Waltz.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Kỹ thuật Legato (nối liền) và Staccato (ngắt rời)', 'content' => 'Tạo sắc thái cho âm nhạc.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Luyện tập nghe nhạc và nhận biết hợp âm', 'content' => 'Phát triển tai nghe để tự đệm các bài hát.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Luyện tập và Phát triển đam mê',
                    'description' => 'Lời khuyên để duy trì việc học piano và khám phá thêm.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Lập kế hoạch luyện tập hàng ngày hiệu quả', 'content' => 'Cách phân bổ thời gian cho các bài tập.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Tìm kiếm sheet nhạc và tài liệu học tập online', 'content' => 'Các nguồn tài nguyên hữu ích cho người học piano.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Luyện tập với backing track và ứng dụng học nhạc', 'content' => 'Sử dụng công nghệ để hỗ trợ việc học.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Chơi các bài hát yêu thích và tự tin biểu diễn', 'content' => 'Áp dụng những gì đã học vào các bài hát bạn yêu thích.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để duy trì niềm vui với Piano', 'content' => 'Biến việc học thành một hành trình thú vị.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Tiếng Hàn Giao Tiếp Cơ Bản cho Người Đi Làm',
            'description' => 'Khóa học tập trung vào các tình huống giao tiếp tiếng Hàn thực tế trong môi trường công sở và đời sống hàng ngày, giúp bạn tự tin trò chuyện với đồng nghiệp, đối tác Hàn Quốc.',
            'price' => 520000.00,
            'categoryIds' => [87, 84], // Tiếng Hàn, Ngoại ngữ
            'requirements' => [
                'Không yêu cầu kiến thức tiếng Hàn trước đó.',
                'Sự kiên trì và hứng thú với văn hóa Hàn Quốc.',
            ],
            'objectives' => [
                'Đọc và viết thành thạo bảng chữ cái Hangeul.',
                'Giới thiệu bản thân, hỏi và trả lời về thông tin cá nhân cơ bản.',
                'Giao tiếp trong các tình huống công sở như chào hỏi, họp hành, trao đổi công việc.',
                'Hiểu và sử dụng các cấu trúc ngữ pháp Sơ cấp 1 (TOPIK 1) cơ bản.',
                'Mở rộng vốn từ vựng liên quan đến công việc và đời sống hàng ngày.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Bảng chữ cái Hangeul và Phát âm',
                    'description' => 'Làm quen với hệ thống chữ viết và cách phát âm chuẩn tiếng Hàn.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Giới thiệu Hangeul: Nguyên âm cơ bản', 'content' => 'Học cách viết và phát âm các nguyên âm đơn.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Giới thiệu Hangeul: Phụ âm cơ bản', 'content' => 'Học cách viết và phát âm các phụ âm đơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Ghép chữ và Quy tắc đọc nối, biến âm cơ bản', 'content' => 'Thực hành ghép các nguyên âm và phụ âm thành chữ, làm quen với quy tắc đọc.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Chào hỏi và các câu xã giao cơ bản', 'content' => 'Annyeonghaseyo, Gamsahamnida, Cheonmaneyo...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Giới thiệu bản thân và Thông tin cá nhân',
                    'description' => 'Học cách giới thiệu bản thân, hỏi và trả lời về thông tin cá nhân.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cấu trúc câu cơ bản: Noun + 입니다/입니까?', 'content' => 'Cách giới thiệu tên, nghề nghiệp, quốc tịch.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Đại từ nhân xưng (저, 저의, 당신, 우리...)', 'content' => 'Cách sử dụng các đại từ nhân xưng trong các tình huống.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Số đếm (Số thuần Hàn và số Hán Hàn)', 'content' => 'Học cách đếm số và ứng dụng trong tuổi tác, thời gian.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Hỏi và trả lời về tuổi tác, nghề nghiệp', 'content' => 'Myot sal-ieyo? Museun il-eul haseyo?', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Từ vựng về gia đình và các mối quan hệ', 'content' => 'Eomma, Appa, Oppa, Eonni...', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Ngữ pháp Sơ cấp 1 và Từ vựng hàng ngày',
                    'description' => 'Nắm vững các cấu trúc ngữ pháp và từ vựng thông dụng trong đời sống.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Tiểu từ chủ ngữ -이/가, tiểu từ tân ngữ -을/를', 'content' => 'Cách sử dụng các tiểu từ cơ bản trong câu.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Động từ và tính từ đuôi -아요/어요', 'content' => 'Cách chia động từ, tính từ ở thì hiện tại thân mật.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Tiểu từ địa điểm -에/에서 và -으로/로', 'content' => 'Diễn tả địa điểm và phương hướng.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Từ vựng về đồ vật, màu sắc, địa điểm', 'content' => 'Mở rộng vốn từ vựng về các chủ đề quen thuộc.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Hỏi và trả lời về vị trí, sự tồn tại', 'content' => 'Eodie isseoyo? Isseoyo/Eopseoyo.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giao tiếp trong môi trường công sở',
                    'description' => 'Các tình huống giao tiếp tiếng Hàn thường gặp ở nơi làm việc.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Chào hỏi đồng nghiệp và cấp trên (kính ngữ cơ bản)', 'content' => 'Cách chào hỏi và sử dụng kính ngữ phù hợp.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Trao đổi công việc đơn giản (요청하기 - yêu cầu)', 'content' => 'Làm ơn, hãy làm..., tôi muốn...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Gọi điện thoại và nhận tin nhắn công việc', 'content' => 'Các cụm từ dùng khi gọi, nhận điện thoại.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Thảo luận trong cuộc họp nhỏ và đưa ra ý kiến', 'content' => 'Cách đưa ra ý kiến, đồng ý, không đồng ý một cách lịch sự.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Viết email công việc ngắn gọn', 'content' => 'Cấu trúc email cơ bản và các cụm từ thông dụng.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Giao tiếp trong đời sống hàng ngày',
                    'description' => 'Thực hành tiếng Hàn trong các tình huống như mua sắm, ăn uống, đi lại.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Mua sắm tại cửa hàng tiện lợi/siêu thị', 'content' => 'Giá bao nhiêu? Tôi muốn cái này.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Gọi món ăn tại nhà hàng và quán cà phê', 'content' => 'Menu, cho tôi món này, ngon quá.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Hỏi đường và sử dụng phương tiện giao thông công cộng', 'content' => 'Ga tàu điện ngầm ở đâu? Đến đó bằng cách nào?', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đặt phòng khách sạn và các dịch vụ cơ bản', 'content' => 'Tôi muốn đặt phòng, có wifi không?', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Nói về thời tiết và các hoạt động giải trí', 'content' => 'Thời tiết hôm nay thế nào? Bạn làm gì vào cuối tuần?', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Văn hóa Hàn Quốc và Lời khuyên học tập',
                    'description' => 'Hiểu biết về văn hóa giúp bạn giao tiếp tự nhiên và tránh hiểu lầm.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Các nghi thức xã giao và phép lịch sự trong văn hóa Hàn', 'content' => 'Cách cúi chào, trao đổi danh thiếp.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Văn hóa ăn uống và các món ăn truyền thống', 'content' => 'Kimchi, Bulgogi, Bibimbap...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Các ngày lễ và phong tục truyền thống của Hàn Quốc', 'content' => 'Seollal, Chuseok...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Các nguồn tài liệu học tiếng Hàn hiệu quả', 'content' => 'Sách, ứng dụng, phim ảnh, K-pop.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để duy trì và phát triển tiếng Hàn', 'content' => 'Thực hành thường xuyên, tìm bạn bè bản xứ.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Lên Kế Hoạch Bữa Ăn Lành Mạnh và Tiết Kiệm',
            'description' => 'Học cách lập kế hoạch bữa ăn thông minh, chuẩn bị thực phẩm hiệu quả để ăn uống lành mạnh, tiết kiệm thời gian và chi phí cho gia đình bận rộn.',
            'price' => 280000.00,
            'categoryIds' => [76, 63], // Dinh dưỡng, Phát triển cá nhân
            'requirements' => [
                'Mong muốn ăn uống lành mạnh và tiết kiệm chi phí.',
                'Có kiến thức nấu ăn cơ bản.',
            ],
            'objectives' => [
                'Xây dựng kế hoạch bữa ăn hàng tuần/tháng phù hợp với nhu cầu dinh dưỡng và ngân sách.',
                'Nắm vững các nguyên tắc mua sắm thông minh tại siêu thị và chợ.',
                'Thực hiện các kỹ thuật sơ chế và bảo quản thực phẩm để kéo dài thời gian sử dụng.',
                'Chế biến các món ăn lành mạnh, ngon miệng và tiết kiệm thời gian.',
                'Giảm thiểu lãng phí thực phẩm trong gia đình.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tại sao cần lên kế hoạch bữa ăn?',
                    'description' => 'Khám phá lợi ích của việc lập kế hoạch bữa ăn và các yếu tố cần cân nhắc.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Lợi ích của việc lên kế hoạch bữa ăn (sức khỏe, tiền bạc, thời gian)', 'content' => 'Tiết kiệm chi phí, giảm căng thẳng, ăn uống lành mạnh hơn.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các yếu tố cần cân nhắc khi lên kế hoạch (ngân sách, thời gian, sở thích)', 'content' => 'Xác định mục tiêu và hạn chế của gia đình.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Nhu cầu dinh dưỡng cơ bản của gia đình', 'content' => 'Hiểu về calo, protein, carbs, chất béo cho từng thành viên.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các công cụ và ứng dụng hỗ trợ lập kế hoạch bữa ăn', 'content' => 'Giới thiệu các ứng dụng, mẫu kế hoạch.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Lập kế hoạch bữa ăn hàng tuần/tháng',
                    'description' => 'Hướng dẫn từng bước để xây dựng một kế hoạch bữa ăn chi tiết và hiệu quả.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Xác định chủ đề bữa ăn và món ăn yêu thích của gia đình', 'content' => 'Lên danh sách các món ăn thường xuyên và món mới.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Lên thực đơn cho từng bữa (sáng, trưa, tối, ăn nhẹ)', 'content' => 'Đảm bảo sự đa dạng và cân bằng dinh dưỡng.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Lập danh sách mua sắm dựa trên thực đơn', 'content' => 'Tránh mua sắm tùy hứng, giảm lãng phí.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Tận dụng nguyên liệu có sẵn và leftovers', 'content' => 'Sáng tạo với đồ ăn còn lại, giảm lãng phí.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Điều chỉnh kế hoạch khi có sự thay đổi', 'content' => 'Linh hoạt thay đổi thực đơn khi cần thiết.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Mua sắm thông minh và Tiết kiệm chi phí',
                    'description' => 'Các mẹo để mua sắm hiệu quả, giảm chi phí mà vẫn đảm bảo chất lượng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Mua sắm theo mùa và tận dụng khuyến mãi', 'content' => 'Chọn thực phẩm tươi ngon, giá rẻ theo mùa.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Mua sắm tại chợ truyền thống và siêu thị', 'content' => 'Ưu nhược điểm của từng kênh mua sắm.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: So sánh giá và đọc nhãn mác thực phẩm', 'content' => 'Trở thành người tiêu dùng thông thái.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Mua sắm số lượng lớn và chia nhỏ bảo quản', 'content' => 'Tiết kiệm chi phí khi mua số lượng lớn.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Tránh các loại thực phẩm đắt tiền và không cần thiết', 'content' => 'Cắt giảm chi phí không cần thiết.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Sơ chế và Bảo quản thực phẩm hiệu quả',
                    'description' => 'Kỹ thuật giúp kéo dài thời gian sử dụng của thực phẩm và tiết kiệm thời gian nấu ăn.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Sơ chế rau củ quả và thịt cá trước khi cất trữ', 'content' => 'Rửa, cắt, thái, ướp gia vị.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các phương pháp bảo quản (đông lạnh, hút chân không, làm khô)', 'content' => 'Tối ưu hóa thời gian bảo quản.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Sắp xếp tủ lạnh và tủ đông khoa học', 'content' => 'Giúp dễ tìm kiếm và tránh lãng phí.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Sử dụng hộp đựng thực phẩm và túi zip lock', 'content' => 'Các dụng cụ bảo quản hiệu quả.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Các công thức bữa ăn nhanh, lành mạnh và tiết kiệm',
                    'description' => 'Gợi ý các món ăn dễ làm, ngon miệng, phù hợp với lịch trình bận rộn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Bữa sáng nhanh gọn và đủ chất (yến mạch, trứng, smoothie)', 'content' => 'Các lựa chọn bữa sáng bổ dưỡng, không tốn thời gian.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Các món salad và món trộn dễ làm cho bữa trưa', 'content' => 'Công thức salad tươi ngon, dễ mang đi.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Món ăn một chảo/nồi cho bữa tối (cơm rang, mì xào, súp)', 'content' => 'Giảm thời gian dọn dẹp sau khi nấu.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Các món ăn vặt lành mạnh tự làm tại nhà', 'content' => 'Hạt, trái cây, sữa chua, bánh quy yến mạch.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Tận dụng nồi chiên không dầu và lò vi sóng', 'content' => 'Nấu ăn nhanh và tiện lợi với các thiết bị hiện đại.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Duy trì thói quen và Lối sống bền vững',
                    'description' => 'Lời khuyên để duy trì kế hoạch bữa ăn và biến nó thành một phần của lối sống.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Vượt qua những rào cản khi lên kế hoạch bữa ăn', 'content' => 'Giải quyết các vấn đề thường gặp.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Chia sẻ công việc nấu ăn với các thành viên gia đình', 'content' => 'Cùng nhau tham gia vào quá trình chuẩn bị bữa ăn.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Đánh giá và điều chỉnh kế hoạch định kỳ', 'content' => 'Thường xuyên xem xét và cải thiện kế hoạch.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Giảm thiểu lãng phí thực phẩm trong gia đình', 'content' => 'Các mẹo để không bỏ phí đồ ăn.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Biến việc lên kế hoạch bữa ăn thành một thói quen tích cực', 'content' => 'Tận hưởng quá trình và kết quả.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Kỹ thuật Piano Nâng Cao: Biểu Cảm và Tốc Độ',
            'description' => 'Khóa học này đi sâu vào các kỹ thuật piano nâng cao như arpeggio, scale phức tạp, legato, staccato, và cách thể hiện cảm xúc sâu sắc hơn qua âm nhạc.',
            'price' => 780000.00,
            'categoryIds' => [70, 69], // Nhạc cụ (Piano), Âm nhạc
            'requirements' => [
                'Đã hoàn thành khóa học Piano Cơ Bản hoặc có kiến thức tương đương (đọc nốt, hợp âm cơ bản).',
                'Có một cây đàn piano hoặc keyboard điện tử.',
                'Sự kiên trì và sẵn sàng luyện tập chăm chỉ.',
            ],
            'objectives' => [
                'Nắm vững các kỹ thuật ngón nâng cao như arpeggio, scale phức tạp, trills, tremolos.',
                'Cải thiện tốc độ và sự linh hoạt của ngón tay.',
                'Kiểm soát sắc thái (dynamics) và biểu cảm âm nhạc một cách tinh tế.',
                'Hiểu và áp dụng các nguyên tắc lý thuyết âm nhạc nâng cao vào việc chơi piano.',
                'Phân tích và trình bày các tác phẩm piano cổ điển và hiện đại.',
                'Phát triển khả năng nghe và tự học các tác phẩm mới.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Ôn tập và Khởi động nâng cao',
                    'description' => 'Củng cố kiến thức cơ bản và làm quen với các bài tập khởi động chuyên sâu hơn.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Ôn tập các hợp âm và âm giai cơ bản', 'content' => 'Thực hành nhanh các hợp âm trưởng, thứ và âm giai chính.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các bài tập Hanon và Czerny cho ngón tay', 'content' => 'Luyện tập các bài tập kỹ thuật kinh điển để tăng cường sức mạnh và sự độc lập của ngón.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Khởi động cổ tay và cánh tay để tránh căng thẳng', 'content' => 'Các động tác thư giãn và làm ấm cơ bắp cần thiết.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Luyện tập với Metronome ở tốc độ tăng dần', 'content' => 'Sử dụng Metronome để cải thiện nhịp điệu và tốc độ.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Kỹ thuật Arpeggio và Scale phức tạp',
                    'description' => 'Học cách chơi các arpeggio và scale ở tốc độ cao và mượt mà.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Arpeggio trưởng và thứ ở các thế đảo', 'content' => 'Thực hành các arpeggio cơ bản và các biến thể của chúng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Scale hòa âm và giai điệu', 'content' => 'Luyện tập các âm giai thứ hòa âm và giai điệu.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Kỹ thuật Legato và Staccato nâng cao', 'content' => 'Kiểm soát độ liền mạch và ngắt quãng của âm thanh.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Bài tập chuyển ngón (Thumb Under) hiệu quả', 'content' => 'Kỹ thuật chuyển ngón cái dưới các ngón khác để chơi mượt mà.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Ứng dụng Arpeggio và Scale vào các đoạn nhạc', 'content' => 'Thực hành các đoạn nhạc có chứa arpeggio và scale.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Kiểm soát Sắc thái (Dynamics) và Biểu cảm',
                    'description' => 'Học cách thể hiện cảm xúc và cường độ âm thanh qua tiếng đàn.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các ký hiệu sắc thái (p, mf, f, crescendo, diminuendo)', 'content' => 'Hiểu ý nghĩa và cách thực hiện các ký hiệu sắc thái.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Sử dụng trọng lực và sức nặng của cánh tay', 'content' => 'Kỹ thuật tạo ra âm thanh đầy đặn và có chiều sâu.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Kỹ thuật Pedal (Sustain, Sostenuto, Una Corda) chuyên sâu', 'content' => 'Sử dụng pedal để tạo hiệu ứng âm thanh và duy trì âm vang.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Phân tích và thể hiện cảm xúc trong các tác phẩm', 'content' => 'Học cách truyền tải ý nghĩa của bản nhạc.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Luyện tập với các đoạn nhạc có sắc thái đa dạng', 'content' => 'Thực hành các bài tập đòi hỏi sự kiểm soát sắc thái.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Hòa âm và Phân tích tác phẩm',
                    'description' => 'Hiểu sâu hơn về cấu trúc hòa âm và cách phân tích các tác phẩm piano.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các hợp âm mở rộng (thứ 7, thứ 9, sus, add)', 'content' => 'Học cách hình thành và sử dụng các hợp âm phức tạp hơn.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Phân tích cấu trúc bài hát (form, chủ đề, phát triển)', 'content' => 'Hiểu về hình thức sonata, rondo, variation...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Nhận biết và sử dụng các vòng hòa âm nâng cao', 'content' => 'Các vòng hòa âm Jazz, Blues, Pop.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Đọc và hiểu các ký hiệu âm nhạc phức tạp', 'content' => 'Ký hiệu về tốc độ, biểu cảm, kỹ thuật.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Luyện tập các tác phẩm kinh điển và hiện đại',
                    'description' => 'Áp dụng các kỹ thuật đã học vào việc chơi các tác phẩm piano nổi tiếng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Các bài tập từ sách của Bach và Chopin', 'content' => 'Luyện tập các tác phẩm để phát triển kỹ thuật và cảm thụ.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chơi các Prelude và Nocturne đơn giản', 'content' => 'Thực hành các tác phẩm có giai điệu và cảm xúc.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Các tác phẩm piano hiện đại và Pop/Jazz', 'content' => 'Khám phá các phong cách âm nhạc khác nhau.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Kỹ thuật tập luyện từng đoạn và ghép nối', 'content' => 'Cách chia nhỏ bản nhạc để luyện tập hiệu quả.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Phát triển phong cách cá nhân và Biểu diễn',
                    'description' => 'Hoàn thiện kỹ năng và tự tin trình diễn trước khán giả.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Kỹ thuật ghi nhớ bản nhạc và chơi không cần nhìn nốt', 'content' => 'Các phương pháp ghi nhớ hiệu quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Cách xử lý lỗi và vượt qua sự lo lắng khi biểu diễn', 'content' => 'Những lời khuyên để tự tin trên sân khấu.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Phát triển phong cách chơi và cá tính âm nhạc', 'content' => 'Tìm kiếm tiếng nói riêng của bạn qua tiếng đàn.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Tự học các tác phẩm mới và tìm kiếm nguồn tài liệu', 'content' => 'Hướng dẫn cách tiếp tục học hỏi và phát triển.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để duy trì đam mê và tiến bộ lâu dài', 'content' => 'Biến piano thành một phần không thể thiếu của cuộc sống.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Tiếng Đức Cơ Bản cho Du Khách',
            'description' => 'Học tiếng Đức cơ bản để tự tin giao tiếp trong các tình huống du lịch tại Đức, Áo, Thụy Sĩ, từ chào hỏi, mua sắm đến hỏi đường và gọi món ăn.',
            'price' => 490000.00,
            'categoryIds' => [90, 84], // Tiếng Đức, Ngoại ngữ
            'requirements' => [
                'Không yêu cầu kiến thức tiếng Đức trước đó.',
                'Sự hứng thú với văn hóa và ngôn ngữ Đức.',
            ],
            'objectives' => [
                'Nắm vững cách phát âm bảng chữ cái và các âm đặc trưng của tiếng Đức.',
                'Thực hiện các câu chào hỏi, giới thiệu bản thân và người khác.',
                'Giao tiếp cơ bản trong các tình huống du lịch (khách sạn, nhà hàng, mua sắm, giao thông).',
                'Hiểu và sử dụng các cấu trúc ngữ pháp A1 cơ bản (chia động từ, danh từ, tính từ).',
                'Mở rộng vốn từ vựng về các chủ đề liên quan đến du lịch.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Bảng chữ cái, Phát âm và Chào hỏi',
                    'description' => 'Làm quen với âm thanh và các cụm từ giao tiếp đầu tiên trong tiếng Đức.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Bảng chữ cái tiếng Đức và các nguyên tắc phát âm', 'content' => 'Học cách đọc các chữ cái và các âm đặc trưng như "ch", "sch", "ei", "ie".', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các âm nguyên âm và phụ âm đôi', 'content' => 'Luyện tập các âm như "ä", "ö", "ü" và các âm kết hợp.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Chào hỏi và giới thiệu bản thân (Guten Tag, Ich heiße...)', 'content' => 'Các câu chào hỏi theo thời gian, giới thiệu tên, quốc tịch.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các câu xã giao cơ bản (Danke schön, Bitte schön...)', 'content' => 'Cảm ơn, xin lỗi, làm ơn, tạm biệt và các cách dùng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Đại từ nhân xưng và Động từ "sein" (to be)',
                    'description' => 'Nắm vững các đại từ và động từ "sein" - một trong những động từ quan trọng nhất.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Đại từ nhân xưng (ich, du, er, sie, es, wir, ihr, sie, Sie)', 'content' => 'Cách sử dụng các đại từ nhân xưng trong tiếng Đức.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Động từ "sein" (to be) và cách chia ở thì hiện tại', 'content' => 'Chia động từ "sein" và các ví dụ trong câu.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Giới thiệu nghề nghiệp và quốc tịch với "sein"', 'content' => 'Ich bin Student/in, Er ist Arzt.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Hỏi và trả lời về thông tin cá nhân (Wie heißen Sie? Woher kommen Sie?)', 'content' => 'Hỏi tên, tuổi, quê quán.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Số đếm từ 0 đến 20', 'content' => 'Học cách đếm số và ứng dụng trong các tình huống đơn giản.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Danh từ, Giống và Mạo từ',
                    'description' => 'Hiểu về giống của danh từ và cách sử dụng các loại mạo từ trong tiếng Đức.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Giống của danh từ (der, die, das) và số nhiều', 'content' => 'Quy tắc xác định giống và cách hình thành số nhiều của danh từ.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Mạo từ xác định (der, die, das) và không xác định (ein, eine)', 'content' => 'Cách sử dụng mạo từ trong các tình huống khác nhau.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Các trường hợp (Nominativ, Akkusativ) cơ bản', 'content' => 'Làm quen với khái niệm về các trường hợp trong tiếng Đức.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Từ vựng về đồ vật, màu sắc và gia đình', 'content' => 'Mở rộng vốn từ vựng về các chủ đề quen thuộc.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Hỏi và trả lời về đồ vật (Was ist das?)', 'content' => 'Thực hành hỏi và trả lời về các đồ vật xung quanh.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giao tiếp tại nhà hàng và mua sắm',
                    'description' => 'Thực hành các tình huống giao tiếp thực tế khi ăn uống và mua sắm tại Đức.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Đặt món ăn và đồ uống (Ich hätte gern..., Eine Cola bitte)', 'content' => 'Các cụm từ cần thiết khi gọi món trong nhà hàng.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Hỏi giá và thanh toán (Wie viel kostet das? Zahlen bitte)', 'content' => 'Cách hỏi giá và yêu cầu thanh toán.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Mua sắm quần áo và quà lưu niệm (Ich suche..., Kann ich das anprobieren?)', 'content' => 'Các câu hỏi và yêu cầu khi mua sắm.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Số đếm từ 21 đến 100 và hơn nữa', 'content' => 'Tiếp tục học số đếm để sử dụng trong giá cả.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Hỏi về kích cỡ, màu sắc và chất liệu', 'content' => 'Welche Größe? Welche Farbe? Aus welchem Material?', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 5: Giao thông và Hỏi đường',
                    'description' => 'Học cách sử dụng phương tiện giao thông công cộng và hỏi đường khi đi du lịch.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Các phương tiện giao thông (Bus, Bahn, Taxi, Flugzeug)', 'content' => 'Từ vựng về các loại hình giao thông.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Mua vé và hỏi về giờ khởi hành/đến', 'content' => 'Eine Fahrkarte bitte, Wann fährt der Zug?', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Hỏi đường (Wo ist...? Wie komme ich zu...?)', 'content' => 'Các câu hỏi để tìm đường.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Chỉ đường (geradeaus, links, rechts, an der Ecke)', 'content' => 'Các cụm từ để chỉ đường.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Từ vựng về địa điểm công cộng (Bahnhof, Flughafen, Museum)', 'content' => 'Mở rộng vốn từ vựng về các địa điểm.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Văn hóa Đức và Lời khuyên du lịch',
                    'description' => 'Tìm hiểu về văn hóa Đức và các mẹo để có chuyến đi suôn sẻ.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Các phong tục và nghi thức xã giao cơ bản của người Đức', 'content' => 'Văn hóa đúng giờ, sự trực tiếp trong giao tiếp.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Ẩm thực Đức và các món ăn đặc trưng', 'content' => 'Bratwurst, Sauerkraut, Bier...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Các ngày lễ và lễ hội nổi tiếng', 'content' => 'Oktoberfest, Weihnachten...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Mẹo du lịch và các cụm từ khẩn cấp', 'content' => 'Hilfe!, Arzt!, Polizei!', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để tiếp tục học tiếng Đức sau chuyến đi', 'content' => 'Các nguồn tài liệu và cách duy trì kỹ năng.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Tập Luyện Tại Nhà: Linh Hoạt và Sức Mạnh Cốt Lõi',
            'description' => 'Khóa học này tập trung vào việc cải thiện độ linh hoạt, dẻo dai và tăng cường sức mạnh cho nhóm cơ cốt lõi thông qua các bài tập bodyweight đơn giản, phù hợp cho mọi cấp độ.',
            'price' => 300000.00,
            'categoryIds' => [77, 73], // Tập luyện thể hình, Sức khỏe & Thể hình
            'requirements' => [
                'Không yêu cầu kinh nghiệm tập luyện trước đó.',
                'Một tấm thảm tập (tùy chọn) và không gian đủ rộng.',
                'Quần áo thoải mái, co giãn.',
            ],
            'objectives' => [
                'Nắm vững kỹ thuật các bài tập kéo giãn và tăng cường cốt lõi một cách an toàn.',
                'Cải thiện đáng kể độ linh hoạt của toàn bộ cơ thể (hông, vai, lưng, gân kheo).',
                'Tăng cường sức mạnh và sự ổn định của nhóm cơ cốt lõi (bụng, lưng dưới).',
                'Giảm đau lưng và cải thiện tư thế.',
                'Xây dựng thói quen tập luyện đều đặn để duy trì sức khỏe và sự dẻo dai.',
                'Hiểu rõ mối liên hệ giữa linh hoạt, sức mạnh cốt lõi và sức khỏe tổng thể.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu và Tầm quan trọng của Linh hoạt & Cốt lõi',
                    'description' => 'Khám phá lợi ích của việc tập luyện linh hoạt và cốt lõi đối với cơ thể.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Linh hoạt và Sức mạnh cốt lõi là gì? Tại sao quan trọng?', 'content' => 'Định nghĩa, lợi ích trong đời sống hàng ngày và thể thao.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các loại kéo giãn (tĩnh, động) và khi nào nên thực hiện', 'content' => 'Phân biệt và ứng dụng các kỹ thuật kéo giãn.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Nguyên tắc an toàn và lắng nghe cơ thể khi kéo giãn', 'content' => 'Tránh chấn thương, không ép cơ thể quá mức.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Chuẩn bị không gian và dụng cụ tập luyện', 'content' => 'Tạo môi trường thoải mái và an toàn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Khởi động và Kéo giãn toàn thân cơ bản',
                    'description' => 'Các bài tập nhẹ nhàng giúp làm ấm cơ thể và tăng cường độ linh hoạt ban đầu.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Khởi động khớp cổ, vai và cánh tay', 'content' => 'Các động tác xoay và kéo giãn nhẹ nhàng cho phần thân trên.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Khởi động khớp hông và chân', 'content' => 'Làm ấm các khớp ở phần dưới cơ thể.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Chuỗi kéo giãn cơ bản (tay, chân, lưng)', 'content' => 'Các động tác kéo giãn tĩnh đơn giản cho toàn thân.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Hít thở sâu và thư giãn trong khi kéo giãn', 'content' => 'Kỹ thuật thở giúp tăng hiệu quả kéo giãn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Bài tập tăng cường Sức mạnh Cốt lõi',
                    'description' => 'Tập trung vào các bài tập giúp săn chắc cơ bụng và tăng cường sức mạnh lưng dưới.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Plank và các biến thể (Side Plank, Elbow Plank)', 'content' => 'Tập luyện toàn diện cơ cốt lõi, từ cơ bản đến nâng cao.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Bird-Dog và Dead Bug', 'content' => 'Các bài tập giúp ổn định cột sống và tăng cường cơ lưng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Russian Twists và Bicycle Crunches', 'content' => 'Tập luyện cơ bụng xiên và cơ bụng trên.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Leg Raises và Reverse Crunches', 'content' => 'Tập trung vào cơ bụng dưới.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Bridge Pose (Cầu mông) và các biến thể', 'content' => 'Tăng cường sức mạnh cơ mông và lưng dưới.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Kéo giãn chuyên sâu cho từng nhóm cơ',
                    'description' => 'Các bài tập kéo giãn sâu hơn để cải thiện linh hoạt ở các vùng cơ cụ thể.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Kéo giãn gân kheo và bắp chân', 'content' => 'Các động tác giúp kéo giãn mặt sau của chân.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Kéo giãn khớp hông và cơ đùi trong', 'content' => 'Các bài tập giúp mở hông và tăng độ dẻo dai.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Kéo giãn vai, ngực và lưng trên', 'content' => 'Các động tác giúp mở rộng lồng ngực và cải thiện tư thế vai.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Kéo giãn cột sống và cơ liên sườn', 'content' => 'Các động tác vặn xoắn và nghiêng người.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Kết hợp Linh hoạt và Cốt lõi vào chuỗi tập luyện',
                    'description' => 'Xây dựng các chuỗi động tác kết hợp cả sức mạnh cốt lõi và độ linh hoạt.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Chuỗi động tác flow cho buổi sáng', 'content' => 'Các bài tập giúp đánh thức cơ thể và tăng năng lượng.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chuỗi động tác thư giãn buổi tối để giảm căng thẳng', 'content' => 'Các bài tập nhẹ nhàng giúp thư giãn trước khi ngủ.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Chuỗi tập luyện cốt lõi 15 phút hiệu quả', 'content' => 'Chương trình tập trung vào nhóm cơ cốt lõi.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Ứng dụng Yoga và Pilates vào tập luyện hàng ngày', 'content' => 'Các động tác cơ bản từ Yoga và Pilates.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Điều chỉnh bài tập cho từng cấp độ (dễ, trung bình, khó)', 'content' => 'Cách tăng/giảm độ khó của bài tập.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Duy trì thói quen và Lối sống năng động',
                    'description' => 'Lời khuyên để duy trì thói quen tập luyện và biến nó thành một phần của cuộc sống.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Lập kế hoạch tập luyện hàng tuần và theo dõi tiến độ', 'content' => 'Cách xây dựng lịch trình phù hợp và ghi nhật ký.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Dinh dưỡng hỗ trợ phục hồi và phát triển cơ bắp', 'content' => 'Chế độ ăn uống phù hợp cho người tập luyện.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tầm quan trọng của giấc ngủ và quản lý căng thẳng', 'content' => 'Giấc ngủ và stress ảnh hưởng đến hiệu suất tập luyện.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Vượt qua sự nản lòng và duy trì động lực', 'content' => 'Các mẹo để không bỏ cuộc và tiếp tục tiến bộ.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để biến tập luyện thành niềm vui', 'content' => 'Tìm kiếm sự đa dạng và thử thách bản thân.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Nấu Món Ăn Đường Phố Việt Nam',
            'description' => 'Khám phá hương vị độc đáo của ẩm thực đường phố Việt Nam. Học cách chế biến các món ăn vặt, món chính và đồ uống phổ biến, mang đến trải nghiệm ẩm thực đích thực ngay tại nhà.',
            'price' => 580000.00,
            'categoryIds' => [63], // Phát triển cá nhân (kỹ năng sống)
            'requirements' => [
                'Có niềm đam mê với ẩm thực đường phố Việt Nam.',
                'Có bếp và các dụng cụ nấu ăn cơ bản.',
                'Sẵn sàng thử nghiệm và thưởng thức.',
            ],
            'objectives' => [
                'Nắm vững các kỹ thuật sơ chế và chế biến đặc trưng của món ăn đường phố Việt.',
                'Chế biến thành thạo ít nhất 10 món ăn đường phố phổ biến của Việt Nam.',
                'Hiểu cách sử dụng các loại gia vị và nước chấm đặc trưng để tạo hương vị chuẩn.',
                'Tự tin chuẩn bị các món ăn đường phố cho gia đình và bạn bè.',
                'Bảo quản thực phẩm và an toàn vệ sinh trong nấu ăn đường phố.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Ẩm thực Đường phố Việt và Nguyên liệu đặc trưng',
                    'description' => 'Tổng quan về văn hóa ẩm thực đường phố và các nguyên liệu không thể thiếu.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Văn hóa ẩm thực đường phố Việt Nam', 'content' => 'Sự đa dạng và sức hấp dẫn của món ăn đường phố.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các loại rau thơm, gia vị và nước chấm đặc trưng', 'content' => 'Rau sống, rau thơm, ớt, chanh, nước mắm pha.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Kỹ thuật chọn và sơ chế nguyên liệu tươi ngon', 'content' => 'Chọn thịt, hải sản, rau củ cho món đường phố.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: An toàn vệ sinh thực phẩm khi chế biến tại nhà', 'content' => 'Đảm bảo vệ sinh trong quá trình nấu ăn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Các món Bún và Phở đặc trưng',
                    'description' => 'Học cách nấu các món bún và phở nổi tiếng của ẩm thực đường phố.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Phở cuốn Hà Nội và Nước chấm chua ngọt', 'content' => 'Hướng dẫn làm phở cuốn thanh mát, dễ ăn.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Bún đậu mắm tôm chuẩn vị', 'content' => 'Bí quyết làm đậu chiên giòn, thịt luộc, chả cốm và mắm tôm pha.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Bún chả nem Hà Nội', 'content' => 'Cách làm chả viên, chả miếng nướng thơm ngon và nước chấm bún chả.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Bún bò Huế đậm đà', 'content' => 'Công thức nấu bún bò Huế với nước dùng cay nồng, thơm lừng.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Bánh mì kẹp thịt (Bánh mì Sài Gòn)', 'content' => 'Cách làm các loại nhân bánh mì và pha nước sốt.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Các món Bánh và Chiên rán phổ biến',
                    'description' => 'Chế biến các loại bánh và món chiên rán được yêu thích trên đường phố.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Bánh khọt Vũng Tàu giòn rụm', 'content' => 'Bí quyết làm bánh khọt vỏ giòn, nhân tôm tươi.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Bánh tráng nướng Đà Lạt (Pizza Việt Nam)', 'content' => 'Cách làm bánh tráng nướng với các loại topping đa dạng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Bánh gối/Bánh rán mặn', 'content' => 'Công thức làm bánh gối nhân thịt mộc nhĩ, vỏ giòn.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Khoai tây chiên lắc phô mai/bột xí muội', 'content' => 'Món ăn vặt đơn giản nhưng hấp dẫn.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Bắp xào tép mỡ hành', 'content' => 'Món bắp xào thơm lừng, đậm đà hương vị.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Các món Gỏi, Nộm và Trộn',
                    'description' => 'Học cách làm các món gỏi, nộm thanh mát, kích thích vị giác.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Gỏi đu đủ khô bò', 'content' => 'Cách làm gỏi đu đủ giòn ngon, chua cay mặn ngọt.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Nộm tai heo dưa chuột', 'content' => 'Món nộm giòn sần sật, thanh mát.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Bánh tráng trộn Sài Gòn', 'content' => 'Công thức làm bánh tráng trộn đúng điệu, đầy đủ topping.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Bánh bột lọc trần/gói', 'content' => 'Cách làm bánh bột lọc dai ngon, nhân tôm thịt.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Các món Chè và Đồ uống giải khát',
                    'description' => 'Hoàn thiện bữa ăn đường phố với các món tráng miệng và đồ uống đặc trưng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Chè khúc bạch', 'content' => 'Món chè thanh mát, thơm ngon, giải nhiệt.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chè bưởi giòn sần sật', 'content' => 'Bí quyết làm chè bưởi không đắng, giòn ngon.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Sữa đậu nành tự làm và sữa ngô', 'content' => 'Cách làm các loại sữa hạt bổ dưỡng.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Nước mía và trà tắc', 'content' => 'Cách pha chế các loại đồ uống giải khát phổ biến.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Cà phê trứng Hà Nội', 'content' => 'Công thức pha cà phê trứng béo ngậy, độc đáo.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Sáng tạo và Kinh doanh món ăn đường phố',
                    'description' => 'Khuyến khích sự sáng tạo và hướng dẫn cách phát triển ý tưởng kinh doanh nhỏ.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Kết hợp các món ăn đường phố thành bữa tiệc nhỏ', 'content' => 'Lên thực đơn và chuẩn bị cho bữa tiệc.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Biến tấu các công thức truyền thống theo khẩu vị cá nhân', 'content' => 'Thử nghiệm các hương vị mới và sáng tạo.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tìm kiếm nguồn nguyên liệu chất lượng và giá tốt', 'content' => 'Mẹo mua sắm tại chợ và siêu thị.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Chụp ảnh và chia sẻ món ăn đường phố hấp dẫn', 'content' => 'Các mẹo chụp ảnh đẹp để chia sẻ lên mạng xã hội.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để bắt đầu kinh doanh món ăn đường phố nhỏ', 'content' => 'Các bước cơ bản để khởi nghiệp với ẩm thực đường phố.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Sáng tác Ca khúc: Từ Ý Tưởng Đến Bản Nhạc Hoàn Chỉnh',
            'description' => 'Khóa học toàn diện này sẽ hướng dẫn bạn từng bước trong quá trình sáng tác một ca khúc, từ việc phát triển ý tưởng, viết lời, đến xây dựng giai điệu và hòa âm.',
            'price' => 650000.00,
            'categoryIds' => [71, 69, 63], // Sản xuất âm nhạc, Âm nhạc, Phát triển cá nhân
            'requirements' => [
                'Có niềm đam mê với âm nhạc và ca từ.',
                'Có khả năng chơi một nhạc cụ cơ bản (guitar/piano) hoặc sử dụng phần mềm soạn nhạc là một lợi thế.',
                'Sự sáng tạo và sẵn sàng thể hiện cảm xúc.',
            ],
            'objectives' => [
                'Phát triển ý tưởng và chủ đề cho ca khúc một cách hiệu quả.',
                'Viết lời ca khúc có ý nghĩa, dễ hiểu và truyền cảm.',
                'Xây dựng giai điệu (melody) hấp dẫn và dễ nhớ.',
                'Hiểu các nguyên tắc hòa âm cơ bản để đệm cho ca khúc.',
                'Sắp xếp cấu trúc bài hát (verse, chorus, bridge) một cách hợp lý.',
                'Hoàn thiện và thu âm bản demo ca khúc của riêng bạn.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Khơi nguồn Ý tưởng và Chủ đề',
                    'description' => 'Tìm kiếm cảm hứng và phát triển ý tưởng ban đầu cho ca khúc của bạn.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Ý tưởng sáng tác đến từ đâu? (Kinh nghiệm, quan sát, cảm xúc)', 'content' => 'Các phương pháp tìm kiếm ý tưởng và chủ đề cho bài hát.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Phát triển chủ đề và thông điệp chính của ca khúc', 'content' => 'Cách biến một ý tưởng mơ hồ thành một thông điệp rõ ràng.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Brainstorming và Mind Mapping cho ca khúc', 'content' => 'Sử dụng các kỹ thuật tư duy để mở rộng ý tưởng.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Lắng nghe và phân tích các ca khúc thành công', 'content' => 'Học hỏi từ các tác phẩm đã có để tìm cảm hứng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Viết Lời ca khúc (Lyric Writing)',
                    'description' => 'Học cách viết lời ca khúc có vần điệu, ý nghĩa và truyền cảm.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cấu trúc lời ca khúc (Verse, Chorus, Bridge, Pre-Chorus, Outro)', 'content' => 'Hiểu về các phần của một bài hát và vai trò của chúng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Vần điệu và nhịp điệu trong lời ca', 'content' => 'Cách sử dụng vần điệu và nhịp điệu để lời ca hấp dẫn hơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Sử dụng hình ảnh và ẩn dụ trong lời ca', 'content' => 'Làm cho lời ca sống động và gợi cảm xúc.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Kể chuyện qua lời ca và phát triển nhân vật', 'content' => 'Cách xây dựng một câu chuyện trong bài hát.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Viết lời ca tiếng Việt và tiếng Anh (so sánh và ứng dụng)', 'content' => 'Các đặc điểm riêng của lời ca trong hai ngôn ngữ.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Xây dựng Giai điệu (Melody Writing)',
                    'description' => 'Học cách sáng tạo giai điệu hấp dẫn và phù hợp với lời ca.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Mối quan hệ giữa lời ca và giai điệu', 'content' => 'Cách giai điệu nâng tầm lời ca và ngược lại.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Các yếu tố của một giai điệu hay (dễ nhớ, độc đáo, cảm xúc)', 'content' => 'Phân tích các yếu tố tạo nên một giai điệu thành công.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Sử dụng thang âm (scale) và hợp âm để tạo giai điệu', 'content' => 'Cách tạo giai điệu dựa trên nền tảng hòa âm.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Kỹ thuật lặp lại và biến tấu trong giai điệu', 'content' => 'Tạo sự nhất quán và phát triển cho giai điệu.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Thu âm ý tưởng giai điệu (hát chay, nhạc cụ)', 'content' => 'Cách ghi lại các ý tưởng giai điệu một cách nhanh chóng.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Hòa âm và Đệm nhạc cho ca khúc',
                    'description' => 'Hiểu về hòa âm và cách tạo phần đệm phù hợp cho ca khúc của bạn.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các hợp âm cơ bản và vòng hòa âm phổ biến', 'content' => 'Ôn tập và mở rộng kiến thức về các hợp âm và vòng hòa âm.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Chọn hợp âm phù hợp với giai điệu và cảm xúc lời ca', 'content' => 'Cách hòa âm để tăng cường ý nghĩa của bài hát.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Các kiểu đệm nhạc (fingerstyle, strumming, piano accompaniment)', 'content' => 'Lựa chọn phong cách đệm phù hợp với ca khúc.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Sử dụng capo và transpose để thay đổi tông bài hát', 'content' => 'Kỹ thuật điều chỉnh tông giọng cho ca sĩ.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Sắp xếp cấu trúc và Hoàn thiện ca khúc',
                    'description' => 'Biến các ý tưởng rời rạc thành một ca khúc hoàn chỉnh và có cấu trúc.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Sắp xếp các phần (Verse, Chorus, Bridge) một cách hợp lý', 'content' => 'Tạo sự phát triển và cao trào cho bài hát.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Tạo Intro và Outro hấp dẫn', 'content' => 'Cách mở đầu và kết thúc bài hát một cách ấn tượng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Thêm các yếu tố phụ (Pre-Chorus, Post-Chorus, Solo)', 'content' => 'Làm phong phú cấu trúc bài hát.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tinh chỉnh lời ca và giai điệu', 'content' => 'Chỉnh sửa để bài hát mượt mà và hoàn hảo hơn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Thu âm Demo và Chia sẻ ca khúc',
                    'description' => 'Các bước cuối cùng để ghi lại và giới thiệu ca khúc của bạn đến mọi người.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Thu âm demo tại nhà với phần mềm đơn giản', 'content' => 'Sử dụng các công cụ thu âm cơ bản (Audacity, GarageBand).', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Mixing và Mastering cơ bản cho bản demo', 'content' => 'Cân bằng âm lượng và làm cho bản thu chuyên nghiệp hơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chia sẻ ca khúc trên các nền tảng (SoundCloud, YouTube)', 'content' => 'Cách đưa nhạc của bạn đến với khán giả.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Bảo vệ bản quyền ca khúc và tìm kiếm phản hồi', 'content' => 'Các vấn đề pháp lý cơ bản và cách nhận góp ý.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để tiếp tục sáng tác và phát triển sự nghiệp', 'content' => 'Duy trì niềm đam mê và học hỏi không ngừng.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Cầu Lông Cơ Bản: Kỹ Thuật và Chiến Thuật cho Người Mới Bắt Đầu',
            'description' => 'Khóa học này cung cấp kiến thức và kỹ năng cơ bản về cầu lông, từ cách cầm vợt, di chuyển đến các cú đánh cơ bản và chiến thuật đơn giản, giúp bạn tự tin chơi cầu lông và nâng cao sức khỏe.',
            'price' => 350000.00,
            'categoryIds' => [77, 73], // Tập luyện thể hình, Sức khỏe & Thể hình (Cầu lông là một môn thể thao giúp rèn luyện thể chất)
            'requirements' => [
                'Có một cây vợt cầu lông và quả cầu.',
                'Có không gian đủ rộng để luyện tập (sân cầu lông hoặc không gian trống).',
                'Sự kiên trì và mong muốn học hỏi.',
            ],
            'objectives' => [
                'Nắm vững cách cầm vợt và tư thế đứng cơ bản trong cầu lông.',
                'Thực hiện thành thạo các kỹ thuật di chuyển trên sân.',
                'Thực hiện được các cú đánh cơ bản: giao cầu, phông cầu, đập cầu, bỏ nhỏ.',
                'Hiểu các nguyên tắc cơ bản về luật chơi và chiến thuật đơn giản trong đánh đơn/đôi.',
                'Cải thiện sức bền, sự linh hoạt và phản xạ khi chơi cầu lông.',
                'Xây dựng thói quen tập luyện thể thao đều đặn.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Cầu lông và Chuẩn bị',
                    'description' => 'Tổng quan về môn cầu lông, lợi ích và những điều cần chuẩn bị trước khi bắt đầu.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Cầu lông là gì? Lịch sử và Lợi ích sức khỏe', 'content' => 'Khám phá môn thể thao cầu lông và những giá trị mang lại.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Chọn vợt, cầu và trang phục phù hợp', 'content' => 'Hướng dẫn chọn dụng cụ và quần áo thoải mái khi chơi.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Khởi động và làm nóng cơ thể trước khi chơi', 'content' => 'Các bài tập khởi động giúp tránh chấn thương.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các nguyên tắc an toàn khi chơi cầu lông', 'content' => 'Đảm bảo an toàn cho bản thân và người chơi khác.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Kỹ thuật Cầm vợt và Di chuyển',
                    'description' => 'Nền tảng quan trọng nhất để chơi cầu lông hiệu quả và đúng kỹ thuật.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cách cầm vợt thuận tay (Forehand Grip) và trái tay (Backhand Grip)', 'content' => 'Hướng dẫn chi tiết cách cầm vợt chuẩn.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Tư thế đứng chuẩn bị (Ready Stance) và tư thế tấn công/phòng thủ', 'content' => 'Các tư thế đứng phù hợp cho từng tình huống.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Kỹ thuật di chuyển cơ bản (Footwork) trên sân', 'content' => 'Di chuyển tiến, lùi, sang ngang một cách linh hoạt.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Phối hợp tay và chân trong di chuyển', 'content' => 'Luyện tập sự ăn ý giữa tay và chân.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Bài tập di chuyển không cầu', 'content' => 'Thực hành các mẫu di chuyển trên sân mà không cần cầu.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Các cú đánh cơ bản (Phần 1)',
                    'description' => 'Học cách thực hiện các cú giao cầu và phông cầu.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Kỹ thuật giao cầu thấp thuận tay và trái tay', 'content' => 'Cách giao cầu để đối thủ khó tấn công.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Kỹ thuật giao cầu cao sâu thuận tay và trái tay', 'content' => 'Giao cầu để đẩy đối thủ về cuối sân.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Kỹ thuật phông cầu thuận tay (Clear Shot - Forehand)', 'content' => 'Đánh cầu cao sâu về cuối sân đối phương.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Kỹ thuật phông cầu trái tay (Clear Shot - Backhand)', 'content' => 'Đánh cầu cao sâu bằng trái tay.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Luyện tập giao cầu và phông cầu có mục tiêu', 'content' => 'Thực hành đưa cầu đến các vị trí mong muốn.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Các cú đánh cơ bản (Phần 2)',
                    'description' => 'Học cách thực hiện các cú đập cầu, bỏ nhỏ và ve cầu.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Kỹ thuật đập cầu (Smash) thuận tay', 'content' => 'Cú đánh tấn công mạnh mẽ nhất trong cầu lông.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Kỹ thuật bỏ nhỏ (Drop Shot) thuận tay và trái tay', 'content' => 'Đánh cầu nhẹ nhàng rơi sát lưới đối phương.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Kỹ thuật ve cầu (Drive Shot) ngang lưới', 'content' => 'Đánh cầu nhanh và thấp qua lưới.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Kỹ thuật hất cầu (Lift Shot) từ dưới lên', 'content' => 'Đánh cầu cao để thoát khỏi áp lực tấn công.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Luật chơi và Chiến thuật cơ bản',
                    'description' => 'Hiểu về luật chơi và áp dụng các chiến thuật đơn giản trong trận đấu.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Luật chơi cầu lông cơ bản (tính điểm, giao cầu, lỗi)', 'content' => 'Các quy tắc cơ bản của một trận đấu cầu lông.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chiến thuật đánh đơn cơ bản', 'content' => 'Di chuyển đối thủ, tấn công vào điểm yếu.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Chiến thuật đánh đôi cơ bản (trước-sau, ngang)', 'content' => 'Phối hợp với đồng đội trong đánh đôi.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Phân tích trận đấu và đọc tình huống', 'content' => 'Cách dự đoán đường cầu và phản ứng nhanh.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Các bài tập luyện phản xạ và tốc độ', 'content' => 'Tăng cường khả năng phản ứng nhanh trên sân.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Luyện tập nâng cao và Duy trì đam mê',
                    'description' => 'Các bài tập giúp nâng cao kỹ năng và lời khuyên để duy trì niềm vui với cầu lông.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Các bài tập phối hợp nhiều cú đánh', 'content' => 'Kết hợp giao cầu, phông cầu, đập cầu trong một chuỗi.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Luyện tập với bạn bè và tham gia các trận đấu', 'content' => 'Thực hành trong môi trường thực tế.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Dinh dưỡng và phục hồi cho người chơi cầu lông', 'content' => 'Chế độ ăn uống và nghỉ ngơi phù hợp.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xử lý các chấn thương thường gặp và cách phòng ngừa', 'content' => 'Đau vai, khuỷu tay, đầu gối.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Lời khuyên để duy trì niềm vui và tiến bộ lâu dài', 'content' => 'Biến cầu lông thành một phần của lối sống năng động.', 'sortOrder' => 5],
                    ]
                ],
            ]
        ],
        [
            'title' => 'Khóa học Thiết kế Đồ họa Chuyên Sâu với Adobe Photoshop và Illustrator',
            'description' => 'Trở thành chuyên gia thiết kế đồ họa bằng cách làm chủ Adobe Photoshop và Illustrator, từ cơ bản đến nâng cao, ứng dụng vào các dự án thực tế.',
            'price' => 999000.00,
            'categoryIds' => [54, 49, 52, 53], // Thiết kế đồ họa 2D/3D, Thiết kế, Adobe Photoshop, Illustrator
            'requirements' => [
                'Máy tính cài đặt sẵn Adobe Photoshop và Illustrator (phiên bản CS6 trở lên).',
                'Có kiến thức cơ bản về sử dụng máy tính.',
                'Đam mê với thiết kế và sáng tạo.'
            ],
            'objectives' => [
                'Sử dụng thành thạo các công cụ và tính năng cốt lõi của Photoshop và Illustrator.',
                'Thiết kế logo, bộ nhận diện thương hiệu, banner quảng cáo, ấn phẩm marketing.',
                'Chỉnh sửa ảnh chuyên nghiệp, tạo hiệu ứng hình ảnh độc đáo.',
                'Hiểu rõ về lý thuyết màu sắc, bố cục trong thiết kế.',
                'Tạo ra các sản phẩm thiết kế đồ họa chất lượng cao, đáp ứng yêu cầu thực tế.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Làm quen với Adobe Photoshop - Nền tảng vững chắc',
                    'description' => 'Giới thiệu giao diện, công cụ cơ bản và các thao tác làm việc đầu tiên với Photoshop.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Tổng quan về Photoshop và không gian làm việc', 'content' => 'Nội dung chi tiết về giao diện, panel, menu...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các công cụ lựa chọn (Selection Tools) cơ bản và nâng cao', 'content' => 'Marquee, Lasso, Magic Wand, Quick Selection, Pen Tool...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Layer và Mask - Quản lý và chỉnh sửa không phá hủy', 'content' => 'Khái niệm layer, layer mask, clipping mask...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các chế độ hòa trộn (Blending Modes) và hiệu ứng Layer Style', 'content' => 'Ứng dụng blending modes và layer style để tạo hiệu ứng...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Chỉnh sửa ảnh chuyên nghiệp với Photoshop',
                    'description' => 'Kỹ thuật retouch ảnh, cân bằng màu sắc, ánh sáng và tạo hiệu ứng.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cân bằng màu sắc và ánh sáng (Levels, Curves, Hue/Saturation)', 'content' => 'Sử dụng các công cụ điều chỉnh màu sắc, độ sáng tối...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Kỹ thuật Retouch ảnh chân dung cơ bản (Healing Brush, Clone Stamp)', 'content' => 'Xóa mụn, làm mịn da, chỉnh sửa khuyết điểm...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Tách nền ảnh và ghép ảnh (Refine Edge, Select and Mask)', 'content' => 'Các kỹ thuật tách nền phức tạp và ghép ảnh tự nhiên...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Tạo hiệu ứng ảnh nghệ thuật (Filters, Adjustments)', 'content' => 'Sử dụng filter gallery và các adjustment layer để tạo hiệu ứng...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Giới thiệu Adobe Illustrator và Đồ họa Vector',
                    'description' => 'Khám phá sức mạnh của đồ họa vector và các công cụ vẽ cơ bản trong Illustrator.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: So sánh đồ họa Raster và Vector - Tại sao chọn Illustrator?', 'content' => 'Ưu nhược điểm của từng loại và ứng dụng của vector...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Giao diện và công cụ vẽ cơ bản trong Illustrator (Pen Tool, Shape Tools)', 'content' => 'Làm quen với Pen Tool, Rectangle, Ellipse Tool...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Làm việc với màu sắc, Gradient và Pattern', 'content' => 'Sử dụng Swatches, Color Picker, Gradient Tool, tạo Pattern...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Quản lý đối tượng với Layers và Artboards', 'content' => 'Sắp xếp, nhóm đối tượng, làm việc với nhiều Artboard...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Thiết kế Logo và Bộ nhận diện thương hiệu với Illustrator',
                    'description' => 'Quy trình và kỹ thuật thiết kế logo chuyên nghiệp, xây dựng bộ nhận diện thương hiệu.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Nguyên tắc thiết kế logo hiệu quả', 'content' => 'Đơn giản, dễ nhớ, phù hợp, độc đáo, linh hoạt...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Phác thảo ý tưởng và vẽ logo vector', 'content' => 'Từ sketch đến hoàn thiện logo bằng Illustrator...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Thiết kế Namecard, Letterhead và các ấn phẩm văn phòng', 'content' => 'Ứng dụng logo vào các sản phẩm nhận diện thương hiệu...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Xây dựng Brand Guidelines cơ bản', 'content' => 'Quy chuẩn sử dụng logo, màu sắc, font chữ...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Thiết kế Banner, Poster và Ấn phẩm Marketing',
                    'description' => 'Ứng dụng Photoshop và Illustrator để tạo ra các ấn phẩm quảng cáo thu hút.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Thiết kế Banner quảng cáo online (Facebook, Google Ads)', 'content' => 'Kích thước chuẩn, bố cục, thông điệp hiệu quả...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Thiết kế Poster sự kiện, quảng bá sản phẩm', 'content' => 'Sử dụng hình ảnh, typography và màu sắc ấn tượng...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Thiết kế Brochure, Flyer giới thiệu công ty/sản phẩm', 'content' => 'Bố cục thông tin, hình ảnh minh họa hấp dẫn...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Chuẩn bị file in ấn và xuất file cho web', 'content' => 'Lưu ý về hệ màu, độ phân giải, định dạng file...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Dự án thực tế và Phát triển sự nghiệp Thiết kế',
                    'description' => 'Thực hành các dự án tổng hợp và định hướng phát triển trong ngành thiết kế đồ họa.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Thực hiện dự án thiết kế bộ nhận diện thương hiệu hoàn chỉnh', 'content' => 'Từ brief khách hàng đến sản phẩm cuối cùng...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Xây dựng Portfolio cá nhân ấn tượng', 'content' => 'Cách trình bày sản phẩm, lựa chọn dự án showcase...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tìm kiếm cơ hội việc làm và làm việc freelance', 'content' => 'Các trang web tìm việc, kỹ năng phỏng vấn...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xu hướng thiết kế đồ họa hiện nay và tương lai', 'content' => 'Cập nhật kiến thức và kỹ năng mới...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Figma Masterclass: Từ Cơ bản đến Thiết kế UI/UX Chuyên nghiệp',
            'description' => 'Làm chủ Figma, công cụ thiết kế UI/UX hàng đầu, để tạo ra các giao diện web và ứng dụng di động đẹp mắt, thân thiện với người dùng.',
            'price' => 799000.00,
            'categoryIds' => [100, 51, 49], // Figma, Thiết kế UI/UX, Thiết kế
            'requirements' => [
                'Máy tính có kết nối internet và trình duyệt web.',
                'Không yêu cầu kiến thức thiết kế trước, nhưng có gu thẩm mỹ là một lợi thế.',
                'Mong muốn học hỏi và thực hành thường xuyên.'
            ],
            'objectives' => [
                'Nắm vững giao diện và các tính năng cốt lõi của Figma.',
                'Thiết kế wireframe, mockup và prototype tương tác cho website và mobile app.',
                'Xây dựng và quản lý Design System hiệu quả với Components, Variants, Auto Layout.',
                'Hiểu và áp dụng các nguyên tắc thiết kế UI/UX vào sản phẩm.',
                'Cộng tác hiệu quả với đội nhóm trên Figma.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Figma và Không gian làm việc',
                    'description' => 'Bắt đầu hành trình với Figma, tìm hiểu giao diện và các khái niệm cơ bản.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Figma là gì? Tại sao Figma lại phổ biến?', 'content' => 'Ưu điểm của Figma, so sánh với các công cụ khác...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Tạo tài khoản và khám phá giao diện Figma', 'content' => 'File Browser, Editor, Layers Panel, Properties Panel...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các công cụ vẽ cơ bản: Frame, Shape, Text, Pen', 'content' => 'Cách sử dụng các công cụ để tạo đối tượng đồ họa...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Thao tác với đối tượng: Group, Align, Distribute', 'content' => 'Quản lý và sắp xếp đối tượng hiệu quả...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Thiết kế Wireframe và Mockup cơ bản',
                    'description' => 'Học cách tạo khung sườn và giao diện trực quan cho sản phẩm.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Wireframing: Lên ý tưởng và cấu trúc giao diện', 'content' => 'Tầm quan trọng của wireframe, các loại wireframe...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Thực hành vẽ wireframe cho một trang web đơn giản', 'content' => 'Sử dụng các shape tool và text tool để tạo wireframe...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Từ Wireframe đến Mockup: Thêm màu sắc, hình ảnh, typography', 'content' => 'Nguyên tắc chọn màu, font chữ, sử dụng hình ảnh...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Thiết kế Mockup cho màn hình Mobile App', 'content' => 'Lưu ý về kích thước, bố cục cho thiết bị di động...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Auto Layout và Components - Tối ưu quy trình thiết kế',
                    'description' => 'Sử dụng các tính năng mạnh mẽ của Figma để thiết kế linh hoạt và nhất quán.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Auto Layout: Tạo bố cục tự động co giãn', 'content' => 'Cách sử dụng Auto Layout cho button, card, list...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Components: Tạo và quản lý các thành phần tái sử dụng', 'content' => 'Main Component, Instance, lợi ích của Component...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Variants: Quản lý các trạng thái khác nhau của Component', 'content' => 'Tạo button với các state: default, hover, disabled...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Xây dựng thư viện UI Kit cơ bản với Components và Variants', 'content' => 'Tạo các component phổ biến: input, dropdown, navigation...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Prototyping - Tạo mẫu thử tương tác',
                    'description' => 'Biến thiết kế tĩnh thành các prototype động, mô phỏng trải nghiệm người dùng.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Giới thiệu chế độ Prototype trong Figma', 'content' => 'Connections, Interactions, Animations...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Tạo các luồng người dùng (User Flows) cơ bản', 'content' => 'Liên kết các màn hình, thiết lập điều hướng...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Thêm hiệu ứng chuyển cảnh (Transitions) và Animation', 'content' => 'Smart Animate, easing curves...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Chia sẻ và kiểm thử Prototype với người dùng', 'content' => 'Cách lấy link chia sẻ, thu thập feedback...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Design Systems và Cộng tác trong Figma',
                    'description' => 'Xây dựng hệ thống thiết kế và làm việc nhóm hiệu quả trên Figma.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Khái niệm Design System và tầm quan trọng', 'content' => 'Lợi ích của Design System, các thành phần chính...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Xây dựng Design System cơ bản trong Figma (Styles, Components)', 'content' => 'Tạo Text Styles, Color Styles, Effect Styles...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Cộng tác thời gian thực và quản lý phiên bản', 'content' => 'Mời thành viên, comment, version history...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Bàn giao thiết kế cho Developer (Handoff)', 'content' => 'Sử dụng Inspect Panel, xuất assets...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Thực hành dự án UI/UX hoàn chỉnh và Mẹo nâng cao',
                    'description' => 'Áp dụng kiến thức đã học vào một dự án thực tế và khám phá các kỹ thuật Figma nâng cao.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Dự án thiết kế UI/UX cho ứng dụng đặt đồ ăn', 'content' => 'Từ nghiên cứu, wireframe, mockup đến prototype...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Plugins hữu ích trong Figma để tăng năng suất', 'content' => 'Giới thiệu các plugin phổ biến: Unsplash, Iconify, Content Reel...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tổ chức file và project Figma chuyên nghiệp', 'content' => 'Cách đặt tên layer, group, sắp xếp trang...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xu hướng UI/UX và cập nhật tính năng mới của Figma', 'content' => 'Luôn học hỏi và phát triển kỹ năng...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Kỹ năng Giao tiếp Ứng xử Thông minh và Tự tin',
            'description' => 'Nâng cao khả năng giao tiếp hiệu quả trong mọi tình huống, từ công việc đến cuộc sống cá nhân, xây dựng mối quan hệ tốt đẹp và đạt được thành công.',
            'price' => 499000.00,
            'categoryIds' => [64, 63], // Kỹ năng giao tiếp, Phát triển cá nhân
            'requirements' => [
                'Mong muốn cải thiện kỹ năng giao tiếp.',
                'Thái độ cởi mở và sẵn sàng thực hành.'
            ],
            'objectives' => [
                'Hiểu rõ các yếu tố cấu thành giao tiếp hiệu quả.',
                'Nắm vững kỹ năng lắng nghe chủ động và phản hồi tinh tế.',
                'Sử dụng ngôn ngữ cơ thể và giọng nói một cách tự tin, cuốn hút.',
                'Giải quyết xung đột và đàm phán thành công.',
                'Xây dựng và duy trì các mối quan hệ tích cực.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Nền tảng Giao tiếp Hiệu quả',
                    'description' => 'Tìm hiểu về bản chất của giao tiếp và các yếu tố ảnh hưởng.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Giao tiếp là gì? Vai trò của giao tiếp trong cuộc sống', 'content' => 'Định nghĩa, mô hình giao tiếp, tầm quan trọng...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các rào cản trong giao tiếp và cách vượt qua', 'content' => 'Tiếng ồn, định kiến, cảm xúc, khác biệt văn hóa...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giao tiếp bằng lời và phi ngôn ngữ - Sự kết hợp hoàn hảo', 'content' => 'Từ ngữ, giọng điệu, ánh mắt, cử chỉ, trang phục...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Xác định phong cách giao tiếp cá nhân', 'content' => 'Hiểu rõ điểm mạnh, điểm yếu để cải thiện...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Kỹ năng Lắng nghe Chủ động và Thấu hiểu',
                    'description' => 'Học cách lắng nghe để thực sự hiểu người khác.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tại sao lắng nghe lại quan trọng hơn nói?', 'content' => 'Lợi ích của việc lắng nghe tích cực...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Các cấp độ lắng nghe và kỹ thuật lắng nghe chủ động', 'content' => 'Tập trung, đặt câu hỏi, phản hồi, ghi nhớ...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Thấu cảm (Empathy) trong giao tiếp', 'content' => 'Đặt mình vào vị trí người khác để hiểu cảm xúc và suy nghĩ của họ...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Thực hành lắng nghe và phản hồi trong các tình huống cụ thể', 'content' => 'Bài tập tình huống, đóng vai...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Nghệ thuật Nói và Trình bày Cuốn hút',
                    'description' => 'Phát triển khả năng diễn đạt ý tưởng rõ ràng, mạch lạc và thuyết phục.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Sử dụng ngôn từ tích cực và xây dựng', 'content' => 'Tránh từ ngữ tiêu cực, lựa chọn từ ngữ phù hợp...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Cấu trúc bài nói chuyện hiệu quả', 'content' => 'Mở đầu, thân bài, kết luận, sử dụng ví dụ minh họa...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Kiểm soát giọng nói và ngôn ngữ cơ thể tự tin', 'content' => 'Tốc độ, âm lượng, ngữ điệu, giao tiếp bằng mắt, tư thế...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Kỹ năng đặt câu hỏi thông minh và gợi mở', 'content' => 'Câu hỏi mở, câu hỏi đóng, câu hỏi dẫn dắt...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giao tiếp trong các Mối quan hệ Xã hội',
                    'description' => 'Ứng dụng kỹ năng giao tiếp để xây dựng và duy trì mối quan hệ tốt đẹp.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Giao tiếp hiệu quả với đồng nghiệp và cấp trên', 'content' => 'Xây dựng mối quan hệ công sở tích cực, báo cáo công việc...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Giao tiếp với khách hàng và đối tác', 'content' => 'Tạo ấn tượng tốt, lắng nghe nhu cầu, xử lý phàn nàn...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Giao tiếp trong gia đình và bạn bè', 'content' => 'Lắng nghe, chia sẻ, giải quyết mâu thuẫn một cách xây dựng...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Mở rộng mạng lưới quan hệ (Networking)', 'content' => 'Cách tiếp cận, giới thiệu bản thân, duy trì kết nối...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Giải quyết Xung đột và Đàm phán Thuyết phục',
                    'description' => 'Học cách xử lý mâu thuẫn một cách hòa bình và đạt được thỏa thuận.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Nhận diện các loại xung đột và nguyên nhân', 'content' => 'Xung đột cá nhân, xung đột nhóm, xung đột lợi ích...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Các chiến lược giải quyết xung đột hiệu quả', 'content' => 'Hợp tác, thỏa hiệp, né tránh, cạnh tranh, nhượng bộ...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Nguyên tắc đàm phán Win-Win', 'content' => 'Chuẩn bị, lắng nghe, tìm kiếm giải pháp cùng có lợi...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Kỹ thuật đưa ra và nhận phản hồi mang tính xây dựng', 'content' => 'Mô hình Sandwich, STAR...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Giao tiếp Tự tin và Xây dựng Hình ảnh Cá nhân',
                    'description' => 'Nâng cao sự tự tin và tạo dựng thương hiệu cá nhân tích cực thông qua giao tiếp.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Vượt qua nỗi sợ hãi và lo lắng khi giao tiếp', 'content' => 'Kỹ thuật thư giãn, chuẩn bị tâm lý, thực hành...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Xây dựng sự tự tin từ bên trong', 'content' => 'Nhận thức giá trị bản thân, suy nghĩ tích cực...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Giao tiếp phi ngôn ngữ thể hiện sự tự tin', 'content' => 'Dáng đứng, ánh mắt, nụ cười, bắt tay...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Tạo ấn tượng ban đầu tốt đẹp và duy trì hình ảnh chuyên nghiệp', 'content' => 'Trang phục, cách nói chuyện, thái độ...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Quản lý Thời gian Hiệu quả và Tăng Năng suất Làm việc',
            'description' => 'Học cách làm chủ thời gian, sắp xếp công việc khoa học, loại bỏ sự trì hoãn và tối đa hóa hiệu suất cá nhân để đạt được mục tiêu.',
            'price' => 520000.00,
            'categoryIds' => [66, 103, 63], // Quản lý thời gian, Năng suất & Tổ chức công việc, Phát triển cá nhân
            'requirements' => [
                'Mong muốn cải thiện khả năng quản lý thời gian và năng suất.',
                'Sẵn sàng áp dụng các phương pháp và công cụ mới.'
            ],
            'objectives' => [
                'Hiểu rõ tầm quan trọng của quản lý thời gian và các yếu tố gây lãng phí thời gian.',
                'Nắm vững các phương pháp xác định ưu tiên và lập kế hoạch công việc hiệu quả (Ma trận Eisenhower, SMART goals).',
                'Học cách đối phó với sự trì hoãn và xây dựng thói quen làm việc tập trung.',
                'Sử dụng các công cụ và ứng dụng hỗ trợ quản lý thời gian.',
                'Cân bằng giữa công việc và cuộc sống cá nhân.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tư duy về Thời gian và Năng suất',
                    'description' => 'Thay đổi nhận thức về thời gian và khám phá tiềm năng năng suất của bạn.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Thời gian là gì? Tại sao quản lý thời gian lại quan trọng?', 'content' => 'Giá trị của thời gian, lợi ích của việc quản lý thời gian hiệu quả...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các "kẻ đánh cắp" thời gian phổ biến và cách nhận diện', 'content' => 'Mạng xã hội, email, sự xao nhãng, cuộc họp không hiệu quả...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Đánh giá thói quen sử dụng thời gian hiện tại của bạn', 'content' => 'Ghi nhật ký thời gian, phân tích và tìm ra điểm cần cải thiện...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Thiết lập tư duy hướng đến năng suất và kết quả', 'content' => 'Tập trung vào mục tiêu, loại bỏ suy nghĩ tiêu cực...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Xác định Mục tiêu và Lập kế hoạch Thông minh',
                    'description' => 'Học cách đặt mục tiêu rõ ràng và xây dựng kế hoạch hành động chi tiết.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Sức mạnh của việc đặt mục tiêu (SMART Goals)', 'content' => 'Specific, Measurable, Achievable, Relevant, Time-bound...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Phân chia mục tiêu lớn thành các nhiệm vụ nhỏ dễ quản lý', 'content' => 'Kỹ thuật "chia để trị"...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Lập kế hoạch hàng ngày, hàng tuần và hàng tháng hiệu quả', 'content' => 'Sử dụng to-do list, lịch biểu, công cụ lập kế hoạch...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Ma trận Eisenhower: Xác định ưu tiên công việc (Khẩn cấp/Quan trọng)', 'content' => 'Cách phân loại và xử lý công việc hiệu quả...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Kỹ thuật Tăng cường Sự tập trung và Loại bỏ Trì hoãn',
                    'description' => 'Làm chủ sự tập trung và chiến thắng thói quen trì hoãn.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Hiểu về sự trì hoãn: Nguyên nhân và hậu quả', 'content' => 'Tâm lý trì hoãn, các yếu tố kích hoạt...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Các chiến lược vượt qua sự trì hoãn (Kỹ thuật Pomodoro, Quy tắc 2 phút)', 'content' => 'Chia nhỏ công việc, bắt đầu ngay, tạo phần thưởng...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Tạo môi trường làm việc tập trung và giảm thiểu xao nhãng', 'content' => 'Sắp xếp không gian, tắt thông báo, sử dụng tai nghe chống ồn...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Rèn luyện khả năng tập trung sâu (Deep Work)', 'content' => 'Lợi ích của deep work, cách tạo khối thời gian tập trung...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Tổ chức Công việc và Không gian Làm việc',
                    'description' => 'Sắp xếp công việc và môi trường làm việc một cách khoa học để tối ưu hiệu suất.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Nguyên tắc sắp xếp công việc và tài liệu hiệu quả', 'content' => 'Hệ thống lưu trữ, quy trình làm việc rõ ràng...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Dọn dẹp không gian làm việc vật lý và kỹ thuật số', 'content' => 'Lợi ích của không gian gọn gàng, sắp xếp file, email...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Sử dụng công cụ quản lý công việc (Trello, Asana, Todoist)', 'content' => 'Giới thiệu và hướng dẫn sử dụng các công cụ phổ biến...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Kỹ thuật "Batching" - Gom nhóm các công việc tương tự', 'content' => 'Tăng hiệu quả bằng cách xử lý các tác vụ giống nhau cùng lúc...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Ủy thác Công việc và Nói "Không" Hiệu quả',
                    'description' => 'Học cách giải phóng thời gian bằng việc ủy thác và từ chối các yêu cầu không phù hợp.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Khi nào nên ủy thác công việc? Lợi ích của việc ủy thác', 'content' => 'Xác định công việc có thể ủy thác, chọn đúng người...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Quy trình ủy thác công việc hiệu quả', 'content' => 'Giao việc rõ ràng, cung cấp nguồn lực, theo dõi tiến độ...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Nghệ thuật nói "Không" một cách lịch sự và chuyên nghiệp', 'content' => 'Lý do cần nói không, cách từ chối mà không làm mất lòng...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đặt ra ranh giới cá nhân để bảo vệ thời gian và năng lượng', 'content' => 'Tầm quan trọng của việc thiết lập giới hạn...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Duy trì Thói quen Tốt và Cân bằng Cuộc sống',
                    'description' => 'Xây dựng thói quen quản lý thời gian bền vững và tìm kiếm sự cân bằng giữa công việc và cuộc sống.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Xây dựng và duy trì các thói quen quản lý thời gian tích cực', 'content' => 'Sức mạnh của thói quen, cách hình thành thói quen mới...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Đối phó với căng thẳng và áp lực công việc', 'content' => 'Kỹ thuật thư giãn, quản lý stress...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tầm quan trọng của nghỉ ngơi và tái tạo năng lượng', 'content' => 'Giấc ngủ, giải trí, sở thích cá nhân...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Đánh giá và điều chỉnh kế hoạch quản lý thời gian định kỳ', 'content' => 'Liên tục cải thiện để phù hợp với mục tiêu và hoàn cảnh...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Tư duy Phản biện và Giải quyết Vấn đề Sáng tạo',
            'description' => 'Phát triển khả năng phân tích thông tin một cách khách quan, đưa ra quyết định sáng suốt và tìm kiếm giải pháp đột phá cho các vấn đề phức tạp.',
            'price' => 650000.00,
            'categoryIds' => [67, 63], // Tư duy phản biện, Phát triển cá nhân
            'requirements' => [
                'Tinh thần ham học hỏi và sẵn sàng thử thách tư duy cũ.',
                'Khả năng đọc hiểu và phân tích thông tin cơ bản.'
            ],
            'objectives' => [
                'Hiểu rõ khái niệm và tầm quan trọng của tư duy phản biện.',
                'Nhận diện các lỗi ngụy biện và thiên kiến trong suy nghĩ.',
                'Phân tích và đánh giá thông tin, luận điểm một cách logic và khách quan.',
                'Áp dụng quy trình giải quyết vấn đề hiệu quả.',
                'Phát triển khả năng tư duy sáng tạo để tìm ra các giải pháp mới mẻ.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Tư duy Phản biện',
                    'description' => 'Khám phá bản chất, lợi ích và các thành phần cốt lõi của tư duy phản biện.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Tư duy phản biện là gì? Tại sao nó lại cần thiết?', 'content' => 'Định nghĩa, đặc điểm của người có tư duy phản biện...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các cấp độ của tư duy phản biện', 'content' => 'Từ nhận biết đến đánh giá và sáng tạo...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Những rào cản đối với tư duy phản biện', 'content' => 'Thiên kiến cá nhân, áp lực xã hội, thiếu thông tin...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Lợi ích của việc rèn luyện tư duy phản biện trong học tập và công việc', 'content' => 'Đưa ra quyết định tốt hơn, giải quyết vấn đề hiệu quả hơn...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Nhận diện Lỗi Ngụy biện và Thiên kiến',
                    'description' => 'Học cách phát hiện các cạm bẫy logic và những định kiến ảnh hưởng đến suy nghĩ.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Các lỗi ngụy biện phổ biến (Ad Hominem, Straw Man, Slippery Slope)', 'content' => 'Ví dụ và cách nhận diện từng loại ngụy biện...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Thiên kiến xác nhận (Confirmation Bias) và cách tránh', 'content' => 'Tại sao chúng ta có xu hướng tìm kiếm thông tin ủng hộ quan điểm của mình...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Các loại thiên kiến nhận thức khác (Anchoring, Availability Heuristic)', 'content' => 'Hiểu về các lối tắt tư duy và tác động của chúng...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Thực hành phân tích và phản biện các luận điểm chứa lỗi logic', 'content' => 'Bài tập tình huống, phân tích các bài báo, quảng cáo...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Kỹ năng Phân tích và Đánh giá Thông tin',
                    'description' => 'Phát triển khả năng xem xét thông tin một cách kỹ lưỡng và đưa ra những nhận định có cơ sở.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Phân biệt sự thật, ý kiến và giả định', 'content' => 'Cách xác định tính khách quan của thông tin...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Đánh giá độ tin cậy của nguồn thông tin', 'content' => 'Kiểm tra tác giả, nguồn gốc, bằng chứng hỗ trợ...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phân tích cấu trúc của một luận điểm (Luận đề, luận cứ, bằng chứng)', 'content' => 'Xác định các thành phần và mối liên hệ giữa chúng...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Kỹ thuật đặt câu hỏi Socratic để đào sâu vấn đề', 'content' => 'Sử dụng câu hỏi để khám phá, làm rõ và đánh giá...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Quy trình Giải quyết Vấn đề Hiệu quả',
                    'description' => 'Học cách tiếp cận và giải quyết vấn đề một cách có hệ thống và logic.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các bước trong quy trình giải quyết vấn đề (Xác định, Phân tích, Đề xuất, Thực hiện, Đánh giá)', 'content' => 'Mô hình giải quyết vấn đề 5 bước hoặc 7 bước...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Kỹ thuật xác định và làm rõ vấn đề cốt lõi (5 Whys, Fishbone Diagram)', 'content' => 'Tìm ra nguyên nhân gốc rễ của vấn đề...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Thu thập và phân tích dữ liệu liên quan đến vấn đề', 'content' => 'Tìm kiếm thông tin, số liệu, ý kiến chuyên gia...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Đánh giá các giải pháp tiềm năng và lựa chọn giải pháp tối ưu', 'content' => 'Tiêu chí đánh giá, phân tích ưu nhược điểm...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Phát triển Tư duy Sáng tạo',
                    'description' => 'Khơi nguồn sáng tạo và tìm kiếm những giải pháp độc đáo, đột phá.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tư duy sáng tạo là gì? Mối liên hệ với tư duy phản biện', 'content' => 'Đặc điểm của tư duy sáng tạo, vai trò trong giải quyết vấn đề...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Các kỹ thuật kích thích tư duy sáng tạo (Brainstorming, Mind Mapping, SCAMPER)', 'content' => 'Hướng dẫn sử dụng các công cụ và phương pháp sáng tạo...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Vượt qua các rào cản của sự sáng tạo (Sợ thất bại, lối mòn tư duy)', 'content' => 'Xây dựng môi trường khuyến khích sáng tạo...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Kết hợp tư duy phản biện và tư duy sáng tạo để đổi mới', 'content' => 'Sử dụng tư duy phản biện để đánh giá ý tưởng sáng tạo...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Ứng dụng Tư duy Phản biện và Sáng tạo vào Thực tế',
                    'description' => 'Thực hành áp dụng các kỹ năng đã học vào các tình huống công việc và cuộc sống.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Phân tích một vấn đề xã hội phức tạp bằng tư duy phản biện', 'content' => 'Bài tập nhóm, thảo luận và trình bày giải pháp...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Đưa ra quyết định khó khăn trong công việc dựa trên phân tích logic', 'content' => 'Nghiên cứu tình huống, cân nhắc các yếu tố...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Phát triển một ý tưởng kinh doanh mới bằng tư duy sáng tạo', 'content' => 'Từ ý tưởng ban đầu đến kế hoạch sơ bộ...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xây dựng kế hoạch rèn luyện tư duy phản biện và sáng tạo liên tục', 'content' => 'Duy trì thói quen học hỏi và thử thách bản thân...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Trí tuệ Cảm xúc (EQ) - Chìa khóa Thành công và Hạnh phúc',
            'description' => 'Khám phá và phát triển trí tuệ cảm xúc để hiểu rõ bản thân, quản lý cảm xúc hiệu quả, xây dựng mối quan hệ tốt đẹp và đạt được thành công bền vững.',
            'price' => 580000.00,
            'categoryIds' => [104, 63], // Trí tuệ cảm xúc (EQ), Phát triển cá nhân
            'requirements' => [
                'Mong muốn thấu hiểu và cải thiện đời sống cảm xúc.',
                'Sự kiên nhẫn và cam kết thực hành các bài tập.'
            ],
            'objectives' => [
                'Hiểu rõ khái niệm trí tuệ cảm xúc (EQ) và 5 thành phần cốt lõi.',
                'Nâng cao khả năng tự nhận thức: hiểu rõ cảm xúc, điểm mạnh, điểm yếu của bản thân.',
                'Học cách quản lý cảm xúc tiêu cực và nuôi dưỡng cảm xúc tích cực.',
                'Phát triển sự đồng cảm và kỹ năng xã hội để xây dựng mối quan hệ hiệu quả.',
                'Ứng dụng EQ vào công việc và cuộc sống để đạt được thành công và hạnh phúc.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Trí tuệ Cảm xúc (EQ)',
                    'description' => 'Tìm hiểu về EQ, tầm quan trọng và sự khác biệt với IQ.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Trí tuệ cảm xúc (EQ) là gì? Tại sao EQ quan trọng hơn IQ?', 'content' => 'Định nghĩa, lịch sử hình thành, vai trò của EQ...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Năm thành phần của Trí tuệ Cảm xúc theo Daniel Goleman', 'content' => 'Tự nhận thức, Tự quản lý, Động lực, Đồng cảm, Kỹ năng xã hội...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Lợi ích của việc phát triển EQ trong công việc và cuộc sống', 'content' => 'Cải thiện mối quan hệ, giảm stress, tăng hiệu suất...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Đánh giá mức độ EQ hiện tại của bạn', 'content' => 'Các bài trắc nghiệm EQ, tự đánh giá...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Tự Nhận thức - Thấu hiểu Bản thân',
                    'description' => 'Học cách nhận diện và hiểu rõ cảm xúc, suy nghĩ và giá trị của chính mình.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Nhận diện các loại cảm xúc cơ bản và phức tạp', 'content' => 'Vui, buồn, giận, sợ, ngạc nhiên, xấu hổ...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Kỹ thuật quan sát và ghi nhận cảm xúc (Journaling, Mindfulness)', 'content' => 'Cách theo dõi và hiểu rõ hơn về cảm xúc của mình...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Khám phá điểm mạnh, điểm yếu và giá trị cốt lõi của bản thân', 'content' => 'Bài tập tự phản ánh, nhận phản hồi từ người khác...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Hiểu về mối liên hệ giữa suy nghĩ, cảm xúc và hành vi', 'content' => 'Mô hình ABC trong liệu pháp nhận thức hành vi...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Tự Quản lý - Làm chủ Cảm xúc và Hành vi',
                    'description' => 'Phát triển khả năng kiểm soát cảm xúc, ứng phó với căng thẳng và duy trì thái độ tích cực.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các chiến lược quản lý cảm xúc tiêu cực (Giận dữ, Lo lắng, Buồn bã)', 'content' => 'Kỹ thuật hít thở, thư giãn, thay đổi góc nhìn...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Xây dựng khả năng phục hồi (Resilience) sau thất bại và khó khăn', 'content' => 'Học từ sai lầm, duy trì hy vọng, tìm kiếm sự hỗ trợ...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phát triển tính tự chủ và kiểm soát hành vi bốc đồng', 'content' => 'Suy nghĩ trước khi hành động, trì hoãn sự hài lòng tức thời...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Nuôi dưỡng cảm xúc tích cực và lòng biết ơn', 'content' => 'Thực hành lòng biết ơn, tìm kiếm niềm vui trong cuộc sống...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Động lực - Thúc đẩy Bản thân Hành động',
                    'description' => 'Khơi dậy và duy trì động lực nội tại để đạt được mục tiêu và vượt qua thử thách.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Xác định nguồn động lực cá nhân (Intrinsic vs. Extrinsic)', 'content' => 'Tìm kiếm ý nghĩa và mục đích trong công việc và cuộc sống...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Thiết lập mục tiêu truyền cảm hứng và tạo kế hoạch hành động', 'content' => 'Kết nối mục tiêu với giá trị cá nhân...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Duy trì sự lạc quan và thái độ "có thể làm được" (Can-do attitude)', 'content' => 'Đối mặt với thách thức bằng sự tự tin...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Vượt qua sự trì hoãn và duy trì cam kết với mục tiêu', 'content' => 'Xây dựng kỷ luật tự giác, tự thưởng cho bản thân...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Đồng cảm - Thấu hiểu Cảm xúc Người khác',
                    'description' => 'Phát triển khả năng nhận biết và thấu hiểu cảm xúc, nhu cầu của những người xung quanh.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Đồng cảm là gì? Phân biệt đồng cảm và thương hại', 'content' => 'Đặt mình vào vị trí người khác, lắng nghe bằng cả trái tim...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Kỹ năng lắng nghe chủ động để thể hiện sự đồng cảm', 'content' => 'Chú ý đến ngôn ngữ cơ thể, giọng điệu, phản hồi phù hợp...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Nhận diện cảm xúc của người khác qua biểu hiện phi ngôn ngữ', 'content' => 'Đọc vị nét mặt, cử chỉ, ánh mắt...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Xây dựng mối quan hệ dựa trên sự tin tưởng và thấu hiểu', 'content' => 'Tạo không gian an toàn để chia sẻ cảm xúc...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Kỹ năng Xã hội - Xây dựng Mối quan hệ Tích cực',
                    'description' => 'Nâng cao khả năng tương tác, giao tiếp và xây dựng mối quan hệ bền chặt, hiệu quả.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Kỹ năng giao tiếp hiệu quả và gây ảnh hưởng tích cực', 'content' => 'Truyền đạt thông điệp rõ ràng, thuyết phục, truyền cảm hứng...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Giải quyết xung đột một cách xây dựng và hợp tác', 'content' => 'Tìm kiếm giải pháp win-win, duy trì sự tôn trọng...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Kỹ năng làm việc nhóm và lãnh đạo bằng trí tuệ cảm xúc', 'content' => 'Tạo động lực cho đội nhóm, xây dựng văn hóa tin cậy...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Áp dụng EQ để xây dựng mạng lưới quan hệ và phát triển sự nghiệp', 'content' => 'Kết nối chân thành, hỗ trợ người khác, tạo dựng uy tín...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Thiết kế Web Responsive với HTML, CSS và JavaScript Hiện Đại',
            'description' => 'Nắm vững các kỹ thuật xây dựng website đáp ứng (responsive) đẹp mắt, hoạt động trơn tru trên mọi thiết bị từ máy tính đến điện thoại di động.',
            'price' => 850000.00,
            'categoryIds' => [50, 2, 3, 4], // Thiết kế Web, Lập trình Web, HTML & CSS, JavaScript
            'requirements' => [
                'Kiến thức cơ bản về HTML và CSS.',
                'Hiểu biết sơ lược về JavaScript là một lợi thế.',
                'Máy tính có trình duyệt web và trình soạn thảo code (VS Code, Sublime Text...).'
            ],
            'objectives' => [
                'Hiểu rõ các nguyên tắc của Responsive Web Design (RWD).',
                'Sử dụng thành thạo Media Queries để điều chỉnh layout theo kích thước màn hình.',
                'Xây dựng layout linh hoạt với Flexbox và CSS Grid.',
                'Tối ưu hóa hình ảnh và nội dung cho các thiết bị khác nhau.',
                'Áp dụng JavaScript để tăng cường tính tương tác và trải nghiệm người dùng trên mobile.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Responsive Web Design (RWD)',
                    'description' => 'Tìm hiểu khái niệm, tầm quan trọng và các thành phần cốt lõi của thiết kế web đáp ứng.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Responsive Web Design là gì? Tại sao lại cần thiết?', 'content' => 'Sự bùng nổ của thiết bị di động, trải nghiệm người dùng...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Ba trụ cột của RWD: Fluid Grids, Flexible Images, Media Queries', 'content' => 'Khái niệm và vai trò của từng thành phần...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Mobile First vs. Desktop First: Lựa chọn chiến lược phù hợp', 'content' => 'Ưu nhược điểm của từng phương pháp tiếp cận...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Thiết lập Viewport và các công cụ kiểm tra responsive', 'content' => 'Meta viewport tag, Chrome DevTools, các trình giả lập...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Media Queries - Điều khiển Layout theo Thiết bị',
                    'description' => 'Làm chủ Media Queries để tạo ra các điểm ngắt (breakpoints) và thay đổi CSS cho từng kích thước màn hình.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cú pháp và cách sử dụng Media Queries cơ bản', 'content' => '@media rule, các media features (width, height, orientation)...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Xác định Breakpoints hiệu quả cho dự án', 'content' => 'Dựa trên nội dung hay dựa trên thiết bị phổ biến?', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Áp dụng Media Queries để thay đổi Bố cục, Font chữ, Hình ảnh', 'content' => 'Ví dụ thực tế về điều chỉnh CSS cho mobile, tablet, desktop...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Media Queries nâng cao: Resolution, Aspect Ratio, Hover', 'content' => 'Sử dụng các media features ít phổ biến hơn...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Xây dựng Layout Linh hoạt với Flexbox',
                    'description' => 'Sử dụng Flexbox để tạo ra các layout một chiều mạnh mẽ và dễ dàng căn chỉnh các phần tử.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Giới thiệu về Flexbox và các khái niệm cơ bản (Container, Items, Main Axis, Cross Axis)', 'content' => 'Tại sao Flexbox ra đời và giải quyết vấn đề gì...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Các thuộc tính Flex Container (display: flex, flex-direction, justify-content, align-items)', 'content' => 'Cách điều khiển sự sắp xếp của các flex items...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Các thuộc tính Flex Items (flex-grow, flex-shrink, flex-basis, order, align-self)', 'content' => 'Cách tùy chỉnh kích thước và vị trí của từng item...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Xây dựng các thành phần UI phổ biến với Flexbox (Navigation, Card Layout, Gallery)', 'content' => 'Ví dụ thực hành áp dụng Flexbox...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Làm chủ CSS Grid Layout',
                    'description' => 'Khám phá sức mạnh của CSS Grid để tạo ra các layout hai chiều phức tạp một cách dễ dàng và trực quan.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Giới thiệu CSS Grid và sự khác biệt với Flexbox', 'content' => 'Khi nào nên dùng Grid, khi nào nên dùng Flexbox...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các khái niệm cơ bản trong Grid (Grid Container, Grid Items, Grid Lines, Grid Tracks, Grid Cells, Grid Areas)', 'content' => 'Hiểu rõ thuật ngữ để làm việc hiệu quả...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Định nghĩa Grid với grid-template-columns, grid-template-rows, grid-template-areas', 'content' => 'Cách tạo cấu trúc lưới mong muốn...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Sắp xếp Items vào Grid (grid-column, grid-row, grid-area) và Responsive Grid', 'content' => 'Kết hợp Grid với Media Queries để tạo layout đáp ứng...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Hình ảnh và Typography Đáp ứng',
                    'description' => 'Tối ưu hóa hình ảnh và văn bản để hiển thị đẹp và dễ đọc trên mọi thiết bị.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Kỹ thuật làm hình ảnh responsive (max-width: 100%, <picture> element, srcset attribute)', 'content' => 'Cách đảm bảo hình ảnh co giãn và tải đúng kích thước...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Tối ưu hóa hình ảnh cho web (Định dạng, Nén, Lazy Loading)', 'content' => 'Cải thiện tốc độ tải trang với hình ảnh tối ưu...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Responsive Typography: Sử dụng đơn vị tương đối (em, rem, vw, vh)', 'content' => 'Cách làm cho font chữ co giãn theo kích thước màn hình...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đảm bảo tính dễ đọc (Readability) trên các thiết bị khác nhau', 'content' => 'Line height, contrast, font choice...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: JavaScript và Tương tác Responsive Nâng cao',
                    'description' => 'Sử dụng JavaScript để cải thiện trải nghiệm người dùng trên các thiết bị, đặc biệt là mobile.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Phát hiện thiết bị và kích thước màn hình với JavaScript (window.matchMedia)', 'content' => 'Thực hiện các hành động khác nhau dựa trên thiết bị...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Tạo Navigation Menu responsive (Hamburger Menu)', 'content' => 'Ẩn/hiện menu trên mobile bằng JavaScript và CSS...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Xử lý sự kiện chạm (Touch Events) cho thiết bị di động', 'content' => 'touchstart, touchend, touchmove, swipe gestures...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Các thư viện và Framework hỗ trợ Responsive Design (Bootstrap, Tailwind CSS - Giới thiệu)', 'content' => 'Tổng quan về các công cụ giúp tăng tốc độ phát triển responsive...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Mindfulness và Thiền định: Tìm lại Bình yên Giữa Bộn bề Cuộc sống',
            'description' => 'Học cách thực hành chánh niệm (mindfulness) và thiền định để giảm căng thẳng, tăng cường sự tập trung, cải thiện sức khỏe tinh thần và tìm thấy sự bình an nội tại.',
            'price' => 480000.00,
            'categoryIds' => [108, 75, 63], // Mindfulness & Giảm căng thẳng, Thiền, Phát triển cá nhân
            'requirements' => [
                'Không gian yên tĩnh để thực hành.',
                'Sự kiên trì và cam kết dành thời gian mỗi ngày.',
                'Trang phục thoải mái.'
            ],
            'objectives' => [
                'Hiểu rõ khái niệm mindfulness và lợi ích của việc thực hành chánh niệm.',
                'Nắm vững các kỹ thuật thiền định cơ bản (thiền hơi thở, thiền quét cơ thể, thiền từ tâm).',
                'Ứng dụng chánh niệm vào các hoạt động hàng ngày để sống trọn vẹn hơn.',
                'Giảm căng thẳng, lo âu và cải thiện chất lượng giấc ngủ.',
                'Nâng cao khả năng tập trung, sự tự nhận thức và lòng trắc ẩn.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Mindfulness (Chánh niệm)',
                    'description' => 'Khám phá bản chất, nguồn gốc và những lợi ích khoa học đã chứng minh của mindfulness.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Mindfulness là gì? Phân biệt mindfulness và thiền định', 'content' => 'Định nghĩa, các yếu tố cốt lõi của chánh niệm...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Lịch sử và nguồn gốc của thực hành chánh niệm', 'content' => 'Từ truyền thống Đông phương đến ứng dụng hiện đại...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Lợi ích của mindfulness đối với sức khỏe thể chất và tinh thần', 'content' => 'Giảm stress, cải thiện sự tập trung, tăng cường hệ miễn dịch...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tư thế ngồi thiền đúng và chuẩn bị cho buổi thực hành', 'content' => 'Chọn không gian, thời gian, trang phục phù hợp...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Thực hành Thiền Hơi thở (Anapanasati)',
                    'description' => 'Học cách quan sát và ý thức về hơi thở, nền tảng của nhiều phương pháp thiền định.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tại sao lại tập trung vào hơi thở?', 'content' => 'Hơi thở là mỏ neo của sự chú tâm...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Hướng dẫn chi tiết kỹ thuật thiền hơi thở', 'content' => 'Quan sát sự phồng xẹp của bụng, cảm nhận hơi thở ở mũi...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Đối phó với tâm lang thang và những suy nghĩ xao nhãng', 'content' => 'Nhẹ nhàng đưa tâm trở lại với hơi thở...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Thực hành thiền hơi thở có hướng dẫn (Guided Meditation)', 'content' => 'Bài tập thực hành 5 phút, 10 phút, 15 phút...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Thiền Quét Cơ thể (Body Scan Meditation)',
                    'description' => 'Phát triển sự nhận biết về các cảm giác trên cơ thể, giải tỏa căng thẳng và kết nối sâu hơn với cơ thể.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Mục đích và lợi ích của thiền quét cơ thể', 'content' => 'Nhận biết căng thẳng tích tụ, tăng cường sự thư giãn...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Hướng dẫn thực hành thiền quét cơ thể từ đầu đến chân', 'content' => 'Chú ý đến từng bộ phận, cảm nhận không phán xét...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Làm quen với các cảm giác khác nhau trên cơ thể', 'content' => 'Căng, mỏi, ấm, lạnh, ngứa, đau...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Thực hành thiền quét cơ thể có hướng dẫn', 'content' => 'Bài tập thực hành 20 phút, 30 phút...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Thiền Từ tâm (Metta Meditation - Loving-Kindness)',
                    'description' => 'Nuôi dưỡng lòng từ ái, sự bao dung và tình yêu thương đối với bản thân và mọi chúng sinh.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Khái niệm và ý nghĩa của thiền từ tâm', 'content' => 'Phát triển lòng trắc ẩn, giảm bớt sự phán xét...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các cụm từ thực hành thiền từ tâm', 'content' => '"Cầu cho tôi được an vui", "Cầu cho bạn được hạnh phúc"...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Hướng dẫn thực hành thiền từ tâm cho bản thân, người thân yêu, người khó khăn và tất cả chúng sinh', 'content' => 'Mở rộng vòng tay yêu thương...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Lợi ích của việc thực hành thiền từ tâm đối với các mối quan hệ', 'content' => 'Cải thiện sự kết nối, giảm xung đột...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Ứng dụng Mindfulness vào Cuộc sống Hàng ngày',
                    'description' => 'Mang chánh niệm vào các hoạt động thường nhật để sống ý nghĩa và trọn vẹn hơn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Chánh niệm khi ăn uống (Mindful Eating)', 'content' => 'Cảm nhận hương vị, kết cấu, trân trọng thực phẩm...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Chánh niệm khi đi bộ (Mindful Walking)', 'content' => 'Cảm nhận bước chân, sự tiếp xúc với mặt đất...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Chánh niệm trong giao tiếp và lắng nghe', 'content' => 'Hiện diện trọn vẹn, lắng nghe sâu sắc...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đối phó với cảm xúc khó khăn bằng chánh niệm (Kỹ thuật RAIN)', 'content' => 'Recognize, Allow, Investigate, Nurture...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Xây dựng Thói quen Thực hành Bền vững',
                    'description' => 'Thiết lập và duy trì việc thực hành mindfulness và thiền định như một phần không thể thiếu của cuộc sống.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Thiết lập mục tiêu thực hành cá nhân và lịch trình phù hợp', 'content' => 'Bắt đầu từ những buổi thực hành ngắn, tăng dần thời gian...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Vượt qua những khó khăn và thử thách trong quá trình thực hành', 'content' => 'Sự nhàm chán, thiếu động lực, không có thời gian...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tìm kiếm sự hỗ trợ từ cộng đồng và các nguồn tài liệu', 'content' => 'Tham gia nhóm thiền, đọc sách, nghe podcast...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Mindfulness như một hành trình trọn đời', 'content' => 'Tiếp tục khám phá và làm sâu sắc thêm sự thực hành của bạn...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Lãnh đạo Hiệu quả: Truyền cảm hứng và Dẫn dắt Đội nhóm Thành công',
            'description' => 'Phát triển những phẩm chất và kỹ năng cần thiết của một nhà lãnh đạo xuất sắc, từ việc xây dựng tầm nhìn, tạo động lực đến việc trao quyền và phát triển đội ngũ.',
            'price' => 950000.00,
            'categoryIds' => [65, 63], // Lãnh đạo, Phát triển cá nhân
            'requirements' => [
                'Đang hoặc mong muốn trở thành người quản lý, lãnh đạo.',
                'Cam kết học hỏi và áp dụng các nguyên tắc lãnh đạo vào thực tế.'
            ],
            'objectives' => [
                'Hiểu rõ các phong cách lãnh đạo khác nhau và lựa chọn phong cách phù hợp.',
                'Xây dựng tầm nhìn và chiến lược rõ ràng cho đội nhóm.',
                'Truyền cảm hứng, tạo động lực và gắn kết các thành viên trong đội.',
                'Giao tiếp hiệu quả, ủy thác công việc và trao quyền cho nhân viên.',
                'Phát triển năng lực của từng cá nhân và xây dựng đội nhóm vững mạnh.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Nền tảng về Lãnh đạo Hiện đại',
                    'description' => 'Tìm hiểu bản chất của lãnh đạo, các mô hình và phẩm chất cần có của một nhà lãnh đạo thế kỷ 21.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Lãnh đạo là gì? Phân biệt lãnh đạo và quản lý', 'content' => 'Vai trò, trách nhiệm và sự khác biệt cốt lõi...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các phong cách lãnh đạo phổ biến (Độc đoán, Dân chủ, Tự do, Chuyển đổi, Phục vụ)', 'content' => 'Ưu nhược điểm và tình huống áp dụng của từng phong cách...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Những phẩm chất cốt lõi của nhà lãnh đạo hiệu quả', 'content' => 'Tầm nhìn, chính trực, quyết đoán, đồng cảm, khả năng truyền cảm hứng...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Thách thức của lãnh đạo trong môi trường VUCA (Biến động, Không chắc chắn, Phức tạp, Mơ hồ)', 'content' => 'Cách thích ứng và dẫn dắt trong bối cảnh thay đổi liên tục...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Xây dựng Tầm nhìn và Chiến lược Lãnh đạo',
                    'description' => 'Học cách định hình một tầm nhìn hấp dẫn và xây dựng lộ trình chiến lược để đạt được mục tiêu.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tầm quan trọng của tầm nhìn trong lãnh đạo', 'content' => 'Tầm nhìn là kim chỉ nam, nguồn cảm hứng cho đội nhóm...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Các bước xây dựng một tầm nhìn mạnh mẽ và truyền cảm hứng', 'content' => 'Phân tích bối cảnh, xác định giá trị cốt lõi, hình dung tương lai...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Truyền đạt tầm nhìn hiệu quả đến đội nhóm', 'content' => 'Sử dụng câu chuyện, hình ảnh, tạo sự đồng thuận...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Chuyển hóa tầm nhìn thành mục tiêu và kế hoạch hành động chiến lược', 'content' => 'SMART goals, phân công trách nhiệm, theo dõi tiến độ...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Tạo Động lực và Gắn kết Đội nhóm',
                    'description' => 'Khám phá các yếu tố tạo động lực và xây dựng một môi trường làm việc tích cực, nơi mọi người cảm thấy được trân trọng và cống hiến hết mình.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Hiểu về các lý thuyết tạo động lực (Maslow, Herzberg, Lý thuyết X-Y)', 'content' => 'Các yếu tố thúc đẩy và duy trì động lực làm việc...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Kỹ thuật tạo động lực cho nhân viên: Ghi nhận, khen thưởng, tạo cơ hội phát triển', 'content' => 'Cách công nhận thành tích, phản hồi tích cực...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Xây dựng văn hóa đội nhóm tích cực và tin cậy', 'content' => 'Khuyến khích hợp tác, giao tiếp cởi mở, tôn trọng sự khác biệt...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Vai trò của lãnh đạo trong việc giải quyết xung đột và duy trì sự gắn kết', 'content' => 'Lắng nghe, hòa giải, tìm kiếm giải pháp chung...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giao tiếp Hiệu quả và Trao quyền',
                    'description' => 'Nâng cao kỹ năng giao tiếp của nhà lãnh đạo và học cách ủy thác, trao quyền để phát huy tối đa tiềm năng của nhân viên.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Kỹ năng lắng nghe chủ động và phản hồi xây dựng của nhà lãnh đạo', 'content' => 'Lắng nghe để thấu hiểu, phản hồi để phát triển...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Giao tiếp rõ ràng, minh bạch và truyền cảm hứng', 'content' => 'Cách truyền đạt thông điệp, thuyết trình, tổ chức cuộc họp hiệu quả...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Nghệ thuật ủy thác công việc hiệu quả', 'content' => 'Chọn đúng người, giao việc rõ ràng, cung cấp hỗ trợ, theo dõi và đánh giá...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Trao quyền cho nhân viên: Xây dựng sự tự chủ và trách nhiệm', 'content' => 'Tin tưởng, tạo không gian cho sự sáng tạo và ra quyết định...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Phát triển Năng lực Đội ngũ và Huấn luyện (Coaching)',
                    'description' => 'Trở thành người cố vấn, huấn luyện viên giúp các thành viên trong đội phát triển kỹ năng và đạt được tiềm năng cao nhất.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Đánh giá năng lực và nhu cầu phát triển của từng thành viên', 'content' => 'Xác định điểm mạnh, điểm yếu, cơ hội phát triển...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Kỹ năng huấn luyện (Coaching) hiệu quả: Đặt câu hỏi, lắng nghe, đưa ra thử thách', 'content' => 'Mô hình GROW trong coaching...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Cung cấp phản hồi mang tính xây dựng để giúp nhân viên cải thiện', 'content' => 'Kỹ thuật phản hồi SBI (Situation-Behavior-Impact)...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Xây dựng lộ trình phát triển sự nghiệp cho nhân viên tiềm năng', 'content' => 'Đào tạo, luân chuyển công việc, giao phó dự án thách thức...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Lãnh đạo Sự thay đổi và Xây dựng Di sản',
                    'description' => 'Học cách dẫn dắt đội nhóm vượt qua những thay đổi, thích ứng với thách thức và tạo dựng một di sản lãnh đạo bền vững.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Quản lý và dẫn dắt sự thay đổi trong tổ chức', 'content' => 'Các giai đoạn của sự thay đổi, cách vượt qua sự kháng cự...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Xây dựng khả năng phục hồi và thích ứng cho đội nhóm', 'content' => 'Khuyến khích sự linh hoạt, học hỏi từ thất bại...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Đạo đức trong lãnh đạo và xây dựng uy tín cá nhân', 'content' => 'Tính chính trực, công bằng, trách nhiệm xã hội...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Phát triển thế hệ lãnh đạo kế cận và để lại di sản tích cực', 'content' => 'Mentoring, tạo cơ hội cho người trẻ, xây dựng văn hóa học tập...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Thiết kế Sản phẩm Số: Từ Ý tưởng đến MVP',
            'description' => 'Học quy trình hoàn chỉnh để thiết kế và phát triển một sản phẩm số (web/app) thành công, từ giai đoạn nghiên cứu, lên ý tưởng, thiết kế UI/UX đến việc tạo ra Sản phẩm Khả thi Tối thiểu (MVP).',
            'price' => 1100000.00,
            'categoryIds' => [55, 51, 49], // Thiết kế sản phẩm, Thiết kế UI/UX, Thiết kế
            'requirements' => [
                'Có kiến thức cơ bản về UI/UX là một lợi thế, nhưng không bắt buộc.',
                'Tư duy logic, khả năng giải quyết vấn đề và đam mê tạo ra sản phẩm hữu ích.',
                'Máy tính có kết nối internet và các công cụ thiết kế (Figma, Sketch, Adobe XD - tùy chọn).'
            ],
            'objectives' => [
                'Nắm vững quy trình thiết kế sản phẩm số từ A đến Z.',
                'Thực hiện nghiên cứu người dùng (User Research) và phân tích đối thủ cạnh tranh.',
                'Phát triển ý tưởng sản phẩm, xác định tính năng cốt lõi và xây dựng User Persona, User Journey Map.',
                'Thiết kế Wireframe, Mockup và Prototype tương tác cho sản phẩm.',
                'Hiểu về khái niệm MVP và cách lên kế hoạch phát triển MVP hiệu quả.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tổng quan về Thiết kế Sản phẩm Số và Quy trình',
                    'description' => 'Giới thiệu về lĩnh vực thiết kế sản phẩm số, vai trò của Product Designer và các giai đoạn chính trong quy trình.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Thiết kế Sản phẩm Số là gì? Vai trò của Product Designer', 'content' => 'Sự khác biệt với UI/UX Designer, các kỹ năng cần có...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các giai đoạn chính trong quy trình thiết kế sản phẩm (Discover, Define, Design, Deliver/Develop, Iterate)', 'content' => 'Mô hình Double Diamond và các biến thể...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Tư duy Thiết kế (Design Thinking) trong phát triển sản phẩm', 'content' => 'Empathize, Define, Ideate, Prototype, Test - ứng dụng thực tế...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các công cụ hỗ trợ Product Designer (Figma, Miro, Jira, Notion...)', 'content' => 'Giới thiệu các công cụ phổ biến cho từng giai đoạn...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Giai đoạn Khám phá (Discovery) - Nghiên cứu Người dùng và Thị trường',
                    'description' => 'Học cách thu thập thông tin chi tiết về người dùng mục tiêu, nhu cầu của họ và bối cảnh thị trường.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tầm quan trọng của User Research trong thiết kế sản phẩm', 'content' => 'Tại sao cần hiểu người dùng trước khi thiết kế...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Các phương pháp nghiên cứu người dùng định tính (Phỏng vấn, Quan sát, Usability Testing)', 'content' => 'Cách tiến hành và phân tích kết quả...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Các phương pháp nghiên cứu người dùng định lượng (Khảo sát, Phân tích dữ liệu)', 'content' => 'Thiết kế khảo sát, sử dụng Google Analytics...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Phân tích đối thủ cạnh tranh và xu hướng thị trường', 'content' => 'Xác định điểm mạnh, điểm yếu của đối thủ, cơ hội cho sản phẩm...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Giai đoạn Xác định (Define) - Định hình Vấn đề và Cơ hội',
                    'description' => 'Tổng hợp kết quả nghiên cứu để xác định rõ vấn đề cần giải quyết, đối tượng người dùng và cơ hội cho sản phẩm.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Xây dựng User Persona (Chân dung người dùng mục tiêu)', 'content' => 'Cách tạo Persona chi tiết và hữu ích...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tạo User Journey Map (Bản đồ hành trình người dùng)', 'content' => 'Hiểu rõ các điểm chạm, cảm xúc và khó khăn của người dùng...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Xác định Problem Statement (Phát biểu vấn đề) và How Might We questions', 'content' => 'Tập trung vào vấn đề cốt lõi cần giải quyết...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Xác định Value Proposition (Tuyên bố giá trị) của sản phẩm', 'content' => 'Sản phẩm của bạn mang lại lợi ích gì độc đáo cho người dùng...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giai đoạn Lên ý tưởng (Ideation) và Xác định Tính năng',
                    'description' => 'Sử dụng các kỹ thuật sáng tạo để phát triển nhiều ý tưởng giải pháp và lựa chọn các tính năng cốt lõi cho sản phẩm.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các kỹ thuật Brainstorming và phát triển ý tưởng (Crazy 8s, Storyboarding)', 'content' => 'Khuyến khích sự sáng tạo và đa dạng ý tưởng...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Sơ đồ luồng người dùng (User Flows) và Sơ đồ thông tin (Information Architecture)', 'content' => 'Thiết kế cấu trúc và điều hướng cho sản phẩm...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Xác định và ưu tiên hóa các tính năng sản phẩm (MoSCoW, RICE scoring)', 'content' => 'Lựa chọn những gì cần xây dựng cho MVP...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Tạo Low-fidelity Wireframes (Khung sườn mức độ chi tiết thấp)', 'content' => 'Phác thảo nhanh cấu trúc và bố cục các màn hình...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Giai đoạn Thiết kế (Design) - UI, UX và Prototyping',
                    'description' => 'Chuyển hóa ý tưởng thành các thiết kế giao diện trực quan, trải nghiệm người dùng mượt mà và các mẫu thử tương tác.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Nguyên tắc thiết kế UI/UX hiệu quả (Visual Hierarchy, Consistency, Feedback)', 'content' => 'Các quy tắc vàng trong thiết kế giao diện và trải nghiệm...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Thiết kế High-fidelity Mockups (Mô hình chi tiết cao) với màu sắc, typography, icon', 'content' => 'Sử dụng Figma hoặc các công cụ tương tự để tạo giao diện hoàn chỉnh...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Xây dựng Design System cơ bản (Components, Styles)', 'content' => 'Đảm bảo tính nhất quán và hiệu quả trong thiết kế...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tạo Interactive Prototypes (Mẫu thử tương tác) để kiểm thử', 'content' => 'Liên kết các màn hình, thêm hiệu ứng chuyển động...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Phát triển Sản phẩm Khả thi Tối thiểu (MVP) và Kiểm thử',
                    'description' => 'Lên kế hoạch xây dựng MVP, phối hợp với đội ngũ phát triển và thực hiện kiểm thử với người dùng để thu thập phản hồi.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Khái niệm Sản phẩm Khả thi Tối thiểu (MVP) và tầm quan trọng', 'content' => 'Tại sao cần MVP? Lợi ích của việc ra mắt sớm và học hỏi...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Lên kế hoạch phát triển MVP: Xác định phạm vi, nguồn lực và thời gian', 'content' => 'Tập trung vào các tính năng cốt lõi mang lại giá trị cao nhất...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Phối hợp với Developer trong quá trình phát triển (Handoff, Communication)', 'content' => 'Cách bàn giao thiết kế, giải đáp thắc mắc, đảm bảo chất lượng...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Thực hiện Usability Testing với MVP và thu thập phản hồi để cải tiến', 'content' => 'Quan sát người dùng tương tác, ghi nhận vấn đề, lặp lại quy trình...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Đọc nhanh và Ghi nhớ Siêu tốc: Khai phá Tiềm năng Não bộ',
            'description' => 'Nắm vững các kỹ thuật đọc nhanh tiên tiến và phương pháp ghi nhớ hiệu quả để tiếp thu kiến thức nhanh hơn, nhớ lâu hơn và nâng cao hiệu suất học tập, làm việc.',
            'price' => 590000.00,
            'categoryIds' => [68, 63], // Đọc nhanh & Ghi nhớ, Phát triển cá nhân
            'requirements' => [
                'Khả năng đọc hiểu cơ bản.',
                'Sự kiên trì luyện tập hàng ngày.',
                'Tài liệu đọc (sách, báo, tài liệu học tập).'
            ],
            'objectives' => [
                'Tăng tốc độ đọc lên ít nhất gấp 2-3 lần mà vẫn đảm bảo khả năng hiểu.',
                'Loại bỏ các thói quen đọc chậm và không hiệu quả.',
                'Nắm vững các kỹ thuật ghi nhớ siêu đẳng (Phương pháp Loci, Liên kết, Kể chuyện).',
                'Cải thiện khả năng tập trung và ghi nhớ thông tin chi tiết.',
                'Ứng dụng vào việc học tập, nghiên cứu và công việc để đạt hiệu quả cao hơn.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Khám phá Tiềm năng Đọc và Ghi nhớ của Bạn',
                    'description' => 'Hiểu về cơ chế đọc của não bộ, các yếu tố ảnh hưởng đến tốc độ đọc và khả năng ghi nhớ hiện tại.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Não bộ và quá trình đọc hiểu: Điều gì thực sự xảy ra?', 'content' => 'Vai trò của mắt, não, các vùng xử lý thông tin...', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các yếu tố ảnh hưởng đến tốc độ đọc (Đọc thầm, Đọc lại, Thiếu tập trung)', 'content' => 'Nhận diện những thói quen cản trở tốc độ đọc...', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Kiểm tra tốc độ đọc và khả năng ghi nhớ hiện tại của bạn', 'content' => 'Bài test đầu vào để đánh giá và đặt mục tiêu...', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Thiết lập mục tiêu đọc nhanh và ghi nhớ hiệu quả', 'content' => 'Xác định tốc độ mong muốn, mức độ hiểu cần đạt...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Kỹ thuật Loại bỏ Thói quen Đọc chậm',
                    'description' => 'Học cách khắc phục những rào cản phổ biến khiến bạn đọc chậm và không hiệu quả.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Loại bỏ việc đọc thầm (Subvocalization)', 'content' => 'Tại sao đọc thầm làm chậm tốc độ? Các bài tập giảm đọc thầm...', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Hạn chế việc đọc lại (Regression) không cần thiết', 'content' => 'Nguyên nhân đọc lại và cách rèn luyện đọc lướt qua...', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Mở rộng tầm nhìn (Peripheral Vision) để đọc nhiều từ cùng lúc', 'content' => 'Bài tập mở rộng trường nhìn của mắt...', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Sử dụng vật dẫn đường (Pacer) để tăng tốc độ và sự tập trung', 'content' => 'Dùng ngón tay, bút hoặc con trỏ chuột để dẫn dắt mắt...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Các Phương pháp Đọc nhanh Hiệu quả',
                    'description' => 'Nắm vững các kỹ thuật đọc lướt, đọc quét và đọc tìm ý chính để xử lý lượng lớn thông tin một cách nhanh chóng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Kỹ thuật đọc lướt (Skimming) để nắm bắt ý chính', 'content' => 'Cách đọc tiêu đề, đoạn mở đầu, kết luận, từ khóa...', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Kỹ thuật đọc quét (Scanning) để tìm kiếm thông tin cụ thể', 'content' => 'Tìm tên, ngày tháng, số liệu một cách nhanh chóng...', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Đọc theo cụm từ (Phrase Reading) thay vì từng từ một', 'content' => 'Cách nhóm các từ lại để tăng tốc độ và khả năng hiểu...', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Luyện tập đọc nhanh với các loại tài liệu khác nhau (Sách, Báo, Tài liệu chuyên ngành)', 'content' => 'Điều chỉnh kỹ thuật phù hợp với từng loại văn bản...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Nền tảng của Trí nhớ Siêu đẳng',
                    'description' => 'Hiểu về cơ chế hoạt động của trí nhớ và các nguyên tắc giúp thông tin được lưu trữ lâu dài.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các loại trí nhớ: Trí nhớ ngắn hạn, dài hạn, giác quan', 'content' => 'Cách thông tin được chuyển từ trí nhớ ngắn hạn sang dài hạn...', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Nguyên tắc ghi nhớ hiệu quả: Sự tập trung, Liên kết, Hình dung, Lặp lại', 'content' => 'Các yếu tố then chốt để ghi nhớ tốt hơn...', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Vai trò của cảm xúc và sự thú vị trong việc ghi nhớ', 'content' => 'Tại sao chúng ta dễ nhớ những điều gắn liền với cảm xúc mạnh...', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Chế độ dinh dưỡng và lối sống ảnh hưởng đến trí nhớ', 'content' => 'Thực phẩm tốt cho não, giấc ngủ, vận động...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Các Kỹ thuật Ghi nhớ Siêu tốc',
                    'description' => 'Làm chủ các phương pháp ghi nhớ đã được chứng minh hiệu quả để nhớ mọi thứ từ danh sách, số liệu đến kiến thức phức tạp.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Phương pháp Liên kết (Association Method) để nhớ các cặp thông tin', 'content' => 'Tạo mối liên hệ giữa thông tin mới và thông tin đã biết...', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Phương pháp Loci (Hành trình Trí nhớ - Memory Palace)', 'content' => 'Gắn thông tin cần nhớ vào các địa điểm quen thuộc...', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Kỹ thuật Kể chuyện (Story Method) để nhớ chuỗi thông tin', 'content' => 'Tạo một câu chuyện logic hoặc hài hước để xâu chuỗi các mục...', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Ghi nhớ tên và khuôn mặt hiệu quả', 'content' => 'Các mẹo và kỹ thuật để không bao giờ quên tên người khác...', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Ứng dụng Đọc nhanh và Ghi nhớ vào Thực tế',
                    'description' => 'Áp dụng các kỹ năng đã học để nâng cao hiệu suất trong học tập, công việc và cuộc sống hàng ngày.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Đọc và ghi nhớ tài liệu học tập hiệu quả (Sách giáo khoa, Bài giảng)', 'content' => 'Cách tóm tắt, ghi chú mindmap, ôn tập thông minh...', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Ghi nhớ từ vựng ngoại ngữ nhanh chóng và bền vững', 'content' => 'Sử dụng flashcard, phương pháp liên tưởng, lặp lại ngắt quãng...', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chuẩn bị cho các bài thuyết trình và ghi nhớ nội dung quan trọng', 'content' => 'Kỹ thuật ghi nhớ dàn ý, số liệu, trích dẫn...', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Xây dựng thói quen đọc sách và học hỏi suốt đời', 'content' => 'Duy trì sự tò mò, khám phá kiến thức mới mỗi ngày...', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Khởi nghiệp và Phát triển Doanh nghiệp',
            'description' => 'Học cách xây dựng và phát triển doanh nghiệp thành công, từ ý tưởng đến thực thi.',
            'price' => 999000.00,
            'categoryIds' => [35, 34], // Doanh nghiệp khởi nghiệp, Quản trị kinh doanh
            'requirements' => [
                'Đam mê kinh doanh và khởi nghiệp.',
                'Có kiến thức cơ bản về thị trường và khách hàng.',
            ],
            'objectives' => [
                'Xây dựng ý tưởng kinh doanh khả thi.',
                'Phân tích thị trường và xác định khách hàng mục tiêu.',
                'Lập kế hoạch kinh doanh chi tiết.',
                'Quản lý tài chính và huy động vốn.',
                'Xây dựng và phát triển đội ngũ.',
                'Marketing và bán hàng hiệu quả.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tư duy Khởi nghiệp và Ý tưởng Kinh doanh',
                    'description' => 'Tìm hiểu về tư duy khởi nghiệp và cách tìm kiếm ý tưởng kinh doanh.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Tư duy Khởi nghiệp là gì?', 'content' => 'Đặc điểm và tầm quan trọng của tư duy khởi nghiệp.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Tìm kiếm và đánh giá ý tưởng kinh doanh', 'content' => 'Phương pháp tìm kiếm và đánh giá ý tưởng.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Phân tích SWOT và PESTLE', 'content' => 'Ứng dụng SWOT và PESTLE trong phân tích kinh doanh.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Nghiên cứu thị trường và xác định nhu cầu', 'content' => 'Cách nghiên cứu thị trường và xác định nhu cầu khách hàng.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 2: Lập Kế hoạch Kinh doanh và Chiến lược',
                    'description' => 'Hướng dẫn lập kế hoạch kinh doanh chi tiết và xây dựng chiến lược.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Cấu trúc và nội dung của bản kế hoạch kinh doanh', 'content' => 'Các phần chính của bản kế hoạch kinh doanh.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Phân tích thị trường và đối thủ cạnh tranh', 'content' => 'Cách phân tích thị trường và đối thủ.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Chiến lược sản phẩm và dịch vụ', 'content' => 'Xây dựng chiến lược sản phẩm và dịch vụ.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Chiến lược Marketing và bán hàng', 'content' => 'Xây dựng chiến lược marketing và bán hàng.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Kế hoạch tài chính và dự báo', 'content' => 'Lập kế hoạch tài chính và dự báo.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 3: Quản lý Tài chính và Huy động Vốn',
                    'description' => 'Tìm hiểu về quản lý tài chính và cách huy động vốn cho doanh nghiệp.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Quản lý dòng tiền và ngân sách', 'content' => 'Quản lý dòng tiền và ngân sách hiệu quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Phân tích điểm hòa vốn', 'content' => 'Cách phân tích điểm hòa vốn.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Các hình thức huy động vốn', 'content' => 'Tìm hiểu về các hình thức huy động vốn.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Chuẩn bị hồ sơ và thuyết trình với nhà đầu tư', 'content' => 'Cách chuẩn bị hồ sơ và thuyết trình với nhà đầu tư.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Quản lý rủi ro tài chính', 'content' => 'Quản lý rủi ro tài chính.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 4: Xây dựng và Phát triển Đội ngũ',
                    'description' => 'Cách xây dựng và phát triển đội ngũ nhân sự.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Tuyển dụng và lựa chọn nhân sự', 'content' => 'Quy trình tuyển dụng và lựa chọn nhân sự.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Xây dựng văn hóa doanh nghiệp', 'content' => 'Xây dựng văn hóa doanh nghiệp mạnh mẽ.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Quản lý và phát triển nhân viên', 'content' => 'Quản lý và phát triển nhân viên.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Xây dựng và duy trì đội ngũ hiệu quả', 'content' => 'Xây dựng và duy trì đội ngũ hiệu quả.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 5: Marketing và Bán hàng',
                    'description' => 'Các chiến lược marketing và bán hàng hiệu quả.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Xây dựng thương hiệu', 'content' => 'Xây dựng thương hiệu mạnh mẽ.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Marketing nội dung và SEO', 'content' => 'Marketing nội dung và SEO.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Quảng cáo trực tuyến (Google Ads, Facebook Ads)', 'content' => 'Quảng cáo trực tuyến.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Chiến lược bán hàng và chăm sóc khách hàng', 'content' => 'Chiến lược bán hàng và chăm sóc khách hàng.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 6: Quản lý và Điều hành Doanh nghiệp',
                    'description' => 'Quản lý và điều hành doanh nghiệp hiệu quả.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Quản lý dự án và quy trình làm việc', 'content' => 'Quản lý dự án và quy trình làm việc.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Đánh giá hiệu quả hoạt động kinh doanh', 'content' => 'Đánh giá hiệu quả hoạt động kinh doanh.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Giải quyết vấn đề và ra quyết định', 'content' => 'Giải quyết vấn đề và ra quyết định.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Phát triển và mở rộng doanh nghiệp', 'content' => 'Phát triển và mở rộng doanh nghiệp.', 'sortOrder' => 4],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Khóa học Quản lý Dự án Chuyên nghiệp theo PMI',
            'description' => 'Học các phương pháp quản lý dự án theo chuẩn PMI (Project Management Institute).',
            'price' => 1199000.00,
            'categoryIds' => [36, 34], // Quản lý dự án, Quản trị kinh doanh
            'requirements' => [
                'Có kiến thức cơ bản về quản lý dự án.',
                'Mong muốn đạt chứng chỉ PMP (Project Management Professional).',
            ],
            'objectives' => [
                'Hiểu rõ các quy trình quản lý dự án theo PMI.',
                'Lập kế hoạch dự án chi tiết.',
                'Quản lý phạm vi, thời gian, chi phí, chất lượng, rủi ro và nguồn lực.',
                'Sử dụng các công cụ và kỹ thuật quản lý dự án.',
                'Chuẩn bị cho kỳ thi PMP.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tổng quan về Quản lý Dự án và PMI',
                    'description' => 'Giới thiệu về quản lý dự án và chuẩn PMI.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Quản lý dự án là gì?', 'content' => 'Khái niệm và tầm quan trọng của quản lý dự án.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Giới thiệu về PMI và PMBOK Guide', 'content' => 'PMI, PMBOK Guide và các chứng chỉ liên quan.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các nhóm quy trình trong quản lý dự án', 'content' => 'Các nhóm quy trình: Khởi tạo, Lập kế hoạch, Thực hiện, Kiểm soát và Kết thúc.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Vai trò của Project Manager', 'content' => 'Các kỹ năng và trách nhiệm của Project Manager.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 2: Quản lý Phạm vi Dự án',
                    'description' => 'Quản lý phạm vi dự án hiệu quả.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Thu thập yêu cầu', 'content' => 'Phương pháp thu thập yêu cầu.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Xác định phạm vi', 'content' => 'Xác định và phân chia phạm vi dự án.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Tạo cấu trúc phân chia công việc (WBS)', 'content' => 'Cách tạo WBS.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Kiểm soát phạm vi', 'content' => 'Kiểm soát và quản lý thay đổi phạm vi.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 3: Quản lý Thời gian Dự án',
                    'description' => 'Quản lý thời gian dự án hiệu quả.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Xác định các hoạt động', 'content' => 'Xác định các hoạt động cần thiết.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Sắp xếp các hoạt động', 'content' => 'Sắp xếp các hoạt động theo trình tự.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Ước tính thời gian và nguồn lực', 'content' => 'Ước tính thời gian và nguồn lực.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Lập lịch trình dự án', 'content' => 'Lập lịch trình dự án và sử dụng các công cụ.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Kiểm soát lịch trình', 'content' => 'Kiểm soát và điều chỉnh lịch trình.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 4: Quản lý Chi phí Dự án',
                    'description' => 'Quản lý chi phí dự án hiệu quả.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Ước tính chi phí', 'content' => 'Các phương pháp ước tính chi phí.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Lập ngân sách', 'content' => 'Lập ngân sách dự án.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Kiểm soát chi phí', 'content' => 'Kiểm soát và quản lý chi phí.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Phân tích giá trị kiếm được (Earned Value Management)', 'content' => 'Phân tích giá trị kiếm được.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 5: Quản lý Chất lượng Dự án',
                    'description' => 'Quản lý chất lượng dự án hiệu quả.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Lập kế hoạch quản lý chất lượng', 'content' => 'Lập kế hoạch quản lý chất lượng.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Thực hiện đảm bảo chất lượng', 'content' => 'Thực hiện đảm bảo chất lượng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Kiểm soát chất lượng', 'content' => 'Kiểm soát chất lượng và các công cụ.', 'sortOrder' => 3],
                    ],
                ],
                [
                    'title' => 'Chương 6: Quản lý Rủi ro Dự án',
                    'description' => 'Quản lý rủi ro dự án hiệu quả.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Lập kế hoạch quản lý rủi ro', 'content' => 'Lập kế hoạch quản lý rủi ro.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Nhận diện rủi ro', 'content' => 'Phương pháp nhận diện rủi ro.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Phân tích rủi ro định tính và định lượng', 'content' => 'Phân tích rủi ro.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Lập kế hoạch ứng phó rủi ro', 'content' => 'Lập kế hoạch ứng phó rủi ro.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Theo dõi và kiểm soát rủi ro', 'content' => 'Theo dõi và kiểm soát rủi ro.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 7: Quản lý Nguồn lực Dự án',
                    'description' => 'Quản lý nguồn lực dự án hiệu quả.',
                    'sortOrder' => 7,
                    'lessons' => [
                        ['title' => 'Bài 7.1: Lập kế hoạch quản lý nguồn lực', 'content' => 'Lập kế hoạch quản lý nguồn lực.', 'sortOrder' => 1],
                        ['title' => 'Bài 7.2: Tuyển dụng và phát triển đội dự án', 'content' => 'Tuyển dụng và phát triển đội dự án.', 'sortOrder' => 2],
                        ['title' => 'Bài 7.3: Quản lý đội dự án', 'content' => 'Quản lý đội dự án.', 'sortOrder' => 3],
                    ],
                ],
                [
                    'title' => 'Chương 8: Quản lý Truyền thông Dự án',
                    'description' => 'Quản lý truyền thông dự án hiệu quả.',
                    'sortOrder' => 8,
                    'lessons' => [
                        ['title' => 'Bài 8.1: Lập kế hoạch quản lý truyền thông', 'content' => 'Lập kế hoạch quản lý truyền thông.', 'sortOrder' => 1],
                        ['title' => 'Bài 8.2: Phân phối thông tin', 'content' => 'Phân phối thông tin.', 'sortOrder' => 2],
                        ['title' => 'Bài 8.3: Báo cáo hiệu suất', 'content' => 'Báo cáo hiệu suất.', 'sortOrder' => 3],
                    ],
                ],
                [
                    'title' => 'Chương 9: Quản lý Các bên liên quan Dự án',
                    'description' => 'Quản lý các bên liên quan dự án hiệu quả.',
                    'sortOrder' => 9,
                    'lessons' => [
                        ['title' => 'Bài 9.1: Nhận diện các bên liên quan', 'content' => 'Nhận diện các bên liên quan.', 'sortOrder' => 1],
                        ['title' => 'Bài 9.2: Lập kế hoạch quản lý các bên liên quan', 'content' => 'Lập kế hoạch quản lý các bên liên quan.', 'sortOrder' => 2],
                        ['title' => 'Bài 9.3: Quản lý sự tham gia của các bên liên quan', 'content' => 'Quản lý sự tham gia của các bên liên quan.', 'sortOrder' => 3],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Khóa học Phân tích Kinh doanh và Chuyển đổi Số',
            'description' => 'Học cách phân tích kinh doanh và ứng dụng công nghệ để chuyển đổi số doanh nghiệp.',
            'price' => 850000.00,
            'categoryIds' => [39, 34], // Phân tích kinh doanh, Quản trị kinh doanh
            'requirements' => [
                'Có kiến thức cơ bản về kinh doanh.',
                'Mong muốn tìm hiểu về phân tích dữ liệu và chuyển đổi số.',
            ],
            'objectives' => [
                'Hiểu rõ về phân tích kinh doanh và chuyển đổi số.',
                'Thu thập và phân tích dữ liệu kinh doanh.',
                'Xác định các vấn đề và cơ hội kinh doanh.',
                'Đề xuất các giải pháp và chiến lược chuyển đổi số.',
                'Sử dụng các công cụ phân tích dữ liệu.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Phân tích Kinh doanh và Chuyển đổi Số',
                    'description' => 'Tổng quan về phân tích kinh doanh và chuyển đổi số.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Phân tích kinh doanh là gì?', 'content' => 'Khái niệm và vai trò của phân tích kinh doanh.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Chuyển đổi số là gì?', 'content' => 'Khái niệm và tầm quan trọng của chuyển đổi số.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Mối quan hệ giữa phân tích kinh doanh và chuyển đổi số', 'content' => 'Cách phân tích kinh doanh hỗ trợ chuyển đổi số.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các xu hướng công nghệ trong chuyển đổi số', 'content' => 'Các xu hướng công nghệ.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 2: Thu thập và Phân tích Dữ liệu Kinh doanh',
                    'description' => 'Học cách thu thập và phân tích dữ liệu kinh doanh.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Các nguồn dữ liệu kinh doanh', 'content' => 'Các nguồn dữ liệu.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Thu thập và làm sạch dữ liệu', 'content' => 'Cách thu thập và làm sạch dữ liệu.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Phân tích dữ liệu mô tả', 'content' => 'Phân tích dữ liệu mô tả.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Phân tích dữ liệu dự báo', 'content' => 'Phân tích dữ liệu dự báo.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Trực quan hóa dữ liệu', 'content' => 'Trực quan hóa dữ liệu.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 3: Xác định Vấn đề và Cơ hội Kinh doanh',
                    'description' => 'Cách xác định vấn đề và cơ hội kinh doanh.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Phân tích SWOT', 'content' => 'Phân tích SWOT.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Phân tích PESTLE', 'content' => 'Phân tích PESTLE.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phân tích 5 áp lực cạnh tranh của Porter', 'content' => 'Phân tích 5 áp lực cạnh tranh của Porter.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Xác định các vấn đề kinh doanh', 'content' => 'Xác định các vấn đề kinh doanh.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Xác định các cơ hội kinh doanh', 'content' => 'Xác định các cơ hội kinh doanh.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 4: Đề xuất Giải pháp và Chiến lược Chuyển đổi Số',
                    'description' => 'Đề xuất giải pháp và chiến lược chuyển đổi số.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Xây dựng tầm nhìn và mục tiêu chuyển đổi số', 'content' => 'Xây dựng tầm nhìn và mục tiêu.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Lựa chọn các công nghệ phù hợp', 'content' => 'Lựa chọn các công nghệ.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Xây dựng lộ trình chuyển đổi số', 'content' => 'Xây dựng lộ trình.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Quản lý thay đổi và đào tạo nhân viên', 'content' => 'Quản lý thay đổi và đào tạo.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Đo lường và đánh giá hiệu quả', 'content' => 'Đo lường và đánh giá.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 5: Ứng dụng Công cụ Phân tích Dữ liệu',
                    'description' => 'Sử dụng các công cụ phân tích dữ liệu.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Giới thiệu về Excel và Google Sheets', 'content' => 'Excel và Google Sheets.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Phân tích dữ liệu với Excel', 'content' => 'Phân tích dữ liệu với Excel.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Giới thiệu về Power BI', 'content' => 'Power BI.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Phân tích dữ liệu với Power BI', 'content' => 'Phân tích dữ liệu với Power BI.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Các công cụ phân tích dữ liệu khác', 'content' => 'Các công cụ khác.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 6: Các Case Study về Chuyển đổi Số',
                    'description' => 'Nghiên cứu các case study về chuyển đổi số.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Case Study về Chuyển đổi Số trong Bán lẻ', 'content' => 'Case Study.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Case Study về Chuyển đổi Số trong Tài chính', 'content' => 'Case Study.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Case Study về Chuyển đổi Số trong Sản xuất', 'content' => 'Case Study.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Case Study về Chuyển đổi Số trong Marketing', 'content' => 'Case Study.', 'sortOrder' => 4],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Quản trị Kinh doanh Toàn diện: Chiến lược và Thực thi',
            'description' => 'Khóa học này cung cấp kiến thức và kỹ năng cần thiết để quản lý và điều hành doanh nghiệp hiệu quả, từ xây dựng chiến lược đến thực thi và đánh giá.',
            'price' => 1200000.00,
            'categoryIds' => [34, 35],
            'requirements' => [
                'Không yêu cầu kinh nghiệm trước.',
                'Có kiến thức cơ bản về kinh doanh là một lợi thế.',
                'Máy tính có kết nối internet.',
            ],
            'objectives' => [
                'Hiểu rõ các nguyên tắc quản trị kinh doanh cốt lõi.',
                'Xây dựng và triển khai chiến lược kinh doanh hiệu quả.',
                'Quản lý các chức năng chính của doanh nghiệp: Marketing, Tài chính, Vận hành, Nhân sự.',
                'Phân tích và đưa ra quyết định dựa trên dữ liệu.',
                'Phát triển kỹ năng lãnh đạo và quản lý đội nhóm.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tổng quan về Quản trị Kinh doanh',
                    'description' => 'Giới thiệu về quản trị kinh doanh, các khái niệm cơ bản và vai trò của nhà quản lý.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Quản trị Kinh doanh là gì?', 'content' => 'Khái niệm, vai trò và tầm quan trọng của quản trị kinh doanh.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các cấp độ quản lý và vai trò của nhà quản lý', 'content' => 'Các cấp độ quản lý (chiến lược, chiến thuật, tác nghiệp) và vai trò của nhà quản lý ở mỗi cấp độ.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các chức năng chính của quản trị kinh doanh', 'content' => 'Marketing, Tài chính, Vận hành, Nhân sự.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Đạo đức kinh doanh và trách nhiệm xã hội của doanh nghiệp', 'content' => 'Tầm quan trọng của đạo đức kinh doanh và trách nhiệm xã hội.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 2: Xây dựng Chiến lược Kinh doanh',
                    'description' => 'Hướng dẫn xây dựng và triển khai chiến lược kinh doanh hiệu quả.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Phân tích môi trường kinh doanh', 'content' => 'Phân tích PESTLE và SWOT.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Xác định tầm nhìn, sứ mệnh và giá trị cốt lõi', 'content' => 'Cách xác định tầm nhìn, sứ mệnh và giá trị cốt lõi của doanh nghiệp.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Lựa chọn chiến lược kinh doanh', 'content' => 'Các chiến lược tăng trưởng, cạnh tranh và đa dạng hóa.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Triển khai và đánh giá chiến lược', 'content' => 'KPIs và các công cụ đánh giá hiệu quả chiến lược.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Phân tích thị trường và khách hàng', 'content' => 'Nghiên cứu thị trường, phân khúc khách hàng, chân dung khách hàng.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 3: Quản lý Marketing và Bán hàng',
                    'description' => 'Các chiến lược và kỹ thuật marketing và bán hàng hiệu quả.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Marketing Mix (4Ps)', 'content' => 'Product, Price, Place, Promotion.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Marketing kỹ thuật số', 'content' => 'SEO, SEM, Social Media Marketing, Content Marketing.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Quản lý bán hàng', 'content' => 'Quy trình bán hàng, quản lý đội ngũ bán hàng, CRM.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Xây dựng thương hiệu', 'content' => 'Branding và các yếu tố nhận diện thương hiệu.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Phân tích hiệu quả marketing', 'content' => 'ROI, CAC, CLTV.', 'sortOrder' => 5],
                        ['title' => 'Bài 3.6: Chiến lược giá', 'content' => 'Các phương pháp định giá sản phẩm/dịch vụ.', 'sortOrder' => 6],
                    ],
                ],
                [
                    'title' => 'Chương 4: Quản lý Tài chính và Kế toán',
                    'description' => 'Kiến thức về quản lý tài chính và kế toán trong doanh nghiệp.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Báo cáo tài chính', 'content' => 'Bảng cân đối kế toán, báo cáo kết quả kinh doanh, báo cáo lưu chuyển tiền tệ.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Phân tích tài chính', 'content' => 'Các chỉ số tài chính, phân tích dòng tiền.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Quản lý vốn lưu động', 'content' => 'Quản lý hàng tồn kho, khoản phải thu, khoản phải trả.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Ngân sách và dự báo tài chính', 'content' => 'Lập ngân sách, dự báo doanh thu và chi phí.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Quyết định đầu tư', 'content' => 'NPV, IRR.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 5: Quản lý Vận hành và Sản xuất',
                    'description' => 'Các khía cạnh về quản lý vận hành và sản xuất.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Quản lý chuỗi cung ứng', 'content' => 'Quản lý nguyên vật liệu, sản xuất và phân phối.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Quản lý chất lượng', 'content' => 'Các phương pháp quản lý chất lượng (TQM, Six Sigma).', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Quản lý dự án', 'content' => 'Lập kế hoạch, thực hiện và kiểm soát dự án.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tối ưu hóa quy trình', 'content' => 'Lean Management.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Quản lý rủi ro', 'content' => 'Xác định, đánh giá và giảm thiểu rủi ro.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 6: Quản lý Nhân sự',
                    'description' => 'Các chiến lược và kỹ năng quản lý nhân sự hiệu quả.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Tuyển dụng và tuyển chọn nhân sự', 'content' => 'Quy trình tuyển dụng, phỏng vấn.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Đào tạo và phát triển nhân viên', 'content' => 'Các phương pháp đào tạo, phát triển năng lực.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Đánh giá hiệu quả công việc', 'content' => 'KPIs, đánh giá 360 độ.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Quản lý lương thưởng và phúc lợi', 'content' => 'Xây dựng hệ thống lương thưởng, phúc lợi.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Xây dựng văn hóa doanh nghiệp', 'content' => 'Giá trị cốt lõi, tầm nhìn, sứ mệnh.', 'sortOrder' => 5],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Khởi nghiệp thành công: Từ Ý tưởng đến Thực tế',
            'description' => 'Khóa học này cung cấp hướng dẫn chi tiết về quá trình khởi nghiệp, từ việc xác định ý tưởng, lập kế hoạch kinh doanh, đến việc gọi vốn và phát triển doanh nghiệp.',
            'price' => 950000.00,
            'categoryIds' => [35, 36],
            'requirements' => [
                'Đam mê khởi nghiệp.',
                'Có ý tưởng kinh doanh hoặc mong muốn tìm kiếm ý tưởng.',
                'Sẵn sàng học hỏi và làm việc chăm chỉ.',
            ],
            'objectives' => [
                'Xác định và đánh giá các ý tưởng kinh doanh tiềm năng.',
                'Lập kế hoạch kinh doanh chi tiết và hiệu quả.',
                'Tìm kiếm và thu hút vốn đầu tư.',
                'Xây dựng và quản lý đội ngũ khởi nghiệp.',
                'Phát triển và mở rộng doanh nghiệp.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Khởi đầu Hành trình Khởi nghiệp',
                    'description' => 'Giới thiệu về khởi nghiệp, các yếu tố cần thiết và tư duy khởi nghiệp.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Khởi nghiệp là gì?', 'content' => 'Định nghĩa, lợi ích và thách thức của khởi nghiệp.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Tư duy khởi nghiệp', 'content' => 'Sự khác biệt giữa tư duy khởi nghiệp và tư duy truyền thống.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các yếu tố then chốt để khởi nghiệp thành công', 'content' => 'Ý tưởng, thị trường, đội ngũ, vốn.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các mô hình kinh doanh phổ biến', 'content' => 'B2B, B2C, C2C.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 2: Tìm kiếm và Đánh giá Ý tưởng',
                    'description' => 'Hướng dẫn tìm kiếm, đánh giá và lựa chọn ý tưởng kinh doanh.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tìm kiếm ý tưởng kinh doanh', 'content' => 'Các nguồn ý tưởng, brainstorming.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Đánh giá tính khả thi của ý tưởng', 'content' => 'Phân tích thị trường, phân tích đối thủ cạnh tranh.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Xác định vấn đề và giải pháp', 'content' => 'Xác định vấn đề mà bạn muốn giải quyết và giải pháp của bạn.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Nghiên cứu thị trường', 'content' => 'Thu thập thông tin về thị trường mục tiêu.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Phân tích SWOT', 'content' => 'Điểm mạnh, điểm yếu, cơ hội, thách thức.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 3: Lập Kế hoạch Kinh doanh',
                    'description' => 'Hướng dẫn chi tiết về việc lập kế hoạch kinh doanh.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Tổng quan về kế hoạch kinh doanh', 'content' => 'Tầm quan trọng của kế hoạch kinh doanh.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Phân tích thị trường và khách hàng', 'content' => 'Nghiên cứu thị trường, phân khúc khách hàng, chân dung khách hàng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Chiến lược Marketing và Bán hàng', 'content' => 'Marketing Mix, chiến lược giá, kênh phân phối.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Kế hoạch Vận hành', 'content' => 'Quy trình sản xuất, quản lý chuỗi cung ứng.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Kế hoạch Tài chính', 'content' => 'Dự báo doanh thu, chi phí, lợi nhuận, dòng tiền.', 'sortOrder' => 5],
                        ['title' => 'Bài 3.6: Quản lý rủi ro', 'content' => 'Xác định và quản lý rủi ro.', 'sortOrder' => 6],
                    ],
                ],
                [
                    'title' => 'Chương 4: Gọi Vốn và Quản lý Tài chính',
                    'description' => 'Hướng dẫn về gọi vốn và quản lý tài chính cho startup.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các nguồn vốn cho startup', 'content' => 'Vốn tự có, vay ngân hàng, nhà đầu tư thiên thần, quỹ đầu tư mạo hiểm.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Chuẩn bị hồ sơ gọi vốn', 'content' => 'Pitch deck, kế hoạch kinh doanh.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Quản lý dòng tiền', 'content' => 'Theo dõi doanh thu, chi phí, dòng tiền.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Quản lý tài sản', 'content' => 'Quản lý tài sản cố định và tài sản lưu động.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Các chỉ số tài chính quan trọng', 'content' => 'ROI, IRR, BEP.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 5: Xây dựng và Quản lý Đội ngũ',
                    'description' => 'Hướng dẫn xây dựng và quản lý đội ngũ khởi nghiệp.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tuyển dụng và tuyển chọn nhân sự', 'content' => 'Tuyển dụng, phỏng vấn, lựa chọn.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Xây dựng văn hóa doanh nghiệp', 'content' => 'Giá trị cốt lõi, tầm nhìn, sứ mệnh.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Quản lý hiệu suất làm việc', 'content' => 'KPIs, đánh giá hiệu suất.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Giải quyết xung đột', 'content' => 'Các kỹ năng giải quyết xung đột.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Lãnh đạo và tạo động lực cho đội ngũ', 'content' => 'Các phong cách lãnh đạo.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 6: Phát triển và Mở rộng Doanh nghiệp',
                    'description' => 'Hướng dẫn về phát triển và mở rộng doanh nghiệp.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Chiến lược tăng trưởng', 'content' => 'Các chiến lược tăng trưởng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Mở rộng thị trường', 'content' => 'Mở rộng thị trường trong nước và quốc tế.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Đổi mới và sáng tạo', 'content' => 'Các phương pháp đổi mới và sáng tạo.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Quản lý rủi ro trong quá trình mở rộng', 'content' => 'Xác định và quản lý rủi ro.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Thoái vốn', 'content' => 'IPO, M&A.', 'sortOrder' => 5],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Phân tích Kinh doanh Chuyên sâu: Ứng dụng và Thực hành',
            'description' => 'Khóa học này cung cấp kiến thức chuyên sâu về phân tích kinh doanh, giúp học viên hiểu rõ cách sử dụng dữ liệu để đưa ra quyết định kinh doanh hiệu quả.',
            'price' => 1100000.00,
            'categoryIds' => [39, 34],
            'requirements' => [
                'Kiến thức cơ bản về kinh doanh và thống kê.',
                'Kỹ năng sử dụng bảng tính (Excel, Google Sheets).',
                'Mong muốn tìm hiểu về phân tích dữ liệu.',
            ],
            'objectives' => [
                'Hiểu rõ vai trò của phân tích kinh doanh trong việc ra quyết định.',
                'Thu thập, làm sạch và phân tích dữ liệu hiệu quả.',
                'Sử dụng các công cụ phân tích dữ liệu phổ biến.',
                'Xây dựng các báo cáo và dashboard trực quan.',
                'Đưa ra các khuyến nghị dựa trên phân tích dữ liệu.',
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu về Phân tích Kinh doanh',
                    'description' => 'Tổng quan về phân tích kinh doanh, vai trò và các phương pháp tiếp cận.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Phân tích Kinh doanh là gì?', 'content' => 'Định nghĩa, vai trò và tầm quan trọng.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Quy trình phân tích kinh doanh', 'content' => 'Các bước trong quy trình phân tích.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các loại hình phân tích kinh doanh', 'content' => 'Phân tích mô tả, chẩn đoán, dự đoán và chỉ định.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các kỹ năng cần thiết cho nhà phân tích kinh doanh', 'content' => 'Kỹ năng phân tích, giao tiếp, làm việc nhóm.', 'sortOrder' => 4],
                    ],
                ],
                [
                    'title' => 'Chương 2: Thu thập và Làm sạch Dữ liệu',
                    'description' => 'Hướng dẫn thu thập, làm sạch và chuẩn bị dữ liệu cho phân tích.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Thu thập dữ liệu', 'content' => 'Các nguồn dữ liệu: internal, external.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Làm sạch dữ liệu', 'content' => 'Xử lý dữ liệu thiếu, dữ liệu sai.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Chuyển đổi dữ liệu', 'content' => 'Định dạng dữ liệu, chuyển đổi kiểu dữ liệu.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Tích hợp dữ liệu', 'content' => 'Kết hợp dữ liệu từ nhiều nguồn.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Sử dụng SQL để truy vấn dữ liệu', 'content' => 'Các câu lệnh SQL cơ bản.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 3: Phân tích Dữ liệu với Excel và các Công cụ khác',
                    'description' => 'Hướng dẫn phân tích dữ liệu bằng Excel và các công cụ phân tích khác.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Phân tích dữ liệu trong Excel', 'content' => 'Các hàm Excel, phân tích PivotTable.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Phân tích thống kê cơ bản', 'content' => 'Trung bình, độ lệch chuẩn, phân phối.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phân tích xu hướng', 'content' => 'Phân tích xu hướng thời gian.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Giới thiệu về Power BI', 'content' => 'Các tính năng cơ bản của Power BI.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Giới thiệu về Tableau', 'content' => 'Các tính năng cơ bản của Tableau.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 4: Trực quan hóa Dữ liệu',
                    'description' => 'Hướng dẫn trực quan hóa dữ liệu để trình bày thông tin một cách hiệu quả.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Nguyên tắc trực quan hóa dữ liệu', 'content' => 'Các nguyên tắc cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các loại biểu đồ phổ biến', 'content' => 'Biểu đồ cột, đường, tròn, heatmap.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Thiết kế Dashboard', 'content' => 'Cách thiết kế dashboard hiệu quả.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Sử dụng Power BI để trực quan hóa dữ liệu', 'content' => 'Tạo biểu đồ, dashboard trong Power BI.', 'sortOrder' => 4],
                        ['title' => 'Bài 4.5: Sử dụng Tableau để trực quan hóa dữ liệu', 'content' => 'Tạo biểu đồ, dashboard trong Tableau.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 5: Ứng dụng Phân tích Kinh doanh',
                    'description' => 'Ứng dụng phân tích kinh doanh vào các lĩnh vực khác nhau.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Phân tích bán hàng', 'content' => 'Phân tích doanh thu, thị phần.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Phân tích marketing', 'content' => 'Phân tích hiệu quả chiến dịch, ROI.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Phân tích tài chính', 'content' => 'Phân tích báo cáo tài chính, dòng tiền.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Phân tích vận hành', 'content' => 'Phân tích hiệu quả hoạt động.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Phân tích khách hàng', 'content' => 'Phân tích hành vi khách hàng, phân khúc.', 'sortOrder' => 5],
                    ],
                ],
                [
                    'title' => 'Chương 6: Đưa ra Khuyến nghị và Quyết định',
                    'description' => 'Hướng dẫn đưa ra các khuyến nghị và quyết định dựa trên phân tích dữ liệu.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Xây dựng khuyến nghị', 'content' => 'Cách xây dựng khuyến nghị dựa trên phân tích.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Trình bày kết quả phân tích', 'content' => 'Kỹ năng trình bày kết quả phân tích.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Đưa ra quyết định', 'content' => 'Cách đưa ra quyết định dựa trên dữ liệu.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Theo dõi và đánh giá', 'content' => 'Theo dõi và đánh giá hiệu quả của quyết định.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Các case study', 'content' => 'Phân tích các case study thực tế.', 'sortOrder' => 5],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Khóa học Toán học Cao cấp: Giải tích và Đại số Tuyến tính',
            'description' => 'Khóa học này cung cấp kiến thức chuyên sâu về giải tích hàm một biến và các khái niệm cơ bản của đại số tuyến tính, là nền tảng cho nhiều lĩnh vực khoa học và kỹ thuật.',
            'price' => 950000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Có kiến thức toán học phổ thông vững chắc.',
                'Mong muốn tìm hiểu sâu về toán học cao cấp.'
            ],
            'objectives' => [
                'Nắm vững các khái niệm về giới hạn, đạo hàm, tích phân.',
                'Áp dụng các kỹ thuật giải tích để giải quyết bài toán thực tế.',
                'Hiểu rõ về không gian vector, phép biến đổi tuyến tính và ma trận.',
                'Sử dụng đại số tuyến tính để phân tích dữ liệu và mô hình hóa.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Giới thiệu Giải tích và Hàm số',
                    'description' => 'Tổng quan về giải tích và các loại hàm số cơ bản.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Khái niệm về hàm số và đồ thị', 'content' => 'Nội dung chi tiết về định nghĩa hàm số, miền xác định, miền giá trị và cách vẽ đồ thị.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các loại hàm số cơ bản (đa thức, lượng giác, mũ, logarit)', 'content' => 'Phân tích đặc điểm và ứng dụng của từng loại hàm số.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giới hạn của hàm số', 'content' => 'Định nghĩa giới hạn, các định lý về giới hạn và cách tính giới hạn.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tính liên tục của hàm số', 'content' => 'Khái niệm hàm liên tục và các điều kiện để hàm số liên tục.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Đạo hàm và Ứng dụng',
                    'description' => 'Tìm hiểu về đạo hàm và cách ứng dụng vào các bài toán tối ưu.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Định nghĩa đạo hàm và ý nghĩa hình học', 'content' => 'Khái niệm đạo hàm, quy tắc tính đạo hàm và ý nghĩa của nó trên đồ thị.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Các quy tắc tính đạo hàm (tổng, hiệu, tích, thương)', 'content' => 'Hướng dẫn chi tiết các quy tắc tính đạo hàm cho các phép toán cơ bản.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Đạo hàm của hàm hợp và hàm ngược', 'content' => 'Cách tính đạo hàm cho hàm hợp và hàm ngược.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Ứng dụng của đạo hàm trong tìm cực trị và khảo sát hàm số', 'content' => 'Sử dụng đạo hàm để tìm điểm cực trị, khoảng đồng biến, nghịch biến của hàm số.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Định lý giá trị trung bình và quy tắc L\'Hôpital', 'content' => 'Nghiên cứu các định lý quan trọng và quy tắc L\'Hôpital để tính giới hạn dạng vô định.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Tích phân và Ứng dụng',
                    'description' => 'Khám phá tích phân và các ứng dụng trong tính diện tích, thể tích.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Nguyên hàm và tích phân bất định', 'content' => 'Định nghĩa nguyên hàm, các phương pháp tính tích phân bất định.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Tích phân xác định và định lý cơ bản của giải tích', 'content' => 'Khái niệm tích phân xác định, ý nghĩa hình học và định lý Newton-Leibniz.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Các phương pháp tính tích phân xác định (đổi biến, tích phân từng phần)', 'content' => 'Hướng dẫn chi tiết các kỹ thuật tính tích phân xác định.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Ứng dụng của tích phân trong tính diện tích và thể tích', 'content' => 'Sử dụng tích phân để tính diện tích hình phẳng, thể tích vật thể tròn xoay.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Tích phân suy rộng', 'content' => 'Tìm hiểu về tích phân suy rộng và các tiêu chuẩn hội tụ.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giới thiệu Đại số Tuyến tính và Hệ phương trình',
                    'description' => 'Các khái niệm cơ bản về đại số tuyến tính và cách giải hệ phương trình tuyến tính.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Ma trận và các phép toán trên ma trận', 'content' => 'Định nghĩa ma trận, các loại ma trận và các phép cộng, trừ, nhân ma trận.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Định thức của ma trận', 'content' => 'Cách tính định thức và các tính chất của định thức.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Ma trận nghịch đảo', 'content' => 'Khái niệm ma trận nghịch đảo và phương pháp tìm ma trận nghịch đảo.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Hệ phương trình tuyến tính và phương pháp giải Gauss', 'content' => 'Giới thiệu hệ phương trình tuyến tính và phương pháp khử Gauss để giải hệ.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Không gian Vector và Phép biến đổi Tuyến tính',
                    'description' => 'Tìm hiểu về không gian vector và các phép biến đổi tuyến tính.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Không gian vector và không gian con', 'content' => 'Định nghĩa không gian vector, các tính chất và ví dụ về không gian con.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Cơ sở và số chiều của không gian vector', 'content' => 'Khái niệm cơ sở, số chiều và cách tìm cơ sở cho không gian vector.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Phép biến đổi tuyến tính', 'content' => 'Định nghĩa phép biến đổi tuyến tính, ảnh và hạt nhân của phép biến đổi.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Ma trận của phép biến đổi tuyến tính', 'content' => 'Cách biểu diễn phép biến đổi tuyến tính bằng ma trận.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Giá trị riêng và Vector riêng',
                    'description' => 'Khái niệm về giá trị riêng, vector riêng và ứng dụng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Định nghĩa giá trị riêng và vector riêng', 'content' => 'Khái niệm và ý nghĩa của giá trị riêng, vector riêng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Cách tìm giá trị riêng và vector riêng', 'content' => 'Phương pháp tính giá trị riêng và vector riêng của ma trận.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chéo hóa ma trận', 'content' => 'Khái niệm chéo hóa ma trận và ứng dụng của nó.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Ứng dụng của giá trị riêng và vector riêng', 'content' => 'Ứng dụng trong các bài toán về hệ động lực, phân tích thành phần chính (PCA).', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Vật lý Đại cương: Cơ học và Nhiệt động lực học',
            'description' => 'Khóa học cung cấp kiến thức nền tảng về cơ học Newton, dao động, sóng và các nguyên lý cơ bản của nhiệt động lực học.',
            'price' => 880000.00,
            'categoryIds' => [80], // Vật lý
            'requirements' => [
                'Có kiến thức toán học cơ bản (đại số, lượng giác).',
                'Sự tò mò về thế giới tự nhiên.'
            ],
            'objectives' => [
                'Hiểu và áp dụng các định luật Newton về chuyển động.',
                'Phân tích các dạng năng lượng và định luật bảo toàn năng lượng.',
                'Nắm vững các khái niệm về dao động và sóng.',
                'Hiểu các nguyên lý của nhiệt động lực học và ứng dụng của chúng.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Động học Chất điểm',
                    'description' => 'Nghiên cứu chuyển động của chất điểm mà không xét đến nguyên nhân.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Vị trí, quãng đường, độ dịch chuyển', 'content' => 'Phân biệt các khái niệm cơ bản trong động học.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Vận tốc và gia tốc', 'content' => 'Định nghĩa vận tốc tức thời, vận tốc trung bình, gia tốc.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Chuyển động thẳng đều và biến đổi đều', 'content' => 'Các công thức và bài tập về chuyển động thẳng đều và biến đổi đều.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Chuyển động ném ngang, ném xiên và chuyển động tròn đều', 'content' => 'Phân tích các dạng chuyển động phức tạp hơn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Động lực học Newton',
                    'description' => 'Nghiên cứu nguyên nhân gây ra chuyển động và sự thay đổi của chuyển động.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Các định luật Newton về chuyển động', 'content' => 'Nội dung và ý nghĩa của ba định luật Newton.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Lực ma sát và lực căng dây', 'content' => 'Phân tích các loại lực thường gặp trong cơ học.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Hệ quy chiếu quán tính và phi quán tính', 'content' => 'Phân biệt các loại hệ quy chiếu và lực quán tính.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Ứng dụng các định luật Newton trong giải bài tập', 'content' => 'Thực hành giải các bài tập cơ học phức tạp.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Công, Năng lượng và Định luật Bảo toàn',
                    'description' => 'Tìm hiểu về công, năng lượng và các định luật bảo toàn quan trọng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Công và công suất', 'content' => 'Định nghĩa công, công suất và các đơn vị đo.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Động năng và định lý động năng', 'content' => 'Khái niệm động năng và mối liên hệ với công.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Thế năng (trọng trường, đàn hồi) và định luật bảo toàn cơ năng', 'content' => 'Các dạng thế năng và định luật bảo toàn cơ năng.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Định luật bảo toàn động lượng', 'content' => 'Khái niệm động lượng và định luật bảo toàn động lượng.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Va chạm và các loại va chạm', 'content' => 'Phân tích các loại va chạm (đàn hồi, không đàn hồi) và ứng dụng định luật bảo toàn động lượng.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Dao động và Sóng',
                    'description' => 'Nghiên cứu các hiện tượng dao động và sóng trong vật lý.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Dao động điều hòa đơn giản', 'content' => 'Định nghĩa, phương trình và đặc điểm của dao động điều hòa.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Con lắc đơn và con lắc lò xo', 'content' => 'Phân tích chuyển động của con lắc đơn và con lắc lò xo.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Sóng cơ và các đặc trưng của sóng', 'content' => 'Khái niệm sóng cơ, bước sóng, tần số, chu kỳ, biên độ.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Giao thoa và nhiễu xạ sóng', 'content' => 'Các hiện tượng giao thoa, nhiễu xạ và điều kiện xảy ra.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Nhiệt độ và Nhiệt lượng',
                    'description' => 'Các khái niệm cơ bản về nhiệt độ, nhiệt lượng và sự truyền nhiệt.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Nhiệt độ và các thang đo nhiệt độ', 'content' => 'Định nghĩa nhiệt độ, thang Celsius, Fahrenheit, Kelvin.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Nhiệt lượng và nhiệt dung riêng', 'content' => 'Khái niệm nhiệt lượng, nhiệt dung riêng và cách tính nhiệt lượng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Các phương thức truyền nhiệt (dẫn nhiệt, đối lưu, bức xạ)', 'content' => 'Phân tích các cơ chế truyền nhiệt và ví dụ thực tế.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Sự nở vì nhiệt của vật rắn, lỏng, khí', 'content' => 'Nghiên cứu sự thay đổi kích thước của vật chất khi nhiệt độ thay đổi.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Các nguyên lý Nhiệt động lực học',
                    'description' => 'Tìm hiểu các định luật cơ bản chi phối sự chuyển hóa năng lượng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Nguyên lý thứ nhất của nhiệt động lực học', 'content' => 'Định luật bảo toàn năng lượng trong các quá trình nhiệt.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Công và nội năng của hệ nhiệt động', 'content' => 'Khái niệm công, nội năng và mối quan hệ giữa chúng.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Nguyên lý thứ hai của nhiệt động lực học (Entropy)', 'content' => 'Khái niệm entropy và chiều của các quá trình tự nhiên.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Chu trình Carnot và hiệu suất động cơ nhiệt', 'content' => 'Phân tích chu trình Carnot lý tưởng và giới hạn hiệu suất của động cơ nhiệt.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Lịch sử Thế giới: Từ Cổ đại đến Hiện đại',
            'description' => 'Hành trình khám phá các nền văn minh vĩ đại, sự kiện lịch sử trọng đại và những biến đổi xã hội đã định hình thế giới ngày nay.',
            'price' => 720000.00,
            'categoryIds' => [111], // Lịch sử
            'requirements' => [
                'Sự quan tâm đến lịch sử và văn hóa nhân loại.',
                'Khả năng đọc hiểu tài liệu lịch sử.'
            ],
            'objectives' => [
                'Nắm vững các mốc thời gian và sự kiện quan trọng trong lịch sử thế giới.',
                'Hiểu được sự phát triển và suy tàn của các nền văn minh lớn.',
                'Phân tích nguyên nhân và hệ quả của các cuộc cách mạng và chiến tranh.',
                'Đánh giá vai trò của các nhân vật lịch sử và các tư tưởng lớn.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Các nền văn minh Cổ đại',
                    'description' => 'Khám phá những nền văn minh đầu tiên của nhân loại ở phương Đông và phương Tây.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Văn minh Lưỡng Hà và Ai Cập cổ đại', 'content' => 'Nghiên cứu về nền văn minh sông Nile và Tigris-Euphrates.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Văn minh Ấn Độ và Trung Hoa cổ đại', 'content' => 'Tìm hiểu về các triều đại, tôn giáo và phát minh của Ấn Độ, Trung Hoa.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Hy Lạp cổ đại: Nền tảng của văn minh phương Tây', 'content' => 'Khám phá triết học, nghệ thuật, dân chủ của Hy Lạp.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Đế chế La Mã: Từ Cộng hòa đến Đế quốc', 'content' => 'Nghiên cứu về sự hình thành, phát triển và suy tàn của La Mã.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Thời kỳ Trung cổ ở Châu Âu và các nền văn minh khác',
                    'description' => 'Giai đoạn giữa cổ đại và cận đại, với sự trỗi dậy của các vương quốc và tôn giáo.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Sự sụp đổ của Đế chế La Mã và sự hình thành các vương quốc Germanic', 'content' => 'Nguyên nhân và hậu quả của sự kiện lịch sử này.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Thời kỳ phong kiến Châu Âu và vai trò của Giáo hội', 'content' => 'Cấu trúc xã hội phong kiến và ảnh hưởng của Giáo hội.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Sự trỗi dậy của Hồi giáo và các đế chế Hồi giáo', 'content' => 'Lịch sử hình thành và phát triển của các đế chế Hồi giáo.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Các nền văn minh tiền Columbus ở Châu Mỹ', 'content' => 'Tìm hiểu về Maya, Aztec, Inca và các nền văn minh khác.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Thời kỳ Phục hưng và Cải cách Tôn giáo',
                    'description' => 'Giai đoạn bùng nổ về nghệ thuật, khoa học và tư tưởng ở Châu Âu.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Phục hưng Ý: Nơi khởi nguồn của một kỷ nguyên mới', 'content' => 'Các nghệ sĩ, nhà khoa học và triết gia tiêu biểu.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Sự lan tỏa của Phục hưng ra khắp Châu Âu', 'content' => 'Ảnh hưởng của Phục hưng đến các quốc gia khác.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Cải cách Tôn giáo: Martin Luther và sự chia rẽ Giáo hội', 'content' => 'Nguyên nhân, diễn biến và hệ quả của Cải cách Tôn giáo.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Phản Cải cách và Chiến tranh Tôn giáo', 'content' => 'Phản ứng của Giáo hội Công giáo và các cuộc chiến tranh tôn giáo.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Thời kỳ Khám phá và Cách mạng Khoa học',
                    'description' => 'Sự mở rộng thế giới và những bước đột phá trong khoa học.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Các cuộc thám hiểm địa lý vĩ đại', 'content' => 'Columbus, Magellan và các nhà thám hiểm khác.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Hệ quả của thời kỳ khám phá: Chủ nghĩa thực dân và trao đổi Columbia', 'content' => 'Tác động của khám phá địa lý đến thế giới.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Cách mạng Khoa học: Từ Copernicus đến Newton', 'content' => 'Những phát hiện đột phá và sự thay đổi tư duy khoa học.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Thời kỳ Khai sáng và các tư tưởng chính trị mới', 'content' => 'Triết học Khai sáng và ảnh hưởng đến các cuộc cách mạng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Các cuộc Cách mạng và Thời đại Công nghiệp',
                    'description' => 'Những thay đổi sâu rộng về chính trị, kinh tế và xã hội.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Cách mạng Mỹ và sự ra đời của Hợp chủng quốc Hoa Kỳ', 'content' => 'Nguyên nhân, diễn biến và ý nghĩa của Cách mạng Mỹ.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Cách mạng Pháp và thời kỳ Napoleon', 'content' => 'Sự kiện Cách mạng Pháp và ảnh hưởng của Napoleon.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Cách mạng Công nghiệp lần thứ nhất', 'content' => 'Sự phát triển của máy hơi nước, nhà máy và tác động xã hội.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Cách mạng Công nghiệp lần thứ hai và sự trỗi dậy của các cường quốc', 'content' => 'Điện, hóa chất, thép và sự cạnh tranh giữa các đế quốc.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Thế chiến I và II: Định hình thế kỷ 20',
                    'description' => 'Hai cuộc chiến tranh lớn nhất lịch sử và hậu quả của chúng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Nguyên nhân và diễn biến của Thế chiến I', 'content' => 'Hệ thống liên minh, sự kiện Sarajevo và các mặt trận chính.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Hậu quả của Thế chiến I và Hiệp ước Versailles', 'content' => 'Sự thay đổi bản đồ chính trị thế giới và các điều khoản hòa bình.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Sự trỗi dậy của các chế độ độc tài và con đường dẫn đến Thế chiến II', 'content' => 'Chủ nghĩa phát xít, chủ nghĩa quân phiệt và sự bành trướng.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Diễn biến chính của Thế chiến II và sự hình thành phe Đồng minh, phe Trục', 'content' => 'Các chiến dịch lớn, vai trò của các nước và sự kết thúc chiến tranh.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Hậu quả của Thế chiến II và sự ra đời của Liên Hợp Quốc', 'content' => 'Tác động toàn cầu, chiến tranh lạnh và trật tự thế giới mới.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Hóa học Đại cương: Cấu tạo Chất và Phản ứng Hóa học',
            'description' => 'Khóa học cung cấp kiến thức nền tảng về cấu tạo nguyên tử, liên kết hóa học, các loại phản ứng và nguyên lý cơ bản của hóa học.',
            'price' => 800000.00,
            'categoryIds' => [109], // Hóa học
            'requirements' => [
                'Có kiến thức hóa học phổ thông cơ bản.',
                'Sự quan tâm đến các hiện tượng hóa học.'
            ],
            'objectives' => [
                'Hiểu cấu trúc nguyên tử và cách các nguyên tố được sắp xếp trong bảng tuần hoàn.',
                'Nắm vững các loại liên kết hóa học và cách chúng hình thành phân tử.',
                'Phân loại và cân bằng các phản ứng hóa học.',
                'Hiểu các nguyên lý về động hóa học và cân bằng hóa học.',
                'Có kiến thức cơ bản về hóa học hữu cơ.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Cấu tạo Nguyên tử và Bảng tuần hoàn',
                    'description' => 'Tìm hiểu về cấu trúc cơ bản của nguyên tử và cách các nguyên tố được sắp xếp.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Mô hình nguyên tử và các hạt cơ bản', 'content' => 'Electron, proton, neutron và mô hình nguyên tử hiện đại.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cấu hình electron và quỹ đạo nguyên tử', 'content' => 'Cách sắp xếp electron trong nguyên tử và hình dạng quỹ đạo.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Bảng tuần hoàn các nguyên tố hóa học', 'content' => 'Cấu trúc bảng tuần hoàn, chu kỳ, nhóm và xu hướng biến đổi tính chất.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Tính chất tuần hoàn của nguyên tố', 'content' => 'Bán kính nguyên tử, năng lượng ion hóa, độ âm điện.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Liên kết Hóa học',
                    'description' => 'Khám phá các loại liên kết giúp các nguyên tử kết hợp thành phân tử.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Liên kết ion', 'content' => 'Sự hình thành liên kết ion và các hợp chất ion.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Liên kết cộng hóa trị', 'content' => 'Sự hình thành liên kết cộng hóa trị, liên kết đơn, đôi, ba.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Độ phân cực của liên kết và phân tử', 'content' => 'Khái niệm độ phân cực, momen lưỡng cực và ảnh hưởng đến tính chất.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Liên kết kim loại và liên kết hydro', 'content' => 'Đặc điểm của liên kết kim loại và vai trò của liên kết hydro.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Phản ứng Oxy hóa - Khử',
                    'description' => 'Nghiên cứu các phản ứng có sự thay đổi số oxy hóa của các nguyên tố.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Khái niệm về số oxy hóa và chất oxy hóa, chất khử', 'content' => 'Cách xác định số oxy hóa và phân biệt chất oxy hóa, chất khử.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Phản ứng oxy hóa - khử và cách cân bằng', 'content' => 'Các phương pháp cân bằng phản ứng oxy hóa - khử (thăng bằng electron, ion-electron).', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Điện hóa học: Pin điện hóa và điện phân', 'content' => 'Nguyên lý hoạt động của pin điện hóa và quá trình điện phân.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Ứng dụng của phản ứng oxy hóa - khử trong đời sống và công nghiệp', 'content' => 'Ví dụ về ăn mòn kim loại, sản xuất hóa chất.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Axit, Bazơ và Muối',
                    'description' => 'Tìm hiểu về các hợp chất vô cơ cơ bản và các phản ứng của chúng.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Thuyết Arrhenius, Brønsted-Lowry và Lewis về axit-bazơ', 'content' => 'Các định nghĩa khác nhau về axit và bazơ.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: pH và chỉ thị axit-bazơ', 'content' => 'Khái niệm pH, cách tính pH và sử dụng chỉ thị.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Phản ứng trung hòa và muối', 'content' => 'Phản ứng giữa axit và bazơ, các loại muối và tính chất.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Dung dịch đệm và chuẩn độ axit-bazơ', 'content' => 'Vai trò của dung dịch đệm và kỹ thuật chuẩn độ.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Động hóa học và Cân bằng hóa học',
                    'description' => 'Nghiên cứu tốc độ phản ứng và trạng thái cân bằng của hệ hóa học.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tốc độ phản ứng hóa học', 'content' => 'Các yếu tố ảnh hưởng đến tốc độ phản ứng.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Năng lượng hoạt hóa và cơ chế phản ứng', 'content' => 'Khái niệm năng lượng hoạt hóa và các bước của phản ứng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Trạng thái cân bằng hóa học', 'content' => 'Hằng số cân bằng và các yếu tố ảnh hưởng đến cân bằng.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Nguyên lý Le Chatelier', 'content' => 'Dự đoán sự dịch chuyển cân bằng khi có tác động từ bên ngoài.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Hóa học hữu cơ cơ bản',
                    'description' => 'Giới thiệu về các hợp chất carbon và các nhóm chức chính.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Giới thiệu về hóa học hữu cơ và liên kết carbon', 'content' => 'Đặc điểm của hợp chất hữu cơ và khả năng tạo liên kết của carbon.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Hydrocarbon: Ankan, Anken, Ankin, Aren', 'content' => 'Cấu trúc, danh pháp và tính chất của các hydrocarbon.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Các nhóm chức cơ bản: Alcohol, Ether, Aldehyde, Ketone', 'content' => 'Đặc điểm và phản ứng của các hợp chất có nhóm chức oxy.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Axit Carboxylic, Este, Amin và Amide', 'content' => 'Tính chất và ứng dụng của các hợp chất hữu cơ quan trọng khác.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Địa lý Tự nhiên và Kinh tế Xã hội',
            'description' => 'Khóa học cung cấp cái nhìn toàn diện về các quá trình tự nhiên trên Trái đất và mối quan hệ giữa con người với môi trường.',
            'price' => 700000.00,
            'categoryIds' => [112], // Địa lý
            'requirements' => [
                'Sự quan tâm đến môi trường và các vấn đề toàn cầu.',
                'Khả năng đọc hiểu bản đồ và biểu đồ.'
            ],
            'objectives' => [
                'Hiểu cấu trúc và các hiện tượng địa chất của Trái đất.',
                'Phân tích các yếu tố khí hậu, thời tiết và thủy văn.',
                'Nắm vững các khái niệm về dân số, đô thị hóa và di cư.',
                'Đánh giá các mô hình kinh tế và vấn đề phát triển bền vững.',
                'Sử dụng bản đồ và các công cụ địa lý để phân tích thông tin.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Trái đất và Hệ Mặt Trời',
                    'description' => 'Giới thiệu về vị trí của Trái đất trong vũ trụ và các đặc điểm cơ bản.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Vị trí của Trái đất trong Hệ Mặt Trời', 'content' => 'Các hành tinh, thiên thể và chuyển động của Trái đất.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Hình dạng, kích thước và các đường kinh tuyến, vĩ tuyến', 'content' => 'Đặc điểm hình học của Trái đất và hệ tọa độ địa lý.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Chuyển động tự quay và chuyển động quanh Mặt Trời', 'content' => 'Hệ quả của các chuyển động: ngày đêm, mùa, múi giờ.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Cấu trúc bên trong của Trái đất', 'content' => 'Lớp vỏ, lớp manti, lõi và các thành phần của chúng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Cấu trúc Trái đất và Hiện tượng Địa chất',
                    'description' => 'Khám phá các lực hình thành bề mặt Trái đất và các hiện tượng địa chất.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Thuyết kiến tạo mảng', 'content' => 'Các mảng kiến tạo, ranh giới mảng và chuyển động của chúng.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Động đất và núi lửa', 'content' => 'Nguyên nhân, phân bố và tác động của động đất, núi lửa.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Quá trình phong hóa và xói mòn', 'content' => 'Các yếu tố gây phong hóa, xói mòn và vai trò trong hình thành địa hình.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Các dạng địa hình chính trên Trái đất', 'content' => 'Núi, cao nguyên, đồng bằng, thung lũng và sự hình thành.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Khí hậu và Thời tiết',
                    'description' => 'Nghiên cứu các yếu tố tạo nên khí hậu và thời tiết trên toàn cầu.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Các yếu tố hình thành khí hậu (vĩ độ, địa hình, dòng biển)', 'content' => 'Ảnh hưởng của các yếu tố đến nhiệt độ, lượng mưa.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Khí quyển và các tầng khí quyển', 'content' => 'Cấu trúc khí quyển và vai trò của từng tầng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Hệ thống gió toàn cầu và áp suất khí quyển', 'content' => 'Gió mậu dịch, gió tây ôn đới và các đai áp.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Các kiểu khí hậu chính trên thế giới', 'content' => 'Khí hậu xích đạo, nhiệt đới, ôn đới, hàn đới và đặc điểm.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Biến đổi khí hậu và tác động toàn cầu', 'content' => 'Nguyên nhân, hậu quả và các giải pháp ứng phó.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Thủy văn và Đại dương',
                    'description' => 'Tìm hiểu về nước trên Trái đất, sông ngòi, hồ và đại dương.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Chu trình nước toàn cầu', 'content' => 'Các giai đoạn của chu trình nước và tầm quan trọng.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Sông ngòi và hồ: Đặc điểm và vai trò', 'content' => 'Hệ thống sông, các loại hồ và giá trị kinh tế, sinh thái.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Đại dương và các dòng hải lưu', 'content' => 'Đặc điểm của đại dương, các dòng hải lưu và ảnh hưởng khí hậu.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Tài nguyên nước và các vấn đề môi trường nước', 'content' => 'Sử dụng bền vững tài nguyên nước và ô nhiễm nước.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Dân số và Đô thị hóa',
                    'description' => 'Nghiên cứu về sự phân bố, tăng trưởng dân số và quá trình đô thị hóa.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Phân bố dân cư và các yếu tố ảnh hưởng', 'content' => 'Các khu vực tập trung dân cư và các yếu tố tự nhiên, kinh tế.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Tăng trưởng dân số và cấu trúc dân số', 'content' => 'Tỷ suất sinh, tử, tháp dân số và các giai đoạn tăng trưởng.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Di cư và các loại hình di cư', 'content' => 'Nguyên nhân, hậu quả của di cư trong nước và quốc tế.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đô thị hóa và các vấn đề đô thị', 'content' => 'Xu hướng đô thị hóa, các loại hình đô thị và thách thức.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Kinh tế và Phát triển Bền vững',
                    'description' => 'Tìm hiểu về các hoạt động kinh tế và hướng đến phát triển bền vững.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Các ngành kinh tế chính (nông nghiệp, công nghiệp, dịch vụ)', 'content' => 'Đặc điểm, phân bố và xu hướng phát triển của từng ngành.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Toàn cầu hóa kinh tế và thương mại quốc tế', 'content' => 'Tác động của toàn cầu hóa đến các quốc gia.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tài nguyên thiên nhiên và sử dụng bền vững', 'content' => 'Các loại tài nguyên và chiến lược quản lý bền vững.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Phát triển bền vững và các Mục tiêu Phát triển Bền vững (SDGs)', 'content' => 'Khái niệm phát triển bền vững và vai trò của SDGs.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Toán lớp 6: Số học và Hình học Cơ bản',
            'description' => 'Khóa học này cung cấp kiến thức nền tảng về số tự nhiên, số nguyên, phân số, số thập phân và các khái niệm hình học cơ bản, giúp học sinh xây dựng nền tảng vững chắc cho các cấp học tiếp theo.',
            'price' => 350000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Học sinh đã hoàn thành chương trình tiểu học.',
                'Có tinh thần ham học hỏi và luyện tập.'
            ],
            'objectives' => [
                'Thực hiện thành thạo các phép tính với số tự nhiên, số nguyên, phân số và số thập phân.',
                'Nắm vững các khái niệm về ước, bội, số nguyên tố, hợp số.',
                'Nhận biết và vẽ được các hình hình học cơ bản như điểm, đường thẳng, đoạn thẳng, góc.',
                'Tính toán được chu vi, diện tích các hình đơn giản.',
                'Giải quyết các bài toán thực tế liên quan đến số và hình học.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Tập hợp và Các phép toán về Số tự nhiên',
                    'description' => 'Tìm hiểu về tập hợp, các phép toán cộng, trừ, nhân, chia số tự nhiên và lũy thừa.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Tập hợp và phần tử của tập hợp', 'content' => 'Khái niệm tập hợp, cách viết tập hợp, phần tử thuộc/không thuộc tập hợp.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Các phép toán cộng, trừ số tự nhiên', 'content' => 'Quy tắc thực hiện phép cộng, trừ và các tính chất.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các phép toán nhân, chia số tự nhiên', 'content' => 'Quy tắc thực hiện phép nhân, chia và các tính chất.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Lũy thừa với số mũ tự nhiên', 'content' => 'Khái niệm lũy thừa, cách tính lũy thừa và các phép toán liên quan.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Số nguyên và Các phép toán',
                    'description' => 'Giới thiệu về số nguyên âm, số nguyên dương và các phép tính trên tập hợp số nguyên.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Tập hợp số nguyên và số đối', 'content' => 'Khái niệm số nguyên, biểu diễn trên trục số, số đối.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Cộng, trừ hai số nguyên', 'content' => 'Quy tắc cộng, trừ hai số nguyên cùng dấu, khác dấu.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Nhân, chia hai số nguyên', 'content' => 'Quy tắc nhân, chia hai số nguyên.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Giá trị tuyệt đối của số nguyên', 'content' => 'Định nghĩa giá trị tuyệt đối và ứng dụng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Phân số và Các phép toán',
                    'description' => 'Học về phân số, các phép toán cộng, trừ, nhân, chia phân số và ứng dụng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Khái niệm phân số và tính chất cơ bản', 'content' => 'Định nghĩa phân số, phân số bằng nhau, rút gọn phân số.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Quy đồng mẫu số các phân số', 'content' => 'Cách quy đồng mẫu số để so sánh và thực hiện phép tính.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Cộng, trừ phân số', 'content' => 'Quy tắc cộng, trừ phân số cùng mẫu, khác mẫu.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Nhân, chia phân số', 'content' => 'Quy tắc nhân, chia phân số và phân số nghịch đảo.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Số thập phân và Ứng dụng',
                    'description' => 'Tìm hiểu về số thập phân, các phép toán và mối liên hệ với phân số.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Khái niệm số thập phân và cách đọc, viết', 'content' => 'Định nghĩa số thập phân, giá trị của chữ số trong số thập phân.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: So sánh và làm tròn số thập phân', 'content' => 'Quy tắc so sánh và làm tròn số thập phân.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Các phép toán với số thập phân', 'content' => 'Cộng, trừ, nhân, chia số thập phân.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Tỷ số phần trăm', 'content' => 'Khái niệm tỷ số phần trăm và các bài toán liên quan.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Hình học phẳng cơ bản',
                    'description' => 'Giới thiệu các khái niệm cơ bản về điểm, đường thẳng, đoạn thẳng, tia, góc.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Điểm, đường thẳng, đoạn thẳng, tia', 'content' => 'Định nghĩa và cách vẽ các đối tượng hình học cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Góc và các loại góc', 'content' => 'Khái niệm góc, cách đo góc, góc nhọn, tù, vuông, bẹt.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Tia phân giác của góc', 'content' => 'Định nghĩa và cách vẽ tia phân giác.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Đường tròn và các yếu tố liên quan', 'content' => 'Tâm, bán kính, đường kính, dây cung của đường tròn.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 6: Một số hình phẳng trong thực tiễn',
                    'description' => 'Tìm hiểu về chu vi, diện tích các hình tam giác, tứ giác, hình tròn.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Chu vi và diện tích hình chữ nhật, hình vuông', 'content' => 'Công thức tính chu vi, diện tích và bài tập ứng dụng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Chu vi và diện tích hình tam giác', 'content' => 'Công thức tính chu vi, diện tích tam giác.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Chu vi và diện tích hình tròn', 'content' => 'Công thức tính chu vi, diện tích hình tròn.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Hình học không gian cơ bản (hình hộp chữ nhật, hình lập phương)', 'content' => 'Nhận biết, đếm số đỉnh, cạnh, mặt của hình hộp chữ nhật, hình lập phương.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Toán lớp 7: Số hữu tỉ, Đại số và Hình học',
            'description' => 'Khóa học tập trung vào số hữu tỉ, số thực, đại lượng tỉ lệ thuận, tỉ lệ nghịch, biểu thức đại số và các kiến thức hình học về đường thẳng song song, tam giác.',
            'price' => 380000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Đã hoàn thành chương trình Toán lớp 6.',
                'Có khả năng tư duy logic và giải quyết vấn đề.'
            ],
            'objectives' => [
                'Thực hiện thành thạo các phép tính với số hữu tỉ và số thực.',
                'Giải các bài toán về đại lượng tỉ lệ thuận, tỉ lệ nghịch.',
                'Thực hiện các phép tính với biểu thức đại số, đơn thức, đa thức.',
                'Nắm vững các tính chất của đường thẳng song song, tam giác, các trường hợp bằng nhau của tam giác.',
                'Giải quyết các bài toán hình học liên quan đến tam giác và đường thẳng.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Số hữu tỉ và Số thực',
                    'description' => 'Mở rộng tập hợp số sang số hữu tỉ và số thực, các phép toán và giá trị tuyệt đối.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Tập hợp số hữu tỉ', 'content' => 'Khái niệm số hữu tỉ, biểu diễn số hữu tỉ trên trục số.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cộng, trừ, nhân, chia số hữu tỉ', 'content' => 'Quy tắc thực hiện các phép toán với số hữu tỉ.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giá trị tuyệt đối của một số hữu tỉ', 'content' => 'Định nghĩa và ứng dụng của giá trị tuyệt đối.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Số thực và căn bậc hai số học', 'content' => 'Khái niệm số thực, số vô tỉ, căn bậc hai số học.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Đại lượng tỉ lệ thuận, tỉ lệ nghịch',
                    'description' => 'Tìm hiểu về mối quan hệ tỉ lệ thuận, tỉ lệ nghịch giữa các đại lượng và ứng dụng.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Đại lượng tỉ lệ thuận', 'content' => 'Định nghĩa, tính chất và bài toán về đại lượng tỉ lệ thuận.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Một số bài toán về đại lượng tỉ lệ thuận', 'content' => 'Thực hành giải các dạng bài tập khác nhau.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Đại lượng tỉ lệ nghịch', 'content' => 'Định nghĩa, tính chất và bài toán về đại lượng tỉ lệ nghịch.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Một số bài toán về đại lượng tỉ lệ nghịch', 'content' => 'Thực hành giải các dạng bài tập khác nhau.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Biểu thức đại số',
                    'description' => 'Giới thiệu về biểu thức đại số, đơn thức, đa thức và các phép toán.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Biểu thức đại số', 'content' => 'Khái niệm biểu thức đại số, giá trị của biểu thức đại số.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Đơn thức', 'content' => 'Định nghĩa đơn thức, đơn thức đồng dạng, cộng trừ đơn thức đồng dạng.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Đa thức', 'content' => 'Định nghĩa đa thức, cộng trừ đa thức.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Đa thức một biến', 'content' => 'Sắp xếp đa thức, nghiệm của đa thức một biến.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Đường thẳng song song và Cắt nhau',
                    'description' => 'Tìm hiểu về các mối quan hệ giữa các đường thẳng và các góc tạo bởi đường thẳng cắt hai đường thẳng.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Hai đường thẳng song song', 'content' => 'Dấu hiệu nhận biết hai đường thẳng song song.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Tiên đề Euclid về đường thẳng song song', 'content' => 'Nội dung tiên đề và các hệ quả.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Quan hệ giữa tính vuông góc và tính song song', 'content' => 'Đường thẳng vuông góc với một trong hai đường thẳng song song.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Định lý Py-ta-go', 'content' => 'Nội dung định lý Py-ta-go và định lý Py-ta-go đảo.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Tam giác',
                    'description' => 'Nghiên cứu về tam giác, các trường hợp bằng nhau của tam giác và các đường đặc biệt trong tam giác.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tổng ba góc của một tam giác', 'content' => 'Định lý tổng ba góc và ứng dụng.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Hai tam giác bằng nhau', 'content' => 'Định nghĩa hai tam giác bằng nhau.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Các trường hợp bằng nhau của tam giác (c.c.c, c.g.c, g.c.g)', 'content' => 'Ba trường hợp cơ bản để chứng minh hai tam giác bằng nhau.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tam giác cân, tam giác đều', 'content' => 'Định nghĩa, tính chất và dấu hiệu nhận biết.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Quan hệ giữa các yếu tố trong tam giác', 'content' => 'Quan hệ giữa cạnh và góc đối diện, bất đẳng thức tam giác.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Thống kê và Biểu đồ',
                    'description' => 'Giới thiệu về thu thập, tổ chức dữ liệu và các loại biểu đồ thống kê.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Thu thập và tổ chức dữ liệu', 'content' => 'Cách thu thập dữ liệu, bảng tần số.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Biểu đồ đoạn thẳng, biểu đồ cột', 'content' => 'Cách vẽ và đọc các loại biểu đồ.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Tần số và tần suất', 'content' => 'Khái niệm tần số, tần suất và ý nghĩa.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Số trung bình cộng, mốt của dấu hiệu', 'content' => 'Cách tính số trung bình cộng và tìm mốt.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Toán lớp 8: Phân thức đại số, Phương trình và Tứ giác',
            'description' => 'Khóa học tập trung vào các phép toán với phân thức đại số, giải phương trình, bất phương trình bậc nhất và các kiến thức hình học về tứ giác, đa giác.',
            'price' => 400000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Đã hoàn thành chương trình Toán lớp 7.',
                'Có khả năng suy luận và áp dụng công thức.'
            ],
            'objectives' => [
                'Thực hiện thành thạo các phép toán với phân thức đại số.',
                'Giải phương trình bậc nhất một ẩn, phương trình tích, phương trình chứa ẩn ở mẫu.',
                'Giải bất phương trình bậc nhất một ẩn.',
                'Nắm vững các tính chất và dấu hiệu nhận biết các loại tứ giác đặc biệt.',
                'Giải các bài toán hình học liên quan đến tứ giác và đa giác.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Phép nhân và Phép chia đa thức',
                    'description' => 'Ôn tập về đa thức và học các phép nhân, chia đa thức.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Nhân đơn thức với đa thức', 'content' => 'Quy tắc nhân đơn thức với từng hạng tử của đa thức.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Nhân đa thức với đa thức', 'content' => 'Quy tắc nhân từng hạng tử của đa thức này với đa thức kia.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Những hằng đẳng thức đáng nhớ', 'content' => 'Bảy hằng đẳng thức cơ bản và ứng dụng.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Chia đơn thức cho đơn thức, chia đa thức cho đơn thức', 'content' => 'Quy tắc chia đơn thức, đa thức cho đơn thức.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Phân thức đại số',
                    'description' => 'Giới thiệu về phân thức đại số, các phép toán cộng, trừ, nhân, chia phân thức.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Khái niệm phân thức đại số', 'content' => 'Định nghĩa phân thức, giá trị của phân thức.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Rút gọn phân thức', 'content' => 'Cách rút gọn phân thức về dạng tối giản.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Cộng, trừ các phân thức', 'content' => 'Quy tắc cộng, trừ phân thức cùng mẫu, khác mẫu.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Nhân, chia các phân thức', 'content' => 'Quy tắc nhân, chia phân thức.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Phương trình bậc nhất một ẩn',
                    'description' => 'Học cách giải các loại phương trình bậc nhất một ẩn và ứng dụng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Phương trình bậc nhất một ẩn', 'content' => 'Định nghĩa, cách giải phương trình bậc nhất.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Phương trình đưa được về dạng ax + b = 0', 'content' => 'Các bước giải phương trình phức tạp hơn.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phương trình tích', 'content' => 'Cách giải phương trình tích.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Phương trình chứa ẩn ở mẫu', 'content' => 'Điều kiện xác định và cách giải phương trình chứa ẩn ở mẫu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Bất phương trình bậc nhất một ẩn',
                    'description' => 'Tìm hiểu về bất phương trình, các phép biến đổi tương đương và cách giải.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Bất phương trình bậc nhất một ẩn', 'content' => 'Định nghĩa, tập nghiệm của bất phương trình.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Hai bất phương trình tương đương', 'content' => 'Các phép biến đổi tương đương bất phương trình.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Giải bất phương trình bậc nhất một ẩn', 'content' => 'Các bước giải bất phương trình bậc nhất.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Bài toán giải bất phương trình', 'content' => 'Thực hành giải các dạng bài tập bất phương trình.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Tứ giác và Đa giác',
                    'description' => 'Nghiên cứu về các loại tứ giác đặc biệt và đa giác, diện tích đa giác.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Tứ giác', 'content' => 'Định nghĩa tứ giác, tổng các góc của tứ giác.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Hình thang, hình thang cân', 'content' => 'Định nghĩa, tính chất và dấu hiệu nhận biết.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Hình bình hành', 'content' => 'Định nghĩa, tính chất và dấu hiệu nhận biết.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Hình chữ nhật, hình thoi, hình vuông', 'content' => 'Định nghĩa, tính chất và dấu hiệu nhận biết các hình đặc biệt.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Đa giác, đa giác đều', 'content' => 'Định nghĩa đa giác, công thức tính tổng số đo các góc của đa giác.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Diện tích đa giác và Hình không gian',
                    'description' => 'Tính toán diện tích các đa giác và giới thiệu về hình không gian.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Diện tích hình chữ nhật, hình vuông', 'content' => 'Ôn tập và nâng cao các bài toán về diện tích.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Diện tích hình tam giác, hình thang', 'content' => 'Công thức tính diện tích và bài tập ứng dụng.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Diện tích hình bình hành, hình thoi', 'content' => 'Công thức tính diện tích và bài tập ứng dụng.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Hình chóp đều, hình lăng trụ đứng', 'content' => 'Nhận biết, vẽ hình và tính diện tích xung quanh, thể tích.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Toán lớp 9: Căn bậc hai, Hàm số và Đường tròn',
            'description' => 'Khóa học tập trung vào căn bậc hai, căn bậc ba, hàm số bậc nhất, hàm số bậc hai, hệ phương trình và các kiến thức hình học về đường tròn.',
            'price' => 420000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Đã hoàn thành chương trình Toán lớp 8.',
                'Có khả năng phân tích và giải quyết bài toán phức tạp.'
            ],
            'objectives' => [
                'Thực hiện thành thạo các phép biến đổi với căn bậc hai, căn bậc ba.',
                'Vẽ đồ thị và giải các bài toán liên quan đến hàm số bậc nhất, hàm số bậc hai.',
                'Giải hệ phương trình bậc nhất hai ẩn.',
                'Nắm vững các tính chất của đường tròn, tiếp tuyến, góc với đường tròn.',
                'Giải quyết các bài toán hình học liên quan đến đường tròn và các yếu tố trong đường tròn.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Căn bậc hai, căn bậc ba',
                    'description' => 'Tìm hiểu về căn bậc hai, căn bậc ba và các phép biến đổi liên quan.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Căn bậc hai số học', 'content' => 'Định nghĩa, ký hiệu và tính chất của căn bậc hai số học.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Căn thức bậc hai và hằng đẳng thức', 'content' => 'Điều kiện để căn thức có nghĩa, hằng đẳng thức $\\sqrt{A^2} = |A|$.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các phép biến đổi biểu thức chứa căn thức bậc hai', 'content' => 'Đưa thừa số ra/vào dấu căn, khử mẫu của biểu thức lấy căn.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Căn bậc ba', 'content' => 'Định nghĩa, tính chất và cách tính căn bậc ba.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Hàm số bậc nhất và Hàm số bậc hai',
                    'description' => 'Nghiên cứu về hàm số bậc nhất, hàm số bậc hai và đồ thị của chúng.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Hàm số bậc nhất', 'content' => 'Định nghĩa, tính chất đồng biến, nghịch biến.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Đồ thị của hàm số y = ax + b', 'content' => 'Cách vẽ đồ thị và các bài toán liên quan đến đồ thị.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Hàm số y = ax^2 (a ≠ 0)', 'content' => 'Định nghĩa, tính chất và đồ thị của hàm số bậc hai đơn giản.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Vị trí tương đối của đường thẳng và parabol', 'content' => 'Xác định giao điểm của đường thẳng và parabol.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Phương trình bậc hai một ẩn',
                    'description' => 'Học cách giải phương trình bậc hai một ẩn và các bài toán ứng dụng.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Phương trình bậc hai một ẩn', 'content' => 'Định nghĩa, công thức nghiệm và công thức nghiệm thu gọn.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Hệ thức Vi-ét và ứng dụng', 'content' => 'Mối quan hệ giữa các nghiệm và các hệ số của phương trình.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Giải bài toán bằng cách lập phương trình', 'content' => 'Các bước giải bài toán có lời văn bằng cách lập phương trình bậc hai.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Phương trình quy về phương trình bậc hai', 'content' => 'Giải các phương trình trùng phương, phương trình chứa ẩn ở mẫu.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Hệ phương trình bậc nhất hai ẩn',
                    'description' => 'Tìm hiểu cách giải hệ phương trình bậc nhất hai ẩn bằng các phương pháp khác nhau.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Phương trình bậc nhất hai ẩn', 'content' => 'Định nghĩa, tập nghiệm và biểu diễn tập nghiệm trên mặt phẳng tọa độ.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Hệ hai phương trình bậc nhất hai ẩn', 'content' => 'Khái niệm hệ phương trình, nghiệm của hệ phương trình.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Giải hệ phương trình bằng phương pháp thế', 'content' => 'Các bước giải hệ phương trình bằng phương pháp thế.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Giải hệ phương trình bằng phương pháp cộng đại số', 'content' => 'Các bước giải hệ phương trình bằng phương pháp cộng đại số.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Đường tròn',
                    'description' => 'Nghiên cứu về đường tròn, dây cung, tiếp tuyến và các loại góc với đường tròn.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Sự xác định đường tròn, tính chất đối xứng', 'content' => 'Tâm, bán kính, đường kính, dây cung, cung.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Đường kính và dây cung', 'content' => 'Mối quan hệ giữa đường kính và dây cung.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Vị trí tương đối của đường thẳng và đường tròn', 'content' => 'Đường thẳng cắt đường tròn, tiếp xúc, không cắt.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tiếp tuyến của đường tròn', 'content' => 'Định nghĩa, tính chất và cách vẽ tiếp tuyến.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Vị trí tương đối của hai đường tròn', 'content' => 'Hai đường tròn cắt nhau, tiếp xúc, không giao nhau.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Góc với đường tròn và Hình trụ, hình nón, hình cầu',
                    'description' => 'Tìm hiểu về các loại góc liên quan đến đường tròn và các hình không gian cơ bản.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Góc ở tâm. Số đo cung', 'content' => 'Mối quan hệ giữa góc ở tâm và số đo cung.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Góc nội tiếp', 'content' => 'Định nghĩa, tính chất và hệ quả của góc nội tiếp.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Góc tạo bởi tia tiếp tuyến và dây cung', 'content' => 'Định nghĩa và tính chất của góc tạo bởi tia tiếp tuyến và dây cung.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Hình trụ, diện tích xung quanh và thể tích hình trụ', 'content' => 'Đặc điểm, công thức tính diện tích và thể tích hình trụ.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Hình nón, hình cầu', 'content' => 'Đặc điểm, công thức tính diện tích và thể tích hình nón, hình cầu.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Toán lớp 10: Hàm số, Phương trình, Bất phương trình và Hình học',
            'description' => 'Khóa học cung cấp kiến thức về tập hợp, mệnh đề, hàm số, phương trình, bất phương trình, hệ phương trình, hệ bất phương trình và các kiến thức hình học phẳng.',
            'price' => 450000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Đã hoàn thành chương trình Toán THCS.',
                'Có khả năng tư duy trừu tượng và logic.'
            ],
            'objectives' => [
                'Nắm vững các khái niệm về tập hợp, mệnh đề và logic toán học.',
                'Xác định miền xác định, tính đơn điệu, tính chẵn lẻ của hàm số.',
                'Giải thành thạo các loại phương trình, bất phương trình, hệ phương trình, hệ bất phương trình.',
                'Hiểu và áp dụng các kiến thức về vectơ, tọa độ trong mặt phẳng.',
                'Giải các bài toán hình học phẳng liên quan đến đường thẳng, đường tròn, elip, hypebol, parabol.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Mệnh đề và Tập hợp',
                    'description' => 'Tìm hiểu về các khái niệm cơ bản của logic toán học và tập hợp.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Mệnh đề', 'content' => 'Định nghĩa mệnh đề, mệnh đề phủ định, mệnh đề kéo theo, mệnh đề tương đương.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Tập hợp', 'content' => 'Khái niệm tập hợp, tập con, tập hợp bằng nhau.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Các phép toán trên tập hợp', 'content' => 'Giao của hai tập hợp, hợp của hai tập hợp, hiệu của hai tập hợp, phần bù.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Các tập hợp số', 'content' => 'Tập hợp số tự nhiên, số nguyên, số hữu tỉ, số thực, các khoảng, đoạn, nửa khoảng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Hàm số bậc nhất và bậc hai',
                    'description' => 'Nghiên cứu sâu hơn về hàm số bậc nhất và bậc hai, đồ thị và các bài toán liên quan.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Đại cương về hàm số', 'content' => 'Khái niệm hàm số, tập xác định, tập giá trị, hàm số đồng biến, nghịch biến.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Hàm số bậc nhất', 'content' => 'Đồ thị, sự biến thiên, các bài toán liên quan đến đường thẳng.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Hàm số bậc hai', 'content' => 'Đồ thị (Parabol), đỉnh, trục đối xứng, sự biến thiên.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Khảo sát và vẽ đồ thị hàm số bậc hai', 'content' => 'Các bước khảo sát và vẽ đồ thị hàm số bậc hai.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Phương trình và Hệ phương trình',
                    'description' => 'Giải các loại phương trình và hệ phương trình phức tạp hơn.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Đại cương về phương trình', 'content' => 'Khái niệm phương trình tương đương, hệ quả.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Phương trình bậc nhất, bậc hai', 'content' => 'Ôn tập và nâng cao các bài toán về phương trình bậc nhất, bậc hai.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Phương trình quy về bậc hai (trùng phương, chứa ẩn ở mẫu, chứa dấu giá trị tuyệt đối)', 'content' => 'Các phương pháp giải các loại phương trình đặc biệt.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Hệ phương trình bậc nhất nhiều ẩn', 'content' => 'Giải hệ phương trình bằng phương pháp thế, cộng đại số, định thức.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Bất phương trình và Hệ bất phương trình',
                    'description' => 'Tìm hiểu về bất phương trình, hệ bất phương trình và ứng dụng.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Bất phương trình và hệ bất phương trình bậc nhất một ẩn', 'content' => 'Giải và biểu diễn tập nghiệm trên trục số.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Dấu của nhị thức bậc nhất', 'content' => 'Xét dấu nhị thức bậc nhất và ứng dụng giải bất phương trình.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Dấu của tam thức bậc hai', 'content' => 'Xét dấu tam thức bậc hai và ứng dụng giải bất phương trình bậc hai.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Bất phương trình và hệ bất phương trình bậc hai một ẩn', 'content' => 'Giải và ứng dụng giải các bài toán thực tế.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Vectơ và Tọa độ trong mặt phẳng',
                    'description' => 'Giới thiệu về vectơ, các phép toán vectơ và tọa độ trong mặt phẳng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Khái niệm vectơ', 'content' => 'Định nghĩa vectơ, hai vectơ bằng nhau, vectơ không, giá của vectơ.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Tổng và hiệu của hai vectơ', 'content' => 'Quy tắc ba điểm, quy tắc hình bình hành.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Tích của một số với một vectơ', 'content' => 'Định nghĩa, tính chất và ứng dụng.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Hệ trục tọa độ và tọa độ của vectơ, điểm', 'content' => 'Tọa độ của vectơ, tọa độ của điểm, các phép toán tọa độ.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Tích vô hướng của hai vectơ', 'content' => 'Định nghĩa, tính chất và ứng dụng tính góc, khoảng cách.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Phương pháp tọa độ trong mặt phẳng',
                    'description' => 'Áp dụng phương pháp tọa độ để giải các bài toán hình học phẳng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Phương trình đường thẳng', 'content' => 'Phương trình tham số, tổng quát, hệ số góc của đường thẳng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Vị trí tương đối của hai đường thẳng', 'content' => 'Hai đường thẳng song song, cắt nhau, trùng nhau.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Khoảng cách từ một điểm đến một đường thẳng', 'content' => 'Công thức tính khoảng cách và ứng dụng.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Phương trình đường tròn', 'content' => 'Phương trình chính tắc, phương trình tổng quát của đường tròn.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Phương trình Elip, Hypebol, Parabol', 'content' => 'Định nghĩa, phương trình chính tắc và hình dạng của các đường conic.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Toán lớp 11: Lượng giác, Tổ hợp, Xác suất và Hình học không gian',
            'description' => 'Khóa học cung cấp kiến thức về hàm số lượng giác, phương trình lượng giác, tổ hợp, xác suất, giới hạn và các kiến thức hình học không gian.',
            'price' => 480000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Đã hoàn thành chương trình Toán lớp 10.',
                'Có khả năng ghi nhớ công thức và áp dụng linh hoạt.'
            ],
            'objectives' => [
                'Nắm vững các công thức lượng giác, giải thành thạo phương trình lượng giác cơ bản và nâng cao.',
                'Thực hiện các bài toán về quy tắc đếm, hoán vị, chỉnh hợp, tổ hợp.',
                'Tính toán xác suất của các biến cố.',
                'Hiểu các khái niệm về giới hạn của dãy số, hàm số.',
                'Nắm vững các mối quan hệ song song, vuông góc trong không gian, tính khoảng cách, góc.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Hàm số lượng giác và Phương trình lượng giác',
                    'description' => 'Nghiên cứu về các hàm số lượng giác và cách giải các phương trình lượng giác.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Các công thức lượng giác cơ bản', 'content' => 'Công thức cộng, nhân đôi, biến đổi tổng thành tích, tích thành tổng.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Hàm số lượng giác', 'content' => 'Đồ thị, tập xác định, tập giá trị, tính chẵn lẻ, chu kỳ của các hàm sin, cos, tan, cot.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Phương trình lượng giác cơ bản', 'content' => 'Giải các phương trình sin x = a, cos x = a, tan x = a, cot x = a.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Một số phương trình lượng giác thường gặp', 'content' => 'Phương trình bậc hai với một hàm số lượng giác, phương trình đẳng cấp bậc hai.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 2: Tổ hợp và Xác suất',
                    'description' => 'Tìm hiểu về các quy tắc đếm, hoán vị, chỉnh hợp, tổ hợp và xác suất.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Quy tắc cộng và quy tắc nhân', 'content' => 'Ứng dụng trong các bài toán đếm số cách.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Hoán vị, chỉnh hợp, tổ hợp', 'content' => 'Định nghĩa, công thức và bài tập ứng dụng.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Nhị thức Newton', 'content' => 'Công thức nhị thức Newton và ứng dụng khai triển.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Biến cố và xác suất của biến cố', 'content' => 'Định nghĩa biến cố, không gian mẫu, công thức tính xác suất.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 3: Dãy số, Cấp số cộng và Cấp số nhân',
                    'description' => 'Nghiên cứu về dãy số, cấp số cộng, cấp số nhân và các bài toán liên quan.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Dãy số', 'content' => 'Định nghĩa dãy số, cách cho dãy số, dãy số tăng, giảm, bị chặn.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Cấp số cộng', 'content' => 'Định nghĩa, công thức số hạng tổng quát, tổng n số hạng đầu tiên.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Cấp số nhân', 'content' => 'Định nghĩa, công thức số hạng tổng quát, tổng n số hạng đầu tiên.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Ứng dụng của cấp số cộng, cấp số nhân trong thực tế', 'content' => 'Các bài toán lãi suất, tăng trưởng dân số.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 4: Giới hạn',
                    'description' => 'Giới thiệu về giới hạn của dãy số và giới hạn của hàm số.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Giới hạn của dãy số', 'content' => 'Định nghĩa giới hạn hữu hạn, vô cực của dãy số.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Các định lý về giới hạn hữu hạn', 'content' => 'Các quy tắc tính giới hạn của tổng, hiệu, tích, thương.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Giới hạn của hàm số', 'content' => 'Định nghĩa giới hạn của hàm số tại một điểm, tại vô cực.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Hàm số liên tục', 'content' => 'Định nghĩa hàm số liên tục tại một điểm, trên một khoảng.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Đạo hàm',
                    'description' => 'Giới thiệu về đạo hàm, các quy tắc tính đạo hàm và ứng dụng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Định nghĩa và ý nghĩa của đạo hàm', 'content' => 'Đạo hàm tại một điểm, đạo hàm trên một khoảng.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Các quy tắc tính đạo hàm', 'content' => 'Đạo hàm của tổng, hiệu, tích, thương, đạo hàm của hàm hợp.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Đạo hàm của các hàm số cơ bản', 'content' => 'Đạo hàm của hàm số mũ, logarit, lượng giác.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Vi phân và đạo hàm cấp cao', 'content' => 'Khái niệm vi phân, đạo hàm cấp hai, cấp ba.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Ứng dụng của đạo hàm trong vật lý', 'content' => 'Vận tốc tức thời, gia tốc.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Hình học không gian',
                    'description' => 'Nghiên cứu các mối quan hệ giữa đường thẳng, mặt phẳng trong không gian.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Đại cương về đường thẳng và mặt phẳng', 'content' => 'Vị trí tương đối của đường thẳng và mặt phẳng, hai mặt phẳng.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Đường thẳng vuông góc với mặt phẳng', 'content' => 'Định nghĩa, điều kiện, hình chiếu vuông góc.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Hai mặt phẳng vuông góc', 'content' => 'Định nghĩa, điều kiện, hình lăng trụ đứng, hình chóp đều.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Khoảng cách và góc trong không gian', 'content' => 'Khoảng cách từ điểm đến mặt phẳng, khoảng cách giữa hai đường thẳng chéo nhau.', 'sortOrder' => 4],
                        ['title' => 'Bài 6.5: Thể tích khối đa diện', 'content' => 'Công thức tính thể tích khối chóp, khối lăng trụ.', 'sortOrder' => 5],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Khóa học Toán lớp 12: Giải tích và Hình học không gian nâng cao',
            'description' => 'Khóa học tổng hợp và nâng cao kiến thức về đạo hàm, tích phân, số phức, ứng dụng của đạo hàm để khảo sát hàm số, và các bài toán hình học không gian phức tạp.',
            'price' => 500000.00,
            'categoryIds' => [79], // Toán học
            'requirements' => [
                'Đã hoàn thành chương trình Toán lớp 11.',
                'Có mục tiêu đạt điểm cao trong các kỳ thi quan trọng (THPT Quốc gia).'
            ],
            'objectives' => [
                'Thành thạo khảo sát và vẽ đồ thị hàm số, tìm giá trị lớn nhất, nhỏ nhất.',
                'Giải quyết các bài toán về tiệm cận, cực trị, sự đồng biến, nghịch biến của hàm số.',
                'Thực hiện thành thạo các phép tính tích phân và ứng dụng tính diện tích, thể tích.',
                'Nắm vững kiến thức về số phức, các phép toán và ứng dụng.',
                'Giải quyết các bài toán hình học không gian liên quan đến khoảng cách, góc, thể tích phức tạp.'
            ],
            'chapters' => [
                [
                    'title' => 'Chương 1: Ứng dụng của đạo hàm để khảo sát và vẽ đồ thị hàm số',
                    'description' => 'Nghiên cứu sâu về các ứng dụng của đạo hàm trong việc khảo sát hàm số.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Bài 1.1: Sự đồng biến, nghịch biến của hàm số', 'content' => 'Điều kiện để hàm số đồng biến, nghịch biến trên một khoảng.', 'sortOrder' => 1],
                        ['title' => 'Bài 1.2: Cực trị của hàm số', 'content' => 'Tìm điểm cực đại, cực tiểu của hàm số.', 'sortOrder' => 2],
                        ['title' => 'Bài 1.3: Giá trị lớn nhất và giá trị nhỏ nhất của hàm số', 'content' => 'Tìm GTLN, GTNN trên một đoạn, khoảng.', 'sortOrder' => 3],
                        ['title' => 'Bài 1.4: Đường tiệm cận của đồ thị hàm số', 'content' => 'Tiệm cận đứng, tiệm cận ngang, tiệm cận xiên.', 'sortOrder' => 4],
                        ['title' => 'Bài 1.5: Khảo sát sự biến thiên và vẽ đồ thị hàm số', 'content' => 'Các bước khảo sát và vẽ đồ thị các hàm số bậc ba, trùng phương, phân thức.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 2: Hàm số lũy thừa, hàm số mũ và hàm số logarit',
                    'description' => 'Tìm hiểu về các hàm số đặc biệt này và các bài toán liên quan.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Bài 2.1: Lũy thừa với số mũ thực', 'content' => 'Định nghĩa, tính chất và các phép toán với lũy thừa.', 'sortOrder' => 1],
                        ['title' => 'Bài 2.2: Hàm số lũy thừa', 'content' => 'Định nghĩa, tập xác định, đạo hàm của hàm số lũy thừa.', 'sortOrder' => 2],
                        ['title' => 'Bài 2.3: Logarit', 'content' => 'Định nghĩa, tính chất của logarit.', 'sortOrder' => 3],
                        ['title' => 'Bài 2.4: Hàm số mũ và hàm số logarit', 'content' => 'Đồ thị, tính chất, đạo hàm của hàm số mũ và logarit.', 'sortOrder' => 4],
                        ['title' => 'Bài 2.5: Phương trình, bất phương trình mũ và logarit', 'content' => 'Các phương pháp giải phương trình, bất phương trình mũ và logarit.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 3: Nguyên hàm, tích phân và ứng dụng',
                    'description' => 'Nghiên cứu về nguyên hàm, tích phân và các ứng dụng trong hình học.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Bài 3.1: Nguyên hàm', 'content' => 'Định nghĩa, tính chất, bảng nguyên hàm cơ bản.', 'sortOrder' => 1],
                        ['title' => 'Bài 3.2: Các phương pháp tính nguyên hàm', 'content' => 'Phương pháp đổi biến, phương pháp tích phân từng phần.', 'sortOrder' => 2],
                        ['title' => 'Bài 3.3: Tích phân', 'content' => 'Định nghĩa tích phân, tính chất, ý nghĩa hình học.', 'sortOrder' => 3],
                        ['title' => 'Bài 3.4: Ứng dụng của tích phân trong hình học', 'content' => 'Tính diện tích hình phẳng, thể tích vật thể tròn xoay.', 'sortOrder' => 4],
                        ['title' => 'Bài 3.5: Ứng dụng của tích phân trong vật lý', 'content' => 'Tính công, quãng đường.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 4: Số phức',
                    'description' => 'Giới thiệu về số phức, các phép toán và biểu diễn hình học của số phức.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Bài 4.1: Số phức', 'content' => 'Định nghĩa số phức, phần thực, phần ảo, môđun của số phức.', 'sortOrder' => 1],
                        ['title' => 'Bài 4.2: Cộng, trừ, nhân, chia số phức', 'content' => 'Quy tắc thực hiện các phép toán với số phức.', 'sortOrder' => 2],
                        ['title' => 'Bài 4.3: Phương trình bậc hai với hệ số thực', 'content' => 'Giải phương trình bậc hai trong tập số phức.', 'sortOrder' => 3],
                        ['title' => 'Bài 4.4: Biểu diễn hình học của số phức', 'content' => 'Điểm biểu diễn số phức trên mặt phẳng tọa độ.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chương 5: Khối đa diện và Thể tích khối đa diện',
                    'description' => 'Nghiên cứu sâu hơn về các loại khối đa diện và cách tính thể tích của chúng.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Bài 5.1: Khái niệm về khối đa diện', 'content' => 'Định nghĩa khối đa diện, khối lăng trụ, khối chóp.', 'sortOrder' => 1],
                        ['title' => 'Bài 5.2: Thể tích khối lăng trụ', 'content' => 'Công thức tính thể tích khối lăng trụ.', 'sortOrder' => 2],
                        ['title' => 'Bài 5.3: Thể tích khối chóp', 'content' => 'Công thức tính thể tích khối chóp.', 'sortOrder' => 3],
                        ['title' => 'Bài 5.4: Tỷ số thể tích của khối chóp, khối lăng trụ', 'content' => 'Ứng dụng tỷ số thể tích để giải bài toán.', 'sortOrder' => 4],
                        ['title' => 'Bài 5.5: Khoảng cách và góc trong không gian', 'content' => 'Ôn tập và nâng cao các bài toán về khoảng cách, góc.', 'sortOrder' => 5],
                    ]
                ],
                [
                    'title' => 'Chương 6: Mặt nón, mặt trụ, mặt cầu',
                    'description' => 'Tìm hiểu về các mặt tròn xoay và các bài toán liên quan đến chúng.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Bài 6.1: Mặt nón và hình nón', 'content' => 'Định nghĩa, công thức tính diện tích xung quanh, thể tích.', 'sortOrder' => 1],
                        ['title' => 'Bài 6.2: Mặt trụ và hình trụ', 'content' => 'Định nghĩa, công thức tính diện tích xung quanh, thể tích.', 'sortOrder' => 2],
                        ['title' => 'Bài 6.3: Mặt cầu và khối cầu', 'content' => 'Định nghĩa, công thức tính diện tích mặt cầu, thể tích khối cầu.', 'sortOrder' => 3],
                        ['title' => 'Bài 6.4: Vị trí tương đối của mặt phẳng với mặt cầu, đường thẳng với mặt cầu', 'content' => 'Các trường hợp giao nhau và không giao nhau.', 'sortOrder' => 4],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Curso de Enseñanza de Inglés para Principiantes',
            'description' => 'Aprende las bases para enseñar inglés a principiantes, cubriendo gramática esencial, vocabulario y metodologías de enseñanza efectivas.',
            'price' => 599000.00,
            'categoryIds' => [85, 84, 78], // Tiếng Anh, Ngoại ngữ, Giảng dạy & Học thuật
            'requirements' => [
                'Conocimiento básico de español.',
                'Interés en la enseñanza de idiomas.',
                'Acceso a internet y una computadora.'
            ],
            'objectives' => [
                'Comprender los fundamentos de la gramática inglesa para principiantes.',
                'Adquirir vocabulario esencial para la comunicación diaria.',
                'Desarrollar habilidades para planificar y ejecutar lecciones de inglés.',
                'Aplicar metodologías de enseñanza interactivas y dinámicas.',
                'Evaluar el progreso de los estudiantes de manera efectiva.'
            ],
            'chapters' => [
                [
                    'title' => 'Capítulo 1: Introducción a la Enseñanza de Inglés',
                    'description' => 'Conceptos básicos y preparación para la enseñanza de inglés.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Lección 1.1: ¿Por qué enseñar inglés?', 'content' => 'La importancia global del inglés y las oportunidades de enseñanza.', 'sortOrder' => 1],
                        ['title' => 'Lección 1.2: Perfil del estudiante principiante', 'content' => 'Características y necesidades de los alumnos que inician.', 'sortOrder' => 2],
                        ['title' => 'Lección 1.3: Materiales y recursos esenciales', 'content' => 'Libros, plataformas online y herramientas didácticas.', 'sortOrder' => 3],
                        ['title' => 'Lección 1.4: Estableciendo objetivos de aprendizaje', 'content' => 'Cómo definir metas claras y alcanzables para los estudiantes.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Capítulo 2: Gramática Básica para Principiantes',
                    'description' => 'Los pilares gramaticales que todo principiante debe dominar.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Lección 2.1: El verbo "To Be" y pronombres', 'content' => 'Uso y conjugación de "To Be", pronombres personales y posesivos.', 'sortOrder' => 1],
                        ['title' => 'Lección 2.2: Presente Simple y Presente Continuo', 'content' => 'Formación, uso y diferencia entre ambos tiempos verbales.', 'sortOrder' => 2],
                        ['title' => 'Lección 2.3: Artículos (a, an, the) y sustantivos', 'content' => 'Uso de artículos definidos e indefinidos, sustantivos contables e incontables.', 'sortOrder' => 3],
                        ['title' => 'Lección 2.4: Adjetivos y Adverbios básicos', 'content' => 'Posición y uso de adjetivos y adverbios comunes.', 'sortOrder' => 4],
                        ['title' => 'Lección 2.5: Preposiciones de lugar y tiempo', 'content' => 'In, on, at, under, over, before, after, etc.', 'sortOrder' => 5],
                        ['title' => 'Lección 2.6: Preguntas y respuestas (Wh-questions)', 'content' => 'Formación de preguntas con Who, What, Where, When, Why, How.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Capítulo 3: Vocabulario Esencial y Pronunciación',
                    'description' => 'Construcción de un vocabulario fundamental y técnicas de pronunciación.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Lección 3.1: Saludos y presentaciones', 'content' => 'Frases comunes para iniciar y terminar conversaciones.', 'sortOrder' => 1],
                        ['title' => 'Lección 3.2: Familia, amigos y descripciones personales', 'content' => 'Vocabulario para hablar de relaciones y características físicas.', 'sortOrder' => 2],
                        ['title' => 'Lección 3.3: Comida, bebida y restaurantes', 'content' => 'Nombres de alimentos, bebidas y frases para ordenar.', 'sortOrder' => 3],
                        ['title' => 'Lección 3.4: Números, colores y formas', 'content' => 'Vocabulario básico para describir y cuantificar.', 'sortOrder' => 4],
                        ['title' => 'Lección 3.5: Pronunciación de vocales y consonantes', 'content' => 'Ejercicios para mejorar la articulación de sonidos clave.', 'sortOrder' => 5],
                        ['title' => 'Lección 3.6: Entonación y ritmo en inglés', 'content' => 'Cómo el estrés y el ritmo afectan el significado.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Capítulo 4: Habilidades de Comunicación Oral',
                    'description' => 'Desarrollo de la fluidez y la comprensión auditiva.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Lección 4.1: Escucha activa y comprensión', 'content' => 'Estrategias para entender conversaciones simples.', 'sortOrder' => 1],
                        ['title' => 'Lección 4.2: Conversaciones cortas y diálogos', 'content' => 'Práctica de interacciones cotidianas.', 'sortOrder' => 2],
                        ['title' => 'Lección 4.3: Hablar sobre el día a día y rutinas', 'content' => 'Descripción de actividades diarias y hábitos.', 'sortOrder' => 3],
                        ['title' => 'Lección 4.4: Expresar gustos y preferencias', 'content' => 'Cómo decir lo que te gusta y lo que no.', 'sortOrder' => 4],
                        ['title' => 'Lección 4.5: Juegos de rol y simulaciones', 'content' => 'Actividades para practicar situaciones reales.', 'sortOrder' => 5],
                        ['title' => 'Lección 4.6: Corrección de errores comunes', 'content' => 'Identificar y corregir fallos típicos en principiantes.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Capítulo 5: Lectura y Escritura para Principiantes',
                    'description' => 'Fomento de la comprensión lectora y la expresión escrita.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Lección 5.1: Lectura de textos simples', 'content' => 'Cuentos cortos, descripciones y mensajes sencillos.', 'sortOrder' => 1],
                        ['title' => 'Lección 5.2: Comprensión de instrucciones básicas', 'content' => 'Seguir indicaciones en inglés.', 'sortOrder' => 2],
                        ['title' => 'Lección 5.3: Escribir oraciones y párrafos cortos', 'content' => 'Construcción de frases coherentes y textos breves.', 'sortOrder' => 3],
                        ['title' => 'Lección 5.4: Redacción de correos electrónicos sencillos', 'content' => 'Escribir emails para situaciones cotidianas.', 'sortOrder' => 4],
                        ['title' => 'Lección 5.5: Uso de diccionarios y traductores', 'content' => 'Herramientas útiles para el aprendizaje.', 'sortOrder' => 5],
                        ['title' => 'Lección 5.6: Ejercicios de reescritura y resumen', 'content' => 'Mejorar la expresión escrita a través de la práctica.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Capítulo 6: Planificación y Evaluación de Clases',
                    'description' => 'Estrategias para diseñar y evaluar el aprendizaje de los estudiantes.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Lección 6.1: Diseño de unidades didácticas', 'content' => 'Cómo estructurar un curso de inglés para principiantes.', 'sortOrder' => 1],
                        ['title' => 'Lección 6.2: Actividades para el aula', 'content' => 'Juegos, dinámicas y ejercicios para mantener el interés.', 'sortOrder' => 2],
                        ['title' => 'Lección 6.3: Manejo de la clase y motivación', 'content' => 'Técnicas para gestionar grupos y mantener a los alumnos motivados.', 'sortOrder' => 3],
                        ['title' => 'Lección 6.4: Evaluación formativa y sumativa', 'content' => 'Tipos de evaluación y cómo implementarlas.', 'sortOrder' => 4],
                        ['title' => 'Lección 6.5: Retroalimentación efectiva', 'content' => 'Cómo dar feedback constructivo a los estudiantes.', 'sortOrder' => 5],
                        ['title' => 'Lección 6.6: Recursos adicionales y autoaprendizaje', 'content' => 'Fomentar la autonomía del estudiante fuera del aula.', 'sortOrder' => 6],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Cours d\'Enseignement de l\'Anglais pour Débutants',
            'description' => 'Apprenez les bases pour enseigner l\'anglais aux débutants, couvrant la grammaire essentielle, le vocabulaire et les méthodologies d\'enseignement efficaces.',
            'price' => 599000.00,
            'categoryIds' => [85, 84, 78], // Tiếng Anh, Ngoại ngữ, Giảng dạy & Học thuật
            'requirements' => [
                'Connaissance de base du français.',
                'Intérêt pour l\'enseignement des langues.',
                'Accès à internet et un ordinateur.'
            ],
            'objectives' => [
                'Comprendre les fondements de la grammaire anglaise pour débutants.',
                'Acquérir un vocabulaire essentiel pour la communication quotidienne.',
                'Développer des compétences pour planifier et exécuter des leçons d\'anglais.',
                'Appliquer des méthodologies d\'enseignement interactives et dynamiques.',
                'Évaluer efficacement les progrès des étudiants.'
            ],
            'chapters' => [
                [
                    'title' => 'Chapitre 1: Introduction à l\'Enseignement de l\'Anglais',
                    'description' => 'Concepts de base et préparation à l\'enseignement de l\'anglais.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Leçon 1.1: Pourquoi enseigner l\'anglais ?', 'content' => 'L\'importance mondiale de l\'anglais et les opportunités d\'enseignement.', 'sortOrder' => 1],
                        ['title' => 'Leçon 1.2: Profil de l\'étudiant débutant', 'content' => 'Caractéristiques et besoins des apprenants débutants.', 'sortOrder' => 2],
                        ['title' => 'Leçon 1.3: Matériels et ressources essentiels', 'content' => 'Livres, plateformes en ligne et outils didactiques.', 'sortOrder' => 3],
                        ['title' => 'Leçon 1.4: Établir des objectifs d\'apprentissage', 'content' => 'Comment définir des objectifs clairs et réalisables pour les étudiants.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Chapitre 2: Grammaire de Base pour Débutants',
                    'description' => 'Les piliers grammaticaux que tout débutant doit maîtriser.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Leçon 2.1: Le verbe "To Be" et les pronoms', 'content' => 'Utilisation et conjugaison de "To Be", pronoms personnels et possessifs.', 'sortOrder' => 1],
                        ['title' => 'Leçon 2.2: Présent Simple et Présent Continu', 'content' => 'Formation, utilisation et différence entre ces deux temps verbaux.', 'sortOrder' => 2],
                        ['title' => 'Leçon 2.3: Articles (a, an, the) et noms', 'content' => 'Utilisation des articles définis et indéfinis, noms dénombrables et indénombrables.', 'sortOrder' => 3],
                        ['title' => 'Leçon 2.4: Adjectifs et Adverbes de base', 'content' => 'Position et utilisation des adjectifs et adverbes courants.', 'sortOrder' => 4],
                        ['title' => 'Leçon 2.5: Prépositions de lieu et de temps', 'content' => 'In, on, at, under, over, before, after, etc.', 'sortOrder' => 5],
                        ['title' => 'Leçon 2.6: Questions et réponses (Wh-questions)', 'content' => 'Formation de questions avec Who, What, Where, When, Why, How.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Chapitre 3: Vocabulaire Essentiel et Prononciation',
                    'description' => 'Construction d\'un vocabulaire fondamental et techniques de prononciation.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Leçon 3.1: Salutations et présentations', 'content' => 'Phrases courantes pour commencer et terminer des conversations.', 'sortOrder' => 1],
                        ['title' => 'Leçon 3.2: Famille, amis et descriptions personnelles', 'content' => 'Vocabulaire pour parler des relations et des caractéristiques physiques.', 'sortOrder' => 2],
                        ['title' => 'Leçon 3.3: Nourriture, boissons et restaurants', 'content' => 'Noms d\'aliments, de boissons et phrases pour commander.', 'sortOrder' => 3],
                        ['title' => 'Leçon 3.4: Nombres, couleurs et formes', 'content' => 'Vocabulaire de base pour décrire et quantifier.', 'sortOrder' => 4],
                        ['title' => 'Leçon 3.5: Prononciation des voyelles et consonnes', 'content' => 'Exercices pour améliorer l\'articulation des sons clés.', 'sortOrder' => 5],
                        ['title' => 'Leçon 3.6: Intonation et rythme en anglais', 'content' => 'Comment le stress et le rythme affectent le sens.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Chapitre 4: Compétences de Communication Orale',
                    'description' => 'Développement de la fluidité et de la compréhension auditive.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Leçon 4.1: Écoute active et compréhension', 'content' => 'Stratégies pour comprendre des conversations simples.', 'sortOrder' => 1],
                        ['title' => 'Leçon 4.2: Conversations courtes et dialogues', 'content' => 'Pratique des interactions quotidiennes.', 'sortOrder' => 2],
                        ['title' => 'Leçon 4.3: Parler du quotidien et des routines', 'content' => 'Description des activités quotidiennes et des habitudes.', 'sortOrder' => 3],
                        ['title' => 'Leçon 4.4: Exprimer ses goûts et préférences', 'content' => 'Comment dire ce que vous aimez et ce que vous n\'aimez pas.', 'sortOrder' => 4],
                        ['title' => 'Leçon 4.5: Jeux de rôle et simulations', 'content' => 'Activités pour pratiquer des situations réelles.', 'sortOrder' => 5],
                        ['title' => 'Leçon 4.6: Correction des erreurs courantes', 'content' => 'Identifier et corriger les fautes typiques chez les débutants.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Chapitre 5: Lecture et Écriture pour Débutants',
                    'description' => 'Développement de la compréhension écrite et de l\'expression écrite.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Leçon 5.1: Lecture de textes simples', 'content' => 'Contes courts, descriptions et messages simples.', 'sortOrder' => 1],
                        ['title' => 'Leçon 5.2: Compréhension des instructions de base', 'content' => 'Suivre des indications en anglais.', 'sortOrder' => 2],
                        ['title' => 'Leçon 5.3: Écrire des phrases et des paragraphes courts', 'content' => 'Construction de phrases cohérentes et de textes brefs.', 'sortOrder' => 3],
                        ['title' => 'Leçon 5.4: Rédaction d\'e-mails simples', 'content' => 'Écrire des e-mails pour des situations quotidiennes.', 'sortOrder' => 4],
                        ['title' => 'Leçon 5.5: Utilisation de dictionnaires et traducteurs', 'content' => 'Outils utiles pour l\'apprentissage.', 'sortOrder' => 5],
                        ['title' => 'Leçon 5.6: Exercices de réécriture et de résumé', 'content' => 'Améliorer l\'expression écrite par la pratique.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Chapitre 6: Planification et Évaluation des Cours',
                    'description' => 'Stratégies pour concevoir et évaluer l\'apprentissage des étudiants.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Leçon 6.1: Conception d\'unités didactiques', 'content' => 'Comment structurer un cours d\'anglais pour débutants.', 'sortOrder' => 1],
                        ['title' => 'Leçon 6.2: Activités pour la classe', 'content' => 'Jeux, dynamiques et exercices pour maintenir l\'intérêt.', 'sortOrder' => 2],
                        ['title' => 'Leçon 6.3: Gestion de classe et motivation', 'content' => 'Techniques pour gérer des groupes et maintenir les élèves motivés.', 'sortOrder' => 3],
                        ['title' => 'Leçon 6.4: Évaluation formative et sommative', 'content' => 'Types d\'évaluation et comment les mettre en œuvre.', 'sortOrder' => 4],
                        ['title' => 'Leçon 6.5: Rétroaction efficace', 'content' => 'Comment donner un feedback constructif aux étudiants.', 'sortOrder' => 5],
                        ['title' => 'Leçon 6.6: Ressources supplémentaires et auto-apprentissage', 'content' => 'Encourager l\'autonomie de l\'étudiant en dehors de la classe.', 'sortOrder' => 6],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Englisch-Lehrkurs für Anfänger',
            'description' => 'Lernen Sie die Grundlagen, um Anfängern Englisch beizubringen, einschließlich wesentlicher Grammatik, Vokabular und effektiver Lehrmethoden.',
            'price' => 599000.00,
            'categoryIds' => [85, 84, 78], // Tiếng Anh, Ngoại ngữ, Giảng dạy & Học thuật
            'requirements' => [
                'Grundkenntnisse in Deutsch.',
                'Interesse am Sprachenunterricht.',
                'Internetzugang und ein Computer.'
            ],
            'objectives' => [
                'Die Grundlagen der englischen Grammatik für Anfänger verstehen.',
                'Wesentliches Vokabular für die tägliche Kommunikation erwerben.',
                'Fähigkeiten zur Planung und Durchführung von Englischstunden entwickeln.',
                'Interaktive und dynamische Lehrmethoden anwenden.',
                'Den Fortschritt der Schüler effektiv bewerten.'
            ],
            'chapters' => [
                [
                    'title' => 'Kapitel 1: Einführung in den Englischunterricht',
                    'description' => 'Grundlagen und Vorbereitung für den Englischunterricht.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Lektion 1.1: Warum Englisch unterrichten?', 'content' => 'Die globale Bedeutung des Englischen und die Lehrmöglichkeiten.', 'sortOrder' => 1],
                        ['title' => 'Lektion 1.2: Profil des Anfänger-Schülers', 'content' => 'Eigenschaften und Bedürfnisse von Anfängern.', 'sortOrder' => 2],
                        ['title' => 'Lektion 1.3: Wesentliche Materialien und Ressourcen', 'content' => 'Bücher, Online-Plattformen und didaktische Werkzeuge.', 'sortOrder' => 3],
                        ['title' => 'Lektion 1.4: Lernziele festlegen', 'content' => 'Wie man klare und erreichbare Ziele für Schüler definiert.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Kapitel 2: Grundlegende Grammatik für Anfänger',
                    'description' => 'Die grammatikalischen Säulen, die jeder Anfänger beherrschen sollte.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Lektion 2.1: Das Verb "To Be" und Pronomen', 'content' => 'Verwendung und Konjugation von "To Be", Personal- und Possessivpronomen.', 'sortOrder' => 1],
                        ['title' => 'Lektion 2.2: Simple Present und Present Continuous', 'content' => 'Bildung, Verwendung und Unterschied zwischen diesen beiden Zeitformen.', 'sortOrder' => 2],
                        ['title' => 'Lektion 2.3: Artikel (a, an, the) und Substantive', 'content' => 'Verwendung von bestimmten und unbestimmten Artikeln, zählbare und unzählbare Substantive.', 'sortOrder' => 3],
                        ['title' => 'Lektion 2.4: Grundlegende Adjektive und Adverbien', 'content' => 'Position und Verwendung gängiger Adjektive und Adverbien.', 'sortOrder' => 4],
                        ['title' => 'Lektion 2.5: Präpositionen des Ortes und der Zeit', 'content' => 'In, on, at, under, over, before, after, etc.', 'sortOrder' => 5],
                        ['title' => 'Lektion 2.6: Fragen und Antworten (Wh-questions)', 'content' => 'Bildung von Fragen mit Who, What, Where, When, Why, How.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Kapitel 3: Wesentliches Vokabular und Aussprache',
                    'description' => 'Aufbau eines grundlegenden Wortschatzes und Aussprachetechniken.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Lektion 3.1: Begrüßungen und Vorstellungen', 'content' => 'Gängige Phrasen zum Beginnen und Beenden von Gesprächen.', 'sortOrder' => 1],
                        ['title' => 'Lektion 3.2: Familie, Freunde und persönliche Beschreibungen', 'content' => 'Vokabular, um über Beziehungen und körperliche Merkmale zu sprechen.', 'sortOrder' => 2],
                        ['title' => 'Lektion 3.3: Essen, Trinken und Restaurants', 'content' => 'Namen von Lebensmitteln, Getränken und Phrasen zum Bestellen.', 'sortOrder' => 3],
                        ['title' => 'Lektion 3.4: Zahlen, Farben und Formen', 'content' => 'Grundlegendes Vokabular zum Beschreiben und Quantifizieren.', 'sortOrder' => 4],
                        ['title' => 'Lektion 3.5: Aussprache von Vokalen und Konsonanten', 'content' => 'Übungen zur Verbesserung der Artikulation wichtiger Laute.', 'sortOrder' => 5],
                        ['title' => 'Lektion 3.6: Intonation und Rhythmus im Englischen', 'content' => 'Wie Betonung und Rhythmus die Bedeutung beeinflussen.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Kapitel 4: Mündliche Kommunikationsfähigkeiten',
                    'description' => 'Entwicklung von Sprachfluss und Hörverständnis.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Lektion 4.1: Aktives Zuhören und Verständnis', 'content' => 'Strategien zum Verstehen einfacher Gespräche.', 'sortOrder' => 1],
                        ['title' => 'Lektion 4.2: Kurze Gespräche und Dialoge', 'content' => 'Übung alltäglicher Interaktionen.', 'sortOrder' => 2],
                        ['title' => 'Lektion 4.3: Über den Alltag und Routinen sprechen', 'content' => 'Beschreibung täglicher Aktivitäten und Gewohnheiten.', 'sortOrder' => 3],
                        ['title' => 'Lektion 4.4: Vorlieben und Abneigungen ausdrücken', 'content' => 'Wie man sagt, was man mag und was nicht.', 'sortOrder' => 4],
                        ['title' => 'Lektion 4.5: Rollenspiele und Simulationen', 'content' => 'Aktivitäten zum Üben realer Situationen.', 'sortOrder' => 5],
                        ['title' => 'Lektion 4.6: Korrektur häufiger Fehler', 'content' => 'Typische Fehler bei Anfängern identifizieren und korrigieren.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Kapitel 5: Lesen und Schreiben für Anfänger',
                    'description' => 'Förderung des Leseverständnisses und des schriftlichen Ausdrucks.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Lektion 5.1: Lesen einfacher Texte', 'content' => 'Kurzgeschichten, Beschreibungen und einfache Nachrichten.', 'sortOrder' => 1],
                        ['title' => 'Lektion 5.2: Verständnis grundlegender Anweisungen', 'content' => 'Anweisungen auf Englisch befolgen.', 'sortOrder' => 2],
                        ['title' => 'Lektion 5.3: Schreiben von Sätzen und kurzen Absätzen', 'content' => 'Bildung kohärenter Sätze und kurzer Texte.', 'sortOrder' => 3],
                        ['title' => 'Lektion 5.4: Verfassen einfacher E-Mails', 'content' => 'E-Mails für alltägliche Situationen schreiben.', 'sortOrder' => 4],
                        ['title' => 'Lektion 5.5: Verwendung von Wörterbüchern und Übersetzern', 'content' => 'Nützliche Werkzeuge zum Lernen.', 'sortOrder' => 5],
                        ['title' => 'Lektion 5.6: Umschreibungs- und Zusammenfassungsübungen', 'content' => 'Verbesserung des schriftlichen Ausdrucks durch Übung.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Kapitel 6: Unterrichtsplanung und -bewertung',
                    'description' => 'Strategien zur Gestaltung und Bewertung des Schülerlernens.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Lektion 6.1: Gestaltung von Unterrichtseinheiten', 'content' => 'Wie man einen Englischkurs für Anfänger strukturiert.', 'sortOrder' => 1],
                        ['title' => 'Lektion 6.2: Aktivitäten für den Unterricht', 'content' => 'Spiele, Dynamiken und Übungen, um das Interesse zu wecken.', 'sortOrder' => 2],
                        ['title' => 'Lektion 6.3: Klassenführung und Motivation', 'content' => 'Techniken zur Gruppenverwaltung und Motivierung der Schüler.', 'sortOrder' => 3],
                        ['title' => 'Lektion 6.4: Formative und summative Bewertung', 'content' => 'Arten der Bewertung und deren Umsetzung.', 'sortOrder' => 4],
                        ['title' => 'Lektion 6.5: Effektives Feedback', 'content' => 'Wie man Schülern konstruktives Feedback gibt.', 'sortOrder' => 5],
                        ['title' => 'Lektion 6.6: Zusätzliche Ressourcen und Selbstlernen', 'content' => 'Förderung der Schülerautonomie außerhalb des Klassenzimmers.', 'sortOrder' => 6],
                    ]
                ]
            ]
        ],
        [
            'title' => '初心者向け英語教育コース',
            'description' => '初心者向けに英語を教えるための基礎を学びます。必須の文法、語彙、効果的な指導方法を網羅しています。',
            'price' => 599000.00,
            'categoryIds' => [85, 84, 78], // Tiếng Anh, Ngoại ngữ, Giảng dạy & Học thuật
            'requirements' => [
                '日本語の基本的な知識。',
                '語学教育への関心。',
                'インターネット接続とコンピュータ。'
            ],
            'objectives' => [
                '初心者向け英語文法の基礎を理解する。',
                '日常会話に必要な語彙を習得する。',
                '英語のレッスンを計画・実行するスキルを開発する。',
                'インタラクティブでダイナミックな指導方法を適用する。',
                '生徒の進捗を効果的に評価する。'
            ],
            'chapters' => [
                [
                    'title' => '第1章: 英語教育入門',
                    'description' => '英語教育の基本概念と準備。',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'レッスン1.1: なぜ英語を教えるのか？', 'content' => '英語の世界的な重要性と教育の機会。', 'sortOrder' => 1],
                        ['title' => 'レッスン1.2: 初心者学習者のプロフィール', 'content' => '学習を開始する生徒の特徴とニーズ。', 'sortOrder' => 2],
                        ['title' => 'レッスン1.3: 必須の教材とリソース', 'content' => '書籍、オンラインプラットフォーム、教育ツール。', 'sortOrder' => 3],
                        ['title' => 'レッスン1.4: 学習目標の設定', 'content' => '生徒のために明確で達成可能な目標を定義する方法。', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => '第2章: 初心者向け基本文法',
                    'description' => 'すべての初心者が習得すべき文法の柱。',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'レッスン2.1: 動詞 "To Be" と代名詞', 'content' => '"To Be" の使用と活用、人称代名詞と所有代名詞。', 'sortOrder' => 1],
                        ['title' => 'レッスン2.2: 現在形と現在進行形', 'content' => 'これら2つの時制の形成、使用、および違い。', 'sortOrder' => 2],
                        ['title' => 'レッスン2.3: 冠詞 (a, an, the) と名詞', 'content' => '定冠詞と不定冠詞の使用、可算名詞と不可算名詞。', 'sortOrder' => 3],
                        ['title' => 'レッスン2.4: 基本的な形容詞と副詞', 'content' => '一般的な形容詞と副詞の位置と使用。', 'sortOrder' => 4],
                        ['title' => 'レッスン2.5: 場所と時間の前置詞', 'content' => 'In, on, at, under, over, before, afterなど。', 'sortOrder' => 5],
                        ['title' => 'レッスン2.6: 疑問文と回答 (Wh-questions)', 'content' => 'Who, What, Where, When, Why, How を使った疑問文の形成。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第3章: 必須語彙と発音',
                    'description' => '基本的な語彙の構築と発音テクニック。',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'レッスン3.1: 挨拶と自己紹介', 'content' => '会話を開始および終了するための一般的なフレーズ。', 'sortOrder' => 1],
                        ['title' => 'レッスン3.2: 家族、友人、個人的な説明', 'content' => '人間関係や身体的特徴について話すための語彙。', 'sortOrder' => 2],
                        ['title' => 'レッスン3.3: 食事、飲み物、レストラン', 'content' => '食品、飲み物の名前、注文するためのフレーズ。', 'sortOrder' => 3],
                        ['title' => 'レッスン3.4: 数字、色、形', 'content' => '記述と定量化のための基本的な語彙。', 'sortOrder' => 4],
                        ['title' => 'レッスン3.5: 母音と子音の発音', 'content' => '主要な音の明確化を改善するための演習。', 'sortOrder' => 5],
                        ['title' => 'レッスン3.6: 英語のイントネーションとリズム', 'content' => 'ストレスとリズムが意味にどのように影響するか。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第4章: 口頭コミュニケーションスキル',
                    'description' => '流暢さとリスニング理解の向上。',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'レッスン4.1: アクティブリスニングと理解', 'content' => '簡単な会話を理解するための戦略。', 'sortOrder' => 1],
                        ['title' => 'レッスン4.2: 短い会話と対話', 'content' => '日常的なやり取りの練習。', 'sortOrder' => 2],
                        ['title' => 'レッスン4.3: 日常生活とルーティンについて話す', 'content' => '日々の活動と習慣の説明。', 'sortOrder' => 3],
                        ['title' => 'レッスン4.4: 好みや嗜好を表現する', 'content' => '好きなものと嫌いなものを伝える方法。', 'sortOrder' => 4],
                        ['title' => 'レッスン4.5: ロールプレイングとシミュレーション', 'content' => '実際の状況を練習するための活動。', 'sortOrder' => 5],
                        ['title' => 'レッスン4.6: 一般的な間違いの修正', 'content' => '初心者の典型的な誤りを特定して修正する。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第5章: 初心者向け読解と作文',
                    'description' => '読解力と書面表現の促進。',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'レッスン5.1: 簡単なテキストの読解', 'content' => '短い物語、説明、簡単なメッセージ。', 'sortOrder' => 1],
                        ['title' => 'レッスン5.2: 基本的な指示の理解', 'content' => '英語の指示に従う。', 'sortOrder' => 2],
                        ['title' => 'レッスン5.3: 短い文と段落の作成', 'content' => '一貫性のある文と短いテキストの構築。', 'sortOrder' => 3],
                        ['title' => 'レッスン5.4: 簡単な電子メールの作成', 'content' => '日常的な状況のための電子メールの作成。', 'sortOrder' => 4],
                        ['title' => 'レッスン5.5: 辞書と翻訳ツールの使用', 'content' => '学習に役立つツール。', 'sortOrder' => 5],
                        ['title' => 'レッスン5.6: 書き換えと要約の演習', 'content' => '練習を通して書面表現を改善する。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第6章: 授業計画と評価',
                    'description' => '生徒の学習を設計し評価するための戦略。',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'レッスン6.1: 授業単位の設計', 'content' => '初心者向け英語コースの構成方法。', 'sortOrder' => 1],
                        ['title' => 'レッスン6.2: 教室活動', 'content' => '興味を維持するためのゲーム、ダイナミクス、演習。', 'sortOrder' => 2],
                        ['title' => 'レッスン6.3: クラス運営とモチベーション', 'content' => 'グループを管理し、生徒のモチベーションを維持するテクニック。', 'sortOrder' => 3],
                        ['title' => 'レッスン6.4: 形成的評価と総括的評価', 'content' => '評価の種類と実施方法。', 'sortOrder' => 4],
                        ['title' => 'レッスン6.5: 効果的なフィードバック', 'content' => '生徒に建設的なフィードバックを与える方法。', 'sortOrder' => 5],
                        ['title' => 'レッスン6.6: 追加リソースと自己学習', 'content' => '教室外での生徒の自律性を促進する。', 'sortOrder' => 6],
                    ]
                ]
            ]
        ],
        [
            'title' => '초급자를 위한 영어 교육 과정',
            'description' => '초급자에게 영어를 가르치기 위한 기초를 배우세요. 필수 문법, 어휘, 효과적인 교수법을 다룹니다.',
            'price' => 599000.00,
            'categoryIds' => [85, 84, 78], // Tiếng Anh, Ngoại ngữ, Giảng dạy & Học thuật
            'requirements' => [
                '한국어에 대한 기본적인 지식.',
                '언어 교육에 대한 관심.',
                '인터넷 접속 및 컴퓨터.'
            ],
            'objectives' => [
                '초급자를 위한 영어 문법의 기초를 이해합니다.',
                '일상적인 의사소통을 위한 필수 어휘를 습득합니다.',
                '영어 수업을 계획하고 실행하는 기술을 개발합니다.',
                '상호 작용적이고 역동적인 교수법을 적용합니다.',
                '학생들의 진행 상황을 효과적으로 평가합니다.'
            ],
            'chapters' => [
                [
                    'title' => '1장: 영어 교육 소개',
                    'description' => '영어 교육의 기본 개념 및 준비.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => '1.1과: 왜 영어를 가르쳐야 하는가?', 'content' => '영어의 세계적 중요성과 교육 기회.', 'sortOrder' => 1],
                        ['title' => '1.2과: 초급 학습자의 프로필', 'content' => '학습을 시작하는 학생들의 특징과 필요성.', 'sortOrder' => 2],
                        ['title' => '1.3과: 필수 자료 및 자원', 'content' => '책, 온라인 플랫폼 및 교육 도구.', 'sortOrder' => 3],
                        ['title' => '1.4과: 학습 목표 설정', 'content' => '학생들을 위한 명확하고 달성 가능한 목표를 정의하는 방법.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => '2장: 초급자를 위한 기본 문법',
                    'description' => '모든 초급자가 마스터해야 할 문법의 기둥.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => '2.1과: 동사 "To Be" 및 대명사', 'content' => '"To Be"의 사용 및 활용, 인칭 대명사 및 소유 대명사.', 'sortOrder' => 1],
                        ['title' => '2.2과: 현재 단순형 및 현재 진행형', 'content' => '두 시제의 형성, 사용 및 차이점.', 'sortOrder' => 2],
                        ['title' => '2.3과: 관사 (a, an, the) 및 명사', 'content' => '정관사 및 부정관사의 사용, 가산 명사 및 불가산 명사.', 'sortOrder' => 3],
                        ['title' => '2.4과: 기본 형용사 및 부사', 'content' => '일반적인 형용사 및 부사의 위치와 사용.', 'sortOrder' => 4],
                        ['title' => '2.5과: 장소 및 시간의 전치사', 'content' => 'In, on, at, under, over, before, after 등.', 'sortOrder' => 5],
                        ['title' => '2.6과: 질문 및 답변 (Wh-questions)', 'content' => 'Who, What, Where, When, Why, How를 사용한 질문 형성.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '3장: 필수 어휘 및 발음',
                    'description' => '기본 어휘 구축 및 발음 기술.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => '3.1과: 인사 및 자기소개', 'content' => '대화를 시작하고 끝내는 일반적인 문구.', 'sortOrder' => 1],
                        ['title' => '3.2과: 가족, 친구 및 개인 설명', 'content' => '관계 및 신체적 특징에 대해 이야기하기 위한 어휘.', 'sortOrder' => 2],
                        ['title' => '3.3과: 음식, 음료 및 레스토랑', 'content' => '음식, 음료 이름 및 주문을 위한 문구.', 'sortOrder' => 3],
                        ['title' => '3.4과: 숫자, 색상 및 모양', 'content' => '묘사 및 수량화를 위한 기본 어휘.', 'sortOrder' => 4],
                        ['title' => '3.5과: 모음 및 자음 발음', 'content' => '주요 소리의 명확성을 향상시키기 위한 연습.', 'sortOrder' => 5],
                        ['title' => '3.6과: 영어의 억양 및 리듬', 'content' => '강세와 리듬이 의미에 미치는 영향.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '4장: 구두 의사소통 기술',
                    'description' => '유창성 및 듣기 이해력 개발.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => '4.1과: 능동적 듣기 및 이해', 'content' => '간단한 대화를 이해하기 위한 전략.', 'sortOrder' => 1],
                        ['title' => '4.2과: 짧은 대화 및 대화', 'content' => '일상적인 상호 작용 연습.', 'sortOrder' => 2],
                        ['title' => '4.3과: 일상생활 및 루틴에 대해 이야기하기', 'content' => '매일의 활동과 습관 설명.', 'sortOrder' => 3],
                        ['title' => '4.4과: 취향 및 선호도 표현', 'content' => '좋아하는 것과 싫어하는 것을 말하는 방법.', 'sortOrder' => 4],
                        ['title' => '4.5과: 역할극 및 시뮬레이션', 'content' => '실제 상황을 연습하기 위한 활동.', 'sortOrder' => 5],
                        ['title' => '4.6과: 일반적인 오류 수정', 'content' => '초급자의 일반적인 오류를 식별하고 수정합니다.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '5장: 초급자를 위한 읽기 및 쓰기',
                    'description' => '읽기 이해력 및 서면 표현력 증진.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => '5.1과: 간단한 텍스트 읽기', 'content' => '짧은 이야기, 설명 및 간단한 메시지.', 'sortOrder' => 1],
                        ['title' => '5.2과: 기본 지침 이해', 'content' => '영어로 된 지시 따르기.', 'sortOrder' => 2],
                        ['title' => '5.3과: 짧은 문장 및 단락 작성', 'content' => '일관성 있는 문장 및 짧은 텍스트 구축.', 'sortOrder' => 3],
                        ['title' => '5.4과: 간단한 이메일 작성', 'content' => '일상적인 상황을 위한 이메일 작성.', 'sortOrder' => 4],
                        ['title' => '5.5과: 사전 및 번역기 사용', 'content' => '학습에 유용한 도구.', 'sortOrder' => 5],
                        ['title' => '5.6과: 다시 쓰기 및 요약 연습', 'content' => '연습을 통해 서면 표현 개선.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '6장: 수업 계획 및 평가',
                    'description' => '학생 학습을 설계하고 평가하기 위한 전략.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => '6.1과: 교육 단위 설계', 'content' => '초급자를 위한 영어 코스를 구성하는 방법.', 'sortOrder' => 1],
                        ['title' => '6.2과: 교실 활동', 'content' => '흥미를 유지하기 위한 게임, 역학 및 연습.', 'sortOrder' => 2],
                        ['title' => '6.3과: 수업 관리 및 동기 부여', 'content' => '그룹을 관리하고 학생들의 동기를 유지하는 기술.', 'sortOrder' => 3],
                        ['title' => '6.4과: 형성 평가 및 총괄 평가', 'content' => '평가 유형 및 구현 방법.', 'sortOrder' => 4],
                        ['title' => '6.5과: 효과적인 피드백', 'content' => '학생들에게 건설적인 피드백을 제공하는 방법.', 'sortOrder' => 5],
                        ['title' => '6.6과: 추가 자료 및 자기 학습', 'content' => '교실 밖에서 학생의 자율성 증진.', 'sortOrder' => 6],
                    ]
                ]
            ]
        ],
        [
            'title' => '初级英语教学课程',
            'description' => '学习如何教授初级英语，涵盖基本语法、词汇和有效的教学方法。',
            'price' => 599000.00,
            'categoryIds' => [85, 84, 78], // Tiếng Anh, Ngoại ngữ, Giảng dạy & Học thuật
            'requirements' => [
                '中文基础知识。',
                '对语言教学的兴趣。',
                '互联网连接和一台电脑。'
            ],
            'objectives' => [
                '理解初级英语语法的基本原理。',
                '掌握日常交流所需的基本词汇。',
                '发展英语课程的规划和执行能力。',
                '应用互动和动态的教学方法。',
                '有效评估学生的学习进度。'
            ],
            'chapters' => [
                [
                    'title' => '第一章: 英语教学导论',
                    'description' => '英语教学的基本概念和准备。',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => '1.1课: 为什么要教英语？', 'content' => '英语的全球重要性和教学机会。', 'sortOrder' => 1],
                        ['title' => '1.2课: 初级学习者的画像', 'content' => '初学者的特点和需求。', 'sortOrder' => 2],
                        ['title' => '1.3课: 基本教材和资源', 'content' => '书籍、在线平台和教学工具。', 'sortOrder' => 3],
                        ['title' => '1.4课: 设定学习目标', 'content' => '如何为学生定义清晰可达成的目标。', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => '第二章: 初级英语语法基础',
                    'description' => '每个初学者都应掌握的语法支柱。',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => '2.1课: 动词 "To Be" 和代词', 'content' => '"To Be" 的用法和变位，人称代词和物主代词。', 'sortOrder' => 1],
                        ['title' => '2.2课: 一般现在时和现在进行时', 'content' => '这两种时态的构成、用法和区别。', 'sortOrder' => 2],
                        ['title' => '2.3课: 冠词 (a, an, the) 和名词', 'content' => '定冠词和不定冠词的用法，可数名词和不可数名词。', 'sortOrder' => 3],
                        ['title' => '2.4课: 基本形容词和副词', 'content' => '常见形容词和副词的位置和用法。', 'sortOrder' => 4],
                        ['title' => '2.5课: 地点和时间介词', 'content' => 'In, on, at, under, over, before, after 等。', 'sortOrder' => 5],
                        ['title' => '2.6课: 疑问句和回答 (Wh-questions)', 'content' => '使用 Who, What, Where, When, Why, How 构成疑问句。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第三章: 基本词汇和发音',
                    'description' => '构建基础词汇和发音技巧。',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => '3.1课: 问候和自我介绍', 'content' => '开始和结束对话的常用短语。', 'sortOrder' => 1],
                        ['title' => '3.2课: 家庭、朋友和个人描述', 'content' => '谈论关系和身体特征的词汇。', 'sortOrder' => 2],
                        ['title' => '3.3课: 食物、饮料和餐厅', 'content' => '食物、饮料名称和点餐短语。', 'sortOrder' => 3],
                        ['title' => '3.4课: 数字、颜色和形状', 'content' => '描述和量化的基本词汇。', 'sortOrder' => 4],
                        ['title' => '3.5课: 元音和辅音发音', 'content' => '改善关键发音清晰度的练习。', 'sortOrder' => 5],
                        ['title' => '3.6课: 英语语调和节奏', 'content' => '重音和节奏如何影响意义。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第四章: 口语交流技能',
                    'description' => '发展流利度和听力理解能力。',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => '4.1课: 积极倾听和理解', 'content' => '理解简单对话的策略。', 'sortOrder' => 1],
                        ['title' => '4.2课: 简短对话和对白', 'content' => '日常互动的练习。', 'sortOrder' => 2],
                        ['title' => '4.3课: 谈论日常生活和例行公事', 'content' => '描述日常活动和习惯。', 'sortOrder' => 3],
                        ['title' => '4.4课: 表达喜好和偏爱', 'content' => '如何表达你喜欢和不喜欢的东西。', 'sortOrder' => 4],
                        ['title' => '4.5课: 角色扮演和模拟', 'content' => '练习真实情境的活动。', 'sortOrder' => 5],
                        ['title' => '4.6课: 常见错误纠正', 'content' => '识别和纠正初学者常见错误。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第五章: 初级读写',
                    'description' => '培养阅读理解和书面表达能力。',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => '5.1课: 阅读简单文本', 'content' => '短篇故事、描述和简单信息。', 'sortOrder' => 1],
                        ['title' => '5.2课: 理解基本指令', 'content' => '遵循英语指令。', 'sortOrder' => 2],
                        ['title' => '5.3课: 撰写短句和短段落', 'content' => '构建连贯的句子和简短的文本。', 'sortOrder' => 3],
                        ['title' => '5.4课: 撰写简单电子邮件', 'content' => '为日常情境撰写电子邮件。', 'sortOrder' => 4],
                        ['title' => '5.5课: 使用词典和翻译工具', 'content' => '有用的学习工具。', 'sortOrder' => 5],
                        ['title' => '5.6课: 改写和总结练习', 'content' => '通过练习提高书面表达能力。', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => '第六章: 课程规划和评估',
                    'description' => '设计和评估学生学习的策略。',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => '6.1课: 教学单元设计', 'content' => '如何构建初级英语课程。', 'sortOrder' => 1],
                        ['title' => '6.2课: 课堂活动', 'content' => '游戏、动态和练习以保持兴趣。', 'sortOrder' => 2],
                        ['title' => '6.3课: 课堂管理和激励', 'content' => '管理小组和保持学生积极性的技巧。', 'sortOrder' => 3],
                        ['title' => '6.4课: 形成性评估和总结性评估', 'content' => '评估类型及其实施方法。', 'sortOrder' => 4],
                        ['title' => '6.5课: 有效反馈', 'content' => '如何给学生建设性反馈。', 'sortOrder' => 5],
                        ['title' => '6.6课: 额外资源和自主学习', 'content' => '促进学生在课堂外的自主性。', 'sortOrder' => 6],
                    ]
                ]
            ]
        ],
        [
            'title' => 'Курс обучения английскому языку для начинающих',
            'description' => 'Изучите основы преподавания английского языка для начинающих, включая базовую грамматику, лексику и эффективные методики обучения.',
            'price' => 599000.00,
            'categoryIds' => [85, 84, 78], // Tiếng Anh, Ngoại ngữ, Giảng dạy & Học thuật
            'requirements' => [
                'Базовые знания русского языка.',
                'Интерес к преподаванию языков.',
                'Доступ в интернет и компьютер.'
            ],
            'objectives' => [
                'Понять основы английской грамматики для начинающих.',
                'Приобрести базовую лексику для повседневного общения.',
                'Развить навыки планирования и проведения уроков английского языка.',
                'Применять интерактивные и динамичные методики обучения.',
                'Эффективно оценивать прогресс студентов.'
            ],
            'chapters' => [
                [
                    'title' => 'Глава 1: Введение в преподавание английского языка',
                    'description' => 'Основные понятия и подготовка к преподаванию английского языка.',
                    'sortOrder' => 1,
                    'lessons' => [
                        ['title' => 'Урок 1.1: Зачем преподавать английский?', 'content' => 'Глобальное значение английского языка и возможности преподавания.', 'sortOrder' => 1],
                        ['title' => 'Урок 1.2: Профиль начинающего ученика', 'content' => 'Характеристики и потребности начинающих учеников.', 'sortOrder' => 2],
                        ['title' => 'Урок 1.3: Основные материалы и ресурсы', 'content' => 'Книги, онлайн-платформы и дидактические инструменты.', 'sortOrder' => 3],
                        ['title' => 'Урок 1.4: Постановка целей обучения', 'content' => 'Как определить четкие и достижимые цели для студентов.', 'sortOrder' => 4],
                    ]
                ],
                [
                    'title' => 'Глава 2: Базовая грамматика для начинающих',
                    'description' => 'Грамматические основы, которые должен освоить каждый начинающий.',
                    'sortOrder' => 2,
                    'lessons' => [
                        ['title' => 'Урок 2.1: Глагол "To Be" и местоимения', 'content' => 'Использование и спряжение "To Be", личные и притяжательные местоимения.', 'sortOrder' => 1],
                        ['title' => 'Урок 2.2: Present Simple и Present Continuous', 'content' => 'Образование, использование и разница между этими двумя временами.', 'sortOrder' => 2],
                        ['title' => 'Урок 2.3: Артикли (a, an, the) и существительные', 'content' => 'Использование определенных и неопределенных артиклей, исчисляемые и неисчисляемые существительные.', 'sortOrder' => 3],
                        ['title' => 'Урок 2.4: Базовые прилагательные и наречия', 'content' => 'Положение и использование распространенных прилагательных и наречий.', 'sortOrder' => 4],
                        ['title' => 'Урок 2.5: Предлоги места и времени', 'content' => 'In, on, at, under, over, before, after и т.д.', 'sortOrder' => 5],
                        ['title' => 'Урок 2.6: Вопросы и ответы (Wh-questions)', 'content' => 'Формирование вопросов с Who, What, Where, When, Why, How.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Глава 3: Основная лексика и произношение',
                    'description' => 'Наращивание базового словарного запаса и техники произношения.',
                    'sortOrder' => 3,
                    'lessons' => [
                        ['title' => 'Урок 3.1: Приветствия и представления', 'content' => 'Общие фразы для начала и окончания разговоров.', 'sortOrder' => 1],
                        ['title' => 'Урок 3.2: Семья, друзья и личные описания', 'content' => 'Лексика для разговора об отношениях и физических характеристиках.', 'sortOrder' => 2],
                        ['title' => 'Урок 3.3: Еда, напитки и рестораны', 'content' => 'Названия продуктов, напитков и фразы для заказа.', 'sortOrder' => 3],
                        ['title' => 'Урок 3.4: Числа, цвета и формы', 'content' => 'Базовая лексика для описания и количественной оценки.', 'sortOrder' => 4],
                        ['title' => 'Урок 3.5: Произношение гласных и согласных', 'content' => 'Упражнения для улучшения артикуляции ключевых звуков.', 'sortOrder' => 5],
                        ['title' => 'Урок 3.6: Интонация и ритм в английском языке', 'content' => 'Как ударение и ритм влияют на значение.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Глава 4: Навыки устной коммуникации',
                    'description' => 'Развитие беглости речи и понимания на слух.',
                    'sortOrder' => 4,
                    'lessons' => [
                        ['title' => 'Урок 4.1: Активное слушание и понимание', 'content' => 'Стратегии для понимания простых разговоров.', 'sortOrder' => 1],
                        ['title' => 'Урок 4.2: Короткие разговоры и диалоги', 'content' => 'Практика повседневных взаимодействий.', 'sortOrder' => 2],
                        ['title' => 'Урок 4.3: Разговор о повседневной жизни и рутине', 'content' => 'Описание ежедневных занятий и привычек.', 'sortOrder' => 3],
                        ['title' => 'Урок 4.4: Выражение вкусов и предпочтений', 'content' => 'Как сказать, что вам нравится и что нет.', 'sortOrder' => 4],
                        ['title' => 'Урок 4.5: Ролевые игры и симуляции', 'content' => 'Действия для отработки реальных ситуаций.', 'sortOrder' => 5],
                        ['title' => 'Урок 4.6: Исправление распространенных ошибок', 'content' => 'Выявление и исправление типичных ошибок у начинающих.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Глава 5: Чтение и письмо для начинающих',
                    'description' => 'Развитие понимания прочитанного и письменного выражения.',
                    'sortOrder' => 5,
                    'lessons' => [
                        ['title' => 'Урок 5.1: Чтение простых текстов', 'content' => 'Короткие рассказы, описания и простые сообщения.', 'sortOrder' => 1],
                        ['title' => 'Урок 5.2: Понимание базовых инструкций', 'content' => 'Следование инструкциям на английском языке.', 'sortOrder' => 2],
                        ['title' => 'Урок 5.3: Написание коротких предложений и абзацев', 'content' => 'Построение связных предложений и коротких текстов.', 'sortOrder' => 3],
                        ['title' => 'Урок 5.4: Написание простых электронных писем', 'content' => 'Написание электронных писем для повседневных ситуаций.', 'sortOrder' => 4],
                        ['title' => 'Урок 5.5: Использование словарей и переводчиков', 'content' => 'Полезные инструменты для обучения.', 'sortOrder' => 5],
                        ['title' => 'Урок 5.6: Упражнения на перефразирование и суммирование', 'content' => 'Улучшение письменного выражения через практику.', 'sortOrder' => 6],
                    ]
                ],
                [
                    'title' => 'Глава 6: Планирование и оценка уроков',
                    'description' => 'Стратегии для разработки и оценки обучения студентов.',
                    'sortOrder' => 6,
                    'lessons' => [
                        ['title' => 'Урок 6.1: Разработка учебных модулей', 'content' => 'Как структурировать курс английского языка для начинающих.', 'sortOrder' => 1],
                        ['title' => 'Урок 6.2: Занятия для класса', 'content' => 'Игры, динамические упражнения для поддержания интереса.', 'sortOrder' => 2],
                        ['title' => 'Урок 6.3: Управление классом и мотивация', 'content' => 'Техники управления группами и поддержания мотивации учащихся.', 'sortOrder' => 3],
                        ['title' => 'Урок 6.4: Формирующее и итоговое оценивание', 'content' => 'Виды оценивания и способы их реализации.', 'sortOrder' => 4],
                        ['title' => 'Урок 6.5: Эффективная обратная связь', 'content' => 'Как давать конструктивную обратную связь студентам.', 'sortOrder' => 5],
                        ['title' => 'Урок 6.6: Дополнительные ресурсы и самообучение', 'content' => 'Поощрение самостоятельности студентов вне класса.', 'sortOrder' => 6],
                    ]
                ]
            ]
        ]
    ];


    public function __construct()
    {
        $this->db = new Database();

        $this->courseService = new CourseService();
        $this->categoryService = new CategoryService();
        $this->chapterService = new ChapterService();
        $this->lessonService = new LessonService();
        $this->courseRequirementService = new CourseRequirementService();
        $this->courseObjectiveService = new CourseObjectiveService();
        $this->instructorService = new InstructorService();
    }

    public function initialize(): void
    {
        echo "Starting course initialization...\n";

        // Vẫn cần fetch instructors vì không có sẵn trong code
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
            echo "WARNING: Failed to fetch instructors or no instructors found in the database. (Message: " . ($allInstructorsResponse->message ?? "N/A") . "). Courses requiring instructors might not be created or assigned correctly.\n";
        }

        // Sử dụng CATEGORIES_DATA trực tiếp thay vì gọi get_all_categories()
        $availableCategoryIDs = array_keys(self::CATEGORIES_DATA); // Lấy tất cả các ID từ hằng số
        $availableCategoryMap = self::CATEGORIES_DATA; // Sử dụng hằng số làm map

        echo count($availableCategoryIDs) . " categories loaded from constant.\n";

        if (empty(self::ADMIN_USER_ID_CREATED_BY)) {
            echo "ERROR: ADMIN_USER_ID_CREATED_BY is not configured. Please set this constant with an existing User ID.\n";
            echo "Course initialization aborted.\n";
            return;
        }

        foreach ($this->coursesToInitialize as $courseData) {
            echo "\nInitializing course: '{$courseData['title']}'...\n";

            // Randomly assign a number of instructors (1 to 4)
            $currentCourseInstructorIDs = [];
            if (!empty($availableInstructorIDs)) {
                $numInstructorsToAssign = rand(1, min(4, count($availableInstructorIDs)));
                // array_rand có thể trả về một giá trị duy nhất nếu num=1, hoặc một mảng các keys nếu num > 1
                $randomInstructorKeys = (array) array_rand($availableInstructorIDs, $numInstructorsToAssign);

                foreach ($randomInstructorKeys as $key) {
                    $currentCourseInstructorIDs[] = $availableInstructorIDs[$key];
                }
                echo "  Assigned " . count($currentCourseInstructorIDs) . " instructor(s): " . implode(', ', $currentCourseInstructorIDs) . "\n";
            } else {
                echo "  WARNING: No instructors available in the database to assign for course '{$courseData['title']}'. This course will not be created.\n";
                continue; // Skip course creation if no instructor can be assigned
            }

            $currentCourseCategoryIDs = [];
            $validCategoriesFound = 0;
            // Kiểm tra category IDs từ dữ liệu khóa học với CATEGORIES_DATA đã định nghĩa
            if (!empty($availableCategoryIDs)) { // Check if there are categories in our constant map
                foreach ($courseData['categoryIds'] as $categoryId) {
                    if (isset(self::CATEGORIES_DATA[$categoryId])) { // Use isset for direct lookup
                        $currentCourseCategoryIDs[] = (string)$categoryId; // Ensure string for service call
                        echo "  Assigned category: '" . self::CATEGORIES_DATA[$categoryId] . "' (ID: $categoryId)\n";
                        $validCategoriesFound++;
                    } else {
                        echo "  WARNING: Category ID {$categoryId} for course '{$courseData['title']}' not found in internal category data. Skipping this category.\n";
                    }
                }
            } else {
                echo "  WARNING: No categories defined in internal constant. Courses cannot be assigned categories.\n";
            }

            if ($validCategoriesFound === 0 && !empty($courseData['categoryIds'])) {
                echo "  ERROR: Could not assign any valid categories for course '{$courseData['title']}' based on specified IDs. Skipping this course.\n";
                continue;
            }


            $courseResponse = $this->courseService->create_course(
                $courseData['title'],
                $courseData['description'],
                $courseData['price'],
                $currentCourseInstructorIDs,
                $currentCourseCategoryIDs,
                self::ADMIN_USER_ID_CREATED_BY
            );

            if (!$courseResponse->success) {
                echo "  Failed to create course '{$courseData['title']}': {$courseResponse->message}\n";
                continue;
            }

            $courseId = $courseResponse->data;
            echo "  Course '{$courseData['title']}' created successfully with ID: $courseId\n";

            if (!empty($courseData['requirements'])) {
                foreach ($courseData['requirements'] as $requirementText) {
                    $reqResponse = $this->courseRequirementService->create($courseId, $requirementText);
                    if ($reqResponse->success) {
                        echo "    Added requirement: '$requirementText'\n";
                    } else {
                        echo "    Failed to add requirement '$requirementText': {$reqResponse->message}\n";
                    }
                }
            }

            if (!empty($courseData['objectives'])) {
                foreach ($courseData['objectives'] as $objectiveText) {
                    $objResponse = $this->courseObjectiveService->create($courseId, $objectiveText);
                    if ($objResponse->success) {
                        echo "    Added objective: '$objectiveText'\n";
                    } else {
                        echo "    Failed to add objective '$objectiveText': {$objResponse->message}\n";
                    }
                }
            }

            if (!empty($courseData['chapters'])) {
                foreach ($courseData['chapters'] as $chapterData) {
                    $chapterResponse = $this->chapterService->create_chapter(
                        $courseId,
                        $chapterData['title'],
                        $chapterData['description'],
                        $chapterData['sortOrder']
                    );

                    if (!$chapterResponse->success || !isset($chapterResponse->data->chapterID)) {
                        echo "    Failed to create chapter '{$chapterData['title']}': {$chapterResponse->message}\n";
                        continue;
                    }

                    $chapterDto = $chapterResponse->data;
                    $chapterId = $chapterDto->chapterID;
                    echo "    Chapter '{$chapterData['title']}' created with ID: $chapterId\n";

                    if (!empty($chapterData['lessons'])) {
                        foreach ($chapterData['lessons'] as $lessonData) {
                            $lessonResponse = $this->lessonService->create_lesson(
                                "null", // content_type is not used for text lessons
                                $courseId,
                                $chapterId,
                                $lessonData['title'],
                                $lessonData['content'],
                                $lessonData['sortOrder']
                            );

                            if ($lessonResponse->success) {
                                echo "      Added lesson '{$lessonData['title']}' to chapter '$chapterId'.\n";
                            } else {
                                echo "      Failed to add lesson '{$lessonData['title']}': {$lessonResponse->message}\n";
                            }
                        }
                    }
                }
            }
        }

        echo "\nCourse initialization finished.\n";
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>\n";
$initializer = new CourseInitializer();
$initializer->initialize();
echo "</pre>\n";
