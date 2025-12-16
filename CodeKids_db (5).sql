

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `category` enum('programming','design','research','presentation') NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in hours',
  `lessons` int(11) NOT NULL DEFAULT '0',
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `language` varchar(50) DEFAULT 'ط§ظ„ط¹ط±ط¨ظٹط©',
  `instructor_id` int(11) NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `instructor_img` varchar(255) DEFAULT NULL,
  `students` int(11) DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `views` int(11) NOT NULL DEFAULT '0',
  `reviews` int(11) NOT NULL DEFAULT '0',
  `total_lessons` int(11) DEFAULT '0',
  `total_sections` int(11) DEFAULT '0',
  `requirements` text,
  `what_you_learn` text,
  `certificate_included` tinyint(1) DEFAULT '1',
  `featured` tinyint(1) DEFAULT '0',
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;


ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_courses_category` (`category`),
  ADD KEY `idx_courses_instructor` (`instructor_id`);


ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


