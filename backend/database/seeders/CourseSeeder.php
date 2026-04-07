<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseImage;
use App\Models\CourseLesson;
use App\Models\CourseObjective;
use App\Models\CourseRequirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if courses already exist
        if (Course::count() > 0) {
            $this->command->info('Courses already seeded. Skipping...');
            return;
        }

        // Get first instructor
        $instructor = \App\Models\Instructor::first();
        if (!$instructor) {
            $this->command->error('No instructor found. Run InstructorSeeder first.');
            return;
        }

        $courses = [
            [
                'title' => 'Lập trình Python Cơ bản đến Nâng cao',
                'description' => 'Khóa học toàn diện đưa bạn từ những khái niệm cơ bản nhất của Python đến các chủ đề nâng cao như lập trình hướng đối tượng, xử lý file, và làm việc với thư viện phổ biến.',
                'price' => 799000,
                'difficulty' => 'Beginner',
                'language' => 'vi',
                'category_slugs' => ['python', 'data-science'],
                'requirements' => [
                    'Không yêu cầu kinh nghiệm lập trình trước.',
                    'Máy tính cá nhân có kết nối internet.',
                    'Năng lực sử dụng máy tính ở mức cơ bản.',
                    'Sẵn sàng dành thời gian luyện tập ít nhất 2 giờ mỗi ngày.',
                    'Kiên nhẫn và ham học hỏi khi gặp lỗi.',
                ],
                'objectives' => [
                    'Nắm vững cú pháp cơ bản của Python.',
                    'Hiểu và áp dụng lập trình hướng đối tượng trong Python.',
                    'Thao tác với file và xử lý ngoại lệ.',
                    'Làm việc hiệu quả với các thư viện NumPy, Pandas.',
                    'Viết các chương trình Python có cấu trúc tốt và dễ bảo trì.',
                    'Phát triển được ứng dụng Python hoàn chỉnh từ đầu đến cuối.',
                    'Có nền tảng vững chắc để tiếp tục học các chủ đề chuyên sâu hơn.',
                ],
                'images' => [
                    ['image_path' => 'storage/images/courses/python-course-banner.jpg', 'caption' => 'Banner khóa học Python từ cơ bản đến nâng cao', 'is_primary' => true, 'sort_order' => 1],
                    ['image_path' => 'storage/images/courses/python-code-sample.jpg', 'caption' => 'Mã nguồn Python minh họa cho bài học', 'is_primary' => false, 'sort_order' => 2],
                    ['image_path' => 'storage/images/courses/python-projects.jpg', 'caption' => 'Các dự án thực tế được xây dựng trong khóa học', 'is_primary' => false, 'sort_order' => 3],
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Giới thiệu Python và Cài đặt Môi trường',
                        'description' => 'Làm quen với Python và thiết lập môi trường phát triển.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Bài 1.1: Python là gì? Lịch sử và ứng dụng',
                                'content' => 'Tổng quan về Python và các lĩnh vực ứng dụng của nó. Python là ngôn ngữ lập trình đa năng, dễ học, được sử dụng rộng rãi trong web development, data science, AI, automation.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Giới thiệu tổng quan về Python', 'url' => 'https://www.youtube.com/embed/kqtD5dpn9C8', 'duration' => 480, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Slide bài giảng chương 1', 'resource_path' => 'storage/resources/python/chapter1-intro.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.2: Cài đặt Python và Pip',
                                'content' => 'Hướng dẫn cài đặt Python trên các hệ điều hành Windows, macOS, Linux và quản lý gói Pip để cài đặt thư viện bên thứ ba.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Hướng dẫn cài đặt Python chi tiết', 'url' => 'https://www.youtube.com/embed/qWqFpX3PV8', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Hướng dẫn cài đặt Python từng bước', 'resource_path' => 'storage/resources/python/python-installation-guide.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.3: Sử dụng IDE (VS Code) và Jupyter Notebook',
                                'content' => 'Thiết lập và làm quen với các công cụ phát triển phổ biến như Visual Studio Code và Jupyter Notebook cho phân tích dữ liệu.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Thiết lập VS Code cho Python', 'url' => 'https://www.youtube.com/embed/08T7fOkN1Hg', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Cấu hình VS Code cho Python', 'resource_path' => 'storage/resources/python/vscode-python-config.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.4: Viết chương trình Python đầu tiên',
                                'content' => 'Chương trình "Hello World" và cách chạy mã Python. Hướng dẫn cách sử dụng Python interpreter và chạy script từ command line.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Chương trình Python đầu tiên', 'url' => 'https://www.youtube.com/embed/yCkSgnXZY9I', 'duration' => 360, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mã nguồn bài tập Hello World', 'resource_path' => 'storage/resources/python/lesson1-hello-world.py', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Cú pháp cơ bản và Kiểu dữ liệu',
                        'description' => 'Tìm hiểu các thành phần cơ bản của ngôn ngữ Python.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Bài 2.1: Biến, toán tử và biểu thức',
                                'content' => 'Khai báo biến, các loại toán tử số học, so sánh, logic và cách Python tự động xác định kiểu dữ liệu.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Biến và toán tử trong Python', 'url' => 'https://www.youtube.com/embed/_uJzJ1c7mBk', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Bảng tổng hợp toán tử Python', 'resource_path' => 'storage/resources/python/operators-cheatsheet.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.2: Kiểu dữ liệu số (int, float, complex)',
                                'content' => 'Làm việc với các loại số nguyên, số thực, số phức và các phép toán số học nâng cao như chia lấy dư, luỹ thừa.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Kiểu dữ liệu số trong Python', 'url' => 'https://www.youtube.com/embed/Es6Kjqn1DPc', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Bài tập thực hành kiểu số', 'resource_path' => 'storage/resources/python/numeric-types-exercises.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.3: Kiểu dữ liệu chuỗi (string) và các phương thức',
                                'content' => 'Tạo chuỗi, nối chuỗi, định dạng chuỗi với f-strings và các phương thức xử lý chuỗi như split, strip, replace, find.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Xử lý chuỗi trong Python', 'url' => 'https://www.youtube.com/embed/ZM1R4H8BLPs', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Cheatsheet phương thức chuỗi', 'resource_path' => 'storage/resources/python/string-methods-cheatsheet.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.4: Kiểu dữ liệu Boolean và None',
                                'content' => 'Sử dụng giá trị True/False, các phép toán logic AND, OR, NOT và khái niệm None để biểu diễn giá trị rỗng.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Boolean và None trong Python', 'url' => 'https://www.youtube.com/embed/Y1cuRDrXhlQ', 'duration' => 420, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Bài tập về Boolean', 'resource_path' => 'storage/resources/python/boolean-exercises.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.5: Kiểu dữ liệu List, Tuple, Set, Dictionary',
                                'content' => 'Tìm hiểu các cấu trúc dữ liệu phức tạp trong Python: danh sách (list), bộ giá trị (tuple), tập hợp (set), và từ điển (dictionary).',
                                'sort_order' => 5,
                                'videos' => [
                                    ['title' => 'Cấu trúc dữ liệu trong Python', 'url' => 'https://www.youtube.com/embed/WPyN2yFSL-A', 'duration' => 900, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'So sánh List vs Tuple vs Set vs Dict', 'resource_path' => 'storage/resources/python/data-structures-comparison.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 3: Lập trình hướng đối tượng (OOP)',
                        'description' => 'Tìm hiểu các nguyên lý lập trình hướng đối tượng trong Python.',
                        'sort_order' => 3,
                        'lessons' => [
                            [
                                'title' => 'Bài 3.1: Lớp (Class) và Đối tượng (Object)',
                                'content' => 'Khái niệm về lớp, đối tượng, thuộc tính và phương thức. Cách định nghĩa class trong Python và tạo instance.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Giới thiệu về Class và Object', 'url' => 'https://www.youtube.com/embed/8D3iKKJjO1s', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu định nghĩa Class cơ bản', 'resource_path' => 'storage/resources/python/class-template.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.2: Kế thừa (Inheritance) và Đa hình (Polymorphism)',
                                'content' => 'Cách tạo class kế thừa từ class khác, ghi đè phương thức, và khái niệm đa hình trong lập trình hướng đối tượng.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Kế thừa trong Python OOP', 'url' => 'https://www.youtube.com/embed/RSrlh2qK1e8', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Ví dụ về kế thừa class', 'resource_path' => 'storage/resources/python/inheritance-examples.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.3: Đóng gói (Encapsulation) và getter/setter',
                                'content' => 'Bảo vệ dữ liệu với các thuộc tính private, protected. Sử dụng property decorator để tạo getter/setter.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Đóng gói dữ liệu trong Python', 'url' => 'https://www.youtube.com/embed/YBQTCF-qwP0', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Ví dụ về Encapsulation', 'resource_path' => 'storage/resources/python/encapsulation-examples.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.4: Abstract Class và Interface',
                                'content' => 'Sử dụng abstract base class (ABC) để tạo class trừu tượng, định nghĩa interface cho các class khác implement.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Abstract Class trong Python', 'url' => 'https://www.youtube.com/embed/xG5_Nj-2tP8', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu Abstract Class', 'resource_path' => 'storage/resources/python/abstract-class-template.py', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 4: Làm việc với thư viện và API',
                        'description' => 'Học cách sử dụng các thư viện phổ biến và tích hợp API.',
                        'sort_order' => 4,
                        'lessons' => [
                            [
                                'title' => 'Bài 4.1: Giới thiệu NumPy cho tính toán số',
                                'content' => 'NumPy là thư viện nền tảng cho tính toán số trong Python. Học cách tạo mảng, các phép toán vector, matrix.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'NumPy cơ bản', 'url' => 'https://www.youtube.com/embed/Q8hAcHHT5B4', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'NumPy cheatsheet', 'resource_path' => 'storage/resources/python/numpy-cheatsheet.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.2: Pandas cho phân tích dữ liệu',
                                'content' => 'Pandas là thư viện mạnh mẽ để thao tác với dữ liệu dạng bảng. Học DataFrame, Series, đọc/ghi file CSV, Excel.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Pandas cơ bản cho phân tích dữ liệu', 'url' => 'https://www.youtube.com/embed/yN1wGqIiO1U', 'duration' => 960, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Bài tập thực hành Pandas', 'resource_path' => 'storage/resources/python/pandas-exercises.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.3: Gọi API REST với thư viện Requests',
                                'content' => 'Cách gọi HTTP API từ Python, xử lý JSON response, headers, authentication và error handling.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Gọi API REST với Python', 'url' => 'https://www.youtube.com/embed/W4GsGbhQv4k', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu code gọi API', 'resource_path' => 'storage/resources/python/api-request-template.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.4: Xử lý file JSON và CSV',
                                'content' => 'Đọc, ghi file JSON và CSV. Parse dữ liệu từ các nguồn bên ngoài và chuyển đổi giữa các định dạng.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Xử lý file JSON và CSV', 'url' => 'https://www.youtube.com/embed/yCkSgnXZY9I', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Dataset mẫu CSV', 'resource_path' => 'storage/resources/python/sample-dataset.csv', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 5: Dự án thực tế',
                        'description' => 'Áp dụng kiến thức đã học để xây dựng các dự án thực tế.',
                        'sort_order' => 5,
                        'lessons' => [
                            [
                                'title' => 'Bài 5.1: Xây dựng ứng dụng Todo List với OOP',
                                'content' => 'Phát triển ứng dụng quản lý công việc sử dụng các nguyên lý OOP đã học. Quản lý task với class, kế thừa.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Xây dựng Todo App với Python OOP', 'url' => 'https://www.youtube.com/embed/4t7j6T7JGZr', 'duration' => 1200, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mã nguồn Todo App hoàn chỉnh', 'resource_path' => 'storage/resources/python/todo-app-source.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.2: Phân tích dữ liệu với Pandas và NumPy',
                                'content' => 'Thực hành phân tích dataset thực tế: đọc dữ liệu, làm sạch, tính toán thống kê và trực quan hóa cơ bản.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Phân tích dữ liệu thực tế với Pandas', 'url' => 'https://www.youtube.com/embed/KqO0pjQLiH0', 'duration' => 1080, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Dataset phân tích doanh thu', 'resource_path' => 'storage/resources/python/sales-data.csv', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.3: Tạo chatbot kết nối API thời tiết',
                                'content' => 'Xây dựng ứng dụng chatbot đơn giản gọi API thời tiết, xử lý response và hiển thị thông tin cho người dùng.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Xây dựng Weather Chatbot với Python', 'url' => 'https://www.youtube.com/embed/q_wqFpX3PV8', 'duration' => 900, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mã nguồn Weather Chatbot', 'resource_path' => 'storage/resources/python/weather-chatbot.py', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'ReactJS - Xây dựng Giao diện Người dùng Hiện đại',
                'description' => 'Học cách xây dựng các ứng dụng web hiện đại với ReactJS, từ cơ bản đến nâng cao bao gồm Hooks, Context API, và React Router.',
                'price' => 899000,
                'difficulty' => 'Intermediate',
                'language' => 'vi',
                'category_slugs' => ['reactjs', 'web-development'],
                'requirements' => [
                    'Kiến thức cơ bản về HTML, CSS và JavaScript.',
                    'Hiểu về ES6+ syntax.',
                    'Biết sử dụng terminal/command line cơ bản.',
                    'Máy tính có RAM tối thiểu 4GB để chạy môi trường phát triển.',
                    'Đã có kiến thức về DOM và sự kiện trình duyệt.',
                ],
                'objectives' => [
                    'Hiểu rõ về JSX và Virtual DOM.',
                    'Thành thạo React Hooks (useState, useEffect, useContext, etc.).',
                    'Xây dựng Single Page Application với React Router.',
                    'Quản lý state với Context API.',
                    'Tích hợp API và xử lý bất đồng bộ.',
                    'Tối ưu performance với React.memo và useMemo.',
                    'Triển khai ứng dụng React lên production.',
                ],
                'images' => [
                    ['image_path' => 'storage/images/courses/reactjs-course-banner.jpg', 'caption' => 'Banner khóa học ReactJS hiện đại', 'is_primary' => true, 'sort_order' => 1],
                    ['image_path' => 'storage/images/courses/reactjs-components.jpg', 'caption' => 'Kiến trúc Component trong React', 'is_primary' => false, 'sort_order' => 2],
                    ['image_path' => 'storage/images/courses/reactjs-projects.jpg', 'caption' => 'Các dự án thực tế xây dựng trong khóa', 'is_primary' => false, 'sort_order' => 3],
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Nhập môn ReactJS',
                        'description' => 'Giới thiệu về React và thiết lập môi trường.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Bài 1.1: React là gì? Tại sao sử dụng React?',
                                'content' => 'Giới thiệu tổng quan về React, lịch sử phát triển, và tại sao React trở thành framework phổ biến nhất cho frontend development.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Tổng quan về ReactJS', 'url' => 'https://www.youtube.com/embed/Ke90Tje7VS0', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Slide giới thiệu React', 'resource_path' => 'storage/resources/reactjs/chapter1-intro.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.2: Cài đặt Create React App và Vite',
                                'content' => 'Thiết lập môi trường phát triển React với Create React App và Vite. So sánh ưu nhược điểm của từng công cụ.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Thiết lập dự án React với Vite', 'url' => 'https://www.youtube.com/embed/9Srj_yKBS34', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Checklist cài đặt môi trường React', 'resource_path' => 'storage/resources/reactjs/react-setup-checklist.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.3: JSX - JavaScript XML',
                                'content' => 'Hiểu về JSX - cú pháp mở rộng của JavaScript cho phép viết HTML-like code trong React. Cách JSX được biên dịch sang JavaScript.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'JSX chi tiết', 'url' => 'https://www.youtube.com/embed/2Ohjk4RH8d4', 'duration' => 480, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'JSX vs HTML comparison', 'resource_path' => 'storage/resources/reactjs/jsx-vs-html.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.4: Virtual DOM và Rendering',
                                'content' => 'Tìm hiểu cơ chế Virtual DOM của React và cách React tối ưu hóa việc cập nhật giao diện thông qua diffing algorithm.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Virtual DOM hoạt động thế nào', 'url' => 'https://www.youtube.com/embed/7f9f6cC1n6Y', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Sơ đồ Virtual DOM', 'resource_path' => 'storage/resources/reactjs/virtual-dom-diagram.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Components và Props',
                        'description' => 'Học về Components và cách truyền dữ liệu với Props.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Bài 2.1: Functional Components',
                                'content' => 'Tạo và sử dụng functional components trong React. Cách định nghĩa component, return JSX, và sử dụng component trong ứng dụng.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Functional Components trong React', 'url' => 'https://www.youtube.com/embed/9V5aLXjNNc4', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu Functional Component', 'resource_path' => 'storage/resources/reactjs/functional-component-template.jsx', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.2: Props và truyền dữ liệu',
                                'content' => 'Cách truyền dữ liệu từ component cha xuống component con thông qua props. Default props và prop types.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Props trong React', 'url' => 'https://www.youtube.com/embed/Y7L9_UsDlBM', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Ví dụ về Props', 'resource_path' => 'storage/resources/reactjs/props-examples.jsx', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.3: Component có State và Lifecycle',
                                'content' => 'Sử dụng useState hook để quản lý state trong functional component. Giới thiệu về lifecycle của React component.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'State và Lifecycle trong React', 'url' => 'https://www.youtube.com/embed/Y7L9_UsDlBM', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Bài tập thực hành State', 'resource_path' => 'storage/resources/reactjs/state-exercises.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 3: React Hooks nâng cao',
                        'description' => 'Nắm vững các hooks nâng cao của React.',
                        'sort_order' => 3,
                        'lessons' => [
                            [
                                'title' => 'Bài 3.1: useEffect và Side Effects',
                                'content' => 'useEffect hook để xử lý side effects như gọi API, subscription, thao tác với DOM. Cleanup function và dependency array.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'useEffect hook chi tiết', 'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Cheatsheet useEffect', 'resource_path' => 'storage/resources/reactjs/useEffect-cheatsheet.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.2: useContext và Context API',
                                'content' => 'Truyền dữ liệu qua nhiều tầng component mà không cần prop drilling bằng cách sử dụng Context API và useContext hook.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Context API trong React', 'url' => 'https://www.youtube.com/embed/6BSN7RI2p9Q', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu Context Provider', 'resource_path' => 'storage/resources/reactjs/context-provider-template.jsx', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.3: useReducer cho State phức tạp',
                                'content' => 'Quản lý state phức tạp với useReducer thay vì useState. Reducer pattern và cách dispatch actions.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'useReducer hook', 'url' => 'https://www.youtube.com/embed/n0tHS5FdVcY', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Ví dụ useReducer', 'resource_path' => 'storage/resources/reactjs/useReducer-example.jsx', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.4: Custom Hooks',
                                'content' => 'Tạo custom hooks để tái sử dụng logic giữa các component. Cách extract logic từ component thành custom hook.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Tạo Custom Hooks', 'url' => 'https://www.youtube.com/embed/1NIHq-NKg3Y', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Thư viện Custom Hooks mẫu', 'resource_path' => 'storage/resources/reactjs/custom-hooks-library.js', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 4: Quản lý state với Context API & Redux',
                        'description' => 'Học cách quản lý state toàn cục cho ứng dụng React.',
                        'sort_order' => 4,
                        'lessons' => [
                            [
                                'title' => 'Bài 4.1: Giới thiệu Redux và Redux Toolkit',
                                'content' => 'Tổng quan về Redux, kiến trúc unidirectional data flow, và cách Redux Toolkit đơn giản hóa việc sử dụng Redux.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Redux Toolkit cho người mới', 'url' => 'https://www.youtube.com/embed/m9PIoMjy4j4', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Kiến trúc Redux flow', 'resource_path' => 'storage/resources/reactjs/redux-flow-diagram.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.2: Store, Actions, Reducers',
                                'content' => 'Tạo Redux store, định nghĩa actions và reducers. Cách combine reducers và kết nối component với store.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Store, Actions, Reducers trong Redux', 'url' => 'https://www.youtube.com/embed/o8J3tJ1y0K4', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu Redux store', 'resource_path' => 'storage/resources/reactjs/redux-store-template.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.3: Redux Thunk cho Async Actions',
                                'content' => 'Xử lý asynchronous operations với Redux Thunk. Gọi API trong action creators và quản lý loading states.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Redux Thunk async actions', 'url' => 'https://www.youtube.com/embed/pI8K4J5R0L6', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Ví dụ async action', 'resource_path' => 'storage/resources/reactjs/async-action-example.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.4: Kết hợp Context API và Redux',
                                'content' => 'Khi nào nên dùng Context API, khi nào nên dùng Redux. Cách kết hợp cả hai trong cùng ứng dụng.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Context vs Redux', 'url' => 'https://www.youtube.com/embed/HI0J0K0L0M', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Decision guide Context vs Redux', 'resource_path' => 'storage/resources/reactjs/context-vs-redux-guide.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 5: Deployment và Performance',
                        'description' => 'Triển khai ứng dụng React và tối ưu hiệu suất.',
                        'sort_order' => 5,
                        'lessons' => [
                            [
                                'title' => 'Bài 5.1: React Router cho SPA',
                                'content' => 'Thiết lập React Router để xây dựng Single Page Application với nhiều trang, nested routes, và navigation.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'React Router v6', 'url' => 'https://www.youtube.com/embed/FLo8df8BWhk', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu React Router setup', 'resource_path' => 'storage/resources/reactjs/react-router-setup.jsx', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.2: Tối ưu Performance với useMemo và React.memo',
                                'content' => 'Tránh re-render không cần thiết với React.memo, useMemo, và useCallback. Performance optimization techniques.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'React Performance Optimization', 'url' => 'https://www.youtube.com/embed/GIqL0L0S0K4', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Performance checklist', 'resource_path' => 'storage/resources/reactjs/performance-checklist.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.3: Triển khai ứng dụng lên Vercel và Netlify',
                                'content' => 'Deploy ứng dụng React lên các nền tảng hosting phổ biến. Cấu hình environment variables và custom domain.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Deploy React lên Vercel', 'url' => 'https://www.youtube.com/embed/HI0J0K0L0M', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Deployment guide', 'resource_path' => 'storage/resources/reactjs/deployment-guide.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Machine Learning Cơ bản',
                'description' => 'Khóa học nhập môn Machine Learning với Python, bao gồm các thuật toán cơ bản và ứng dụng thực tế.',
                'price' => 999000,
                'difficulty' => 'Intermediate',
                'language' => 'vi',
                'category_slugs' => ['machine-learning', 'data-science'],
                'requirements' => [
                    'Kiến thức Python cơ bản.',
                    'Hiểu biết cơ bản về thống kê và đại số tuyến tính.',
                    'Đại số tuyến tính: ma trận, vector, phép nhân ma trận.',
                    'Xác suất cơ bản: phân phối, kỳ vọng, phương sai.',
                    'Biết sử dụng Jupyter Notebook hoặc Google Colab.',
                ],
                'objectives' => [
                    'Hiểu các khái niệm cơ bản của Machine Learning.',
                    'Áp dụng các thuật toán Supervised Learning.',
                    'Làm việc với Scikit-learn.',
                    'Đánh giá và tối ưu model.',
                    'Xây dựng được mô hình ML hoàn chỉnh từ data preprocessing đến deployment.',
                    'Đọc và hiểu các nghiên cứu ML cơ bản.',
                ],
                'images' => [
                    ['image_path' => 'storage/images/courses/ml-course-banner.jpg', 'caption' => 'Banner khóa học Machine Learning', 'is_primary' => true, 'sort_order' => 1],
                    ['image_path' => 'storage/images/courses/ml-algorithms.jpg', 'caption' => 'Các thuật toán Machine Learning', 'is_primary' => false, 'sort_order' => 2],
                    ['image_path' => 'storage/images/courses/ml-projects.jpg', 'caption' => 'Dự án thực tế với Machine Learning', 'is_primary' => false, 'sort_order' => 3],
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Giới thiệu Machine Learning',
                        'description' => 'Tổng quan về ML và các khái niệm cơ bản.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Bài 1.1: Machine Learning là gì?',
                                'content' => 'Giới thiệu tổng quan về Machine Learning, sự khác biệt giữa ML và lập trình truyền thống, và các ứng dụng thực tế.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Giới thiệu về Machine Learning', 'url' => 'https://www.youtube.com/embed/GwIo3R6q6v8', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Slide bài giảng ML tổng quan', 'resource_path' => 'storage/resources/ml/chapter1-intro.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.2: Các loại Machine Learning',
                                'content' => 'Phân biệt Supervised Learning, Unsupervised Learning, và Reinforcement Learning. Ví dụ về từng loại.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Các loại Machine Learning', 'url' => 'https://www.youtube.com/embed/h0e1D5K5M9s', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'So sánh các loại ML', 'resource_path' => 'storage/resources/ml/ml-types-comparison.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.3: Machine Learning Workflow',
                                'content' => 'Quy trình làm việc ML từ thu thập dữ liệu, tiền xử lý, huấn luyện model, đánh giá đến deployment.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'ML Workflow chi tiết', 'url' => 'https://www.youtube.com/embed/4N02K4J7G_Q', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'ML Workflow checklist', 'resource_path' => 'storage/resources/ml/ml-workflow-checklist.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.4: Công cụ và Thư viện ML',
                                'content' => 'Giới thiệu các thư viện Python phổ biến: Scikit-learn, TensorFlow, PyTorch, Pandas, NumPy.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Tổng quan thư viện ML', 'url' => 'https://www.youtube.com/embed/GwIo3R6q6v8', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Hướng dẫn cài đặt thư viện ML', 'resource_path' => 'storage/resources/ml/ml-libraries-setup.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Supervised Learning',
                        'description' => 'Học các thuật toán Supervised Learning.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Bài 2.1: Linear Regression',
                                'content' => 'Hồi quy tuyến tính cho dự đoán giá trị liên tục. Cách hoạt động, gradient descent, và Regularization.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Linear Regression chi tiết', 'url' => 'https://www.youtube.com/embed/h0e1D5K5M9s', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Linear Regression cheat sheet', 'resource_path' => 'storage/resources/ml/linear-regression-cheatsheet.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.2: Logistic Regression',
                                'content' => 'Hồi quy logistic cho bài toán phân loại nhị phân. Hàm sigmoid và Decision boundary.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Logistic Regression', 'url' => 'https://www.youtube.com/embed/4N02K4J7G_Q', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Bài tập Logistic Regression', 'resource_path' => 'storage/resources/ml/logistic-regression-exercises.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.3: Decision Tree và Random Forest',
                                'content' => 'Thuật toán Decision Tree cho classification và regression. Random Forest để cải thiện accuracy.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Decision Tree và Random Forest', 'url' => 'https://www.youtube.com/embed/7V4J6T4G_Q', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mẫu code Decision Tree', 'resource_path' => 'storage/resources/ml/decision-tree-template.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.4: Support Vector Machine (SVM)',
                                'content' => 'Thuật toán SVM cho phân loại. Khái niệm hyperplane, kernel trick, và cách handle non-linear data.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'SVM chi tiết', 'url' => 'https://www.youtube.com/embed/S4J6Z5K6J_Q', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'SVM kernel functions', 'resource_path' => 'storage/resources/ml/svm-kernels.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.5: K-Nearest Neighbors (KNN)',
                                'content' => 'Thuật toán KNN đơn giản nhưng hiệu quả. Cách chọn K value và đo khoảng cách.',
                                'sort_order' => 5,
                                'videos' => [
                                    ['title' => 'KNN Algorithm', 'url' => 'https://www.youtube.com/embed/9J4_Z5K6Q', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'KNN implementation', 'resource_path' => 'storage/resources/ml/knn-template.py', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 3: Unsupervised Learning',
                        'description' => 'Học các thuật toán không cần nhãn dữ liệu.',
                        'sort_order' => 3,
                        'lessons' => [
                            [
                                'title' => 'Bài 3.1: K-Means Clustering',
                                'content' => 'Thuật toán K-Means để phân cụm dữ liệu. Cách chọn số cụm K và đánh giá chất lượng phân cụm.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'K-Means Clustering', 'url' => 'https://www.youtube.com/embed/1J4J6T5L6Q', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'K-Means code examples', 'resource_path' => 'storage/resources/ml/kmeans-examples.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.2: Hierarchical Clustering',
                                'content' => 'Phân cụm phân cấp với Agglomerative và Divisive clustering. Dendrogram và linkage methods.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Hierarchical Clustering', 'url' => 'https://www.youtube.com/embed/2J7K5K6L6Q', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Hierarchical clustering guide', 'resource_path' => 'storage/resources/ml/hierarchical-clustering.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.3: Principal Component Analysis (PCA)',
                                'content' => 'PCA để giảm chiều dữ liệu, loại bỏ multicollinearity, và trực quan hóa dữ liệu nhiều chiều.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'PCA chi tiết', 'url' => 'https://www.youtube.com/embed/3J4K6L6M6Q', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'PCA implementation', 'resource_path' => 'storage/resources/ml/pca-implementation.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.4: Dimensionality Reduction techniques',
                                'content' => 'Các phương pháp giảm chiều dữ liệu: t-SNE, LDA. So sánh và cách chọn phương pháp phù hợp.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Dimensionality Reduction', 'url' => 'https://www.youtube.com/embed/4K4J6M6N6Q', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 't-SNE vs PCA comparison', 'resource_path' => 'storage/resources/ml/tsne-vs-pca.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 4: Deep Learning với TensorFlow',
                        'description' => 'Giới thiệu Deep Learning và TensorFlow.',
                        'sort_order' => 4,
                        'lessons' => [
                            [
                                'title' => 'Bài 4.1: Neural Network cơ bản',
                                'content' => 'Cấu trúc Neural Network: input layer, hidden layers, output layer. Activation functions và backpropagation.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Neural Network fundamentals', 'url' => 'https://www.youtube.com/embed/5J4K6M6O6Q', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Neural network architecture', 'resource_path' => 'storage/resources/ml/neural-network-architecture.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.2: TensorFlow và Keras',
                                'content' => 'Sử dụng TensorFlow/Keras để xây dựng Neural Network. Sequential API và Functional API.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'TensorFlow/Keras tutorial', 'url' => 'https://www.youtube.com/embed/6J4K6M6P6Q', 'duration' => 900, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Keras quickstart guide', 'resource_path' => 'storage/resources/ml/keras-quickstart.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.3: Convolutional Neural Network (CNN)',
                                'content' => 'CNN cho image classification. Convolution, pooling, và cách xây dựng CNN với TensorFlow.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'CNN chi tiết', 'url' => 'https://www.youtube.com/embed/7J4K6M6Q6Q', 'duration' => 960, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'CNN architecture examples', 'resource_path' => 'storage/resources/ml/cnn-architectures.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.4: Transfer Learning',
                                'content' => 'Sử dụng pretrained models (VGG, ResNet, MobileNet) cho bài toán mới. Fine-tuning và feature extraction.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Transfer Learning với TensorFlow', 'url' => 'https://www.youtube.com/embed/8J4K6M6R6Q', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Transfer learning guide', 'resource_path' => 'storage/resources/ml/transfer-learning-guide.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 5: Dự án thực tế',
                        'description' => 'Áp dụng kiến thức vào các dự án thực tế.',
                        'sort_order' => 5,
                        'lessons' => [
                            [
                                'title' => 'Bài 5.1: Xây dựng mô hình dự đoán giá nhà',
                                'content' => 'Dự án hoàn chỉnh: thu thập dữ liệu giá nhà, tiền xử lý, huấn luyện Linear Regression, đánh giá và deployment.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'House Price Prediction Project', 'url' => 'https://www.youtube.com/embed/9J4K6M6S6Q', 'duration' => 1200, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'House price dataset', 'resource_path' => 'storage/resources/ml/house-prices.csv', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.2: Phân loại hình ảnh với CNN',
                                'content' => 'Xây dựng mô hình CNN để phân loại hình ảnh (ví dụ: cats vs dogs). Data augmentation và model evaluation.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Image Classification Project', 'url' => 'https://www.youtube.com/embed/0J4K6M6T6Q', 'duration' => 1080, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Image classification code', 'resource_path' => 'storage/resources/ml/image-classification.py', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.3: Customer Segmentation',
                                'content' => 'Ứng dụng K-Means và PCA để phân khúc khách hàng. Phân tích kết quả và đề xuất chiến lược marketing.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Customer Segmentation Project', 'url' => 'https://www.youtube.com/embed/1J4K6M6U6Q', 'duration' => 960, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Customer dataset', 'resource_path' => 'storage/resources/ml/customer-data.csv', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Node.js - Backend Development với JavaScript',
                'description' => 'Học cách xây dựng REST API và ứng dụng backend với Node.js, Express và MongoDB.',
                'price' => 849000,
                'difficulty' => 'Intermediate',
                'language' => 'vi',
                'category_slugs' => ['nodejs', 'web-development'],
                'requirements' => [
                    'Kiến thức JavaScript cơ bản.',
                    'Hiểu về HTTP và REST API.',
                    'Biết sử dụng command line/terminal.',
                    'Máy tính có RAM tối thiểu 4GB.',
                    'Đã làm quen với khái niệm async/await.',
                ],
                'objectives' => [
                    'Hiểu về Node.js runtime và event loop.',
                    'Xây dựng REST API với Express.',
                    'Làm việc với MongoDB và Mongoose.',
                    'Authentication và Authorization với JWT.',
                    'Viết unit tests và integration tests.',
                    'Triển khai ứng dụng Node.js lên server.',
                ],
                'images' => [
                    ['image_path' => 'storage/images/courses/nodejs-course-banner.jpg', 'caption' => 'Banner khóa học Node.js Backend', 'is_primary' => true, 'sort_order' => 1],
                    ['image_path' => 'storage/images/courses/nodejs-architecture.jpg', 'caption' => 'Kiến trúc ứng dụng Node.js', 'is_primary' => false, 'sort_order' => 2],
                    ['image_path' => 'storage/images/courses/nodejs-projects.jpg', 'caption' => 'Dự án REST API thực tế', 'is_primary' => false, 'sort_order' => 3],
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Nhập môn Node.js',
                        'description' => 'Giới thiệu Node.js và thiết lập môi trường.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Bài 1.1: Node.js là gì?',
                                'content' => 'Giới thiệu tổng quan về Node.js, runtime environment, và tại sao Node.js phù hợp cho backend development.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Giới thiệu Node.js', 'url' => 'https://www.youtube.com/embed/w-9cofGgox4', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Slide giới thiệu Node.js', 'resource_path' => 'storage/resources/nodejs/chapter1-intro.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.2: Module System và NPM',
                                'content' => 'Làm việc với Node.js modules (CommonJS, ES Modules), sử dụng NPM để quản lý dependencies.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Node.js Modules và NPM', 'url' => 'https://www.youtube.com/embed/OJ8N5TFe2vA', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'package.json mẫu', 'resource_path' => 'storage/resources/nodejs/package-sample.json', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.3: Event Loop và Asynchronous Programming',
                                'content' => 'Hiểu về Event Loop trong Node.js, callback pattern, Promise, và async/await cho xử lý bất đồng bộ.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Event Loop trong Node.js', 'url' => 'https://www.youtube.com/embed/8On_XJ2VzqM', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Async patterns cheatsheet', 'resource_path' => 'storage/resources/nodejs/async-patterns.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.4: Xây dựng HTTP Server cơ bản',
                                'content' => 'Sử dụng Node.js http module để tạo web server, xử lý request và response. Giới thiệu về Express framework.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'HTTP Server với Node.js', 'url' => 'https://www.youtube.com/embed/9J9N5TFe2vA', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'HTTP server template', 'resource_path' => 'storage/resources/nodejs/http-server-template.js', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Express Framework',
                        'description' => 'Xây dựng web server với Express.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Bài 2.1: Routing và Middleware',
                                'content' => 'Xử lý request với Express router, tạo middleware functions, và xâu chuỗi các middleware.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Express Routing và Middleware', 'url' => 'https://www.youtube.com/embed/lY6icfhap2o', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Express middleware patterns', 'resource_path' => 'storage/resources/nodejs/middleware-patterns.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.2: Xây dựng REST API',
                                'content' => 'Tạo CRUD API endpoints cho resource. Request body parsing, validation, và error handling.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'REST API với Express', 'url' => 'https://www.youtube.com/embed/6W00K2rlH04', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'REST API routes template', 'resource_path' => 'storage/resources/nodejs/rest-api-routes.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.3: Error Handling và Validation',
                                'content' => 'Xử lý lỗi tập trung với Express error handler, validation dữ liệu với express-validator hoặc Joi.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Error Handling trong Express', 'url' => 'https://www.youtube.com/embed/7W00K2rlH04', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Validation examples', 'resource_path' => 'storage/resources/nodejs/validation-examples.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.4: CORS, Helmet và Security cơ bản',
                                'content' => 'Bảo mật ứng dụng Express với CORS, Helmet, rate limiting và các best practices.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Express Security', 'url' => 'https://www.youtube.com/embed/7W00K2rlH05', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Security checklist', 'resource_path' => 'storage/resources/nodejs/security-checklist.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 3: MongoDB và Mongoose ODM',
                        'description' => 'Làm việc với MongoDB và Mongoose.',
                        'sort_order' => 3,
                        'lessons' => [
                            [
                                'title' => 'Bài 3.1: MongoDB cơ bản',
                                'content' => 'Giới thiệu MongoDB, document-based database, so sánh với SQL, MongoDB Atlas và cách kết nối.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'MongoDB cho người mới', 'url' => 'https://www.youtube.com/embed/HG1tWGmM6vE', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'MongoDB cheatsheet', 'resource_path' => 'storage/resources/nodejs/mongodb-cheatsheet.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.2: Mongoose ODM',
                                'content' => 'Sử dụng Mongoose để định nghĩa schemas và models, kết nối MongoDB, và thao tác với database.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Mongoose ODM tutorial', 'url' => 'https://www.youtube.com/embed/JG0C5OH5Jz0', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mongoose model template', 'resource_path' => 'storage/resources/nodejs/mongoose-model-template.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.3: Querying và Aggregation',
                                'content' => 'Các phương pháp truy vấn MongoDB: find, findOne, update, delete. Giới thiệu Aggregation pipeline.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'MongoDB Queries', 'url' => 'https://www.youtube.com/embed/KH0NtGz0lAE', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Query examples', 'resource_path' => 'storage/resources/nodejs/query-examples.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.4: Relationships và Populate',
                                'content' => 'Quản lý relationships trong MongoDB với referenced documents và embedded documents. Sử dụng populate().',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'MongoDB Relationships', 'url' => 'https://www.youtube.com/embed/LH2J1f5R1mE', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Mongoose relationships guide', 'resource_path' => 'storage/resources/nodejs/relationships-guide.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 4: Authentication với JWT',
                        'description' => 'Xây dựng hệ thống authentication và authorization.',
                        'sort_order' => 4,
                        'lessons' => [
                            [
                                'title' => 'Bài 4.1: JSON Web Token (JWT)',
                                'content' => 'Giới thiệu về JWT, cấu trúc token (header, payload, signature), và cách JWT hoạt động.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'JWT Authentication', 'url' => 'https://www.youtube.com/embed/m9PIoMjy4j4', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'JWT flow diagram', 'resource_path' => 'storage/resources/nodejs/jwt-flow.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.2: Registration và Login',
                                'content' => 'Xây dựng API đăng ký và đăng nhập với password hashing (bcrypt), tạo và verify JWT tokens.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Auth API với JWT', 'url' => 'https://www.youtube.com/embed/n0tHS5FdVcY', 'duration' => 900, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Auth controller template', 'resource_path' => 'storage/resources/nodejs/auth-controller.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.3: Authorization và Middleware',
                                'content' => 'Tạo authentication middleware để bảo vệ routes, kiểm tra token và extract user information.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Auth Middleware', 'url' => 'https://www.youtube.com/embed/o8J3tJ1y0K4', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Auth middleware template', 'resource_path' => 'storage/resources/nodejs/auth-middleware.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.4: Refresh Tokens và Logout',
                                'content' => 'Implementing refresh tokens, token rotation, và proper logout với token invalidation.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Refresh Token Implementation', 'url' => 'https://www.youtube.com/embed/o8J3tJ1y0K4', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Refresh token flow', 'resource_path' => 'storage/resources/nodejs/refresh-token-flow.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 5: Testing và Deployment',
                        'description' => 'Viết tests và triển khai ứng dụng Node.js.',
                        'sort_order' => 5,
                        'lessons' => [
                            [
                                'title' => 'Bài 5.1: Unit Testing với Jest',
                                'content' => 'Viết unit tests cho functions và modules với Jest. Mocking dependencies và assertions.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Jest Testing Tutorial', 'url' => 'https://www.youtube.com/embed/FLo8df8BWhk', 'duration' => 840, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Jest testing patterns', 'resource_path' => 'storage/resources/nodejs/jest-patterns.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.2: Integration Testing',
                                'content' => 'Viết integration tests cho API endpoints với Supertest. Test database operations thực sự.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Integration Testing với Supertest', 'url' => 'https://www.youtube.com/embed/GIqL0L0S0J4', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Integration test examples', 'resource_path' => 'storage/resources/nodejs/integration-tests.js', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.3: Deploy lên AWS EC2 và Docker',
                                'content' => 'Triển khai ứng dụng Node.js lên AWS EC2, sử dụng Docker và Docker Compose để đóng gói và chạy ứng dụng.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Deploy Node.js với Docker', 'url' => 'https://www.youtube.com/embed/HI0J0K0L0L', 'duration' => 960, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Dockerfile template', 'resource_path' => 'storage/resources/nodejs/Dockerfile', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'UI/UX Design - Thiết kế Trải nghiệm Người dùng',
                'description' => 'Học nguyên lý thiết kế UI/UX và thực hành với Figma để tạo ra các sản phẩm số có trải nghiệm vượt trội.',
                'price' => 749000,
                'difficulty' => 'Beginner',
                'language' => 'vi',
                'category_slugs' => ['design'],
                'requirements' => [
                    'Không yêu cầu kinh nghiệm thiết kế trước.',
                    'Máy tính có cài đặt Figma (miễn phí).',
                    'Máy tính có RAM tối thiểu 4GB.',
                    'Sẵn sàng quan sát và phân tích các sản phẩm số.',
                    'Tư duy sáng tạo và thích khám phá.',
                ],
                'objectives' => [
                    'Hiểu nguyên lý Design Thinking.',
                    'Thực hành User Research.',
                    'Thiết kế Wireframe và Prototype.',
                    'Thành thạo Figma.',
                    'Xây dựng Design System hoàn chỉnh.',
                    'Thực hiện Usability Testing và iterate thiết kế.',
                ],
                'images' => [
                    ['image_path' => 'storage/images/courses/uiux-course-banner.jpg', 'caption' => 'Banner khóa học UI/UX Design', 'is_primary' => true, 'sort_order' => 1],
                    ['image_path' => 'storage/images/courses/uiux-wireframes.jpg', 'caption' => 'Wireframes và Prototypes', 'is_primary' => false, 'sort_order' => 2],
                    ['image_path' => 'storage/images/courses/uiux-projects.jpg', 'caption' => 'Dự án thiết kế thực tế', 'is_primary' => false, 'sort_order' => 3],
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Nhập môn UI/UX',
                        'description' => 'Giới thiệu về UI/UX Design.',
                        'sort_order' => 1,
                        'lessons' => [
                            [
                                'title' => 'Bài 1.1: UI vs UX - Sự khác biệt',
                                'content' => 'Phân biệt UI (User Interface) và UX (User Experience). Tại sao cả hai đều quan trọng và cách chúng bổ trợ cho nhau.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'UI vs UX giải thích', 'url' => 'https://www.youtube.com/embed/9BFX6E5qM9E', 'duration' => 480, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Slide UI vs UX', 'resource_path' => 'storage/resources/uiux/ui-vs-ux.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.2: Design Thinking Process',
                                'content' => 'Quy trình 5 giai đoạn của Design Thinking: Empathize, Define, Ideate, Prototype, Test.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Design Thinking overview', 'url' => 'https://www.youtube.com/embed/a7s1CkGn3Rk', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Design Thinking template', 'resource_path' => 'storage/resources/uiux/design-thinking-template.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.3: Nguyên tắc Thiết kế cơ bản',
                                'content' => 'Các nguyên tắc thiết kế nền tảng: contrast, hierarchy, balance, alignment, proximity.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Design Principles', 'url' => 'https://www.youtube.com/embed/sn0aD82X6qE', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Design principles checklist', 'resource_path' => 'storage/resources/uiux/design-principles.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 1.4: Typography và Color Theory',
                                'content' => 'Chọn font chữ phù hợp, sử dụng màu sắc hiệu quả, và tạo bảng màu cho dự án.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Typography và Colors', 'url' => 'https://www.youtube.com/embed/5J0L5J0K2qE', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Color wheel template', 'resource_path' => 'storage/resources/uiux/color-wheel.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Figma Cơ bản',
                        'description' => 'Làm quen với công cụ Figma.',
                        'sort_order' => 2,
                        'lessons' => [
                            [
                                'title' => 'Bài 2.1: Interface và Tools',
                                'content' => 'Giao diện Figma và các công cụ cơ bản: move, frame, shape, text, pen tool.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Figma interface tour', 'url' => 'https://www.youtube.com/embed/FT7VQLYJbV4', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Figma shortcuts cheatsheet', 'resource_path' => 'storage/resources/uiux/figma-shortcuts.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.2: Tạo Design System',
                                'content' => 'Xây dựng hệ thống thiết kế với colors, typography, icons, và components có thể tái sử dụng.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Design System in Figma', 'url' => 'https://www.youtube.com/embed/kB3c1qYl7gU', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Design System template', 'resource_path' => 'storage/resources/uiux/design-system-template.fig', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.3: Auto Layout',
                                'content' => 'Sử dụng Auto Layout để tạo responsive designs, quản lý spacing và padding tự động.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Figma Auto Layout', 'url' => 'https://www.youtube.com/embed/m1V7G3a9J4k', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Auto Layout examples', 'resource_path' => 'storage/resources/uiux/auto-layout-examples.fig', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.4: Components và Variants',
                                'content' => 'Tạo components có thể tái sử dụng, sử dụng variants và properties để quản lý các trạng thái.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Components và Variants', 'url' => 'https://www.youtube.com/embed/n2V4q6J5K6m', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Button component template', 'resource_path' => 'storage/resources/uiux/button-component.fig', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 2.5: Working with Images và Icons',
                                'content' => 'Chèn, cắt, và xử lý hình ảnh. Sử dụng icon sets và cách export assets.',
                                'sort_order' => 5,
                                'videos' => [
                                    ['title' => 'Images và Icons in Figma', 'url' => 'https://www.youtube.com/embed/7J4K6L6M6m', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Icon set template', 'resource_path' => 'storage/resources/uiux/icon-set.fig', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 3: User Research và Persona',
                        'description' => 'Học cách nghiên cứu người dùng và xây dựng persona.',
                        'sort_order' => 3,
                        'lessons' => [
                            [
                                'title' => 'Bài 3.1: User Research Methods',
                                'content' => 'Các phương pháp nghiên cứu người dùng: interview, survey, observation, competitive analysis.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'User Research Methods', 'url' => 'https://www.youtube.com/embed/J4K6L6M7n', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Research methods guide', 'resource_path' => 'storage/resources/uiux/user-research-guide.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.2: Xây dựng Persona',
                                'content' => 'Cách tạo user persona từ dữ liệu nghiên cứu. Template persona và cách sử dụng persona trong thiết kế.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Creating User Personas', 'url' => 'https://www.youtube.com/embed/J4K6L6M8o', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Persona template', 'resource_path' => 'storage/resources/uiux/persona-template.fig', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.3: User Journey Mapping',
                                'content' => 'Tạo user journey map để hiểu các điểm tiếp xúc, cảm xúc và pain points của người dùng.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'User Journey Mapping', 'url' => 'https://www.youtube.com/embed/K4J6L6M9p', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Journey map template', 'resource_path' => 'storage/resources/uiux/journey-map-template.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 3.4: Information Architecture',
                                'content' => 'Tổ chức thông tin và nội dung với sitemap, hierarchy và navigation patterns.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Information Architecture', 'url' => 'https://www.youtube.com/embed/L4J6L6M0q', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Sitemap template', 'resource_path' => 'storage/resources/uiux/sitemap-template.fig', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 4: Wireframing và Prototyping',
                        'description' => 'Thiết kế wireframes và tạo prototypes tương tác.',
                        'sort_order' => 4,
                        'lessons' => [
                            [
                                'title' => 'Bài 4.1: Low-fidelity Wireframing',
                                'content' => 'Tạo wireframes nhanh với placeholder boxes. Mục đích của lo-fi wireframes và khi nào nên sử dụng.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Low-fi Wireframing', 'url' => 'https://www.youtube.com/embed/M4J6L6N1r', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Wireframe kit', 'resource_path' => 'storage/resources/uiux/wireframe-kit.fig', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.2: High-fidelity Wireframing',
                                'content' => 'Nâng cấp wireframes với styling, typography và chi tiết thực tế hơn trong Figma.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'High-fi Wireframing', 'url' => 'https://www.youtube.com/embed/N4J6L6O2s', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Hi-fi wireframe example', 'resource_path' => 'storage/resources/uiux/hifi-wireframe.fig', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.3: Interactive Prototyping',
                                'content' => 'Tạo prototypes có thể tương tác trong Figma. Thiết lập interactions, transitions và animations.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Figma Prototyping', 'url' => 'https://www.youtube.com/embed/O4J6L6P3t', 'duration' => 780, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Prototype examples', 'resource_path' => 'storage/resources/uiux/prototype-examples.fig', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 4.4: Responsive Design',
                                'content' => 'Thiết kế cho nhiều kích thước màn hình: desktop, tablet, mobile. Auto layout cho responsive.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Responsive Design in Figma', 'url' => 'https://www.youtube.com/embed/P4J6L6Q4u', 'duration' => 720, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Responsive breakpoints guide', 'resource_path' => 'storage/resources/uiux/breakpoints-guide.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chương 5: Usability Testing và Iteration',
                        'description' => 'Kiểm tra tính khả dụng và cải thiện thiết kế.',
                        'sort_order' => 5,
                        'lessons' => [
                            [
                                'title' => 'Bài 5.1: Usability Testing Setup',
                                'content' => 'Chuẩn bị và thực hiện usability testing. Viết test plan, chọn participants và setup.',
                                'sort_order' => 1,
                                'videos' => [
                                    ['title' => 'Usability Testing Guide', 'url' => 'https://www.youtube.com/embed/Q4J6L6R5v', 'duration' => 660, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Usability test script template', 'resource_path' => 'storage/resources/uiux/test-script-template.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.2: Conducting User Tests',
                                'content' => 'Hướng dẫn điều hành user test, quan sát và ghi chép hành vi người dùng.',
                                'sort_order' => 2,
                                'videos' => [
                                    ['title' => 'Conducting User Tests', 'url' => 'https://www.youtube.com/embed/R4J6L6S6w', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Observation checklist', 'resource_path' => 'storage/resources/uiux/observation-checklist.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.3: Analyzing Test Results',
                                'content' => 'Phân tích dữ liệu từ usability tests, xác định pain points và ưu tiên cải tiến.',
                                'sort_order' => 3,
                                'videos' => [
                                    ['title' => 'Analyzing Usability Data', 'url' => 'https://www.youtube.com/embed/S4J6L6T7x', 'duration' => 540, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Analysis worksheet', 'resource_path' => 'storage/resources/uiux/analysis-worksheet.pdf', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'title' => 'Bài 5.4: Design Iteration',
                                'content' => 'Áp dụng feedback để cải thiện thiết kế. Iterative design process và document changes.',
                                'sort_order' => 4,
                                'videos' => [
                                    ['title' => 'Design Iteration Process', 'url' => 'https://www.youtube.com/embed/T4J6L6U8y', 'duration' => 600, 'sort_order' => 1],
                                ],
                                'resources' => [
                                    ['title' => 'Iteration log template', 'resource_path' => 'storage/resources/uiux/iteration-log.pdf', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($courses as $courseData) {
            $chapterList = $courseData['chapters'];
            $categorySlugs = $courseData['category_slugs'];
            $requirements = $courseData['requirements'];
            $objectives = $courseData['objectives'];
            $images = $courseData['images'] ?? [];

            unset($courseData['chapters'], $courseData['category_slugs'], $courseData['requirements'], $courseData['objectives'], $courseData['images']);

            // Create course
            $course = Course::create([
                'course_id' => Str::uuid(),
                'title' => $courseData['title'],
                'description' => $courseData['description'],
                'price' => $courseData['price'],
                'difficulty' => $courseData['difficulty'],
                'language' => $courseData['language'],
                'created_by' => $instructor->instructor_id,
            ]);

            // Attach instructor
            DBFacade::table('course_instructor')->insert([
                'course_id' => $course->course_id,
                'instructor_id' => $instructor->instructor_id,
                'created_at' => now(),
            ]);

            // Attach categories
            $categoryIds = Category::whereIn('slug', $categorySlugs)->pluck('id')->all();

            if (count($categoryIds) !== count($categorySlugs)) {
                $missingSlugs = implode(', ', array_diff($categorySlugs, Category::whereIn('slug', $categorySlugs)->pluck('slug')->all()));
                throw new \RuntimeException("Missing categories for slugs: {$missingSlugs}");
            }

            foreach ($categoryIds as $categoryId) {
                DBFacade::table('course_category')->insert([
                    'course_id' => $course->course_id,
                    'category_id' => $categoryId,
                    'created_at' => now(),
                ]);
            }

            // Add requirements
            foreach ($requirements as $index => $requirement) {
                \App\Models\CourseRequirement::create([
                    'requirement_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'requirement' => $requirement,
                ]);
            }

            // Add objectives
            foreach ($objectives as $index => $objective) {
                \App\Models\CourseObjective::create([
                    'objective_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'objective' => $objective,
                ]);
            }

            // Add course images
            foreach ($images as $imageData) {
                \App\Models\CourseImage::create([
                    'image_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'image_path' => $imageData['image_path'],
                    'caption' => $imageData['caption'],
                    'is_primary' => $imageData['is_primary'] ?? false,
                    'sort_order' => $imageData['sort_order'],
                ]);
            }

            // Add chapters and lessons
            foreach ($chapterList as $chapterData) {
                $chapter = CourseChapter::create([
                    'chapter_id' => Str::uuid(),
                    'course_id' => $course->course_id,
                    'title' => $chapterData['title'],
                    'description' => $chapterData['description'],
                    'sort_order' => $chapterData['sort_order'],
                ]);

                foreach ($chapterData['lessons'] as $lessonData) {
                    $lesson = CourseLesson::create([
                        'lesson_id' => Str::uuid(),
                        'course_id' => $course->course_id,
                        'chapter_id' => $chapter->chapter_id,
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'],
                        'sort_order' => $lessonData['sort_order'],
                    ]);

                    // Add videos for this lesson
                    if (isset($lessonData['videos']) && is_array($lessonData['videos'])) {
                        foreach ($lessonData['videos'] as $videoData) {
                            \App\Models\CourseVideo::create([
                                'video_id' => Str::uuid(),
                                'lesson_id' => $lesson->lesson_id,
                                'title' => $videoData['title'],
                                'url' => $videoData['url'],
                                'duration' => $videoData['duration'],
                                'sort_order' => $videoData['sort_order'],
                            ]);
                        }
                    }

                    // Add resources for this lesson
                    if (isset($lessonData['resources']) && is_array($lessonData['resources'])) {
                        foreach ($lessonData['resources'] as $resourceData) {
                            \App\Models\CourseResource::create([
                                'resource_id' => Str::uuid(),
                                'lesson_id' => $lesson->lesson_id,
                                'title' => $resourceData['title'],
                                'resource_path' => $resourceData['resource_path'],
                                'sort_order' => $resourceData['sort_order'],
                            ]);
                        }
                    }
                }
            }
        }
    }
}
