-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Host: mysql.bgggcr.shalom.craimer.org
-- Generation Time: Jan 31, 2016 at 01:33 PM
-- Server version: 5.6.25
-- PHP Version: 5.6.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `bgggcr_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `authrequests`
--

CREATE TABLE IF NOT EXISTS `authrequests` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `username` varchar(250) NOT NULL COMMENT 'BGG username to athenticate',
	  `requested_at` int(11) NOT NULL COMMENT 'Date/time at which the authentication was requested',
	  `cookie` varchar(250) NOT NULL COMMENT 'The code used in the authentication URL, once the user clicks on the link.',
	  `gm_sent_at` int(11) DEFAULT NULL COMMENT 'UNIX timestamp when the geekmail was sent',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `cookie` (`cookie`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='The list of users waiting to be authenticated' AUTO_INCREMENT=2 ;
	
	--
-- Dumping data for table `authrequests`
--


-- --------------------------------------------------------

--
-- Table structure for table `awards`
--

CREATE TABLE IF NOT EXISTS `awards` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `giver_user_id` int(11) NOT NULL COMMENT 'The ID of the user from the users table',
	  `receiver_bgg_username` varchar(250) NOT NULL COMMENT 'The BGG username of the recipient',
	  `year` int(11) NOT NULL COMMENT 'The year in which the star was awarded',
	  `month` int(11) NOT NULL COMMENT 'The month in which the star was awarded',
	  `awarded_at` int(11) DEFAULT NULL COMMENT 'The date on which the microbadge was awarded, or NULL if not awarded',
	  `award_verified` tinyint(4) DEFAULT NULL COMMENT 'After the award was verified, the profile will be checked, if the microbadge is there, this will be set to ''1''. Otherwise, it is NULL.',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `giver_user_id` (`giver_user_id`,`year`,`month`),
	  KEY `awarded_at` (`awarded_at`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Microbadges waiting to be awarded and those already awarded' AUTO_INCREMENT=7 ;
	
	--
-- Dumping data for table `awards`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `username` varchar(250) NOT NULL COMMENT 'BGG username',
	  `cookie` varchar(250) DEFAULT NULL COMMENT 'The code used in the authentication URL and the login cookie. This is the one that has been verified by using the URL sent in the geekmail.',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `username` (`username`),
	  UNIQUE KEY `uniq_code` (`cookie`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='The list of BGG users' AUTO_INCREMENT=4 ;
	
	--
-- Dumping data for table `users`
--


