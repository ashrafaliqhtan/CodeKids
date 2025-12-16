-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;


-- Create quizzes table
DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE `quizzes` (
  `quiz_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `quiz_title` varchar(255) NOT NULL,
  `quiz_description` text,
  `passing_score` int DEFAULT '70',
  `time_limit` int DEFAULT '30' COMMENT 'in minutes',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`quiz_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE
)  ;

-- Create quiz_questions table
DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE `quiz_questions` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer') NOT NULL,
  `points` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE
)  ;

-- Create quiz_options table
DROP TABLE IF EXISTS `quiz_options`;
CREATE TABLE `quiz_options` (
  `option_id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `quiz_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE
)  ;

-- Create quiz_results table
DROP TABLE IF EXISTS `quiz_results`;
CREATE TABLE `quiz_results` (
  `result_id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `student_id` int NOT NULL,
  `score` int NOT NULL,
  `total_score` int NOT NULL,
  `passed` tinyint(1) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_taken` int DEFAULT NULL COMMENT 'in seconds',
  PRIMARY KEY (`result_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `student_id` (`student_id`),
  KEY `idx_quiz_student` (`quiz_id`,`student_id`),
  CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`stu_id`) ON DELETE CASCADE
)  ;

-- Create student_answers table
DROP TABLE IF EXISTS `student_answers`;
CREATE TABLE `student_answers` (
  `answer_id` int NOT NULL AUTO_INCREMENT,
  `result_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_id` int DEFAULT NULL,
  `answer_text` text,
  `is_correct` tinyint(1) NOT NULL,
  `points_earned` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`answer_id`),
  KEY `result_id` (`result_id`),
  KEY `question_id` (`question_id`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `quiz_results` (`result_id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `quiz_options` (`option_id`) ON DELETE SET NULL
)  ;

-- Create admin_users table
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
)  ;

-- Create sessions table
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `session_id` varchar(128) NOT NULL,
  `expires` int unsigned NOT NULL,
  `data` text,
  PRIMARY KEY (`session_id`)
)  ;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;