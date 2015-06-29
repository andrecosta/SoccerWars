-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Host: pyxis
-- Generation Time: Jun 16, 2015 at 11:54 PM
-- Server version: 5.5.31
-- PHP Version: 5.4.4-9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `soccerwars`
--

DROP DATABASE IF EXISTS soccerwars;
CREATE DATABASE soccerwars;

USE soccerwars;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `show_error`$$
CREATE DEFINER=`soccerwars`@`%` PROCEDURE `show_error`(msg VARCHAR(255))
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = msg;
  END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Bet`
--

DROP TABLE IF EXISTS `Bet`;
CREATE TABLE IF NOT EXISTS `Bet` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `MatchID` int(11) NOT NULL,
  `Type` int(11) NOT NULL,
  `PointAmount` int(11) DEFAULT NULL,
  `Team` int(11) NOT NULL,
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Obsolete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `MatchID` (`MatchID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `Bet`
--

INSERT INTO `Bet` (`ID`, `UserID`, `MatchID`, `Type`, `PointAmount`, `Team`, `TimeStamp`, `Obsolete`) VALUES
(1, 1, 1, 1, 50, 1, '2015-06-12 15:56:08', 0),
(2, 2, 1, 1, 50, 2, '2015-06-12 15:56:08', 0),
(3, 3, 1, 1, 50, 1, '2015-06-12 15:56:08', 0),
(4, 1, 2, 2, 50, 1, '2015-06-12 15:56:08', 0),
(5, 2, 2, 2, 50, 2, '2015-06-12 15:56:08', 0),
(6, 3, 2, 2, 50, 1, '2015-06-12 15:56:08', 0),
(7, 3, 3, 2, 50, 1, '2015-06-12 15:56:08', 0),
(8, 3, 4, 1, 50, 1, '2015-06-12 15:56:08', 0);

--
-- Triggers `Bet`
--
DROP TRIGGER IF EXISTS `Bet_before_insert`;
DELIMITER //
CREATE TRIGGER `Bet_before_insert` BEFORE INSERT ON `Bet`
 FOR EACH ROW BEGIN
    -- Declare variables
    DECLARE user_id      INT;
    DECLARE user_balance INT;
    DECLARE user_status  VARCHAR(45);
    DECLARE match_id     INT;
    DECLARE match_start  DATETIME;
    DECLARE match_end    DATETIME;
    DECLARE bet_amount   INT;
    DECLARE bet_count    INT;

    -- Set variables
    SET user_id      = NEW.UserID;
    SET user_balance =(SELECT Points FROM User WHERE ID = user_id);
    SET user_status  =(SELECT Status FROM User WHERE ID = user_id);
    SET match_id     = NEW.MatchID;
    SET match_start  =(SELECT StartTime FROM `Match` WHERE ID = match_id);
    SET match_end    =(SELECT EndTime FROM `Match` WHERE ID = match_id);
    SET bet_amount   = NEW.PointAmount;
    SET bet_count    =(SELECT COUNT(*) FROM Bet WHERE UserID = user_id AND MatchID = match_id);

    -- [IC 03]
    IF (bet_amount > user_balance) THEN
      CALL show_error('[IC 03] - BET AMOUNT IS LARGER THAN USER POINTS BALANCE');
    END IF;

    -- [IC 04]
    IF (bet_amount < 1) THEN
      CALL show_error('[IC 04] - BET AMOUNT MUST BE GREATER THAN ZERO');
    END IF;

    -- [IC 05]
    IF (user_status != 'active') THEN
      CALL show_error('[IC 05] - USER ACCOUNT IS NOT ACTIVATED');
    END IF;

    -- [IC 07]
    IF (bet_count = 1) THEN
      CALL show_error('[IC 07] - THIS USER HAS ALREADY PLACED A BET ON THIS MATCH');
    END IF;

    -- [IC 08]
    IF (match_end > CURRENT_TIMESTAMP AND CURRENT_TIMESTAMP > match_start) THEN
      CALL show_error('[IC 08] - CANNOT BET IN A MATCH IN PROGRESS');
    END IF;

    -- [IC 06]
    UPDATE User
    SET points = points - bet_amount
    WHERE id = user_id;

  END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `Bet_before_update`;
