CREATE TABLE IF NOT EXISTS `configr_history` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scope` enum('default','websites','stores','config') NOT NULL DEFAULT 'default',
  `scope_id` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT 'general',
  `old_value` text NOT NULL,
  `value` text NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `user_name` varchar(255),
  `created_at` DATETIME,
  PRIMARY KEY (`history_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8