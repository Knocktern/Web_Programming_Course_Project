# Job Grooming & Placement Platform

A university Web Programming project that connects job seekers, employers, and administrators. Job seekers build CVs, complete server-scored preliminary quizzes, apply only after passing, and receive category-based courses after failed attempts.

## Four-member study guides

The full project is divided into four study responsibilities, balanced by complexity rather than raw line count. Each guide begins with concepts and the shared architecture, then explains every assigned source file in chunks with original line references, code, workflow connections, and viva questions.

1. [Member 1 — Foundations, authentication, and shared UI](docs/member-guides/member-1-foundations-and-ui.md)
2. [Member 2 — Jobseeker journey and skill search](docs/member-guides/member-2-jobseeker-and-search.md)
3. [Member 3 — Employer journey, interviews, and regression checks](docs/member-guides/member-3-employer-and-interviews.md)
4. [Member 4 — Administration, database design, and setup](docs/member-guides/member-4-admin-and-database.md)

These guides describe the code snapshot on 27 September 2026, including current limitations. They are a proposed learning/work allocation, not a record of past authorship. Actual local configuration credentials are excluded.

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

For a database created before interview scheduling was added, execute [database/add_interview_scheduling.sql](database/add_interview_scheduling.sql) once in MySQL Workbench.

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
- Server-filtered job search by title, category, location, employment type, and required skill badges such as C++, Python, and JavaScript.
- Seeker profile and CV content management, preview, removal, and browser print-to-PDF.
- Direct job quiz questions. Correct answers never render to the browser; PHP calculates and stores results.
- Application submission verifies a passing attempt for the same job and seeker.
- Failed quizzes recommend active courses linked to the job category.
- Course pages contain validated YouTube video IDs and manual completion tracking.
- Employer company profile, job creation/editing/deactivation, required skills, quiz question management, successful-applicant CV/profile review, application status updates, and online/office interview scheduling.
- Seekers can view interview dates, office addresses, online meeting links, and employer instructions on their dashboard and in My applications. Interview details stay visible and remain restricted to the application owner.
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

### Skill badge search

Open **Jobs → Search by skill** and select **Python**, **C++**, or another skill badge. The page shows only open jobs requiring that skill. The selected badge has a checkmark; click it again or choose **All skills** to remove the skill filter while keeping the other search filters. Badges on job cards are clickable too.

Badge counts show all open, unexpired jobs for that skill; additional search filters can reduce the result count. Skills added by employers appear automatically when their jobs are open. Common badges remain available with a zero count when no open jobs require them.

Badges occupy one horizontally scrollable row to keep the results close to the top. Open **More filters** for keyword, category, location, employment type, and workplace filters. These controls collapse after applying filters, and the selected skill is preserved. An applied-filter count and clear link remain visible.

For the viva: the badge sends a URL parameter such as `skill=C%2B%2B`. PHP uses a prepared `EXISTS` query against `job_required_skills` to match the skill. This uses the existing database schema and works without JavaScript.

1. Log in as a job seeker and open a job.
2. Submit its preliminary quiz and explain that PHP reads the correct answers and calculates the result.
3. Show that a pass permits an application, while a failure displays category-based courses.
4. Open a recommended course, switch videos, and mark it complete.
5. Show the CV builder, saved entries, preview, and print-to-PDF option.
6. Log in as an employer to create a job, add/edit quiz questions, and review applicants.
7. Log in as the administrator to show statistics, moderation, reports, categories, courses, and videos.

### Interview demonstration

1. As an employer, open **My jobs → Applicants → View profile, CV & interview**.
2. Choose online or office, enter a future date/time, provide a meeting link or full office address, and add candidate instructions.
3. Save the interview. Selecting Interview from the applicant status menu also opens this scheduling form, so a status change alone cannot create a new interview without details.
4. As the corresponding job seeker, open the dashboard or **My applications** to see the saved details and instructions.
5. Return as the employer to edit the schedule; the form is prefilled. Invalid entries show an error without clearing the form.

The implementation uses the existing `applications` columns and one shared PHP display function in `includes/interview-details.php`. No framework or additional database table is needed. This update requires no new ALTER script when the existing interview columns are present. Older installations should use the existing `database/add_interview_scheduling.sql` once; do not re-import `web.sql` into a database containing work you want to keep.

Interview times use the PHP server timezone, shown beside the date and on the scheduling form. Keep the server timezone consistent with the timezone used when scheduling interviews.

### Checks

`php tests/interview-flow.php` checks online/office saves, invalid input, seeker visibility, candidate instructions, safe meeting links, and the status-to-scheduling flow. It requires a configured local database with at least one application. It runs each case in a transaction and rolls back its changes, preserving existing application data.

The main security point for the viva is that browser input is never trusted for identity, role, correct quiz answers, quiz score, or application eligibility.

## Future Improvements

- Profile image upload with MIME and size validation.
- Pagination, email notifications, audit logs, and automated integration tests.
