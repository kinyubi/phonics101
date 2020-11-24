/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP DATABASE IF EXISTS `readxyz1_1`;
CREATE DATABASE IF NOT EXISTS `readxyz1_1` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `readxyz1_1`;

DROP TABLE IF EXISTS `abc_gametypes`;
CREATE TABLE IF NOT EXISTS `abc_gametypes` (
  `gameTypeId` varchar(32) NOT NULL,
  `gameDisplayAs` varchar(32) NOT NULL,
  `thumbNailUrl` varchar(64) NOT NULL,
  `cssClass` varchar(32) NOT NULL DEFAULT '',
  `tabTypeId` varchar(32) NOT NULL,
  `isUniversal` enum('Y','N') NOT NULL DEFAULT 'N',
  `universalGameUrl` varchar(128) NOT NULL DEFAULT '',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`gameTypeId`),
  KEY `fk__tabTypeId` (`tabTypeId`),
  CONSTRAINT `fk__tabTypeId` FOREIGN KEY (`tabTypeId`) REFERENCES `abc_tabtypes` (`tabTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_groups`;
CREATE TABLE IF NOT EXISTS `abc_groups` (
  `groupCode` varchar(32) NOT NULL,
  `groupName` varchar(128) NOT NULL,
  `groupDisplayAs` varchar(128) NOT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'bool',
  `ordinal` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`groupCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_keychain`;
CREATE TABLE IF NOT EXISTS `abc_keychain` (
  `keychainCode` varchar(32) NOT NULL,
  `fileName` varchar(32) NOT NULL,
  `friendlyName` varchar(32) NOT NULL,
  `groupCode` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`keychainCode`),
  KEY `FK_keychain__group` (`groupCode`),
  CONSTRAINT `FK_keychain__group` FOREIGN KEY (`groupCode`) REFERENCES `abc_groups` (`groupCode`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_lessons`;
CREATE TABLE IF NOT EXISTS `abc_lessons` (
  `lessonCode` varchar(32) NOT NULL COMMENT 'Format: G01L01',
  `lessonName` varchar(128) NOT NULL,
  `lessonDisplayAs` varchar(128) NOT NULL,
  `groupCode` varchar(32) DEFAULT NULL,
  `lessonContent` json DEFAULT NULL,
  `wordList` varchar(1024) DEFAULT NULL,
  `supplementalWordList` varchar(1024) DEFAULT NULL,
  `stretchList` varchar(1024) DEFAULT NULL,
  `flipBook` varchar(50) NOT NULL DEFAULT '',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `alternateNames` json DEFAULT NULL,
  `fluencySentences` json DEFAULT NULL,
  `games` json DEFAULT NULL,
  `spinner` json DEFAULT NULL,
  `contrastImages` json DEFAULT NULL,
  `ordinal` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lessonCode`),
  KEY `fk_groups__groupCode` (`groupCode`),
  CONSTRAINT `fk_groups__groupCode` FOREIGN KEY (`groupCode`) REFERENCES `abc_groups` (`groupCode`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_onetime_pass`;
CREATE TABLE IF NOT EXISTS `abc_onetime_pass` (
  `hash` varchar(50) NOT NULL DEFAULT '',
  `username` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_students`;
CREATE TABLE IF NOT EXISTS `abc_students` (
  `studentCode` varchar(32) NOT NULL,
  `userName` varchar(100) DEFAULT NULL,
  `studentName` varchar(50) NOT NULL,
  `avatarFileName` varchar(50) NOT NULL DEFAULT '',
  `createdDate` date NOT NULL,
  `lastAccessedDate` date NOT NULL,
  `validUntilDate` date DEFAULT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`studentCode`),
  KEY `fk_student__trainer` (`userName`),
  CONSTRAINT `fk_student__trainer` FOREIGN KEY (`userName`) REFERENCES `abc_trainers` (`userName`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replaces abc_Student';

DROP TABLE IF EXISTS `abc_student_animals`;
CREATE TABLE IF NOT EXISTS `abc_student_animals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `studentCode` varchar(32) DEFAULT NULL,
  `animalCode` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_animalCode_animals` (`animalCode`),
  KEY `studentCode` (`studentCode`),
  CONSTRAINT `FK_animalCode_animals` FOREIGN KEY (`animalCode`) REFERENCES `abc_zoo_animals` (`animalCode`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_student_won_animal` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_student_keychain`;
CREATE TABLE IF NOT EXISTS `abc_student_keychain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keychainCode` varchar(32) NOT NULL,
  `studentCode` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_animalCode__keychain` (`keychainCode`),
  KEY `FK_student_has_keychain` (`studentCode`),
  CONSTRAINT `FK_animalCode__keychain` FOREIGN KEY (`keychainCode`) REFERENCES `abc_keychain` (`keychainCode`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_student_has_keychain` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_student_lesson`;
CREATE TABLE IF NOT EXISTS `abc_student_lesson` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `studentCode` varchar(32) DEFAULT NULL,
  `lessonCode` varchar(32) NOT NULL,
  `timePresented` smallint(5) unsigned NOT NULL DEFAULT '0',
  `masteryLevel` enum('none','advancing','mastered') NOT NULL DEFAULT 'none' COMMENT '0-none, 1-advancing, 2-mastered',
  `masteryDate` date DEFAULT NULL,
  `fluencyTimes` varchar(16) NOT NULL DEFAULT '' COMMENT 'each char is a hex value. Array of up to 16 entries',
  `testTimes` varchar(16) NOT NULL DEFAULT '' COMMENT '16 hex digit entries',
  `lastPresentedDate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lessonCode` (`lessonCode`),
  KEY `studentCode` (`studentCode`),
  CONSTRAINT `FK_lessonCode_studentLesson__lessons` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_student_has_lesson` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to track a students progress in a lesson';

DROP TABLE IF EXISTS `abc_system_log`;
CREATE TABLE IF NOT EXISTS `abc_system_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timeStamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `level` enum('info','debug','warning','error','fatal') NOT NULL DEFAULT 'info',
  `trace` varchar(8192) DEFAULT NULL,
  `message` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_tabtypes`;
CREATE TABLE IF NOT EXISTS `abc_tabtypes` (
  `tabTypeId` varchar(32) NOT NULL,
  `tabDisplayAs` varchar(32) NOT NULL,
  `alias` varchar(32) NOT NULL DEFAULT '',
  `script` varchar(1024) NOT NULL DEFAULT '',
  `iconUrl` varchar(128) NOT NULL,
  PRIMARY KEY (`tabTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_trainers`;
CREATE TABLE IF NOT EXISTS `abc_trainers` (
  `userName` varchar(100) NOT NULL COMMENT 'WordPress email/username',
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `dateCreated` date NOT NULL,
  `dateModified` date NOT NULL,
  `dateLastAccessed` date NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `trainerType` enum('parent','trainer','admin') DEFAULT NULL,
  `membershipValidTo` date DEFAULT NULL,
  `hash` varchar(128) NOT NULL,
  `trainerCode` varchar(32) NOT NULL,
  PRIMARY KEY (`userName`),
  UNIQUE KEY `trainerCode` (`trainerCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replacement for abc_Users';

DROP TABLE IF EXISTS `abc_usermastery`;
CREATE TABLE IF NOT EXISTS `abc_usermastery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studentCode` varchar(32) DEFAULT NULL,
  `word` varchar(16) NOT NULL,
  `twice` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `studentCode` (`studentCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_warmups`;
CREATE TABLE IF NOT EXISTS `abc_warmups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lessonCode` varchar(32) NOT NULL,
  `ordinal` tinyint(4) NOT NULL,
  `directions` varchar(1024) NOT NULL DEFAULT '',
  `parts` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_warmup__lessonCode` (`lessonCode`),
  CONSTRAINT `fk_warmup__lessonCode` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_zoo_animals`;
CREATE TABLE IF NOT EXISTS `abc_zoo_animals` (
  `animalCode` varchar(32) NOT NULL,
  `fileName` varchar(32) NOT NULL,
  `grayFileName` varchar(32) NOT NULL,
  `friendlyName` varchar(32) NOT NULL,
  `associatedLesson` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`animalCode`),
  KEY `FK_animal__lesson` (`associatedLesson`),
  CONSTRAINT `FK_animal__lesson` FOREIGN KEY (`associatedLesson`) REFERENCES `abc_lessons` (`lessonCode`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP VIEW IF EXISTS `vw_accordion`;
CREATE TABLE `vw_accordion` (
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01' COLLATE 'utf8_general_ci',
	`lessonName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`lessonDisplayAs` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`active` ENUM('Y','N') NOT NULL COLLATE 'utf8_general_ci',
	`groupCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci',
	`groupName` VARCHAR(128) NULL COLLATE 'utf8_general_ci',
	`groupDisplayAs` VARCHAR(128) NULL COLLATE 'utf8_general_ci',
	`mastery` VARCHAR(9) NOT NULL COLLATE 'utf8_general_ci',
	`masteryIndex` DOUBLE NOT NULL,
	`studentCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci',
	`animalFileName` VARCHAR(32) NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_lessons_with_group_fields`;
CREATE TABLE `vw_lessons_with_group_fields` (
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01' COLLATE 'utf8_general_ci',
	`lessonName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`lessonDisplayAs` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`ordinal` TINYINT(4) NOT NULL,
	`groupCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci',
	`groupName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_lesson_mastery`;
CREATE TABLE `vw_lesson_mastery` (
	`masteryLevel` ENUM('none','advancing','mastered') NOT NULL COMMENT '0-none, 1-advancing, 2-mastered' COLLATE 'utf8_general_ci',
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01' COLLATE 'utf8_general_ci',
	`lessonName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`studentCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_students_with_username`;
CREATE TABLE `vw_students_with_username` (
	`studentCode` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	`studentName` VARCHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`userName` VARCHAR(100) NOT NULL COMMENT 'WordPress email/username' COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_student_lessons`;
CREATE TABLE `vw_student_lessons` (
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01' COLLATE 'utf8_general_ci',
	`lessonName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`lessonDisplayAs` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`groupCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci',
	`lessonContent` JSON NULL,
	`wordList` VARCHAR(1024) NULL COLLATE 'utf8_general_ci',
	`supplementalWordList` VARCHAR(1024) NULL COLLATE 'utf8_general_ci',
	`stretchList` VARCHAR(1024) NULL COLLATE 'utf8_general_ci',
	`flipBook` VARCHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`active` ENUM('Y','N') NOT NULL COLLATE 'utf8_general_ci',
	`alternateNames` JSON NULL,
	`fluencySentences` JSON NULL,
	`games` JSON NULL,
	`spinner` JSON NULL,
	`contrastImages` JSON NULL,
	`ordinal` TINYINT(4) NOT NULL,
	`masteryLevel` ENUM('none','advancing','mastered') NULL COMMENT '0-none, 1-advancing, 2-mastered' COLLATE 'utf8_general_ci',
	`fluencyTimes` VARCHAR(16) NULL COMMENT 'each char is a hex value. Array of up to 16 entries' COLLATE 'utf8_general_ci',
	`testTimes` VARCHAR(16) NULL COMMENT '16 hex digit entries' COLLATE 'utf8_general_ci',
	`studentCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_accordion`;
DROP TABLE IF EXISTS `vw_accordion`;
CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz1_admin`@`localhost` SQL SECURITY DEFINER VIEW `vw_accordion` AS select `al`.`lessonCode`                    AS `lessonCode`,
       `al`.`lessonName`                    AS `lessonName`,
       `al`.`lessonDisplayAs`               AS `lessonDisplayAs`,
       `al`.`active`                        AS `active`,
       `ag`.`groupCode`                     AS `groupCode`,
       `ag`.`groupName`                     AS `groupName`,
       `ag`.`groupDisplayAs`                AS `groupDisplayAs`,
       ifnull(`asl`.`masteryLevel`, 'none') AS `mastery`,
       ifnull(asl.masteryLevel + 0, 0)      AS masteryIndex,
       `asl`.`studentCode`                    AS `studentCode`,
       `ak`.`fileName`                      AS `animalFileName`
from (((`readxyz1_1`.`abc_lessons` `al` left join `readxyz1_1`.`abc_groups` `ag` on ((`al`.`groupCode` = `ag`.`groupCode`))) left join `readxyz1_1`.`abc_student_lesson` `asl` on ((`al`.`lessonCode` = `asl`.`lessonCode`)))
         left join `readxyz1_1`.`abc_keychain` `ak` on ((`ag`.`groupCode` = `ak`.`groupCode`)))
where (`al`.`active` = 'y')
order by `al`.`lessonCode` ;

DROP VIEW IF EXISTS `vw_lessons_with_group_fields`;
DROP TABLE IF EXISTS `vw_lessons_with_group_fields`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_lessons_with_group_fields` AS SELECT L.lessonCode, L.lessonName, L.lessonDisplayAs, L.ordinal, L.groupCode, G. groupName
FROM abc_lessons L  INNER JOIN abc_groups G ON L.groupCode = G.groupCode WHERE L.active = 'Y' ORDER BY L.lessonCode ;

DROP VIEW IF EXISTS `vw_lesson_mastery`;
DROP TABLE IF EXISTS `vw_lesson_mastery`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_lesson_mastery` AS SELECT S.masteryLevel, L.lessonCode, L.lessonName, S.studentCode
FROM abc_student_lesson S INNER JOIN abc_lessons L ON S.lessonCode = L.lessonCode ;

DROP VIEW IF EXISTS `vw_students_with_username`;
DROP TABLE IF EXISTS `vw_students_with_username`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_students_with_username` AS
    SELECT S.studentCode, S.studentName, T.userName, T.trainerCode
FROM abc_students S INNER JOIN abc_trainers T ON S.userName = T.userName ;

DROP VIEW IF EXISTS `vw_student_lessons`;
DROP TABLE IF EXISTS `vw_student_lessons`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_student_lessons` AS
    SELECT  L.*, SL.masteryLevel, SL.fluencyTimes, SL.testTimes, SL.studentCode FROM abc_Lessons AS L
LEFT JOIN abc_student_lesson SL ON L.lessonCode = SL.lessonCode ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