DELIMITER //
CREATE TRIGGER `Bet_before_update` BEFORE UPDATE ON `Bet`
 FOR EACH ROW BEGIN
    -- Declare variables
    DECLARE user_id        INT;
    DECLARE user_balance   INT;
    DECLARE user_status    VARCHAR(45);
    DECLARE match_id       INT;
    DECLARE match_start    DATETIME;
    DECLARE match_end      DATETIME;
    DECLARE bet_difference INT;

    -- Set variables
    SET user_id        = NEW.UserID;
    SET user_balance   =(SELECT Points FROM User WHERE ID = user_id);
    SET user_status    =(SELECT Status FROM User WHERE ID = user_id);
    SET match_id       = NEW.MatchID;
    SET match_start    =(SELECT StartTime FROM `Match` WHERE ID = match_id);
    SET match_end      =(SELECT EndTime FROM `Match` WHERE ID = match_id);
    SET bet_difference = NEW.PointAmount - OLD.PointAmount;

    -- [IC 03]
    IF (user_balance - bet_difference < 0) THEN
      CALL show_error('[IC 03] - USER DOES NOT HAVE ENOUGH POINTS TO CHANGE THE BET');
    END IF;

    -- [IC 08]
    IF (match_end > CURRENT_TIMESTAMP AND CURRENT_TIMESTAMP > match_start) THEN
      CALL show_error('[IC 08] - CANNOT EDIT BET IN A MATCH STILL IN PROGRESS');
    END IF;

    -- [IC 06]
    IF (bet_difference != 0) THEN
      UPDATE User
      SET points = points - bet_difference
      WHERE id = user_id;
    END IF;

  END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Country`
--

DROP TABLE IF EXISTS `Country`;
CREATE TABLE IF NOT EXISTS `Country` (
  `CountryCode` char(3) NOT NULL DEFAULT '',
  `Name` varchar(50) NOT NULL,
  PRIMARY KEY (`CountryCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Country`
--

INSERT INTO `Country` (`CountryCode`, `Name`) VALUES
('GBR', 'United Kingdom'),
('HKG', 'Hong Kong'),
('PRT', 'Portugal'),
('UKR', 'Ukraine');

-- --------------------------------------------------------

--
-- Table structure for table `Match`
--

DROP TABLE IF EXISTS `Match`;
CREATE TABLE IF NOT EXISTS `Match` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `StartTime` datetime DEFAULT NULL,
  `EndTime` datetime DEFAULT NULL,
  `Status` enum('started','half time','extended','ended') DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `Match`
--

INSERT INTO `Match` (`ID`, `StartTime`, `EndTime`, `Status`) VALUES
(1, '2015-05-25 16:00:00', '2015-05-25 16:10:00', NULL),
(2, '2015-05-26 14:00:00', '2015-05-26 14:10:00', NULL),
(3, '2015-05-27 17:00:00', '2015-05-27 17:10:00', NULL),
(4, '2015-05-28 12:00:00', '2015-05-28 12:10:00', NULL),
(5, '2015-06-01 18:00:00', '2015-06-30 18:10:00', NULL),
(6, '2015-06-30 18:00:00', '2015-06-30 18:30:00', NULL),
(7, '2015-06-27 18:00:00', '2015-06-27 18:40:00', NULL),
(8, '2015-06-28 05:00:00', '2015-06-28 06:40:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `MatchProgress`
--

DROP TABLE IF EXISTS `MatchProgress`;
CREATE TABLE IF NOT EXISTS `MatchProgress` (
  `TeamID` int(11) NOT NULL,
  `MatchID` int(11) NOT NULL,
  `Goals` int(11) NOT NULL DEFAULT '0',
  `YellowCards` int(11) NOT NULL DEFAULT '0',
  `RedCards` int(11) NOT NULL DEFAULT '0',
  `Defenses` int(11) NOT NULL DEFAULT '0',
  `Result` enum('lost','tie','won') DEFAULT NULL,
  PRIMARY KEY (`TeamID`,`MatchID`),
  KEY `MatchID` (`MatchID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `MatchProgress`
