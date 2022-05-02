-- Adminer 4.7.8 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`id`, `name`, `surname`, `login`, `password`, `role`) VALUES
(1,	'Admin',	'',	'administrator',	'$2a$12$O8mw19xx8q2zGFbmzo9.9.Toan/AmxHuopLJLObHoyvB7zakCvKiK',	'superadmin');

DROP TABLE IF EXISTS `alacartecategory`;
CREATE TABLE `alacartecategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img_main` varchar(255) DEFAULT NULL,
  `image_position` tinyint(4) NOT NULL DEFAULT 1,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `is_half` tinyint(4) NOT NULL DEFAULT 0,
  `ordered` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `alacartecategorydictionary`;
CREATE TABLE `alacartecategorydictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `alacartecategory_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `language_id` (`language_id`),
  KEY `alacartecategory_id` (`alacartecategory_id`),
  CONSTRAINT `alacartecategorydictionary_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alacartecategorydictionary_ibfk_2` FOREIGN KEY (`alacartecategory_id`) REFERENCES `alacartecategory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `alacarteitem`;
CREATE TABLE `alacarteitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price` decimal(13,2) NOT NULL DEFAULT 0.00,
  `ordered` int(11) NOT NULL DEFAULT 0,
  `amount` varchar(255) NOT NULL,
  `amount_side_dish` varchar(255) NOT NULL,
  `top` tinyint(4) NOT NULL,
  `hot` tinyint(4) NOT NULL,
  `vegan` tinyint(4) NOT NULL,
  `alacartecategory_id` int(11) NOT NULL,
  `is_active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alacartecategory_id` (`alacartecategory_id`),
  CONSTRAINT `alacarteitem_ibfk_1` FOREIGN KEY (`alacartecategory_id`) REFERENCES `alacartecategory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `alacarteitemallergen`;
CREATE TABLE `alacarteitemallergen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alacarteitem_id` int(11) NOT NULL,
  `allergen_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alacarteitem_id` (`alacarteitem_id`),
  KEY `allergen_id` (`allergen_id`),
  CONSTRAINT `alacarteitemallergen_ibfk_1` FOREIGN KEY (`alacarteitem_id`) REFERENCES `alacarteitem` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alacarteitemallergen_ibfk_2` FOREIGN KEY (`allergen_id`) REFERENCES `allergen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `alacarteitemdictionary`;
CREATE TABLE `alacarteitemdictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alacarteitem_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `language_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alacarteitem_id` (`alacarteitem_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `alacarteitemdictionary_ibfk_1` FOREIGN KEY (`alacarteitem_id`) REFERENCES `alacarteitem` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alacarteitemdictionary_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `alacarteitemvariant`;
CREATE TABLE `alacarteitemvariant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alacarteitem_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `amount_side_dish` varchar(255) NOT NULL,
  `is_active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alacarteitem_id` (`alacarteitem_id`),
  CONSTRAINT `alacarteitemvariant_ibfk_1` FOREIGN KEY (`alacarteitem_id`) REFERENCES `alacarteitem` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `alacarteitemvariantdictionary`;
CREATE TABLE `alacarteitemvariantdictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alacarteitemvariant_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alacarteitemvariant_id` (`alacarteitemvariant_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `alacarteitemvariantdictionary_ibfk_1` FOREIGN KEY (`alacarteitemvariant_id`) REFERENCES `alacarteitemvariant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alacarteitemvariantdictionary_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `allergen`;
CREATE TABLE `allergen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(11) NOT NULL,
  `ordered` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `allergendictionary`;
