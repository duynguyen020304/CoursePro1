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
                'category_ids' => [9, 5], // Python (child of Technology), Data Science
                'requirements' => [
                    'Không yêu cầu kinh nghiệm lập trình trước.',
                    'Máy tính cá nhân có kết nối internet.',
                ],
                'objectives' => [
                    'Nắm vững cú pháp cơ bản của Python.',
                    'Hiểu và áp dụng lập trình hướng đối tượng trong Python.',
                    'Thao tác với file và xử lý ngoại lệ.',
                    'Làm việc hiệu quả với các thư viện NumPy, Pandas.',
                    'Viết các chương trình Python có cấu trúc tốt và dễ bảo trì.',
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Giới thiệu Python và Cài đặt Môi trường',
                        'description' => 'Làm quen với Python và thiết lập môi trường phát triển.',
                        'sort_order' => 1,
                        'lessons' => [
                            ['title' => 'Bài 1.1: Python là gì? Lịch sử và ứng dụng', 'content' => 'Tổng quan về Python và các lĩnh vực ứng dụng của nó.', 'sort_order' => 1],
                            ['title' => 'Bài 1.2: Cài đặt Python và Pip', 'content' => 'Hướng dẫn cài đặt Python trên các hệ điều hành và quản lý gói Pip.', 'sort_order' => 2],
                            ['title' => 'Bài 1.3: Sử dụng IDE (VS Code) và Jupyter Notebook', 'content' => 'Thiết lập và làm quen với các công cụ phát triển phổ biến.', 'sort_order' => 3],
                            ['title' => 'Bài 1.4: Viết chương trình Python đầu tiên', 'content' => 'Chương trình "Hello World" và cách chạy mã Python.', 'sort_order' => 4],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Cú pháp cơ bản và Kiểu dữ liệu',
                        'description' => 'Tìm hiểu các thành phần cơ bản của ngôn ngữ Python.',
                        'sort_order' => 2,
                        'lessons' => [
                            ['title' => 'Bài 2.1: Biến, toán tử và biểu thức', 'content' => 'Khai báo biến, các loại toán tử số học, so sánh, logic.', 'sort_order' => 1],
                            ['title' => 'Bài 2.2: Kiểu dữ liệu số (int, float, complex)', 'content' => 'Làm việc với các loại số và các phép toán liên quan.', 'sort_order' => 2],
                            ['title' => 'Bài 2.3: Kiểu dữ liệu chuỗi (string) và các phương thức', 'content' => 'Tạo chuỗi, nối chuỗi, định dạng chuỗi và các phương thức xử lý chuỗi.', 'sort_order' => 3],
                            ['title' => 'Bài 2.4: Kiểu dữ liệu Boolean và None', 'content' => 'Sử dụng giá trị True/False và khái niệm None.', 'sort_order' => 4],
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
                'category_ids' => [13, 6], // ReactJS, Web Development
                'requirements' => [
                    'Kiến thức cơ bản về HTML, CSS và JavaScript.',
                    'Hiểu về ES6+ syntax.',
                ],
                'objectives' => [
                    'Hiểu rõ về JSX và Virtual DOM.',
                    'Thành thạo React Hooks (useState, useEffect, useContext, etc.).',
                    'Xây dựng Single Page Application với React Router.',
                    'Quản lý state với Context API.',
                    'Tích hợp API và xử lý bất đồng bộ.',
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Nhập môn ReactJS',
                        'description' => 'Giới thiệu về React và thiết lập môi trường.',
                        'sort_order' => 1,
                        'lessons' => [
                            ['title' => 'Bài 1.1: React là gì? Tại sao sử dụng React?', 'content' => 'Giới thiệu về React và lợi ích.', 'sort_order' => 1],
                            ['title' => 'Bài 1.2: Cài đặt Create React App', 'content' => 'Thiết lập môi trường phát triển React.', 'sort_order' => 2],
                            ['title' => 'Bài 1.3: JSX - JavaScript XML', 'content' => 'Hiểu về JSX và cách sử dụng.', 'sort_order' => 3],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Components và Props',
                        'description' => 'Học về Components và cách truyền dữ liệu với Props.',
                        'sort_order' => 2,
                        'lessons' => [
                            ['title' => 'Bài 2.1: Functional Components', 'content' => 'Tạo và sử dụng functional components.', 'sort_order' => 1],
                            ['title' => 'Bài 2.2: Props và truyền dữ liệu', 'content' => 'Cách truyền dữ liệu giữa các components.', 'sort_order' => 2],
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
                'category_ids' => [18, 5], // Machine Learning, Data Science
                'requirements' => [
                    'Kiến thức Python cơ bản.',
                    'Hiểu biết cơ bản về thống kê và đại số tuyến tính.',
                ],
                'objectives' => [
                    'Hiểu các khái niệm cơ bản của Machine Learning.',
                    'Áp dụng các thuật toán Supervised Learning.',
                    'Làm việc với Scikit-learn.',
                    'Đánh giá và tối ưu model.',
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Giới thiệu Machine Learning',
                        'description' => 'Tổng quan về ML và các khái niệm cơ bản.',
                        'sort_order' => 1,
                        'lessons' => [
                            ['title' => 'Bài 1.1: Machine Learning là gì?', 'content' => 'Giới thiệu về ML và ứng dụng.', 'sort_order' => 1],
                            ['title' => 'Bài 1.2: Các loại Machine Learning', 'content' => 'Supervised, Unsupervised, Reinforcement Learning.', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Supervised Learning',
                        'description' => 'Học các thuật toán Supervised Learning.',
                        'sort_order' => 2,
                        'lessons' => [
                            ['title' => 'Bài 2.1: Linear Regression', 'content' => 'Hồi quy tuyến tính.', 'sort_order' => 1],
                            ['title' => 'Bài 2.2: Logistic Regression', 'content' => 'Hồi quy logistic cho phân loại.', 'sort_order' => 2],
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
                'category_ids' => [14, 6], // Node.js, Web Development
                'requirements' => [
                    'Kiến thức JavaScript cơ bản.',
                    'Hiểu về HTTP và REST API.',
                ],
                'objectives' => [
                    'Hiểu về Node.js runtime và event loop.',
                    'Xây dựng REST API với Express.',
                    'Làm việc với MongoDB và Mongoose.',
                    'Authentication và Authorization với JWT.',
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Nhập môn Node.js',
                        'description' => 'Giới thiệu Node.js và thiết lập môi trường.',
                        'sort_order' => 1,
                        'lessons' => [
                            ['title' => 'Bài 1.1: Node.js là gì?', 'content' => 'Giới thiệu về Node.js.', 'sort_order' => 1],
                            ['title' => 'Bài 1.2: Module System và NPM', 'content' => 'Làm việc với modules và package manager.', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Express Framework',
                        'description' => 'Xây dựng web server với Express.',
                        'sort_order' => 2,
                        'lessons' => [
                            ['title' => 'Bài 2.1: Routing và Middleware', 'content' => 'Xử lý request và middleware.', 'sort_order' => 1],
                            ['title' => 'Bài 2.2: Xây dựng REST API', 'content' => 'Tạo CRUD API.', 'sort_order' => 2],
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
                'category_ids' => [3], // Design
                'requirements' => [
                    'Không yêu cầu kinh nghiệm thiết kế trước.',
                    'Máy tính có cài đặt Figma (miễn phí).',
                ],
                'objectives' => [
                    'Hiểu nguyên lý Design Thinking.',
                    'Thực hành User Research.',
                    'Thiết kế Wireframe và Prototype.',
                    'Thành thạo Figma.',
                ],
                'chapters' => [
                    [
                        'title' => 'Chương 1: Nhập môn UI/UX',
                        'description' => 'Giới thiệu về UI/UX Design.',
                        'sort_order' => 1,
                        'lessons' => [
                            ['title' => 'Bài 1.1: UI vs UX - Sự khác biệt', 'content' => 'Phân biệt UI và UX.', 'sort_order' => 1],
                            ['title' => 'Bài 1.2: Design Thinking Process', 'content' => 'Quy trình tư duy thiết kế.', 'sort_order' => 2],
                        ],
                    ],
                    [
                        'title' => 'Chương 2: Figma Cơ bản',
                        'description' => 'Làm quen với công cụ Figma.',
                        'sort_order' => 2,
                        'lessons' => [
                            ['title' => 'Bài 2.1: Interface và Tools', 'content' => 'Giao diện và công cụ Figma.', 'sort_order' => 1],
                            ['title' => 'Bài 2.2: Tạo Design System', 'content' => 'Xây dựng hệ thống thiết kế.', 'sort_order' => 2],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($courses as $courseData) {
            $chapterList = $courseData['chapters'];
            $categoryIds = $courseData['category_ids'];
            $requirements = $courseData['requirements'];
            $objectives = $courseData['objectives'];

            unset($courseData['chapters'], $courseData['category_ids'], $courseData['requirements'], $courseData['objectives']);

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
            \DB::table('course_instructor')->insert([
                'course_id' => $course->course_id,
                'instructor_id' => $instructor->instructor_id,
                'created_at' => now(),
            ]);

            // Attach categories
            foreach ($categoryIds as $categoryId) {
                \DB::table('course_category')->insert([
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
                    CourseLesson::create([
                        'lesson_id' => Str::uuid(),
                        'course_id' => $course->course_id,
                        'chapter_id' => $chapter->chapter_id,
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'],
                        'sort_order' => $lessonData['sort_order'],
                    ]);
                }
            }
        }
    }
}
