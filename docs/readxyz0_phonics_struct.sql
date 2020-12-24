/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

USE `readxyz0_phonics`;

DROP TABLE IF EXISTS `abc_gametypes`;
CREATE TABLE IF NOT EXISTS `abc_gametypes` (
  `gameTypeId` varchar(32) NOT NULL,
  `gameDisplayAs` varchar(32) NOT NULL,
  `thumbNailUrl` varchar(64) NOT NULL,
  `cssClass` varchar(32) NOT NULL DEFAULT '',
  `belongsOnTab` varchar(32) NOT NULL,
  `isUniversal` enum('Y','N') NOT NULL DEFAULT 'N',
  `universalGameUrl` varchar(128) NOT NULL DEFAULT '',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`gameTypeId`),
  KEY `fk__tabTypeId` (`belongsOnTab`),
  CONSTRAINT `fk__tabTypeId` FOREIGN KEY (`belongsOnTab`) REFERENCES `abc_tabtypes` (`tabTypeId`)
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
  `wordList` varchar(1024) DEFAULT NULL,
  `supplementalWordList` varchar(1024) DEFAULT NULL,
  `stretchList` varchar(1024) DEFAULT NULL,
  `flipBook` varchar(50) NOT NULL DEFAULT '',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `alternateNames` varchar(65535) DEFAULT NULL,
  `fluencySentences` varchar(65535) DEFAULT NULL,
  `games` varchar(65535) DEFAULT NULL,
  `spinner` varchar(65535) DEFAULT NULL,
  `contrastImages` varchar(65535) DEFAULT NULL,
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
  `dateCreated` date NOT NULL,
  `dateLastAccessed` date NOT NULL,
  `validUntilDate` date DEFAULT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
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
  `dateMastered` date DEFAULT NULL,
  `fluencyTimes` varchar(16) NOT NULL DEFAULT '' COMMENT 'each char is a hex value. Array of up to 16 entries',
  `testTimes` varchar(16) NOT NULL DEFAULT '' COMMENT '16 hex digit entries',
  `dateLastPresented` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lessonCode` (`lessonCode`),
  KEY `studentCode` (`studentCode`),
  CONSTRAINT `FK_lessonCode_studentLesson__lessons` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_student_has_lesson` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Used to track a students progress in a lesson';

DROP TABLE IF EXISTS `abc_system_log`;
CREATE TABLE IF NOT EXISTS `abc_system_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timeStamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `level` enum('info','debug','warning','error','fatal') NOT NULL DEFAULT 'info',
  `trace` varchar(8192) DEFAULT NULL,
  `message` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8;

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
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `trainerType` enum('reserve','parent','trainer','staff','admin') DEFAULT NULL,
  `membershipValidTo` date DEFAULT NULL,
  `hash` varchar(128) NOT NULL,
  `trainerCode` varchar(32) NOT NULL,
  PRIMARY KEY (`userName`),
  UNIQUE KEY `trainerCode` (`trainerCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replacement for abc_Users';

DROP TABLE IF EXISTS `abc_warmups`;
CREATE TABLE IF NOT EXISTS `abc_warmups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lessonCode` varchar(32) DEFAULT NULL,
  `ordinal` tinyint(4) NOT NULL,
  `instructions` varchar(1024) NOT NULL DEFAULT '',
  `parts` varchar(65535) DEFAULT NULL COMMENT '[{''directions'': ''xx'', ''parts'': [''xx'', ''xx'', ...]}]',
  PRIMARY KEY (`id`),
  KEY `fk_warmup__lessonCode` (`lessonCode`),
  CONSTRAINT `fk_warmup__lessonCode` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `abc_word_mastery`;
CREATE TABLE IF NOT EXISTS `abc_word_mastery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studentCode` varchar(32) NOT NULL,
  `word` varchar(16) NOT NULL,
  `twice` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `studentCode` (`studentCode`)
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

