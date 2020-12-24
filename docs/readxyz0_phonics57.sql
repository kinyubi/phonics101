-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2020 at 05:15 PM
-- Server version: 5.6.40-84.0-log
-- PHP Version: 5.6.30

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `readxyz0_phonics`
--

-- --------------------------------------------------------

--
-- Table structure for table `abc_gametypes`
--

DROP TABLE IF EXISTS `abc_gametypes`;
CREATE TABLE `abc_gametypes` (
  `gameTypeId` varchar(32) NOT NULL,
  `gameDisplayAs` varchar(32) NOT NULL,
  `thumbNailUrl` varchar(64) NOT NULL,
  `cssClass` varchar(32) NOT NULL DEFAULT '',
  `belongsOnTab` varchar(32) NOT NULL,
  `isUniversal` enum('Y','N') NOT NULL DEFAULT 'N',
  `universalGameUrl` varchar(128) NOT NULL DEFAULT '',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_gametypes`
--

TRUNCATE TABLE `abc_gametypes`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_groups`
--

DROP TABLE IF EXISTS `abc_groups`;
CREATE TABLE `abc_groups` (
  `groupCode` varchar(32) NOT NULL,
  `groupName` varchar(128) NOT NULL,
  `groupDisplayAs` varchar(128) NOT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'bool',
  `ordinal` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_groups`
--

TRUNCATE TABLE `abc_groups`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_keychain`
--

DROP TABLE IF EXISTS `abc_keychain`;
CREATE TABLE `abc_keychain` (
  `keychainCode` varchar(32) NOT NULL,
  `fileName` varchar(32) NOT NULL,
  `friendlyName` varchar(32) NOT NULL,
  `groupCode` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_keychain`
--

TRUNCATE TABLE `abc_keychain`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_lessons`
--

DROP TABLE IF EXISTS `abc_lessons`;
CREATE TABLE `abc_lessons` (
  `lessonCode` varchar(32) NOT NULL COMMENT 'Format: G01L01',
  `lessonName` varchar(128) NOT NULL,
  `lessonDisplayAs` varchar(128) NOT NULL,
  `groupCode` varchar(32) DEFAULT NULL,
  `wordList` varchar(1024) DEFAULT NULL,
  `supplementalWordList` varchar(1024) DEFAULT NULL,
  `stretchList` varchar(1024) DEFAULT NULL,
  `flipBook` varchar(50) NOT NULL DEFAULT '',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `alternateNames` mediumtext,
  `fluencySentences` mediumtext,
  `games` mediumtext,
  `spinner` mediumtext,
  `contrastImages` mediumtext,
  `ordinal` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_lessons`
--

TRUNCATE TABLE `abc_lessons`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_onetime_pass`
--

DROP TABLE IF EXISTS `abc_onetime_pass`;
CREATE TABLE `abc_onetime_pass` (
  `hash` varchar(50) NOT NULL DEFAULT '',
  `username` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_onetime_pass`
--

TRUNCATE TABLE `abc_onetime_pass`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_students`
--

DROP TABLE IF EXISTS `abc_students`;
CREATE TABLE `abc_students` (
  `studentCode` varchar(32) NOT NULL,
  `userName` varchar(100) DEFAULT NULL,
  `studentName` varchar(50) NOT NULL,
  `avatarFileName` varchar(50) NOT NULL DEFAULT '',
  `dateCreated` date NOT NULL,
  `dateLastAccessed` date NOT NULL,
  `validUntilDate` date DEFAULT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replaces abc_Student';

--
-- Truncate table before insert `abc_students`
--

TRUNCATE TABLE `abc_students`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_student_animals`
--

DROP TABLE IF EXISTS `abc_student_animals`;
CREATE TABLE `abc_student_animals` (
  `id` int(10) UNSIGNED NOT NULL,
  `studentCode` varchar(32) DEFAULT NULL,
  `animalCode` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_student_animals`
--

TRUNCATE TABLE `abc_student_animals`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_student_keychain`
--

DROP TABLE IF EXISTS `abc_student_keychain`;
CREATE TABLE `abc_student_keychain` (
  `id` int(10) UNSIGNED NOT NULL,
  `keychainCode` varchar(32) NOT NULL,
  `studentCode` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_student_keychain`
--

TRUNCATE TABLE `abc_student_keychain`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_student_lesson`
--

DROP TABLE IF EXISTS `abc_student_lesson`;
CREATE TABLE `abc_student_lesson` (
  `id` int(10) UNSIGNED NOT NULL,
  `studentCode` varchar(32) DEFAULT NULL,
  `lessonCode` varchar(32) NOT NULL,
  `timePresented` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `masteryLevel` enum('none','advancing','mastered') NOT NULL DEFAULT 'none' COMMENT '0-none, 1-advancing, 2-mastered',
  `dateMastered` date DEFAULT NULL,
  `fluencyTimes` varchar(16) NOT NULL DEFAULT '' COMMENT 'each char is a hex value. Array of up to 16 entries',
  `testTimes` varchar(16) NOT NULL DEFAULT '' COMMENT '16 hex digit entries',
  `dateLastPresented` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to track a students progress in a lesson';

--
-- Truncate table before insert `abc_student_lesson`
--

TRUNCATE TABLE `abc_student_lesson`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_system_log`
--

DROP TABLE IF EXISTS `abc_system_log`;
CREATE TABLE `abc_system_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `timeStamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `level` enum('info','debug','warning','error','fatal') NOT NULL DEFAULT 'info',
  `trace` varchar(8192) DEFAULT NULL,
  `message` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_system_log`
--

TRUNCATE TABLE `abc_system_log`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_tabtypes`
--

DROP TABLE IF EXISTS `abc_tabtypes`;
CREATE TABLE `abc_tabtypes` (
  `tabTypeId` varchar(32) NOT NULL,
  `tabDisplayAs` varchar(32) NOT NULL,
  `alias` varchar(32) NOT NULL DEFAULT '',
  `script` varchar(1024) NOT NULL DEFAULT '',
  `iconUrl` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_tabtypes`
--

TRUNCATE TABLE `abc_tabtypes`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_trainers`
--

DROP TABLE IF EXISTS `abc_trainers`;
CREATE TABLE `abc_trainers` (
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
  `trainerCode` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replacement for abc_Users';

--
-- Truncate table before insert `abc_trainers`
--

TRUNCATE TABLE `abc_trainers`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_warmups`
--

DROP TABLE IF EXISTS `abc_warmups`;
CREATE TABLE `abc_warmups` (
  `id` int(10) UNSIGNED NOT NULL,
  `lessonCode` varchar(32) DEFAULT NULL,
  `ordinal` tinyint(4) NOT NULL,
  `instructions` varchar(1024) NOT NULL DEFAULT '',
  `parts` mediumtext COMMENT '[{''directions'': ''xx'', ''parts'': [''xx'', ''xx'', ...]}]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_warmups`
--

TRUNCATE TABLE `abc_warmups`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_word_mastery`
--

DROP TABLE IF EXISTS `abc_word_mastery`;
CREATE TABLE `abc_word_mastery` (
  `id` int(11) NOT NULL,
  `studentCode` varchar(32) NOT NULL,
  `word` varchar(16) NOT NULL,
  `twice` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_word_mastery`
--

TRUNCATE TABLE `abc_word_mastery`;
-- --------------------------------------------------------

--
-- Table structure for table `abc_zoo_animals`
--

DROP TABLE IF EXISTS `abc_zoo_animals`;
CREATE TABLE `abc_zoo_animals` (
  `animalCode` varchar(32) NOT NULL,
  `fileName` varchar(32) NOT NULL,
  `grayFileName` varchar(32) NOT NULL,
  `friendlyName` varchar(32) NOT NULL,
  `associatedLesson` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncate table before insert `abc_zoo_animals`
--

TRUNCATE TABLE `abc_zoo_animals`;
-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_accordion`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_accordion`;
CREATE TABLE `vw_accordion` (
`lessonCode` varchar(32)
,`lessonName` varchar(128)
,`lessonDisplayAs` varchar(128)
,`active` enum('Y','N')
,`groupCode` varchar(32)
,`groupName` varchar(128)
,`groupDisplayAs` varchar(128)
,`mastery` varchar(9)
,`masteryIndex` double
,`studentCode` varchar(32)
,`animalFileName` varchar(32)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_group_with_keychain`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_group_with_keychain`;
CREATE TABLE `vw_group_with_keychain` (
`groupCode` varchar(32)
,`groupName` varchar(128)
,`groupDisplayAs` varchar(128)
,`active` enum('Y','N')
,`ordinal` tinyint(4)
,`fileName` varchar(32)
,`friendlyName` varchar(32)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_lessons_with_group_fields`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_lessons_with_group_fields`;
CREATE TABLE `vw_lessons_with_group_fields` (
`lessonCode` varchar(32)
,`lessonName` varchar(128)
,`lessonDisplayAs` varchar(128)
,`ordinal` tinyint(4)
,`groupCode` varchar(32)
,`groupName` varchar(128)
,`groupDisplayAs` varchar(128)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_lesson_mastery`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_lesson_mastery`;
CREATE TABLE `vw_lesson_mastery` (
`studentCode` varchar(32)
,`studentName` varchar(50)
,`lessonCode` varchar(32)
,`lessonName` varchar(128)
,`groupCode` varchar(32)
,`groupName` varchar(128)
,`masteryLevel` varchar(9)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_mastery_subquery`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_mastery_subquery`;
CREATE TABLE `vw_mastery_subquery` (
`masteryLevel` enum('none','advancing','mastered')
,`studentCode` varchar(32)
,`lessonCode` varchar(32)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_students_with_username`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_students_with_username`;
CREATE TABLE `vw_students_with_username` (
`studentCode` varchar(32)
,`studentName` varchar(50)
,`userName` varchar(100)
,`trainerCode` varchar(32)
,`active` enum('Y','N')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_student_lessons`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_student_lessons`;
CREATE TABLE `vw_student_lessons` (
`lessonCode` varchar(32)
,`lessonName` varchar(128)
,`lessonDisplayAs` varchar(128)
,`groupCode` varchar(32)
,`wordList` varchar(1024)
,`supplementalWordList` varchar(1024)
,`stretchList` varchar(1024)
,`flipBook` varchar(50)
,`active` enum('Y','N')
,`alternateNames` mediumtext
,`fluencySentences` mediumtext
,`games` mediumtext
,`spinner` mediumtext
,`contrastImages` mediumtext
,`ordinal` tinyint(4)
,`masteryLevel` enum('none','advancing','mastered')
,`fluencyTimes` varchar(16)
,`testTimes` varchar(16)
,`studentCode` varchar(32)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_accordion`
--
DROP TABLE IF EXISTS `vw_accordion`;

CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz0`@`localhost` SQL SECURITY DEFINER VIEW `vw_accordion`  AS  select `al`.`lessonCode` AS `lessonCode`,`al`.`lessonName` AS `lessonName`,`al`.`lessonDisplayAs` AS `lessonDisplayAs`,`al`.`active` AS `active`,`ag`.`groupCode` AS `groupCode`,`ag`.`groupName` AS `groupName`,`ag`.`groupDisplayAs` AS `groupDisplayAs`,ifnull(`asl`.`masteryLevel`,'none') AS `mastery`,ifnull((`asl`.`masteryLevel` + 0),0) AS `masteryIndex`,`asl`.`studentCode` AS `studentCode`,`ak`.`fileName` AS `animalFileName` from (((`abc_lessons` `al` left join `abc_groups` `ag` on((`al`.`groupCode` = `ag`.`groupCode`))) left join `abc_student_lesson` `asl` on((`al`.`lessonCode` = `asl`.`lessonCode`))) left join `abc_keychain` `ak` on((`ag`.`groupCode` = `ak`.`groupCode`))) where (`al`.`active` = 'y') order by `al`.`lessonCode` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_group_with_keychain`
--
DROP TABLE IF EXISTS `vw_group_with_keychain`;

CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz0`@`localhost` SQL SECURITY DEFINER VIEW `vw_group_with_keychain`  AS  select `G`.`groupCode` AS `groupCode`,`G`.`groupName` AS `groupName`,`G`.`groupDisplayAs` AS `groupDisplayAs`,`G`.`active` AS `active`,`G`.`ordinal` AS `ordinal`,`K`.`fileName` AS `fileName`,`K`.`friendlyName` AS `friendlyName` from (`abc_groups` `G` left join `abc_keychain` `K` on((`G`.`groupCode` = `K`.`groupCode`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_lessons_with_group_fields`
--
DROP TABLE IF EXISTS `vw_lessons_with_group_fields`;

CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz0`@`localhost` SQL SECURITY DEFINER VIEW `vw_lessons_with_group_fields`  AS  select `L`.`lessonCode` AS `lessonCode`,`L`.`lessonName` AS `lessonName`,`L`.`lessonDisplayAs` AS `lessonDisplayAs`,`L`.`ordinal` AS `ordinal`,`L`.`groupCode` AS `groupCode`,`G`.`groupName` AS `groupName`,`G`.`groupDisplayAs` AS `groupDisplayAs` from (`abc_lessons` `L` join `abc_groups` `G` on((`L`.`groupCode` = `G`.`groupCode`))) where (`L`.`active` = 'Y') order by `L`.`lessonCode` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_lesson_mastery`
--
DROP TABLE IF EXISTS `vw_lesson_mastery`;

CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz0`@`localhost` SQL SECURITY DEFINER VIEW `vw_lesson_mastery`  AS  select `S`.`studentCode` AS `studentCode`,`S`.`studentName` AS `studentName`,`L`.`lessonCode` AS `lessonCode`,`L`.`lessonName` AS `lessonName`,`G`.`groupCode` AS `groupCode`,`G`.`groupName` AS `groupName`,ifnull(`X`.`masteryLevel`,'none') AS `masteryLevel` from (((`abc_lessons` `L` join `abc_groups` `G` on((`L`.`groupCode` = `G`.`groupCode`))) join `abc_students` `S`) left join `vw_mastery_subquery` `X` on(((`L`.`lessonCode` = `X`.`lessonCode`) and (`S`.`studentCode` = `X`.`studentCode`)))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_mastery_subquery`
--
DROP TABLE IF EXISTS `vw_mastery_subquery`;

CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz0`@`localhost` SQL SECURITY DEFINER VIEW `vw_mastery_subquery`  AS  select `SL`.`masteryLevel` AS `masteryLevel`,`SL`.`studentCode` AS `studentCode`,`SL`.`lessonCode` AS `lessonCode` from ((`abc_student_lesson` `SL` join `abc_students` on((`SL`.`studentCode` = `abc_students`.`studentCode`))) join `abc_lessons` on((`SL`.`lessonCode` = `abc_lessons`.`lessonCode`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_students_with_username`
--
DROP TABLE IF EXISTS `vw_students_with_username`;

CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz0`@`localhost` SQL SECURITY DEFINER VIEW `vw_students_with_username`  AS  select `S`.`studentCode` AS `studentCode`,`S`.`studentName` AS `studentName`,`T`.`userName` AS `userName`,`T`.`trainerCode` AS `trainerCode`,`S`.`active` AS `active` from (`abc_students` `S` join `abc_trainers` `T` on((`S`.`userName` = `T`.`userName`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_student_lessons`
--
DROP TABLE IF EXISTS `vw_student_lessons`;

CREATE ALGORITHM=UNDEFINED DEFINER=`readxyz0`@`localhost` SQL SECURITY DEFINER VIEW `vw_student_lessons`  AS  select `L`.`lessonCode` AS `lessonCode`,`L`.`lessonName` AS `lessonName`,`L`.`lessonDisplayAs` AS `lessonDisplayAs`,`L`.`groupCode` AS `groupCode`,`L`.`wordList` AS `wordList`,`L`.`supplementalWordList` AS `supplementalWordList`,`L`.`stretchList` AS `stretchList`,`L`.`flipBook` AS `flipBook`,`L`.`active` AS `active`,`L`.`alternateNames` AS `alternateNames`,`L`.`fluencySentences` AS `fluencySentences`,`L`.`games` AS `games`,`L`.`spinner` AS `spinner`,`L`.`contrastImages` AS `contrastImages`,`L`.`ordinal` AS `ordinal`,`SL`.`masteryLevel` AS `masteryLevel`,`SL`.`fluencyTimes` AS `fluencyTimes`,`SL`.`testTimes` AS `testTimes`,`SL`.`studentCode` AS `studentCode` from (`abc_lessons` `L` left join `abc_student_lesson` `SL` on((`L`.`lessonCode` = `SL`.`lessonCode`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abc_gametypes`
--
ALTER TABLE `abc_gametypes`
  ADD PRIMARY KEY (`gameTypeId`),
  ADD KEY `fk__tabTypeId` (`belongsOnTab`);

--
-- Indexes for table `abc_groups`
--
ALTER TABLE `abc_groups`
  ADD PRIMARY KEY (`groupCode`);

--
-- Indexes for table `abc_keychain`
--
ALTER TABLE `abc_keychain`
  ADD PRIMARY KEY (`keychainCode`),
  ADD KEY `FK_keychain__group` (`groupCode`);

--
-- Indexes for table `abc_lessons`
--
ALTER TABLE `abc_lessons`
  ADD PRIMARY KEY (`lessonCode`),
  ADD KEY `fk_groups__groupCode` (`groupCode`);

--
-- Indexes for table `abc_onetime_pass`
--
ALTER TABLE `abc_onetime_pass`
  ADD PRIMARY KEY (`hash`);

--
-- Indexes for table `abc_students`
--
ALTER TABLE `abc_students`
  ADD PRIMARY KEY (`studentCode`),
  ADD KEY `fk_student__trainer` (`userName`);

--
-- Indexes for table `abc_student_animals`
--
ALTER TABLE `abc_student_animals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_animalCode_animals` (`animalCode`),
  ADD KEY `studentCode` (`studentCode`);

--
-- Indexes for table `abc_student_keychain`
--
ALTER TABLE `abc_student_keychain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_animalCode__keychain` (`keychainCode`),
  ADD KEY `FK_student_has_keychain` (`studentCode`);

--
-- Indexes for table `abc_student_lesson`
--
ALTER TABLE `abc_student_lesson`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lessonCode` (`lessonCode`),
  ADD KEY `studentCode` (`studentCode`);

--
-- Indexes for table `abc_system_log`
--
ALTER TABLE `abc_system_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `abc_tabtypes`
--
ALTER TABLE `abc_tabtypes`
  ADD PRIMARY KEY (`tabTypeId`);

--
-- Indexes for table `abc_trainers`
--
ALTER TABLE `abc_trainers`
  ADD PRIMARY KEY (`userName`),
  ADD UNIQUE KEY `trainerCode` (`trainerCode`);

--
-- Indexes for table `abc_warmups`
--
ALTER TABLE `abc_warmups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_warmup__lessonCode` (`lessonCode`);

--
-- Indexes for table `abc_word_mastery`
--
ALTER TABLE `abc_word_mastery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studentCode` (`studentCode`);

--
-- Indexes for table `abc_zoo_animals`
--
ALTER TABLE `abc_zoo_animals`
  ADD PRIMARY KEY (`animalCode`),
  ADD KEY `FK_animal__lesson` (`associatedLesson`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abc_student_animals`
--
ALTER TABLE `abc_student_animals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `abc_student_keychain`
--
ALTER TABLE `abc_student_keychain`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `abc_student_lesson`
--
ALTER TABLE `abc_student_lesson`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `abc_system_log`
--
ALTER TABLE `abc_system_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;
--
-- AUTO_INCREMENT for table `abc_warmups`
--
ALTER TABLE `abc_warmups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `abc_word_mastery`
--
ALTER TABLE `abc_word_mastery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `abc_gametypes`
--
ALTER TABLE `abc_gametypes`
  ADD CONSTRAINT `fk__tabTypeId` FOREIGN KEY (`belongsOnTab`) REFERENCES `abc_tabtypes` (`tabTypeId`);

--
-- Constraints for table `abc_keychain`
--
ALTER TABLE `abc_keychain`
  ADD CONSTRAINT `FK_keychain__group` FOREIGN KEY (`groupCode`) REFERENCES `abc_groups` (`groupCode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `abc_lessons`
--
ALTER TABLE `abc_lessons`
  ADD CONSTRAINT `fk_groups__groupCode` FOREIGN KEY (`groupCode`) REFERENCES `abc_groups` (`groupCode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `abc_students`
--
ALTER TABLE `abc_students`
  ADD CONSTRAINT `fk_student__trainer` FOREIGN KEY (`userName`) REFERENCES `abc_trainers` (`userName`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `abc_student_animals`
--
ALTER TABLE `abc_student_animals`
  ADD CONSTRAINT `FK_animalCode_animals` FOREIGN KEY (`animalCode`) REFERENCES `abc_zoo_animals` (`animalCode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_student_won_animal` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `abc_student_keychain`
--
ALTER TABLE `abc_student_keychain`
  ADD CONSTRAINT `FK_animalCode__keychain` FOREIGN KEY (`keychainCode`) REFERENCES `abc_keychain` (`keychainCode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_student_has_keychain` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `abc_student_lesson`
--
ALTER TABLE `abc_student_lesson`
  ADD CONSTRAINT `FK_lessonCode_studentLesson__lessons` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_student_has_lesson` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `abc_warmups`
--
ALTER TABLE `abc_warmups`
  ADD CONSTRAINT `fk_warmup__lessonCode` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`);

--
-- Constraints for table `abc_zoo_animals`
--
ALTER TABLE `abc_zoo_animals`
  ADD CONSTRAINT `FK_animal__lesson` FOREIGN KEY (`associatedLesson`) REFERENCES `abc_lessons` (`lessonCode`) ON DELETE SET NULL ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
