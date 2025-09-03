# CoursePro1 - E-Learning Platform

A comprehensive, full-stack e-learning platform built with PHP, MySQL/Oracle, and modern web technologies. CoursePro1 provides a complete solution for online education with features for students, instructors, and administrators.

## 🚀 Features

### For Students
- **Course Discovery**: Browse and search through various course categories
- **Video Learning**: Watch course videos with streaming capabilities
- **Progress Tracking**: Monitor learning progress and achievements
- **Certificate System**: Earn certificates upon course completion
- **Shopping Cart**: Add courses to cart and manage purchases
- **Purchase History**: Track all course transactions

### For Instructors
- **Course Management**: Create, edit, and manage course content
- **Video Upload**: Upload and manage course videos with streaming
- **Content Organization**: Structure courses with chapters and lessons
- **Student Analytics**: Monitor student progress and engagement
- **Resource Management**: Upload and manage course materials

### For Administrators
- **User Management**: Manage students, instructors, and admin accounts
- **Course Oversight**: Monitor and approve course content
- **Revenue Tracking**: Track sales and revenue analytics
- **System Administration**: Manage platform settings and configurations

## 🛠️ Technology Stack

### Backend
- **PHP 8.0+**: Core application logic
- **MySQL/Oracle**: Database management with dual compatibility
- **RESTful APIs**: Comprehensive API endpoints for all functionality
- **JWT Authentication**: Secure user authentication and authorization

### Frontend
- **HTML5/CSS3**: Modern, responsive web design
- **JavaScript**: Interactive user experience
- **Bootstrap 5**: Responsive UI framework
- **Font Awesome**: Icon library
- **Swiper.js**: Touch-enabled sliders

### Additional Technologies
- **FFmpeg**: Video processing and streaming
- **PHPMailer**: Email functionality
- **Python Integration**: AI-powered recommendation system and image processing
- **Apache/Nginx**: Web server support

## Project Structure


## 🚀 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0+ or Oracle Database
- Apache/Nginx web server
- Composer (for PHP dependencies)
- Python 3.8+ (for AI features)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/CoursePro1.git
   cd CoursePro1
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Python dependencies**
   ```bash
   cd api
   pip install -r requirements.txt
   cd ..
   ```

4. **Database setup**
   - Import the schema from `model/schema.sql`
   - Configure database connection in `config.php`

5. **Configure web server**
   - Point document root to the project directory
   - Ensure proper permissions for uploads and videos

6. **Environment configuration**
   - Update `config.php` with your server settings
   - Configure email settings for PHPMailer

## Configuration

### Database Configuration
The platform supports both MySQL and Oracle databases. Update the configuration in `config.php`:

```php
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'coursepro1');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Email Configuration
Configure PHPMailer settings for email functionality:

```php
// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password');
```

## 🧪 Testing

Run the test suite using PHPUnit:

```bash
./vendor/bin/phpunit
```

The project includes comprehensive tests for:
- API endpoints
- Business logic
- Database operations
- User authentication

## 📊 Database Schema

The platform uses a comprehensive database design with:
- **User Management**: Users, roles, and authentication
- **Course System**: Courses, chapters, lessons, and videos
- **E-commerce**: Shopping cart, orders, and payments
- **Learning Analytics**: Progress tracking and certificates
- **Content Management**: Categories, resources, and reviews

## 🔒 Security Features

- **JWT Authentication**: Secure token-based authentication
- **Password Hashing**: Bcrypt password encryption
- **SQL Injection Prevention**: Prepared statements and parameterized queries
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Cross-site request forgery prevention

## 🌟 Key Features

### AI-Powered Recommendations
- Python-based recommendation system
- Content-based filtering
- Personalized course suggestions

### Video Streaming
- Adaptive video streaming
- Multiple video format support
- Efficient video delivery

### Multi-language Support
- Vietnamese and English interface
- Localized content management
- Internationalization ready

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is released under the MIT License.