DROP VIEW IF EXISTS `vw_group_with_keychain`;
CREATE TABLE `vw_group_with_keychain` (
	`groupCode` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	`groupName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`groupDisplayAs` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`active` ENUM('Y','N') NOT NULL COMMENT 'bool' COLLATE 'utf8_general_ci',
	`ordinal` TINYINT(4) NOT NULL,
	`fileName` VARCHAR(32) NULL COLLATE 'utf8_general_ci',
	`friendlyName` VARCHAR(32) NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_lessons_with_group_fields`;
CREATE TABLE `vw_lessons_with_group_fields` (
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01' COLLATE 'utf8_general_ci',
	`lessonName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`lessonDisplayAs` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`ordinal` TINYINT(4) NOT NULL,
	`groupCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci',
	`groupName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`groupDisplayAs` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_lesson_mastery`;
CREATE TABLE `vw_lesson_mastery` (
	`studentCode` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	`studentName` VARCHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01' COLLATE 'utf8_general_ci',
	`lessonName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`groupCode` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	`groupName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`masteryLevel` VARCHAR(9) NOT NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_students_with_username`;
CREATE TABLE `vw_students_with_username` (
	`studentCode` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	`studentName` VARCHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`userName` VARCHAR(100) NOT NULL COMMENT 'WordPress email/username' COLLATE 'utf8_general_ci',
	`trainerCode` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	`active` ENUM('Y','N') NOT NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_student_lessons`;
CREATE TABLE `vw_student_lessons` (
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01' COLLATE 'utf8_general_ci',
	`lessonName` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`lessonDisplayAs` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
	`groupCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci',
	`lessonContent` varchar(65535) NULL,
	`wordList` VARCHAR(1024) NULL COLLATE 'utf8_general_ci',
	`supplementalWordList` VARCHAR(1024) NULL COLLATE 'utf8_general_ci',
	`stretchList` VARCHAR(1024) NULL COLLATE 'utf8_general_ci',
	`flipBook` VARCHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`active` ENUM('Y','N') NOT NULL COLLATE 'utf8_general_ci',
	`alternateNames` varchar(65535) NULL,
	`fluencySentences` varchar(65535) NULL,
	`games` varchar(65535) NULL,
	`spinner` varchar(65535) NULL,
	`contrastImages` varchar(65535) NULL,
	`ordinal` TINYINT(4) NOT NULL,
	`masteryLevel` ENUM('none','advancing','mastered') NULL COMMENT '0-none, 1-advancing, 2-mastered' COLLATE 'utf8_general_ci',
	`fluencyTimes` VARCHAR(16) NULL COMMENT 'each char is a hex value. Array of up to 16 entries' COLLATE 'utf8_general_ci',
	`testTimes` VARCHAR(16) NULL COMMENT '16 hex digit entries' COLLATE 'utf8_general_ci',
	`studentCode` VARCHAR(32) NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

DROP VIEW IF EXISTS `vw_accordion`;
DROP TABLE IF EXISTS `vw_accordion`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_accordion` AS select `al`.`lessonCode`                    AS `lessonCode`,
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
from (((`readxyz0_phonics`.`abc_lessons` `al` left join `readxyz0_phonics`.`abc_groups` `ag` on ((`al`.`groupCode` = `ag`.`groupCode`))) left join `readxyz0_phonics`.`abc_student_lesson` `asl` on ((`al`.`lessonCode` = `asl`.`lessonCode`)))
         left join `readxyz0_phonics`.`abc_keychain` `ak` on ((`ag`.`groupCode` = `ak`.`groupCode`)))
where (`al`.`active` = 'y')
order by `al`.`lessonCode` ;

DROP VIEW IF EXISTS `vw_group_with_keychain`;
DROP TABLE IF EXISTS `vw_group_with_keychain`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_group_with_keychain` AS SELECT G.`*`, K.fileName, K.friendlyName FROM abc_groups G
LEFT JOIN abc_keychain K USING (groupCode) ;

DROP VIEW IF EXISTS `vw_lessons_with_group_fields`;
DROP TABLE IF EXISTS `vw_lessons_with_group_fields`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_lessons_with_group_fields` AS SELECT L.lessonCode, L.lessonName, L.lessonDisplayAs, L.ordinal, L.groupCode, G. groupName, G.groupDisplayAs 
FROM abc_lessons L  INNER JOIN abc_groups G ON L.groupCode = G.groupCode WHERE L.active = 'Y' ORDER BY L.lessonCode ;

DROP VIEW IF EXISTS `vw_lesson_mastery`;
DROP TABLE IF EXISTS `vw_lesson_mastery`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_lesson_mastery` AS SELECT S.studentCode, S.studentName,  L.lessonCode, L.lessonName, G.groupCode, G.groupName, IFNULL(X.masteryLevel,'none') AS masteryLevel
FROM  abc_lessons L 
INNER JOIN abc_groups G USING (groupCode)   
CROSS JOIN abc_students S  
LEFT JOIN (
	SELECT SL.masteryLevel, SL.studentCode, SL.lessonCode
	FROM abc_student_lesson SL 
	INNER JOIN abc_students  USING (studentCode)
	INNER JOIN abc_lessons USING (lessonCode) 
) X ON L.lessonCode = X.lessonCode AND S.studentCode = X.studentCode ;

DROP VIEW IF EXISTS `vw_students_with_username`;
DROP TABLE IF EXISTS `vw_students_with_username`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_students_with_username` AS SELECT S.studentCode, S.studentName, T.userName, T.trainerCode, S.active 
FROM abc_students S INNER JOIN abc_trainers T ON S.userName = T.userName ;

DROP VIEW IF EXISTS `vw_student_lessons`;
DROP TABLE IF EXISTS `vw_student_lessons`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_student_lessons` AS SELECT  L.*, SL.masteryLevel, SL.fluencyTimes, SL.testTimes, SL.studentCode FROM abc_Lessons AS L 
LEFT JOIN abc_student_lesson SL ON L.lessonCode = SL.lessonCode ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
