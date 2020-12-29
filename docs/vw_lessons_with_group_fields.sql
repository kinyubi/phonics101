-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 26, 2020 at 02:55 PM
-- Server version: 5.7.24
-- PHP Version: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: readxyz0_phonics
--

-- --------------------------------------------------------

--
-- Structure for view vw_lessons_with_group_fields
--

CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vw_lessons_with_group_fields  AS SELECT l.lessonCode AS `lessonCode`, l.lessonName AS `lessonName`, l.lessonDisplayAs AS `lessonDisplayAs`, l.groupCode AS `groupCode`, l.lessonContent AS `lessonContent`, l.wordList AS `wordList`, l.supplementalWordList AS `supplementalWordList`, l.stretchList AS `stretchList`, l.flipBook AS `flipBook`, l.active AS `active`, l.alternateNames AS `alternateNames`, l.fluencySentences AS `fluencySentences`, l.games AS `games`, l.spinner AS `spinner`, l.contrastImages AS `contrastImages`, l.ordinal AS `ordinal`, g.groupName AS `groupName`, g.groupDisplayAs AS `groupDisplayAs` FROM (abc_lessons l join abc_groups g on((l.groupCode = g.groupCode))) WHERE (l.active = 'Y') ORDER BY l.lessonCode ASC ;

--
-- VIEW vw_lessons_with_group_fields
-- Data: None
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
