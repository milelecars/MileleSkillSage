# MileleSkillSage: Test Management System

## 📋 Overview
MileleSkillSage is a secure, feature-rich online test management system built with Laravel 11. It provides a comprehensive platform for creating, conducting, and monitoring online assessments with advanced anti-cheating capabilities.

## 🌟 Key Features

### 🔐 Authentication & Security
- Dual authentication system for administrators and candidates
- Secure session management
- Token-based test invitations
- Protected file storage for test materials

### 📝 Test Management
- Excel/CSV-based question import
- Customizable test duration
- Invitation system with email notifications
- Real-time test monitoring
- Automatic test submission

### 🎯 Anti-Cheating Measures
- Tab switching detection
- Window blur monitoring
- TensorFlow.js integration for enhanced security
- Activity flagging system
- Test session tracking

### 📊 Results & Analytics
- Detailed performance metrics
- Score calculation and analysis
- Test completion status tracking
- Time-based analytics
- Individual candidate performance reports

## 🛠 Technology Stack

### Backend
- PHP 8.2
- Laravel 11
- PostgreSQL Database
- Livewire 3.5

### Frontend
- Tailwind CSS
- Alpine.js
- TensorFlow.js
- Vite

### Packages
- Laravel Excel (maatwebsite/excel)
- PHPMailer
- GeoIP

## ⚙️ Installation

1. Clone the repository
```bash
git clone https://github.com/milelecars/MileleSkillSage.git
cd MileleSkillSage
```

2. Install PHP dependencies
```bash
composer install
```

3. Install Node dependencies
```bash
npm install
```

4. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure database in `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations
```bash
php artisan migrate
```

7. Create storage link
```bash
php artisan storage:link
```

8. Build assets
```bash
npm run build
```

## 🏗 System Architecture

### Database Structure
- Users (Administrators)
- Candidates
- Tests
- Test Invitations
- Questions
- Test Results
- Monitoring Data

### File Storage
- Secure question file storage
- Protected test materials
- Public storage for non-sensitive assets

## 👥 Admin Roles

### Administrator
- Create and manage tests
- Import questions
- Generate test invitations
- Review results
- Manage candidates

### Candidate
- Access tests via invitation
- Passwordless authentication
- Take tests within time limits
- View results I'm performance 

## 🔒 Security Features

### Test Protection
- Secure file storage for test materials
- Encrypted test sessions
- Anti-tampering measures

### Monitoring
- Real-time activity tracking
- Suspicious behavior detection
- Session management
- Time tracking

## 📈 Test Features

### Creation
- Excel/CSV bulk question import
- Test duration settings
- Invitation email

### Execution
- Real-time progress tracking
- Auto-save functionality
- Time management
- Anti-cheating measures

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit changes
4. Push to the branch
5. Open a pull request

## ⚠️ Requirements
- PHP >= 8.2
- PostgreSQL
- Node.js
- Composer
- NPM/Yarn

