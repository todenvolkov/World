-- MySQL dump 10.13  Distrib 5.1.62, for pc-linux-gnu (x86_64)
--
-- Host: mysql69.1gb.ru    Database: gb_mirr
-- ------------------------------------------------------
-- Server version	5.1.62-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES cp1251 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `b_adv_banner`
--

DROP TABLE IF EXISTS `b_adv_banner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CONTRACT_ID` int(18) NOT NULL DEFAULT '1',
  `TYPE_SID` varchar(255) NOT NULL,
  `STATUS_SID` varchar(255) NOT NULL DEFAULT 'PUBLISHED',
  `STATUS_COMMENTS` text,
  `NAME` varchar(255) DEFAULT NULL,
  `GROUP_SID` varchar(255) DEFAULT NULL,
  `FIRST_SITE_ID` char(2) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `WEIGHT` int(18) NOT NULL DEFAULT '100',
  `MAX_SHOW_COUNT` int(18) DEFAULT NULL,
  `SHOW_COUNT` int(18) NOT NULL DEFAULT '0',
  `FIX_CLICK` char(1) NOT NULL DEFAULT 'Y',
  `FIX_SHOW` char(1) NOT NULL DEFAULT 'Y',
  `MAX_CLICK_COUNT` int(18) DEFAULT NULL,
  `CLICK_COUNT` int(18) NOT NULL DEFAULT '0',
  `MAX_VISITOR_COUNT` int(18) DEFAULT NULL,
  `VISITOR_COUNT` int(18) NOT NULL DEFAULT '0',
  `SHOWS_FOR_VISITOR` int(18) DEFAULT NULL,
  `DATE_LAST_SHOW` datetime DEFAULT NULL,
  `DATE_LAST_CLICK` datetime DEFAULT NULL,
  `DATE_SHOW_FROM` datetime DEFAULT NULL,
  `DATE_SHOW_TO` datetime DEFAULT NULL,
  `IMAGE_ID` int(18) DEFAULT NULL,
  `IMAGE_ALT` varchar(255) DEFAULT NULL,
  `URL` text,
  `URL_TARGET` varchar(255) DEFAULT NULL,
  `CODE` text,
  `CODE_TYPE` varchar(5) NOT NULL DEFAULT 'html',
  `STAT_EVENT_1` varchar(255) DEFAULT NULL,
  `STAT_EVENT_2` varchar(255) DEFAULT NULL,
  `STAT_EVENT_3` varchar(255) DEFAULT NULL,
  `FOR_NEW_GUEST` char(1) DEFAULT NULL,
  `KEYWORDS` text,
  `COMMENTS` text,
  `DATE_CREATE` datetime DEFAULT NULL,
  `CREATED_BY` int(18) DEFAULT NULL,
  `DATE_MODIFY` datetime DEFAULT NULL,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  `SHOW_USER_GROUP` char(1) NOT NULL DEFAULT 'N',
  `NO_URL_IN_FLASH` char(1) NOT NULL DEFAULT 'N',
  `FLYUNIFORM` char(1) NOT NULL DEFAULT 'N',
  `DATE_SHOW_FIRST` datetime DEFAULT NULL,
  `AD_TYPE` varchar(20) DEFAULT NULL,
  `FLASH_TRANSPARENT` varchar(20) DEFAULT NULL,
  `FLASH_IMAGE` int(18) DEFAULT NULL,
  `FLASH_JS` char(1) NOT NULL DEFAULT 'N',
  `FLASH_VER` varchar(20) DEFAULT NULL,
  `STAT_TYPE` varchar(20) DEFAULT NULL,
  `STAT_COUNT` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_ACTIVE_TYPE_SID` (`ACTIVE`,`TYPE_SID`),
  KEY `IX_CONTRACT_TYPE` (`CONTRACT_ID`,`TYPE_SID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_banner_2_country`
--

DROP TABLE IF EXISTS `b_adv_banner_2_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner_2_country` (
  `BANNER_ID` int(18) NOT NULL DEFAULT '0',
  `COUNTRY_ID` char(2) NOT NULL,
  `REGION` varchar(200) DEFAULT NULL,
  `CITY_ID` int(18) DEFAULT NULL,
  KEY `ix_b_adv_banner_2_country_1` (`COUNTRY_ID`,`REGION`(50),`BANNER_ID`),
  KEY `ix_b_adv_banner_2_country_2` (`CITY_ID`,`BANNER_ID`),
  KEY `ix_b_adv_banner_2_country_3` (`BANNER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_banner_2_day`
--

DROP TABLE IF EXISTS `b_adv_banner_2_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner_2_day` (
  `DATE_STAT` date NOT NULL DEFAULT '0000-00-00',
  `BANNER_ID` int(18) NOT NULL DEFAULT '0',
  `SHOW_COUNT` int(18) NOT NULL DEFAULT '0',
  `CLICK_COUNT` int(18) NOT NULL DEFAULT '0',
  `VISITOR_COUNT` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`BANNER_ID`,`DATE_STAT`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_banner_2_group`
--

