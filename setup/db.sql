/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for school-schedule
CREATE DATABASE IF NOT EXISTS `school-schedule` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `school-schedule`;

-- Dumping structure for table school-schedule.lessons
CREATE TABLE IF NOT EXISTS `lessons` (
  `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
  `owner`      int(11) unsigned NOT NULL DEFAULT '0',
  `name`       varchar(255)     NOT NULL,
  `subject`    varchar(255)     NOT NULL,
  `due`        date                      DEFAULT NULL,
  `created_at` datetime         NOT NULL,
  `updated_at` datetime         NOT NULL,
  `deleted_at` datetime                  DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.lessons_attendees
CREATE TABLE IF NOT EXISTS `lessons_attendees` (
  `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id`  int(11) unsigned NOT NULL,
  `user_id`    int(11) unsigned NOT NULL,
  `created_at` datetime         NOT NULL,
  `updated_at` datetime         NOT NULL,
  `deleted_at` datetime                  DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`),
  KEY `lesson` (`lesson_id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.log
CREATE TABLE IF NOT EXISTS `log` (
  `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code`       int(11) unsigned NOT NULL,
  `type`       int(11) unsigned NOT NULL,
  `user`       varchar(50)      NOT NULL,
  `message`    text             NOT NULL,
  `data`       text             NOT NULL,
  `ip`         varchar(50)      NOT NULL,
  `created_at` datetime         NOT NULL,
  `updated_at` datetime         NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.login_attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`         int(11) unsigned    NOT NULL AUTO_INCREMENT,
  `ip`         varchar(50)         NOT NULL,
  `user`       varchar(50)         NOT NULL,
  `tries`      tinyint(4) unsigned NOT NULL,
  `message`    text                NOT NULL,
  `created_at` datetime            NOT NULL,
  `updated_at` datetime            NOT NULL,
  `deleted_at` datetime                     DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id`  int(11) unsigned NOT NULL,
  `message`    text             NOT NULL,
  `created_at` datetime         NOT NULL,
  `updated_at` datetime         NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.schedule
CREATE TABLE IF NOT EXISTS `schedule` (
  `id`         int(11) unsigned                         NOT NULL AUTO_INCREMENT,
  `lesson_id`  int(11) unsigned                         NOT NULL,
  `week`       tinyint(4) unsigned                      NOT NULL,
  `day`        enum ('mon', 'tue', 'wed', 'thu', 'fri') NOT NULL,
  `period`     tinyint(4) unsigned                      NOT NULL,
  `status`     varchar(50)                              NOT NULL,
  `hasClass`   enum ('0', '1')                          NOT NULL DEFAULT '1',
  `created_at` datetime                                 NOT NULL,
  `updated_at` datetime                                 NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         int(11) unsigned NOT NULL            AUTO_INCREMENT,
  `setting`    varchar(255)                         DEFAULT NULL,
  `value`      text,
  `created_at` datetime                             DEFAULT NULL,
  `updated_at` datetime                             DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.users
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid`                varchar(50)      NOT NULL,
  `name`                varchar(255)     NOT NULL,
  `password`            varchar(255)     NOT NULL,
  `email`               varchar(255)     NOT NULL,
  `remember_identifier` varchar(128)     NOT NULL,
  `remember_token`      varchar(64)      NOT NULL,
  `created_at`          datetime         NOT NULL,
  `updated_at`          datetime         NOT NULL,
  `deleted_at`          datetime                  DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.users_data
CREATE TABLE IF NOT EXISTS `users_data` (
  `id`                int(11) unsigned     NOT NULL         AUTO_INCREMENT,
  `user_id`           int(11) unsigned     NOT NULL,
  `active`            tinyint(1) unsigned  NOT NULL,
  `activation_code`   varchar(255)                          DEFAULT NULL,
  `dob`               date                 NOT NULL,
  `sex`               enum ('m', 'f', 'i') NOT NULL,
  `notification_seen` datetime             NOT NULL,
  `banned`            tinyint(1) unsigned  NOT NULL,
  `banned_until`      datetime                              DEFAULT NULL,
  `created_at`        datetime             NOT NULL,
  `updated_at`        datetime             NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- Data exporting was unselected.
-- Dumping structure for table school-schedule.users_permissions
CREATE TABLE IF NOT EXISTS `users_permissions` (
  `id`         int(10) unsigned    NOT NULL AUTO_INCREMENT,
  `user_id`    int(11) unsigned    NOT NULL,
  `type`       tinyint(4) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime            NOT NULL,
  `updated_at` datetime            NOT NULL,
  `deleted_at` datetime                     DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE = IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS = IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