--

INSERT INTO `MatchProgress` (`TeamID`, `MatchID`, `Goals`, `YellowCards`, `RedCards`, `Defenses`, `Result`) VALUES
(1, 1, 0, 0, 0, 0, NULL),
(1, 2, 1, 0, 0, 0, NULL),
(1, 3, 0, 0, 0, 0, NULL),
(1, 4, 2, 0, 0, 0, NULL),
(1, 5, 0, 0, 0, 0, NULL),
(1, 6, 0, 0, 0, 0, NULL),
(1, 7, 0, 0, 0, 0, NULL),
(1, 8, 0, 0, 0, 0, NULL),
(2, 1, 3, 0, 0, 0, NULL),
(2, 2, 0, 0, 0, 0, NULL),
(2, 3, 1, 0, 0, 0, NULL),
(2, 4, 0, 0, 0, 0, NULL),
(2, 5, 2, 0, 0, 0, NULL),
(2, 6, 0, 0, 0, 0, NULL),
(2, 7, 0, 0, 0, 0, NULL),
(2, 8, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Player`
--

DROP TABLE IF EXISTS `Player`;
CREATE TABLE IF NOT EXISTS `Player` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(32) NOT NULL,
  `BirthDate` date DEFAULT NULL,
  `Country` char(3) DEFAULT NULL,
  `Photo` varchar(255) DEFAULT NULL,
  `Stamina` float NOT NULL,
  `Catch` float NOT NULL,
  `KickPower` float NOT NULL,
  `Speed` float NOT NULL,
  `Decay` float NOT NULL,
  `DashRate` float NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Country` (`Country`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `Player`
--

INSERT INTO `Player` (`ID`, `Name`, `BirthDate`, `Country`, `Photo`, `Stamina`, `Catch`, `KickPower`, `Speed`, `Decay`, `DashRate`) VALUES
(1, 'Manuel Neuer', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83),
(2, 'Pepe Reina', '2000-01-06', 'UKR', 'photo2.jpg', 1.3, 1.03, 0.56, 1.23, 2.04, 1.54),
(3, 'Javi Martinez', '1990-12-25', 'HKG', 'photo3.jpg', 1.4, 1.02, 0.16, 1.12, 4.31, 1.93),
(4, 'Medhi Benatia', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83),
(5, 'Dante', '2000-01-06', 'UKR', 'photo2.jpg', 1.3, 1.03, 0.56, 1.23, 2.04, 1.54),
(6, 'Holger Badstuber', '1990-12-25', 'HKG', 'photo3.jpg', 1.4, 1.02, 0.16, 1.12, 4.31, 1.93),
(7, 'David Alaba', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83),
(8, 'Juan Bernat', '2000-01-06', 'UKR', 'photo2.jpg', 1.3, 1.03, 0.56, 1.23, 2.04, 1.54),
(9, 'Philipp Lahm', '1990-12-25', 'HKG', 'photo3.jpg', 1.4, 1.02, 0.16, 1.12, 4.31, 1.93),
(10, 'Sebastian Rode', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83),
(11, 'Xabi Alonso', '2000-01-06', 'UKR', 'photo2.jpg', 1.3, 1.03, 0.56, 1.23, 2.04, 1.54),
(12, 'Bastian Schweinsteiger', '1990-12-25', 'HKG', 'photo3.jpg', 1.4, 1.02, 0.16, 1.12, 4.31, 1.93),
(13, 'Thiago Alcentara', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83),
(14, 'Gianluca Gaudino', '2000-01-06', 'UKR', 'photo2.jpg', 1.3, 1.03, 0.56, 1.23, 2.04, 1.54),
(15, 'Mario Gatze', '1990-12-25', 'HKG', 'photo3.jpg', 1.4, 1.02, 0.16, 1.12, 4.31, 1.93),
(16, 'Franck Rubiery', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83),
(17, 'Mitchell Weiser', '2000-01-06', 'UKR', 'photo2.jpg', 1.3, 1.03, 0.56, 1.23, 2.04, 1.54),
(18, 'Sinan Kurt', '1990-12-25', 'HKG', 'photo3.jpg', 1.4, 1.02, 0.16, 1.12, 4.31, 1.93),
(19, 'Arjen Robben', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83),
(20, 'Thomas Mufcller', '2000-01-06', 'UKR', 'photo2.jpg', 1.3, 1.03, 0.56, 1.23, 2.04, 1.54),
(21, 'Robert Lewandowski', '1990-12-25', 'HKG', 'photo3.jpg', 1.4, 1.02, 0.16, 1.12, 4.31, 1.93),
(22, 'Claudio Pizarro', '2015-05-16', 'PRT', 'photo1.jpg', 1.2, 1.05, 0.86, 1.09, 1.04, 0.83);

-- --------------------------------------------------------

--
-- Table structure for table `Team`
--

DROP TABLE IF EXISTS `Team`;
CREATE TABLE IF NOT EXISTS `Team` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(32) NOT NULL,
  `Rank` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `Team`
--

INSERT INTO `Team` (`ID`, `Name`, `Rank`) VALUES
(1, 'Augsburg', 40),
(2, 'SC Freiburg', 20);

-- --------------------------------------------------------

--
-- Table structure for table `TeamComposition`
--

DROP TABLE IF EXISTS `TeamComposition`;
CREATE TABLE IF NOT EXISTS `TeamComposition` (
  `TeamID` int(11) NOT NULL,
  `PlayerID` int(11) NOT NULL,
  `ShirtNumber` int(11) DEFAULT NULL,
  `Position` enum('goalkeeper','midfield','advanced','defense') DEFAULT NULL,
  PRIMARY KEY (`TeamID`,`PlayerID`),
  KEY `PlayerID` (`PlayerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `TeamComposition`
--

INSERT INTO `TeamComposition` (`TeamID`, `PlayerID`, `ShirtNumber`, `Position`) VALUES
(1, 1, 1, 'goalkeeper'),
(1, 2, 2, 'defense'),
(1, 3, 3, 'defense'),
(1, 4, 4, 'defense'),
(1, 5, 5, 'midfield'),
(1, 6, 6, 'midfield'),
(1, 7, 7, 'midfield'),
(1, 8, 8, 'midfield'),
(1, 9, 9, 'advanced'),
(1, 10, 10, 'advanced'),
(1, 11, 11, 'advanced'),
(2, 12, 1, 'goalkeeper'),
(2, 13, 2, 'defense'),
(2, 14, 3, 'defense'),
(2, 15, 4, 'defense'),
(2, 16, 5, 'midfield'),
(2, 17, 6, 'midfield'),
(2, 18, 7, 'midfield'),
(2, 19, 8, 'midfield'),
(2, 20, 9, 'advanced'),
(2, 21, 10, 'advanced'),
(2, 22, 11, 'advanced');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE IF NOT EXISTS `User` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(64) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `UserName` varchar(32) NOT NULL,
  `FirstName` varchar(32) DEFAULT NULL,
  `LastName` varchar(32) DEFAULT NULL,
  `Avatar` varchar(255) DEFAULT NULL,
  `Points` int(11) NOT NULL DEFAULT '200',
  `Status` enum('pending activation','active','suspended','disabled') NOT NULL DEFAULT 'pending activation',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (id, email, pw_hash, userName, firstName, lastName, avatar, points, status) VALUES
(1, 'user1@example.com', '098f6bcd4621d373cade4e832627b4f6', 'User1', 'Sónia', 'Silva', 'image1.jpg', 200, 'active'),
(2, 'user2@example.com', '098f6bcd4621d373cade4e832627b4f6', 'User2', 'Marta', 'Maria', 'image2.jpg', 100, 'active'),
(3, 'user3@example.com', '098f6bcd4621d373cade4e832627b4f6', 'User3', 'Orquídea', 'Olga', 'image3.jpg', 100, 'active'),
(4, 'user4@example.com', '098f6bcd4621d373cade4e832627b4f6', 'User4', 'Liliana', 'Larga', 'image4.jpg', 200, 'suspended');

--
-- Triggers `User`
--
DROP TRIGGER IF EXISTS `User_before_update`;
DELIMITER //
CREATE TRIGGER `User_before_update` BEFORE UPDATE ON `User`
 FOR EACH ROW BEGIN
    -- Declare variables
    DECLARE user_id         INT;
    DECLARE user_new_status VARCHAR(30);

    -- Set variables
    SET user_id         = NEW.ID;
    SET user_new_status = NEW.Status;

    -- [IC 10]
    IF (user_new_status = 'disabled') THEN
      UPDATE Bet
      SET Bet.Obsolete = 1
      WHERE Bet.UserID = user_id;
    END IF;

  END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `UserComment`
--

DROP TABLE IF EXISTS `UserComment`;
CREATE TABLE IF NOT EXISTS `UserComment` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `MatchID` int(11) NOT NULL,
  `Text` varchar(255) NOT NULL,
  `Parent` int(11) DEFAULT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `MatchID` (`MatchID`),
  KEY `Parent` (`Parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `UserFriend`
--

DROP TABLE IF EXISTS `UserFriend`;
CREATE TABLE IF NOT EXISTS `UserFriend` (
  `UserID` int(11) NOT NULL,
  `FriendID` int(11) NOT NULL,
  PRIMARY KEY (`UserID`,`FriendID`),
  KEY `FriendID` (`FriendID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Bet`
--
ALTER TABLE `Bet`
  ADD CONSTRAINT `Bet_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `User` (id),
  ADD CONSTRAINT `Bet_ibfk_2` FOREIGN KEY (`MatchID`) REFERENCES `Match` (`ID`);

--
-- Constraints for table `MatchProgress`
--
ALTER TABLE `MatchProgress`
  ADD CONSTRAINT `MatchProgress_ibfk_1` FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`),
  ADD CONSTRAINT `MatchProgress_ibfk_2` FOREIGN KEY (`MatchID`) REFERENCES `Match` (`ID`);

--
-- Constraints for table `Player`
--
ALTER TABLE `Player`
  ADD CONSTRAINT `Player_ibfk_1` FOREIGN KEY (`Country`) REFERENCES `Country` (`CountryCode`);

--
-- Constraints for table `TeamComposition`
--
ALTER TABLE `TeamComposition`
  ADD CONSTRAINT `TeamComposition_ibfk_1` FOREIGN KEY (`TeamID`) REFERENCES `Team` (`ID`),
  ADD CONSTRAINT `TeamComposition_ibfk_2` FOREIGN KEY (`PlayerID`) REFERENCES `Player` (`ID`);

--
-- Constraints for table `UserComment`
--
ALTER TABLE `UserComment`
  ADD CONSTRAINT `UserComment_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `User` (id),
  ADD CONSTRAINT `UserComment_ibfk_2` FOREIGN KEY (`MatchID`) REFERENCES `Match` (`ID`),
  ADD CONSTRAINT `UserComment_ibfk_3` FOREIGN KEY (`Parent`) REFERENCES `UserComment` (`ID`);

--
-- Constraints for table `UserFriend`
--
ALTER TABLE `UserFriend`
  ADD CONSTRAINT `UserFriend_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `User` (id),
  ADD CONSTRAINT `UserFriend_ibfk_2` FOREIGN KEY (`FriendID`) REFERENCES `User` (id);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
