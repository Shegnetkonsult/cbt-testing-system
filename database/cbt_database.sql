
---

## 4️⃣ Database Schema (Sample – Put in `/database/cbt_database.sql`)

```sql
CREATE DATABASE cbt_system;
USE cbt_system;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('student','admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_title VARCHAR(100),
    duration INT COMMENT 'minutes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT,
    question TEXT,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_option CHAR(1),
    FOREIGN KEY (exam_id) REFERENCES exams(id)
);

CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    exam_id INT,
    score INT,
    total INT,
    taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
