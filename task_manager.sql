CREATE DATABASE IF NOT EXISTS `task_manager`;
USE `task_manager`;

CREATE TABLE `tbl_lists` (
  `list_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `list_name` varchar(50) NOT NULL,
  `list_description` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_lists` (`list_id`, `list_name`, `list_description`, `notes`, `user_id`) VALUES
(1, 'To Do', 'All the tasks that must be done soon.', NULL, 1),
(2, 'Doing', 'All the Tasks that are currently being done.', NULL, 1),
(3, 'Done', 'All the Tasks that are completed', NULL, 1),
(7, 'Shopping', 'Tasks for Shopping', NULL, 1),
(9, 'Internal', 'Internal project notes', NULL, 1);

CREATE TABLE `tbl_tasks` (
  `task_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_name` varchar(150) NOT NULL,
  `task_description` text NOT NULL,
  `list_id` int(11) NOT NULL,
  `priority` varchar(10) NOT NULL,
  `deadline` date NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_tasks` (`task_id`, `task_name`, `task_description`, `list_id`, `priority`, `deadline`, `user_id`) VALUES
(2, 'icon Design', 'This is urgent', 1, 'High', '2026-06-03', 1),
(3, 'Buy Things', 'Okay Buy', 3, 'Medium', '2026-06-12', 1),
(4, 'Web Page Design', 'All the Tasks for Web Page Design', 1, 'Medium', '2026-06-11', 1),
(5, 'Application Development', 'All the tasks', 1, 'Low', '2026-07-03', 1),
(6, 'SEO', 'Search Engine Optimization', 2, 'Medium', '2026-06-19', 1),
(7, 'Desktop Application Development', 'This is Important', 3, 'Low', '2026-06-26', 1),
(8, '4K Monitor', 'For Video Editing', 1, 'Medium', '2026-06-18', 1);

CREATE TABLE `tbl_users` (
  `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_users` (`user_id`, `username`, `password`, `email`, `role`, `token`) VALUES
(1, 'admin', 'sup3r_s3cr3t_p4ss', 'admin@taskmanager.local', 'admin', 'un10n_1s_p0w3rful'),
(2, 'staff', 'staff123', 'staff@taskmanager.local', 'user', '3rr0r_b4s3d_m4st3r'),
(4, 'intern', 'intern123', 'intern@taskmanager.local', 'user', 'b00l34n_bl1nd_pr0'),
(5, 'contractor', 'contr123', 'contractor@taskmanager.local', 'user', 't1m3_1s_1lus10n');


INSERT INTO `tbl_users` (`user_id`, `username`, `password`, `email`, `role`, `token`) VALUES
(3, 'manager', 'm4n4g3r_p4ss', 'manager@taskmanager.local', 'admin', 'M@n4g3r_T0k3n_h3re');

CREATE TABLE `tbl_feedback` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE VIEW vw_union_flag AS SELECT username, token FROM tbl_users WHERE username IN ('admin', 'manager');
CREATE VIEW vw_error_flag AS SELECT username, token FROM tbl_users WHERE username = 'staff';
CREATE VIEW vw_boolean_flag AS SELECT username, token FROM tbl_users WHERE username = 'intern';
CREATE VIEW vw_time_flag AS SELECT username, token FROM tbl_users WHERE username = 'contractor';

