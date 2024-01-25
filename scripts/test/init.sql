-- MariaDB dump 10.19-11.1.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: i3crm
-- ------------------------------------------------------
-- Server version	11.1.3-MariaDB-1:11.1.3+maria~ubu2204

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `contact`
--

use i3crm;

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `cid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `firstName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` VALUES
(1,'admin','foo','last','admin@admin.tld','555-555-5555'),
(2,'test','test','test','test2@rumplestilzken.com','test'),
(3,'test2','test2','test2','test3@rumplestilzken.com','test2'),
(4,'test3','test3','test3','test4@rumplestilzken.com','test3'),
(5,'test4','test4','test4','test@rumplestilzken.com','test4'),
(6,'test5','test5','test5','test5@rumplestilzken.com','test5'),
(7,'test6','test6','test6','test6@rumplestilzken.com','test6');
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_amazon`
--

DROP TABLE IF EXISTS `contact_amazon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_amazon` (
  `cid` mediumint(8) unsigned NOT NULL,
  `amazon_name` varchar(255) NOT NULL,
  PRIMARY KEY (`amazon_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_amazon`
--

LOCK TABLES `contact_amazon` WRITE;
/*!40000 ALTER TABLE `contact_amazon` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_amazon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_email`
--

DROP TABLE IF EXISTS `contact_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_email` (
  `cid` int(10) unsigned NOT NULL,
  `email` varchar(512) NOT NULL,
  PRIMARY KEY (`cid`,`email`),
  UNIQUE KEY `unique_contact_email_email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_email`
--

LOCK TABLES `contact_email` WRITE;
/*!40000 ALTER TABLE `contact_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `key`
--

DROP TABLE IF EXISTS `key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `key` (
  `kid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cid` mediumint(8) unsigned NOT NULL,
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `serial` varchar(255) NOT NULL,
  `slot` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`kid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `key`
--

LOCK TABLES `key` WRITE;
/*!40000 ALTER TABLE `key` DISABLE KEYS */;
INSERT INTO `key` VALUES
(1,2,'2023-12-16',NULL,'1',1),
(2,3,'2023-12-16',NULL,'1',1),
(3,7,'2023-12-16',NULL,'1',1);
/*!40000 ALTER TABLE `key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member` (
  `cid` mediumint(8) unsigned NOT NULL,
  `emergencyName` varchar(255) NOT NULL,
  `emergencyPhone` varchar(16) NOT NULL,
  `emergencyRelation` varchar(255) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member`
--

LOCK TABLES `member` WRITE;
/*!40000 ALTER TABLE `member` DISABLE KEYS */;
INSERT INTO `member` VALUES
(2,'sdfa','asfda','asdfas'),
(3,'test2','test2','test2'),
(4,'test3','test3','test3'),
(5,'test4','test4','test4'),
(6,'test5','test5','test5'),
(7,'test6','test6','test6');
/*!40000 ALTER TABLE `member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `membership`
--

DROP TABLE IF EXISTS `membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership` (
  `sid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cid` mediumint(8) unsigned NOT NULL,
  `pid` mediumint(8) unsigned NOT NULL,
  `start` date NOT NULL,
  `end` date DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membership`
--

LOCK TABLES `membership` WRITE;
/*!40000 ALTER TABLE `membership` DISABLE KEYS */;
INSERT INTO `membership` VALUES
(1,1,17,'2021-12-23',NULL),
(2,2,6,'2023-12-10',NULL),
(3,3,6,'2023-12-10',NULL),
(4,4,6,'2023-12-10','2023-12-10'),
(5,5,6,'2023-12-10',NULL),
(6,6,6,'2023-12-17',NULL),
(7,7,6,'2023-12-16','2023-12-19');
/*!40000 ALTER TABLE `membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentor`
--

DROP TABLE IF EXISTS `mentor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor` (
  `cid` mediumint(8) unsigned NOT NULL,
  `mentor_cid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`cid`,`mentor_cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentor`
--

LOCK TABLES `mentor` WRITE;
/*!40000 ALTER TABLE `mentor` DISABLE KEYS */;
/*!40000 ALTER TABLE `mentor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module` (
  `did` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `revision` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`did`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module`
--

LOCK TABLES `module` WRITE;
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
INSERT INTO `module` VALUES
(4,'core',3),
(5,'member',6),
(6,'key',2),
(7,'variable',1),
(8,'payment',1),
(9,'amazon_payment',1),
(10,'billing',1),
(11,'contact',2),
(12,'user',2),
(13,'mentor',1),
(14,'profile_picture',1),
(15,'template',1),
(16,'services',1),
(17,'secrets',1),
(18,'reports',1),
(19,'storage',1),
(20,'debug',1);
/*!40000 ALTER TABLE `module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `pmtid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `code` varchar(8) NOT NULL,
  `value` mediumint(8) NOT NULL,
  `credit` mediumint(8) unsigned NOT NULL,
  `debit` mediumint(8) unsigned NOT NULL,
  `method` varchar(255) NOT NULL,
  `confirmation` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduleId` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`pmtid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_amazon`
--

DROP TABLE IF EXISTS `payment_amazon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_amazon` (
  `pmtid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `amazon_name` varchar(255) NOT NULL,
  PRIMARY KEY (`pmtid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_amazon`
--

LOCK TABLES `payment_amazon` WRITE;
/*!40000 ALTER TABLE `payment_amazon` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_amazon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plan`
--

DROP TABLE IF EXISTS `plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plan` (
  `pid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` varchar(6) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `voting` tinyint(1) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plan`
--

LOCK TABLES `plan` WRITE;
/*!40000 ALTER TABLE `plan` DISABLE KEYS */;
INSERT INTO `plan` VALUES
(1,'Core - 89','89.00',0,1),
(2,'Standard - 39','39.00',0,0),
(3,'Starving Hacker - 39','39.00',0,0),
(4,'Member - 100','100.00',0,1),
(5,'Sponsored - 5','5.00',0,0),
(6,'Scholarship','0.00',1,1),
(8,'Sponsored - 50','50.00',0,0),
(9,'Hiatus','0.00',1,0),
(10,'Member - 49','49.00',1,0),
(14,'Member - $59','59',1,1),
(13,'Onboarding','0.00',1,0),
(15,'Left without notice','',1,0),
(16,'Left in good standing','',1,0),
(17,'CONTACT TREASURER','0.00',1,0),
(19,'Landlord','0',1,0);
/*!40000 ALTER TABLE `plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resetPassword`
--

DROP TABLE IF EXISTS `resetPassword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resetPassword` (
  `cid` mediumint(8) unsigned NOT NULL,
  `code` varchar(40) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resetPassword`
--

LOCK TABLES `resetPassword` WRITE;
/*!40000 ALTER TABLE `resetPassword` DISABLE KEYS */;
INSERT INTO `resetPassword` VALUES
(2,'2bea4f4c541ce29b18e427d6731559b920bda9c3'),
(3,'56644c267354a2f5954369c8ee094fb744b275f6'),
(4,'684404c484c8a62c4a30e05918798008b0e690c7'),
(5,'91923ae305c27fdd1cff570ac03ddb10f41ca95d'),
(6,'06d37c3b56e731d06fa79d2b8104185f3ab5d7cb'),
(7,'6bae1036a400d162a60d7eefcce4006bb52660ad');
/*!40000 ALTER TABLE `resetPassword` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `rid` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES
(1,'authenticated'),
(2,'member'),
(3,'director'),
(4,'president'),
(5,'vp'),
(6,'secretary'),
(7,'treasurer'),
(8,'webAdmin'),
(9,'keymaster'),
(10,'storagemaster');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permission`
--

DROP TABLE IF EXISTS `role_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permission` (
  `rid` mediumint(8) unsigned NOT NULL,
  `permission` varchar(255) NOT NULL,
  PRIMARY KEY (`rid`,`permission`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permission`
--

LOCK TABLES `role_permission` WRITE;
/*!40000 ALTER TABLE `role_permission` DISABLE KEYS */;
INSERT INTO `role_permission` VALUES
(2,'contact_view'),
(2,'member_list'),
(2,'member_view'),
(2,'report_view'),
(2,'storage_view'),
(3,'contact_add'),
(3,'contact_delete'),
(3,'contact_edit'),
(3,'contact_list'),
(3,'contact_view'),
(3,'key_edit'),
(3,'key_view'),
(3,'member_add'),
(3,'member_edit'),
(3,'member_list'),
(3,'member_membership_edit'),
(3,'member_membership_view'),
(3,'member_plan_edit'),
(3,'member_view'),
(3,'mentor_delete'),
(3,'mentor_edit'),
(3,'mentor_view'),
(3,'payment_delete'),
(3,'payment_edit'),
(3,'payment_view'),
(3,'reports_delete'),
(3,'reports_edit'),
(3,'reports_view'),
(3,'report_view'),
(3,'services_delete'),
(3,'services_edit'),
(3,'services_view'),
(3,'storage_delete'),
(3,'storage_edit'),
(3,'storage_view'),
(3,'user_add'),
(3,'user_delete'),
(3,'user_edit'),
(3,'user_permissions_edit'),
(3,'user_role_edit'),
(7,'key_edit'),
(7,'key_view'),
(7,'member_membership_view'),
(7,'member_view'),
(7,'payment_delete'),
(7,'payment_edit'),
(7,'payment_view'),
(7,'report_view'),
(8,'contact_list'),
(8,'key_edit'),
(8,'member_delete'),
(8,'member_edit'),
(8,'member_list'),
(8,'module_upgrade'),
(8,'secrets_delete'),
(8,'secrets_edit'),
(8,'secrets_view'),
(8,'user_edit'),
(8,'user_permissions_edit'),
(8,'user_role_edit'),
(9,'key_delete'),
(9,'key_edit'),
(9,'key_view'),
(10,'reports_view'),
(10,'storage_delete'),
(10,'storage_edit'),
(10,'storage_view');
/*!40000 ALTER TABLE `role_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storage_log`
--

DROP TABLE IF EXISTS `storage_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `storage_log` (
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `pid` mediumint(8) unsigned NOT NULL,
  `desc` varchar(255) NOT NULL,
  `cid` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `reapmonth` mediumint(8) unsigned NOT NULL DEFAULT 1,
  `reapdate` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storage_log`
--

LOCK TABLES `storage_log` WRITE;
/*!40000 ALTER TABLE `storage_log` DISABLE KEYS */;
INSERT INTO `storage_log` VALUES
('2023-12-16 05:44:38','1','Add',1,'','','',1,'2023-12-16'),
('2023-12-16 05:45:07','1','Assign',1,'','2','',1,'2023-12-16'),
('2023-12-16 05:49:51','1','Add',2,'','','',1,'2023-12-16'),
('2023-12-16 05:50:16','1','Assign',2,'','3','',1,'2023-12-16'),
('2023-12-16 05:50:22','1','Vacate',2,'','3','',1,'2023-12-16'),
('2023-12-16 05:50:26','1','Assign',2,'','3','',1,'2023-12-16');
/*!40000 ALTER TABLE `storage_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storage_plot`
--

DROP TABLE IF EXISTS `storage_plot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `storage_plot` (
  `pid` mediumint(8) unsigned NOT NULL,
  `desc` varchar(255) NOT NULL,
  `cid` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `reapmonth` mediumint(8) unsigned NOT NULL DEFAULT 1,
  `reapdate` date NOT NULL,
  PRIMARY KEY (`pid`),
  UNIQUE KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storage_plot`
--

LOCK TABLES `storage_plot` WRITE;
/*!40000 ALTER TABLE `storage_plot` DISABLE KEYS */;
INSERT INTO `storage_plot` VALUES
(1,'','2','',1,'2023-12-16'),
(2,'','3','',1,'2023-12-16');
/*!40000 ALTER TABLE `storage_plot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `cid` mediumint(11) unsigned NOT NULL,
  `username` varchar(32) NOT NULL,
  `hash` varchar(40) DEFAULT NULL,
  `salt` varchar(16) DEFAULT NULL,
  `makepi-uuid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES
(1,'admin','c21bff3e485bdc98e5de614a76e0926932ae981a','?)#)^|@5~yy+]2`u',NULL),
(2,'ttest','1cff1734446d5020955b4dc8147096786f97da0c','[&~\\]x8r7.hfu[%r',NULL),
(3,'test',NULL,NULL,NULL),
(4,'test3',NULL,NULL,NULL),
(5,'test4',NULL,NULL,NULL),
(6,'test5',NULL,NULL,NULL),
(7,'test6',NULL,NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `cid` mediumint(8) unsigned NOT NULL,
  `rid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`cid`,`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role`
--

LOCK TABLES `user_role` WRITE;
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
INSERT INTO `user_role` VALUES
(1,8),
(2,2),
(3,2),
(4,2),
(5,2),
(6,2),
(7,2);
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variable`
--

DROP TABLE IF EXISTS `variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variable` (
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variable`
--

LOCK TABLES `variable` WRITE;
/*!40000 ALTER TABLE `variable` DISABLE KEYS */;
INSERT INTO `variable` VALUES
('payment_last_date','2013-02-28'),
('billing_last_date','2021-12-17'),
('amazon_payment_last_email','2020-07-20'),
('slack_token','no'),
('amazon_payment_access_key_id','no'),
('amazon_payment_secret','no'),
('foxycart_apikey','no'),
('i3_epoch','2009-07-01'),
('storage_reap_months','111111011110'),
('storage_admin_email','test_storage@i3detroit.org'),
('storage_send_announce','1'),
('storage_announce_address','i3detroit-announce@googlegroups.com'),
('storage_subject_weekOne','{{month}} Storage Cleaning e-mail #1'),
('storage_body_weekOne','This is the announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nPlots to be vacated:\r\n{{plotlist}}\r\n\r\nShould you not be able to take your things home, the second tier of the pallet racks over the carts is available as temporary storage from now until {{returnby}}.\r\n\r\nWe no longer use the wiki to indicate ownership of storage plots, now there is a new addition to the CRM which does that.  If you want to move to a different plot you can check the new \"Storage\" tab at the top to find vacant ones and from your profile page you can unassign your current plot and assign yourself to a vacant one.  \r\n\r\nOn {{outby}} the contents of the plots to be vacated will be moved to the dumpster.  This can happen any time from midnight on Tuesday morning, and for the six days following.  If you have to ask which time zone, you will probably be digging your stuff out of the dumpster.  If you cannot move your things by then you need to have another member move them for you (another member that is not me).  There is very little temporary storage space allocated this month so you are encouraged to take your things home if you find that you are not using them on a regular basis.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so while you have to move your things out you may not put them back at the end of the month.  \r\n\r\nTimeline:\r\nOut by: {{outby}}\r\nCan return on: {{returnon}}\r\nHave to be returned by: {{returnby}}\r\n\r\nThe next e-mail will go out in 1 week as a reminder. '),
('storage_subject_announce_weekOne','{{month}} Storage Cleaning e-mail #1'),
('storage_body_announce_weekOne','This is the announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nPlots to be vacated:\r\n{{plotlist}}\r\n\r\nShould you not be able to take your things home, the second tier of the pallet racks over the carts is available as temporary storage from now until {{returnby}}.\r\n\r\nWe no longer use the wiki to indicate ownership of storage plots, now there is a new addition to the CRM which does that.  If you want to move to a different plot you can check the new \"Storage\" tab at the top to find vacant ones and from your profile page you can unassign your current plot and assign yourself to a vacant one.  \r\n\r\nOn {{outby}} the contents of the plots to be vacated will be moved to the dumpster.  This can happen any time from midnight on Tuesday morning, and for the six days following.  If you have to ask which time zone, you will probably be digging your stuff out of the dumpster.  If you cannot move your things by then you need to have another member move them for you (another member that is not me).  There is very little temporary storage space allocated this month so you are encouraged to take your things home if you find that you are not using them on a regular basis.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so while you have to move your things out you may not put them back at the end of the month.  \r\n\r\nTimeline:\r\nOut by: {{outby}}\r\nCan return on: {{returnon}}\r\nHave to be returned by: {{returnby}}\r\n\r\nThe next e-mail will go out in 1 week as a reminder. '),
('storage_subject_weekTwo','{{month}} Storage Cleaning e-mail #2'),
('storage_body_weekTwo','This is the second announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nPlots to be vacated:\r\n{{plotlist}}\r\n\r\nShould you not be able to take your things home, the second tier of the pallet racks over the carts is available as temporary storage from now until {{returnby}}.\r\n\r\nWe no longer use the wiki to indicate ownership of storage plots, now there is a new addition to the CRM which does that.  If you want to move to a different plot you can check the new \"Storage\" tab at the top to find vacant ones and from your profile page you can unassign your current plot and assign yourself to a vacant one.  \r\n\r\nOn {{outby}} the contents of the plots to be vacated will be moved to the dumpster.  This can happen any time from midnight on Tuesday morning, and for the six days following.  If you have to ask which time zone, you will probably be digging your stuff out of the dumpster.  If you cannot move your things by then you need to have another member move them for you (another member that is not me).  There is very little temporary storage space allocated this month so you are encouraged to take your things home if you find that you are not using them on a regular basis.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so while you have to move your things out you may not put them back at the end of the month.  \r\n\r\nTimeline:\r\nOut by: {{outby}}\r\nCan return on: {{returnon}}\r\nHave to be returned by: {{returnby}}\r\n\r\nThe next e-mail will go out in 1 week after the plot contents have been disposed of. '),
('storage_subject_announce_weekTwo','{{month}} Storage Cleaning e-mail #2'),
('storage_body_announce_weekTwo','This is the second announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nPlots to be vacated:\r\n{{plotlist}}\r\n\r\nShould you not be able to take your things home, the second tier of the pallet racks over the carts is available as temporary storage from now until {{returnby}}.\r\n\r\nWe no longer use the wiki to indicate ownership of storage plots, now there is a new addition to the CRM which does that.  If you want to move to a different plot you can check the new \"Storage\" tab at the top to find vacant ones and from your profile page you can unassign your current plot and assign yourself to a vacant one.  \r\n\r\nOn {{outby}} the contents of the plots to be vacated will be moved to the dumpster.  This can happen any time from midnight on Tuesday morning, and for the six days following.  If you have to ask which time zone, you will probably be digging your stuff out of the dumpster.  If you cannot move your things by then you need to have another member move them for you (another member that is not me).  There is very little temporary storage space allocated this month so you are encouraged to take your things home if you find that you are not using them on a regular basis.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so while you have to move your things out you may not put them back at the end of the month.  \r\n\r\nTimeline:\r\nOut by: {{outby}}\r\nCan return on: {{returnon}}\r\nHave to be returned by: {{returnby}}\r\n\r\nThe next e-mail will go out in 1 week after the plot contents have been disposed of. \r\nIf you know anyone who has stuff in one of these plots please contact them to make sure everything they want to keep has been moved.  '),
('storage_subject_weekThree','{{month}} Storage Cleaning e-mail #3'),
('storage_body_weekThree','This is the third announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nAs per our interim storage policy I, with help, have cleaned out the storage plots {{plotlist}}.  The remaining contents have been moved to the dumpster or the graveyard.  This isn\'t a warning, all the items have already been moved.  On {{returnon}} the people who have storage plots on this list and are still members can move their things back (this will be accompanied by another e-mail).  The temporary storage plots will have to be vacated by the end of the month or the contents will get the same treatment as the stuff left in plots {{plotlist}} got.  If you know someone who had storage in one of these plots this is beyond your last chance to retrieve the contents, but it is currently all on the top of the other dumpster contents.  Remember, trash day is thursday morning.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so you may not re-occupy a storage plot.  \r\n\r\nTimeline:\r\nCan return on: {{returnon}}\r\nHave to be returned by: {{returnby}}\r\n\r\nThe next e-mail will go out as a reminder to move from the temporary storage area. '),
('storage_subject_announce_weekThree','{{month}} Storage Cleaning e-mail #3'),
('storage_body_announce_weekThree','This is the third announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nAs per our interim storage policy I, with help, have cleaned out the storage plots {{plotlist}}.  The remaining contents have been moved to the dumpster or the graveyard.  This isn\'t a warning, all the items have already been moved.  On {{returnon}} the people who have storage plots on this list and are still members can move their things back (this will be accompanied by another e-mail).  The temporary storage plots will have to be vacated by the end of the month or the contents will get the same treatment as the stuff left in plots {{plotlist}} got.  If you know someone who had storage in one of these plots this is beyond your last chance to retrieve the contents, but it is currently all on the top of the other dumpster contents.  Remember, trash day is thursday morning.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so you may not re-occupy a storage plot.  \r\n\r\nTimeline:\r\nCan return on: {{returnon}}\r\nHave to be returned by: {{returnby}}\r\n\r\nThe next e-mail will go out as a reminder to move from the temporary storage area. '),
('storage_subject_weekFour','{{month}} Storage Cleaning e-mail #4'),
('storage_body_weekFour','This is the fourth announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nAs per our interim storage policy the residents of plots {{plotlist}} may now move back into their old storage plots.  This needs to happen by {{returnby}}.  The next day all plots still unoccupied will be put up for any other member to claim.  Also, anything left in the temporary storage location will be disposed of.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so you may not re-occupy a storage plot.  '),
('storage_subject_announce_weekFour','{{month}} Storage Cleaning e-mail #4'),
('storage_body_announce_weekFour','This is the fourth announcement of who has to vacate their storage plots (final one of the year).  See the 2016-10-04 meeting minutes post.  \r\n\r\nAs per our interim storage policy the residents of plots {{plotlist}} may now move back into their old storage plots.  This needs to happen by {{returnby}}.  The next day all plots still unoccupied will be put up for any other member to claim.  Also, anything left in the temporary storage location will be disposed of.  \r\n\r\nIf you are not listed in the CRM as either \"Member - 49\" or \"Scholarship\" you are not entitled to a member storage spot, so you may not re-occupy a storage plot.  '),
('storage_send_html',''),
('storage_email_headers','1'),
('storage_send_members','1'),
('treasurer_email','treasurer@i3detroit.org'),
('makepiiframeurl','https://makepi-iframe.i3detroit.org'),
('jwtsecret','no'),
('environment','TEST'),
('contact_email','test_contact@i3detroit.org');
/*!40000 ALTER TABLE `variable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xml_log`
--

DROP TABLE IF EXISTS `xml_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xml_log` (
  `xml_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xml_log`
--

LOCK TABLES `xml_log` WRITE;
/*!40000 ALTER TABLE `xml_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `xml_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-12-16  7:02:57
