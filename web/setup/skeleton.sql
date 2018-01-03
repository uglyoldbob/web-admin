/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `emp_id` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` char(64) DEFAULT NULL,
  `salt` char(64) NOT NULL,
  `stretching` int(11) NOT NULL,
  `fail_logins` int(11) NOT NULL DEFAULT '0',
  `fail_pass_change` int(11) NOT NULL DEFAULT '0',
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
  UNIQUE KEY `emp_id` (`emp_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id1` int(11) DEFAULT NULL,
  `id2` int(11) NOT NULL,
  `permission` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id1` (`id1`,`id2`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_estimations` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment` (
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
) ENGINE=MyISAM AUTO_INCREMENT=579 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uploader` int(11) NOT NULL,
  `size` int(11) DEFAULT NULL,
  `file_vga` varchar(255) NOT NULL,
  `file_thumb` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspections` (
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jobid` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `what_happened` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `job` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `position` int(32) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `img_id` int(11) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=111 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(32) NOT NULL,
  `item` int(11) NOT NULL,
  `status` varchar(16) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_codes` (
  `code` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(255) NOT NULL,
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `version` (
  `id` varchar(255) NOT NULL,
  `num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `version` VALUES ('this',1),('contacts',1),('contact_permission',1),('cost_estimations',1),('equipment',1),('images',1),('inspections',1),('jobs',1),('job_status',1),('job_tasks',1),('locations',1),('payments',1),('properties',1),('status',1),('status_codes',1);