CREATE TABLE `allergendictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_slovak_ci DEFAULT NULL,
  `language_id` int(11) NOT NULL,
  `allergen_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language_id` (`language_id`),
  KEY `allergen_id` (`allergen_id`),
  CONSTRAINT `allergendictionary_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE,
  CONSTRAINT `allergendictionary_ibfk_2` FOREIGN KEY (`allergen_id`) REFERENCES `allergen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovak_ci;


DROP TABLE IF EXISTS `banner`;
CREATE TABLE `banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordered` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `image_secondary` varchar(255) DEFAULT NULL,
  `content_position` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `bannerdictionary`;
CREATE TABLE `bannerdictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `button_text` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language_id` (`language_id`),
  KEY `banner_id` (`banner_id`),
  CONSTRAINT `bannerdictionary_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bannerdictionary_ibfk_2` FOREIGN KEY (`banner_id`) REFERENCES `banner` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordered` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallerytype` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `gallerydictionary`;
CREATE TABLE `gallerydictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `gallery_id` (`gallery_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `gallerydictionary_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gallerydictionary_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `galleryphoto`;
CREATE TABLE `galleryphoto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `img_original` varchar(255) DEFAULT NULL,
  `img_thumb` varchar(255) DEFAULT NULL,
  `ordered` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_id` (`gallery_id`),
  CONSTRAINT `galleryphoto_ibfk_2` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `galleryvideo`;
CREATE TABLE `galleryvideo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `protectpersonalinfo` tinyint(4) NOT NULL DEFAULT 0,
  `gallery_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_id` (`gallery_id`),
  CONSTRAINT `galleryvideo_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `language`;
CREATE TABLE `language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `webname` varchar(255) DEFAULT NULL,
  `shortcode` varchar(100) DEFAULT NULL,
  `ordered` int(11) DEFAULT 0,
  `is_active` tinyint(1) unsigned zerofill NOT NULL DEFAULT 0,
  `is_default` tinyint(1) unsigned zerofill NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `language` (`id`, `name`, `webname`, `shortcode`, `ordered`, `is_active`, `is_default`) VALUES
(1,	'Slovenčina',	'Slovenčina',	'sk',	1,	1,	1),
(2,	'Angličtina',	'English',	'en',	0,	1,	0);

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `showfrom` date DEFAULT NULL,
  `showto` date DEFAULT NULL,
  `popup` int(11) NOT NULL DEFAULT 0,
  `is_visible` int(11) NOT NULL DEFAULT 1,
  `img_head` varchar(255) DEFAULT NULL,
  `img_popup` varchar(255) DEFAULT NULL,
  `is_top` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `newsdictionary`;
CREATE TABLE `newsdictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `perex` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `language_id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language_id` (`language_id`),
  KEY `news_id` (`news_id`),
  CONSTRAINT `newsdictionary_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE,
  CONSTRAINT `newsdictionary_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `newsgallery`;
CREATE TABLE `newsgallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `gallery_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `gallery_id` (`gallery_id`),
  CONSTRAINT `newsgallery_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `newsgallery_ibfk_2` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `seosettings`;
CREATE TABLE `seosettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `adminname` varchar(255) NOT NULL,
  `ogpimage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `seosettings` (`id`, `section`, `adminname`, `ogpimage`) VALUES
(1,	'uvod',	'Úvod',	''),
(2,	'blog',	'Blog',	''),
(6,	'obecne',	'Obecné nastavenie pre prípad nevyplnenej sekcie',	'');

DROP TABLE IF EXISTS `seosettingsdictionary`;
CREATE TABLE `seosettingsdictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seosettings_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seosettings_id` (`seosettings_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `seosettingsdictionary_ibfk_1` FOREIGN KEY (`seosettings_id`) REFERENCES `seosettings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seosettingsdictionary_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `seosettingsdictionary` (`id`, `seosettings_id`, `language_id`, `title`, `description`) VALUES
(1,	1,	1,	'',	''),
(2,	1,	2,	'',	''),
(3,	2,	1,	'',	''),
(4,	2,	2,	'',	''),
(11,	6,	1,	'',	'\r\n'),
(12,	6,	2,	'',	'');

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `maplink` text DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `mo` varchar(255) DEFAULT NULL,
  `tu` varchar(255) DEFAULT NULL,
  `we` varchar(255) DEFAULT NULL,
  `th` varchar(255) DEFAULT NULL,
  `fr` varchar(255) DEFAULT NULL,
  `sa` varchar(255) DEFAULT NULL,
  `su` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`id`, `email`, `phone`, `name`, `address`, `maplink`, `facebook`, `instagram`, `mo`, `tu`, `we`, `th`, `fr`, `sa`, `su`) VALUES
(1,	'',	'',	'',	'',	'',	'',	'',	'11:00 - 22:00',	'11:00 - 22:00',	'11:00 - 22:00',	'11:00 - 22:00',	'11:00 - 23:00',	'11:00 - 23:00',	'11:00 - 22:00');

DROP TABLE IF EXISTS `spaceoffer`;
CREATE TABLE `spaceoffer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordered` int(11) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `spaceofferdictionary`;
CREATE TABLE `spaceofferdictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spaceoffer_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `spaceoffer_id` (`spaceoffer_id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `spaceofferdictionary_ibfk_1` FOREIGN KEY (`spaceoffer_id`) REFERENCES `spaceoffer` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spaceofferdictionary_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `spaceoffergallery`;
CREATE TABLE `spaceoffergallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spaceoffer_id` int(11) NOT NULL,
  `gallery_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `spaceoffer_id` (`spaceoffer_id`),
  KEY `gallery_id` (`gallery_id`),
  CONSTRAINT `spaceoffergallery_ibfk_1` FOREIGN KEY (`spaceoffer_id`) REFERENCES `spaceoffer` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spaceoffergallery_ibfk_2` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `translation`;
CREATE TABLE `translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `placeholder` varchar(255) NOT NULL,
  `translation` text DEFAULT NULL,
  `locale` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2022-05-02 11:06:40
