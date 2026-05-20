CREATE DATABASE IF NOT EXISTS `task_manager`;
USE `task_manager`;

CREATE TABLE `tbl_lists` (
  `list_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `list_name` varchar(50) NOT NULL,
  `list_description` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_lists` (`list_id`, `list_name`, `list_description`, `notes`) VALUES
(1, 'To Do', 'All the tasks that must be done soon.', NULL),
(2, 'Doing', 'All the Tasks that are currently being done.', NULL),
(3, 'Done', 'All the Tasks that are completed', NULL),
(7, 'Shopping', 'Tasks for Shopping', NULL),
(9, 'Internal', 'Internal project notes', NULL);

CREATE TABLE `tbl_tasks` (
  `task_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_name` varchar(150) NOT NULL,
  `task_description` text NOT NULL,
  `list_id` int(11) NOT NULL,
  `priority` varchar(10) NOT NULL,
  `deadline` date NOT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_tasks` (`task_id`, `task_name`, `task_description`, `list_id`, `priority`, `deadline`) VALUES
(2, 'icon Design', 'This is urgent', 1, 'High', '2020-06-03'),
(3, 'Buy Things', 'Okay Buy', 3, 'Medium', '2020-06-12'),
(4, 'Web Page Design', 'All the Tasks for Web Page Design', 1, 'Medium', '2020-06-11'),
(5, 'Application Development', 'All the tasks', 1, 'Low', '2020-07-03'),
(6, 'SEO', 'Search Engine Optimization', 2, 'Medium', '2020-06-19'),
(7, 'Desktop Application Development', 'This is Important', 3, 'Low', '2020-06-26'),
(8, '4K Monitor', 'For Video Editing', 1, 'Medium', '2020-06-18'),
(9, 'Confidential', 'DBS401{n0t_s0_h4rd_t0_f1nd}', 999, 'Low', '2026-12-31'),
(10, 'Internal Memo', 'DBS401{s3Arch_n0t_s0_s3cur3}', 999, 'Low', '2026-12-31');

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
(1, 'admin', 'sup3r_s3cr3t_p4ss', 'admin@taskmanager.local', 'admin', 'DBS401{un10n_1s_p0w3rful}'),
(2, 'staff', 'staff123', 'staff@taskmanager.local', 'user', 'DBS401{bl1nd_but_n0t_mute}');
