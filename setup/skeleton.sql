-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 18, 2012 at 02:18 AM
-- Server version: 5.5.24
-- PHP Version: 5.3.10-1ubuntu3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `webtest`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `emp_id` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` char(64) DEFAULT NULL,
  `salt` char(64) NOT NULL,
  `classification` varchar(25) NOT NULL DEFAULT 'Employee',
  `payment_eligible` tinyint(1) NOT NULL DEFAULT '0',
  `ssn` varchar(11) NOT NULL,
  `phone_mobile` varchar(25) NOT NULL,
  `phone_home` varchar(25) NOT NULL,
  `phone_other` varchar(25) NOT NULL,
  `website` varchar(320) NOT NULL,
  `email` varchar(320) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(5) NOT NULL,
  `zipcode` varchar(5) NOT NULL,
  `permission_contacts` int(10) unsigned NOT NULL,
  `permission_payments` int(10) unsigned NOT NULL,
  `permission_jobs` int(10) unsigned NOT NULL,
  UNIQUE KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=574 ;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_type` varchar(25) NOT NULL,
  `image` blob NOT NULL,
  `image_size` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

CREATE TABLE IF NOT EXISTS `inspections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `inspector` int(11) NOT NULL,
  `referrer` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `paid_by` int(11) NOT NULL,
  `comments` varchar(255) NOT NULL,
  `report` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cust_billing` int(11) NOT NULL,
  `cust_shipping` int(11) NOT NULL,
  `job_name` varchar(255) NOT NULL,
  `phone1` varchar(50) NOT NULL,
  `phone2` varchar(50) NOT NULL,
  `phone3` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `job_status`
--

CREATE TABLE IF NOT EXISTS `job_status` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `what_happened` varchar(255) NOT NULL,
  `new_status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=96 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE IF NOT EXISTS `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

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
  `code` char(1) NOT NULL,
  `Description` varchar(255) NOT NULL,
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
