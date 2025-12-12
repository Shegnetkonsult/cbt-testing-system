-- =====================================================
-- DATABASE: CBT TESTING SYSTEM
-- AUTHOR: Adigun Joseph
-- STACK: PHP + MySQL
-- =====================================================

CREATE DATABASE IF NOT EXISTS cbt_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cbt_system;

-- =====================================================
-- USERS TABLE (Students & Admins)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- EXAMS TABLE
-- =====================================================
CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_title VARCHAR(150) NOT NULL,
    description TEXT,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    total_questions INT NOT NULL,
    pass_mark INT DEFAULT 50,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- QUESTIONS TABLE
-- =====================================================
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('A','B','C','D') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id)
        ON DELETE CASCADE
);

-- =====================================================
-- STUDENT EXAM ATTEMPTS
-- =====================================================
CREATE TABLE exam_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    status ENUM('in_progress', 'completed', 'expired') DEFAULT 'in_progress',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (exam_id) REFERENCES exams(id)
);

-- =====================================================
-- STUDENT ANSWERS TABLE
-- =====================================================
CREATE TABLE student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option ENUM('A','B','C','D'),
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id)
        ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE
);

-- =====================================================
-- RESULTS TABLE
-- =====================================================
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage DECIMAL(5,2),
    result_status ENUM('pass', 'fail'),
    taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (exam_id) REFERENCES exams(id)
);

-- =====================================================
-- ADMIN ACTIVITY LOG (OPTIONAL BUT PROFESSIONAL)
-- =====================================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    activity TEXT NOT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_exam_status ON exams(status);
CREATE INDEX idx_attempt_user_exam ON exam_attempts(user_id, exam_id);
CREATE INDEX idx_result_user_exam ON results(user_id, exam_id);

-- =====================================================
-- DEFAULT ADMIN ACCOUNT (CHANGE PASSWORD AFTER LOGIN)
-- Password = admin123
-- =====================================================
INSERT INTO users (full_name, email, password, role)
VALUES (
    'System Administrator',
    'admin@cbt.local',
    '$2y$10$eImiTXuWVxfM37uY4JANjQ==',
    'admin'
);

