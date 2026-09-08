# Job Grooming & Placement Platform

A university Web Programming project that connects job seekers, employers, and administrators. Job seekers build CVs, complete server-scored preliminary quizzes, apply only after passing, and receive category-based courses after failed attempts.

## Technology

- PHP 8.2+ (compatible with PHP 8.5)
- MySQL 8+
- HTML5, CSS3, and vanilla JavaScript
- PDO prepared statements and PHP sessions

No frontend framework, PHP framework, Bootstrap, Tailwind, jQuery, or Node.js is used.

## Installation

1. Create a MySQL connection in MySQL Workbench.
2. Open and execute [database/web.sql](database/web.sql). It creates the `web` database and demo data.
3. Copy `config/config.example.php` to `config/config.php` if it does not already exist.
4. Set the local MySQL password in `config/config.php`. This file is ignored by Git.
5. Serve the project using Apache/XAMPP or PHP's built-in server from the project root:

```powershell
php -S localhost:8000
```

6. Open `http://localhost:8000/public/index.php`.

## Demo Accounts

All seeded accounts use password `DemoPass123!`.

| Role | Email |
| --- | --- |
| Admin | admin@demo.test |
| Job seeker | alex.seeker@demo.test |
| Employer | talent@northstar.demo |

## Main Features

- Role-based registration, login, logout, active-account checks, and sessions.
- CSRF tokens for state-changing forms; PDO prepared statements and escaped output.
- Server-filtered job search and job details.
- Seeker profile and CV content with browser print-to-PDF.
- Direct job quiz questions. Correct answers never render to the browser; PHP calculates and stores results.
- Application submission verifies a passing attempt for the same job and seeker.
- Failed quizzes recommend active courses linked to the job category.
- Course pages contain validated YouTube video IDs and manual completion tracking.
- Employer company profile, job creation/editing/deactivation, quiz management, applicant review, and application status updates.
- Admin statistics, user activation, job moderation, job categories, courses, and course-video management.

## Database Overview

`users` owns the job seeker or employer profile. Employers create `jobs`; each job belongs to one category and has direct `quiz_questions`. `quiz_attempts` and `quiz_answers` preserve server-calculated results. `applications` stores the qualifying quiz attempt. Courses use the many-to-many `course_job_categories` mapping and own `course_videos`.

## Security Notes

- Passwords use `password_hash()` and `password_verify()`.
- IDs and roles for protected operations come from the authenticated session, not submitted form data.
- CSRF is verified before database mutations.
- MySQL credentials belong only in ignored `config/config.php`.
- YouTube embeds are accepted only when the stored video ID matches the expected 11-character format.

## Future Improvements

- Profile image upload with MIME and size validation.
- Edit/delete controls for all CV entries, courses, and quiz questions.
- Pagination, email notifications, audit logs, and automated integration tests.