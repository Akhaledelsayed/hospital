SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- notifications table
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `recipient` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- inserting into notifications
INSERT INTO `notifications` (`id`, `message`, `recipient`, `type`, `date`, `is_read`) VALUES
(1, '\'Customer Feedback Survey Analysis\' has been assigned to you. Please review and start working on it.', 7, 'New Task Assigned', '2024-09-05', 1),
(2, '\'test task\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '0000-00-00', 1),
(3, '\'Example task 2\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2006-09-24', 1),
(4, '\'test\' has been assigned to you. Please review and start working on it', 8, 'New Task Assigned', '2009-06-24', 0),
(5, '\'test task 3\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(6, '\'Prepare monthly sales report\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(7, '\'Update client database\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 1),
(8, '\'Fix server downtime issue\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(9, '\'Plan annual marketing strategy\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(10, '\'Onboard new employees\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(11, '\'Design new company website\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(12, '\'Conduct software testing\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(13, '\'Schedule team meeting\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0),
(14, '\'Prepare budget for Q4\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(15, '\'Write blog post on industry trend\' has been assigned to you. Please review and start working on it', 7, 'New Task Assigned', '2024-09-06', 0),
(16, '\'Renew software license\' has been assigned to you. Please review and start working on it', 2, 'New Task Assigned', '2024-09-06', 0);

-- projects table
CREATE TABLE projects (
  id int(11) NOT NULL ,
  project_name varchar(100) NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 

-- users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL ,
  `full_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- tasks table
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text  NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `deadline` date NOT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- sheet_data_3 table
CREATE TABLE `sheet_data_3` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL,
    `day` VARCHAR(20) NOT NULL,
    `project_name` VARCHAR(255) NOT NULL,
    `ten_am` VARCHAR(255) NOT NULL,
    `eleven_am` VARCHAR(255) NOT NULL,
    `twelve_pm` VARCHAR(255) NOT NULL,
    `one_pm` VARCHAR(255) NOT NULL,
    `two_pm` VARCHAR(255) NOT NULL,
    `three_pm` VARCHAR(255) NOT NULL,
    `four_pm` VARCHAR(255) NOT NULL,
    `five_pm` VARCHAR(255) NOT NULL,
    `six_pm` VARCHAR(255) NOT NULL,
    `notes` TEXT,
    `user_name` INT(11) NOT NULL, -- Added user_name column
    CONSTRAINT `fk_user_name` FOREIGN KEY (`user_name`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert users data
INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Dr\ Khaled', 'khaled', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(2, 'Dr\ Hanaa', 'hanaa', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(3, 'M\ Mohamad', 'Mmohaned', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(4, 'admin', 'admin', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'admin', '2024-08-28 07:10:40'),
(5, 'Eng\ Mohamad reda', 'Emohaned', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(11, 'Asmaa Amin', 'asmaa', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(12, 'Eman Abd.ALfattah', 'eman', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(14, 'Lamia Ahmed', 'lamia', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(16, 'Shrouk Al.Fedawy', 'shrouk', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(17, 'Soha Hisham', 'soha', '$2y$10$hD.G7YIFuj2xZPPuX8fQIO/6z8U.wncS5lGjqLwzDoHJ6qU1M5JTC', 'employee', '2024-08-28 07:10:40'),
(18, 'Mohammed Hammam', 'mohamed', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(19, 'Nada Hussien', 'nada', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(20, 'Ahmed Gamal Abd-Alaziz', 'ahmed', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(21, 'Amgad Sallam', 'amged', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(22, 'Amira Hasanin', 'amira', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(23, 'Raneem Reda', 'raneem', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(24, 'Dina Hamed', 'dina', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(25, 'Rehab Mostafa', 'rehab', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(26, 'maryam Abdelmegeed samir', 'maryam', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(27, 'sara usama mostafa', 'sara', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(28, 'menna allah ibrahim', 'menna', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(29, 'Reem Ahmed', 'reem', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-28 07:10:40'),
(15, 'islam gamal  abdallah', 'islam', '$2y$10$TnyR1Y43m1EIWpb0MiwE8Ocm6rj0F2KojE3PobVfQDo9HYlAHY/7O', 'employee', '2024-08-29 17:11:34');

ALTER TABLE `projects`
ADD COLUMN `description` TEXT,
ADD COLUMN `stakeholder_name` VARCHAR(100),
ADD COLUMN `engineer_ids` VARCHAR(100);

ALTER TABLE tasks ADD COLUMN hours INT DEFAULT 0;

ALTER TABLE projects ADD COLUMN total_hours INT DEFAULT 0;


COMMIT;
