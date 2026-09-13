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

6. Open `http://localhost:8000/`. The root entry point redirects to the public homepage.

If PHP reports `could not find driver`, enable the installed `pdo_mysql` extension in `php.ini` and restart the PHP server.

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
- Seeker profile and CV content management, preview, removal, and browser print-to-PDF.
- Direct job quiz questions. Correct answers never render to the browser; PHP calculates and stores results.
- Application submission verifies a passing attempt for the same job and seeker.
- Failed quizzes recommend active courses linked to the job category.
- Course pages contain validated YouTube video IDs and manual completion tracking.
- Employer company profile, job creation/editing/deactivation, quiz question management, applicant review, and application status updates.
- Admin statistics, application reports, quiz reports, user activation, job moderation, categories, course editing/deactivation, category assignment, and video management.
- Responsive custom CSS with accessible focus states, mobile layouts, and print styles. Bootstrap and other UI frameworks are not used.

## Folder Structure

```text
admin/       Administrator dashboards and maintenance pages
assets/      Shared CSS, JavaScript, and images
config/      Local application and PDO configuration
database/    MySQL schema and fictional demonstration data
employer/    Company profile, jobs, quizzes, and applicants
includes/    Authentication, CSRF, helpers, and shared layout
public/      Public homepage, authentication, and job browsing
seeker/      Profile, CV, quizzes, applications, and courses
uploads/     Ignored local upload storage
```

## Database Overview

`users` owns the job seeker or employer profile. Employers create `jobs`; each job belongs to one category and has direct `quiz_questions`. `quiz_attempts` and `quiz_answers` preserve server-calculated results. `applications` stores the qualifying quiz attempt. Courses use the many-to-many `course_job_categories` mapping and own `course_videos`.

Important relationships:

- One employer has many jobs.
- One job has many quiz questions and attempts.
- One seeker has many CV records, attempts, applications, and course completions.
- One seeker can apply to a job only once and only with a passing attempt for that job.
- Courses and job categories have a many-to-many relationship.

## Security Notes

- Passwords use `password_hash()` and `password_verify()`.
- IDs and roles for protected operations come from the authenticated session, not submitted form data.
- CSRF is verified before database mutations.
- MySQL credentials belong only in ignored `config/config.php`.
- YouTube embeds are accepted only when the stored video ID matches the expected 11-character format.

## Viva Demonstration

1. Log in as a job seeker and open a job.
2. Submit its preliminary quiz and explain that PHP reads the correct answers and calculates the result.
3. Show that a pass permits an application, while a failure displays category-based courses.
4. Open a recommended course, switch videos, and mark it complete.
5. Show the CV builder, saved entries, preview, and print-to-PDF option.
6. Log in as an employer to create a job, add/edit quiz questions, and review applicants.
7. Log in as the administrator to show statistics, moderation, reports, categories, courses, and videos.

The main security point for the viva is that browser input is never trusted for identity, role, correct quiz answers, quiz score, or application eligibility.

## Future Improvements

- Profile image upload with MIME and size validation.
- Pagination, email notifications, audit logs, and automated integration tests.