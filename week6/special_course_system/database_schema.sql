-- ============================================================
-- ระบบขอเปิดหมู่เรียนพิเศษ — Database Schema
-- Engine: MySQL 8+ / MariaDB 10.4+
-- Charset: utf8mb4 (รองรับภาษาไทยและ Emoji)
-- ============================================================

CREATE DATABASE IF NOT EXISTS special_course_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE special_course_db;

-- ----------------------------------------------------------
-- 1. students
-- ----------------------------------------------------------
CREATE TABLE students (
    student_id       VARCHAR(20)  NOT NULL,
    student_prefix   VARCHAR(10)  NOT NULL,
    student_firstname VARCHAR(50) NOT NULL,
    student_lastname  VARCHAR(50) NOT NULL,
    student_email    VARCHAR(100) NOT NULL,
    student_phone    VARCHAR(20)  DEFAULT NULL,
    faculty          VARCHAR(100) NOT NULL,
    department       VARCHAR(100) DEFAULT NULL,
    major            VARCHAR(100) DEFAULT NULL,
    study_year       TINYINT UNSIGNED NOT NULL,
    student_type     ENUM('regular','evening','weekend') NOT NULL DEFAULT 'regular',
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (student_id),
    UNIQUE  KEY uq_student_email (student_email),
    INDEX   idx_faculty (faculty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. instructors
-- ----------------------------------------------------------
CREATE TABLE instructors (
    instructor_id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    instructor_prefix    VARCHAR(10)  NOT NULL,
    instructor_firstname VARCHAR(50)  NOT NULL,
    instructor_lastname  VARCHAR(50)  NOT NULL,
    instructor_email     VARCHAR(100) NOT NULL,
    instructor_phone     VARCHAR(20)  DEFAULT NULL,
    department           VARCHAR(100) DEFAULT NULL,
    position             VARCHAR(50)  DEFAULT NULL,
    status               ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (instructor_id),
    UNIQUE  KEY uq_instructor_email (instructor_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. courses
-- ----------------------------------------------------------
CREATE TABLE courses (
    course_code     VARCHAR(20)  NOT NULL,
    course_name_th  VARCHAR(200) NOT NULL,
    course_name_en  VARCHAR(200) DEFAULT NULL,
    credit_theory   DECIMAL(3,1) NOT NULL DEFAULT 0,
    credit_practice DECIMAL(3,1) NOT NULL DEFAULT 0,
    credit_self     DECIMAL(3,1) NOT NULL DEFAULT 0,
    department      VARCHAR(100) DEFAULT NULL,
    faculty         VARCHAR(100) DEFAULT NULL,
    course_level    ENUM('undergraduate','graduate') NOT NULL DEFAULT 'undergraduate',
    status          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (course_code),
    INDEX idx_course_name_th (course_name_th(50)),
    INDEX idx_faculty (faculty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. request_status  (lookup / reference table)
-- ----------------------------------------------------------
CREATE TABLE request_status (
    status_id      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    status_code    VARCHAR(20)      NOT NULL,
    status_name_th VARCHAR(50)      NOT NULL,
    status_name_en VARCHAR(50)      DEFAULT NULL,
    status_color   VARCHAR(20)      NOT NULL DEFAULT '#6c757d',
    sort_order     TINYINT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (status_id),
    UNIQUE KEY uq_status_code (status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO request_status
    (status_code, status_name_th, status_name_en, status_color, sort_order)
VALUES
    ('PENDING',   'รอดำเนินการ',    'Pending',   '#ffc107', 1),
    ('REVIEWING', 'กำลังตรวจสอบ',   'Reviewing', '#17a2b8', 2),
    ('APPROVED',  'อนุมัติ',        'Approved',  '#28a745', 3),
    ('REJECTED',  'ไม่อนุมัติ',     'Rejected',  '#dc3545', 4),
    ('CANCELLED', 'ยกเลิก',         'Cancelled', '#6c757d', 5);

-- ----------------------------------------------------------
-- 5. special_course_requests  (main table)
-- ----------------------------------------------------------
CREATE TABLE special_course_requests (
    request_id             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    request_number         VARCHAR(30)    NOT NULL,
    student_id             VARCHAR(20)    NOT NULL,
    course_code            VARCHAR(20)    NOT NULL,
    instructor_id          INT UNSIGNED   DEFAULT NULL,
    status_id              TINYINT UNSIGNED NOT NULL DEFAULT 1,
    semester               TINYINT UNSIGNED NOT NULL COMMENT '1, 2, หรือ 3',
    academic_year          SMALLINT UNSIGNED NOT NULL COMMENT 'ปีการศึกษา (พ.ศ.)',
    reason                 TEXT           NOT NULL,
    expected_students      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    is_in_regular_schedule TINYINT(1)     NOT NULL DEFAULT 0,
    request_date           DATE           NOT NULL,
    review_date            DATETIME       DEFAULT NULL,
    reviewed_by            VARCHAR(100)   DEFAULT NULL,
    review_notes           TEXT           DEFAULT NULL,
    approval_date          DATETIME       DEFAULT NULL,
    section_number         VARCHAR(10)    DEFAULT NULL,
    created_at             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (request_id),
    UNIQUE  KEY uq_request_number (request_number),
    INDEX   idx_student   (student_id),
    INDEX   idx_course    (course_code),
    INDEX   idx_status    (status_id),
    INDEX   idx_semester  (semester, academic_year),
    INDEX   idx_req_date  (request_date),

    CONSTRAINT fk_scr_student
        FOREIGN KEY (student_id)    REFERENCES students(student_id)        ON UPDATE CASCADE,
    CONSTRAINT fk_scr_course
        FOREIGN KEY (course_code)   REFERENCES courses(course_code)        ON UPDATE CASCADE,
    CONSTRAINT fk_scr_instructor
        FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id)  ON DELETE SET NULL,
    CONSTRAINT fk_scr_status
        FOREIGN KEY (status_id)     REFERENCES request_status(status_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. request_logs  (audit trail)
-- ----------------------------------------------------------
CREATE TABLE request_logs (
    log_id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    request_id         INT UNSIGNED  NOT NULL,
    action_type        VARCHAR(50)   NOT NULL,
    action_description TEXT          DEFAULT NULL,
    performed_by       VARCHAR(100)  DEFAULT NULL,
    performed_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address         VARCHAR(45)   DEFAULT NULL,
    user_agent         TEXT          DEFAULT NULL,

    PRIMARY KEY (log_id),
    INDEX idx_request     (request_id),
    INDEX idx_performed_at(performed_at),

    CONSTRAINT fk_log_request
        FOREIGN KEY (request_id) REFERENCES special_course_requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. View: vw_special_course_requests
-- ----------------------------------------------------------
CREATE OR REPLACE VIEW vw_special_course_requests AS
SELECT
    scr.request_id,
    scr.request_number,
    scr.student_id,
    CONCAT(s.student_prefix, ' ', s.student_firstname, ' ', s.student_lastname) AS student_name,
    s.student_email,
    s.faculty,
    s.study_year,
    scr.course_code,
    c.course_name_th,
    c.credit_theory,
    c.credit_practice,
    CONCAT(c.credit_theory, '-', c.credit_practice, '-', c.credit_self)         AS credits,
    scr.instructor_id,
    CONCAT(i.instructor_prefix, ' ', i.instructor_firstname, ' ', i.instructor_lastname) AS instructor_name,
    scr.status_id,
    rs.status_code,
    rs.status_name_th,
    rs.status_color,
    scr.semester,
    scr.academic_year,
    scr.reason,
    scr.expected_students,
    scr.is_in_regular_schedule,
    scr.request_date,
    scr.review_date,
    scr.reviewed_by,
    scr.review_notes,
    scr.approval_date,
    scr.section_number,
    scr.created_at,
    scr.updated_at
FROM special_course_requests scr
LEFT JOIN students       s  ON scr.student_id    = s.student_id
LEFT JOIN courses        c  ON scr.course_code   = c.course_code
LEFT JOIN instructors    i  ON scr.instructor_id = i.instructor_id
LEFT JOIN request_status rs ON scr.status_id     = rs.status_id
ORDER BY scr.created_at DESC;
