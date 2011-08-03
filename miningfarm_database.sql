-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 03, 2011 at 11:31 AM
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `accountBalance`
--

INSERT INTO `accountBalance` (`id`, `userId`, `balance`, `payoutAddress`, `threshhold`) VALUES
(5, 5, '0', '', ''),
(6, 6, '0.00', '', ''),
(7, 7, '0.00', '', ''),
(8, 8, '0.00', '', ''),
(9, 9, '0.00', '', ''),
(10, 10, '0', '', '');

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

--
-- Dumping data for table `blogPosts`
--

INSERT INTO `blogPosts` (`id`, `timestamp`, `title`, `message`) VALUES
(4, 0, 'What we do?', '<div style="float:left;">\r\n<iframe width="360" height="224" src="http://www.youtube.com/embed/Um63OQz3bjo" frameborder="0" allowfullscreen></iframe>\r\n</div>\r\n<div style="padding:1em 1em 1em 1em;">\r\nFirst off welcome to the "Mining Farm" you can run your computer to mine an Internet Commodity known as "Bitcoins". These <i>Bitcoins</i> can be used to purchase many things such as Alpaca socks, Webhosting, or even VOIP phone services, the list goes on.<br/><br/>\r\n\r\nWikipedia explains bitcoins a little bit better by stating: Bitcoin enables rapid payments (and micropayments) at very low cost, and avoids the need for central authorities and issuers. Digitally signed transactions, with one node signing over some amount of the currency to another node, are broadcast to all nodes in a peer-to-peer network. A proof-of-work system is used as measurement against double-spending and initial currency distribution mechanism.<br/>\r\n<a href="http://en.wikipedia.org/wiki/Bitcoin" target="_BLANK">WikiePedia Source</a>\r\n</div>');

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

--
-- Dumping data for table `donationList`
--

INSERT INTO `donationList` (`id`, `locked`, `display`, `bitcoinAddress`, `coinAddresstype`) VALUES
(3, 1, 'Mining Farm (The website software you are using)', '1Fc2ScswXAHPUgj3qzmbRmwWJSLL2yv8Q', 1);

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `menuAddition`
--

INSERT INTO `menuAddition` (`id`, `displayTitle`, `url`, `matches`, `requireLogin`, `requireAdmin`, `order`) VALUES
(1, 'Welcome', '/', 'index.php, /', 0, 0, 1),
(2, 'Account Details', '/accountDetails.php', '/accountDetails.php', 1, 0, 3),
(3, 'Statistics', '/stats.php', '/stats.php', 0, 0, 2),
(4, 'Administration', '/adminPanel.php', '/adminPanel.php', 1, 1, 4),
(5, 'User Privileges', '/adminPanel.php?show=editUsers', '/adminPanel.php?show=editUsers', 1, 1, 5),
(6, 'Workers', '/workers.php', '/workers.php', 1, 0, 7),
(11, 'Blog Editor', '/adminPanel.php?show=blogEditor', '/adminPanel.php?show=blogEditor', 1, 1, 12),
(14, 'Download 5.1.4', 'https://github.com/Xenland/MiningFarm/tarball/master', '', 0, 0, 3),
(15, 'Bug List', 'https://github.com/Xenland/MiningFarm/issues?sort=created&direction=desc&state=open', '', 0, 0, 5),
(16, 'Licensed Farms', '/licensedfarms.php', '/licensedfarms.php', 0, 0, 7);

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
  `rewardsCollected` int(1) NOT NULL COMMENT 'Lets blockFound.php know that we have already rewarded the users from this block.',
  `amount` int(255) NOT NULL COMMENT 'The amount of coins stored in this block',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `networkBlocks`
--


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
  KEY `username` (`username`(6))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `pool_worker`
--


