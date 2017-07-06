-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: sql312.rf.gd
-- Generation Time: Jul 06, 2017 at 12:23 PM
-- Server version: 5.6.35-81.0
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `rfgd_20333149_webtest`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `emp_id` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `username` varchar(32) NOT NULL,
  `password` char(64) DEFAULT NULL,
  `salt` char(64) DEFAULT NULL,
  `stretching` int(11) DEFAULT NULL,
  `fail_logins` int(11) NOT NULL DEFAULT '0',
  `fail_pass_change` int(11) NOT NULL DEFAULT '0',
  `classification` varchar(25) NOT NULL DEFAULT 'Employee',
  `payment_eligible` tinyint(1) NOT NULL DEFAULT '0',
  `phone_mobile` varchar(25) DEFAULT NULL,
  `phone_home` varchar(25) DEFAULT NULL,
  `phone_other` varchar(25) DEFAULT NULL,
  `website` varchar(320) DEFAULT NULL,
  `email` varchar(320) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(5) DEFAULT NULL,
  `zipcode` varchar(5) DEFAULT NULL,
  UNIQUE KEY `emp_id` (`emp_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `contact_permission`
--

CREATE TABLE IF NOT EXISTS `contact_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id1` int(11) DEFAULT NULL,
  `id2` int(11) DEFAULT NULL,
  `permission` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id1` (`id1`,`id2`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `cost_estimations`
--

CREATE TABLE IF NOT EXISTS `cost_estimations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `cost_type` varchar(25) NOT NULL,
  `cost` double NOT NULL,
  `jobs` float NOT NULL,
  `days` float NOT NULL,
  `weeks` float NOT NULL,
  `months` float NOT NULL,
  `years` float NOT NULL,
  `jobhours` float NOT NULL,
  `quarters` float NOT NULL,
  `employees` int(11) NOT NULL,
  `notes` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE IF NOT EXISTS `equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  `quantity` int(32) NOT NULL DEFAULT '1',
  `unit` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `last_known` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `img_id` int(11) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=580 ;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uploader` int(11) NOT NULL,
  `size` int(11) DEFAULT NULL,
  `file_vga` varchar(255) DEFAULT NULL,
  `file_thumb` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cust_billing` int(11) NOT NULL,
  `cust_shipping` int(11) NOT NULL,
  `job_name` varchar(255) NOT NULL,
  `phone_notify_id` int(11) DEFAULT NULL,
  `email` varchar(320) DEFAULT NULL,
  `email2` varchar(320) DEFAULT NULL,
  `comments` text,
  `invoice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `job_expenses`
--

CREATE TABLE IF NOT EXISTS `job_expenses` (
  `job_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `expense` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `job_status`
--

CREATE TABLE IF NOT EXISTS `job_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jobid` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `what_happened` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `job_tasks`
--

CREATE TABLE IF NOT EXISTS `job_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `job` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `position` int(32) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `img_id` int(11) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=114 ;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_to` int(11) NOT NULL,
  `paid_by` int(11) NOT NULL,
  `amount_earned` decimal(10,2) NOT NULL,
  `date_earned` date NOT NULL,
  `comments` varchar(100) DEFAULT NULL,
  `category` varchar(64) NOT NULL,
  `date_paid` date DEFAULT NULL,
  `inspection` int(11) DEFAULT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  UNIQUE KEY `payment_id` (`payment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `id` int(32) NOT NULL,
  `item` int(11) NOT NULL,
  `status` varchar(16) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `status_codes`
--

CREATE TABLE IF NOT EXISTS `status_codes` (
  `code` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(255) NOT NULL,
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_categories`
--

CREATE TABLE IF NOT EXISTS `transaction_categories` (
  `code` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `version`
--

CREATE TABLE IF NOT EXISTS `version` (
  `id` varchar(255) NOT NULL,
  `num` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
