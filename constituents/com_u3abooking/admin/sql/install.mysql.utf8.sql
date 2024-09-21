CREATE TABLE IF NOT EXISTS `#__u3a_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` varchar(4000) NOT NULL DEFAULT '',
  `organiser_email` varchar(100) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `catid` int(10) unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT 0,
  `checked_out` int(10) unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT 0,
  `access` int(10) unsigned NOT NULL DEFAULT 0,
  `venue` varchar(400) NOT NULL DEFAULT '',
  `event_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `event_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `capacity` int(4) NOT NULL DEFAULT 0,
  `max_tickets_per_booking` int(4) NOT NULL DEFAULT 1,
  `tickets_taken` int(4) NOT NULL DEFAULT 0,
  `image` varchar(400) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_created_user_id` (`created_by`),
  KEY `idx_access` (`access`),
  KEY `idx_alias` (`alias`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__u3a_booking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `booking_ref_part` varchar(255) NOT NULL DEFAULT '',
  `num_tickets` int(4) NOT NULL DEFAULT 0,
  `attendees` varchar(4000) NOT NULL DEFAULT '',
  `telephone` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `special_requirements` varchar(4000),
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