-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE IF NOT EXISTS `shares` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `time` varchar(255) NOT NULL COMMENT 'This is set from pushpoold query',
  `rem_host` varchar(255) NOT NULL,
  `username` varchar(120) NOT NULL,
  `our_result` enum('Y','N') NOT NULL,
  `upstream_result` enum('Y','N') DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `solution` varchar(257) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username_index` (`username`(6))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `shares`
--


-- --------------------------------------------------------

--
-- Table structure for table `shares_dead`
--

CREATE TABLE IF NOT EXISTS `shares_dead` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `blockNumber` int(255) NOT NULL,
  `time` int(255) NOT NULL,
  `rem_host` varchar(255) NOT NULL,
  `username` varchar(120) NOT NULL,
  `our_result` enum('Y','N') NOT NULL,
  `upstream_result` enum('Y','N') DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `solution` varchar(257) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`(6))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `shares_dead`
--


-- --------------------------------------------------------

--
-- Table structure for table `shares_history`
--

CREATE TABLE IF NOT EXISTS `shares_history` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `shareCounted` int(1) NOT NULL,
  `blockNumber` int(255) NOT NULL,
  `time` int(255) NOT NULL,
  `rem_host` varchar(255) NOT NULL,
  `username` varchar(120) NOT NULL,
  `our_result` enum('Y','N') NOT NULL,
  `upstream_result` enum('Y','N') DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `solution` varchar(257) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`(6))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `shares_history`
--


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_bitcoinConversionHistory`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_hitCounter`
--

CREATE TABLE IF NOT EXISTS `stats_hitCounter` (
  `name` varchar(10) NOT NULL COMMENT 'This is the name of the counter, but also used as a reference for quries',
  `hits` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `stats_hitCounter`
--


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_poolMHashHistory`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_topHashers`
--

CREATE TABLE IF NOT EXISTS `stats_topHashers` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` int(255) NOT NULL,
  `totalHashes` int(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_topHashers`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_topSharers`
--

CREATE TABLE IF NOT EXISTS `stats_topSharers` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL,
  `shares` int(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_topSharers`
--


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_userMHashHistory`
--


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
  `serverAccountBalance` varchar(40) NOT NULL COMMENT 'This holds how much money is saved up from fees',
  `serverFeeRemoteAddress` varchar(255) NOT NULL COMMENT 'This will house any server fees',
  `tradeHillWorth` varchar(20) NOT NULL COMMENT 'Current worth of bitcoins',
  `mtgoxWorth` varchar(10) NOT NULL,
  `currencyData` varchar(20) NOT NULL COMMENT 'Three letter identifiers what the general default currency should be set too',
  `enableRequiredEmail` int(1) NOT NULL COMMENT 'Boolean for enabled emails to be authorized',
  `coinType` int(3) NOT NULL COMMENT '1=Bitcoin website; 2 = namecoind website',
  `shares_this_round` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `websiteSettings`
--

INSERT INTO `websiteSettings` (`noreplyEmail`, `confirmEmailPrefix`, `browserTitle`, `cashoutMinimum`, `serverFeePercentage`, `serverAccountBalance`, `serverFeeRemoteAddress`, `tradeHillWorth`, `mtgoxWorth`, `currencyData`, `enableRequiredEmail`, `coinType`, `shares_this_round`) VALUES
('noreply@noreply.com', 'Welcome to the Pool, You must activate your account before we can progress you any further. Click the link below and type in your credentials(if necessary) then we should be able to activate your account from there and start mining miners. :)\\n', 'Mining Farm Official Vendor website', '0.02', '1', '235', '', '11.12', '0', 'btc', 0, 2, 21);

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
  `emailResetToken` int(64) NOT NULL COMMENT 'The token that must be used to reset their password from their email link',
  `authPin` varchar(255) NOT NULL COMMENT 'A pin that must be supplied when changing details to various things',
  `failedLoginAttempts` int(5) NOT NULL,
  `failedLoginTimestampLock` int(255) NOT NULL COMMENT 'Epoch time until user is allowed access to page',
  `apiToken` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `websiteUsers`
--

INSERT INTO `websiteUsers` (`id`, `isAdmin`, `disabled`, `username`, `password`, `randomSecret`, `sessTimestamp`, `loggedIp`, `email`, `emailAuthorised`, `emailAuthorisePin`, `emailResetToken`, `authPin`, `failedLoginAttempts`, `failedLoginTimestampLock`, `apiToken`) VALUES
(5, 0, 0, 'xenland', '6460624c7064ec8f0fbb9495db41a6601276779fe8fc8c6469130b8020209e9d', 'xD1cN4HMBf', 1312385800, '74.38.12.47', 'xennetwork@hotmail.com', 0, '1jhWhG5EOfXJQjIzgvTLyosU53odrKrjTy6141wJ7kjNtSdAdXcCbum6nBaFcrOW', 0, '0ffe1abd1a08215353c233d6e009613e95eec4253832a761af28ff37ac5a150c', 0, 0, 'PKNKC9jzktdDbh3f56I6qV3Dm39pkNb0oPAQPKgZ3kt5snbnjJjzud3H62WhGY7U'),
(6, 0, 0, 'suppp', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', '8JBEhOrB9X', 1311277711, '94.41.78.63', 'suppp@yandex.ru', 0, 'onBRJFa7DQZYGe3ZO99tHsDt0QvhLXJ0abHKGHIaoyYVDSKiRKBo24ITL40nSzdS', 0, '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 0, 0, 'BKt7i18xqWiTET1mttBlo94Z4UcMkguLQOJZGHnWuvFZexbxRDJ5CDVwoY8y4taL'),
(7, 0, 0, 'ziomik', 'b3c6ccf35e36a57a137b8b807ba86154e30ab400e4f5518b53810a852e729380', 'SFvB0vHeKS', 1311281782, '82.60.254.183', 'ziomik@ziomik.net', 0, '5z0yKta83F3yjhXP8epgzY3W8Zvun1njreH2xI0rdUPn2D21Ii7770T5Qfq47Edo', 0, 'dec1ab2f6d6e994cfe718666751e27a22a3c8c2e7c2708b995fb1e021197136e', 0, 0, 'IKh5i7nlS3yLwrC4zz2wqLr7QI1Oc52KF9GN7UZQNoraFU54kXqAzIyfhqUjlNTQ'),
(8, 0, 0, 'trc2', 'f8f1b2826d9ecfc646333bb6ace8a7651ac78952c798f54dae24489e4aaea3ed', 'B2TJz5FV6X', 1311339203, '85.157.196.87', 'test@test.net', 0, 'Zaw1elh3IflwLrFTpjj3Sd1oQD37lgaahx1mI9fgerDQI9zXiJR1NIftc9rngrnn', 0, '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 0, 0, 'OfAneFujXYZwXpk6Y1XBA3VC2cP8u2m97MnciHl5varjpBfet2GTWrlOu0MPTZOR'),
(9, 0, 0, 'monk_funky', 'd77810709b4dbccab62488b1c4282ef9e0a2bbd4736db372efa05fd1ef443746', '18Hv202JrI', 1311799462, '207.134.160.55', 'monk_funky@yahoo.com', 0, 'ixrByagX3hwAzCCgnP2HlwNaSSmTolwwINY6N4THcg8CJAJXgBurY8rGQEq5PMsn', 0, '72d1b5da6eeaf1789df86487da50ad5e9dadb5ffaecb56b6de592aa286c9c1b8', 0, 0, 'pgk3b3BdazGJ0fw6GQnvPF1vahqPTJ39Pd2Q6uU6TqGJv3G2KUnppeLql255BY4g'),
(10, 0, 0, 'bitcoinminer21', '553399b5917103972b28934ad119117dbfc0e162b04a63f453dbe4e3523bf007', 'eJa4MeN3fQ', 1312204687, '76.101.223.214', 'bitcoinminer21@yahoo.com', 0, '6ogOdT4FC0VkXdT5VjNHWZPYs9HLDfyztEdxn82QYO0LRJGDTkaF9QusP23i7sIq', 0, '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 0, 0, 'WLOaJGQxkG92gGwZQxvQdP9TI22FlBW7cA8M7Oail9bsGxhnUD3Yj2IRVBm62944');