DROP TABLE IF EXISTS `b_adv_banner_2_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner_2_group` (
  `BANNER_ID` int(18) NOT NULL DEFAULT '0',
  `GROUP_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`BANNER_ID`,`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_banner_2_page`
--

DROP TABLE IF EXISTS `b_adv_banner_2_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner_2_page` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `BANNER_ID` int(18) NOT NULL DEFAULT '0',
  `PAGE` varchar(255) NOT NULL,
  `SHOW_ON_PAGE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `IX_BANNER_ID` (`BANNER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_banner_2_site`
--

DROP TABLE IF EXISTS `b_adv_banner_2_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner_2_site` (
  `BANNER_ID` int(18) NOT NULL DEFAULT '0',
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`BANNER_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_banner_2_stat_adv`
--

DROP TABLE IF EXISTS `b_adv_banner_2_stat_adv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner_2_stat_adv` (
  `BANNER_ID` int(18) NOT NULL DEFAULT '0',
  `STAT_ADV_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`BANNER_ID`,`STAT_ADV_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_banner_2_weekday`
--

DROP TABLE IF EXISTS `b_adv_banner_2_weekday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_banner_2_weekday` (
  `BANNER_ID` int(18) NOT NULL DEFAULT '0',
  `C_WEEKDAY` varchar(10) NOT NULL,
  `C_HOUR` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`BANNER_ID`,`C_WEEKDAY`,`C_HOUR`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_contract`
--

DROP TABLE IF EXISTS `b_adv_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_contract` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `KEYWORDS` text,
  `ADMIN_COMMENTS` text,
  `WEIGHT` int(18) NOT NULL DEFAULT '100',
  `SORT` int(18) DEFAULT NULL,
  `MAX_SHOW_COUNT` int(18) DEFAULT NULL,
  `SHOW_COUNT` int(18) NOT NULL DEFAULT '0',
  `MAX_CLICK_COUNT` int(18) DEFAULT NULL,
  `CLICK_COUNT` int(18) NOT NULL DEFAULT '0',
  `MAX_VISITOR_COUNT` int(18) DEFAULT NULL,
  `VISITOR_COUNT` int(18) NOT NULL DEFAULT '0',
  `DATE_SHOW_FROM` datetime DEFAULT NULL,
  `DATE_SHOW_TO` datetime DEFAULT NULL,
  `DEFAULT_STATUS_SID` varchar(255) NOT NULL DEFAULT 'PUBLISHED',
  `EMAIL_COUNT` int(18) NOT NULL DEFAULT '0',
  `DATE_CREATE` datetime DEFAULT NULL,
  `CREATED_BY` int(18) DEFAULT NULL,
  `DATE_MODIFY` datetime DEFAULT NULL,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_contract_2_page`
--

DROP TABLE IF EXISTS `b_adv_contract_2_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_contract_2_page` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CONTRACT_ID` int(18) NOT NULL DEFAULT '0',
  `PAGE` varchar(255) NOT NULL,
  `SHOW_ON_PAGE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `IX_CONTRACT_ID` (`CONTRACT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_contract_2_site`
--

DROP TABLE IF EXISTS `b_adv_contract_2_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_contract_2_site` (
  `CONTRACT_ID` int(18) NOT NULL DEFAULT '0',
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`CONTRACT_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_contract_2_type`
--

DROP TABLE IF EXISTS `b_adv_contract_2_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_contract_2_type` (
  `CONTRACT_ID` int(18) NOT NULL DEFAULT '0',
  `TYPE_SID` varchar(255) NOT NULL,
  PRIMARY KEY (`CONTRACT_ID`,`TYPE_SID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_contract_2_user`
--

DROP TABLE IF EXISTS `b_adv_contract_2_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_contract_2_user` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CONTRACT_ID` int(18) NOT NULL DEFAULT '0',
  `USER_ID` int(18) NOT NULL DEFAULT '1',
  `PERMISSION` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_CONTRACT_ID` (`CONTRACT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_contract_2_weekday`
--

DROP TABLE IF EXISTS `b_adv_contract_2_weekday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_contract_2_weekday` (
  `CONTRACT_ID` int(18) NOT NULL DEFAULT '0',
  `C_WEEKDAY` varchar(10) NOT NULL,
  `C_HOUR` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CONTRACT_ID`,`C_WEEKDAY`,`C_HOUR`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_adv_type`
--

DROP TABLE IF EXISTS `b_adv_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_adv_type` (
  `SID` varchar(255) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(18) NOT NULL DEFAULT '100',
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `DATE_CREATE` datetime DEFAULT NULL,
  `CREATED_BY` int(18) DEFAULT NULL,
  `DATE_MODIFY` datetime DEFAULT NULL,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  PRIMARY KEY (`SID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_agent`
--

DROP TABLE IF EXISTS `b_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_agent` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `MODULE_ID` varchar(50) DEFAULT NULL,
  `SORT` int(18) NOT NULL DEFAULT '100',
  `NAME` text,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `LAST_EXEC` datetime DEFAULT NULL,
  `NEXT_EXEC` datetime NOT NULL,
  `DATE_CHECK` datetime DEFAULT NULL,
  `AGENT_INTERVAL` int(18) DEFAULT '86400',
  `IS_PERIOD` char(1) DEFAULT 'Y',
  `USER_ID` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_act_next_exec` (`ACTIVE`,`NEXT_EXEC`),
  KEY `ix_agent_user_id` (`USER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog`
--

DROP TABLE IF EXISTS `b_blog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `OWNER_ID` int(11) DEFAULT NULL,
  `SOCNET_GROUP_ID` int(11) DEFAULT NULL,
  `URL` varchar(255) NOT NULL,
  `REAL_URL` varchar(255) DEFAULT NULL,
  `GROUP_ID` int(11) NOT NULL,
  `ENABLE_COMMENTS` char(1) NOT NULL DEFAULT 'Y',
  `ENABLE_IMG_VERIF` char(1) NOT NULL DEFAULT 'N',
  `ENABLE_RSS` char(1) NOT NULL DEFAULT 'Y',
  `LAST_POST_ID` int(11) DEFAULT NULL,
  `LAST_POST_DATE` datetime DEFAULT NULL,
  `AUTO_GROUPS` varchar(255) DEFAULT NULL,
  `EMAIL_NOTIFY` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_HTML` char(1) NOT NULL DEFAULT 'N',
  `SEARCH_INDEX` char(1) NOT NULL DEFAULT 'Y',
  `USE_SOCNET` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_BLOG_4` (`URL`),
  KEY `IX_BLOG_BLOG_1` (`GROUP_ID`,`ACTIVE`),
  KEY `IX_BLOG_BLOG_2` (`OWNER_ID`),
  KEY `IX_BLOG_BLOG_5` (`LAST_POST_DATE`),
  KEY `IX_BLOG_BLOG_6` (`SOCNET_GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_category`
--

DROP TABLE IF EXISTS `b_blog_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_category` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BLOG_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_CAT_1` (`BLOG_ID`,`NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_comment`
--

DROP TABLE IF EXISTS `b_blog_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_comment` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BLOG_ID` int(11) NOT NULL,
  `POST_ID` int(11) NOT NULL,
  `PARENT_ID` int(11) DEFAULT NULL,
  `AUTHOR_ID` int(11) DEFAULT NULL,
  `ICON_ID` int(11) DEFAULT NULL,
  `AUTHOR_NAME` varchar(255) DEFAULT NULL,
  `AUTHOR_EMAIL` varchar(255) DEFAULT NULL,
  `AUTHOR_IP` varchar(20) DEFAULT NULL,
  `AUTHOR_IP1` varchar(20) DEFAULT NULL,
  `DATE_CREATE` datetime NOT NULL,
  `TITLE` varchar(255) DEFAULT NULL,
  `POST_TEXT` text NOT NULL,
  `PUBLISH_STATUS` char(1) NOT NULL DEFAULT 'P',
  `PATH` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_BLOG_COMM_1` (`BLOG_ID`,`POST_ID`),
  KEY `IX_BLOG_COMM_2` (`AUTHOR_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_group`
--

DROP TABLE IF EXISTS `b_blog_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_BLOG_GROUP_1` (`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_image`
--

DROP TABLE IF EXISTS `b_blog_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_image` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILE_ID` int(11) NOT NULL DEFAULT '0',
  `BLOG_ID` int(11) NOT NULL DEFAULT '0',
  `POST_ID` int(11) NOT NULL DEFAULT '0',
  `USER_ID` int(11) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TITLE` varchar(255) DEFAULT NULL,
  `IMAGE_SIZE` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_post`
--

DROP TABLE IF EXISTS `b_blog_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_post` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL,
  `BLOG_ID` int(11) NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL,
  `PREVIEW_TEXT` text,
  `PREVIEW_TEXT_TYPE` char(4) NOT NULL DEFAULT 'text',
  `DETAIL_TEXT` text NOT NULL,
  `DETAIL_TEXT_TYPE` char(4) NOT NULL DEFAULT 'text',
  `DATE_CREATE` datetime NOT NULL,
  `DATE_PUBLISH` datetime NOT NULL,
  `KEYWORDS` varchar(255) DEFAULT NULL,
  `PUBLISH_STATUS` char(1) NOT NULL DEFAULT 'P',
  `CATEGORY_ID` char(100) DEFAULT NULL,
  `ATRIBUTE` varchar(255) DEFAULT NULL,
  `ENABLE_TRACKBACK` char(1) NOT NULL DEFAULT 'Y',
  `ENABLE_COMMENTS` char(1) NOT NULL DEFAULT 'Y',
  `ATTACH_IMG` int(11) DEFAULT NULL,
  `NUM_COMMENTS` int(11) NOT NULL DEFAULT '0',
  `NUM_TRACKBACKS` int(11) NOT NULL DEFAULT '0',
  `VIEWS` int(11) DEFAULT NULL,
  `FAVORITE_SORT` int(11) DEFAULT NULL,
  `PATH` varchar(255) DEFAULT NULL,
  `CODE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_BLOG_POST_1` (`BLOG_ID`,`PUBLISH_STATUS`,`DATE_PUBLISH`),
  KEY `IX_BLOG_POST_2` (`BLOG_ID`,`DATE_PUBLISH`,`PUBLISH_STATUS`),
  KEY `IX_BLOG_POST_3` (`BLOG_ID`,`CATEGORY_ID`),
  KEY `IX_BLOG_POST_4` (`PUBLISH_STATUS`,`DATE_PUBLISH`),
  KEY `IX_BLOG_POST_CODE` (`BLOG_ID`,`CODE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_post_category`
--

DROP TABLE IF EXISTS `b_blog_post_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_post_category` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BLOG_ID` int(11) NOT NULL,
  `POST_ID` int(11) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_POST_CATEGORY` (`POST_ID`,`CATEGORY_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_site_path`
--

DROP TABLE IF EXISTS `b_blog_site_path`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_site_path` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `PATH` varchar(255) NOT NULL,
  `TYPE` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_SITE_PATH_2` (`SITE_ID`,`TYPE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_smile`
--

DROP TABLE IF EXISTS `b_blog_smile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_smile` (
  `ID` smallint(3) NOT NULL AUTO_INCREMENT,
  `SMILE_TYPE` char(1) NOT NULL DEFAULT 'S',
  `TYPING` varchar(100) DEFAULT NULL,
  `IMAGE` varchar(128) NOT NULL,
  `DESCRIPTION` varchar(50) DEFAULT NULL,
  `CLICKABLE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(10) NOT NULL DEFAULT '150',
  `IMAGE_WIDTH` int(11) NOT NULL DEFAULT '0',
  `IMAGE_HEIGHT` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_smile_lang`
--

DROP TABLE IF EXISTS `b_blog_smile_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_smile_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SMILE_ID` int(11) NOT NULL DEFAULT '0',
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_SMILE_K` (`SMILE_ID`,`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_socnet`
--

DROP TABLE IF EXISTS `b_blog_socnet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_socnet` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BLOG_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_SOCNET` (`BLOG_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_trackback`
--

DROP TABLE IF EXISTS `b_blog_trackback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_trackback` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL,
  `URL` varchar(255) NOT NULL,
  `PREVIEW_TEXT` text NOT NULL,
  `BLOG_NAME` varchar(255) DEFAULT NULL,
  `POST_DATE` datetime NOT NULL,
  `BLOG_ID` int(11) NOT NULL,
  `POST_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_BLOG_TRBK_1` (`BLOG_ID`,`POST_ID`),
  KEY `IX_BLOG_TRBK_2` (`POST_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_user`
--

DROP TABLE IF EXISTS `b_blog_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `ALIAS` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `AVATAR` int(11) DEFAULT NULL,
  `INTERESTS` varchar(255) DEFAULT NULL,
  `LAST_VISIT` datetime DEFAULT NULL,
  `DATE_REG` datetime NOT NULL,
  `ALLOW_POST` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_USER_1` (`USER_ID`),
  KEY `IX_BLOG_USER_2` (`ALIAS`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_user2blog`
--

DROP TABLE IF EXISTS `b_blog_user2blog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_user2blog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `BLOG_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_USER2GROUP_1` (`BLOG_ID`,`USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_user2user_group`
--

DROP TABLE IF EXISTS `b_blog_user2user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_user2user_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `BLOG_ID` int(11) NOT NULL,
  `USER_GROUP_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_USER2GROUP_1` (`USER_ID`,`BLOG_ID`,`USER_GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_user_group`
--

DROP TABLE IF EXISTS `b_blog_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_user_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BLOG_ID` int(11) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_BLOG_USER_GROUP_1` (`BLOG_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_blog_user_group_perms`
--

DROP TABLE IF EXISTS `b_blog_user_group_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_blog_user_group_perms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BLOG_ID` int(11) NOT NULL,
  `USER_GROUP_ID` int(11) NOT NULL,
  `PERMS_TYPE` char(1) NOT NULL DEFAULT 'P',
  `POST_ID` int(11) DEFAULT NULL,
  `PERMS` char(1) NOT NULL DEFAULT 'D',
  `AUTOSET` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_BLOG_UG_PERMS_1` (`BLOG_ID`,`USER_GROUP_ID`,`PERMS_TYPE`,`POST_ID`),
  KEY `IX_BLOG_UG_PERMS_2` (`USER_GROUP_ID`,`PERMS_TYPE`,`POST_ID`),
  KEY `IX_BLOG_UG_PERMS_3` (`POST_ID`,`USER_GROUP_ID`,`PERMS_TYPE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_history`
--

DROP TABLE IF EXISTS `b_bp_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MODULE_ID` varchar(32) DEFAULT NULL,
  `ENTITY` varchar(64) NOT NULL,
  `DOCUMENT_ID` varchar(128) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `DOCUMENT` blob,
  `MODIFIED` datetime NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_bp_history_doc` (`DOCUMENT_ID`,`ENTITY`,`MODULE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_task`
--

DROP TABLE IF EXISTS `b_bp_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_task` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `WORKFLOW_ID` varchar(32) NOT NULL,
  `ACTIVITY` varchar(128) NOT NULL,
  `ACTIVITY_NAME` varchar(128) NOT NULL,
  `MODIFIED` datetime NOT NULL,
  `OVERDUE_DATE` datetime DEFAULT NULL,
  `NAME` varchar(128) NOT NULL,
  `DESCRIPTION` text,
  `PARAMETERS` text,
  PRIMARY KEY (`ID`),
  KEY `ix_bp_tasks_sort` (`OVERDUE_DATE`,`MODIFIED`),
  KEY `ix_bp_tasks_wf` (`WORKFLOW_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_task_user`
--

DROP TABLE IF EXISTS `b_bp_task_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_task_user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `TASK_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_bp_task_user` (`USER_ID`,`TASK_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_tracking`
--

DROP TABLE IF EXISTS `b_bp_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_tracking` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `WORKFLOW_ID` varchar(32) NOT NULL,
  `TYPE` int(11) NOT NULL,
  `MODIFIED` datetime NOT NULL,
  `ACTION_NAME` varchar(128) NOT NULL,
  `ACTION_TITLE` varchar(255) DEFAULT NULL,
  `EXECUTION_STATUS` int(11) NOT NULL DEFAULT '0',
  `EXECUTION_RESULT` int(11) NOT NULL DEFAULT '0',
  `ACTION_NOTE` text,
  `MODIFIED_BY` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_bp_tracking_wf` (`WORKFLOW_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_workflow_instance`
--

DROP TABLE IF EXISTS `b_bp_workflow_instance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_workflow_instance` (
  `ID` varchar(32) NOT NULL,
  `WORKFLOW` blob,
  `STATUS` int(11) DEFAULT NULL,
  `MODIFIED` datetime NOT NULL,
  `OWNER_ID` varchar(32) DEFAULT NULL,
  `OWNED_UNTIL` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_workflow_permissions`
--

DROP TABLE IF EXISTS `b_bp_workflow_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_workflow_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `WORKFLOW_ID` varchar(32) NOT NULL,
  `OBJECT_ID` varchar(64) NOT NULL,
  `PERMISSION` varchar(64) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_bp_wf_permissions_wt` (`WORKFLOW_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_workflow_state`
--

DROP TABLE IF EXISTS `b_bp_workflow_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_workflow_state` (
  `ID` varchar(32) NOT NULL,
  `MODULE_ID` varchar(32) DEFAULT NULL,
  `ENTITY` varchar(64) NOT NULL,
  `DOCUMENT_ID` varchar(128) NOT NULL,
  `DOCUMENT_ID_INT` int(11) NOT NULL,
  `WORKFLOW_TEMPLATE_ID` int(11) NOT NULL,
  `STATE` varchar(128) DEFAULT NULL,
  `STATE_TITLE` varchar(128) DEFAULT NULL,
  `STATE_PARAMETERS` text,
  `MODIFIED` datetime NOT NULL,
  `STARTED` datetime DEFAULT NULL,
  `STARTED_BY` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_bp_ws_document_id` (`DOCUMENT_ID`,`ENTITY`,`MODULE_ID`),
  KEY `ix_bp_ws_document_id1` (`DOCUMENT_ID_INT`,`ENTITY`,`MODULE_ID`,`STATE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bp_workflow_template`
--

DROP TABLE IF EXISTS `b_bp_workflow_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_bp_workflow_template` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MODULE_ID` varchar(32) DEFAULT NULL,
  `ENTITY` varchar(64) NOT NULL,
  `DOCUMENT_TYPE` varchar(128) NOT NULL,
  `AUTO_EXECUTE` int(11) NOT NULL DEFAULT '0',
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `TEMPLATE` blob,
  `PARAMETERS` blob,
  `VARIABLES` blob,
  `MODIFIED` datetime NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `SYSTEM_CODE` varchar(50) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `ix_bp_wf_template_mo` (`MODULE_ID`,`ENTITY`,`DOCUMENT_TYPE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_cache_tag`
--

DROP TABLE IF EXISTS `b_cache_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_cache_tag` (
  `SITE_ID` char(2) DEFAULT NULL,
  `CACHE_SALT` char(4) DEFAULT NULL,
  `RELATIVE_PATH` varchar(255) DEFAULT NULL,
  `TAG` varchar(100) DEFAULT NULL,
  KEY `ix_b_cache_tag_0` (`SITE_ID`,`CACHE_SALT`,`RELATIVE_PATH`(50)),
  KEY `ix_b_cache_tag_1` (`TAG`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_captcha`
--

DROP TABLE IF EXISTS `b_captcha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_captcha` (
  `ID` varchar(32) NOT NULL,
  `CODE` varchar(20) NOT NULL,
  `IP` varchar(15) NOT NULL,
  `DATE_CREATE` datetime NOT NULL,
  UNIQUE KEY `UX_B_CAPTCHA` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_currency`
--

DROP TABLE IF EXISTS `b_catalog_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_currency` (
  `CURRENCY` char(3) NOT NULL,
  `AMOUNT_CNT` int(11) NOT NULL DEFAULT '1',
  `AMOUNT` decimal(18,4) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `DATE_UPDATE` datetime NOT NULL,
  PRIMARY KEY (`CURRENCY`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_currency_lang`
--

DROP TABLE IF EXISTS `b_catalog_currency_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_currency_lang` (
  `CURRENCY` char(3) NOT NULL,
  `LID` char(2) NOT NULL,
  `FORMAT_STRING` varchar(50) NOT NULL,
  `FULL_NAME` varchar(50) DEFAULT NULL,
  `DEC_POINT` varchar(5) DEFAULT '.',
  `THOUSANDS_SEP` varchar(5) DEFAULT ' ',
  `DECIMALS` tinyint(4) NOT NULL DEFAULT '2',
  `THOUSANDS_VARIANT` char(1) DEFAULT NULL,
  PRIMARY KEY (`CURRENCY`,`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_currency_rate`
--

DROP TABLE IF EXISTS `b_catalog_currency_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_currency_rate` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CURRENCY` char(3) NOT NULL,
  `DATE_RATE` date NOT NULL,
  `RATE_CNT` int(11) NOT NULL DEFAULT '1',
  `RATE` decimal(18,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_CURRENCY_RATE` (`CURRENCY`,`DATE_RATE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_discount`
--

DROP TABLE IF EXISTS `b_catalog_discount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_discount` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `ACTIVE_FROM` datetime DEFAULT NULL,
  `ACTIVE_TO` datetime DEFAULT NULL,
  `RENEWAL` char(1) NOT NULL DEFAULT 'N',
  `NAME` varchar(255) DEFAULT NULL,
  `MAX_USES` int(11) NOT NULL DEFAULT '0',
  `COUNT_USES` int(11) NOT NULL DEFAULT '0',
  `COUPON` varchar(20) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `MAX_DISCOUNT` decimal(18,4) DEFAULT NULL,
  `VALUE_TYPE` char(1) NOT NULL DEFAULT 'P',
  `VALUE` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `CURRENCY` char(3) NOT NULL,
  `MIN_ORDER_SUM` decimal(18,4) DEFAULT '0.0000',
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `NOTES` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_C_D_COUPON` (`COUPON`),
  KEY `IX_C_D_ACT` (`ACTIVE`,`ACTIVE_FROM`,`ACTIVE_TO`),
  KEY `IX_C_D_ACT_B` (`SITE_ID`,`RENEWAL`,`ACTIVE`,`ACTIVE_FROM`,`ACTIVE_TO`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_discount2cat`
--

DROP TABLE IF EXISTS `b_catalog_discount2cat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_discount2cat` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DISCOUNT_ID` int(11) NOT NULL,
  `CATALOG_GROUP_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_C_D2C_CATDIS` (`CATALOG_GROUP_ID`,`DISCOUNT_ID`),
  UNIQUE KEY `IX_C_D2C_CATDIS_B` (`DISCOUNT_ID`,`CATALOG_GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_discount2group`
--

DROP TABLE IF EXISTS `b_catalog_discount2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_discount2group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DISCOUNT_ID` int(11) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_C_D2G_GRDIS` (`GROUP_ID`,`DISCOUNT_ID`),
  UNIQUE KEY `IX_C_D2G_GRDIS_B` (`DISCOUNT_ID`,`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_discount2product`
--

DROP TABLE IF EXISTS `b_catalog_discount2product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_discount2product` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DISCOUNT_ID` int(11) NOT NULL,
  `PRODUCT_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_C_D2P_PRODIS` (`PRODUCT_ID`,`DISCOUNT_ID`),
  UNIQUE KEY `IX_C_D2P_PRODIS_B` (`DISCOUNT_ID`,`PRODUCT_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_discount2section`
--

DROP TABLE IF EXISTS `b_catalog_discount2section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_discount2section` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DISCOUNT_ID` int(11) NOT NULL,
  `SECTION_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_C_D2S_SECDIS` (`SECTION_ID`,`DISCOUNT_ID`),
  UNIQUE KEY `IX_C_D2S_SECDIS_B` (`DISCOUNT_ID`,`SECTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_discount_coupon`
--

DROP TABLE IF EXISTS `b_catalog_discount_coupon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_discount_coupon` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DISCOUNT_ID` int(11) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `COUPON` varchar(32) NOT NULL,
  `DATE_APPLY` datetime DEFAULT NULL,
  `ONE_TIME` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_cat_dc_index1` (`DISCOUNT_ID`,`COUPON`),
  KEY `ix_cat_dc_index2` (`COUPON`,`ACTIVE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_export`
--

DROP TABLE IF EXISTS `b_catalog_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_export` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILE_NAME` varchar(100) NOT NULL,
  `NAME` varchar(250) NOT NULL,
  `DEFAULT_PROFILE` char(1) NOT NULL DEFAULT 'N',
  `IN_MENU` char(1) NOT NULL DEFAULT 'N',
  `IN_AGENT` char(1) NOT NULL DEFAULT 'N',
  `IN_CRON` char(1) NOT NULL DEFAULT 'N',
  `SETUP_VARS` text,
  `LAST_USE` datetime DEFAULT NULL,
  `IS_EXPORT` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `BCAT_EX_FILE_NAME` (`FILE_NAME`),
  KEY `IX_CAT_IS_EXPORT` (`IS_EXPORT`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_extra`
--

DROP TABLE IF EXISTS `b_catalog_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_extra` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) NOT NULL,
  `PERCENTAGE` decimal(18,2) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_group`
--

DROP TABLE IF EXISTS `b_catalog_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) NOT NULL,
  `BASE` char(1) NOT NULL DEFAULT 'N',
  `SORT` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_group2group`
--

DROP TABLE IF EXISTS `b_catalog_group2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_group2group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CATALOG_GROUP_ID` int(11) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  `BUY` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_CATG2G_UNI` (`CATALOG_GROUP_ID`,`GROUP_ID`,`BUY`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_group_lang`
--

DROP TABLE IF EXISTS `b_catalog_group_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_group_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CATALOG_GROUP_ID` int(11) NOT NULL,
  `LID` char(3) NOT NULL,
  `NAME` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_CATALOG_GROUP_ID` (`CATALOG_GROUP_ID`,`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_iblock`
--

DROP TABLE IF EXISTS `b_catalog_iblock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_iblock` (
  `IBLOCK_ID` int(11) NOT NULL,
  `YANDEX_EXPORT` char(1) NOT NULL DEFAULT 'N',
  `SUBSCRIPTION` char(1) NOT NULL DEFAULT 'N',
  `VAT_ID` int(11) DEFAULT '0',
  `OFFERS_IBLOCK_ID` int(11) NOT NULL DEFAULT '0',
  `OFFERS` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`IBLOCK_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_load`
--

DROP TABLE IF EXISTS `b_catalog_load`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_load` (
  `NAME` varchar(250) NOT NULL,
  `VALUE` text NOT NULL,
  `TYPE` char(1) NOT NULL DEFAULT 'I',
  `LAST_USED` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`NAME`,`TYPE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_price`
--

DROP TABLE IF EXISTS `b_catalog_price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_price` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PRODUCT_ID` int(11) NOT NULL,
  `EXTRA_ID` int(11) DEFAULT NULL,
  `CATALOG_GROUP_ID` int(11) NOT NULL,
  `PRICE` decimal(18,2) NOT NULL,
  `CURRENCY` char(3) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `QUANTITY_FROM` int(11) DEFAULT NULL,
  `QUANTITY_TO` int(11) DEFAULT NULL,
  `TMP_ID` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_CAT_PRICE_PID` (`PRODUCT_ID`,`CATALOG_GROUP_ID`),
  KEY `IXS_CAT_PRICE_GID` (`CATALOG_GROUP_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2229 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_product`
--

DROP TABLE IF EXISTS `b_catalog_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_product` (
  `ID` int(11) NOT NULL,
  `QUANTITY` double NOT NULL,
  `QUANTITY_TRACE` char(1) NOT NULL DEFAULT 'N',
  `WEIGHT` double NOT NULL DEFAULT '0',
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `PRICE_TYPE` char(1) NOT NULL DEFAULT 'S',
  `RECUR_SCHEME_LENGTH` int(11) DEFAULT NULL,
  `RECUR_SCHEME_TYPE` char(1) NOT NULL DEFAULT 'M',
  `TRIAL_PRICE_ID` int(11) DEFAULT NULL,
  `WITHOUT_ORDER` char(1) NOT NULL DEFAULT 'N',
  `SELECT_BEST_PRICE` char(1) NOT NULL DEFAULT 'Y',
  `VAT_ID` int(11) DEFAULT '0',
  `VAT_INCLUDED` char(1) DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_product2group`
--

DROP TABLE IF EXISTS `b_catalog_product2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_product2group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PRODUCT_ID` int(11) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  `ACCESS_LENGTH` int(11) NOT NULL,
  `ACCESS_LENGTH_TYPE` char(1) NOT NULL DEFAULT 'D',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_C_P2G_PROD_GROUP` (`PRODUCT_ID`,`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_catalog_vat`
--

DROP TABLE IF EXISTS `b_catalog_vat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_catalog_vat` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `C_SORT` int(18) NOT NULL DEFAULT '100',
  `NAME` varchar(50) NOT NULL DEFAULT '',
  `RATE` decimal(18,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`ID`),
  KEY `IX_CAT_VAT_ACTIVE` (`ACTIVE`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_event`
--

DROP TABLE IF EXISTS `b_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_event` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `EVENT_NAME` varchar(50) NOT NULL,
  `MESSAGE_ID` int(18) DEFAULT NULL,
  `LID` varchar(255) NOT NULL,
  `C_FIELDS` longtext,
  `DATE_INSERT` datetime DEFAULT NULL,
  `DATE_EXEC` datetime DEFAULT NULL,
  `SUCCESS_EXEC` char(1) NOT NULL DEFAULT 'N',
  `DUPLICATE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `ix_success` (`SUCCESS_EXEC`)
) ENGINE=MyISAM AUTO_INCREMENT=568 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_event_log`
--

DROP TABLE IF EXISTS `b_event_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_event_log` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SEVERITY` varchar(50) NOT NULL,
  `AUDIT_TYPE_ID` varchar(50) NOT NULL,
  `MODULE_ID` varchar(50) NOT NULL,
  `ITEM_ID` varchar(255) NOT NULL,
  `REMOTE_ADDR` varchar(40) DEFAULT NULL,
  `USER_AGENT` text,
  `REQUEST_URI` text,
  `SITE_ID` char(2) DEFAULT NULL,
  `USER_ID` int(18) DEFAULT NULL,
  `GUEST_ID` int(18) DEFAULT NULL,
  `DESCRIPTION` mediumtext,
  PRIMARY KEY (`ID`),
  KEY `ix_b_event_log_time` (`TIMESTAMP_X`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_event_message`
--

DROP TABLE IF EXISTS `b_event_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_event_message` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `EVENT_NAME` varchar(50) NOT NULL,
  `LID` char(2) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `EMAIL_FROM` varchar(255) NOT NULL DEFAULT '#EMAIL_FROM#',
  `EMAIL_TO` varchar(255) NOT NULL DEFAULT '#EMAIL_TO#',
  `SUBJECT` varchar(255) DEFAULT NULL,
  `MESSAGE` text,
  `BODY_TYPE` varchar(4) NOT NULL DEFAULT 'text',
  `BCC` text,
  `REPLY_TO` varchar(255) DEFAULT NULL,
  `CC` varchar(255) DEFAULT NULL,
  `IN_REPLY_TO` varchar(255) DEFAULT NULL,
  `PRIORITY` varchar(50) DEFAULT NULL,
  `FIELD1_NAME` varchar(50) DEFAULT NULL,
  `FIELD1_VALUE` varchar(255) DEFAULT NULL,
  `FIELD2_NAME` varchar(50) DEFAULT NULL,
  `FIELD2_VALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_event_message_site`
--

DROP TABLE IF EXISTS `b_event_message_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_event_message_site` (
  `EVENT_MESSAGE_ID` int(11) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`EVENT_MESSAGE_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_event_type`
--

DROP TABLE IF EXISTS `b_event_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_event_type` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `LID` char(2) NOT NULL,
  `EVENT_NAME` varchar(50) NOT NULL,
  `NAME` varchar(100) DEFAULT NULL,
  `DESCRIPTION` text,
  `SORT` int(18) NOT NULL DEFAULT '150',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ux_1` (`EVENT_NAME`,`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_favorite`
--

DROP TABLE IF EXISTS `b_favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_favorite` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `DATE_CREATE` datetime DEFAULT NULL,
  `C_SORT` int(18) NOT NULL DEFAULT '100',
  `MODIFIED_BY` int(18) DEFAULT NULL,
  `CREATED_BY` int(18) DEFAULT NULL,
  `MODULE_ID` varchar(50) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `URL` text,
  `COMMENTS` text,
  `LANGUAGE_ID` char(2) DEFAULT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `COMMON` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_file`
--

DROP TABLE IF EXISTS `b_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_file` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MODULE_ID` varchar(50) DEFAULT NULL,
  `HEIGHT` int(18) DEFAULT NULL,
  `WIDTH` int(18) DEFAULT NULL,
  `FILE_SIZE` int(18) NOT NULL,
  `CONTENT_TYPE` varchar(255) DEFAULT 'IMAGE',
  `SUBDIR` varchar(255) DEFAULT NULL,
  `FILE_NAME` varchar(255) NOT NULL,
  `ORIGINAL_NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1475 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_file_search`
--

DROP TABLE IF EXISTS `b_file_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_file_search` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SESS_ID` varchar(255) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `F_PATH` varchar(255) DEFAULT NULL,
  `B_DIR` int(11) NOT NULL DEFAULT '0',
  `F_SIZE` int(11) NOT NULL DEFAULT '0',
  `F_TIME` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form`
--

DROP TABLE IF EXISTS `b_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `SID` varchar(50) NOT NULL,
  `BUTTON` varchar(255) DEFAULT NULL,
  `C_SORT` int(18) DEFAULT '100',
  `FIRST_SITE_ID` char(2) DEFAULT NULL,
  `IMAGE_ID` int(18) DEFAULT NULL,
  `USE_CAPTCHA` char(1) DEFAULT 'N',
  `DESCRIPTION` text,
  `DESCRIPTION_TYPE` varchar(4) NOT NULL DEFAULT 'html',
  `FORM_TEMPLATE` text,
  `USE_DEFAULT_TEMPLATE` char(1) DEFAULT 'Y',
  `SHOW_TEMPLATE` varchar(255) DEFAULT NULL,
  `MAIL_EVENT_TYPE` varchar(50) DEFAULT NULL,
  `SHOW_RESULT_TEMPLATE` varchar(255) DEFAULT NULL,
  `PRINT_RESULT_TEMPLATE` varchar(255) DEFAULT NULL,
  `EDIT_RESULT_TEMPLATE` varchar(255) DEFAULT NULL,
  `FILTER_RESULT_TEMPLATE` text,
  `TABLE_RESULT_TEMPLATE` text,
  `USE_RESTRICTIONS` char(1) DEFAULT 'N',
  `RESTRICT_USER` int(5) DEFAULT '0',
  `RESTRICT_TIME` int(10) DEFAULT '0',
  `RESTRICT_STATUS` varchar(255) DEFAULT NULL,
  `STAT_EVENT1` varchar(255) DEFAULT NULL,
  `STAT_EVENT2` varchar(255) DEFAULT NULL,
  `STAT_EVENT3` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_SID` (`SID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_2_group`
--

DROP TABLE IF EXISTS `b_form_2_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_2_group` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `GROUP_ID` int(18) NOT NULL DEFAULT '0',
  `PERMISSION` int(5) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `IX_FORM_ID` (`FORM_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_2_mail_template`
--

DROP TABLE IF EXISTS `b_form_2_mail_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_2_mail_template` (
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `MAIL_TEMPLATE_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`FORM_ID`,`MAIL_TEMPLATE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_2_site`
--

DROP TABLE IF EXISTS `b_form_2_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_2_site` (
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`FORM_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_answer`
--

DROP TABLE IF EXISTS `b_form_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_answer` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FIELD_ID` int(18) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `MESSAGE` text,
  `C_SORT` int(18) NOT NULL DEFAULT '100',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `VALUE` varchar(255) DEFAULT NULL,
  `FIELD_TYPE` varchar(255) NOT NULL DEFAULT 'text',
  `FIELD_WIDTH` int(18) DEFAULT NULL,
  `FIELD_HEIGHT` int(18) DEFAULT NULL,
  `FIELD_PARAM` text,
  PRIMARY KEY (`ID`),
  KEY `IX_FIELD_ID` (`FIELD_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_field`
--

DROP TABLE IF EXISTS `b_form_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_field` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TITLE` text,
  `TITLE_TYPE` varchar(4) NOT NULL DEFAULT 'text',
  `SID` varchar(50) DEFAULT NULL,
  `C_SORT` int(18) NOT NULL DEFAULT '100',
  `ADDITIONAL` char(1) NOT NULL DEFAULT 'N',
  `REQUIRED` char(1) NOT NULL DEFAULT 'N',
  `IN_FILTER` char(1) NOT NULL DEFAULT 'N',
  `IN_RESULTS_TABLE` char(1) NOT NULL DEFAULT 'N',
  `IN_EXCEL_TABLE` char(1) NOT NULL DEFAULT 'Y',
  `FIELD_TYPE` varchar(50) DEFAULT NULL,
  `IMAGE_ID` int(18) DEFAULT NULL,
  `COMMENTS` text,
  `FILTER_TITLE` text,
  `RESULTS_TABLE_TITLE` text,
  PRIMARY KEY (`ID`),
  KEY `IX_FORM_ID` (`FORM_ID`),
  KEY `IX_SID` (`SID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_field_filter`
--

DROP TABLE IF EXISTS `b_form_field_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_field_filter` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FIELD_ID` int(18) NOT NULL DEFAULT '0',
  `PARAMETER_NAME` varchar(50) NOT NULL,
  `FILTER_TYPE` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_FIELD_ID` (`FIELD_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_field_validator`
--

DROP TABLE IF EXISTS `b_form_field_validator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_field_validator` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `FIELD_ID` int(18) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `ACTIVE` char(1) DEFAULT 'y',
  `C_SORT` int(18) DEFAULT '100',
  `VALIDATOR_SID` varchar(255) NOT NULL DEFAULT '',
  `PARAMS` text,
  PRIMARY KEY (`ID`),
  KEY `IX_FORM_ID` (`FORM_ID`),
  KEY `IX_FIELD_ID` (`FIELD_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_menu`
--

DROP TABLE IF EXISTS `b_form_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_menu` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `LID` char(2) NOT NULL,
  `MENU` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_FORM_ID` (`FORM_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_result`
--

DROP TABLE IF EXISTS `b_form_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_result` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `DATE_CREATE` datetime DEFAULT NULL,
  `STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `USER_ID` int(18) DEFAULT NULL,
  `USER_AUTH` char(1) NOT NULL DEFAULT 'N',
  `STAT_GUEST_ID` int(18) DEFAULT NULL,
  `STAT_SESSION_ID` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_FORM_ID` (`FORM_ID`),
  KEY `IX_STATUS_ID` (`STATUS_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_result_answer`
--

DROP TABLE IF EXISTS `b_form_result_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_result_answer` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `RESULT_ID` int(18) NOT NULL DEFAULT '0',
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `FIELD_ID` int(18) NOT NULL DEFAULT '0',
  `ANSWER_ID` int(18) DEFAULT NULL,
  `ANSWER_TEXT` text,
  `ANSWER_TEXT_SEARCH` longtext,
  `ANSWER_VALUE` varchar(255) DEFAULT NULL,
  `ANSWER_VALUE_SEARCH` longtext,
  `USER_TEXT` longtext,
  `USER_TEXT_SEARCH` longtext,
  `USER_DATE` datetime DEFAULT NULL,
  `USER_FILE_ID` int(18) DEFAULT NULL,
  `USER_FILE_NAME` varchar(255) DEFAULT NULL,
  `USER_FILE_IS_IMAGE` char(1) DEFAULT NULL,
  `USER_FILE_HASH` varchar(255) DEFAULT NULL,
  `USER_FILE_SUFFIX` varchar(255) DEFAULT NULL,
  `USER_FILE_SIZE` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_RESULT_ID` (`RESULT_ID`),
  KEY `IX_FIELD_ID` (`FIELD_ID`),
  KEY `IX_ANSWER_ID` (`ANSWER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_status`
--

DROP TABLE IF EXISTS `b_form_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_status` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FORM_ID` int(18) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `C_SORT` int(18) NOT NULL DEFAULT '100',
  `TITLE` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `DEFAULT_VALUE` char(1) NOT NULL DEFAULT 'N',
  `CSS` varchar(255) DEFAULT 'statusgreen',
  `HANDLER_OUT` varchar(255) DEFAULT NULL,
  `HANDLER_IN` varchar(255) DEFAULT NULL,
  `MAIL_EVENT_TYPE` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_FORM_ID` (`FORM_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_status_2_group`
--

DROP TABLE IF EXISTS `b_form_status_2_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_status_2_group` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `GROUP_ID` int(18) NOT NULL DEFAULT '0',
  `PERMISSION` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_form_status_2_mail_template`
--

DROP TABLE IF EXISTS `b_form_status_2_mail_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_form_status_2_mail_template` (
  `STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `MAIL_TEMPLATE_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`STATUS_ID`,`MAIL_TEMPLATE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum`
--

DROP TABLE IF EXISTS `b_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `FORUM_GROUP_ID` int(11) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `SORT` int(10) NOT NULL DEFAULT '150',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_HTML` char(1) NOT NULL DEFAULT 'N',
  `ALLOW_ANCHOR` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_BIU` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_IMG` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_VIDEO` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_LIST` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_QUOTE` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_CODE` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_FONT` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_SMILES` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_UPLOAD` char(1) NOT NULL DEFAULT 'N',
  `ALLOW_UPLOAD_EXT` varchar(255) DEFAULT NULL,
  `ALLOW_MOVE_TOPIC` char(1) NOT NULL DEFAULT 'Y',
  `ALLOW_TOPIC_TITLED` char(1) NOT NULL DEFAULT 'N',
  `ALLOW_NL2BR` char(1) NOT NULL DEFAULT 'N',
  `ALLOW_KEEP_AMP` char(1) NOT NULL DEFAULT 'N',
  `PATH2FORUM_MESSAGE` varchar(255) DEFAULT NULL,
  `ASK_GUEST_EMAIL` char(1) NOT NULL DEFAULT 'N',
  `USE_CAPTCHA` char(1) NOT NULL DEFAULT 'N',
  `INDEXATION` char(1) NOT NULL DEFAULT 'Y',
  `MODERATION` char(1) NOT NULL DEFAULT 'N',
  `ORDER_BY` char(1) NOT NULL DEFAULT 'P',
  `ORDER_DIRECTION` varchar(4) NOT NULL DEFAULT 'DESC',
  `LID` char(2) NOT NULL DEFAULT 'ru',
  `TOPICS` int(11) NOT NULL DEFAULT '0',
  `POSTS` int(11) NOT NULL DEFAULT '0',
  `LAST_POSTER_ID` int(11) DEFAULT NULL,
  `LAST_POSTER_NAME` varchar(255) DEFAULT NULL,
  `LAST_POST_DATE` datetime DEFAULT NULL,
  `LAST_MESSAGE_ID` bigint(20) DEFAULT NULL,
  `POSTS_UNAPPROVED` int(11) DEFAULT '0',
  `ABS_LAST_POSTER_ID` int(11) DEFAULT NULL,
  `ABS_LAST_POSTER_NAME` varchar(255) DEFAULT NULL,
  `ABS_LAST_POST_DATE` datetime DEFAULT NULL,
  `ABS_LAST_MESSAGE_ID` bigint(20) DEFAULT NULL,
  `EVENT1` varchar(255) DEFAULT 'forum',
  `EVENT2` varchar(255) DEFAULT 'message',
  `EVENT3` varchar(255) DEFAULT NULL,
  `HTML` varchar(255) DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_FORUM_SORT` (`SORT`),
  KEY `IX_FORUM_ACTIVE` (`ACTIVE`),
  KEY `IX_FORUM_GROUP_ID` (`FORUM_GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum2site`
--

DROP TABLE IF EXISTS `b_forum2site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum2site` (
  `FORUM_ID` int(11) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  `PATH2FORUM_MESSAGE` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`FORUM_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_dictionary`
--

DROP TABLE IF EXISTS `b_forum_dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_dictionary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(50) DEFAULT NULL,
  `TYPE` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_email`
--

DROP TABLE IF EXISTS `b_forum_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_email` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `EMAIL_FORUM_ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `FORUM_ID` int(11) NOT NULL,
  `SOCNET_GROUP_ID` int(11) DEFAULT NULL,
  `MAIL_FILTER_ID` int(11) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `USE_EMAIL` char(1) DEFAULT NULL,
  `EMAIL_GROUP` varchar(255) DEFAULT NULL,
  `SUBJECT_SUF` varchar(50) DEFAULT NULL,
  `USE_SUBJECT` char(1) DEFAULT NULL,
  `URL_TEMPLATES_MESSAGE` varchar(255) DEFAULT NULL,
  `NOT_MEMBER_POST` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_FORUM_EMAIL_FORUM_SOC` (`FORUM_ID`,`SOCNET_GROUP_ID`),
  KEY `IX_B_FORUM_EMAIL_FILTER_ID` (`MAIL_FILTER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_file`
--

DROP TABLE IF EXISTS `b_forum_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_file` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `FORUM_ID` int(18) DEFAULT NULL,
  `TOPIC_ID` int(20) DEFAULT NULL,
  `MESSAGE_ID` int(20) DEFAULT NULL,
  `FILE_ID` int(18) NOT NULL,
  `USER_ID` int(18) DEFAULT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `HITS` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_FORUM_FILE_FILE` (`FILE_ID`),
  KEY `IX_FORUM_FILE_FORUM` (`FORUM_ID`),
  KEY `IX_FORUM_FILE_TOPIC` (`TOPIC_ID`),
  KEY `IX_FORUM_FILE_MESSAGE` (`MESSAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_filter`
--

DROP TABLE IF EXISTS `b_forum_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_filter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DICTIONARY_ID` int(11) DEFAULT NULL,
  `WORDS` varchar(255) DEFAULT NULL,
  `PATTERN` text,
  `REPLACEMENT` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `USE_IT` varchar(50) DEFAULT NULL,
  `PATTERN_CREATE` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_FORUM_FILTER_2` (`USE_IT`),
  KEY `IX_B_FORUM_FILTER_3` (`PATTERN_CREATE`)
) ENGINE=MyISAM AUTO_INCREMENT=152 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_group`
--

DROP TABLE IF EXISTS `b_forum_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SORT` int(11) NOT NULL DEFAULT '150',
  `PARENT_ID` int(11) DEFAULT NULL,
  `LEFT_MARGIN` int(11) DEFAULT NULL,
  `RIGHT_MARGIN` int(11) DEFAULT NULL,
  `DEPTH_LEVEL` int(11) DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_group_lang`
--

DROP TABLE IF EXISTS `b_forum_group_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_group_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FORUM_GROUP_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_FORUM_GROUP` (`FORUM_GROUP_ID`,`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_letter`
--

DROP TABLE IF EXISTS `b_forum_letter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_letter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DICTIONARY_ID` int(11) DEFAULT '0',
  `LETTER` varchar(50) DEFAULT NULL,
  `REPLACEMENT` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_message`
--

DROP TABLE IF EXISTS `b_forum_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_message` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `FORUM_ID` int(10) NOT NULL,
  `TOPIC_ID` bigint(20) NOT NULL,
  `USE_SMILES` char(1) NOT NULL DEFAULT 'Y',
  `NEW_TOPIC` char(1) NOT NULL DEFAULT 'N',
  `APPROVED` char(1) NOT NULL DEFAULT 'Y',
  `SOURCE_ID` varchar(255) NOT NULL DEFAULT 'WEB',
  `POST_DATE` datetime NOT NULL,
  `POST_MESSAGE` text,
  `POST_MESSAGE_HTML` text,
  `POST_MESSAGE_FILTER` text,
  `POST_MESSAGE_CHECK` char(32) DEFAULT NULL,
  `ATTACH_IMG` int(11) DEFAULT NULL,
  `PARAM1` varchar(2) DEFAULT NULL,
  `PARAM2` int(11) DEFAULT NULL,
  `AUTHOR_ID` int(10) DEFAULT NULL,
  `AUTHOR_NAME` varchar(255) DEFAULT NULL,
  `AUTHOR_EMAIL` varchar(255) DEFAULT NULL,
  `AUTHOR_IP` varchar(255) DEFAULT NULL,
  `AUTHOR_REAL_IP` varchar(128) DEFAULT NULL,
  `GUEST_ID` int(10) DEFAULT NULL,
  `EDITOR_ID` int(10) DEFAULT NULL,
  `EDITOR_NAME` varchar(255) DEFAULT NULL,
  `EDITOR_EMAIL` varchar(255) DEFAULT NULL,
  `EDIT_REASON` text,
  `EDIT_DATE` datetime DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  `HTML` text,
  `MAIL_HEADER` text,
  PRIMARY KEY (`ID`),
  KEY `IX_FORUM_MESSAGE_FORUM` (`FORUM_ID`,`APPROVED`),
  KEY `IX_FORUM_MESSAGE_TOPIC` (`TOPIC_ID`,`APPROVED`),
  KEY `IX_FORUM_MESSAGE_AUTHOR` (`AUTHOR_ID`,`APPROVED`,`FORUM_ID`),
  KEY `IX_FORUM_MESSAGE_APPROVED` (`APPROVED`),
  KEY `IX_FORUM_MESSAGE_XML_ID` (`XML_ID`),
  KEY `IX_FORUM_MESSAGE_DATE_AUTHOR_ID` (`POST_DATE`,`AUTHOR_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_perms`
--

DROP TABLE IF EXISTS `b_forum_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_perms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FORUM_ID` int(11) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  `PERMISSION` char(1) NOT NULL DEFAULT 'M',
  PRIMARY KEY (`ID`),
  KEY `IX_FORUM_PERMS_FORUM` (`FORUM_ID`,`GROUP_ID`),
  KEY `IX_FORUM_PERMS_GROUP` (`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_pm_folder`
--

DROP TABLE IF EXISTS `b_forum_pm_folder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_pm_folder` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(50) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `SORT` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_FORUM_PM_FOLDER_USER_IST` (`USER_ID`,`ID`,`SORT`,`TITLE`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_points`
--

DROP TABLE IF EXISTS `b_forum_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_points` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MIN_POINTS` int(11) NOT NULL,
  `CODE` varchar(100) DEFAULT NULL,
  `VOTES` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_FORUM_P_MP` (`MIN_POINTS`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_points2post`
--

DROP TABLE IF EXISTS `b_forum_points2post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_points2post` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MIN_NUM_POSTS` int(11) NOT NULL,
  `POINTS_PER_POST` decimal(18,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_FORUM_P2P_MNP` (`MIN_NUM_POSTS`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_points_lang`
--

DROP TABLE IF EXISTS `b_forum_points_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_points_lang` (
  `POINTS_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`POINTS_ID`,`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_private_message`
--

DROP TABLE IF EXISTS `b_forum_private_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_private_message` (
  `ID` bigint(10) NOT NULL AUTO_INCREMENT,
  `AUTHOR_ID` int(11) DEFAULT '0',
  `RECIPIENT_ID` int(11) DEFAULT '0',
  `POST_DATE` datetime NOT NULL,
  `POST_SUBJ` varchar(50) NOT NULL,
  `POST_MESSAGE` text NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `FOLDER_ID` int(11) NOT NULL,
  `IS_READ` varchar(50) NOT NULL,
  `REQUEST_IS_READ` char(1) NOT NULL,
  `USE_SMILES` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_FORUM_PM_USER` (`USER_ID`),
  KEY `IX_B_FORUM_PM_AFR` (`AUTHOR_ID`,`FOLDER_ID`,`IS_READ`),
  KEY `IX_B_FORUM_PM_UFP` (`USER_ID`,`FOLDER_ID`,`POST_DATE`),
  KEY `IX_B_FORUM_PM_POST_DATE` (`POST_DATE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_rank`
--

DROP TABLE IF EXISTS `b_forum_rank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_rank` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` varchar(100) DEFAULT NULL,
  `MIN_NUM_POSTS` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_rank_lang`
--

DROP TABLE IF EXISTS `b_forum_rank_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_rank_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RANK_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_FORUM_RANK` (`RANK_ID`,`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_smile`
--

DROP TABLE IF EXISTS `b_forum_smile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_smile` (
  `ID` smallint(3) NOT NULL AUTO_INCREMENT,
  `TYPE` char(1) NOT NULL DEFAULT 'S',
  `TYPING` varchar(100) DEFAULT NULL,
  `IMAGE` varchar(255) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `CLICKABLE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(10) NOT NULL DEFAULT '150',
  `IMAGE_WIDTH` int(11) NOT NULL DEFAULT '0',
  `IMAGE_HEIGHT` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_smile_lang`
--

DROP TABLE IF EXISTS `b_forum_smile_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_smile_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SMILE_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_FORUM_SMILE_K` (`SMILE_ID`,`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_stat`
--

DROP TABLE IF EXISTS `b_forum_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_stat` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(10) DEFAULT NULL,
  `IP_ADDRESS` varchar(128) DEFAULT NULL,
  `PHPSESSID` varchar(255) DEFAULT NULL,
  `LAST_VISIT` datetime DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `FORUM_ID` smallint(5) NOT NULL DEFAULT '0',
  `TOPIC_ID` int(10) DEFAULT NULL,
  `SHOW_NAME` varchar(101) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_FORUM_STAT_SITE_ID` (`SITE_ID`,`LAST_VISIT`),
  KEY `IX_B_FORUM_STAT_TOPIC_ID` (`TOPIC_ID`,`LAST_VISIT`),
  KEY `IX_B_FORUM_STAT_FORUM_ID` (`FORUM_ID`,`LAST_VISIT`),
  KEY `IX_B_FORUM_STAT_PHPSESSID` (`PHPSESSID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_subscribe`
--

DROP TABLE IF EXISTS `b_forum_subscribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_subscribe` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(10) NOT NULL,
  `FORUM_ID` int(10) NOT NULL,
  `TOPIC_ID` int(10) DEFAULT NULL,
  `START_DATE` datetime NOT NULL,
  `LAST_SEND` int(10) DEFAULT NULL,
  `NEW_TOPIC_ONLY` char(50) NOT NULL DEFAULT 'N',
  `SITE_ID` char(2) NOT NULL DEFAULT 'ru',
  `SOCNET_GROUP_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_FORUM_SUBSCRIBE_USER` (`USER_ID`,`FORUM_ID`,`TOPIC_ID`,`SOCNET_GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_topic`
--

DROP TABLE IF EXISTS `b_forum_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_topic` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `FORUM_ID` int(10) NOT NULL,
  `TOPIC_ID` bigint(20) DEFAULT NULL,
  `TITLE` varchar(255) NOT NULL,
  `TAGS` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `ICON_ID` tinyint(2) DEFAULT NULL,
  `STATE` char(1) NOT NULL DEFAULT 'Y',
  `APPROVED` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(10) NOT NULL DEFAULT '150',
  `VIEWS` int(10) NOT NULL DEFAULT '0',
  `USER_START_ID` int(10) DEFAULT NULL,
  `USER_START_NAME` varchar(255) DEFAULT NULL,
  `START_DATE` datetime NOT NULL,
  `POSTS` int(10) NOT NULL DEFAULT '0',
  `LAST_POSTER_ID` int(10) DEFAULT NULL,
  `LAST_POSTER_NAME` varchar(255) NOT NULL,
  `LAST_POST_DATE` datetime NOT NULL,
  `LAST_MESSAGE_ID` bigint(20) DEFAULT NULL,
  `POSTS_UNAPPROVED` int(11) DEFAULT '0',
  `ABS_LAST_POSTER_ID` int(10) DEFAULT NULL,
  `ABS_LAST_POSTER_NAME` varchar(255) DEFAULT NULL,
  `ABS_LAST_POST_DATE` datetime DEFAULT NULL,
  `ABS_LAST_MESSAGE_ID` bigint(20) DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  `HTML` text,
  `SOCNET_GROUP_ID` int(10) DEFAULT NULL,
  `OWNER_ID` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_FORUM_TOPIC_FORUM` (`FORUM_ID`,`APPROVED`),
  KEY `IX_FORUM_TOPIC_APPROVED` (`APPROVED`),
  KEY `IX_FORUM_TOPIC_ABS_L_POST_DATE` (`ABS_LAST_POST_DATE`),
  KEY `IX_FORUM_TOPIC_LAST_POST_DATE` (`LAST_POST_DATE`),
  KEY `IX_FORUM_TOPIC_USER_START_ID` (`USER_START_ID`),
  KEY `IX_FORUM_TOPIC_DATE_USER_START_ID` (`START_DATE`,`USER_START_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_user`
--

DROP TABLE IF EXISTS `b_forum_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_user` (
  `ID` bigint(10) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(10) NOT NULL,
  `ALIAS` varchar(64) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `IP_ADDRESS` varchar(128) DEFAULT NULL,
  `AVATAR` int(10) DEFAULT NULL,
  `NUM_POSTS` int(10) DEFAULT '0',
  `INTERESTS` text,
  `LAST_POST` int(10) DEFAULT NULL,
  `ALLOW_POST` char(1) NOT NULL DEFAULT 'Y',
  `LAST_VISIT` datetime NOT NULL,
  `DATE_REG` date NOT NULL,
  `REAL_IP_ADDRESS` varchar(128) DEFAULT NULL,
  `SIGNATURE` varchar(255) DEFAULT NULL,
  `SHOW_NAME` char(1) NOT NULL DEFAULT 'Y',
  `RANK_ID` int(11) DEFAULT NULL,
  `POINTS` int(11) NOT NULL DEFAULT '0',
  `HIDE_FROM_ONLINE` char(1) NOT NULL DEFAULT 'N',
  `SUBSC_GROUP_MESSAGE` char(1) NOT NULL DEFAULT 'N',
  `SUBSC_GET_MY_MESSAGE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_FORUM_USER_USER6` (`USER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_user_forum`
--

DROP TABLE IF EXISTS `b_forum_user_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_user_forum` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) DEFAULT NULL,
  `FORUM_ID` int(11) DEFAULT NULL,
  `LAST_VISIT` datetime DEFAULT NULL,
  `MAIN_LAST_VISIT` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_FORUM_USER_FORUM_ID1` (`USER_ID`,`FORUM_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_user_points`
--

DROP TABLE IF EXISTS `b_forum_user_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_user_points` (
  `FROM_USER_ID` int(11) NOT NULL,
  `TO_USER_ID` int(11) NOT NULL,
  `POINTS` int(11) NOT NULL DEFAULT '0',
  `DATE_UPDATE` datetime DEFAULT NULL,
  PRIMARY KEY (`FROM_USER_ID`,`TO_USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_forum_user_topic`
--

DROP TABLE IF EXISTS `b_forum_user_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_forum_user_topic` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `TOPIC_ID` int(11) NOT NULL DEFAULT '0',
  `USER_ID` int(11) NOT NULL DEFAULT '0',
  `FORUM_ID` int(11) DEFAULT NULL,
  `LAST_VISIT` datetime DEFAULT NULL,
  PRIMARY KEY (`TOPIC_ID`,`USER_ID`),
  KEY `ID` (`ID`),
  KEY `IX_B_FORUM_USER_FORUM_ID2` (`USER_ID`,`FORUM_ID`,`TOPIC_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_group`
--

DROP TABLE IF EXISTS `b_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_group` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `C_SORT` int(18) NOT NULL DEFAULT '100',
  `ANONYMOUS` char(1) NOT NULL DEFAULT 'N',
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `SECURITY_POLICY` text,
  `STRING_ID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_group_collection_task`
--

DROP TABLE IF EXISTS `b_group_collection_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_group_collection_task` (
  `GROUP_ID` int(11) NOT NULL,
  `TASK_ID` int(11) NOT NULL,
  `COLLECTION_ID` int(11) NOT NULL,
  PRIMARY KEY (`GROUP_ID`,`TASK_ID`,`COLLECTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_group_subordinate`
--

DROP TABLE IF EXISTS `b_group_subordinate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_group_subordinate` (
  `ID` int(18) NOT NULL,
  `AR_SUBGROUP_ID` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_group_task`
--

DROP TABLE IF EXISTS `b_group_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_group_task` (
  `GROUP_ID` int(18) NOT NULL,
  `TASK_ID` int(18) NOT NULL,
  `EXTERNAL_ID` varchar(50) DEFAULT '',
  PRIMARY KEY (`GROUP_ID`,`TASK_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock`
--

DROP TABLE IF EXISTS `b_iblock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `IBLOCK_TYPE_ID` varchar(50) NOT NULL,
  `LID` char(2) NOT NULL,
  `CODE` varchar(50) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `LIST_PAGE_URL` varchar(255) DEFAULT NULL,
  `DETAIL_PAGE_URL` varchar(255) DEFAULT NULL,
  `SECTION_PAGE_URL` varchar(255) DEFAULT NULL,
  `PICTURE` int(18) DEFAULT NULL,
  `DESCRIPTION` text,
  `DESCRIPTION_TYPE` char(4) NOT NULL DEFAULT 'text',
  `RSS_TTL` int(11) NOT NULL DEFAULT '24',
  `RSS_ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `RSS_FILE_ACTIVE` char(1) NOT NULL DEFAULT 'N',
  `RSS_FILE_LIMIT` int(11) DEFAULT NULL,
  `RSS_FILE_DAYS` int(11) DEFAULT NULL,
  `RSS_YANDEX_ACTIVE` char(1) NOT NULL DEFAULT 'N',
  `XML_ID` varchar(255) DEFAULT NULL,
  `TMP_ID` varchar(40) DEFAULT NULL,
  `INDEX_ELEMENT` char(1) NOT NULL DEFAULT 'Y',
  `INDEX_SECTION` char(1) NOT NULL DEFAULT 'N',
  `WORKFLOW` char(1) NOT NULL DEFAULT 'Y',
  `BIZPROC` char(1) NOT NULL DEFAULT 'N',
  `SECTION_CHOOSER` char(1) DEFAULT NULL,
  `LIST_MODE` char(1) DEFAULT NULL,
  `VERSION` int(11) NOT NULL DEFAULT '1',
  `LAST_CONV_ELEMENT` int(11) NOT NULL DEFAULT '0',
  `SOCNET_GROUP_ID` int(18) DEFAULT NULL,
  `EDIT_FILE_BEFORE` varchar(255) DEFAULT NULL,
  `EDIT_FILE_AFTER` varchar(255) DEFAULT NULL,
  `SECTIONS_NAME` varchar(100) DEFAULT NULL,
  `SECTION_NAME` varchar(100) DEFAULT NULL,
  `ELEMENTS_NAME` varchar(100) DEFAULT NULL,
  `ELEMENT_NAME` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_iblock` (`IBLOCK_TYPE_ID`,`LID`,`ACTIVE`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_cache`
--

DROP TABLE IF EXISTS `b_iblock_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_cache` (
  `CACHE_KEY` varchar(35) NOT NULL,
  `CACHE` longtext NOT NULL,
  `CACHE_DATE` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`CACHE_KEY`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_element`
--

DROP TABLE IF EXISTS `b_iblock_element`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_element` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  `DATE_CREATE` datetime DEFAULT NULL,
  `CREATED_BY` int(18) DEFAULT NULL,
  `IBLOCK_ID` int(11) NOT NULL DEFAULT '0',
  `IBLOCK_SECTION_ID` int(11) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `ACTIVE_FROM` datetime DEFAULT NULL,
  `ACTIVE_TO` datetime DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `NAME` varchar(255) NOT NULL,
  `PREVIEW_PICTURE` int(18) DEFAULT NULL,
  `PREVIEW_TEXT` text,
  `PREVIEW_TEXT_TYPE` varchar(4) NOT NULL DEFAULT 'text',
  `DETAIL_PICTURE` int(18) DEFAULT NULL,
  `DETAIL_TEXT` longtext,
  `DETAIL_TEXT_TYPE` varchar(4) NOT NULL DEFAULT 'text',
  `SEARCHABLE_CONTENT` text,
  `WF_STATUS_ID` int(18) DEFAULT '1',
  `WF_PARENT_ELEMENT_ID` int(11) DEFAULT NULL,
  `WF_NEW` char(1) DEFAULT NULL,
  `WF_LOCKED_BY` int(18) DEFAULT NULL,
  `WF_DATE_LOCK` datetime DEFAULT NULL,
  `WF_COMMENTS` text,
  `IN_SECTIONS` char(1) NOT NULL DEFAULT 'N',
  `XML_ID` varchar(255) DEFAULT NULL,
  `CODE` varchar(255) DEFAULT NULL,
  `TAGS` varchar(255) DEFAULT NULL,
  `TMP_ID` varchar(40) DEFAULT NULL,
  `WF_LAST_HISTORY_ID` int(11) DEFAULT NULL,
  `SHOW_COUNTER` int(18) DEFAULT NULL,
  `SHOW_COUNTER_START` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_iblock_element_1` (`IBLOCK_ID`,`IBLOCK_SECTION_ID`),
  KEY `ix_iblock_element_4` (`IBLOCK_ID`,`XML_ID`,`WF_PARENT_ELEMENT_ID`),
  KEY `ix_iblock_element_3` (`WF_PARENT_ELEMENT_ID`),
  KEY `ix_iblock_element_code` (`IBLOCK_ID`,`CODE`)
) ENGINE=MyISAM AUTO_INCREMENT=4518 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_element_lock`
--

DROP TABLE IF EXISTS `b_iblock_element_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_element_lock` (
  `IBLOCK_ELEMENT_ID` int(11) NOT NULL,
  `DATE_LOCK` datetime DEFAULT NULL,
  `LOCKED_BY` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`IBLOCK_ELEMENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_element_prop_m6`
--

DROP TABLE IF EXISTS `b_iblock_element_prop_m6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_element_prop_m6` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IBLOCK_ELEMENT_ID` int(11) NOT NULL,
  `IBLOCK_PROPERTY_ID` int(11) NOT NULL,
  `VALUE` text NOT NULL,
  `VALUE_ENUM` int(11) DEFAULT NULL,
  `VALUE_NUM` decimal(18,4) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_iblock_elem_prop_m6_1` (`IBLOCK_ELEMENT_ID`,`IBLOCK_PROPERTY_ID`),
  KEY `ix_iblock_elem_prop_m6_2` (`IBLOCK_PROPERTY_ID`),
  KEY `ix_iblock_elem_prop_m6_3` (`VALUE_ENUM`,`IBLOCK_PROPERTY_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_element_prop_m8`
--

DROP TABLE IF EXISTS `b_iblock_element_prop_m8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_element_prop_m8` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IBLOCK_ELEMENT_ID` int(11) NOT NULL,
  `IBLOCK_PROPERTY_ID` int(11) NOT NULL,
  `VALUE` text NOT NULL,
  `VALUE_ENUM` int(11) DEFAULT NULL,
  `VALUE_NUM` decimal(18,4) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_iblock_elem_prop_m8_1` (`IBLOCK_ELEMENT_ID`,`IBLOCK_PROPERTY_ID`),
  KEY `ix_iblock_elem_prop_m8_2` (`IBLOCK_PROPERTY_ID`),
  KEY `ix_iblock_elem_prop_m8_3` (`VALUE_ENUM`,`IBLOCK_PROPERTY_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_element_prop_s6`
--

DROP TABLE IF EXISTS `b_iblock_element_prop_s6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_element_prop_s6` (
  `IBLOCK_ELEMENT_ID` int(11) NOT NULL,
  `PROPERTY_38` text,
  `DESCRIPTION_38` varchar(255) DEFAULT NULL,
  `PROPERTY_39` text,
  `DESCRIPTION_39` varchar(255) DEFAULT NULL,
  `PROPERTY_40` text,
  `DESCRIPTION_40` varchar(255) DEFAULT NULL,
  `PROPERTY_41` text,
  `DESCRIPTION_41` varchar(255) DEFAULT NULL,
  `PROPERTY_42` int(11) DEFAULT NULL,
  `DESCRIPTION_42` varchar(255) DEFAULT NULL,
  `PROPERTY_43` int(11) DEFAULT NULL,
  `DESCRIPTION_43` varchar(255) DEFAULT NULL,
  `PROPERTY_44` text,
  `DESCRIPTION_44` varchar(255) DEFAULT NULL,
  `PROPERTY_45` text,
  `DESCRIPTION_45` varchar(255) DEFAULT NULL,
  `PROPERTY_46` text,
  `DESCRIPTION_46` varchar(255) DEFAULT NULL,
  `PROPERTY_47` text,
  `DESCRIPTION_47` varchar(255) DEFAULT NULL,
  `PROPERTY_48` text,
  `DESCRIPTION_48` varchar(255) DEFAULT NULL,
  `PROPERTY_49` text,
  `DESCRIPTION_49` varchar(255) DEFAULT NULL,
  `PROPERTY_51` text,
  `DESCRIPTION_51` varchar(255) DEFAULT NULL,
  `PROPERTY_52` text,
  `DESCRIPTION_52` varchar(255) DEFAULT NULL,
  `PROPERTY_53` text,
  `DESCRIPTION_53` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IBLOCK_ELEMENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_element_prop_s8`
--

DROP TABLE IF EXISTS `b_iblock_element_prop_s8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_element_prop_s8` (
  `IBLOCK_ELEMENT_ID` int(11) NOT NULL,
  PRIMARY KEY (`IBLOCK_ELEMENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_element_property`
--

DROP TABLE IF EXISTS `b_iblock_element_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_element_property` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IBLOCK_PROPERTY_ID` int(11) NOT NULL,
  `IBLOCK_ELEMENT_ID` int(11) NOT NULL,
  `VALUE` text NOT NULL,
  `VALUE_TYPE` char(4) NOT NULL DEFAULT 'text',
  `VALUE_ENUM` int(11) DEFAULT NULL,
  `VALUE_NUM` decimal(18,4) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_iblock_element_property_1` (`IBLOCK_ELEMENT_ID`,`IBLOCK_PROPERTY_ID`),
  KEY `ix_iblock_element_property_2` (`IBLOCK_PROPERTY_ID`),
  KEY `ix_iblock_element_prop_enum` (`VALUE_ENUM`,`IBLOCK_PROPERTY_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=9011 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_fields`
--

DROP TABLE IF EXISTS `b_iblock_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_fields` (
  `IBLOCK_ID` int(18) NOT NULL,
  `FIELD_ID` varchar(50) NOT NULL,
  `IS_REQUIRED` char(1) DEFAULT NULL,
  `DEFAULT_VALUE` longtext,
  PRIMARY KEY (`IBLOCK_ID`,`FIELD_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_group`
--

DROP TABLE IF EXISTS `b_iblock_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_group` (
  `IBLOCK_ID` int(11) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  `PERMISSION` char(1) NOT NULL,
  UNIQUE KEY `ux_iblock_group_1` (`IBLOCK_ID`,`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_messages`
--

DROP TABLE IF EXISTS `b_iblock_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_messages` (
  `IBLOCK_ID` int(18) NOT NULL,
  `MESSAGE_ID` varchar(50) NOT NULL,
  `MESSAGE_TEXT` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IBLOCK_ID`,`MESSAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_property`
--

DROP TABLE IF EXISTS `b_iblock_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_property` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `IBLOCK_ID` int(11) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `CODE` varchar(50) DEFAULT NULL,
  `DEFAULT_VALUE` text,
  `PROPERTY_TYPE` char(1) NOT NULL DEFAULT 'S',
  `ROW_COUNT` int(11) NOT NULL DEFAULT '1',
  `COL_COUNT` int(11) NOT NULL DEFAULT '30',
  `LIST_TYPE` char(1) NOT NULL DEFAULT 'L',
  `MULTIPLE` char(1) NOT NULL DEFAULT 'N',
  `XML_ID` varchar(100) DEFAULT NULL,
  `FILE_TYPE` varchar(200) DEFAULT NULL,
  `MULTIPLE_CNT` int(11) DEFAULT NULL,
  `TMP_ID` varchar(40) DEFAULT NULL,
  `LINK_IBLOCK_ID` int(18) DEFAULT NULL,
  `WITH_DESCRIPTION` char(1) DEFAULT NULL,
  `SEARCHABLE` char(1) NOT NULL DEFAULT 'N',
  `FILTRABLE` char(1) NOT NULL DEFAULT 'N',
  `IS_REQUIRED` char(1) DEFAULT NULL,
  `VERSION` int(11) NOT NULL DEFAULT '1',
  `USER_TYPE` varchar(255) DEFAULT NULL,
  `USER_TYPE_SETTINGS` text,
  PRIMARY KEY (`ID`),
  KEY `ix_iblock_property_1` (`IBLOCK_ID`),
  KEY `ix_iblock_property_2` (`CODE`)
) ENGINE=MyISAM AUTO_INCREMENT=70 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_property_enum`
--

DROP TABLE IF EXISTS `b_iblock_property_enum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_property_enum` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PROPERTY_ID` int(11) NOT NULL,
  `VALUE` varchar(255) NOT NULL,
  `DEF` char(1) NOT NULL DEFAULT 'N',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `XML_ID` varchar(200) NOT NULL,
  `TMP_ID` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ux_iblock_property_enum` (`PROPERTY_ID`,`XML_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_rss`
--

DROP TABLE IF EXISTS `b_iblock_rss`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_rss` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IBLOCK_ID` int(11) NOT NULL,
  `NODE` varchar(50) NOT NULL,
  `NODE_VALUE` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_section`
--

DROP TABLE IF EXISTS `b_iblock_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_section` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  `DATE_CREATE` datetime DEFAULT NULL,
  `CREATED_BY` int(18) DEFAULT NULL,
  `IBLOCK_ID` int(11) NOT NULL,
  `IBLOCK_SECTION_ID` int(11) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `GLOBAL_ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `NAME` varchar(255) NOT NULL,
  `PICTURE` int(18) DEFAULT NULL,
  `LEFT_MARGIN` int(18) DEFAULT NULL,
  `RIGHT_MARGIN` int(18) DEFAULT NULL,
  `DEPTH_LEVEL` int(18) DEFAULT NULL,
  `DESCRIPTION` text,
  `DESCRIPTION_TYPE` char(4) NOT NULL DEFAULT 'text',
  `SEARCHABLE_CONTENT` text,
  `CODE` varchar(255) DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  `TMP_ID` varchar(40) DEFAULT NULL,
  `DETAIL_PICTURE` int(18) DEFAULT NULL,
  `SOCNET_GROUP_ID` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_iblock_section_1` (`IBLOCK_ID`,`IBLOCK_SECTION_ID`),
  KEY `ix_iblock_section_depth_level` (`IBLOCK_ID`,`DEPTH_LEVEL`),
  KEY `ix_iblock_section_left_margin` (`IBLOCK_ID`,`LEFT_MARGIN`,`RIGHT_MARGIN`),
  KEY `ix_iblock_section_right_margin` (`IBLOCK_ID`,`RIGHT_MARGIN`,`LEFT_MARGIN`),
  KEY `ix_iblock_section_code` (`IBLOCK_ID`,`CODE`)
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_section_element`
--

DROP TABLE IF EXISTS `b_iblock_section_element`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_section_element` (
  `IBLOCK_SECTION_ID` int(11) NOT NULL,
  `IBLOCK_ELEMENT_ID` int(11) NOT NULL,
  `ADDITIONAL_PROPERTY_ID` int(18) DEFAULT NULL,
  UNIQUE KEY `ux_iblock_section_element` (`IBLOCK_SECTION_ID`,`IBLOCK_ELEMENT_ID`,`ADDITIONAL_PROPERTY_ID`),
  KEY `UX_IBLOCK_SECTION_ELEMENT2` (`IBLOCK_ELEMENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_sequence`
--

DROP TABLE IF EXISTS `b_iblock_sequence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_sequence` (
  `IBLOCK_ID` int(18) NOT NULL,
  `CODE` varchar(50) NOT NULL,
  `SEQ_VALUE` int(11) DEFAULT NULL,
  PRIMARY KEY (`IBLOCK_ID`,`CODE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_site`
--

DROP TABLE IF EXISTS `b_iblock_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_site` (
  `IBLOCK_ID` int(18) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`IBLOCK_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_type`
--

DROP TABLE IF EXISTS `b_iblock_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_type` (
  `ID` varchar(50) NOT NULL,
  `SECTIONS` char(1) NOT NULL DEFAULT 'Y',
  `EDIT_FILE_BEFORE` varchar(255) DEFAULT NULL,
  `EDIT_FILE_AFTER` varchar(255) DEFAULT NULL,
  `IN_RSS` char(1) NOT NULL DEFAULT 'N',
  `SORT` int(18) NOT NULL DEFAULT '500',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_iblock_type_lang`
--

DROP TABLE IF EXISTS `b_iblock_type_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_iblock_type_lang` (
  `IBLOCK_TYPE_ID` varchar(50) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `SECTION_NAME` varchar(100) DEFAULT NULL,
  `ELEMENT_NAME` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_lang`
--

DROP TABLE IF EXISTS `b_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_lang` (
  `LID` char(2) NOT NULL,
  `SORT` int(18) NOT NULL DEFAULT '100',
  `DEF` char(1) NOT NULL DEFAULT 'N',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(50) NOT NULL,
  `DIR` varchar(50) NOT NULL,
  `FORMAT_DATE` varchar(50) NOT NULL,
  `FORMAT_DATETIME` varchar(50) NOT NULL,
  `CHARSET` varchar(255) DEFAULT NULL,
  `LANGUAGE_ID` char(2) NOT NULL,
  `DOC_ROOT` varchar(255) DEFAULT NULL,
  `DOMAIN_LIMITED` char(1) NOT NULL DEFAULT 'N',
  `SERVER_NAME` varchar(255) DEFAULT NULL,
  `SITE_NAME` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_lang_domain`
--

DROP TABLE IF EXISTS `b_lang_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_lang_domain` (
  `LID` char(2) NOT NULL,
  `DOMAIN` varchar(255) NOT NULL,
  PRIMARY KEY (`LID`,`DOMAIN`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_language`
--

DROP TABLE IF EXISTS `b_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_language` (
  `LID` char(2) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `DEF` char(1) NOT NULL DEFAULT 'N',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(50) NOT NULL,
  `FORMAT_DATE` varchar(50) NOT NULL,
  `FORMAT_DATETIME` varchar(50) NOT NULL,
  `CHARSET` varchar(255) DEFAULT NULL,
  `DIRECTION` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_answer`
--

DROP TABLE IF EXISTS `b_learn_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_answer` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `QUESTION_ID` int(11) unsigned NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '10',
  `ANSWER` text NOT NULL,
  `CORRECT` char(1) NOT NULL,
  `FEEDBACK` text,
  `MATCH_ANSWER` text,
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_ANSWER1` (`QUESTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_attempt`
--

DROP TABLE IF EXISTS `b_learn_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_attempt` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `TEST_ID` int(11) NOT NULL,
  `STUDENT_ID` int(18) NOT NULL,
  `DATE_START` datetime NOT NULL,
  `DATE_END` datetime DEFAULT NULL,
  `STATUS` char(1) NOT NULL DEFAULT 'B',
  `COMPLETED` char(1) NOT NULL DEFAULT 'N',
  `SCORE` int(11) DEFAULT '0',
  `MAX_SCORE` int(11) DEFAULT '0',
  `QUESTIONS` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_ATTEMPT1` (`STUDENT_ID`,`TEST_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_certification`
--

DROP TABLE IF EXISTS `b_learn_certification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_certification` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `STUDENT_ID` int(18) NOT NULL,
  `COURSE_ID` int(11) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DATE_CREATE` datetime DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `FROM_ONLINE` char(1) NOT NULL DEFAULT 'Y',
  `PUBLIC_PROFILE` char(1) NOT NULL DEFAULT 'Y',
  `SUMMARY` int(11) NOT NULL DEFAULT '0',
  `MAX_SUMMARY` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_CERTIFICATION1` (`STUDENT_ID`,`COURSE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_chapter`
--

DROP TABLE IF EXISTS `b_learn_chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_chapter` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `COURSE_ID` int(11) unsigned NOT NULL,
  `CHAPTER_ID` int(11) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `CODE` varchar(50) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `PREVIEW_PICTURE` int(18) DEFAULT NULL,
  `PREVIEW_TEXT` text,
  `PREVIEW_TEXT_TYPE` char(4) NOT NULL DEFAULT 'text',
  `DETAIL_PICTURE` int(18) DEFAULT NULL,
  `DETAIL_TEXT` longtext,
  `DETAIL_TEXT_TYPE` char(4) NOT NULL DEFAULT 'text',
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_CHAPTER1` (`COURSE_ID`,`CHAPTER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_course`
--

DROP TABLE IF EXISTS `b_learn_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_course` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `CODE` varchar(50) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `PREVIEW_PICTURE` int(18) DEFAULT NULL,
  `PREVIEW_TEXT` text,
  `PREVIEW_TEXT_TYPE` char(4) NOT NULL DEFAULT 'text',
  `DESCRIPTION` text,
  `DESCRIPTION_TYPE` char(4) NOT NULL DEFAULT 'text',
  `ACTIVE_FROM` datetime DEFAULT NULL,
  `ACTIVE_TO` datetime DEFAULT NULL,
  `SCORM` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_course_permission`
--

DROP TABLE IF EXISTS `b_learn_course_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_course_permission` (
  `COURSE_ID` int(11) unsigned NOT NULL,
  `USER_GROUP_ID` int(18) unsigned NOT NULL,
  `PERMISSION` char(1) NOT NULL,
  PRIMARY KEY (`COURSE_ID`,`USER_GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_course_site`
--

DROP TABLE IF EXISTS `b_learn_course_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_course_site` (
  `COURSE_ID` int(11) unsigned NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`COURSE_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_gradebook`
--

DROP TABLE IF EXISTS `b_learn_gradebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_gradebook` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `STUDENT_ID` int(18) NOT NULL,
  `TEST_ID` int(11) NOT NULL,
  `RESULT` int(11) DEFAULT NULL,
  `MAX_RESULT` int(11) DEFAULT NULL,
  `ATTEMPTS` int(11) NOT NULL DEFAULT '1',
  `COMPLETED` char(1) NOT NULL DEFAULT 'N',
  `EXTRA_ATTEMPTS` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_B_LEARN_GRADEBOOK1` (`STUDENT_ID`,`TEST_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_lesson`
--

DROP TABLE IF EXISTS `b_learn_lesson`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_lesson` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DATE_CREATE` datetime DEFAULT NULL,
  `CREATED_BY` int(18) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `COURSE_ID` int(11) unsigned NOT NULL DEFAULT '0',
  `CHAPTER_ID` int(11) unsigned DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `PREVIEW_PICTURE` int(18) DEFAULT NULL,
  `PREVIEW_TEXT` text,
  `PREVIEW_TEXT_TYPE` char(4) NOT NULL DEFAULT 'text',
  `DETAIL_PICTURE` int(18) DEFAULT NULL,
  `DETAIL_TEXT` longtext,
  `DETAIL_TEXT_TYPE` char(4) NOT NULL DEFAULT 'text',
  `LAUNCH` text,
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_LESSON1` (`COURSE_ID`,`CHAPTER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_question`
--

DROP TABLE IF EXISTS `b_learn_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_question` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LESSON_ID` int(11) unsigned NOT NULL,
  `QUESTION_TYPE` char(1) NOT NULL DEFAULT 'S',
  `NAME` varchar(255) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `DESCRIPTION` text,
  `DESCRIPTION_TYPE` char(4) NOT NULL DEFAULT 'text',
  `FILE_ID` int(18) DEFAULT NULL,
  `SELF` char(1) NOT NULL DEFAULT 'N',
  `POINT` int(11) NOT NULL DEFAULT '10',
  `DIRECTION` char(1) NOT NULL DEFAULT 'V',
  `CORRECT_REQUIRED` char(1) NOT NULL DEFAULT 'N',
  `EMAIL_ANSWER` char(1) NOT NULL DEFAULT 'N',
  `INCORRECT_MESSAGE` text,
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_QUESTION1` (`LESSON_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_site_path`
--

DROP TABLE IF EXISTS `b_learn_site_path`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_site_path` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `PATH` varchar(255) NOT NULL,
  `TYPE` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_LEARN_SITE_PATH_2` (`SITE_ID`,`TYPE`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_student`
--

DROP TABLE IF EXISTS `b_learn_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_student` (
  `USER_ID` int(18) NOT NULL,
  `TRANSCRIPT` int(11) NOT NULL,
  `PUBLIC_PROFILE` char(1) NOT NULL DEFAULT 'N',
  `RESUME` text,
  PRIMARY KEY (`USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_test`
--

DROP TABLE IF EXISTS `b_learn_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_test` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `COURSE_ID` int(11) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `DESCRIPTION_TYPE` char(4) NOT NULL DEFAULT 'text',
  `ATTEMPT_LIMIT` int(11) NOT NULL DEFAULT '0',
  `TIME_LIMIT` int(11) DEFAULT '0',
  `COMPLETED_SCORE` int(11) DEFAULT NULL,
  `QUESTIONS_FROM` char(1) NOT NULL DEFAULT 'A',
  `QUESTIONS_AMOUNT` int(11) NOT NULL DEFAULT '0',
  `RANDOM_QUESTIONS` char(1) NOT NULL DEFAULT 'Y',
  `RANDOM_ANSWERS` char(1) NOT NULL DEFAULT 'Y',
  `APPROVED` char(1) NOT NULL DEFAULT 'Y',
  `INCLUDE_SELF_TEST` char(1) NOT NULL DEFAULT 'N',
  `PASSAGE_TYPE` char(1) NOT NULL DEFAULT '0',
  `PREVIOUS_TEST_ID` int(11) DEFAULT NULL,
  `PREVIOUS_TEST_SCORE` int(11) DEFAULT '0',
  `INCORRECT_CONTROL` char(1) NOT NULL DEFAULT 'N',
  `CURRENT_INDICATION` int(11) NOT NULL DEFAULT '0',
  `FINAL_INDICATION` int(11) NOT NULL DEFAULT '0',
  `MIN_TIME_BETWEEN_ATTEMPTS` int(11) NOT NULL DEFAULT '0',
  `SHOW_ERRORS` char(1) NOT NULL DEFAULT 'N',
  `NEXT_QUESTION_ON_ERROR` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_TEST1` (`COURSE_ID`),
  KEY `IX_B_LEARN_TEST2` (`PREVIOUS_TEST_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_test_mark`
--

DROP TABLE IF EXISTS `b_learn_test_mark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_test_mark` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TEST_ID` int(11) NOT NULL,
  `SCORE` int(11) NOT NULL,
  `MARK` varchar(50) NOT NULL,
  `DESCRIPTION` text,
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_TEST_MARK1` (`TEST_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_learn_test_result`
--

DROP TABLE IF EXISTS `b_learn_test_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_learn_test_result` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ATTEMPT_ID` int(11) unsigned NOT NULL,
  `QUESTION_ID` int(11) NOT NULL,
  `RESPONSE` text,
  `POINT` int(11) NOT NULL DEFAULT '0',
  `CORRECT` char(1) NOT NULL DEFAULT 'N',
  `ANSWERED` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `IX_B_LEARN_TEST_RESULT1` (`ATTEMPT_ID`,`QUESTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_list_rubric`
--

DROP TABLE IF EXISTS `b_list_rubric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_list_rubric` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` char(2) NOT NULL,
  `NAME` varchar(100) DEFAULT NULL,
  `DESCRIPTION` text,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `AUTO` char(1) NOT NULL DEFAULT 'N',
  `DAYS_OF_MONTH` varchar(100) DEFAULT NULL,
  `DAYS_OF_WEEK` varchar(15) DEFAULT NULL,
  `TIMES_OF_DAY` varchar(255) DEFAULT NULL,
  `TEMPLATE` varchar(100) DEFAULT NULL,
  `LAST_EXECUTED` datetime DEFAULT NULL,
  `VISIBLE` char(1) NOT NULL DEFAULT 'Y',
  `FROM_FIELD` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_lists_field`
--

DROP TABLE IF EXISTS `b_lists_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_lists_field` (
  `IBLOCK_ID` int(11) NOT NULL,
  `FIELD_ID` varchar(50) NOT NULL,
  `SORT` int(11) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `SETTINGS` text,
  PRIMARY KEY (`IBLOCK_ID`,`FIELD_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_lists_permission`
--

DROP TABLE IF EXISTS `b_lists_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_lists_permission` (
  `IBLOCK_TYPE_ID` varchar(50) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  PRIMARY KEY (`IBLOCK_TYPE_ID`,`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_lists_socnet_group`
--

DROP TABLE IF EXISTS `b_lists_socnet_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_lists_socnet_group` (
  `IBLOCK_ID` int(11) NOT NULL,
  `SOCNET_ROLE` char(1) DEFAULT NULL,
  `PERMISSION` char(1) NOT NULL,
  UNIQUE KEY `ux_b_lists_socnet_group_1` (`IBLOCK_ID`,`SOCNET_ROLE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_filter`
--

DROP TABLE IF EXISTS `b_mail_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_filter` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MAILBOX_ID` int(18) NOT NULL,
  `PARENT_FILTER_ID` int(18) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `SORT` int(18) NOT NULL DEFAULT '500',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `PHP_CONDITION` text,
  `WHEN_MAIL_RECEIVED` char(1) NOT NULL DEFAULT 'N',
  `WHEN_MANUALLY_RUN` char(1) NOT NULL DEFAULT 'N',
  `SPAM_RATING` decimal(9,4) DEFAULT NULL,
  `SPAM_RATING_TYPE` char(1) DEFAULT '<',
  `MESSAGE_SIZE` int(18) DEFAULT NULL,
  `MESSAGE_SIZE_TYPE` char(1) DEFAULT '<',
  `MESSAGE_SIZE_UNIT` char(1) DEFAULT NULL,
  `ACTION_STOP_EXEC` char(1) NOT NULL DEFAULT 'N',
  `ACTION_DELETE_MESSAGE` char(1) NOT NULL DEFAULT 'N',
  `ACTION_READ` char(1) NOT NULL DEFAULT '-',
  `ACTION_PHP` text,
  `ACTION_TYPE` varchar(50) DEFAULT NULL,
  `ACTION_VARS` text,
  `ACTION_SPAM` char(1) NOT NULL DEFAULT '-',
  PRIMARY KEY (`ID`),
  KEY `IX_MAIL_FILTER_MAILBOX` (`MAILBOX_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_filter_cond`
--

DROP TABLE IF EXISTS `b_mail_filter_cond`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_filter_cond` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILTER_ID` int(11) NOT NULL,
  `TYPE` varchar(50) NOT NULL,
  `STRINGS` text NOT NULL,
  `COMPARE_TYPE` varchar(30) NOT NULL DEFAULT 'CONTAIN',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_log`
--

DROP TABLE IF EXISTS `b_mail_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_log` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `MAILBOX_ID` int(18) NOT NULL DEFAULT '0',
  `FILTER_ID` int(18) DEFAULT NULL,
  `MESSAGE_ID` int(18) DEFAULT NULL,
  `LOG_TYPE` varchar(50) DEFAULT NULL,
  `DATE_INSERT` datetime NOT NULL,
  `STATUS_GOOD` char(1) NOT NULL DEFAULT 'Y',
  `MESSAGE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_MAIL_MSGLOG_1` (`MAILBOX_ID`),
  KEY `IX_MAIL_MSGLOG_2` (`MESSAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_mailbox`
--

DROP TABLE IF EXISTS `b_mail_mailbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_mailbox` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LID` char(2) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(255) DEFAULT NULL,
  `SERVER` varchar(255) NOT NULL,
  `PORT` int(18) NOT NULL DEFAULT '110',
  `LOGIN` varchar(255) DEFAULT NULL,
  `CHARSET` varchar(255) DEFAULT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `USE_MD5` char(1) NOT NULL DEFAULT 'N',
  `DELETE_MESSAGES` char(1) NOT NULL DEFAULT 'N',
  `PERIOD_CHECK` int(15) DEFAULT NULL,
  `MAX_MSG_COUNT` int(11) DEFAULT '0',
  `MAX_MSG_SIZE` int(11) DEFAULT '0',
  `MAX_KEEP_DAYS` int(11) DEFAULT '0',
  `USE_TLS` char(1) NOT NULL DEFAULT 'N',
  `SERVER_TYPE` varchar(5) NOT NULL DEFAULT 'pop3',
  `DOMAINS` varchar(255) DEFAULT NULL,
  `RELAY` char(1) NOT NULL DEFAULT 'Y',
  `AUTH_RELAY` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_message`
--

DROP TABLE IF EXISTS `b_mail_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_message` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `MAILBOX_ID` int(18) NOT NULL,
  `DATE_INSERT` datetime NOT NULL,
  `FULL_TEXT` longtext,
  `MESSAGE_SIZE` int(18) NOT NULL,
  `HEADER` text,
  `FIELD_DATE` datetime DEFAULT NULL,
  `FIELD_FROM` varchar(255) DEFAULT NULL,
  `FIELD_REPLY_TO` varchar(255) DEFAULT NULL,
  `FIELD_TO` varchar(255) DEFAULT NULL,
  `FIELD_CC` varchar(255) DEFAULT NULL,
  `FIELD_BCC` varchar(255) DEFAULT NULL,
  `FIELD_PRIORITY` int(18) NOT NULL DEFAULT '3',
  `SUBJECT` varchar(255) DEFAULT NULL,
  `BODY` longtext,
  `ATTACHMENTS` int(18) DEFAULT '0',
  `NEW_MESSAGE` char(1) DEFAULT 'Y',
  `SPAM` char(1) NOT NULL DEFAULT '?',
  `SPAM_RATING` decimal(18,4) DEFAULT NULL,
  `SPAM_WORDS` varchar(255) DEFAULT NULL,
  `SPAM_LAST_RESULT` char(1) NOT NULL DEFAULT 'N',
  `FOR_SPAM_TEST` mediumtext,
  `EXTERNAL_ID` varchar(255) DEFAULT NULL,
  `MSG_ID` varchar(255) DEFAULT NULL,
  `IN_REPLY_TO` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_MAIL_MESSAGE` (`MAILBOX_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_message_uid`
--

DROP TABLE IF EXISTS `b_mail_message_uid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_message_uid` (
  `ID` varchar(32) NOT NULL,
  `MAILBOX_ID` int(18) NOT NULL,
  `SESSION_ID` varchar(32) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DATE_INSERT` datetime NOT NULL,
  `MESSAGE_ID` int(18) NOT NULL,
  PRIMARY KEY (`ID`,`MAILBOX_ID`),
  KEY `IX_MAIL_MSG_UID` (`MAILBOX_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_msg_attachment`
--

DROP TABLE IF EXISTS `b_mail_msg_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_msg_attachment` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `MESSAGE_ID` int(18) NOT NULL,
  `FILE_ID` int(18) NOT NULL DEFAULT '0',
  `FILE_NAME` varchar(255) DEFAULT NULL,
  `FILE_SIZE` int(11) NOT NULL DEFAULT '0',
  `FILE_DATA` longblob,
  `CONTENT_TYPE` varchar(255) DEFAULT NULL,
  `IMAGE_WIDTH` int(18) DEFAULT NULL,
  `IMAGE_HEIGHT` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_MAIL_MESSATTACHMENT` (`MESSAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mail_spam_weight`
--

DROP TABLE IF EXISTS `b_mail_spam_weight`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_mail_spam_weight` (
  `WORD_ID` varchar(32) NOT NULL,
  `WORD_REAL` varchar(50) NOT NULL,
  `GOOD_CNT` int(18) NOT NULL DEFAULT '0',
  `BAD_CNT` int(18) NOT NULL DEFAULT '0',
  `TOTAL_CNT` int(18) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`WORD_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_medialib_collection`
--

DROP TABLE IF EXISTS `b_medialib_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_medialib_collection` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `DATE_UPDATE` datetime NOT NULL,
  `OWNER_ID` int(11) DEFAULT NULL,
  `PARENT_ID` int(11) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `KEYWORDS` varchar(255) DEFAULT NULL,
  `ITEMS_COUNT` int(11) DEFAULT NULL,
  `ML_TYPE` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_medialib_collection_item`
--

DROP TABLE IF EXISTS `b_medialib_collection_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_medialib_collection_item` (
  `COLLECTION_ID` int(11) NOT NULL,
  `ITEM_ID` int(11) NOT NULL,
  PRIMARY KEY (`ITEM_ID`,`COLLECTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_medialib_item`
--

DROP TABLE IF EXISTS `b_medialib_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_medialib_item` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `ITEM_TYPE` char(30) NOT NULL,
  `DESCRIPTION` text,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `SOURCE_ID` int(11) NOT NULL,
  `KEYWORDS` varchar(255) DEFAULT NULL,
  `SEARCHABLE_CONTENT` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_medialib_type`
--

DROP TABLE IF EXISTS `b_medialib_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_medialib_type` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  `CODE` varchar(255) NOT NULL,
  `EXT` varchar(255) NOT NULL,
  `SYSTEM` char(1) NOT NULL DEFAULT 'N',
  `DESCRIPTION` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_module`
--

DROP TABLE IF EXISTS `b_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_module` (
  `ID` varchar(50) NOT NULL,
  `DATE_ACTIVE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_module_group`
--

DROP TABLE IF EXISTS `b_module_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_module_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MODULE_ID` varchar(50) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  `G_ACCESS` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UK_GROUP_MODULE` (`MODULE_ID`,`GROUP_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_module_to_module`
--

DROP TABLE IF EXISTS `b_module_to_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_module_to_module` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SORT` int(18) NOT NULL DEFAULT '100',
  `FROM_MODULE_ID` varchar(50) NOT NULL,
  `MESSAGE_ID` varchar(50) NOT NULL,
  `TO_MODULE_ID` varchar(50) NOT NULL,
  `TO_PATH` varchar(255) DEFAULT NULL,
  `TO_CLASS` varchar(50) DEFAULT NULL,
  `TO_METHOD` varchar(50) DEFAULT NULL,
  `TO_METHOD_ARG` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_module_to_module` (`FROM_MODULE_ID`,`MESSAGE_ID`,`TO_MODULE_ID`,`TO_CLASS`,`TO_METHOD`)
) ENGINE=MyISAM AUTO_INCREMENT=158 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_operation`
--

DROP TABLE IF EXISTS `b_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_operation` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) NOT NULL,
  `MODULE_ID` varchar(50) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `BINDING` varchar(50) DEFAULT 'module',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_option`
--

DROP TABLE IF EXISTS `b_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_option` (
  `MODULE_ID` varchar(50) DEFAULT NULL,
  `NAME` varchar(50) NOT NULL,
  `VALUE` text,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  UNIQUE KEY `ix_option` (`MODULE_ID`,`NAME`,`SITE_ID`),
  KEY `ix_option_name` (`NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_perf_cluster`
--

DROP TABLE IF EXISTS `b_perf_cluster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_perf_cluster` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `THREADS` int(11) DEFAULT NULL,
  `HITS` int(11) DEFAULT NULL,
  `ERRORS` int(11) DEFAULT NULL,
  `PAGES_PER_SECOND` float DEFAULT NULL,
  `PAGE_EXEC_TIME` float DEFAULT NULL,
  `PAGE_RESP_TIME` float DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_perf_component`
--

DROP TABLE IF EXISTS `b_perf_component`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_perf_component` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `HIT_ID` int(18) DEFAULT NULL,
  `NN` int(18) DEFAULT NULL,
  `CACHE_TYPE` char(1) DEFAULT NULL,
  `CACHE_SIZE` int(11) DEFAULT NULL,
  `COMPONENT_TIME` float DEFAULT NULL,
  `QUERIES` int(11) DEFAULT NULL,
  `QUERIES_TIME` float DEFAULT NULL,
  `COMPONENT_NAME` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_B_PERF_COMPONENT_0` (`HIT_ID`,`NN`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_perf_error`
--

DROP TABLE IF EXISTS `b_perf_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_perf_error` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `HIT_ID` int(18) DEFAULT NULL,
  `ERRNO` int(18) DEFAULT NULL,
  `ERRSTR` text,
  `ERRFILE` text,
  `ERRLINE` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_PERF_ERROR_0` (`HIT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_perf_hit`
--

DROP TABLE IF EXISTS `b_perf_hit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_perf_hit` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DATE_HIT` datetime DEFAULT NULL,
  `IS_ADMIN` char(1) DEFAULT NULL,
  `REQUEST_METHOD` varchar(50) DEFAULT NULL,
  `SERVER_NAME` varchar(50) DEFAULT NULL,
  `SERVER_PORT` int(11) DEFAULT NULL,
  `SCRIPT_NAME` text,
  `REQUEST_URI` text,
  `INCLUDED_FILES` int(11) DEFAULT NULL,
  `MEMORY_PEAK_USAGE` int(11) DEFAULT NULL,
  `CACHE_TYPE` char(1) DEFAULT NULL,
  `CACHE_SIZE` int(11) DEFAULT NULL,
  `QUERIES` int(11) DEFAULT NULL,
  `QUERIES_TIME` float DEFAULT NULL,
  `COMPONENTS` int(11) DEFAULT NULL,
  `COMPONENTS_TIME` float DEFAULT NULL,
  `SQL_LOG` char(1) DEFAULT NULL,
  `PAGE_TIME` float DEFAULT NULL,
  `PROLOG_TIME` float DEFAULT NULL,
  `PROLOG_BEFORE_TIME` float DEFAULT NULL,
  `AGENTS_TIME` float DEFAULT NULL,
  `PROLOG_AFTER_TIME` float DEFAULT NULL,
  `WORK_AREA_TIME` float DEFAULT NULL,
  `EPILOG_TIME` float DEFAULT NULL,
  `EPILOG_BEFORE_TIME` float DEFAULT NULL,
  `EVENTS_TIME` float DEFAULT NULL,
  `EPILOG_AFTER_TIME` float DEFAULT NULL,
  `MENU_RECALC` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_PERF_HIT_0` (`DATE_HIT`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_perf_sql`
--

DROP TABLE IF EXISTS `b_perf_sql`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_perf_sql` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `HIT_ID` int(18) DEFAULT NULL,
  `COMPONENT_ID` int(18) DEFAULT NULL,
  `NN` int(18) DEFAULT NULL,
  `QUERY_TIME` float DEFAULT NULL,
  `MODULE_NAME` text,
  `COMPONENT_NAME` text,
  `SQL_TEXT` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_B_PERF_SQL_0` (`HIT_ID`,`NN`),
  KEY `IX_B_PERF_SQL_1` (`COMPONENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_perf_test`
--

DROP TABLE IF EXISTS `b_perf_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_perf_test` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `REFERENCE_ID` int(18) DEFAULT NULL,
  `NAME` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_B_PERF_TEST_0` (`REFERENCE_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=401 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_posting`
--

DROP TABLE IF EXISTS `b_posting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_posting` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `STATUS` char(1) NOT NULL DEFAULT 'D',
  `VERSION` char(1) DEFAULT NULL,
  `DATE_SENT` datetime DEFAULT NULL,
  `SENT_BCC` mediumtext,
  `FROM_FIELD` varchar(255) NOT NULL,
  `TO_FIELD` varchar(255) DEFAULT NULL,
  `BCC_FIELD` mediumtext,
  `EMAIL_FILTER` varchar(255) DEFAULT NULL,
  `SUBJECT` varchar(255) NOT NULL,
  `BODY_TYPE` varchar(4) NOT NULL DEFAULT 'text',
  `BODY` mediumtext NOT NULL,
  `DIRECT_SEND` char(1) NOT NULL DEFAULT 'N',
  `CHARSET` varchar(50) DEFAULT NULL,
  `MSG_CHARSET` varchar(255) DEFAULT NULL,
  `SUBSCR_FORMAT` varchar(4) DEFAULT NULL,
  `ERROR_EMAIL` mediumtext,
  `AUTO_SEND_TIME` datetime DEFAULT NULL,
  `BCC_TO_SEND` mediumtext,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_posting_email`
--

DROP TABLE IF EXISTS `b_posting_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_posting_email` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `POSTING_ID` int(11) NOT NULL,
  `STATUS` char(1) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `SUBSCRIPTION_ID` int(11) DEFAULT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_posting_email_status` (`POSTING_ID`,`STATUS`),
  KEY `ix_posting_email_email` (`POSTING_ID`,`EMAIL`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_posting_file`
--

DROP TABLE IF EXISTS `b_posting_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_posting_file` (
  `POSTING_ID` int(11) NOT NULL,
  `FILE_ID` int(11) NOT NULL,
  UNIQUE KEY `UK_POSTING_POSTING_FILE` (`POSTING_ID`,`FILE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_posting_group`
--

DROP TABLE IF EXISTS `b_posting_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_posting_group` (
  `POSTING_ID` int(11) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  UNIQUE KEY `UK_POSTING_POSTING_GROUP` (`POSTING_ID`,`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_posting_rubric`
--

DROP TABLE IF EXISTS `b_posting_rubric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_posting_rubric` (
  `POSTING_ID` int(11) NOT NULL,
  `LIST_RUBRIC_ID` int(11) NOT NULL,
  UNIQUE KEY `UK_POSTING_POSTING_RUBRIC` (`POSTING_ID`,`LIST_RUBRIC_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating`
--

DROP TABLE IF EXISTS `b_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ACTIVE` char(1) NOT NULL,
  `NAME` varchar(512) NOT NULL,
  `ENTITY_ID` varchar(50) NOT NULL,
  `CALCULATION_METHOD` varchar(3) NOT NULL DEFAULT 'SUM',
  `CREATED` datetime DEFAULT NULL,
  `LAST_MODIFIED` datetime DEFAULT NULL,
  `LAST_CALCULATED` datetime DEFAULT NULL,
  `POSITION` char(1) DEFAULT 'N',
  `AUTHORITY` char(1) DEFAULT 'N',
  `CALCULATED` char(1) NOT NULL DEFAULT 'N',
  `CONFIGS` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_component`
--

DROP TABLE IF EXISTS `b_rating_component`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_component` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RATING_ID` int(11) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'N',
  `ENTITY_ID` varchar(50) NOT NULL,
  `MODULE_ID` varchar(50) NOT NULL,
  `RATING_TYPE` varchar(50) NOT NULL,
  `NAME` varchar(50) NOT NULL,
  `COMPLEX_NAME` varchar(200) NOT NULL,
  `CLASS` varchar(255) NOT NULL,
  `CALC_METHOD` varchar(255) NOT NULL,
  `EXCEPTION_METHOD` varchar(255) DEFAULT NULL,
  `LAST_MODIFIED` datetime DEFAULT NULL,
  `LAST_CALCULATED` datetime DEFAULT NULL,
  `NEXT_CALCULATION` datetime DEFAULT NULL,
  `REFRESH_INTERVAL` int(11) NOT NULL,
  `CONFIG` text,
  PRIMARY KEY (`ID`),
  KEY `IX_RATING_ID_1` (`RATING_ID`,`ACTIVE`,`NEXT_CALCULATION`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_component_results`
--

DROP TABLE IF EXISTS `b_rating_component_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_component_results` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RATING_ID` int(11) NOT NULL,
  `ENTITY_TYPE_ID` varchar(50) NOT NULL,
  `ENTITY_ID` int(11) NOT NULL,
  `MODULE_ID` varchar(50) NOT NULL,
  `RATING_TYPE` varchar(50) NOT NULL,
  `NAME` varchar(50) NOT NULL,
  `COMPLEX_NAME` varchar(200) NOT NULL,
  `CURRENT_VALUE` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_ENTITY_TYPE_ID` (`ENTITY_TYPE_ID`),
  KEY `IX_COMPLEX_NAME` (`COMPLEX_NAME`),
  KEY `IX_RATING_ID_2` (`RATING_ID`,`COMPLEX_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_results`
--

DROP TABLE IF EXISTS `b_rating_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_results` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RATING_ID` int(11) NOT NULL,
  `ENTITY_TYPE_ID` varchar(50) NOT NULL,
  `ENTITY_ID` int(11) NOT NULL,
  `CURRENT_VALUE` decimal(18,4) DEFAULT NULL,
  `PREVIOUS_VALUE` decimal(18,4) DEFAULT NULL,
  `CURRENT_POSITION` int(11) DEFAULT '0',
  `PREVIOUS_POSITION` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_RATING_3` (`RATING_ID`,`ENTITY_TYPE_ID`,`ENTITY_ID`),
  KEY `IX_RATING_4` (`RATING_ID`,`ENTITY_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_rule`
--

DROP TABLE IF EXISTS `b_rating_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_rule` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ACTIVE` char(1) NOT NULL DEFAULT 'N',
  `NAME` varchar(256) NOT NULL,
  `ENTITY_TYPE_ID` varchar(50) NOT NULL,
  `CONDITION_NAME` varchar(200) NOT NULL,
  `CONDITION_CLASS` varchar(255) NOT NULL,
  `CONDITION_METHOD` varchar(255) NOT NULL,
  `CONDITION_CONFIG` text NOT NULL,
  `ACTION_NAME` varchar(200) NOT NULL,
  `ACTION_CONFIG` text NOT NULL,
  `ACTIVATE` char(1) NOT NULL DEFAULT 'N',
  `ACTIVATE_CLASS` varchar(255) NOT NULL,
  `ACTIVATE_METHOD` varchar(255) NOT NULL,
  `DEACTIVATE` char(1) NOT NULL DEFAULT 'N',
  `DEACTIVATE_CLASS` varchar(255) NOT NULL,
  `DEACTIVATE_METHOD` varchar(255) NOT NULL,
  `CREATED` datetime DEFAULT NULL,
  `LAST_MODIFIED` datetime DEFAULT NULL,
  `LAST_APPLIED` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_rule_vetting`
--

DROP TABLE IF EXISTS `b_rating_rule_vetting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_rule_vetting` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RULE_ID` int(11) NOT NULL,
  `ENTITY_TYPE_ID` varchar(50) NOT NULL,
  `ENTITY_ID` int(11) NOT NULL,
  `ACTIVATE` char(1) NOT NULL DEFAULT 'N',
  `APPLIED` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `RULE_ID` (`RULE_ID`,`ENTITY_TYPE_ID`,`ENTITY_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_user`
--

DROP TABLE IF EXISTS `b_rating_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RATING_ID` int(11) NOT NULL,
  `ENTITY_ID` int(11) NOT NULL,
  `BONUS` decimal(18,4) DEFAULT '0.0000',
  `VOTE_WEIGHT` decimal(18,4) DEFAULT '0.0000',
  `VOTE_COUNT` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `RATING_ID` (`RATING_ID`,`ENTITY_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_vote`
--

DROP TABLE IF EXISTS `b_rating_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_vote` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RATING_VOTING_ID` int(11) NOT NULL,
  `VALUE` decimal(18,4) NOT NULL,
  `ACTIVE` char(1) NOT NULL,
  `CREATED` datetime NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `USER_IP` varchar(64) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_RAT_VOTING_ID` (`RATING_VOTING_ID`,`USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_vote_group`
--

DROP TABLE IF EXISTS `b_rating_vote_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_vote_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GROUP_ID` int(11) NOT NULL,
  `TYPE` char(1) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `RATING_ID` (`GROUP_ID`,`TYPE`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_voting`
--

DROP TABLE IF EXISTS `b_rating_voting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_voting` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ENTITY_TYPE_ID` varchar(50) NOT NULL,
  `ENTITY_ID` int(11) NOT NULL,
  `ACTIVE` char(1) NOT NULL,
  `CREATED` datetime DEFAULT NULL,
  `LAST_CALCULATED` datetime DEFAULT NULL,
  `TOTAL_VALUE` decimal(18,4) NOT NULL,
  `TOTAL_VOTES` int(11) NOT NULL,
  `TOTAL_POSITIVE_VOTES` int(11) NOT NULL,
  `TOTAL_NEGATIVE_VOTES` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_ENTITY_TYPE_ID_2` (`ENTITY_TYPE_ID`,`ENTITY_ID`,`ACTIVE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_rating_weight`
--

DROP TABLE IF EXISTS `b_rating_weight`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_rating_weight` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RATING_FROM` decimal(18,4) NOT NULL,
  `RATING_TO` decimal(18,4) NOT NULL,
  `WEIGHT` decimal(18,4) DEFAULT '0.0000',
  `COUNT` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_affiliate`
--

DROP TABLE IF EXISTS `b_sale_affiliate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_affiliate` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `AFFILIATE_ID` int(11) DEFAULT NULL,
  `PLAN_ID` int(11) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DATE_CREATE` datetime NOT NULL,
  `PAID_SUM` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `APPROVED_SUM` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `PENDING_SUM` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `ITEMS_NUMBER` int(11) NOT NULL DEFAULT '0',
  `ITEMS_SUM` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `LAST_CALCULATE` datetime DEFAULT NULL,
  `AFF_SITE` varchar(200) DEFAULT NULL,
  `AFF_DESCRIPTION` text,
  `FIX_PLAN` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SAA_USER_ID` (`USER_ID`,`SITE_ID`),
  KEY `IX_SAA_AFFILIATE_ID` (`AFFILIATE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_affiliate_plan`
--

DROP TABLE IF EXISTS `b_sale_affiliate_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_affiliate_plan` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `NAME` varchar(250) NOT NULL,
  `DESCRIPTION` text,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `BASE_RATE` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `BASE_RATE_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BASE_RATE_CURRENCY` char(3) DEFAULT NULL,
  `MIN_PAY` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `MIN_PLAN_VALUE` decimal(18,4) DEFAULT NULL,
  `VALUE_CURRENCY` char(3) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_affiliate_plan_section`
--

DROP TABLE IF EXISTS `b_sale_affiliate_plan_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_affiliate_plan_section` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PLAN_ID` int(11) NOT NULL,
  `MODULE_ID` varchar(50) NOT NULL DEFAULT 'catalog',
  `SECTION_ID` varchar(255) NOT NULL,
  `RATE` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `RATE_TYPE` char(1) NOT NULL DEFAULT 'P',
  `RATE_CURRENCY` char(3) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SAP_PLAN_ID` (`PLAN_ID`,`MODULE_ID`,`SECTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_affiliate_tier`
--

DROP TABLE IF EXISTS `b_sale_affiliate_tier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_affiliate_tier` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `RATE1` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `RATE2` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `RATE3` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `RATE4` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `RATE5` decimal(18,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SAT_SITE_ID` (`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_affiliate_transact`
--

DROP TABLE IF EXISTS `b_sale_affiliate_transact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_affiliate_transact` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AFFILIATE_ID` int(11) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `TRANSACT_DATE` datetime NOT NULL,
  `AMOUNT` decimal(18,4) NOT NULL,
  `CURRENCY` char(3) NOT NULL,
  `DEBIT` char(1) NOT NULL DEFAULT 'N',
  `DESCRIPTION` varchar(100) NOT NULL,
  `EMPLOYEE_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_SAT_AFFILIATE_ID` (`AFFILIATE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_auxiliary`
--

DROP TABLE IF EXISTS `b_sale_auxiliary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_auxiliary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ITEM` varchar(255) NOT NULL,
  `ITEM_MD5` varchar(32) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `DATE_INSERT` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_STT_USER_ITEM` (`USER_ID`,`ITEM_MD5`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_basket`
--

DROP TABLE IF EXISTS `b_sale_basket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_basket` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FUSER_ID` int(11) NOT NULL,
  `ORDER_ID` int(11) DEFAULT NULL,
  `PRODUCT_ID` int(11) NOT NULL,
  `PRODUCT_PRICE_ID` int(11) DEFAULT NULL,
  `PRICE` decimal(18,2) NOT NULL,
  `CURRENCY` char(3) NOT NULL,
  `DATE_INSERT` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `WEIGHT` double(18,2) DEFAULT NULL,
  `QUANTITY` double(18,2) NOT NULL DEFAULT '0.00',
  `LID` char(2) NOT NULL,
  `DELAY` char(1) NOT NULL DEFAULT 'N',
  `NAME` varchar(255) NOT NULL,
  `CAN_BUY` char(1) NOT NULL DEFAULT 'Y',
  `MODULE` varchar(100) DEFAULT NULL,
  `CALLBACK_FUNC` varchar(100) DEFAULT NULL,
  `NOTES` varchar(250) DEFAULT NULL,
  `ORDER_CALLBACK_FUNC` varchar(100) DEFAULT NULL,
  `DETAIL_PAGE_URL` varchar(250) DEFAULT NULL,
  `DISCOUNT_PRICE` decimal(18,2) NOT NULL DEFAULT '0.00',
  `CANCEL_CALLBACK_FUNC` varchar(100) DEFAULT NULL,
  `PAY_CALLBACK_FUNC` varchar(100) DEFAULT NULL,
  `CATALOG_XML_ID` varchar(100) DEFAULT NULL,
  `PRODUCT_XML_ID` varchar(100) DEFAULT NULL,
  `DISCOUNT_NAME` varchar(255) DEFAULT NULL,
  `DISCOUNT_VALUE` char(32) DEFAULT NULL,
  `DISCOUNT_COUPON` char(32) DEFAULT NULL,
  `VAT_RATE` decimal(18,2) DEFAULT '0.00',
  PRIMARY KEY (`ID`),
  KEY `IXS_BASKET_LID` (`LID`),
  KEY `IXS_BASKET_USER_ID` (`FUSER_ID`),
  KEY `IXS_BASKET_ORDER_ID` (`ORDER_ID`),
  KEY `IXS_BASKET_PRODUCT_ID` (`PRODUCT_ID`),
  KEY `IXS_BASKET_PRODUCT_PRICE_ID` (`PRODUCT_PRICE_ID`),
  KEY `IXS_SBAS_XML_ID` (`PRODUCT_XML_ID`,`CATALOG_XML_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=713 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_basket_props`
--

DROP TABLE IF EXISTS `b_sale_basket_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_basket_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BASKET_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `VALUE` varchar(255) DEFAULT NULL,
  `CODE` varchar(255) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`),
  KEY `IXS_BASKET_PROPS_BASKET` (`BASKET_ID`),
  KEY `IXS_BASKET_PROPS_CODE` (`CODE`)
) ENGINE=MyISAM AUTO_INCREMENT=3211 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_delivery`
--

DROP TABLE IF EXISTS `b_sale_delivery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_delivery` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `LID` char(2) NOT NULL,
  `PERIOD_FROM` int(11) DEFAULT NULL,
  `PERIOD_TO` int(11) DEFAULT NULL,
  `PERIOD_TYPE` char(1) DEFAULT NULL,
  `WEIGHT_FROM` int(11) DEFAULT NULL,
  `WEIGHT_TO` int(11) DEFAULT NULL,
  `ORDER_PRICE_FROM` decimal(18,2) DEFAULT NULL,
  `ORDER_PRICE_TO` decimal(18,2) DEFAULT NULL,
  `ORDER_CURRENCY` char(3) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `PRICE` decimal(18,2) NOT NULL,
  `CURRENCY` char(3) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `DESCRIPTION` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_DELIVERY_LID` (`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_delivery2location`
--

DROP TABLE IF EXISTS `b_sale_delivery2location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_delivery2location` (
  `DELIVERY_ID` int(11) NOT NULL,
  `LOCATION_ID` int(11) NOT NULL,
  `LOCATION_TYPE` char(1) NOT NULL DEFAULT 'L',
  PRIMARY KEY (`DELIVERY_ID`,`LOCATION_ID`,`LOCATION_TYPE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_delivery_handler`
--

DROP TABLE IF EXISTS `b_sale_delivery_handler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_delivery_handler` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` char(2) DEFAULT '',
  `ACTIVE` char(1) DEFAULT 'Y',
  `HID` varchar(50) NOT NULL DEFAULT '',
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `SORT` int(11) NOT NULL DEFAULT '100',
  `DESCRIPTION` text,
  `HANDLER` varchar(255) NOT NULL DEFAULT '',
  `SETTINGS` text,
  `PROFILES` text,
  `TAX_RATE` double DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_HID` (`HID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_discount`
--

DROP TABLE IF EXISTS `b_sale_discount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_discount` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` char(2) NOT NULL,
  `PRICE_FROM` decimal(18,2) DEFAULT NULL,
  `PRICE_TO` decimal(18,2) DEFAULT NULL,
  `CURRENCY` char(3) DEFAULT NULL,
  `DISCOUNT_VALUE` decimal(18,2) NOT NULL,
  `DISCOUNT_TYPE` char(1) NOT NULL DEFAULT 'P',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '100',
  `ACTIVE_FROM` datetime DEFAULT NULL,
  `ACTIVE_TO` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_DISCOUNT_LID` (`LID`),
  KEY `IX_SSD_ACTIVE_DATE` (`ACTIVE_FROM`,`ACTIVE_TO`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_export`
--

DROP TABLE IF EXISTS `b_sale_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_export` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PERSON_TYPE_ID` int(11) NOT NULL,
  `VARS` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_fuser`
--

DROP TABLE IF EXISTS `b_sale_fuser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_fuser` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DATE_INSERT` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_USER_ID` (`USER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=7731 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_lang`
--

DROP TABLE IF EXISTS `b_sale_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_lang` (
  `LID` char(2) NOT NULL,
  `CURRENCY` char(3) NOT NULL,
  PRIMARY KEY (`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location`
--

DROP TABLE IF EXISTS `b_sale_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `COUNTRY_ID` int(11) NOT NULL,
  `CITY_ID` int(11) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`),
  KEY `IXS_LOCATION_COUNTRY_ID` (`COUNTRY_ID`),
  KEY `IXS_LOCATION_CITY_ID` (`CITY_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location2location_group`
--

DROP TABLE IF EXISTS `b_sale_location2location_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location2location_group` (
  `LOCATION_ID` int(11) NOT NULL,
  `LOCATION_GROUP_ID` int(11) NOT NULL,
  PRIMARY KEY (`LOCATION_ID`,`LOCATION_GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location_city`
--

DROP TABLE IF EXISTS `b_sale_location_city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location_city` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) NOT NULL,
  `SHORT_NAME` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location_city_lang`
--

DROP TABLE IF EXISTS `b_sale_location_city_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location_city_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CITY_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `SHORT_NAME` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IXS_LOCAT_CITY_LID` (`CITY_ID`,`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location_country`
--

DROP TABLE IF EXISTS `b_sale_location_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location_country` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) NOT NULL,
  `SHORT_NAME` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location_country_lang`
--

DROP TABLE IF EXISTS `b_sale_location_country_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location_country_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `COUNTRY_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `SHORT_NAME` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IXS_LOCAT_CNTR_LID` (`COUNTRY_ID`,`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location_group`
--

DROP TABLE IF EXISTS `b_sale_location_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SORT` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location_group_lang`
--

DROP TABLE IF EXISTS `b_sale_location_group_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location_group_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LOCATION_GROUP_ID` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(250) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_location_group_lid` (`LOCATION_GROUP_ID`,`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_location_zip`
--

DROP TABLE IF EXISTS `b_sale_location_zip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_location_zip` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LOCATION_ID` int(11) NOT NULL DEFAULT '0',
  `ZIP` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `IX_LOCATION_ID` (`LOCATION_ID`),
  KEY `IX_ZIP` (`ZIP`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_order`
--

DROP TABLE IF EXISTS `b_sale_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_order` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` char(2) NOT NULL,
  `PERSON_TYPE_ID` int(11) NOT NULL,
  `PAYED` char(1) NOT NULL DEFAULT 'N',
  `DATE_PAYED` datetime DEFAULT NULL,
  `EMP_PAYED_ID` int(11) DEFAULT NULL,
  `CANCELED` char(1) NOT NULL DEFAULT 'N',
  `DATE_CANCELED` datetime DEFAULT NULL,
  `EMP_CANCELED_ID` int(11) DEFAULT NULL,
  `REASON_CANCELED` varchar(255) DEFAULT NULL,
  `STATUS_ID` char(1) NOT NULL DEFAULT 'N',
  `DATE_STATUS` datetime NOT NULL,
  `EMP_STATUS_ID` int(11) DEFAULT NULL,
  `PRICE_DELIVERY` decimal(18,2) NOT NULL,
  `ALLOW_DELIVERY` char(1) NOT NULL DEFAULT 'N',
  `DATE_ALLOW_DELIVERY` datetime DEFAULT NULL,
  `EMP_ALLOW_DELIVERY_ID` int(11) DEFAULT NULL,
  `PRICE` decimal(18,2) NOT NULL,
  `CURRENCY` char(3) NOT NULL,
  `DISCOUNT_VALUE` decimal(18,2) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `PAY_SYSTEM_ID` int(11) DEFAULT NULL,
  `DELIVERY_ID` varchar(50) DEFAULT NULL,
  `DATE_INSERT` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `USER_DESCRIPTION` varchar(250) DEFAULT NULL,
  `ADDITIONAL_INFO` varchar(255) DEFAULT NULL,
  `PS_STATUS` char(1) DEFAULT NULL,
  `PS_STATUS_CODE` char(5) DEFAULT NULL,
  `PS_STATUS_DESCRIPTION` varchar(250) DEFAULT NULL,
  `PS_STATUS_MESSAGE` varchar(250) DEFAULT NULL,
  `PS_SUM` decimal(18,2) DEFAULT NULL,
  `PS_CURRENCY` char(3) DEFAULT NULL,
  `PS_RESPONSE_DATE` datetime DEFAULT NULL,
  `COMMENTS` text,
  `TAX_VALUE` decimal(18,2) NOT NULL DEFAULT '0.00',
  `STAT_GID` varchar(255) DEFAULT NULL,
  `SUM_PAID` decimal(18,2) NOT NULL DEFAULT '0.00',
  `RECURRING_ID` int(11) DEFAULT NULL,
  `PAY_VOUCHER_NUM` varchar(20) DEFAULT NULL,
  `PAY_VOUCHER_DATE` date DEFAULT NULL,
  `LOCKED_BY` int(11) DEFAULT NULL,
  `DATE_LOCK` datetime DEFAULT NULL,
  `RECOUNT_FLAG` char(1) NOT NULL DEFAULT 'Y',
  `AFFILIATE_ID` int(11) DEFAULT NULL,
  `DELIVERY_DOC_NUM` varchar(20) DEFAULT NULL,
  `DELIVERY_DOC_DATE` date DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_ORDER_USER_ID` (`USER_ID`),
  KEY `IXS_ORDER_PERSON_TYPE_ID` (`PERSON_TYPE_ID`),
  KEY `IXS_ORDER_PAYED` (`PAYED`),
  KEY `IXS_ORDER_STATUS_ID` (`STATUS_ID`),
  KEY `IXS_ORDER_REC_ID` (`RECURRING_ID`),
  KEY `IX_SOO_AFFILIATE_ID` (`AFFILIATE_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_order_flags2group`
--

DROP TABLE IF EXISTS `b_sale_order_flags2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_order_flags2group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GROUP_ID` int(11) NOT NULL,
  `ORDER_FLAG` char(1) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_sale_ordfla2group` (`GROUP_ID`,`ORDER_FLAG`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_order_props`
--

DROP TABLE IF EXISTS `b_sale_order_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_order_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PERSON_TYPE_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `TYPE` varchar(20) NOT NULL,
  `REQUIED` char(1) NOT NULL DEFAULT 'N',
  `DEFAULT_VALUE` varchar(255) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `USER_PROPS` char(1) NOT NULL DEFAULT 'N',
  `IS_LOCATION` char(1) NOT NULL DEFAULT 'N',
  `PROPS_GROUP_ID` int(11) NOT NULL,
  `SIZE1` int(11) NOT NULL DEFAULT '0',
  `SIZE2` int(11) NOT NULL DEFAULT '0',
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `IS_EMAIL` char(1) NOT NULL DEFAULT 'N',
  `IS_PROFILE_NAME` char(1) NOT NULL DEFAULT 'N',
  `IS_PAYER` char(1) NOT NULL DEFAULT 'N',
  `IS_LOCATION4TAX` char(1) NOT NULL DEFAULT 'N',
  `IS_FILTERED` char(1) NOT NULL DEFAULT 'N',
  `CODE` varchar(50) DEFAULT NULL,
  `IS_ZIP` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `IXS_ORDER_PROPS_PERSON_TYPE_ID` (`PERSON_TYPE_ID`),
  KEY `IXS_CODE_OPP` (`CODE`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_order_props_group`
--

DROP TABLE IF EXISTS `b_sale_order_props_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_order_props_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PERSON_TYPE_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`),
  KEY `IXS_ORDER_PROPS_GROUP_PERSON_TYPE_ID` (`PERSON_TYPE_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_order_props_value`
--

DROP TABLE IF EXISTS `b_sale_order_props_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_order_props_value` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ORDER_ID` int(11) NOT NULL,
  `ORDER_PROPS_ID` int(11) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `VALUE` varchar(255) DEFAULT NULL,
  `CODE` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SOPV_ORD_PROP_UNI` (`ORDER_ID`,`ORDER_PROPS_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=311 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_order_props_variant`
--

DROP TABLE IF EXISTS `b_sale_order_props_variant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_order_props_variant` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ORDER_PROPS_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `VALUE` varchar(255) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_ORDER_PROPS_VARIANT_ORDER_PROPS_ID` (`ORDER_PROPS_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_order_tax`
--

DROP TABLE IF EXISTS `b_sale_order_tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_order_tax` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ORDER_ID` int(11) NOT NULL,
  `TAX_NAME` varchar(255) NOT NULL,
  `VALUE` decimal(18,2) DEFAULT NULL,
  `VALUE_MONEY` decimal(18,2) NOT NULL,
  `APPLY_ORDER` int(11) NOT NULL,
  `CODE` varchar(50) DEFAULT NULL,
  `IS_PERCENT` char(1) NOT NULL DEFAULT 'Y',
  `IS_IN_PRICE` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `ixs_sot_order_id` (`ORDER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_pay_system`
--

DROP TABLE IF EXISTS `b_sale_pay_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_pay_system` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` char(2) NOT NULL,
  `CURRENCY` char(3) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '100',
  `DESCRIPTION` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_PAY_SYSTEM_LID` (`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_pay_system_action`
--

DROP TABLE IF EXISTS `b_sale_pay_system_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_pay_system_action` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PAY_SYSTEM_ID` int(11) NOT NULL,
  `PERSON_TYPE_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `ACTION_FILE` varchar(255) DEFAULT NULL,
  `RESULT_FILE` varchar(255) DEFAULT NULL,
  `NEW_WINDOW` char(1) NOT NULL DEFAULT 'Y',
  `PARAMS` text,
  `HAVE_PAYMENT` char(1) NOT NULL DEFAULT 'N',
  `HAVE_ACTION` char(1) NOT NULL DEFAULT 'N',
  `HAVE_RESULT` char(1) NOT NULL DEFAULT 'N',
  `HAVE_PREPAY` char(1) NOT NULL DEFAULT 'N',
  `HAVE_RESULT_RECEIVE` char(1) NOT NULL DEFAULT 'N',
  `ENCODING` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SPSA_PSPT_UNI` (`PAY_SYSTEM_ID`,`PERSON_TYPE_ID`),
  KEY `IXS_PAY_SYSTEM_ACTION_PERSON_TYPE_ID` (`PERSON_TYPE_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_person_type`
--

DROP TABLE IF EXISTS `b_sale_person_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_person_type` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '150',
  PRIMARY KEY (`ID`),
  KEY `IXS_PERSON_TYPE_LID` (`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_product2product`
--

DROP TABLE IF EXISTS `b_sale_product2product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_product2product` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PRODUCT_ID` int(11) NOT NULL,
  `PARENT_PRODUCT_ID` int(11) NOT NULL,
  `CNT` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_PRODUCT2PRODUCT_PRODUCT_ID` (`PRODUCT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_recurring`
--

DROP TABLE IF EXISTS `b_sale_recurring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_recurring` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MODULE` varchar(100) DEFAULT NULL,
  `PRODUCT_ID` int(11) DEFAULT NULL,
  `PRODUCT_NAME` varchar(255) DEFAULT NULL,
  `PRODUCT_URL` varchar(255) DEFAULT NULL,
  `PRODUCT_PRICE_ID` int(11) DEFAULT NULL,
  `PRICE_TYPE` char(1) NOT NULL DEFAULT 'R',
  `RECUR_SCHEME_TYPE` char(1) NOT NULL DEFAULT 'M',
  `RECUR_SCHEME_LENGTH` int(11) NOT NULL DEFAULT '0',
  `WITHOUT_ORDER` char(1) NOT NULL DEFAULT 'N',
  `PRICE` decimal(10,0) NOT NULL DEFAULT '0',
  `CURRENCY` char(3) DEFAULT NULL,
  `CANCELED` char(1) NOT NULL DEFAULT 'N',
  `DATE_CANCELED` datetime DEFAULT NULL,
  `PRIOR_DATE` datetime DEFAULT NULL,
  `NEXT_DATE` datetime NOT NULL,
  `CALLBACK_FUNC` varchar(100) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `CANCELED_REASON` varchar(255) DEFAULT NULL,
  `ORDER_ID` int(11) NOT NULL,
  `REMAINING_ATTEMPTS` int(11) NOT NULL DEFAULT '0',
  `SUCCESS_PAYMENT` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `IX_S_R_USER_ID` (`USER_ID`),
  KEY `IX_S_R_NEXT_DATE` (`NEXT_DATE`,`CANCELED`,`REMAINING_ATTEMPTS`),
  KEY `IX_S_R_PRODUCT_ID` (`MODULE`,`PRODUCT_ID`,`PRODUCT_PRICE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_site2group`
--

DROP TABLE IF EXISTS `b_sale_site2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_site2group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GROUP_ID` int(11) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_sale_site2group` (`GROUP_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_status`
--

DROP TABLE IF EXISTS `b_sale_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_status` (
  `ID` char(1) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_status2group`
--

DROP TABLE IF EXISTS `b_sale_status2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_status2group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GROUP_ID` int(11) NOT NULL,
  `STATUS_ID` char(1) NOT NULL,
  `PERM_VIEW` char(1) NOT NULL DEFAULT 'N',
  `PERM_CANCEL` char(1) NOT NULL DEFAULT 'N',
  `PERM_DELIVERY` char(1) NOT NULL DEFAULT 'N',
  `PERM_PAYMENT` char(1) NOT NULL DEFAULT 'N',
  `PERM_STATUS` char(1) NOT NULL DEFAULT 'N',
  `PERM_UPDATE` char(1) NOT NULL DEFAULT 'N',
  `PERM_DELETE` char(1) NOT NULL DEFAULT 'N',
  `PERM_STATUS_FROM` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_sale_s2g_ix1` (`GROUP_ID`,`STATUS_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_status_lang`
--

DROP TABLE IF EXISTS `b_sale_status_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_status_lang` (
  `STATUS_ID` char(1) NOT NULL,
  `LID` char(2) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `DESCRIPTION` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`STATUS_ID`,`LID`),
  UNIQUE KEY `ixs_status_lang_status_id` (`STATUS_ID`,`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_tax`
--

DROP TABLE IF EXISTS `b_sale_tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_tax` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LID` char(2) NOT NULL,
  `NAME` varchar(250) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TIMESTAMP_X` datetime NOT NULL,
  `CODE` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `itax_lid` (`LID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_tax2location`
--

DROP TABLE IF EXISTS `b_sale_tax2location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_tax2location` (
  `TAX_RATE_ID` int(11) NOT NULL,
  `LOCATION_ID` int(11) NOT NULL,
  `LOCATION_TYPE` char(1) NOT NULL DEFAULT 'L',
  PRIMARY KEY (`TAX_RATE_ID`,`LOCATION_ID`,`LOCATION_TYPE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_tax_exempt2group`
--

DROP TABLE IF EXISTS `b_sale_tax_exempt2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_tax_exempt2group` (
  `GROUP_ID` int(11) NOT NULL,
  `TAX_ID` int(11) NOT NULL,
  PRIMARY KEY (`GROUP_ID`,`TAX_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_tax_rate`
--

DROP TABLE IF EXISTS `b_sale_tax_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_tax_rate` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TAX_ID` int(11) NOT NULL,
  `PERSON_TYPE_ID` int(11) DEFAULT NULL,
  `VALUE` decimal(18,4) NOT NULL,
  `CURRENCY` char(3) DEFAULT NULL,
  `IS_PERCENT` char(1) NOT NULL DEFAULT 'Y',
  `IS_IN_PRICE` char(1) NOT NULL DEFAULT 'N',
  `APPLY_ORDER` int(11) NOT NULL DEFAULT '100',
  `TIMESTAMP_X` datetime NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `itax_pers_type` (`PERSON_TYPE_ID`),
  KEY `itax_lid` (`TAX_ID`),
  KEY `itax_inprice` (`IS_IN_PRICE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_user_account`
--

DROP TABLE IF EXISTS `b_sale_user_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_user_account` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CURRENT_BUDGET` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `CURRENCY` char(3) NOT NULL,
  `LOCKED` char(1) NOT NULL DEFAULT 'N',
  `DATE_LOCKED` datetime DEFAULT NULL,
  `NOTES` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_S_U_USER_ID` (`USER_ID`,`CURRENCY`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_user_cards`
--

DROP TABLE IF EXISTS `b_sale_user_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_user_cards` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '100',
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `PAY_SYSTEM_ACTION_ID` int(11) NOT NULL,
  `CURRENCY` char(3) DEFAULT NULL,
  `CARD_TYPE` varchar(20) NOT NULL,
  `CARD_NUM` text NOT NULL,
  `CARD_CODE` varchar(5) DEFAULT NULL,
  `CARD_EXP_MONTH` int(11) NOT NULL,
  `CARD_EXP_YEAR` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `SUM_MIN` decimal(18,4) DEFAULT NULL,
  `SUM_MAX` decimal(18,4) DEFAULT NULL,
  `SUM_CURRENCY` char(3) DEFAULT NULL,
  `LAST_STATUS` char(1) DEFAULT NULL,
  `LAST_STATUS_CODE` varchar(5) DEFAULT NULL,
  `LAST_STATUS_DESCRIPTION` varchar(250) DEFAULT NULL,
  `LAST_STATUS_MESSAGE` varchar(255) DEFAULT NULL,
  `LAST_SUM` decimal(18,4) DEFAULT NULL,
  `LAST_CURRENCY` char(3) DEFAULT NULL,
  `LAST_DATE` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_S_U_C_USER_ID` (`USER_ID`,`ACTIVE`,`CURRENCY`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_user_props`
--

DROP TABLE IF EXISTS `b_sale_user_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_user_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `PERSON_TYPE_ID` int(11) NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_USER_PROPS_USER_ID` (`USER_ID`),
  KEY `IXS_USER_PROPS_PERSON_TYPE_ID` (`PERSON_TYPE_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_user_props_value`
--

DROP TABLE IF EXISTS `b_sale_user_props_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_user_props_value` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_PROPS_ID` int(11) NOT NULL,
  `ORDER_PROPS_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `VALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IXS_USER_PROPS_VALUE_USER_PROPS_ID` (`USER_PROPS_ID`),
  KEY `IXS_USER_PROPS_VALUE_ORDER_PROPS_ID` (`ORDER_PROPS_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sale_user_transact`
--

DROP TABLE IF EXISTS `b_sale_user_transact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sale_user_transact` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `TRANSACT_DATE` datetime NOT NULL,
  `AMOUNT` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `CURRENCY` char(3) NOT NULL,
  `DEBIT` char(1) NOT NULL DEFAULT 'N',
  `ORDER_ID` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) NOT NULL,
  `NOTES` text,
  `EMPLOYEE_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_S_U_T_USER_ID` (`USER_ID`),
  KEY `IX_S_U_T_USER_ID_CURRENCY` (`USER_ID`,`CURRENCY`),
  KEY `IX_S_U_T_ORDER_ID` (`ORDER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_content`
--

DROP TABLE IF EXISTS `b_search_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_content` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DATE_CHANGE` datetime NOT NULL,
  `MODULE_ID` varchar(50) NOT NULL,
  `ITEM_ID` varchar(255) NOT NULL,
  `LID` char(2) NOT NULL,
  `CUSTOM_RANK` int(11) NOT NULL DEFAULT '0',
  `URL` text,
  `TITLE` text,
  `BODY` text,
  `TAGS` text,
  `SEARCHABLE_CONTENT` longtext,
  `PARAM1` text,
  `PARAM2` text,
  `UPD` varchar(32) DEFAULT NULL,
  `DATE_FROM` datetime DEFAULT NULL,
  `DATE_TO` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_B_SEARCH_CONTENT` (`MODULE_ID`,`ITEM_ID`),
  KEY `IX_B_SEARCH_CONTENT_1` (`MODULE_ID`,`PARAM1`(50),`PARAM2`(50))
) ENGINE=MyISAM AUTO_INCREMENT=9329 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_content_freq`
--

DROP TABLE IF EXISTS `b_search_content_freq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_content_freq` (
  `STEM` varchar(50) CHARACTER SET cp1251 COLLATE cp1251_bin NOT NULL DEFAULT '',
  `LANGUAGE_ID` char(2) NOT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `FREQ` float NOT NULL,
  `TF` float DEFAULT NULL,
  UNIQUE KEY `UX_B_SEARCH_CONTENT_FREQ` (`STEM`,`LANGUAGE_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_content_param`
--

DROP TABLE IF EXISTS `b_search_content_param`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_content_param` (
  `SEARCH_CONTENT_ID` int(11) NOT NULL,
  `PARAM_NAME` varchar(100) NOT NULL,
  `PARAM_VALUE` varchar(100) NOT NULL,
  KEY `IX_B_SEARCH_CONTENT_PARAM` (`SEARCH_CONTENT_ID`,`PARAM_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_content_right`
--

DROP TABLE IF EXISTS `b_search_content_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_content_right` (
  `SEARCH_CONTENT_ID` int(11) NOT NULL,
  `GROUP_CODE` varchar(100) NOT NULL,
  UNIQUE KEY `UX_B_SEARCH_CONTENT_RIGHT` (`SEARCH_CONTENT_ID`,`GROUP_CODE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_content_site`
--

DROP TABLE IF EXISTS `b_search_content_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_content_site` (
  `SEARCH_CONTENT_ID` int(18) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  `URL` text,
  PRIMARY KEY (`SEARCH_CONTENT_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_content_stem`
--

DROP TABLE IF EXISTS `b_search_content_stem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_content_stem` (
  `SEARCH_CONTENT_ID` int(11) NOT NULL,
  `LANGUAGE_ID` char(2) NOT NULL,
  `STEM` varchar(50) CHARACTER SET cp1251 COLLATE cp1251_bin NOT NULL,
  `TF` float NOT NULL,
  UNIQUE KEY `UX_B_SEARCH_CONTENT_STEM` (`STEM`,`LANGUAGE_ID`,`TF`,`SEARCH_CONTENT_ID`),
  KEY `IND_B_SEARCH_CONTENT_STEM` (`SEARCH_CONTENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 DELAY_KEY_WRITE=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_content_title`
--

DROP TABLE IF EXISTS `b_search_content_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_content_title` (
  `SEARCH_CONTENT_ID` int(11) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  `WORD` varchar(100) NOT NULL,
  `POS` int(11) NOT NULL,
  UNIQUE KEY `UX_B_SEARCH_CONTENT_TITLE` (`SITE_ID`,`WORD`,`SEARCH_CONTENT_ID`,`POS`),
  KEY `IND_B_SEARCH_CONTENT_TITLE` (`SEARCH_CONTENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 DELAY_KEY_WRITE=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_custom_rank`
--

DROP TABLE IF EXISTS `b_search_custom_rank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_custom_rank` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `MODULE_ID` varchar(200) NOT NULL,
  `PARAM1` text,
  `PARAM2` text,
  `ITEM_ID` varchar(255) DEFAULT NULL,
  `RANK` int(11) NOT NULL DEFAULT '0',
  `APPLIED` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `IND_B_SEARCH_CUSTOM_RANK` (`SITE_ID`,`MODULE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_phrase`
--

DROP TABLE IF EXISTS `b_search_phrase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_phrase` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  `RESULT_COUNT` int(11) NOT NULL,
  `PAGES` int(11) NOT NULL,
  `SESSION_ID` varchar(32) NOT NULL,
  `PHRASE` varchar(250) DEFAULT NULL,
  `TAGS` varchar(250) DEFAULT NULL,
  `URL_TO` text,
  `URL_TO_404` char(1) DEFAULT NULL,
  `URL_TO_SITE_ID` char(2) DEFAULT NULL,
  `STAT_SESS_ID` int(18) DEFAULT NULL,
  `EVENT1` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IND_PK_B_SEARCH_PHRASE_SESS_PH` (`SESSION_ID`,`PHRASE`(50)),
  KEY `IND_PK_B_SEARCH_PHRASE_SESS_TG` (`SESSION_ID`,`TAGS`(50)),
  KEY `IND_PK_B_SEARCH_PHRASE_TIME` (`TIMESTAMP_X`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_suggest`
--

DROP TABLE IF EXISTS `b_search_suggest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_suggest` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `FILTER_MD5` varchar(32) NOT NULL,
  `PHRASE` varchar(250) NOT NULL,
  `RATE` float NOT NULL,
  `TIMESTAMP_X` datetime NOT NULL,
  `RESULT_COUNT` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IND_B_SEARCH_SUGGEST` (`FILTER_MD5`,`PHRASE`(50),`RATE`),
  KEY `IND_B_SEARCH_SUGGEST_PHRASE` (`PHRASE`(50),`RATE`),
  KEY `IND_B_SEARCH_SUGGEST_TIME` (`TIMESTAMP_X`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_tags`
--

DROP TABLE IF EXISTS `b_search_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_tags` (
  `SEARCH_CONTENT_ID` int(11) NOT NULL,
  `SITE_ID` char(2) NOT NULL,
  `NAME` varchar(255) CHARACTER SET cp1251 COLLATE cp1251_bin NOT NULL,
  PRIMARY KEY (`SEARCH_CONTENT_ID`,`SITE_ID`,`NAME`),
  KEY `IX_B_SEARCH_TAGS_0` (`NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 DELAY_KEY_WRITE=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_search_user_right`
--

DROP TABLE IF EXISTS `b_search_user_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_search_user_right` (
  `USER_ID` int(11) NOT NULL,
  `GROUP_CODE` varchar(100) NOT NULL,
  UNIQUE KEY `UX_B_SEARCH_USER_RIGHT` (`USER_ID`,`GROUP_CODE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_filter_mask`
--

DROP TABLE IF EXISTS `b_sec_filter_mask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_filter_mask` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SORT` int(11) NOT NULL DEFAULT '10',
  `SITE_ID` char(2) DEFAULT NULL,
  `FILTER_MASK` varchar(250) DEFAULT NULL,
  `LIKE_MASK` varchar(250) DEFAULT NULL,
  `PREG_MASK` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_iprule`
--

DROP TABLE IF EXISTS `b_sec_iprule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_iprule` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RULE_TYPE` char(1) NOT NULL DEFAULT 'M',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `ADMIN_SECTION` char(1) NOT NULL DEFAULT 'Y',
  `SITE_ID` char(2) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `ACTIVE_FROM` datetime DEFAULT NULL,
  `ACTIVE_FROM_TIMESTAMP` int(11) DEFAULT NULL,
  `ACTIVE_TO` datetime DEFAULT NULL,
  `ACTIVE_TO_TIMESTAMP` int(11) DEFAULT NULL,
  `NAME` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_b_sec_iprule_active_to` (`ACTIVE_TO`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_iprule_excl_ip`
--

DROP TABLE IF EXISTS `b_sec_iprule_excl_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_iprule_excl_ip` (
  `IPRULE_ID` int(11) NOT NULL,
  `RULE_IP` varchar(50) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `IP_START` bigint(18) DEFAULT NULL,
  `IP_END` bigint(18) DEFAULT NULL,
  PRIMARY KEY (`IPRULE_ID`,`RULE_IP`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_iprule_excl_mask`
--

DROP TABLE IF EXISTS `b_sec_iprule_excl_mask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_iprule_excl_mask` (
  `IPRULE_ID` int(11) NOT NULL,
  `RULE_MASK` varchar(250) NOT NULL DEFAULT '',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `LIKE_MASK` varchar(250) DEFAULT NULL,
  `PREG_MASK` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`IPRULE_ID`,`RULE_MASK`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_iprule_incl_ip`
--

DROP TABLE IF EXISTS `b_sec_iprule_incl_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_iprule_incl_ip` (
  `IPRULE_ID` int(11) NOT NULL,
  `RULE_IP` varchar(50) NOT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `IP_START` bigint(18) DEFAULT NULL,
  `IP_END` bigint(18) DEFAULT NULL,
  PRIMARY KEY (`IPRULE_ID`,`RULE_IP`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_iprule_incl_mask`
--

DROP TABLE IF EXISTS `b_sec_iprule_incl_mask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_iprule_incl_mask` (
  `IPRULE_ID` int(11) NOT NULL,
  `RULE_MASK` varchar(250) NOT NULL DEFAULT '',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `LIKE_MASK` varchar(250) DEFAULT NULL,
  `PREG_MASK` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`IPRULE_ID`,`RULE_MASK`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_redirect_url`
--

DROP TABLE IF EXISTS `b_sec_redirect_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_redirect_url` (
  `IS_SYSTEM` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `URL` varchar(250) NOT NULL,
  `PARAMETER_NAME` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_session`
--

DROP TABLE IF EXISTS `b_sec_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_session` (
  `SESSION_ID` varchar(250) NOT NULL,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SESSION_DATA` longtext,
  PRIMARY KEY (`SESSION_ID`),
  KEY `ix_b_sec_session_time` (`TIMESTAMP_X`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_user`
--

DROP TABLE IF EXISTS `b_sec_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_user` (
  `USER_ID` int(11) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'N',
  `SECRET` varchar(50) NOT NULL,
  `COUNTER` int(11) NOT NULL,
  PRIMARY KEY (`USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_virus`
--

DROP TABLE IF EXISTS `b_sec_virus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_virus` (
  `ID` varchar(32) NOT NULL,
  `TIMESTAMP_X` datetime NOT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `SENT` char(1) NOT NULL DEFAULT 'N',
  `INFO` longtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sec_white_list`
--

DROP TABLE IF EXISTS `b_sec_white_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sec_white_list` (
  `ID` int(11) NOT NULL,
  `WHITE_SUBSTR` varchar(250) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_seo_keywords`
--

DROP TABLE IF EXISTS `b_seo_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_seo_keywords` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `KEYWORDS` text,
  PRIMARY KEY (`ID`),
  KEY `ix_b_seo_keywords_url` (`URL`,`SITE_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_site_template`
--

DROP TABLE IF EXISTS `b_site_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_site_template` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `CONDITION` varchar(255) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '500',
  `TEMPLATE` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UX_B_SITE_TEMPLATE` (`SITE_ID`,`CONDITION`,`TEMPLATE`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_event_user_view`
--

DROP TABLE IF EXISTS `b_sonet_event_user_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_event_user_view` (
  `ENTITY_TYPE` char(1) NOT NULL DEFAULT 'G',
  `ENTITY_ID` int(11) NOT NULL,
  `EVENT_ID` varchar(50) NOT NULL,
  `USER_ID` int(11) NOT NULL DEFAULT '0',
  `USER_IM_ID` int(11) NOT NULL DEFAULT '0',
  `USER_ANONYMOUS` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ENTITY_TYPE`,`ENTITY_ID`,`EVENT_ID`,`USER_ID`,`USER_IM_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_features`
--

DROP TABLE IF EXISTS `b_sonet_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_features` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ENTITY_TYPE` char(1) NOT NULL DEFAULT 'G',
  `ENTITY_ID` int(11) NOT NULL,
  `FEATURE` varchar(50) NOT NULL,
  `FEATURE_NAME` varchar(250) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_GROUP_FEATURES_1` (`ENTITY_TYPE`,`ENTITY_ID`,`FEATURE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_features2perms`
--

DROP TABLE IF EXISTS `b_sonet_features2perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_features2perms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FEATURE_ID` int(11) NOT NULL,
  `OPERATION_ID` varchar(50) NOT NULL,
  `ROLE` char(1) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_GROUP_FEATURES2PERMS_1` (`FEATURE_ID`,`OPERATION_ID`),
  KEY `IX_SONET_GROUP_FEATURES2PERMS_2` (`FEATURE_ID`,`ROLE`,`OPERATION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_group`
--

DROP TABLE IF EXISTS `b_sonet_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `VISIBLE` char(1) NOT NULL DEFAULT 'Y',
  `OPENED` char(1) NOT NULL DEFAULT 'N',
  `SUBJECT_ID` int(11) NOT NULL,
  `OWNER_ID` int(11) NOT NULL,
  `KEYWORDS` varchar(255) DEFAULT NULL,
  `IMAGE_ID` int(11) DEFAULT NULL,
  `NUMBER_OF_MEMBERS` int(11) NOT NULL DEFAULT '0',
  `INITIATE_PERMS` char(1) NOT NULL DEFAULT 'K',
  `DATE_ACTIVITY` datetime NOT NULL,
  `CLOSED` char(1) NOT NULL DEFAULT 'N',
  `SPAM_PERMS` char(1) NOT NULL DEFAULT 'K',
  PRIMARY KEY (`ID`),
  KEY `IX_SONET_GROUP_1` (`OWNER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_group_subject`
--

DROP TABLE IF EXISTS `b_sonet_group_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_group_subject` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `SORT` int(10) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_log`
--

DROP TABLE IF EXISTS `b_sonet_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ENTITY_TYPE` char(1) NOT NULL DEFAULT 'G',
  `ENTITY_ID` int(11) NOT NULL,
  `EVENT_ID` varchar(50) NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `LOG_DATE` datetime NOT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `TITLE_TEMPLATE` varchar(250) DEFAULT NULL,
  `TITLE` varchar(250) NOT NULL,
  `MESSAGE` text,
  `TEXT_MESSAGE` text,
  `URL` varchar(250) DEFAULT NULL,
  `MODULE_ID` varchar(50) DEFAULT NULL,
  `CALLBACK_FUNC` varchar(250) DEFAULT NULL,
  `EXTERNAL_ID` varchar(250) DEFAULT NULL,
  `PARAMS` text,
  `TMP_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_SONET_LOG_1` (`ENTITY_TYPE`,`ENTITY_ID`,`EVENT_ID`),
  KEY `IX_SONET_LOG_2` (`USER_ID`,`LOG_DATE`,`EVENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_log_events`
--

DROP TABLE IF EXISTS `b_sonet_log_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_log_events` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `ENTITY_TYPE` char(1) NOT NULL DEFAULT 'G',
  `ENTITY_ID` int(11) NOT NULL,
  `ENTITY_CB` char(1) NOT NULL DEFAULT 'N',
  `ENTITY_MY` char(1) NOT NULL DEFAULT 'N',
  `EVENT_ID` varchar(50) NOT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `MAIL_EVENT` char(1) NOT NULL DEFAULT 'N',
  `TRANSPORT` char(1) NOT NULL DEFAULT 'N',
  `VISIBLE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_LOG_EVENTS_3` (`USER_ID`,`ENTITY_TYPE`,`ENTITY_ID`,`ENTITY_CB`,`ENTITY_MY`,`EVENT_ID`),
  KEY `IX_SONET_LOG_EVENTS_1` (`USER_ID`),
  KEY `IX_SONET_LOG_EVENTS_2` (`ENTITY_TYPE`,`ENTITY_ID`,`EVENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_messages`
--

DROP TABLE IF EXISTS `b_sonet_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_messages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FROM_USER_ID` int(11) NOT NULL,
  `TO_USER_ID` int(11) NOT NULL,
  `TITLE` varchar(250) DEFAULT NULL,
  `MESSAGE` text,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_VIEW` datetime DEFAULT NULL,
  `MESSAGE_TYPE` char(1) NOT NULL DEFAULT 'P',
  `FROM_DELETED` char(1) NOT NULL DEFAULT 'N',
  `TO_DELETED` char(1) NOT NULL DEFAULT 'N',
  `SEND_MAIL` char(1) NOT NULL DEFAULT 'N',
  `EMAIL_TEMPLATE` varchar(250) DEFAULT NULL,
  `IS_LOG` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_SONET_MESSAGES_1` (`FROM_USER_ID`),
  KEY `IX_SONET_MESSAGES_2` (`TO_USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_smile`
--

DROP TABLE IF EXISTS `b_sonet_smile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_smile` (
  `ID` smallint(3) NOT NULL AUTO_INCREMENT,
  `SMILE_TYPE` char(1) NOT NULL DEFAULT 'S',
  `TYPING` varchar(100) DEFAULT NULL,
  `IMAGE` varchar(128) NOT NULL,
  `DESCRIPTION` varchar(50) DEFAULT NULL,
  `CLICKABLE` char(1) NOT NULL DEFAULT 'Y',
  `SORT` int(10) NOT NULL DEFAULT '150',
  `IMAGE_WIDTH` int(11) NOT NULL DEFAULT '0',
  `IMAGE_HEIGHT` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_smile_lang`
--

DROP TABLE IF EXISTS `b_sonet_smile_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_smile_lang` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SMILE_ID` int(11) NOT NULL DEFAULT '0',
  `LID` char(2) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_SMILE_K` (`SMILE_ID`,`LID`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_user2group`
--

DROP TABLE IF EXISTS `b_sonet_user2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_user2group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  `ROLE` char(1) NOT NULL DEFAULT 'U',
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `INITIATED_BY_TYPE` char(1) NOT NULL DEFAULT 'U',
  `INITIATED_BY_USER_ID` int(11) NOT NULL,
  `MESSAGE` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_USER2GROUP_1` (`USER_ID`,`GROUP_ID`),
  KEY `IX_SONET_USER2GROUP_2` (`USER_ID`,`GROUP_ID`,`ROLE`),
  KEY `IX_SONET_USER2GROUP_3` (`GROUP_ID`,`USER_ID`,`ROLE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_user_events`
--

DROP TABLE IF EXISTS `b_sonet_user_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_user_events` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `EVENT_ID` varchar(50) NOT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_USER_PERMS_2` (`USER_ID`,`EVENT_ID`),
  KEY `IX_SONET_USER_PERMS_1` (`USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_user_perms`
--

DROP TABLE IF EXISTS `b_sonet_user_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_user_perms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `OPERATION_ID` varchar(50) NOT NULL,
  `RELATION_TYPE` char(1) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_USER_PERMS_2` (`USER_ID`,`OPERATION_ID`),
  KEY `IX_SONET_USER_PERMS_1` (`USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sonet_user_relations`
--

DROP TABLE IF EXISTS `b_sonet_user_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sonet_user_relations` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FIRST_USER_ID` int(11) NOT NULL,
  `SECOND_USER_ID` int(11) NOT NULL,
  `RELATION` char(1) NOT NULL DEFAULT 'N',
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `MESSAGE` text,
  `INITIATED_BY` char(1) NOT NULL DEFAULT 'F',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SONET_RELATIONS_1` (`FIRST_USER_ID`,`SECOND_USER_ID`),
  KEY `IX_SONET_RELATIONS_2` (`FIRST_USER_ID`,`SECOND_USER_ID`,`RELATION`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_adv`
--

DROP TABLE IF EXISTS `b_stat_adv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_adv` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `REFERER1` varchar(255) DEFAULT NULL,
  `REFERER2` varchar(255) DEFAULT NULL,
  `COST` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `REVENUE` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `EVENTS_VIEW` varchar(255) DEFAULT NULL,
  `GUESTS` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `FAVORITES` int(18) NOT NULL DEFAULT '0',
  `C_HOSTS` int(18) NOT NULL DEFAULT '0',
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `DATE_FIRST` datetime DEFAULT NULL,
  `DATE_LAST` datetime DEFAULT NULL,
  `GUESTS_BACK` int(18) NOT NULL DEFAULT '0',
  `FAVORITES_BACK` int(18) NOT NULL DEFAULT '0',
  `HOSTS_BACK` int(18) NOT NULL DEFAULT '0',
  `SESSIONS_BACK` int(18) NOT NULL DEFAULT '0',
  `HITS_BACK` int(18) NOT NULL DEFAULT '0',
  `DESCRIPTION` text,
  `PRIORITY` int(18) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`),
  KEY `IX_REFERER1` (`REFERER1`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_adv_day`
--

DROP TABLE IF EXISTS `b_stat_adv_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_adv_day` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ADV_ID` int(18) NOT NULL DEFAULT '0',
  `DATE_STAT` date DEFAULT NULL,
  `GUESTS` int(18) NOT NULL DEFAULT '0',
  `GUESTS_DAY` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `FAVORITES` int(18) NOT NULL DEFAULT '0',
  `C_HOSTS` int(18) NOT NULL DEFAULT '0',
  `C_HOSTS_DAY` int(18) NOT NULL DEFAULT '0',
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `GUESTS_BACK` int(18) NOT NULL DEFAULT '0',
  `GUESTS_DAY_BACK` int(18) NOT NULL DEFAULT '0',
  `FAVORITES_BACK` int(18) NOT NULL DEFAULT '0',
  `HOSTS_BACK` int(18) NOT NULL DEFAULT '0',
  `HOSTS_DAY_BACK` int(18) NOT NULL DEFAULT '0',
  `SESSIONS_BACK` int(18) NOT NULL DEFAULT '0',
  `HITS_BACK` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_ADV_ID_DATE_STAT` (`ADV_ID`,`DATE_STAT`),
  KEY `IX_DATE_STAT` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_adv_event`
--

DROP TABLE IF EXISTS `b_stat_adv_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_adv_event` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ADV_ID` int(18) DEFAULT '0',
  `EVENT_ID` int(18) DEFAULT '0',
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `COUNTER_BACK` int(18) NOT NULL DEFAULT '0',
  `MONEY` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `MONEY_BACK` decimal(18,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`ID`),
  KEY `IX_ADV_EVENT_ID` (`ADV_ID`,`EVENT_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_adv_event_day`
--

DROP TABLE IF EXISTS `b_stat_adv_event_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_adv_event_day` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ADV_ID` int(18) DEFAULT '0',
  `EVENT_ID` int(18) DEFAULT '0',
  `DATE_STAT` date DEFAULT NULL,
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `COUNTER_BACK` int(18) NOT NULL DEFAULT '0',
  `MONEY` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `MONEY_BACK` decimal(18,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`ID`),
  KEY `IX_ADV_ID_EVENT_ID_DATE_STAT` (`ADV_ID`,`EVENT_ID`,`DATE_STAT`),
  KEY `IX_DATE_STAT` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_adv_guest`
--

DROP TABLE IF EXISTS `b_stat_adv_guest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_adv_guest` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ADV_ID` int(11) NOT NULL DEFAULT '0',
  `BACK` char(1) NOT NULL DEFAULT 'N',
  `GUEST_ID` int(11) NOT NULL DEFAULT '0',
  `DATE_GUEST_HIT` datetime DEFAULT NULL,
  `DATE_HOST_HIT` datetime DEFAULT NULL,
  `SESSION_ID` int(11) NOT NULL DEFAULT '0',
  `IP` varchar(15) DEFAULT NULL,
  `IP_NUMBER` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_ADV_ID_GUEST` (`ADV_ID`,`GUEST_ID`),
  KEY `IX_ADV_ID_IP_NUMBER` (`ADV_ID`,`IP_NUMBER`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_adv_page`
--

DROP TABLE IF EXISTS `b_stat_adv_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_adv_page` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ADV_ID` int(18) NOT NULL DEFAULT '0',
  `PAGE` varchar(255) NOT NULL,
  `C_TYPE` varchar(5) NOT NULL DEFAULT 'TO',
  PRIMARY KEY (`ID`),
  KEY `IX_ADV_ID_TYPE` (`ADV_ID`,`C_TYPE`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_adv_searcher`
--

DROP TABLE IF EXISTS `b_stat_adv_searcher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_adv_searcher` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ADV_ID` int(18) NOT NULL,
  `SEARCHER_ID` int(18) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_browser`
--

DROP TABLE IF EXISTS `b_stat_browser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_browser` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `USER_AGENT` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_city`
--

DROP TABLE IF EXISTS `b_stat_city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_city` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `COUNTRY_ID` char(2) NOT NULL,
  `REGION` varchar(200) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `UX_B_STAT_CITY` (`COUNTRY_ID`,`REGION`(50),`NAME`(50)),
  KEY `IX_B_STAT_CITY_XML_ID` (`XML_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_city_day`
--

DROP TABLE IF EXISTS `b_stat_city_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_city_day` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CITY_ID` int(18) NOT NULL,
  `DATE_STAT` date NOT NULL,
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_B_STAT_CITY_DAY_1` (`CITY_ID`,`DATE_STAT`),
  KEY `IX_B_STAT_CITY_DAY_2` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=570 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_city_ip`
--

DROP TABLE IF EXISTS `b_stat_city_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_city_ip` (
  `START_IP` bigint(18) NOT NULL,
  `END_IP` bigint(18) NOT NULL,
  `COUNTRY_ID` char(2) NOT NULL,
  `CITY_ID` int(18) NOT NULL,
  PRIMARY KEY (`START_IP`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_country`
--

DROP TABLE IF EXISTS `b_stat_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_country` (
  `ID` char(2) NOT NULL,
  `SHORT_NAME` char(3) DEFAULT NULL,
  `NAME` varchar(50) DEFAULT NULL,
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_country_day`
--

DROP TABLE IF EXISTS `b_stat_country_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_country_day` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `COUNTRY_ID` char(2) NOT NULL,
  `DATE_STAT` date DEFAULT NULL,
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_COUNTRY_ID_DATE_STAT` (`COUNTRY_ID`,`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=570 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_day`
--

DROP TABLE IF EXISTS `b_stat_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_day` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_STAT` date DEFAULT NULL,
  `HITS` int(18) NOT NULL DEFAULT '0',
  `C_HOSTS` int(18) NOT NULL DEFAULT '0',
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  `GUESTS` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `FAVORITES` int(18) NOT NULL DEFAULT '0',
  `TOTAL_HOSTS` int(18) NOT NULL DEFAULT '0',
  `AM_AVERAGE_TIME` decimal(18,2) NOT NULL DEFAULT '0.00',
  `AM_1` int(18) NOT NULL DEFAULT '0',
  `AM_1_3` int(18) NOT NULL DEFAULT '0',
  `AM_3_6` int(18) NOT NULL DEFAULT '0',
  `AM_6_9` int(18) NOT NULL DEFAULT '0',
  `AM_9_12` int(18) NOT NULL DEFAULT '0',
  `AM_12_15` int(18) NOT NULL DEFAULT '0',
  `AM_15_18` int(18) NOT NULL DEFAULT '0',
  `AM_18_21` int(18) NOT NULL DEFAULT '0',
  `AM_21_24` int(18) NOT NULL DEFAULT '0',
  `AM_24` int(18) NOT NULL DEFAULT '0',
  `AH_AVERAGE_HITS` decimal(18,2) NOT NULL DEFAULT '0.00',
  `AH_1` int(18) NOT NULL DEFAULT '0',
  `AH_2_5` int(18) NOT NULL DEFAULT '0',
  `AH_6_9` int(18) NOT NULL DEFAULT '0',
  `AH_10_13` int(18) NOT NULL DEFAULT '0',
  `AH_14_17` int(18) NOT NULL DEFAULT '0',
  `AH_18_21` int(18) NOT NULL DEFAULT '0',
  `AH_22_25` int(18) NOT NULL DEFAULT '0',
  `AH_26_29` int(18) NOT NULL DEFAULT '0',
  `AH_30_33` int(18) NOT NULL DEFAULT '0',
  `AH_34` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_23` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_12` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_DATE_STAT` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=444 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_day_site`
--

DROP TABLE IF EXISTS `b_stat_day_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_day_site` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_STAT` date DEFAULT NULL,
  `SITE_ID` char(2) NOT NULL,
  `HITS` int(18) NOT NULL DEFAULT '0',
  `C_HOSTS` int(18) NOT NULL DEFAULT '0',
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  `GUESTS` int(18) NOT NULL DEFAULT '0',
  `NEW_GUESTS` int(18) NOT NULL DEFAULT '0',
  `FAVORITES` int(18) NOT NULL DEFAULT '0',
  `TOTAL_HOSTS` int(18) NOT NULL DEFAULT '0',
  `AM_AVERAGE_TIME` decimal(18,2) NOT NULL DEFAULT '0.00',
  `AM_1` int(18) NOT NULL DEFAULT '0',
  `AM_1_3` int(18) NOT NULL DEFAULT '0',
  `AM_3_6` int(18) NOT NULL DEFAULT '0',
  `AM_6_9` int(18) NOT NULL DEFAULT '0',
  `AM_9_12` int(18) NOT NULL DEFAULT '0',
  `AM_12_15` int(18) NOT NULL DEFAULT '0',
  `AM_15_18` int(18) NOT NULL DEFAULT '0',
  `AM_18_21` int(18) NOT NULL DEFAULT '0',
  `AM_21_24` int(18) NOT NULL DEFAULT '0',
  `AM_24` int(18) NOT NULL DEFAULT '0',
  `AH_AVERAGE_HITS` decimal(18,2) NOT NULL DEFAULT '0.00',
  `AH_1` int(18) NOT NULL DEFAULT '0',
  `AH_2_5` int(18) NOT NULL DEFAULT '0',
  `AH_6_9` int(18) NOT NULL DEFAULT '0',
  `AH_10_13` int(18) NOT NULL DEFAULT '0',
  `AH_14_17` int(18) NOT NULL DEFAULT '0',
  `AH_18_21` int(18) NOT NULL DEFAULT '0',
  `AH_22_25` int(18) NOT NULL DEFAULT '0',
  `AH_26_29` int(18) NOT NULL DEFAULT '0',
  `AH_30_33` int(18) NOT NULL DEFAULT '0',
  `AH_34` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_HOST_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_GUEST_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_NEW_GUEST_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_SESSION_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_HIT_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_EVENT_23` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_0` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_1` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_2` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_3` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_4` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_5` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_6` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_7` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_8` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_9` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_10` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_11` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_12` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_13` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_14` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_15` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_16` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_17` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_18` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_19` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_20` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_21` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_22` int(18) NOT NULL DEFAULT '0',
  `HOUR_FAVORITE_23` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HOST_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NEW_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_SESSION_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_HIT_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_EVENT_6` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_0` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_1` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_2` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_3` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_4` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_5` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_FAVORITE_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_HOST_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_NEW_GUEST_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_SESSION_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_HIT_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_EVENT_12` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_1` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_2` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_3` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_4` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_5` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_6` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_7` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_8` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_9` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_10` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_11` int(18) NOT NULL DEFAULT '0',
  `MONTH_FAVORITE_12` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_SITE_ID_DATE_STAT` (`SITE_ID`,`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=391 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_ddl`
--

DROP TABLE IF EXISTS `b_stat_ddl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_ddl` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `SQL_TEXT` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_event`
--

DROP TABLE IF EXISTS `b_stat_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_event` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `EVENT1` varchar(166) DEFAULT NULL,
  `EVENT2` varchar(166) DEFAULT NULL,
  `MONEY` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `DATE_ENTER` datetime DEFAULT NULL,
  `DATE_CLEANUP` datetime DEFAULT NULL,
  `C_SORT` int(18) DEFAULT '100',
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `ADV_VISIBLE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(50) DEFAULT NULL,
  `DESCRIPTION` text,
  `KEEP_DAYS` int(18) DEFAULT NULL,
  `DYNAMIC_KEEP_DAYS` int(18) DEFAULT NULL,
  `DIAGRAM_DEFAULT` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `IX_EVENT1_EVENT2` (`EVENT1`,`EVENT2`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_event_day`
--

DROP TABLE IF EXISTS `b_stat_event_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_event_day` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_STAT` date DEFAULT NULL,
  `DATE_LAST` datetime DEFAULT NULL,
  `EVENT_ID` int(18) NOT NULL DEFAULT '0',
  `MONEY` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_EVENT_ID_DATE_STAT` (`EVENT_ID`,`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_event_list`
--

DROP TABLE IF EXISTS `b_stat_event_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_event_list` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `EVENT_ID` int(18) NOT NULL DEFAULT '0',
  `EVENT3` varchar(255) DEFAULT NULL,
  `MONEY` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `DATE_ENTER` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `REFERER_URL` text,
  `URL` text,
  `REDIRECT_URL` text,
  `SESSION_ID` int(18) DEFAULT NULL,
  `GUEST_ID` int(18) DEFAULT NULL,
  `ADV_ID` int(18) DEFAULT NULL,
  `ADV_BACK` char(1) NOT NULL DEFAULT 'N',
  `HIT_ID` int(18) DEFAULT NULL,
  `COUNTRY_ID` char(2) DEFAULT NULL,
  `KEEP_DAYS` int(18) DEFAULT NULL,
  `CHARGEBACK` char(1) NOT NULL DEFAULT 'N',
  `SITE_ID` char(2) DEFAULT NULL,
  `REFERER_SITE_ID` char(2) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_GUEST_ID` (`GUEST_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=811 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_guest`
--

DROP TABLE IF EXISTS `b_stat_guest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_guest` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `FAVORITES` char(1) NOT NULL DEFAULT 'N',
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `REPAIR` char(1) NOT NULL DEFAULT 'N',
  `FIRST_SESSION_ID` int(18) DEFAULT NULL,
  `FIRST_DATE` datetime DEFAULT NULL,
  `FIRST_URL_FROM` text,
  `FIRST_URL_TO` text,
  `FIRST_URL_TO_404` char(1) NOT NULL DEFAULT 'N',
  `FIRST_SITE_ID` char(2) DEFAULT NULL,
  `FIRST_ADV_ID` int(18) DEFAULT NULL,
  `FIRST_REFERER1` varchar(255) DEFAULT NULL,
  `FIRST_REFERER2` varchar(255) DEFAULT NULL,
  `FIRST_REFERER3` varchar(255) DEFAULT NULL,
  `LAST_SESSION_ID` int(18) DEFAULT NULL,
  `LAST_DATE` datetime DEFAULT NULL,
  `LAST_USER_ID` int(18) DEFAULT NULL,
  `LAST_USER_AUTH` char(1) DEFAULT NULL,
  `LAST_URL_LAST` text,
  `LAST_URL_LAST_404` char(1) NOT NULL DEFAULT 'N',
  `LAST_USER_AGENT` text,
  `LAST_IP` varchar(15) DEFAULT NULL,
  `LAST_COOKIE` text,
  `LAST_LANGUAGE` varchar(255) DEFAULT NULL,
  `LAST_ADV_ID` int(18) DEFAULT NULL,
  `LAST_ADV_BACK` char(1) NOT NULL DEFAULT 'N',
  `LAST_REFERER1` varchar(255) DEFAULT NULL,
  `LAST_REFERER2` varchar(255) DEFAULT NULL,
  `LAST_REFERER3` varchar(255) DEFAULT NULL,
  `LAST_SITE_ID` char(2) DEFAULT NULL,
  `LAST_COUNTRY_ID` char(2) DEFAULT NULL,
  `LAST_CITY_ID` int(18) DEFAULT NULL,
  `LAST_CITY_INFO` text,
  PRIMARY KEY (`ID`),
  KEY `IX_LAST_DATE` (`LAST_DATE`)
) ENGINE=MyISAM AUTO_INCREMENT=874 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_hit`
--

DROP TABLE IF EXISTS `b_stat_hit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_hit` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `SESSION_ID` int(18) NOT NULL DEFAULT '0',
  `DATE_HIT` datetime DEFAULT NULL,
  `GUEST_ID` int(18) DEFAULT NULL,
  `NEW_GUEST` char(1) NOT NULL DEFAULT 'N',
  `USER_ID` int(18) DEFAULT NULL,
  `USER_AUTH` char(1) DEFAULT NULL,
  `URL` text,
  `URL_404` char(1) NOT NULL DEFAULT 'N',
  `URL_FROM` text,
  `IP` varchar(15) DEFAULT NULL,
  `METHOD` varchar(10) DEFAULT NULL,
  `COOKIES` text,
  `USER_AGENT` text,
  `STOP_LIST_ID` int(18) DEFAULT NULL,
  `COUNTRY_ID` char(2) DEFAULT NULL,
  `CITY_ID` int(18) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_DATE_HIT` (`DATE_HIT`)
) ENGINE=MyISAM AUTO_INCREMENT=35112 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_page`
--

DROP TABLE IF EXISTS `b_stat_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_page` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DATE_STAT` date NOT NULL DEFAULT '0000-00-00',
  `DIR` char(1) NOT NULL DEFAULT 'N',
  `URL` text NOT NULL,
  `URL_404` char(1) NOT NULL DEFAULT 'N',
  `URL_HASH` int(32) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `COUNTER` int(11) NOT NULL DEFAULT '0',
  `ENTER_COUNTER` int(18) NOT NULL DEFAULT '0',
  `EXIT_COUNTER` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_DATE_STAT` (`DATE_STAT`),
  KEY `IX_URL_HASH` (`URL_HASH`)
) ENGINE=MyISAM AUTO_INCREMENT=7922 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_page_adv`
--

DROP TABLE IF EXISTS `b_stat_page_adv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_page_adv` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_STAT` date DEFAULT NULL,
  `PAGE_ID` int(18) NOT NULL DEFAULT '0',
  `ADV_ID` int(18) NOT NULL DEFAULT '0',
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `ENTER_COUNTER` int(18) NOT NULL DEFAULT '0',
  `EXIT_COUNTER` int(18) NOT NULL DEFAULT '0',
  `COUNTER_BACK` int(18) NOT NULL DEFAULT '0',
  `ENTER_COUNTER_BACK` int(18) NOT NULL DEFAULT '0',
  `EXIT_COUNTER_BACK` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_PAGE_ID_ADV_ID` (`PAGE_ID`,`ADV_ID`),
  KEY `IX_DATE_STAT` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=207 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_path`
--

DROP TABLE IF EXISTS `b_stat_path`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_path` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `PATH_ID` int(32) NOT NULL DEFAULT '0',
  `PARENT_PATH_ID` int(32) DEFAULT NULL,
  `DATE_STAT` date DEFAULT NULL,
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `COUNTER_ABNORMAL` int(18) NOT NULL DEFAULT '0',
  `COUNTER_FULL_PATH` int(18) NOT NULL DEFAULT '0',
  `PAGES` text,
  `FIRST_PAGE` varchar(255) DEFAULT NULL,
  `FIRST_PAGE_404` char(1) NOT NULL DEFAULT 'N',
  `FIRST_PAGE_SITE_ID` char(2) DEFAULT NULL,
  `PREV_PAGE` varchar(255) DEFAULT NULL,
  `PREV_PAGE_HASH` int(32) DEFAULT NULL,
  `LAST_PAGE` varchar(255) DEFAULT NULL,
  `LAST_PAGE_404` char(1) NOT NULL DEFAULT 'N',
  `LAST_PAGE_SITE_ID` char(2) DEFAULT NULL,
  `LAST_PAGE_HASH` int(32) DEFAULT NULL,
  `STEPS` int(18) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `IX_PATH_ID_DATE_STAT` (`PATH_ID`,`DATE_STAT`),
  KEY `IX_PREV_PAGE_HASH_LAST_PAGE_HASH` (`PREV_PAGE_HASH`,`LAST_PAGE_HASH`),
  KEY `IX_DATE_STAT` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=3368 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_path_adv`
--

DROP TABLE IF EXISTS `b_stat_path_adv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_path_adv` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ADV_ID` int(18) NOT NULL DEFAULT '0',
  `PATH_ID` int(32) NOT NULL DEFAULT '0',
  `DATE_STAT` date DEFAULT NULL,
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `COUNTER_BACK` int(18) NOT NULL DEFAULT '0',
  `COUNTER_FULL_PATH` int(18) NOT NULL DEFAULT '0',
  `COUNTER_FULL_PATH_BACK` int(18) NOT NULL DEFAULT '0',
  `STEPS` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_PATH_ID_ADV_ID_DATE_STAT` (`PATH_ID`,`ADV_ID`,`DATE_STAT`),
  KEY `IX_DATE_STAT` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=127 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_path_cache`
--

DROP TABLE IF EXISTS `b_stat_path_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_path_cache` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `SESSION_ID` int(18) NOT NULL DEFAULT '0',
  `DATE_HIT` datetime DEFAULT NULL,
  `PATH_ID` int(32) DEFAULT NULL,
  `PATH_PAGES` text,
  `PATH_FIRST_PAGE` varchar(255) DEFAULT NULL,
  `PATH_FIRST_PAGE_404` char(1) NOT NULL DEFAULT 'N',
  `PATH_FIRST_PAGE_SITE_ID` char(2) DEFAULT NULL,
  `PATH_LAST_PAGE` varchar(255) DEFAULT NULL,
  `PATH_LAST_PAGE_404` char(1) NOT NULL DEFAULT 'N',
  `PATH_LAST_PAGE_SITE_ID` char(2) DEFAULT NULL,
  `PATH_STEPS` int(18) NOT NULL DEFAULT '1',
  `IS_LAST_PAGE` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `IX_SESSION_ID` (`SESSION_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=20979 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_phrase_list`
--

DROP TABLE IF EXISTS `b_stat_phrase_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_phrase_list` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_HIT` datetime DEFAULT NULL,
  `SEARCHER_ID` int(18) DEFAULT NULL,
  `REFERER_ID` int(18) DEFAULT NULL,
  `PHRASE` varchar(255) NOT NULL,
  `URL_FROM` text,
  `URL_TO` text,
  `URL_TO_404` char(1) NOT NULL DEFAULT 'N',
  `SESSION_ID` int(18) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_DATE_HIT` (`DATE_HIT`),
  KEY `IX_URL_TO_SEARCHER_ID` (`URL_TO`(100),`SEARCHER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_referer`
--

DROP TABLE IF EXISTS `b_stat_referer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_referer` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_FIRST` datetime DEFAULT NULL,
  `DATE_LAST` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `SITE_NAME` varchar(255) NOT NULL,
  `SESSIONS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_SITE_NAME` (`SITE_NAME`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_referer_list`
--

DROP TABLE IF EXISTS `b_stat_referer_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_referer_list` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `REFERER_ID` int(18) DEFAULT NULL,
  `DATE_HIT` datetime DEFAULT NULL,
  `PROTOCOL` varchar(10) NOT NULL,
  `SITE_NAME` varchar(255) NOT NULL,
  `URL_FROM` text NOT NULL,
  `URL_TO` text,
  `URL_TO_404` char(1) NOT NULL DEFAULT 'N',
  `SESSION_ID` int(18) DEFAULT NULL,
  `ADV_ID` int(18) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_DATE_HIT` (`DATE_HIT`),
  KEY `IX_SITE_NAME` (`SITE_NAME`(100),`URL_TO`(100))
) ENGINE=MyISAM AUTO_INCREMENT=91 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_searcher`
--

DROP TABLE IF EXISTS `b_stat_searcher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_searcher` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_CLEANUP` datetime DEFAULT NULL,
  `TOTAL_HITS` int(18) NOT NULL DEFAULT '0',
  `SAVE_STATISTIC` char(1) NOT NULL DEFAULT 'Y',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(255) NOT NULL,
  `USER_AGENT` text,
  `DIAGRAM_DEFAULT` char(1) NOT NULL DEFAULT 'N',
  `HIT_KEEP_DAYS` int(18) DEFAULT NULL,
  `DYNAMIC_KEEP_DAYS` int(18) DEFAULT NULL,
  `PHRASES` int(18) NOT NULL DEFAULT '0',
  `PHRASES_HITS` int(18) NOT NULL DEFAULT '0',
  `CHECK_ACTIVITY` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=217 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_searcher_day`
--

DROP TABLE IF EXISTS `b_stat_searcher_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_searcher_day` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_STAT` date DEFAULT NULL,
  `DATE_LAST` datetime DEFAULT NULL,
  `SEARCHER_ID` int(18) NOT NULL DEFAULT '0',
  `TOTAL_HITS` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_SEARCHER_ID_DATE_STAT` (`SEARCHER_ID`,`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=933 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_searcher_hit`
--

DROP TABLE IF EXISTS `b_stat_searcher_hit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_searcher_hit` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_HIT` datetime DEFAULT NULL,
  `SEARCHER_ID` int(18) NOT NULL DEFAULT '0',
  `URL` text NOT NULL,
  `URL_404` char(1) NOT NULL DEFAULT 'N',
  `IP` varchar(15) DEFAULT NULL,
  `USER_AGENT` text,
  `HIT_KEEP_DAYS` int(18) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=10034 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_searcher_params`
--

DROP TABLE IF EXISTS `b_stat_searcher_params`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_searcher_params` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `SEARCHER_ID` int(18) NOT NULL DEFAULT '0',
  `DOMAIN` varchar(255) DEFAULT NULL,
  `VARIABLE` varchar(255) DEFAULT NULL,
  `CHAR_SET` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_SEARCHER_DOMAIN` (`SEARCHER_ID`,`DOMAIN`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_session`
--

DROP TABLE IF EXISTS `b_stat_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_session` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `GUEST_ID` int(18) DEFAULT NULL,
  `NEW_GUEST` char(1) NOT NULL DEFAULT 'N',
  `USER_ID` int(18) DEFAULT NULL,
  `USER_AUTH` char(1) DEFAULT NULL,
  `C_EVENTS` int(18) NOT NULL DEFAULT '0',
  `HITS` int(18) NOT NULL DEFAULT '0',
  `FAVORITES` char(1) NOT NULL DEFAULT 'N',
  `URL_FROM` text,
  `URL_TO` text,
  `URL_TO_404` char(1) NOT NULL DEFAULT 'N',
  `URL_LAST` text,
  `URL_LAST_404` char(1) NOT NULL DEFAULT 'N',
  `USER_AGENT` text,
  `DATE_STAT` date DEFAULT NULL,
  `DATE_FIRST` datetime DEFAULT NULL,
  `DATE_LAST` datetime DEFAULT NULL,
  `IP_FIRST` varchar(15) DEFAULT NULL,
  `IP_FIRST_NUMBER` bigint(20) DEFAULT NULL,
  `IP_LAST` varchar(15) DEFAULT NULL,
  `IP_LAST_NUMBER` bigint(20) DEFAULT NULL,
  `FIRST_HIT_ID` int(18) DEFAULT NULL,
  `LAST_HIT_ID` int(18) DEFAULT NULL,
  `PHPSESSID` varchar(255) DEFAULT NULL,
  `ADV_ID` int(18) DEFAULT NULL,
  `ADV_BACK` char(1) DEFAULT NULL,
  `REFERER1` varchar(255) DEFAULT NULL,
  `REFERER2` varchar(255) DEFAULT NULL,
  `REFERER3` varchar(255) DEFAULT NULL,
  `STOP_LIST_ID` int(18) DEFAULT NULL,
  `COUNTRY_ID` char(2) DEFAULT NULL,
  `CITY_ID` int(18) DEFAULT NULL,
  `FIRST_SITE_ID` char(2) DEFAULT NULL,
  `LAST_SITE_ID` char(2) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_IP_FIRST_NUMBER_DATE_STAT` (`IP_FIRST_NUMBER`,`DATE_STAT`),
  KEY `IX_B_STAT_SESSION_4` (`USER_ID`,`DATE_STAT`),
  KEY `IX_DATE_STAT` (`DATE_STAT`)
) ENGINE=MyISAM AUTO_INCREMENT=1791 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stat_session_data`
--

DROP TABLE IF EXISTS `b_stat_session_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stat_session_data` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE_FIRST` datetime DEFAULT NULL,
  `DATE_LAST` datetime DEFAULT NULL,
  `GUEST_MD5` varchar(255) NOT NULL,
  `SESS_SESSION_ID` int(18) DEFAULT NULL,
  `SESSION_DATA` text,
  PRIMARY KEY (`ID`),
  KEY `IX_GUEST_MD5` (`GUEST_MD5`)
) ENGINE=MyISAM AUTO_INCREMENT=1969 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sticker`
--

DROP TABLE IF EXISTS `b_sticker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sticker` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PAGE_URL` varchar(255) NOT NULL,
  `PAGE_TITLE` varchar(255) NOT NULL,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  `MODIFIED_BY` int(18) NOT NULL,
  `CREATED_BY` int(18) NOT NULL,
  `PERSONAL` char(1) NOT NULL DEFAULT 'N',
  `CONTENT` text,
  `POS_TOP` int(11) DEFAULT NULL,
  `POS_LEFT` int(11) DEFAULT NULL,
  `WIDTH` int(11) DEFAULT NULL,
  `HEIGHT` int(11) DEFAULT NULL,
  `COLOR` int(11) DEFAULT NULL,
  `COLLAPSED` char(1) NOT NULL DEFAULT 'N',
  `COMPLETED` char(1) NOT NULL DEFAULT 'N',
  `CLOSED` char(1) NOT NULL DEFAULT 'N',
  `DELETED` char(1) NOT NULL DEFAULT 'N',
  `MARKER_TOP` int(11) DEFAULT NULL,
  `MARKER_LEFT` int(11) DEFAULT NULL,
  `MARKER_WIDTH` int(11) DEFAULT NULL,
  `MARKER_HEIGHT` int(11) DEFAULT NULL,
  `MARKER_ADJUST` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sticker_group_task`
--

DROP TABLE IF EXISTS `b_sticker_group_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_sticker_group_task` (
  `GROUP_ID` int(11) NOT NULL,
  `TASK_ID` int(11) NOT NULL,
  PRIMARY KEY (`GROUP_ID`,`TASK_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_stop_list`
--

DROP TABLE IF EXISTS `b_stop_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_stop_list` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DATE_START` datetime DEFAULT NULL,
  `DATE_END` datetime DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `SAVE_STATISTIC` char(1) NOT NULL DEFAULT 'N',
  `IP_1` int(18) DEFAULT NULL,
  `IP_2` int(18) DEFAULT NULL,
  `IP_3` int(18) DEFAULT NULL,
  `IP_4` int(18) DEFAULT NULL,
  `MASK_1` int(18) DEFAULT NULL,
  `MASK_2` int(18) DEFAULT NULL,
  `MASK_3` int(18) DEFAULT NULL,
  `MASK_4` int(18) DEFAULT NULL,
  `USER_AGENT` text,
  `USER_AGENT_IS_NULL` char(1) NOT NULL DEFAULT 'N',
  `URL_TO` text,
  `URL_FROM` text,
  `MESSAGE` text,
  `MESSAGE_LID` char(2) NOT NULL DEFAULT 'en',
  `URL_REDIRECT` text,
  `COMMENTS` text,
  `TEST` char(1) NOT NULL DEFAULT 'N',
  `SITE_ID` char(2) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_subscription`
--

DROP TABLE IF EXISTS `b_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_subscription` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DATE_INSERT` datetime NOT NULL,
  `DATE_UPDATE` datetime DEFAULT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `EMAIL` varchar(255) NOT NULL,
  `FORMAT` varchar(4) NOT NULL DEFAULT 'text',
  `CONFIRM_CODE` varchar(8) DEFAULT NULL,
  `CONFIRMED` char(1) NOT NULL DEFAULT 'N',
  `DATE_CONFIRM` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UK_SUBSCRIPTION_EMAIL` (`EMAIL`),
  KEY `IX_DATE_CONFIRM` (`CONFIRMED`,`DATE_CONFIRM`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_subscription_rubric`
--

DROP TABLE IF EXISTS `b_subscription_rubric`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_subscription_rubric` (
  `SUBSCRIPTION_ID` int(11) NOT NULL,
  `LIST_RUBRIC_ID` int(11) NOT NULL,
  UNIQUE KEY `UK_SUBSCRIPTION_RUBRIC` (`SUBSCRIPTION_ID`,`LIST_RUBRIC_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_task`
--

DROP TABLE IF EXISTS `b_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_task` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) NOT NULL,
  `LETTER` char(1) DEFAULT NULL,
  `MODULE_ID` varchar(50) NOT NULL,
  `SYS` char(1) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `BINDING` varchar(50) DEFAULT 'module',
  PRIMARY KEY (`ID`),
  KEY `ix_task` (`MODULE_ID`,`BINDING`,`LETTER`,`SYS`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_task_operation`
--

DROP TABLE IF EXISTS `b_task_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_task_operation` (
  `TASK_ID` int(18) NOT NULL,
  `OPERATION_ID` int(18) NOT NULL,
  PRIMARY KEY (`TASK_ID`,`OPERATION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket`
--

DROP TABLE IF EXISTS `b_ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SITE_ID` char(2) NOT NULL,
  `DATE_CREATE` datetime DEFAULT NULL,
  `DAY_CREATE` date DEFAULT NULL,
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `DATE_CLOSE` datetime DEFAULT NULL,
  `AUTO_CLOSED` char(1) DEFAULT NULL,
  `AUTO_CLOSE_DAYS` int(3) DEFAULT NULL,
  `SLA_ID` int(18) NOT NULL DEFAULT '1',
  `NOTIFY_AGENT_ID` int(18) DEFAULT NULL,
  `EXPIRE_AGENT_ID` int(18) DEFAULT NULL,
  `OVERDUE_MESSAGES` int(18) NOT NULL DEFAULT '0',
  `IS_NOTIFIED` char(1) NOT NULL DEFAULT 'N',
  `IS_OVERDUE` char(1) NOT NULL DEFAULT 'N',
  `CATEGORY_ID` int(18) DEFAULT NULL,
  `CRITICALITY_ID` int(18) DEFAULT NULL,
  `STATUS_ID` int(18) DEFAULT NULL,
  `MARK_ID` int(18) DEFAULT NULL,
  `SOURCE_ID` int(18) DEFAULT NULL,
  `DIFFICULTY_ID` int(18) DEFAULT NULL,
  `TITLE` text NOT NULL,
  `MESSAGES` int(11) NOT NULL DEFAULT '0',
  `IS_SPAM` char(1) DEFAULT NULL,
  `OWNER_USER_ID` int(11) DEFAULT NULL,
  `OWNER_GUEST_ID` int(11) DEFAULT NULL,
  `OWNER_SID` text,
  `CREATED_USER_ID` int(18) DEFAULT NULL,
  `CREATED_GUEST_ID` int(18) DEFAULT NULL,
  `CREATED_MODULE_NAME` varchar(255) DEFAULT NULL,
  `RESPONSIBLE_USER_ID` int(11) DEFAULT NULL,
  `MODIFIED_USER_ID` int(11) DEFAULT NULL,
  `MODIFIED_GUEST_ID` int(11) DEFAULT NULL,
  `MODIFIED_MODULE_NAME` varchar(255) DEFAULT NULL,
  `LAST_MESSAGE_USER_ID` int(18) DEFAULT NULL,
  `LAST_MESSAGE_GUEST_ID` int(18) DEFAULT NULL,
  `LAST_MESSAGE_SID` text,
  `LAST_MESSAGE_BY_SUPPORT_TEAM` char(1) NOT NULL DEFAULT 'N',
  `LAST_MESSAGE_DATE` datetime DEFAULT NULL,
  `SUPPORT_COMMENTS` text,
  `PROBLEM_TIME` int(18) DEFAULT NULL,
  `HOLD_ON` char(1) NOT NULL DEFAULT 'N',
  `REOPEN` char(1) NOT NULL DEFAULT 'N',
  `COUPON` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_dictionary`
--

DROP TABLE IF EXISTS `b_ticket_dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_dictionary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FIRST_SITE_ID` char(2) DEFAULT NULL,
  `C_TYPE` varchar(5) NOT NULL,
  `SID` varchar(255) DEFAULT NULL,
  `SET_AS_DEFAULT` char(1) DEFAULT NULL,
  `C_SORT` int(11) DEFAULT '100',
  `NAME` varchar(255) NOT NULL,
  `DESCR` text,
  `RESPONSIBLE_USER_ID` int(11) DEFAULT NULL,
  `EVENT1` varchar(255) DEFAULT 'ticket',
  `EVENT2` varchar(255) DEFAULT NULL,
  `EVENT3` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_dictionary_2_site`
--

DROP TABLE IF EXISTS `b_ticket_dictionary_2_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_dictionary_2_site` (
  `DICTIONARY_ID` int(18) NOT NULL DEFAULT '0',
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`DICTIONARY_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_message`
--

DROP TABLE IF EXISTS `b_ticket_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_message` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `DATE_CREATE` datetime DEFAULT NULL,
  `DAY_CREATE` date DEFAULT NULL,
  `C_NUMBER` int(11) DEFAULT NULL,
  `TICKET_ID` int(11) NOT NULL DEFAULT '0',
  `IS_HIDDEN` char(1) NOT NULL DEFAULT 'N',
  `IS_LOG` char(1) NOT NULL DEFAULT 'N',
  `IS_OVERDUE` char(1) NOT NULL DEFAULT 'N',
  `CURRENT_RESPONSIBLE_USER_ID` int(18) DEFAULT NULL,
  `NOTIFY_AGENT_DONE` char(1) NOT NULL DEFAULT 'N',
  `EXPIRE_AGENT_DONE` char(1) NOT NULL DEFAULT 'N',
  `MESSAGE` longtext,
  `MESSAGE_SEARCH` longtext,
  `IS_SPAM` char(1) DEFAULT NULL,
  `EXTERNAL_ID` int(18) DEFAULT NULL,
  `EXTERNAL_FIELD_1` text,
  `OWNER_USER_ID` int(11) DEFAULT NULL,
  `OWNER_GUEST_ID` int(11) DEFAULT NULL,
  `OWNER_SID` text,
  `SOURCE_ID` int(18) DEFAULT NULL,
  `CREATED_USER_ID` int(18) DEFAULT NULL,
  `CREATED_GUEST_ID` int(18) DEFAULT NULL,
  `CREATED_MODULE_NAME` varchar(255) DEFAULT NULL,
  `MODIFIED_USER_ID` int(18) DEFAULT NULL,
  `MODIFIED_GUEST_ID` int(18) DEFAULT NULL,
  `MESSAGE_BY_SUPPORT_TEAM` char(1) DEFAULT NULL,
  `TASK_TIME` int(18) DEFAULT NULL,
  `NOT_CHANGE_STATUS` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `IX_TICKET_ID` (`TICKET_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_message_2_file`
--

DROP TABLE IF EXISTS `b_ticket_message_2_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_message_2_file` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `HASH` varchar(255) DEFAULT NULL,
  `MESSAGE_ID` int(18) NOT NULL DEFAULT '0',
  `FILE_ID` int(18) NOT NULL DEFAULT '0',
  `TICKET_ID` int(18) NOT NULL DEFAULT '0',
  `EXTENSION_SUFFIX` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_HASH` (`HASH`),
  KEY `IX_MESSAGE_ID` (`MESSAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_online`
--

DROP TABLE IF EXISTS `b_ticket_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_online` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `TICKET_ID` int(18) DEFAULT NULL,
  `USER_ID` int(18) DEFAULT NULL,
  `CURRENT_MODE` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_TICKET_ID` (`TICKET_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_sla`
--

DROP TABLE IF EXISTS `b_ticket_sla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_sla` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `PRIORITY` int(18) NOT NULL DEFAULT '0',
  `FIRST_SITE_ID` varchar(5) DEFAULT NULL,
  `NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `RESPONSE_TIME` int(18) DEFAULT NULL,
  `RESPONSE_TIME_UNIT` varchar(10) NOT NULL DEFAULT 'hour',
  `NOTICE_TIME` int(18) DEFAULT NULL,
  `NOTICE_TIME_UNIT` varchar(10) NOT NULL DEFAULT 'hour',
  `RESPONSIBLE_USER_ID` int(18) DEFAULT NULL,
  `DATE_CREATE` datetime DEFAULT NULL,
  `CREATED_USER_ID` int(18) DEFAULT NULL,
  `CREATED_GUEST_ID` int(18) DEFAULT NULL,
  `DATE_MODIFY` datetime DEFAULT NULL,
  `MODIFIED_USER_ID` int(18) DEFAULT NULL,
  `MODIFIED_GUEST_ID` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_sla_2_category`
--

DROP TABLE IF EXISTS `b_ticket_sla_2_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_sla_2_category` (
  `SLA_ID` int(18) NOT NULL DEFAULT '0',
  `CATEGORY_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SLA_ID`,`CATEGORY_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_sla_2_criticality`
--

DROP TABLE IF EXISTS `b_ticket_sla_2_criticality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_sla_2_criticality` (
  `SLA_ID` int(18) NOT NULL DEFAULT '0',
  `CRITICALITY_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SLA_ID`,`CRITICALITY_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_sla_2_mark`
--

DROP TABLE IF EXISTS `b_ticket_sla_2_mark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_sla_2_mark` (
  `SLA_ID` int(18) NOT NULL DEFAULT '0',
  `MARK_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SLA_ID`,`MARK_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_sla_2_site`
--

DROP TABLE IF EXISTS `b_ticket_sla_2_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_sla_2_site` (
  `SLA_ID` int(18) NOT NULL,
  `SITE_ID` varchar(5) NOT NULL,
  PRIMARY KEY (`SLA_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_sla_2_user_group`
--

DROP TABLE IF EXISTS `b_ticket_sla_2_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_sla_2_user_group` (
  `SLA_ID` int(18) NOT NULL DEFAULT '0',
  `GROUP_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SLA_ID`,`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_sla_shedule`
--

DROP TABLE IF EXISTS `b_ticket_sla_shedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_sla_shedule` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `SLA_ID` int(18) NOT NULL DEFAULT '0',
  `WEEKDAY_NUMBER` int(2) NOT NULL DEFAULT '0',
  `OPEN_TIME` varchar(10) NOT NULL DEFAULT '24H',
  `MINUTE_FROM` int(18) DEFAULT NULL,
  `MINUTE_TILL` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_SLA_ID` (`SLA_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_supercoupons`
--

DROP TABLE IF EXISTS `b_ticket_supercoupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_supercoupons` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `COUNT_TICKETS` int(11) NOT NULL DEFAULT '0',
  `COUPON` varchar(255) NOT NULL DEFAULT '',
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DATE_CREATE` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `CREATED_USER_ID` int(11) DEFAULT NULL,
  `UPDATED_USER_ID` int(11) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `ACTIVE_FROM` date DEFAULT NULL,
  `ACTIVE_TO` date DEFAULT NULL,
  `SLA_ID` int(11) DEFAULT NULL,
  `COUNT_USED` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_COUPON` (`COUPON`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_supercoupons_log`
--

DROP TABLE IF EXISTS `b_ticket_supercoupons_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_supercoupons_log` (
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `COUPON_ID` int(11) NOT NULL DEFAULT '0',
  `USER_ID` int(11) DEFAULT NULL,
  `SUCCESS` char(1) NOT NULL DEFAULT 'N',
  `AFTER_COUNT` int(11) NOT NULL DEFAULT '0',
  `SESSION_ID` int(11) DEFAULT NULL,
  `GUEST_ID` int(11) DEFAULT NULL,
  `AFFECTED_ROWS` int(11) DEFAULT NULL,
  `COUPON` varchar(255) DEFAULT NULL,
  KEY `IX_COUPON_ID` (`COUPON_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_ugroups`
--

DROP TABLE IF EXISTS `b_ticket_ugroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_ugroups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `XML_ID` varchar(255) DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  `IS_TEAM_GROUP` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_ticket_user_ugroup`
--

DROP TABLE IF EXISTS `b_ticket_user_ugroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_ticket_user_ugroup` (
  `USER_ID` int(11) NOT NULL DEFAULT '0',
  `GROUP_ID` int(11) NOT NULL DEFAULT '0',
  `CAN_VIEW_GROUP_MESSAGES` char(1) NOT NULL DEFAULT 'N',
  `CAN_MAIL_GROUP_MESSAGES` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`GROUP_ID`,`USER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_undo`
--

DROP TABLE IF EXISTS `b_undo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_undo` (
  `ID` varchar(255) NOT NULL,
  `MODULE_ID` varchar(50) DEFAULT NULL,
  `UNDO_TYPE` varchar(50) DEFAULT NULL,
  `UNDO_HANDLER` varchar(255) DEFAULT NULL,
  `CONTENT` mediumtext,
  `USER_ID` int(11) DEFAULT NULL,
  `TIMESTAMP_X` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user`
--

DROP TABLE IF EXISTS `b_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LOGIN` varchar(50) NOT NULL,
  `PASSWORD` varchar(50) NOT NULL,
  `CHECKWORD` varchar(50) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `LAST_LOGIN` datetime DEFAULT NULL,
  `DATE_REGISTER` datetime NOT NULL,
  `LID` char(2) DEFAULT NULL,
  `PERSONAL_PROFESSION` varchar(255) DEFAULT NULL,
  `PERSONAL_WWW` varchar(255) DEFAULT NULL,
  `PERSONAL_ICQ` varchar(255) DEFAULT NULL,
  `PERSONAL_GENDER` char(1) DEFAULT NULL,
  `PERSONAL_BIRTHDATE` varchar(50) DEFAULT NULL,
  `PERSONAL_PHOTO` int(18) DEFAULT NULL,
  `PERSONAL_PHONE` varchar(255) DEFAULT NULL,
  `PERSONAL_FAX` varchar(255) DEFAULT NULL,
  `PERSONAL_MOBILE` varchar(255) DEFAULT NULL,
  `PERSONAL_PAGER` varchar(255) DEFAULT NULL,
  `PERSONAL_STREET` text,
  `PERSONAL_MAILBOX` varchar(255) DEFAULT NULL,
  `PERSONAL_CITY` varchar(255) DEFAULT NULL,
  `PERSONAL_STATE` varchar(255) DEFAULT NULL,
  `PERSONAL_ZIP` varchar(255) DEFAULT NULL,
  `PERSONAL_COUNTRY` varchar(255) DEFAULT NULL,
  `PERSONAL_NOTES` text,
  `WORK_COMPANY` varchar(255) DEFAULT NULL,
  `WORK_DEPARTMENT` varchar(255) DEFAULT NULL,
  `WORK_POSITION` varchar(255) DEFAULT NULL,
  `WORK_WWW` varchar(255) DEFAULT NULL,
  `WORK_PHONE` varchar(255) DEFAULT NULL,
  `WORK_FAX` varchar(255) DEFAULT NULL,
  `WORK_PAGER` varchar(255) DEFAULT NULL,
  `WORK_STREET` text,
  `WORK_MAILBOX` varchar(255) DEFAULT NULL,
  `WORK_CITY` varchar(255) DEFAULT NULL,
  `WORK_STATE` varchar(255) DEFAULT NULL,
  `WORK_ZIP` varchar(255) DEFAULT NULL,
  `WORK_COUNTRY` varchar(255) DEFAULT NULL,
  `WORK_PROFILE` text,
  `WORK_LOGO` int(18) DEFAULT NULL,
  `WORK_NOTES` text,
  `ADMIN_NOTES` text,
  `STORED_HASH` varchar(32) DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  `PERSONAL_BIRTHDAY` date DEFAULT NULL,
  `EXTERNAL_AUTH_ID` varchar(255) DEFAULT NULL,
  `CHECKWORD_TIME` datetime DEFAULT NULL,
  `SECOND_NAME` varchar(50) DEFAULT NULL,
  `CONFIRM_CODE` varchar(8) DEFAULT NULL,
  `LOGIN_ATTEMPTS` int(18) DEFAULT NULL,
  `LAST_ACTIVITY_DATE` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_login` (`LOGIN`,`EXTERNAL_AUTH_ID`),
  KEY `ix_b_user_email` (`EMAIL`),
  KEY `ix_b_user_activity_date` (`LAST_ACTIVITY_DATE`),
  KEY `IX_B_USER_XML_ID` (`XML_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user_field`
--

DROP TABLE IF EXISTS `b_user_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user_field` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ENTITY_ID` varchar(20) DEFAULT NULL,
  `FIELD_NAME` varchar(20) DEFAULT NULL,
  `USER_TYPE_ID` varchar(50) DEFAULT NULL,
  `XML_ID` varchar(255) DEFAULT NULL,
  `SORT` int(11) DEFAULT NULL,
  `MULTIPLE` char(1) NOT NULL DEFAULT 'N',
  `MANDATORY` char(1) NOT NULL DEFAULT 'N',
  `SHOW_FILTER` char(1) NOT NULL DEFAULT 'N',
  `SHOW_IN_LIST` char(1) NOT NULL DEFAULT 'Y',
  `EDIT_IN_LIST` char(1) NOT NULL DEFAULT 'Y',
  `IS_SEARCHABLE` char(1) NOT NULL DEFAULT 'N',
  `SETTINGS` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ux_user_type_entity` (`ENTITY_ID`,`FIELD_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user_field_enum`
--

DROP TABLE IF EXISTS `b_user_field_enum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user_field_enum` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_FIELD_ID` int(11) DEFAULT NULL,
  `VALUE` varchar(255) NOT NULL,
  `DEF` char(1) NOT NULL DEFAULT 'N',
  `SORT` int(11) NOT NULL DEFAULT '500',
  `XML_ID` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ux_user_field_enum` (`USER_FIELD_ID`,`XML_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user_field_lang`
--

DROP TABLE IF EXISTS `b_user_field_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user_field_lang` (
  `USER_FIELD_ID` int(11) NOT NULL DEFAULT '0',
  `LANGUAGE_ID` char(2) NOT NULL DEFAULT '',
  `EDIT_FORM_LABEL` varchar(255) DEFAULT NULL,
  `LIST_COLUMN_LABEL` varchar(255) DEFAULT NULL,
  `LIST_FILTER_LABEL` varchar(255) DEFAULT NULL,
  `ERROR_MESSAGE` varchar(255) DEFAULT NULL,
  `HELP_MESSAGE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`USER_FIELD_ID`,`LANGUAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user_group`
--

DROP TABLE IF EXISTS `b_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user_group` (
  `USER_ID` int(18) NOT NULL,
  `GROUP_ID` int(18) NOT NULL,
  `DATE_ACTIVE_FROM` datetime DEFAULT NULL,
  `DATE_ACTIVE_TO` datetime DEFAULT NULL,
  UNIQUE KEY `ix_user_group` (`USER_ID`,`GROUP_ID`),
  KEY `ix_user_group_group` (`GROUP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user_hit_auth`
--

DROP TABLE IF EXISTS `b_user_hit_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user_hit_auth` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(18) NOT NULL,
  `HASH` varchar(32) NOT NULL,
  `URL` varchar(255) NOT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `TIMESTAMP_X` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_USER_HIT_AUTH_1` (`HASH`),
  KEY `IX_USER_HIT_AUTH_2` (`USER_ID`),
  KEY `IX_USER_HIT_AUTH_3` (`TIMESTAMP_X`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user_option`
--

DROP TABLE IF EXISTS `b_user_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user_option` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) DEFAULT NULL,
  `CATEGORY` varchar(50) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `VALUE` text,
  `COMMON` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `ix_user_option_param` (`CATEGORY`,`NAME`),
  KEY `ix_user_option_user` (`USER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=78 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_user_stored_auth`
--

DROP TABLE IF EXISTS `b_user_stored_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_user_stored_auth` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(18) NOT NULL,
  `DATE_REG` datetime NOT NULL,
  `LAST_AUTH` datetime NOT NULL,
  `STORED_HASH` varchar(32) NOT NULL,
  `TEMP_HASH` char(1) NOT NULL DEFAULT 'N',
  `IP_ADDR` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ux_user_hash` (`USER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote`
--

DROP TABLE IF EXISTS `b_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CHANNEL_ID` int(18) NOT NULL DEFAULT '0',
  `C_SORT` int(18) DEFAULT '100',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DATE_START` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DATE_END` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `COUNTER` int(11) NOT NULL DEFAULT '0',
  `TITLE` varchar(255) DEFAULT NULL,
  `DESCRIPTION` text,
  `DESCRIPTION_TYPE` varchar(4) NOT NULL DEFAULT 'html',
  `IMAGE_ID` int(18) DEFAULT NULL,
  `EVENT1` varchar(255) DEFAULT NULL,
  `EVENT2` varchar(255) DEFAULT NULL,
  `EVENT3` varchar(255) DEFAULT NULL,
  `UNIQUE_TYPE` int(18) NOT NULL DEFAULT '2',
  `KEEP_IP_SEC` int(18) DEFAULT NULL,
  `DELAY` int(18) DEFAULT NULL,
  `DELAY_TYPE` char(1) DEFAULT NULL,
  `TEMPLATE` varchar(255) DEFAULT NULL,
  `RESULT_TEMPLATE` varchar(255) DEFAULT NULL,
  `NOTIFY` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `IX_CHANNEL_ID` (`CHANNEL_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_answer`
--

DROP TABLE IF EXISTS `b_vote_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_answer` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `QUESTION_ID` int(18) NOT NULL DEFAULT '0',
  `C_SORT` int(18) DEFAULT '100',
  `MESSAGE` text,
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `FIELD_TYPE` int(5) NOT NULL DEFAULT '0',
  `FIELD_WIDTH` int(18) DEFAULT NULL,
  `FIELD_HEIGHT` int(18) DEFAULT NULL,
  `FIELD_PARAM` varchar(255) DEFAULT NULL,
  `COLOR` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_QUESTION_ID` (`QUESTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_channel`
--

DROP TABLE IF EXISTS `b_vote_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_channel` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `SYMBOLIC_NAME` varchar(255) NOT NULL,
  `C_SORT` int(18) DEFAULT '100',
  `FIRST_SITE_ID` char(2) DEFAULT NULL,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `TITLE` varchar(255) NOT NULL,
  `VOTE_SINGLE` char(1) NOT NULL DEFAULT 'Y',
  `USE_CAPTCHA` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_channel_2_group`
--

DROP TABLE IF EXISTS `b_vote_channel_2_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_channel_2_group` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CHANNEL_ID` int(18) NOT NULL DEFAULT '0',
  `GROUP_ID` int(18) NOT NULL DEFAULT '0',
  `PERMISSION` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_channel_2_site`
--

DROP TABLE IF EXISTS `b_vote_channel_2_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_channel_2_site` (
  `CHANNEL_ID` int(18) NOT NULL DEFAULT '0',
  `SITE_ID` char(2) NOT NULL,
  PRIMARY KEY (`CHANNEL_ID`,`SITE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_event`
--

DROP TABLE IF EXISTS `b_vote_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_event` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `VOTE_ID` int(18) NOT NULL DEFAULT '0',
  `VOTE_USER_ID` int(18) NOT NULL DEFAULT '0',
  `DATE_VOTE` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `STAT_SESSION_ID` int(18) DEFAULT NULL,
  `IP` varchar(15) DEFAULT NULL,
  `VALID` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `IX_USER_ID` (`VOTE_USER_ID`),
  KEY `IX_B_VOTE_EVENT_2` (`VOTE_ID`,`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_event_answer`
--

DROP TABLE IF EXISTS `b_vote_event_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_event_answer` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `EVENT_QUESTION_ID` int(18) NOT NULL DEFAULT '0',
  `ANSWER_ID` int(18) NOT NULL DEFAULT '0',
  `MESSAGE` text,
  PRIMARY KEY (`ID`),
  KEY `IX_EVENT_QUESTION_ID` (`EVENT_QUESTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_event_question`
--

DROP TABLE IF EXISTS `b_vote_event_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_event_question` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `EVENT_ID` int(18) NOT NULL DEFAULT '0',
  `QUESTION_ID` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_EVENT_ID` (`EVENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_question`
--

DROP TABLE IF EXISTS `b_vote_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_question` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `VOTE_ID` int(18) NOT NULL DEFAULT '0',
  `C_SORT` int(18) DEFAULT '100',
  `COUNTER` int(11) NOT NULL DEFAULT '0',
  `QUESTION` text NOT NULL,
  `QUESTION_TYPE` varchar(4) NOT NULL DEFAULT 'html',
  `IMAGE_ID` int(18) DEFAULT NULL,
  `DIAGRAM` char(1) NOT NULL DEFAULT 'Y',
  `REQUIRED` char(1) NOT NULL DEFAULT 'N',
  `DIAGRAM_TYPE` varchar(10) NOT NULL DEFAULT 'histogram',
  `TEMPLATE` varchar(255) DEFAULT NULL,
  `TEMPLATE_NEW` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_VOTE_ID` (`VOTE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_vote_user`
--

DROP TABLE IF EXISTS `b_vote_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_vote_user` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `STAT_GUEST_ID` int(18) DEFAULT NULL,
  `AUTH_USER_ID` int(18) DEFAULT NULL,
  `COUNTER` int(18) NOT NULL DEFAULT '0',
  `DATE_FIRST` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DATE_LAST` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LAST_IP` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_workflow_document`
--

DROP TABLE IF EXISTS `b_workflow_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_workflow_document` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `DATE_ENTER` datetime DEFAULT NULL,
  `DATE_MODIFY` datetime DEFAULT NULL,
  `DATE_LOCK` datetime DEFAULT NULL,
  `ENTERED_BY` int(18) DEFAULT NULL,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  `LOCKED_BY` int(18) DEFAULT NULL,
  `FILENAME` varchar(255) NOT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `TITLE` varchar(255) DEFAULT NULL,
  `BODY` longtext,
  `BODY_TYPE` varchar(4) NOT NULL DEFAULT 'html',
  `PROLOG` longtext,
  `EPILOG` longtext,
  `COMMENTS` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_workflow_file`
--

DROP TABLE IF EXISTS `b_workflow_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_workflow_file` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DOCUMENT_ID` int(18) DEFAULT '0',
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  `TEMP_FILENAME` varchar(255) DEFAULT NULL,
  `FILENAME` varchar(255) DEFAULT NULL,
  `FILESIZE` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IX_TEMP_FILENAME` (`TEMP_FILENAME`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_workflow_log`
--

DROP TABLE IF EXISTS `b_workflow_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_workflow_log` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DOCUMENT_ID` int(18) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `MODIFIED_BY` int(18) DEFAULT NULL,
  `TITLE` varchar(255) DEFAULT NULL,
  `FILENAME` varchar(255) DEFAULT NULL,
  `SITE_ID` char(2) DEFAULT NULL,
  `BODY` longtext,
  `BODY_TYPE` varchar(4) NOT NULL DEFAULT 'html',
  `STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `COMMENTS` text,
  PRIMARY KEY (`ID`),
  KEY `IX_DOCUMENT_ID` (`DOCUMENT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_workflow_move`
--

DROP TABLE IF EXISTS `b_workflow_move`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_workflow_move` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DOCUMENT_ID` int(18) DEFAULT NULL,
  `IBLOCK_ELEMENT_ID` int(18) DEFAULT NULL,
  `OLD_STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `LOG_ID` int(18) DEFAULT NULL,
  `USER_ID` int(18) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IX_DOCUMENT_ID` (`DOCUMENT_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_workflow_preview`
--

DROP TABLE IF EXISTS `b_workflow_preview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_workflow_preview` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DOCUMENT_ID` int(18) NOT NULL DEFAULT '0',
  `TIMESTAMP_X` datetime DEFAULT NULL,
  `FILENAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_workflow_status`
--

DROP TABLE IF EXISTS `b_workflow_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_workflow_status` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `TIMESTAMP_X` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `C_SORT` int(18) DEFAULT '100',
  `ACTIVE` char(1) NOT NULL DEFAULT 'Y',
  `TITLE` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `IS_FINAL` char(1) NOT NULL DEFAULT 'N',
  `NOTIFY` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_workflow_status2group`
--

DROP TABLE IF EXISTS `b_workflow_status2group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_workflow_status2group` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `STATUS_ID` int(18) NOT NULL DEFAULT '0',
  `GROUP_ID` int(18) NOT NULL DEFAULT '0',
  `PERMISSION_TYPE` int(18) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IX_STATUS_ID` (`STATUS_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_xml_tree`
--

DROP TABLE IF EXISTS `b_xml_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `b_xml_tree` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PARENT_ID` int(11) DEFAULT NULL,
  `LEFT_MARGIN` int(11) DEFAULT NULL,
  `RIGHT_MARGIN` int(11) DEFAULT NULL,
  `DEPTH_LEVEL` int(11) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `VALUE` text,
  `ATTRIBUTES` text,
  PRIMARY KEY (`ID`),
  KEY `ix_b_xml_tree_parent` (`PARENT_ID`),
  KEY `ix_b_xml_tree_left` (`LEFT_MARGIN`)
) ENGINE=MyISAM AUTO_INCREMENT=892 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blog`
--

DROP TABLE IF EXISTS `blog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(300) NOT NULL DEFAULT '',
  `slug` varchar(150) NOT NULL,
  `type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `create_user_id` int(10) unsigned NOT NULL,
  `update_user_id` int(10) unsigned NOT NULL,
  `create_date` int(11) unsigned NOT NULL,
  `update_date` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `create_user_id` (`create_user_id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `update_user_id` (`update_user_id`),
  CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `blog_ibfk_2` FOREIGN KEY (`update_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `description` text,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `code` varchar(100) NOT NULL,
  `description` varchar(300) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(10) unsigned NOT NULL,
  `create_user_id` int(10) unsigned NOT NULL,
  `update_user_id` int(10) unsigned NOT NULL,
  `create_date` int(11) unsigned NOT NULL,
  `update_date` int(11) unsigned NOT NULL,
  `slug` varchar(150) NOT NULL,
  `publish_date` datetime NOT NULL,
  `title` varchar(150) NOT NULL,
  `quote` varchar(300) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `link` varchar(150) NOT NULL DEFAULT '',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `comment_status` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `access_type` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `keywords` varchar(150) NOT NULL DEFAULT '',
  `description` varchar(300) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `comment_status` (`comment_status`),
  KEY `access_type` (`access_type`),
  KEY `create_user_id` (`create_user_id`),
  KEY `update_user_id` (`update_user_id`),
  KEY `blog_id` (`blog_id`),
  CONSTRAINT `post_ibfk_1` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `post_ibfk_2` FOREIGN KEY (`update_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `post_ibfk_3` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `creation_date` datetime NOT NULL,
  `change_date` datetime NOT NULL,
  `first_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `nick_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `birth_date` date DEFAULT NULL,
  `site` varchar(100) NOT NULL DEFAULT '',
  `about` varchar(300) NOT NULL DEFAULT '',
  `location` varchar(150) NOT NULL DEFAULT '',
  `online_status` varchar(150) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL,
  `salt` char(32) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '2',
  `access_level` tinyint(1) NOT NULL DEFAULT '0',
  `last_visit` datetime DEFAULT NULL,
  `registration_date` datetime NOT NULL,
  `registration_ip` varchar(20) NOT NULL,
  `activation_ip` varchar(20) NOT NULL,
  `avatar` varchar(100) DEFAULT NULL,
  `use_gravatar` tinyint(4) NOT NULL DEFAULT '0',
  `activate_key` char(32) NOT NULL,
  `email_confirm` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_nickname_unique` (`nick_name`),
  UNIQUE KEY `user_email_unique` (`email`),
  KEY `user_status_index` (`status`),
  KEY `email_confirm` (`email_confirm`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wiki_page`
--

DROP TABLE IF EXISTS `wiki_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wiki_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_redirect` tinyint(1) DEFAULT '0',
  `page_uid` varchar(255) DEFAULT NULL,
  `namespace` varchar(255) DEFAULT NULL,
  `content` text,
  `revision_id` int(11) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wiki_idx_page_revision_id` (`revision_id`),
  UNIQUE KEY `wiki_idx_page_page_uid` (`page_uid`,`namespace`),
  KEY `wiki_idx_page_namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'gb_mirr'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-08-30 13:41:10
