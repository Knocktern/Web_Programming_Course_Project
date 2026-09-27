-- Job Grooming & Placement Platform database schema
-- Import this file in MySQL Workbench. It creates and selects the `web` database.

CREATE DATABASE IF NOT EXISTS web
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE web;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS course_completions;
DROP TABLE IF EXISTS course_videos;
DROP TABLE IF EXISTS course_job_categories;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS quiz_answers;
DROP TABLE IF EXISTS quiz_attempts;
DROP TABLE IF EXISTS quiz_questions;
DROP TABLE IF EXISTS job_required_skills;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS languages;
DROP TABLE IF EXISTS certifications;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS experience;
DROP TABLE IF EXISTS education;
DROP TABLE IF EXISTS job_seeker_skills;
DROP TABLE IF EXISTS job_categories;
DROP TABLE IF EXISTS employers;
DROP TABLE IF EXISTS job_seekers;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'seeker', 'employer') NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE job_seekers (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    profile_summary TEXT NULL,
    profile_photo_path VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_seekers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE employers (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    logo_path VARCHAR(255) NULL,
    description TEXT NULL,
    industry VARCHAR(100) NULL,
    website VARCHAR(255) NULL,
    location VARCHAR(150) NULL,
    contact_name VARCHAR(150) NULL,
    contact_phone VARCHAR(30) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE job_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_job_categories_name (name),
    KEY idx_categories_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    responsibilities TEXT NULL,
    requirements_text TEXT NULL,
    employment_type ENUM('full_time', 'part_time', 'contract', 'internship', 'temporary') NOT NULL,
    workplace_type ENUM('on_site', 'hybrid', 'remote') NOT NULL,
    location VARCHAR(150) NOT NULL,
    salary_min DECIMAL(12,2) NULL,
    salary_max DECIMAL(12,2) NULL,
    vacancies SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    deadline DATE NOT NULL,
    minimum_passing_score DECIMAL(5,2) NOT NULL DEFAULT 60.00,
    status ENUM('draft', 'active', 'closed', 'deactivated') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jobs_employer FOREIGN KEY (employer_id) REFERENCES employers(user_id) ON DELETE RESTRICT,
    CONSTRAINT fk_jobs_category FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE RESTRICT,
    CONSTRAINT chk_jobs_salary_range CHECK (salary_min IS NULL OR salary_max IS NULL OR salary_min <= salary_max),
    CONSTRAINT chk_jobs_vacancies CHECK (vacancies > 0),
    CONSTRAINT chk_jobs_passing_score CHECK (minimum_passing_score >= 0 AND minimum_passing_score <= 100),
    KEY idx_jobs_browse (status, deadline, category_id),
    KEY idx_jobs_search (title, location),
    KEY idx_jobs_employer (employer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE job_required_skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_skills_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY uq_job_required_skill (job_id, skill_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE quiz_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(500) NOT NULL,
    option_b VARCHAR(500) NOT NULL,
    option_c VARCHAR(500) NOT NULL,
    option_d VARCHAR(500) NOT NULL,
    correct_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    marks DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_quiz_questions_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT chk_quiz_question_marks CHECK (marks > 0),
    UNIQUE KEY uq_quiz_question_order (job_id, display_order),
    UNIQUE KEY uq_quiz_question_job_id (job_id, id),
    KEY idx_quiz_questions_job (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE quiz_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    seeker_id BIGINT UNSIGNED NOT NULL,
    score DECIMAL(8,2) NOT NULL,
    total_marks DECIMAL(8,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    passed BOOLEAN NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quiz_attempts_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_attempts_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_quiz_attempt_score CHECK (score >= 0 AND total_marks > 0 AND score <= total_marks),
    CONSTRAINT chk_quiz_attempt_percentage CHECK (percentage >= 0 AND percentage <= 100),
    UNIQUE KEY uq_quiz_attempt_job_id (id, job_id),
    KEY idx_quiz_attempts_seeker_job (seeker_id, job_id, passed),
    KEY idx_quiz_attempts_job (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE quiz_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id BIGINT UNSIGNED NOT NULL,
    job_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    selected_option ENUM('A', 'B', 'C', 'D') NULL,
    is_correct BOOLEAN NOT NULL,
    marks_awarded DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quiz_answers_attempt FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_answers_question FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE RESTRICT,
    CONSTRAINT fk_quiz_answers_attempt_job FOREIGN KEY (attempt_id, job_id) REFERENCES quiz_attempts(id, job_id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_answers_question_job FOREIGN KEY (job_id, question_id) REFERENCES quiz_questions(job_id, id) ON DELETE RESTRICT,
    CONSTRAINT chk_quiz_answers_marks CHECK (marks_awarded >= 0),
    UNIQUE KEY uq_quiz_answer_question (attempt_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    seeker_id BIGINT UNSIGNED NOT NULL,
    qualifying_attempt_id BIGINT UNSIGNED NOT NULL,
    cover_letter TEXT NULL,
    status ENUM('submitted', 'under_review', 'shortlisted', 'interview', 'selected', 'rejected', 'withdrawn') NOT NULL DEFAULT 'submitted',
    interview_mode ENUM('online', 'office') NULL,
    interview_at DATETIME NULL,
    meeting_url VARCHAR(500) NULL,
    interview_location VARCHAR(255) NULL,
    interview_notes TEXT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE RESTRICT,
    CONSTRAINT fk_applications_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE RESTRICT,
    CONSTRAINT fk_applications_attempt FOREIGN KEY (qualifying_attempt_id) REFERENCES quiz_attempts(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_application_per_job (job_id, seeker_id),
    KEY idx_applications_job_status (job_id, status),
    KEY idx_applications_seeker (seeker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    objectives TEXT NULL,
    skills_covered TEXT NULL,
    difficulty ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner',
    duration_hours DECIMAL(6,2) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_courses_admin FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_courses_duration CHECK (duration_hours > 0),
    KEY idx_courses_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE course_job_categories (
    course_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (course_id, category_id),
    CONSTRAINT fk_course_categories_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_course_categories_category FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE course_videos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    youtube_video_id VARCHAR(30) NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_videos_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY uq_course_video_order (course_id, display_order),
    UNIQUE KEY uq_course_video_id (course_id, youtube_video_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE course_completions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    seeker_id BIGINT UNSIGNED NOT NULL,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_completions_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_course_completions_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    UNIQUE KEY uq_course_completion (course_id, seeker_id),
    KEY idx_course_completions_seeker (seeker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE job_seeker_skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seeker_id BIGINT UNSIGNED NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL DEFAULT 'beginner',
    CONSTRAINT fk_seeker_skills_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    UNIQUE KEY uq_seeker_skill (seeker_id, skill_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE education (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seeker_id BIGINT UNSIGNED NOT NULL,
    institution VARCHAR(180) NOT NULL,
    degree VARCHAR(150) NOT NULL,
    field_of_study VARCHAR(150) NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    result_gpa VARCHAR(50) NULL,
    CONSTRAINT fk_education_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_education_dates CHECK (start_date IS NULL OR end_date IS NULL OR start_date <= end_date),
    KEY idx_education_seeker (seeker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE experience (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seeker_id BIGINT UNSIGNED NOT NULL,
    company VARCHAR(180) NOT NULL,
    position_title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    CONSTRAINT fk_experience_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_experience_dates CHECK (end_date IS NULL OR start_date <= end_date),
    KEY idx_experience_seeker (seeker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seeker_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    technologies VARCHAR(500) NULL,
    project_url VARCHAR(255) NULL,
    CONSTRAINT fk_projects_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    KEY idx_projects_seeker (seeker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE certifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seeker_id BIGINT UNSIGNED NOT NULL,
    certification_name VARCHAR(180) NOT NULL,
    issuing_organization VARCHAR(180) NOT NULL,
    issue_date DATE NULL,
    credential_url VARCHAR(255) NULL,
    CONSTRAINT fk_certifications_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    KEY idx_certifications_seeker (seeker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE languages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seeker_id BIGINT UNSIGNED NOT NULL,
    language_name VARCHAR(100) NOT NULL,
    proficiency ENUM('basic', 'conversational', 'professional', 'native') NOT NULL,
    CONSTRAINT fk_languages_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(user_id) ON DELETE CASCADE,
    UNIQUE KEY uq_seeker_language (seeker_id, language_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- All demo accounts use the password: DemoPass123!
INSERT INTO users (id, email, password_hash, role) VALUES
    (1, 'admin@demo.test', '$2y$12$GmkuHvJ3ME1cUFqkFE8bo.EK7MQIAjwE2t0yaw2MXkmcQm42D6j1.', 'admin'),
    (2, 'alex.seeker@demo.test', '$2y$12$GmkuHvJ3ME1cUFqkFE8bo.EK7MQIAjwE2t0yaw2MXkmcQm42D6j1.', 'seeker'),
    (3, 'sam.seeker@demo.test', '$2y$12$GmkuHvJ3ME1cUFqkFE8bo.EK7MQIAjwE2t0yaw2MXkmcQm42D6j1.', 'seeker'),
    (4, 'priya.seeker@demo.test', '$2y$12$GmkuHvJ3ME1cUFqkFE8bo.EK7MQIAjwE2t0yaw2MXkmcQm42D6j1.', 'seeker'),
    (5, 'talent@northstar.demo', '$2y$12$GmkuHvJ3ME1cUFqkFE8bo.EK7MQIAjwE2t0yaw2MXkmcQm42D6j1.', 'employer'),
    (6, 'careers@pixelcraft.demo', '$2y$12$GmkuHvJ3ME1cUFqkFE8bo.EK7MQIAjwE2t0yaw2MXkmcQm42D6j1.', 'employer'),
    (7, 'hiring@dataforge.demo', '$2y$12$GmkuHvJ3ME1cUFqkFE8bo.EK7MQIAjwE2t0yaw2MXkmcQm42D6j1.', 'employer');

INSERT INTO job_seekers (user_id, full_name, phone, address, profile_summary) VALUES
    (2, 'Alex Morgan', '555-0101', 'Colombo', 'Entry-level web developer focused on accessible interfaces.'),
    (3, 'Sam Rivera', '555-0102', 'Kandy', 'Data graduate interested in analysis and visualization.'),
    (4, 'Priya Shah', '555-0103', 'Galle', 'Computer science student seeking practical software experience.');

INSERT INTO employers (user_id, company_name, description, industry, website, location, contact_name, contact_phone) VALUES
    (5, 'Northstar Systems', 'Fictional technology consultancy.', 'Information Technology', 'https://example.test/northstar', 'Colombo', 'Maya Chen', '555-0201'),
    (6, 'Pixelcraft Studio', 'Fictional product design studio.', 'Design', 'https://example.test/pixelcraft', 'Kandy', 'Jordan Lee', '555-0202'),
    (7, 'Dataforge Labs', 'Fictional data services company.', 'Data Services', 'https://example.test/dataforge', 'Avery Kim', '555-0203');

INSERT INTO job_categories (id, name, description) VALUES
    (1, 'Software Engineer', 'Software design, development, and testing.'),
    (2, 'Web Developer', 'Frontend and backend web development.'),
    (3, 'Data Analyst', 'Data cleaning, reporting, and visualization.'),
    (4, 'Data Scientist', 'Statistical modeling and machine learning.'),
    (5, 'Network Engineer', 'Network infrastructure and operations.'),
    (6, 'DevOps Engineer', 'Deployment automation and infrastructure operations.'),
    (7, 'Cybersecurity Analyst', 'Security monitoring and incident response.'),
    (8, 'UI/UX Designer', 'User research and interface design.'),
    (9, 'Mobile App Developer', 'Mobile application development.'),
    (10, 'Database Administrator', 'Database performance, reliability, and security.');

INSERT INTO jobs (id, employer_id, category_id, title, description, responsibilities,requirements_text, employment_type, workplace_type, location, salary_min, salary_max, vacancies, deadline, minimum_passing_score, status) VALUES
    (1, 5, 1, 'Junior Software Engineer', 'Build and test web platform features.', 'Write maintainable PHP and JavaScript.', 'Basic programming and SQL knowledge.', 'full_time', 'hybrid', 'Colombo', 65000, 85000, 2, '2027-01-31', 60, 'active'),
    (2, 5, 2, 'Frontend Web Developer Intern', 'Support responsive web interface delivery.', 'Implement accessible HTML, CSS, and JavaScript.', 'Portfolio or coursework in web development.', 'internship', 'remote', 'Sri Lanka', 30000, 40000, 2, '2027-02-15', 60, 'active'),
    (3, 7, 3, 'Junior Data Analyst', 'Prepare reports from operational data.', 'Create SQL queries and dashboards.', 'Excel and SQL fundamentals.', 'full_time', 'on_site', 'Colombo', 70000, 95000, 1, '2027-02-28', 65, 'active'),
    (4, 7, 4, 'Data Science Trainee', 'Assist with data preparation and models.', 'Explore and clean datasets.', 'Python and statistics basics.', 'internship', 'hybrid', 'Colombo', 35000, 50000, 1, '2027-03-15', 60, 'active'),
    (5, 5, 5, 'Network Support Engineer', 'Maintain office network services.', 'Troubleshoot connectivity issues.', 'Networking fundamentals.', 'full_time', 'on_site', 'Galle', 60000, 80000, 1, '2027-02-20', 60, 'active'),
    (6, 5, 6, 'DevOps Engineer Intern', 'Support CI/CD and cloud operations.', 'Assist with deployment automation.', 'Linux and Git basics.', 'internship', 'hybrid', 'Colombo', 35000, 50000, 1, '2027-03-01', 60, 'active'),
    (7, 5, 7, 'Cybersecurity Analyst Intern', 'Support security monitoring tasks.', 'Review security events.', 'Security fundamentals.', 'internship', 'on_site', 'Colombo', 35000, 50000, 1, '2027-03-10', 70, 'active'),
    (8, 6, 8, 'Junior UI/UX Designer', 'Create user-centered product interfaces.', 'Produce wireframes and prototypes.', 'Portfolio and design fundamentals.', 'full_time', 'hybrid', 'Kandy', 65000, 90000, 1, '2027-02-25', 65, 'active'),
    (9, 6, 9, 'Mobile App Developer Intern', 'Help build mobile product features.', 'Implement and test application screens.', 'Mobile development coursework.', 'internship', 'remote', 'Sri Lanka', 30000, 45000, 2, '2027-03-05', 60, 'active'),
    (10, 7, 10, 'Database Administrator Trainee', 'Support reliable database operations.', 'Assist with backups and query tuning.', 'SQL fundamentals.', 'full_time', 'on_site', 'Colombo', 65000, 85000, 1, '2027-03-20', 65, 'active');

INSERT INTO job_required_skills (job_id, skill_name) VALUES
    (1, 'PHP'), (1, 'MySQL'), (1, 'JavaScript'), (2, 'HTML'), (2, 'CSS'), (2, 'JavaScript'),
    (3, 'SQL'), (3, 'Excel'), (4, 'Python'), (4, 'Statistics'), (5, 'TCP/IP'), (5, 'Routing'),
    (6, 'Git'), (6, 'Linux'), (7, 'Security Monitoring'), (7, 'Networking'), (8, 'Figma'), (8, 'User Research'),
    (9, 'Mobile Development'), (9, 'JavaScript'), (10, 'SQL'), (10, 'Database Backup');

INSERT INTO courses (id, title, description, objectives, skills_covered, difficulty, duration_hours, created_by) VALUES
    (1, 'Software Engineering Foundations', 'Learn core programming practices.', 'Write readable and tested code.', 'Programming, Git, testing', 'beginner', 12, 1),
    (2, 'Complete Web Development Fundamentals', 'Build responsive websites with web fundamentals.', 'Create accessible web pages.', 'HTML, CSS, JavaScript, PHP, MySQL', 'beginner', 16, 1),
    (3, 'Practical Data Analysis', 'Turn raw data into clear insights.', 'Query and visualize data.', 'SQL, Excel, reporting', 'beginner', 10, 1),
    (4, 'Data Science Essentials', 'Explore the foundations of data science.', 'Prepare datasets and evaluate models.', 'Python, statistics, machine learning', 'intermediate', 14, 1),
    (5, 'Networking Fundamentals', 'Understand network protocols and devices.', 'Diagnose common network issues.', 'TCP/IP, routing, switching', 'beginner', 10, 1),
    (6, 'DevOps Fundamentals', 'Introduce modern delivery practices.', 'Use version control and CI/CD concepts.', 'Git, Linux, CI/CD', 'beginner', 12, 1),
    (7, 'Cybersecurity Basics', 'Learn defensive security concepts.', 'Recognize common threats.', 'Security monitoring, networking', 'beginner', 12, 1),
    (8, 'UI/UX Design Fundamentals', 'Design usable digital interfaces.', 'Create wireframes and validate ideas.', 'Figma, user research, prototyping', 'beginner', 11, 1),
    (9, 'Mobile App Development Basics', 'Learn the mobile development lifecycle.', 'Build and test mobile interfaces.', 'Mobile development, app testing', 'beginner', 14, 1),
    (10, 'Database Administration Fundamentals', 'Operate relational databases reliably.', 'Back up, secure, and tune databases.', 'SQL, backups, performance', 'intermediate', 13, 1);

INSERT INTO course_job_categories (course_id, category_id) VALUES
    (1, 1), (2, 2), (3, 3), (4, 4), (5, 5), (6, 6), (7, 7), (8, 8), (9, 9), (10, 10);

INSERT INTO course_videos (course_id, title, youtube_video_id, display_order) VALUES
    (1, 'Programming Fundamentals', 'zOjov-2OZ0E', 1),
    (2, 'HTML Fundamentals', 'qz0aGYrrlhU', 1), (2, 'CSS Fundamentals', '1Rs2ND1ryYc', 2), (2, 'JavaScript Fundamentals', 'PkZNo7MFNFg', 3),
    (3, 'SQL Basics', 'HXV3zeQKqGY', 1), (4, 'Python for Beginners', 'rfscVS0vtbw', 1),
    (5, 'Networking Basics', 'qiQR5rTSshw', 1), (6, 'Git Basics', 'RGOj5yH7evk', 1),
    (7, 'Cybersecurity Basics', 'inWWhr5tnEA', 1), (8, 'UX Design Basics', 'Ovj4hFxko7c', 1),
    (9, 'Mobile Development Overview', '0-S5a0eXPoc', 1), (10, 'Database Fundamentals', '7S_tz1z_5bA', 1);

-- Each job has direct quiz questions. Correct answers are only returned to server-side scoring queries.
INSERT INTO quiz_questions (job_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks, display_order) VALUES
    (1, 'Which SQL command retrieves data?', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'A', 1, 1),
    (1, 'Which PHP feature prevents SQL injection when used correctly?', 'Prepared statement', 'Cookie', 'CSS class', 'Session name', 'A', 1, 2),
    (2, 'Which HTML element provides the main page heading?', 'h1', 'div', 'span', 'footer', 'A', 1, 1),
    (2, 'Which CSS layout system is two-dimensional?', 'Flexbox', 'Grid', 'Float', 'Position absolute', 'B', 1, 2),
    (3, 'Which SQL clause filters rows?', 'ORDER BY', 'GROUP BY', 'WHERE', 'JOIN', 'C', 1, 1),
    (3, 'Which chart is useful for comparing categories?', 'Bar chart', 'Scatter plot only', 'Map only', 'Text area', 'A', 1, 2),
    (4, 'Which language is commonly used for data science?', 'Python', 'HTML', 'CSS', 'SQL only', 'A', 1, 1),
    (5, 'What does IP stand for?', 'Internet Protocol', 'Internal Port', 'Input Program', 'Internet Process', 'A', 1, 1),
    (6, 'What does CI mean in DevOps?', 'Continuous Integration', 'Code Inspection', 'Cloud Input', 'Central Internet', 'A', 1, 1),
    (7, 'What is phishing?', 'A social engineering attack', 'A backup method', 'A firewall rule', 'A database query', 'A', 1, 1),
    (8, 'What is a wireframe?', 'A low-detail interface layout', 'A database schema', 'A source control branch', 'A server log', 'A', 1, 1),
    (9, 'What should mobile layouts account for?', 'Different screen sizes', 'Only desktop monitors', 'Printer ink', 'Database engines', 'A', 1, 1),
    (10, 'Which action helps protect database data?', 'Regular backups', 'Removing indexes', 'Sharing passwords', 'Disabling access control', 'A', 1, 1);

INSERT INTO job_seeker_skills (seeker_id, skill_name, skill_level) VALUES
    (2, 'HTML', 'intermediate'), (2, 'CSS', 'intermediate'), (2, 'JavaScript', 'beginner'),
    (3, 'SQL', 'intermediate'), (3, 'Excel', 'intermediate'), (4, 'PHP', 'beginner');

INSERT INTO education (seeker_id, institution, degree, field_of_study, start_date, end_date, result_gpa) VALUES
    (2, 'Fictional City University', 'BSc', 'Information Technology', '2023-01-01', '2026-12-31', '3.4/4.0'),
    (3, 'Fictional City University', 'BSc', 'Data Analytics', '2022-01-01', '2025-12-31', '3.6/4.0'),
    (4, 'Fictional Institute of Technology', 'BSc', 'Computer Science', '2023-01-01', '2027-12-31', '3.5/4.0');

INSERT INTO experience (seeker_id, company, position_title, description, start_date, end_date) VALUES
    (2, 'Sample Digital Agency', 'Web Development Volunteer', 'Built event information pages.', '2025-06-01', '2025-10-01'),
    (3, 'Sample Research Office', 'Data Assistant', 'Prepared survey reports.', '2025-01-01', '2025-08-01');

INSERT INTO projects (seeker_id, title, description, technologies, project_url) VALUES
    (2, 'Campus Events Portal', 'A fictional event listing portal.', 'HTML, CSS, JavaScript', 'https://example.test/campus-events'),
    (3, 'Sales Report Dashboard', 'A fictional monthly sales report.', 'SQL, Excel', 'https://example.test/sales-report');

INSERT INTO certifications (seeker_id, certification_name, issuing_organization, issue_date, credential_url) VALUES
    (2, 'Web Foundations Certificate', 'Sample Learning Institute', '2025-11-01', 'https://example.test/web-certificate'),
    (3, 'SQL Fundamentals Certificate', 'Sample Learning Institute', '2025-09-01', 'https://example.test/sql-certificate');

INSERT INTO languages (seeker_id, language_name, proficiency) VALUES
    (2, 'English', 'professional'), (2, 'Sinhala', 'native'), (3, 'English', 'professional'), (4, 'English', 'professional');

-- Alex has a qualifying attempt and application for the web developer role.
INSERT INTO quiz_attempts (id, job_id, seeker_id, score, total_marks, percentage, passed) VALUES
    (1, 2, 2, 2, 2, 100.00, TRUE),
    (2, 3, 3, 1, 2, 50.00, FALSE);

INSERT INTO quiz_answers (attempt_id, job_id, question_id, selected_option, is_correct, marks_awarded) VALUES
    (1, 2, 3, 'A', TRUE, 1), (1, 2, 4, 'B', TRUE, 1),
    (2, 3, 5, 'C', TRUE, 1), (2, 3, 6, 'B', FALSE, 0);

INSERT INTO applications (job_id, seeker_id, qualifying_attempt_id, cover_letter, status) VALUES
    (2, 2, 1, 'I am eager to contribute to accessible web experiences.', 'under_review');

INSERT INTO course_completions (course_id, seeker_id) VALUES
    (3, 3);