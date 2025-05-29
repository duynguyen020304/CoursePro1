<?php
// Bắt đầu bộ đệm đầu ra
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../service/service_user.php';
require_once __DIR__ . '/../model/database.php';


class UserInitializer
{
    private UserService $userService;
    private Database $db;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->db = new Database();
    }
    public function initialize(): void
    {
        $isGenerateInstructorSuccess = true;
        $isGenerateStudentSuccess = true;
        echo "Starting user initialization...\n";
        $passwordAdmin = password_hash("duyadmin123", PASSWORD_DEFAULT);
        $adminID = str_replace('.', '_', uniqid('admin', true));
        $admin_sql = "INSERT INTO Users (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage) VALUES ('{$adminID}', 'Duy', 'Admin', 'duyadmin123@example.com', '{$passwordAdmin}', 'admin', 'null')";
        $this->db->execute($admin_sql);
        // Create 4 instructor accounts
        $instructors = [
            [
                'email' => 'instructor1@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Tuan',
                'role' => 'instructor',
                'biography' => 'Thầy **Nguyễn Tuấn** là một chuyên gia lập trình backend hàng đầu với hơn **18 năm kinh nghiệm** trong ngành công nghiệp phần mềm. Thầy đã từng đảm nhiệm vị trí Kiến trúc sư giải pháp tại các tập đoàn công nghệ đa quốc gia, nơi thầy dẫn dắt nhiều dự án phát triển hệ thống lớn, từ các ứng dụng tài chính ngân hàng đến các nền tảng thương mại điện tử quy mô lớn. Chuyên môn của thầy trải rộng từ **Java Enterprise Edition (JEE)**, **Spring Framework**, **Python** và **Django**, đến các hệ thống cơ sở dữ liệu phân tán và kiến trúc microservices. Thầy Tuấn không chỉ giỏi về kỹ thuật mà còn có khả năng sư phạm xuất sắc, luôn biết cách biến những khái niệm phức tạp thành dễ hiểu, thông qua các ví dụ thực tế và bài tập ứng dụng cao. Thầy thường xuyên cập nhật kiến thức mới nhất trong lĩnh vực công nghệ để mang đến những bài giảng sát với yêu cầu của thị trường lao động. Ngoài ra, thầy cũng là tác giả của một số bài báo khoa học và tham gia nhiều hội nghị chuyên ngành.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor2@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Tran',
                'lastName' => 'Mai',
                'role' => 'instructor',
                'biography' => 'Cô **Trần Mai** là một nhà thiết kế UI/UX tài năng với hơn **12 năm kinh nghiệm** trong việc tạo ra các sản phẩm số có trải nghiệm người dùng vượt trội. Tốt nghiệp chuyên ngành Thiết kế đồ họa và sau đó theo đuổi Thạc sĩ về Thiết kế tương tác, cô Mai đã làm việc cho các agency thiết kế hàng đầu và các công ty khởi nghiệp công nghệ, chuyên về thiết kế ứng dụng di động, trang web và các nền tảng SaaS. Cô có kiến thức sâu rộng về **Human-Centered Design (HCD)**, **Design Thinking**, **Interaction Design**, **User Research** và các công cụ thiết kế hiện đại như Figma, Adobe XD, Sketch. Cô Mai luôn khuyến khích sinh viên tư duy sáng tạo, đặt người dùng làm trung tâm và phát triển khả năng giải quyết vấn đề thông qua quy trình thiết kế lặp đi lặp lại. Cô cũng là diễn giả thường xuyên tại các sự kiện cộng đồng về thiết kế và đổi mới.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor3@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Le',
                'lastName' => 'Thanh',
                'role' => 'instructor',
                'biography' => 'Thầy **Lê Thanh** là một chuyên gia hàng đầu về **Trí tuệ nhân tạo (AI)** và **Machine Learning (ML)**. Thầy tốt nghiệp Tiến sĩ Khoa học Máy tính từ Đại học Quốc gia Singapore và có kinh nghiệm nghiên cứu tại các viện nghiên cứu danh tiếng thế giới. Thầy Thanh đã công bố hơn 20 bài báo khoa học trên các tạp chí và hội nghị quốc tế uy tín, tập trung vào các lĩnh vực như **Deep Learning**, **Natural Language Processing (NLP)**, **Computer Vision** và **Reinforcement Learning**. Với phong cách giảng dạy khoa học, chặt chẽ nhưng cũng rất truyền cảm hứng, thầy giúp sinh viên hiểu rõ nền tảng lý thuyết và ứng dụng thực tiễn của AI/ML. Thầy còn là cố vấn cho nhiều dự án AI khởi nghiệp và đóng góp vào sự phát triển của cộng đồng AI tại Việt Nam.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor4@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Pham',
                'lastName' => 'Huong',
                'role' => 'instructor',
                'biography' => 'Cô **Phạm Hương** là một kỹ sư phần mềm Full-stack năng động và giàu kinh nghiệm, chuyên sâu trong việc xây dựng các ứng dụng web hiệu suất cao và có khả năng mở rộng. Cô có hơn **10 năm kinh nghiệm** làm việc với nhiều công nghệ khác nhau, bao gồm **React, Angular, Vue.js** cho front-end và **Node.js, Express, PHP, Laravel** cho back-end, cùng với kiến thức vững chắc về cơ sở dữ liệu SQL và NoSQL. Cô Hương không chỉ giỏi về kỹ thuật mà còn rất chú trọng đến chất lượng mã nguồn, quy trình phát triển Agile và DevOps. Cô luôn tạo ra môi trường học tập thân thiện, khuyến khích sinh viên đặt câu hỏi và thực hành liên tục. Cô tin rằng, để trở thành một lập trình viên giỏi, cần phải kết hợp giữa lý thuyết và thực hành, và không ngừng học hỏi những điều mới.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor5@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Hoang',
                'lastName' => 'Duc',
                'role' => 'instructor',
                'biography' => 'Thầy **Hoàng Đức** là một chuyên gia kỳ cựu trong lĩnh vực **Cybersecurity (An ninh mạng)** với hơn **20 năm kinh nghiệm** bảo vệ các hệ thống thông tin quan trọng cho các tổ chức lớn. Thầy từng làm việc với vai trò Trưởng phòng An ninh thông tin và Tư vấn an ninh cho các ngân hàng và tập đoàn tài chính. Chuyên môn của thầy bao gồm **phân tích lỗ hổng**, **kiểm thử xâm nhập (penetration testing)**, **phản ứng sự cố (incident response)**, và **kiến trúc bảo mật hệ thống**. Thầy Đức là người có kiến thức sâu rộng về các tiêu chuẩn bảo mật quốc tế và các cuộc tấn công mạng mới nhất. Thầy luôn nhấn mạnh tầm quan trọng của tư duy phản biện và đạo đức nghề nghiệp trong an ninh mạng. Bài giảng của thầy không chỉ cung cấp kiến thức chuyên môn mà còn trang bị cho sinh viên những kỹ năng thực chiến để đối phó với các mối đe dọa trong thế giới số.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor6@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Minh',
                'role' => 'instructor',
                'biography' => 'Thầy **Nguyễn Minh** là một Data Scientist xuất sắc với hơn **8 năm kinh nghiệm** trong việc khai thác dữ liệu để đưa ra các quyết định kinh doanh chiến lược. Thầy có bằng Thạc sĩ về Khoa học Dữ liệu và đã làm việc cho các công ty công nghệ lớn trong lĩnh vực phân tích dữ liệu khách hàng, dự đoán xu hướng thị trường và tối ưu hóa hoạt động. Chuyên môn của thầy bao gồm **Statistical Modeling**, **Machine Learning Algorithms**, **Data Visualization** và sử dụng thành thạo các công cụ như Python (Pandas, NumPy, Scikit-learn), R và SQL. Thầy Minh có khả năng biến những bộ dữ liệu thô thành những thông tin chi tiết có giá trị, giúp sinh viên hiểu được cách thức ứng dụng khoa học dữ liệu vào các bài toán thực tế trong nhiều ngành nghề khác nhau.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor7@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Bui',
                'lastName' => 'Thi Hoa',
                'role' => 'instructor',
                'biography' => 'Cô **Bùi Thị Hoa** là chuyên gia về **Phát triển Game** và **Đồ họa Máy tính** với hơn **10 năm kinh nghiệm** trong ngành công nghiệp game. Cô đã tham gia phát triển nhiều tựa game nổi tiếng trên các nền tảng di động và PC, đóng vai trò là Lập trình viên Gameplay và Đồ họa. Cô có kiến thức sâu rộng về **Unity3D**, **C#**, **OpenGL**, **DirectX** và các nguyên lý thiết kế game. Cô Hoa đam mê việc tạo ra những trải nghiệm tương tác thú vị và truyền đạt niềm đam mê đó cho sinh viên. Các khóa học của cô tập trung vào việc xây dựng game từ con số 0, giúp sinh viên phát triển kỹ năng lập trình, tư duy logic và khả năng sáng tạo trong môi trường phát triển game chuyên nghiệp.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor8@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Vu',
                'lastName' => 'Van Long',
                'role' => 'instructor',
                'biography' => 'Thầy **Vũ Văn Long** là một kỹ sư **DevOps** và **Cloud Computing** hàng đầu, với hơn **15 năm kinh nghiệm** trong việc xây dựng và quản lý hạ tầng công nghệ thông tin. Thầy đã từng làm việc tại các công ty công nghệ lớn, chuyên về tự động hóa triển khai, quản lý cơ sở hạ tầng trên đám mây (AWS, Azure, GCP) và xây dựng các quy trình CI/CD hiệu quả. Thầy Long có kiến thức chuyên sâu về **Docker, Kubernetes, Jenkins, Terraform** và các công cụ giám sát hệ thống. Thầy mang đến những bài giảng thực tế về cách thức vận hành và mở rộng các hệ thống phần mềm trong môi trường sản xuất, giúp sinh viên nắm vững các kỹ năng quan trọng để trở thành một kỹ sư DevOps chuyên nghiệp trong kỷ nguyên số.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor9@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Do',
                'lastName' => 'Trong Nghia',
                'role' => 'instructor',
                'biography' => 'Thầy **Đỗ Trọng Nghĩa** là một chuyên gia về **Quản lý dự án phần mềm** và **Phương pháp Agile/Scrum** với hơn **15 năm kinh nghiệm** trong vai trò Quản lý dự án (PM) và Scrum Master. Thầy đã dẫn dắt thành công nhiều dự án phần mềm phức tạp, từ giai đoạn lên ý tưởng đến triển khai và bảo trì. Thầy Nghĩa có kiến thức sâu rộng về các phương pháp luận quản lý dự án truyền thống và hiện đại, đặc biệt là Agile, Scrum, Kanban. Thầy không chỉ chia sẻ kiến thức lý thuyết mà còn cung cấp những kinh nghiệm thực tiễn quý báu về cách thức xây dựng đội nhóm hiệu quả, quản lý rủi ro và giao tiếp với khách hàng. Thầy giúp sinh viên phát triển các kỹ năng lãnh đạo và quản lý cần thiết để dẫn dắt các dự án công nghệ thành công.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor10@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Phan',
                'lastName' => 'Anh Thu',
                'role' => 'instructor',
                'biography' => 'Cô **Phan Anh Thư** là một chuyên gia về **Phát triển ứng dụng di động** cho cả nền tảng iOS và Android, với hơn **9 năm kinh nghiệm** trong ngành. Cô đã tham gia vào toàn bộ vòng đời phát triển của nhiều ứng dụng di động nổi tiếng, từ giai đoạn thiết kế UI/UX đến lập trình và triển khai lên các cửa hàng ứng dụng. Chuyên môn của cô bao gồm **Swift/Objective-C** cho iOS và **Kotlin/Java** cho Android, cùng với kiến thức về các framework đa nền tảng như React Native và Flutter. Cô Anh Thư luôn cập nhật những xu hướng mới nhất trong phát triển di động và truyền đạt kiến thức một cách thực tế, giúp sinh viên xây dựng những ứng dụng di động chất lượng cao, đáp ứng nhu cầu thị trường.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor11@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Tran',
                'lastName' => 'Van Vu',
                'role' => 'instructor',
                'biography' => 'Thầy **Trần Văn Vũ** là một chuyên gia **Database Administrator (DBA)** và **System Architect** với hơn **16 năm kinh nghiệm** trong việc thiết kế, triển khai và tối ưu hóa các hệ thống cơ sở dữ liệu phức tạp. Thầy có kiến thức sâu rộng về **Oracle, SQL Server, MySQL** và các hệ thống NoSQL như MongoDB, Cassandra. Thầy Vũ không chỉ giỏi về quản trị mà còn có khả năng phân tích và giải quyết các vấn đề hiệu suất cơ sở dữ liệu một cách triệt để. Thầy mang đến cho sinh viên cái nhìn toàn diện về tầm quan trọng của dữ liệu, cách thức quản lý và bảo mật dữ liệu hiệu quả, cùng với các kỹ thuật tối ưu hóa truy vấn và thiết kế lược đồ cơ sở dữ liệu.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor12@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Hoai An',
                'role' => 'instructor',
                'biography' => 'Cô **Nguyễn Hoài An** là một nhà phát triển **Front-end** với niềm đam mê tạo ra các trải nghiệm web tương tác và đẹp mắt. Với hơn **7 năm kinh nghiệm**, cô An đã làm việc với các công ty công nghệ trong lĩnh vực xây dựng các giao diện người dùng phức tạp sử dụng **HTML5, CSS3, JavaScript** và các framework hiện đại như **ReactJS, VueJS**. Cô có kiến thức sâu sắc về tối ưu hóa hiệu suất web, thiết kế đáp ứng (responsive design) và các tiêu chuẩn web. Cô Hoài An là một người hướng dẫn nhiệt tình, luôn chia sẻ những thủ thuật và công cụ mới nhất để giúp sinh viên trở thành những nhà phát triển front-end chuyên nghiệp, có khả năng xây dựng các trang web không chỉ đẹp về mặt hình ảnh mà còn mạnh mẽ về chức năng.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor13@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Le',
                'lastName' => 'Quoc Khanh',
                'role' => 'instructor',
                'biography' => 'Thầy **Lê Quốc Khánh** là một chuyên gia về **Kiểm thử phần mềm (Software Testing)** và **Đảm bảo chất lượng (QA)** với hơn **14 năm kinh nghiệm** trong ngành. Thầy từng làm việc với vai trò Trưởng nhóm QA và Tư vấn kiểm thử cho các dự án phần mềm quy mô lớn, từ ứng dụng doanh nghiệp đến hệ thống nhúng. Thầy Khánh có kiến thức sâu rộng về các phương pháp kiểm thử thủ công và tự động, sử dụng các công cụ như Selenium, JMeter, Postman và các framework kiểm thử. Thầy luôn nhấn mạnh tầm quan trọng của chất lượng sản phẩm và cách thức xây dựng quy trình kiểm thử hiệu quả. Thầy cung cấp cho sinh viên những kỹ năng cần thiết để phát hiện lỗi, phân tích yêu cầu và đảm bảo sản phẩm phần mềm đạt chất lượng cao nhất trước khi đến tay người dùng.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor14@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Dang',
                'lastName' => 'Thi Ngoc',
                'role' => 'instructor',
                'biography' => 'Cô **Đặng Thị Ngọc** là một nhà phát triển **Blockchain** và chuyên gia về **Web3** với hơn **8 năm kinh nghiệm** trong lĩnh vực công nghệ phân tán. Cô đã tham gia vào nhiều dự án phát triển hợp đồng thông minh (smart contracts), DApps (Decentralized Applications) và các giải pháp blockchain cho nhiều ngành công nghiệp khác nhau. Cô Ngọc có kiến thức chuyên sâu về **Ethereum, Solidity, Hyperledger, IPFS** và các nguyên lý của công nghệ blockchain. Cô đam mê việc khám phá tiềm năng của công nghệ này và chia sẻ kiến thức của mình một cách rõ ràng và dễ hiểu. Cô giúp sinh viên nắm vững các khái niệm cơ bản về blockchain, cách thức hoạt động của các mạng lưới phi tập trung và kỹ năng phát triển ứng dụng trên blockchain.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor15@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Hoang',
                'lastName' => 'Sy Long',
                'role' => 'instructor',
                'biography' => 'Thầy **Hoàng Sỹ Long** là một chuyên gia về **Big Data** và **Cloud Data Engineering** với hơn **10 năm kinh nghiệm** xây dựng và quản lý các hệ thống dữ liệu lớn. Thầy đã từng làm việc với vai trò kỹ sư dữ liệu cấp cao tại các công ty công nghệ tài chính, nơi thầy thiết kế và triển khai các pipeline dữ liệu, hệ thống lưu trữ và xử lý dữ liệu phân tán. Chuyên môn của thầy bao gồm **Apache Spark, Hadoop, Kafka, Snowflake** và các dịch vụ dữ liệu trên đám mây (AWS Glue, EMR, Redshift). Thầy Long có khả năng giải thích các kiến trúc Big Data phức tạp một cách dễ hiểu, giúp sinh viên trang bị những kỹ năng cần thiết để làm việc với dữ liệu quy mô lớn và xây dựng các hệ thống dữ liệu hiệu quả trong môi trường đám mây.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor16@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Phuong Vy',
                'role' => 'instructor',
                'biography' => 'Cô **Nguyễn Phương Vy** là một nhà giáo dục công nghệ thông tin nhiệt huyết và là một chuyên gia về **Lập trình hướng đối tượng (OOP)** và **Thiết kế phần mềm**. Với hơn **11 năm kinh nghiệm** giảng dạy và phát triển phần mềm, cô Vy đã giúp hàng trăm sinh viên tiếp cận và nắm vững các nguyên lý cơ bản của lập trình, từ cấu trúc dữ liệu và giải thuật đến các mô hình thiết kế phần mềm tiên tiến. Cô đặc biệt chú trọng đến việc xây dựng nền tảng vững chắc cho sinh viên, khuyến khích tư duy logic và khả năng giải quyết vấn đề một cách hệ thống. Cô Phương Vy tin rằng, việc hiểu rõ các nguyên lý cốt lõi là chìa khóa để trở thành một lập trình viên giỏi và thích nghi với mọi công nghệ mới.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor17@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Tran',
                'lastName' => 'Viet Hung',
                'role' => 'instructor',
                'biography' => 'Thầy **Trần Việt Hùng** là một chuyên gia về **Internet of Things (IoT)** và **Embedded Systems** với hơn **13 năm kinh nghiệm** trong việc thiết kế và phát triển các giải pháp phần cứng và phần mềm nhúng. Thầy đã từng làm việc cho các công ty sản xuất thiết bị thông minh và là người đứng đầu trong nhiều dự án IoT từ cấp độ cảm biến đến nền tảng đám mây. Chuyên môn của thầy bao gồm **Arduino, Raspberry Pi, ESP32**, lập trình C/C++, Python cho hệ thống nhúng, và các giao thức truyền thông IoT. Thầy Việt Hùng mang đến những kiến thức thực tế về cách thức xây dựng các thiết bị thông minh, kết nối chúng với internet và thu thập dữ liệu, giúp sinh viên có cái nhìn toàn diện về thế giới IoT đang phát triển nhanh chóng.',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor18@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Do',
                'lastName' => 'My Hanh',
                'role' => 'instructor',
                'biography' => 'Cô **Đỗ Thị Mỹ Hạnh** là một chuyên gia về **Quản trị cơ sở dữ liệu NoSQL** và **Distributed Systems** với hơn **9 năm kinh nghiệm** trong việc triển khai và tối ưu hóa các hệ thống dữ liệu phân tán hiệu suất cao. Cô đã làm việc với các cơ sở dữ liệu như **MongoDB, Cassandra, Redis** và các công nghệ liên quan. Cô Mỹ Hạnh có kiến thức sâu sắc về việc xử lý dữ liệu lớn, đảm bảo tính nhất quán và khả năng mở rộng của hệ thống trong môi trường phi quan hệ. Cô không chỉ giảng dạy về lý thuyết mà còn chia sẻ kinh nghiệm thực tế về cách thức lựa chọn cơ sở dữ liệu phù hợp, thiết kế lược đồ tối ưu và quản lý các hệ thống dữ liệu phân tán phức tạp.',
                'profileImage' => 'default'
            ]
        ];

        // Create 10 student accounts
        $students = [
            [
                'email' => 'student1@example.com',
                'password' => 'Student@123',
                'firstName' => 'Hoang',
                'lastName' => 'Minh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student2@example.com',
                'password' => 'Student@123',
                'firstName' => 'Phan',
                'lastName' => 'Anh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student3@example.com',
                'password' => 'Student@123',
                'firstName' => 'Do',
                'lastName' => 'Linh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student4@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vu',
                'lastName' => 'Trang',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student5@example.com',
                'password' => 'Student@123',
                'firstName' => 'Bui',
                'lastName' => 'Hai',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student6@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ngo',
                'lastName' => 'Thu',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student7@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dao',
                'lastName' => 'Long',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student8@example.com',
                'password' => 'Student@123',
                'firstName' => 'Duong',
                'lastName' => 'Lan',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student9@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dang',
                'lastName' => 'Quang',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student10@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dinh',
                'lastName' => 'Ha',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student11@example.com',
                'password' => 'Student@123',
                'firstName' => 'Le',
                'lastName' => 'Viet',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student12@example.com',
                'password' => 'Student@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Thi Mai',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student13@example.com',
                'password' => 'Student@123',
                'firstName' => 'Pham',
                'lastName' => 'Van Khoa',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student14@example.com',
                'password' => 'Student@123',
                'firstName' => 'Tran',
                'lastName' => 'Thi Thuy',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student15@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ly',
                'lastName' => 'Cong',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student16@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vo',
                'lastName' => 'Thi Kim',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student17@example.com',
                'password' => 'Student@123',
                'firstName' => 'Tran',
                'lastName' => 'Phuong',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student18@example.com',
                'password' => 'Student@123',
                'firstName' => 'Huynh',
                'lastName' => 'Van Duc',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student19@example.com',
                'password' => 'Student@123',
                'firstName' => 'Bui',
                'lastName' => 'Thi Diem',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student20@example.com',
                'password' => 'Student@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Kim Ngan',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student21@example.com',
                'password' => 'Student@123',
                'firstName' => 'Cao',
                'lastName' => 'Hoang',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student22@example.com',
                'password' => 'Student@123',
                'firstName' => 'Le',
                'lastName' => 'Hong',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student23@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dinh',
                'lastName' => 'Thi Yen',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student24@example.com',
                'password' => 'Student@123',
                'firstName' => 'Truong',
                'lastName' => 'Minh Tri',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student25@example.com',
                'password' => 'Student@123',
                'firstName' => 'Phan',
                'lastName' => 'Thi Cam',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student26@example.com',
                'password' => 'Student@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Duc Thang',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student27@example.com',
                'password' => 'Student@123',
                'firstName' => 'Tran',
                'lastName' => 'Anh Kiet',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student28@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vu',
                'lastName' => 'Cat Tuong',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student29@example.com',
                'password' => 'Student@123',
                'firstName' => 'Le',
                'lastName' => 'Thi Nguyet',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student30@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ho',
                'lastName' => 'Van Tai',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student31@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dang',
                'lastName' => 'Thanh Binh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student32@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ngo',
                'lastName' => 'Phuong Linh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student33@example.com',
                'password' => 'Student@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Trung Hieu',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student34@example.com',
                'password' => 'Student@123',
                'firstName' => 'Tran',
                'lastName' => 'Minh Duc',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student35@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vo',
                'lastName' => 'Minh Tam',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student36@example.com',
                'password' => 'Student@123',
                'firstName' => 'Phan',
                'lastName' => 'Duy Anh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student37@example.com',
                'password' => 'Student@123',
                'firstName' => 'Hoang',
                'lastName' => 'Mai Phuong',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student38@example.com',
                'password' => 'Student@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Hong Anh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student39@example.com',
                'password' => 'Student@123',
                'firstName' => 'Bui',
                'lastName' => 'Ngoc Tuan',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student40@example.com',
                'password' => 'Student@123',
                'firstName' => 'Tran',
                'lastName' => 'Thanh Son',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student41@example.com',
                'password' => 'Student@123',
                'firstName' => 'Le',
                'lastName' => 'Hai Dang',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student42@example.com',
                'password' => 'Student@123',
                'firstName' => 'Pham',
                'lastName' => 'Thi Ngoc Anh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student43@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dang',
                'lastName' => 'Hoang Nam',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student44@example.com',
                'password' => 'Student@123',
                'firstName' => 'Do',
                'lastName' => 'Trong Dat',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student45@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vu',
                'lastName' => 'Thi Thanh Xuan',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student46@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ngo',
                'lastName' => 'Duy Khanh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student47@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dao',
                'lastName' => 'Cong Danh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student48@example.com',
                'password' => 'Student@123',
                'firstName' => 'Duong',
                'lastName' => 'Quynh Nhu',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student49@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dang',
                'lastName' => 'Cong Hau',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student50@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dinh',
                'lastName' => 'Thi Quynh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student51@example.com',
                'password' => 'Student@123',
                'firstName' => 'Hoang',
                'lastName' => 'Manh Dat',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student52@example.com',
                'password' => 'Student@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Thi Thao',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student53@example.com',
                'password' => 'Student@123',
                'firstName' => 'Pham',
                'lastName' => 'Quoc Bao',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student54@example.com',
                'password' => 'Student@123',
                'firstName' => 'Tran',
                'lastName' => 'Van Khoi',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student55@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ly',
                'lastName' => 'Thi Ngoc',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student56@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vo',
                'lastName' => 'Dinh Khoa',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student57@example.com',
                'password' => 'Student@123',
                'firstName' => 'Tran',
                'lastName' => 'Thanh Hai',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student58@example.com',
                'password' => 'Student@123',
                'firstName' => 'Huynh',
                'lastName' => 'Thi Kim Chi',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student59@example.com',
                'password' => 'Student@123',
                'firstName' => 'Bui',
                'lastName' => 'Van Phuc',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student60@example.com',
                'password' => 'Student@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Thi Phuong Thao',
                'role' => 'student',
                'profileImage' => 'default'
            ]
        ];

        echo "Creating instructor accounts...\n";
        foreach ($instructors as $instructor) {
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

        echo "Creating student accounts...\n";
        foreach ($students as $student) {
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
        echo "User initialization completed!\n";

        if ($isGenerateInstructorSuccess && $isGenerateStudentSuccess) {
            ob_end_clean();
            header("Location: course_initializer.php");
            exit();
        } else {
            ob_end_flush();
        }
    }
}

$initializer = new UserInitializer();
$initializer->initialize();
