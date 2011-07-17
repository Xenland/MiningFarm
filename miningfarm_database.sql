-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 17, 2011 at 04:39 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `miningfarm`
--

-- --------------------------------------------------------

--
-- Table structure for table `accountBalance`
--

CREATE TABLE IF NOT EXISTS `accountBalance` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` int(255) NOT NULL,
  `balance` varchar(10) NOT NULL,
  `payoutAddress` varchar(255) NOT NULL,
  `threshhold` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `blogPosts`
--

CREATE TABLE IF NOT EXISTS `blogPosts` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `timestamp` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `donationList`
--

CREATE TABLE IF NOT EXISTS `donationList` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `locked` int(1) NOT NULL,
  `display` varchar(255) NOT NULL,
  `bitcoinAddress` varchar(255) NOT NULL,
  `coinAddresstype` int(3) NOT NULL COMMENT '1=Bitcoin donation address; 2 = namecoind donation address',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `menuAddition`
--

CREATE TABLE IF NOT EXISTS `menuAddition` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `displayTitle` varchar(255) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `matches` varchar(1000) NOT NULL COMMENT 'This is for matching file names',
  `requireLogin` int(1) NOT NULL,
  `requireAdmin` int(1) NOT NULL,
  `order` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `networkBlocks`
--

CREATE TABLE IF NOT EXISTS `networkBlocks` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `blockNumber` int(255) NOT NULL,
  `timestamp` int(255) NOT NULL,
  `txid` varchar(255) NOT NULL,
  `confirms` int(255) NOT NULL,
  `orphan` int(1) NOT NULL,
  `serverFeeCollected` int(1) NOT NULL COMMENT 'Lets blockFound.php know that the server fee was collected from this block to prevent multiple server fee collections',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Table structure for table `pool_worker`
--

CREATE TABLE IF NOT EXISTS `pool_worker` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `associatedUserId` int(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE IF NOT EXISTS `shares` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `epochTimestamp` int(50) NOT NULL,
  `rem_host` varchar(255) NOT NULL,
  `username` varchar(120) NOT NULL,
  `our_result` enum('Y','N') NOT NULL,
  `upstream_result` enum('Y','N') DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `solution` varchar(257) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1096 ;

-- --------------------------------------------------------

--
-- Table structure for table `shares_dead`
--

CREATE TABLE IF NOT EXISTS `shares_dead` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `blockNumber` int(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `rem_host` varchar(255) NOT NULL,
  `username` varchar(120) NOT NULL,
  `our_result` enum('Y','N') NOT NULL,
  `upstream_result` enum('Y','N') DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `solution` varchar(257) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `shares_history`
--

CREATE TABLE IF NOT EXISTS `shares_history` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `shareCounted` int(1) NOT NULL,
  `blockNumber` int(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `rem_host` varchar(255) NOT NULL,
  `username` varchar(120) NOT NULL,
  `our_result` enum('Y','N') NOT NULL,
  `upstream_result` enum('Y','N') DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `solution` varchar(257) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3013357 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats_bitcoinConversionHistory`
--

CREATE TABLE IF NOT EXISTS `stats_bitcoinConversionHistory` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `tradehill` varchar(25) NOT NULL,
  `mtgox` int(5) NOT NULL,
  `timestamp` int(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=404 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats_poolMHashHistory`
--

CREATE TABLE IF NOT EXISTS `stats_poolMHashHistory` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `totalMhash` varchar(50) NOT NULL,
  `averageMhash` varchar(20) NOT NULL,
  `totalValidShares` int(255) NOT NULL,
  `timestamp` int(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=957 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats_userMHashHistory`
--

CREATE TABLE IF NOT EXISTS `stats_userMHashHistory` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `mhashes` varchar(20) NOT NULL,
  `efficiency` varchar(5) NOT NULL,
  `timestamp` int(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3477 ;

-- --------------------------------------------------------

--
-- Table structure for table `websiteSettings`
--

CREATE TABLE IF NOT EXISTS `websiteSettings` (
  `noreplyEmail` text NOT NULL,
  `confirmEmailPrefix` text NOT NULL COMMENT 'The text or HTML written email that is sent for email confirmation',
  `browserTitle` varchar(255) NOT NULL,
  `cashoutMinimum` varchar(5) NOT NULL COMMENT 'The minimum balance required before a user can cash out',
  `serverFeePercentage` varchar(20) NOT NULL COMMENT 'Server fee in percents',
  `serverFeeRemoteAddress` varchar(255) NOT NULL COMMENT 'This will house any server fees',
  `serverFeeAccountBalance` varchar(255) NOT NULL,
  `tradeHillWorth` varchar(20) NOT NULL COMMENT 'Current worth of bitcoins',
  `mtgoxWorth` varchar(10) NOT NULL,
  `currencyData` varchar(20) NOT NULL COMMENT 'Three letter identifiers what the general default currency should be set too',
  `stats_showallusers` int(1) NOT NULL COMMENT 'Although not recommended for commerical sites it is very usefull for private pools',
  `enableRequiredEmail` int(1) NOT NULL COMMENT 'Boolean for enabled emails to be authorized',
  `coinType` int(3) NOT NULL COMMENT '1=Bitcoin website; 2 = namecoind website'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
INSERT INTO `websiteSettings` (`noreplyEmail`, `confirmEmailPrefix`, `browserTitle`, `cashoutMinimum`, `serverFeePercentage`, `serverFeeRemoteAddress`, `serverFeeAccountBalance`, `tradeHillWorth`, `mtgoxWorth`, `currencyData`, `stats_showallusers`, `enableRequiredEmail`) VALUES
('noreply@noreply.com', 'Welcome to the Pool, You must activate your account before we can progress you any further. Click the link below and type in your credentials(if necessary) then we should be able to activate your account from there and start mining miners. :)\\n', 'Running Mining Farm By MIningFarm.com', '0.02', '1', '', '0', '13.98', '0', 'tradehill-USD', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `websiteUsers`
--

CREATE TABLE IF NOT EXISTS `websiteUsers` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `isAdmin` int(1) NOT NULL,
  `disabled` int(1) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `randomSecret` varchar(10) NOT NULL COMMENT 'Generated at login, this secret secures cookies when hashing',
  `sessTimestamp` int(255) NOT NULL COMMENT 'Session timestamp for valid cookie checking',
  `loggedIp` varchar(255) NOT NULL COMMENT 'Validating hashed cookies',
  `email` varchar(255) NOT NULL,
  `emailAuthorised` int(1) NOT NULL DEFAULT '0',
  `emailAuthorisePin` varchar(64) NOT NULL,
  `authPin` varchar(255) NOT NULL COMMENT 'A pin that must be supplied when changing details to various things',
  `failedLoginAttempts` int(5) NOT NULL,
  `failedLoginTimestampLock` int(255) NOT NULL COMMENT 'Epoch time until user is allowed access to page',
  `apiToken` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
