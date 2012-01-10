-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Хост: localhost:3309
-- Время создания: Ноя 19 2011 г., 19:51
-- Версия сервера: 5.1.45
-- Версия PHP: 5.2.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `unicat_old`
--

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_categories`
--

DROP TABLE IF EXISTS `unicat_old_categories`;
CREATE TABLE IF NOT EXISTS `unicat_old_categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uri_part` varchar(255) NOT NULL,
  `is_inheritance` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Включает записи вложенных категорий.',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'ID пользователя владельца категории',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  PRIMARY KEY (`category_id`,`entity_id`,`site_id`),
  UNIQUE KEY `pid-uri_part` (`pid`,`uri_part`,`entity_id`,`site_id`),
  KEY `pos` (`pos`,`entity_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Древовидная структура категорий' AUTO_INCREMENT=20 ;

--
-- Дамп данных таблицы `unicat_old_categories`
--

INSERT INTO `unicat_old_categories` (`category_id`, `entity_id`, `site_id`, `is_active`, `pos`, `pid`, `uri_part`, `is_inheritance`, `owner_id`, `create_datetime`) VALUES
(1, 1, 1, 1, 0, 0, '', 1, 1, '0000-00-00 00:00:00'),
(1, 2, 1, 1, 0, 0, '1', 1, 1, '0000-00-00 00:00:00'),
(4, 2, 1, 1, 0, 1, 'bikes', 0, 1, '0000-00-00 00:00:00'),
(5, 2, 1, 1, 2, 1, 'auto', 1, 1, '0000-00-00 00:00:00'),
(6, 2, 1, 1, 1, 4, 'mtb', 0, 1, '0000-00-00 00:00:00'),
(7, 2, 1, 1, 2, 6, 'sss', 1, 1, '0000-00-00 00:00:00'),
(8, 2, 1, 1, 3, 6, '8', 1, 1, '0000-00-00 00:00:00'),
(9, 2, 1, 1, 4, 6, 'gt', 1, 1, '0000-00-00 00:00:00'),
(10, 2, 1, 1, 0, 7, 'scale', 1, 1, '0000-00-00 00:00:00'),
(11, 2, 1, 1, 5, 6, 'merida', 1, 1, '0000-00-00 00:00:00'),
(12, 2, 1, 1, 0, 5, 'russian', 1, 1, '0000-00-00 00:00:00'),
(13, 2, 1, 1, 1, 5, 'american', 1, 1, '0000-00-00 00:00:00'),
(14, 2, 1, 1, 2, 5, 'japan', 1, 1, '0000-00-00 00:00:00'),
(15, 2, 1, 1, 0, 13, 'ford', 1, 1, '0000-00-00 00:00:00'),
(16, 2, 1, 1, 0, 13, 'jeep', 1, 1, '0000-00-00 00:00:00'),
(17, 2, 1, 1, 0, 12, 'uaz', 1, 1, '0000-00-00 00:00:00'),
(18, 2, 1, 1, 0, 14, 'toyota', 1, 1, '0000-00-00 00:00:00'),
(19, 2, 1, 1, 0, 14, 'nissan', 1, 1, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_categories_properties`
--

DROP TABLE IF EXISTS `unicat_old_categories_properties`;
CREATE TABLE IF NOT EXISTS `unicat_old_categories_properties` (
  `category_id` int(10) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `pos` smallint(5) unsigned NOT NULL,
  `type` enum('string','text','date','datetime','img','file','select','number') NOT NULL DEFAULT 'string',
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Свойства категорий.';

--
-- Дамп данных таблицы `unicat_old_categories_properties`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_categories_translation`
--

DROP TABLE IF EXISTS `unicat_old_categories_translation`;
CREATE TABLE IF NOT EXISTS `unicat_old_categories_translation` (
  `category_id` int(11) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `meta` text COMMENT 'Мета-данные',
  PRIMARY KEY (`category_id`,`entity_id`,`language_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий категорий';

--
-- Дамп данных таблицы `unicat_old_categories_translation`
--

INSERT INTO `unicat_old_categories_translation` (`category_id`, `entity_id`, `site_id`, `language_id`, `title`, `meta`) VALUES
(4, 2, 1, 'ru', 'Велосипеды', 'a:1:{s:11:"description";s:37:"Каталог велосипедов";}'),
(5, 2, 1, 'ru', 'Автомобили', 'a:1:{s:11:"description";s:37:"Каталог автомобилей";}'),
(6, 2, 1, 'ru', 'MTB', NULL),
(7, 2, 1, 'ru', 'Scott', NULL),
(8, 2, 1, 'ru', 'Author', NULL),
(9, 2, 1, 'ru', 'GT', NULL),
(10, 2, 1, 'ru', 'Scale', NULL),
(11, 2, 1, 'ru', 'Merida', NULL),
(12, 2, 1, 'ru', 'Русские', NULL),
(13, 2, 1, 'ru', 'Американские', NULL),
(14, 2, 1, 'ru', 'Японские', NULL),
(15, 2, 1, 'ru', 'Ford', NULL),
(16, 2, 1, 'ru', 'Jeep', NULL),
(17, 2, 1, 'ru', 'УАЗ', NULL),
(18, 2, 1, 'ru', 'Toyota', NULL),
(19, 2, 1, 'ru', 'Nissan', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_entities`
--

DROP TABLE IF EXISTS `unicat_old_entities`;
CREATE TABLE IF NOT EXISTS `unicat_old_entities` (
  `entity_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `pos` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL COMMENT 'Служебное имя',
  `media_collection_id` int(10) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(2) NOT NULL DEFAULT 'ru' COMMENT '??? зачем это тут?',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`entity_id`,`site_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Логические экземпляры каталогов' AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `unicat_old_entities`
--

INSERT INTO `unicat_old_entities` (`entity_id`, `site_id`, `is_active`, `pos`, `name`, `media_collection_id`, `language_id`, `create_datetime`, `owner_id`) VALUES
(1, 1, 1, 0, 'news2', 1, 'ru', '0000-00-00 00:00:00', 1),
(2, 1, 1, 1, 'cat2', 0, 'ru', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_entities_translation`
--

DROP TABLE IF EXISTS `unicat_old_entities_translation`;
CREATE TABLE IF NOT EXISTS `unicat_old_entities_translation` (
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`entity_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий экземпляров каталогов';

--
-- Дамп данных таблицы `unicat_old_entities_translation`
--

INSERT INTO `unicat_old_entities_translation` (`entity_id`, `site_id`, `language_id`, `title`) VALUES
(1, 1, 'ru', 'Новости'),
(2, 1, 'ru', 'Каталог 2');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items`
--

DROP TABLE IF EXISTS `unicat_old_items`;
CREATE TABLE IF NOT EXISTS `unicat_old_items` (
  `item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `uri_part` varchar(100) NOT NULL,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'ID пользователя владельца записи',
  `create_datetime` datetime DEFAULT NULL COMMENT 'Время создания записи',
  `meta` text COMMENT 'Мета-данные',
  PRIMARY KEY (`item_id`,`entity_id`,`site_id`),
  UNIQUE KEY `uri_part` (`uri_part`,`entity_id`,`site_id`),
  KEY `is_active` (`is_active`,`entity_id`,`site_id`),
  KEY `is_deleted` (`is_deleted`,`entity_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Записи элементов каталога' AUTO_INCREMENT=41 ;

--
-- Дамп данных таблицы `unicat_old_items`
--

INSERT INTO `unicat_old_items` (`item_id`, `entity_id`, `site_id`, `is_active`, `is_deleted`, `uri_part`, `owner_id`, `create_datetime`, `meta`) VALUES
(29, 1, 1, 1, 0, 'first', 1, '2011-05-16 08:05:05', NULL),
(30, 1, 1, 1, 0, 'second', 1, '2011-05-16 11:29:26', NULL),
(31, 1, 1, 1, 0, '3rd', 1, '2011-06-10 22:11:36', NULL),
(32, 1, 1, 1, 0, '4th', 1, '2011-07-05 00:32:39', 'a:1:{s:11:"description";s:48:"Четвертая тестовая запись";}'),
(33, 2, 1, 1, 0, '60_2008', 1, '2011-07-06 20:50:53', 'a:1:{s:11:"description";s:22:"Scott Scale 602 (2008)";}'),
(34, 2, 1, 1, 0, 'expedition', 1, '2011-07-07 00:21:40', NULL),
(35, 2, 1, 1, 1, '35-2011-07-07', 1, '2011-07-07 00:32:31', NULL),
(36, 2, 1, 1, 0, 'explorer', 1, '2011-07-07 00:53:31', NULL),
(37, 2, 1, 1, 0, 'cherokee', 1, '2011-07-07 00:53:45', NULL),
(38, 1, 1, 1, 0, 'russia', 1, '2011-08-28 18:51:29', NULL),
(39, 2, 1, 1, 1, 'ltd', 1, '2011-09-01 01:23:10', NULL),
(40, 1, 1, 1, 0, '40yiv', 1, '2011-09-18 21:28:24', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_categories_relation`
--

DROP TABLE IF EXISTS `unicat_old_items_categories_relation`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_categories_relation` (
  `item_id` bigint(20) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`item_id`,`entity_id`,`site_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Связи записей и категорий.';

--
-- Дамп данных таблицы `unicat_old_items_categories_relation`
--

INSERT INTO `unicat_old_items_categories_relation` (`item_id`, `entity_id`, `site_id`, `category_id`) VALUES
(29, 1, 1, 1),
(30, 1, 1, 1),
(31, 1, 1, 1),
(32, 1, 1, 1),
(33, 1, 1, 17),
(33, 2, 1, 10),
(34, 2, 1, 15),
(35, 2, 1, 15),
(36, 2, 1, 15),
(37, 2, 1, 16),
(38, 1, 1, 1),
(39, 2, 1, 10),
(40, 1, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e1_announce`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e1_announce`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e1_announce` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: text';

--
-- Дамп данных таблицы `unicat_old_items_s1_e1_announce`
--

INSERT INTO `unicat_old_items_s1_e1_announce` (`item_id`, `value`) VALUES
(29, 'Анонс первой'),
(30, 'Анонс второй..'),
(31, '333'),
(32, '11111111 111111111111 1'),
(38, ':)');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e1_datetime`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e1_datetime`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e1_datetime` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_old_items_s1_e1_datetime`
--

INSERT INTO `unicat_old_items_s1_e1_datetime` (`item_id`, `value`) VALUES
(29, '2011-05-16 00:00:00'),
(30, '2011-05-26 01:00:00'),
(31, '2011-06-10 22:11:24'),
(32, '2011-07-05 00:32:33'),
(38, '2011-08-28 18:50:56'),
(40, '2011-09-18 21:27:15');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e1_date_end`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e1_date_end`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e1_date_end` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_old_items_s1_e1_date_end`
--

INSERT INTO `unicat_old_items_s1_e1_date_end` (`item_id`, `value`) VALUES
(29, '2011-05-18 08:04:11'),
(30, '2011-05-26 11:28:02'),
(31, '2011-07-28 22:11:24'),
(38, '2011-08-28 18:50:56'),
(40, '2011-09-18 21:27:15');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e1_date_start`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e1_date_start`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e1_date_start` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_old_items_s1_e1_date_start`
--

INSERT INTO `unicat_old_items_s1_e1_date_start` (`item_id`, `value`) VALUES
(29, '2011-05-16 08:04:11'),
(30, '2011-05-16 11:28:02'),
(31, '2011-06-14 22:11:24'),
(38, '2011-08-28 18:50:56'),
(40, '2011-09-18 21:27:15');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e1_img_thumb`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e1_img_thumb`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e1_img_thumb` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: img';

--
-- Дамп данных таблицы `unicat_old_items_s1_e1_img_thumb`
--

INSERT INTO `unicat_old_items_s1_e1_img_thumb` (`item_id`, `value`) VALUES
(29, 35),
(30, 38),
(32, 39);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e1_text`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e1_text`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e1_text` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: text';

--
-- Дамп данных таблицы `unicat_old_items_s1_e1_text`
--

INSERT INTO `unicat_old_items_s1_e1_text` (`item_id`, `value`) VALUES
(29, 'ТЕкст первой'),
(32, 'asd a asdf');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e1_title`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e1_title`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e1_title` (
  `item_id` bigint(20) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Заголовок';

--
-- Дамп данных таблицы `unicat_old_items_s1_e1_title`
--

INSERT INTO `unicat_old_items_s1_e1_title` (`item_id`, `value`) VALUES
(31, '33331 :)'),
(32, '4 :)'),
(40, 'ааа'),
(30, 'Вторая'),
(29, 'Первая 22'),
(38, 'Россия');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e2_attach_file`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e2_attach_file`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e2_attach_file` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: file';

--
-- Дамп данных таблицы `unicat_old_items_s1_e2_attach_file`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e2_derailleur_rear`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e2_derailleur_rear`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e2_derailleur_rear` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_old_items_s1_e2_derailleur_rear`
--

INSERT INTO `unicat_old_items_s1_e2_derailleur_rear` (`item_id`, `value`) VALUES
(33, 'Shimano LX'),
(39, '');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e2_descr`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e2_descr`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e2_descr` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_old_items_s1_e2_descr`
--

INSERT INTO `unicat_old_items_s1_e2_descr` (`item_id`, `value`) VALUES
(35, ''),
(36, 'фыв фв фыв фыв'),
(39, '');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e2_engine_volume`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e2_engine_volume`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e2_engine_volume` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` int(10) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: int';

--
-- Дамп данных таблицы `unicat_old_items_s1_e2_engine_volume`
--

INSERT INTO `unicat_old_items_s1_e2_engine_volume` (`item_id`, `value`) VALUES
(36, 4000),
(37, 4500),
(34, 5400);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_items_s1_e2_title`
--

DROP TABLE IF EXISTS `unicat_old_items_s1_e2_title`;
CREATE TABLE IF NOT EXISTS `unicat_old_items_s1_e2_title` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_old_items_s1_e2_title`
--

INSERT INTO `unicat_old_items_s1_e2_title` (`item_id`, `value`) VALUES
(33, 'Scott Scale 60 (2008)'),
(34, 'Экспедишн'),
(35, 'Эксплорер'),
(36, 'Explorer'),
(37, 'Cherokee'),
(39, 'Limited');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_properties`
--

DROP TABLE IF EXISTS `unicat_old_properties`;
CREATE TABLE IF NOT EXISTS `unicat_old_properties` (
  `property_id` int(10) NOT NULL AUTO_INCREMENT,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `properties_group_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Обязателен для заполнения.',
  `show_in_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отображать в списке администратора',
  `show_in_list` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Отображать в списке записей',
  `show_in_view` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Отображать при просмотре записи',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT 'Служебное имя',
  `type` enum('string','text','date','datetime','img','file','select','multiselect','int','double','checkbox','password') NOT NULL DEFAULT 'string',
  `params` text COMMENT 'Валидаторы',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Отображаемое имя',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`property_id`,`entity_id`,`site_id`),
  UNIQUE KEY `name` (`name`,`entity_id`,`site_id`),
  KEY `show_in_list` (`show_in_list`,`entity_id`,`site_id`),
  KEY `show_in_view` (`show_in_view`,`entity_id`,`site_id`),
  KEY `property_group_id` (`properties_group_id`,`entity_id`,`site_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Поля записей' AUTO_INCREMENT=31 ;

--
-- Дамп данных таблицы `unicat_old_properties`
--

INSERT INTO `unicat_old_properties` (`property_id`, `entity_id`, `site_id`, `is_active`, `pos`, `properties_group_id`, `is_required`, `show_in_admin`, `show_in_list`, `show_in_view`, `name`, `type`, `params`, `create_datetime`, `owner_id`) VALUES
(1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 'title', 'string', '', '0000-00-00 00:00:00', 1),
(14, 1, 1, 1, 4, 1, 1, 0, 0, 0, 'date_start', 'datetime', '', '0000-00-00 00:00:00', 1),
(15, 1, 1, 1, 1, 1, 0, 0, 1, 0, 'img_thumb', 'img', 'a:2:{s:5:"width";i:90;s:6:"height";i:90;}', '0000-00-00 00:00:00', 1),
(16, 1, 1, 1, 5, 1, 1, 0, 0, 0, 'date_end', 'datetime', '', '0000-00-00 00:00:00', 1),
(17, 1, 1, 1, 6, 1, 0, 0, 1, 0, 'announce', 'text', '', '0000-00-00 00:00:00', 1),
(18, 1, 1, 1, 7, 1, 0, 0, 0, 1, 'text', 'text', '', '0000-00-00 00:00:00', 1),
(20, 1, 1, 1, 3, 1, 1, 0, 1, 1, 'datetime', 'datetime', '', '0000-00-00 00:00:00', 1),
(26, 2, 1, 1, 1, 3, 1, 1, 1, 1, 'title', 'string', NULL, '0000-00-00 00:00:00', 1),
(27, 2, 1, 1, 2, 3, 0, 0, 0, 1, 'descr', 'string', NULL, '0000-00-00 00:00:00', 1),
(28, 2, 1, 1, 3, 5, 0, 0, 0, 1, 'engine_volume', 'int', '', '0000-00-00 00:00:00', 1),
(29, 2, 1, 1, 4, 4, 0, 0, 0, 1, 'derailleur_rear', 'string', NULL, '0000-00-00 00:00:00', 1),
(30, 2, 1, 1, 5, 3, 0, 0, 0, 1, 'attach_file', 'file', NULL, '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_properties_groups`
--

DROP TABLE IF EXISTS `unicat_old_properties_groups`;
CREATE TABLE IF NOT EXISTS `unicat_old_properties_groups` (
  `properties_group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`properties_group_id`,`entity_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Группы полей' AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `unicat_old_properties_groups`
--

INSERT INTO `unicat_old_properties_groups` (`properties_group_id`, `entity_id`, `site_id`, `pos`, `name`) VALUES
(1, 1, 1, 0, 'news'),
(3, 2, 1, 1, 'main'),
(4, 2, 1, 2, 'bikes'),
(5, 2, 1, 3, 'auto');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_properties_groups_category_relation`
--

DROP TABLE IF EXISTS `unicat_old_properties_groups_category_relation`;
CREATE TABLE IF NOT EXISTS `unicat_old_properties_groups_category_relation` (
  `properties_group_id` smallint(5) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`properties_group_id`,`category_id`,`entity_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Привязка групп полей к категориям';

--
-- Дамп данных таблицы `unicat_old_properties_groups_category_relation`
--

INSERT INTO `unicat_old_properties_groups_category_relation` (`properties_group_id`, `category_id`, `entity_id`, `site_id`) VALUES
(1, 1, 1, 1),
(3, 1, 2, 1),
(4, 1, 2, 1),
(5, 5, 2, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_properties_groups_translation`
--

DROP TABLE IF EXISTS `unicat_old_properties_groups_translation`;
CREATE TABLE IF NOT EXISTS `unicat_old_properties_groups_translation` (
  `properties_group_id` smallint(5) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`properties_group_id`,`entity_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий групп полей';

--
-- Дамп данных таблицы `unicat_old_properties_groups_translation`
--

INSERT INTO `unicat_old_properties_groups_translation` (`properties_group_id`, `entity_id`, `site_id`, `language_id`, `title`) VALUES
(1, 1, 1, 'ru', 'Новости'),
(3, 2, 1, 'ru', 'Основные свойства'),
(4, 2, 1, 'ru', 'Велосипеды'),
(5, 2, 1, 'ru', 'Автомобили');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_properties_translation`
--

DROP TABLE IF EXISTS `unicat_old_properties_translation`;
CREATE TABLE IF NOT EXISTS `unicat_old_properties_translation` (
  `property_id` int(10) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`property_id`,`entity_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий полей';

--
-- Дамп данных таблицы `unicat_old_properties_translation`
--

INSERT INTO `unicat_old_properties_translation` (`property_id`, `entity_id`, `site_id`, `language_id`, `title`) VALUES
(0, 1, 1, 'ru', 'test2'),
(1, 1, 1, 'ru', 'Заголовок'),
(14, 1, 1, 'ru', 'Дата начала'),
(15, 1, 1, 'ru', 'Картинка'),
(16, 1, 1, 'ru', 'Дата окончания'),
(17, 1, 1, 'ru', 'Аннотация'),
(18, 1, 1, 'ru', 'Полный текст'),
(19, 1, 1, 'ru', 'Важная новость'),
(20, 1, 1, 'ru', 'Дата'),
(26, 2, 1, 'ru', 'Название'),
(27, 2, 1, 'ru', 'Описание'),
(28, 2, 1, 'ru', 'Объём двигателя'),
(29, 2, 1, 'ru', 'Задний переключатель'),
(30, 2, 1, 'ru', 'Прикреслённый файл');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_properties_units_zzz`
--

DROP TABLE IF EXISTS `unicat_old_properties_units_zzz`;
CREATE TABLE IF NOT EXISTS `unicat_old_properties_units_zzz` (
  `property_id` int(10) NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `language_id` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `unit` varchar(100) NOT NULL,
  PRIMARY KEY (`property_id`,`entity_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Единицы измерения полей';

--
-- Дамп данных таблицы `unicat_old_properties_units_zzz`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_tags`
--

DROP TABLE IF EXISTS `unicat_old_tags`;
CREATE TABLE IF NOT EXISTS `unicat_old_tags` (
  `tag_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `tags_group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL COMMENT 'Для использоваться как часть URI',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`tag_id`,`entity_id`,`site_id`),
  UNIQUE KEY `name` (`name`,`entity_id`,`site_id`),
  KEY `pos` (`pos`,`entity_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Тэги' AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `unicat_old_tags`
--

INSERT INTO `unicat_old_tags` (`tag_id`, `entity_id`, `site_id`, `tags_group_id`, `pos`, `name`, `create_datetime`, `owner_id`) VALUES
(1, 2, 1, 1, 1, 'russia', '0000-00-00 00:00:00', 1),
(2, 2, 1, 1, 2, 'usa', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_tags_groups`
--

DROP TABLE IF EXISTS `unicat_old_tags_groups`;
CREATE TABLE IF NOT EXISTS `unicat_old_tags_groups` (
  `tags_group_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `pos` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(40) NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`tags_group_id`,`entity_id`,`site_id`,`name`),
  KEY `pos` (`pos`,`entity_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Группы тэгов' AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `unicat_old_tags_groups`
--

INSERT INTO `unicat_old_tags_groups` (`tags_group_id`, `entity_id`, `site_id`, `pos`, `name`, `create_datetime`, `owner_id`) VALUES
(1, 2, 1, 1, 'cloud', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_tags_groups_category_relation`
--

DROP TABLE IF EXISTS `unicat_old_tags_groups_category_relation`;
CREATE TABLE IF NOT EXISTS `unicat_old_tags_groups_category_relation` (
  `tags_group_id` mediumint(8) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tags_group_id`,`entity_id`,`site_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Привязка групп тэгов к категориям';

--
-- Дамп данных таблицы `unicat_old_tags_groups_category_relation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_tags_groups_translation`
--

DROP TABLE IF EXISTS `unicat_old_tags_groups_translation`;
CREATE TABLE IF NOT EXISTS `unicat_old_tags_groups_translation` (
  `tags_group_id` mediumint(8) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(10) NOT NULL DEFAULT 'ru',
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы групп тэгов';

--
-- Дамп данных таблицы `unicat_old_tags_groups_translation`
--

INSERT INTO `unicat_old_tags_groups_translation` (`tags_group_id`, `entity_id`, `site_id`, `language_id`, `title`) VALUES
(1, 2, 1, 'ru', 'Облако тегов');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_tags_items_relation`
--

DROP TABLE IF EXISTS `unicat_old_tags_items_relation`;
CREATE TABLE IF NOT EXISTS `unicat_old_tags_items_relation` (
  `item_id` bigint(20) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `tag_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`item_id`,`tag_id`,`entity_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Привязка тэгов к записям';

--
-- Дамп данных таблицы `unicat_old_tags_items_relation`
--

INSERT INTO `unicat_old_tags_items_relation` (`item_id`, `entity_id`, `site_id`, `tag_id`) VALUES
(33, 2, 1, 1),
(33, 2, 1, 2),
(34, 2, 1, 1),
(36, 2, 1, 1),
(36, 2, 1, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_old_tags_translation`
--

DROP TABLE IF EXISTS `unicat_old_tags_translation`;
CREATE TABLE IF NOT EXISTS `unicat_old_tags_translation` (
  `tag_id` smallint(5) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(2) NOT NULL DEFAULT 'ru',
  `title` varchar(100) NOT NULL COMMENT 'Заголовок категории',
  PRIMARY KEY (`tag_id`,`language_id`,`entity_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы заголовков тэгов';

--
-- Дамп данных таблицы `unicat_old_tags_translation`
--

INSERT INTO `unicat_old_tags_translation` (`tag_id`, `entity_id`, `site_id`, `language_id`, `title`) VALUES
(1, 2, 1, 'ru', 'Россия'),
(2, 2, 1, 'ru', 'США');
