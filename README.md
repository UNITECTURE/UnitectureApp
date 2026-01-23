# Unitecture - Employee Management System

<p align="center">
  <img src="public/images/logo.png" alt="Unitecture Logo" width="200">
</p>

<p align="center">
  A comprehensive HR and Employee Management System built with Laravel 11
</p>

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Biometric Integration](#biometric-integration)
- [User Roles](#user-roles)
- [API Endpoints](#api-endpoints)
- [Contributing](#contributing)
- [License](#license)

## 🎯 About

Unitecture is a modern, feature-rich Employee Management System designed to streamline HR operations, attendance tracking, leave management, and project coordination. The system integrates with biometric devices for automated attendance tracking and provides a comprehensive dashboard for administrators, supervisors, and employees.

## ✨ Features

### 👥 User Management
- Role-based access control (Super Admin, Admin, Supervisor, Employee)
- User profile management with profile images
- Hierarchical reporting structure
- Telegram integration for notifications

### ⏰ Attendance Management
- **Biometric Integration**: Automatic attendance tracking via eSSL biometric devices
- **Manual Attendance Requests**: Employees can request manual attendance entries
- **Approval Workflow**: Supervisors and admins can approve/reject manual requests
- **Attendance Reports**: Comprehensive attendance reports with export functionality
- **Daily Processing**: Automated daily attendance calculation at 11:00 PM

### 🏖️ Leave Management
- Leave request submission and tracking
- Leave balance tracking with automatic accrual
- Multi-level approval workflow (Supervisor → Admin)
- Leave reports and analytics
- Export functionality for leave data

### 📋 Task Management
- Task creation and assignment
- Task status tracking (Pending, In Progress, Completed)
- Task assignment to multiple team members
- Task filtering and search
- Project-based task organization

### 🏗️ Project Management
- Project creation and tracking
- Project status management
- Department-based project organization
- Project timeline tracking

### 🎉 Holiday Management
- Holiday calendar management
- Holiday-based leave calculations
- Admin-controlled holiday settings

### 📊 Reports & Analytics
- Attendance reports with filtering
- Leave reports and summaries
- Export to various formats
- Role-based report access

### 🔔 Notifications
- Telegram bot integration for real-time notifications
- Manual attendance request notifications
- Leave approval/rejection notifications

## 🛠️ Technology Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade Templates, Tailwind CSS 4.0, Alpine.js
- **Database**: MySQL/MariaDB
- **Build Tool**: Vite
- **Biometric Integration**: Python Bridge (for eSSL devices)
- **Notifications**: Telegram Bot API

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL/MariaDB
- Python 3.x (for biometric bridge)
- Git

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Unitecture-App
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 5. Database Setup

Update your `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=unitecture_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations:

```bash
php artisan migrate
```

### 6. Create Super Admin

Run the super admin creation script:

```bash
php create_super_admin.php
```

Default credentials:
- Email: `superadmin@unitecture.com`
- Password: `Unitecture@2026`

### 7. Build Assets

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

### 8. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## ⚙️ Configuration

### Biometric Device Configuration

Add your biometric device settings to `.env`:

```env
BIOMETRIC_DEVICE_IP=192.168.1.201
BIOMETRIC_DEVICE_PORT=4370
```

For detailed biometric integration instructions, see [BIOMETRIC_INTEGRATION.md](BIOMETRIC_INTEGRATION.md)

### Telegram Bot Configuration

To enable Telegram notifications, add to `.env`:

```env
TELEGRAM_BOT_TOKEN=your_telegram_bot_token
```

### Queue Configuration

For background job processing, configure your queue driver in `.env`:

```env
QUEUE_CONNECTION=database
```

Run the queue worker:

```bash
php artisan queue:work
```

### Scheduler Configuration

Add this to your crontab for scheduled tasks:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📖 Usage

### Login

1. Navigate to `/login`
2. Enter your email and password
3. You'll be redirected to the dashboard based on your role

### Dashboard

The dashboard provides role-specific views:
- **Super Admin/Admin**: Full system overview
- **Supervisor**: Team management and approvals
- **Employee**: Personal attendance and leave tracking

### Attendance

- View your attendance records
- Submit manual attendance requests
- Approve/reject requests (Supervisor/Admin)
- Export attendance reports

### Leave Management

- Submit leave requests
- Track leave balance
- Approve/reject leave requests (Supervisor/Admin)
- View leave reports

### Task Management

- Create and assign tasks
- Track task progress
- Filter tasks by status or project
- View assigned tasks

## 📁 Project Structure

```
Unitecture-App/
├── app/
│   ├── Channels/          # Notification channels (Telegram)
│   ├── Console/           # Artisan commands
│   ├── Http/
│   │   ├── Controllers/   # Application controllers
│   │   └── Middleware/    # Custom middleware
│   ├── Models/            # Eloquent models
│   ├── Notifications/     # Notification classes
│   └── Services/          # Business logic services
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── public/
│   ├── images/            # Static images (logo, etc.)
│   └── favicon.ico        # Application favicon
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── views/             # Blade templates
├── routes/
│   ├── web.php            # Web routes
│   └── console.php        # Scheduled commands
└── local_bridge/          # Python bridge for biometric devices
```

## 🔌 Biometric Integration

The system supports integration with eSSL biometric devices through a Python bridge. The bridge runs on a local PC connected to the biometric device and syncs attendance data to the cloud application.

### Setup Biometric Bridge

1. Navigate to `local_bridge/` directory
2. Install Python dependencies:
   ```bash
   pip install -r requirements.txt
   ```
3. Configure `bridge.py` with device IP and API URL
4. Run the bridge:
   ```bash
   python bridge.py
   ```

For detailed setup instructions, see [local_bridge/README.md](local_bridge/README.md)

## 👤 User Roles

### Super Admin (Role ID: 3)
- Full system access
- User management
- System configuration
- All reports and analytics

### Admin (Role ID: 2)
- User management
- Attendance and leave approvals
- Project and task management
- Reports and exports

### Supervisor (Role ID: 1)
- Team attendance and leave approvals
- Team task assignment
- Team reports
- Personal dashboard

### Employee (Role ID: 0)
- Personal attendance tracking
- Leave request submission
- Task viewing and updates
- Personal reports

## 🔗 API Endpoints

### Biometric Integration
- `POST /api/essl/attendance` - Receive attendance data from biometric device
- `GET /api/attendance/process` - Trigger attendance processing

### Authentication
- `GET /login` - Login page
- `POST /login` - Authenticate user
- `GET /logout` - Logout user

### Attendance
- `GET /employee/attendance` - Employee attendance view
- `GET /supervisor/attendance/team` - Supervisor team attendance
- `GET /admin/attendance/all` - Admin all attendance
- `POST /attendance/manual` - Submit manual attendance request
- `GET /attendance/export` - Export attendance data

### Leave Management
- `GET /leaves` - Leave requests
- `POST /leaves` - Submit leave request
- `PATCH /leaves/{id}/status` - Update leave status
- `GET /leaves/report` - Leave reports
- `GET /leaves/export` - Export leave data

### Task Management
- `GET /tasks` - All tasks
- `GET /tasks/assigned` - Assigned tasks
- `POST /tasks` - Create task
- `PATCH /tasks/{id}/status` - Update task status

## 🧪 Development

### Running Tests

```bash
php artisan test
```

### Code Style

The project uses Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

### Development Mode

Run all services concurrently:

```bash
composer run dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions, please contact the development team or create an issue in the repository.

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Alpine.js
- All contributors and team members

---

<p align="center">Built with ❤️ by the Unitecture Team</p>
