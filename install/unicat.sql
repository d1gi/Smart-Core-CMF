-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Хост: localhost:3309
-- Время создания: Янв 11 2012 г., 04:06
-- Версия сервера: 5.1.45
-- Версия PHP: 5.2.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `unicat`
--

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_categories_s1_e1_rubrics`
--

DROP TABLE IF EXISTS `unicat_categories_s1_e1_rubrics`;
CREATE TABLE IF NOT EXISTS `unicat_categories_s1_e1_rubrics` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uri_part` varchar(255) NOT NULL,
  `is_inheritance` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Включает записи вложенных категорий.',
  `title` varchar(255) NOT NULL,
  `meta` text COMMENT 'Мета-данные',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'ID пользователя владельца категории',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `pid-uri_part` (`pid`,`uri_part`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Древовидная структура категорий' AUTO_INCREMENT=8 ;

--
-- Дамп данных таблицы `unicat_categories_s1_e1_rubrics`
--

INSERT INTO `unicat_categories_s1_e1_rubrics` (`category_id`, `is_active`, `pos`, `pid`, `uri_part`, `is_inheritance`, `title`, `meta`, `owner_id`, `create_datetime`) VALUES
(1, 1, 0, 0, 'mass_media', 1, 'СМИ', NULL, 1, '0000-00-00 00:00:00'),
(2, 1, 0, 1, 'periodicals', 1, 'Периодика', NULL, 1, '0000-00-00 00:00:00'),
(3, 1, 0, 7, 'newspapers', 1, 'Газеты', NULL, 1, '0000-00-00 00:00:00'),
(4, 1, 0, 0, 'auto', 1, 'Авто', NULL, 1, '0000-00-00 00:00:00'),
(5, 1, 0, 4, 'sale', 1, 'Продажа автомобилей', NULL, 1, '0000-00-00 00:00:00'),
(6, 1, 0, 4, 'motorcicles', 1, 'Мотоциклы', NULL, 1, '2011-10-19 09:01:01'),
(7, 1, 0, 1, 'magazines', 1, 'Журналы', NULL, 1, '2011-10-20 06:29:04');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_categories_s1_e1_rubrics_properties`
--

DROP TABLE IF EXISTS `unicat_categories_s1_e1_rubrics_properties`;
CREATE TABLE IF NOT EXISTS `unicat_categories_s1_e1_rubrics_properties` (
  `category_id` int(10) unsigned NOT NULL,
  `pos` smallint(5) unsigned NOT NULL,
  `type` enum('string','text','date','datetime','img','file','number') NOT NULL DEFAULT 'string' COMMENT '@ может и вообще ненадо тип? просто значение текстовое заполнять и всё?',
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`category_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Свойства категорий.';

--
-- Дамп данных таблицы `unicat_categories_s1_e1_rubrics_properties`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_categories_s1_e1_rubrics_translation`
--

DROP TABLE IF EXISTS `unicat_categories_s1_e1_rubrics_translation`;
CREATE TABLE IF NOT EXISTS `unicat_categories_s1_e1_rubrics_translation` (
  `category_id` int(11) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `meta` text COMMENT 'Мета-данные',
  PRIMARY KEY (`category_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@по уомолчанию можно не создават:Переводы названий категорий';

--
-- Дамп данных таблицы `unicat_categories_s1_e1_rubrics_translation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_categories_s2_e3_subscribe_rubrics`
--

DROP TABLE IF EXISTS `unicat_categories_s2_e3_subscribe_rubrics`;
CREATE TABLE IF NOT EXISTS `unicat_categories_s2_e3_subscribe_rubrics` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uri_part` varchar(255) NOT NULL,
  `is_inheritance` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Включает записи вложенных категорий.',
  `title` varchar(255) NOT NULL,
  `meta` text,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'ID пользователя владельца категории',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `pid-uri_part` (`pid`,`uri_part`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `unicat_categories_s2_e3_subscribe_rubrics`
--

INSERT INTO `unicat_categories_s2_e3_subscribe_rubrics` (`category_id`, `is_active`, `pos`, `pid`, `uri_part`, `is_inheritance`, `title`, `meta`, `owner_id`, `create_datetime`) VALUES
(1, 1, 0, 0, 'news', 1, 'Новости', NULL, 1, '2011-12-07 15:19:18'),
(2, 1, 0, 0, 'announcements', 1, 'Анонсы', NULL, 1, '2011-12-07 15:20:22');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_entities`
--

DROP TABLE IF EXISTS `unicat_entities`;
CREATE TABLE IF NOT EXISTS `unicat_entities` (
  `entity_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `name` varchar(100) NOT NULL COMMENT 'Служебное имя',
  `title` varchar(50) NOT NULL COMMENT 'Имя экземпляра',
  `structures` text COMMENT 'Список подключенных структур',
  `is_inheritance` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Включает записи вложенных категорий.',
  `media_collection_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'Медиаколлекция по умолчанию',
  `language_id` varchar(8) NOT NULL DEFAULT 'ru' COMMENT 'Основной язык',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`entity_id`,`site_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Логические экземпляры каталогов' AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `unicat_entities`
--

INSERT INTO `unicat_entities` (`entity_id`, `site_id`, `is_active`, `name`, `title`, `structures`, `is_inheritance`, `media_collection_id`, `language_id`, `create_datetime`, `owner_id`) VALUES
(1, 1, 1, 'catalog_adv', 'Для тестов', 'a:3:{i:0;a:8:{s:2:"id";i:1;s:4:"name";s:6:"rubric";s:5:"table";s:31:"unicat_categories_s1_e1_rubrics";s:7:"entries";s:1:"1";s:5:"descr";s:14:"Рубрики";s:3:"pos";s:1:"1";s:15:"create_datetime";s:19:"2011-10-19 07:34:54";s:7:"reqired";s:1:"0";}i:1;a:8:{s:2:"id";i:3;s:4:"name";s:3:"geo";s:5:"table";s:25:"unicat_structure_geo_base";s:7:"entries";s:6:"single";s:5:"descr";s:14:"Регионы";s:3:"pos";s:1:"3";s:15:"create_datetime";s:19:"2011-10-19 07:39:51";s:7:"reqired";s:1:"1";}i:2;a:8:{s:2:"id";i:4;s:4:"name";s:4:"tags";s:5:"table";s:11:"unicat_tags";s:7:"reqired";s:1:"0";s:7:"entries";s:5:"multi";s:5:"descr";s:4:"Tags";s:3:"pos";s:1:"4";s:15:"create_datetime";s:19:"2011-11-30 11:31:51";}}', 1, 0, 'ru', '0000-00-00 00:00:00', 1),
(2, 1, 1, 'news', 'Новости', 'a:0:{}', 1, 0, 'ru', '2011-12-03 22:56:12', 1),
(3, 2, 1, 'subscribe', 'Рассылки', 'a:1:{i:0;a:8:{s:2:"id";i:1;s:4:"name";s:17:"subscribe_rubrics";s:5:"table";s:41:"unicat_categories_s2_e3_subscribe_rubrics";s:7:"reqired";s:1:"0";s:7:"entries";s:5:"multi";s:5:"descr";s:31:"Рубрики рассылок";s:3:"pos";s:1:"1";s:15:"create_datetime";s:19:"2011-12-07 03:18:39";}}', 1, 0, 'ru', '2011-12-07 14:39:19', 1),
(5, 1, 1, 'subscribe', 'Рассылки', 'a:0:{}', 1, 0, 'ru', '2012-01-10 07:37:38', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items`
--

DROP TABLE IF EXISTS `unicat_items`;
CREATE TABLE IF NOT EXISTS `unicat_items` (
  `item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `uri_part` varchar(100) NOT NULL,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'ID пользователя владельца записи',
  `create_datetime` datetime DEFAULT NULL COMMENT 'Время создания записи',
  `modify_datetime` datetime DEFAULT NULL COMMENT 'Дата последнего изменения',
  `meta` text COMMENT 'Мета-данные',
  PRIMARY KEY (`item_id`,`entity_id`,`site_id`),
  UNIQUE KEY `uri_part` (`uri_part`,`entity_id`,`site_id`),
  KEY `is_active` (`is_active`,`entity_id`,`site_id`),
  KEY `is_deleted` (`is_deleted`,`entity_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Записи элементов каталога' AUTO_INCREMENT=15 ;

--
-- Дамп данных таблицы `unicat_items`
--

INSERT INTO `unicat_items` (`item_id`, `entity_id`, `site_id`, `is_active`, `is_deleted`, `uri_part`, `owner_id`, `create_datetime`, `modify_datetime`, `meta`) VALUES
(1, 1, 1, 1, 0, 'russia', 1, '2011-10-19 04:31:40', NULL, NULL),
(2, 1, 1, 1, 0, 'ukr', 1, '2011-10-19 04:41:37', NULL, NULL),
(3, 1, 1, 1, 0, 'test', 1, '2011-10-20 04:50:48', NULL, NULL),
(4, 1, 1, 1, 0, 'yut', 1, '2011-10-20 06:58:11', NULL, NULL),
(5, 1, 1, 1, 0, 'avto2', 1, '2011-10-20 07:28:08', NULL, NULL),
(6, 2, 1, 1, 0, 'first', 1, '2011-12-03 22:35:56', NULL, NULL),
(7, 2, 1, 1, 0, '7', 1, '2011-12-03 22:39:11', NULL, NULL),
(8, 2, 1, 1, 0, '8', 1, '2011-12-04 23:36:06', NULL, NULL),
(9, 2, 1, 1, 0, '9', 1, '2011-12-05 02:48:07', NULL, NULL),
(10, 2, 1, 1, 0, '10', 1, '2011-12-05 03:23:46', NULL, NULL),
(11, 5, 1, 1, 0, '11', 1, '2012-01-10 10:16:45', NULL, NULL),
(12, 5, 1, 1, 0, '12', 1, '2012-01-10 10:57:41', NULL, NULL),
(13, 5, 1, 1, 1, '13', 1, '2012-01-10 11:00:14', NULL, NULL),
(14, 5, 1, 1, 0, '14', 1, '2012-01-11 04:03:52', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e1_descr`
--

DROP TABLE IF EXISTS `unicat_items_s1_e1_descr`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e1_descr` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_items_s1_e1_descr`
--

INSERT INTO `unicat_items_s1_e1_descr` (`item_id`, `value`) VALUES
(1, ':)'),
(3, '123123123123123');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e1_img`
--

DROP TABLE IF EXISTS `unicat_items_s1_e1_img`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e1_img` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: img';

--
-- Дамп данных таблицы `unicat_items_s1_e1_img`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e1_model`
--

DROP TABLE IF EXISTS `unicat_items_s1_e1_model`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e1_model` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_items_s1_e1_model`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e1_title`
--

DROP TABLE IF EXISTS `unicat_items_s1_e1_title`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e1_title` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_items_s1_e1_title`
--

INSERT INTO `unicat_items_s1_e1_title` (`item_id`, `value`) VALUES
(1, 'Россия 2 (СМИ)'),
(2, 'Украина (СМИ)'),
(3, 'testttt 2 (Газеты, НСО)'),
(4, 'Юный техник (Журналы, ЗапСиб)'),
(5, 'Мото (Мотоциклы)');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e2_announce`
--

DROP TABLE IF EXISTS `unicat_items_s1_e2_announce`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e2_announce` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: text';

--
-- Дамп данных таблицы `unicat_items_s1_e2_announce`
--

INSERT INTO `unicat_items_s1_e2_announce` (`item_id`, `value`) VALUES
(6, 'Первая новость ^)'),
(7, 'Вторая новость'),
(8, 'третья');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e2_datetime`
--

DROP TABLE IF EXISTS `unicat_items_s1_e2_datetime`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e2_datetime` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_items_s1_e2_datetime`
--

INSERT INTO `unicat_items_s1_e2_datetime` (`item_id`, `value`) VALUES
(6, '2011-12-03 22:34:20'),
(7, '2011-12-03 22:38:45'),
(8, '2011-12-04 23:35:06'),
(9, '2011-12-05 02:47:55'),
(10, '2011-12-05 03:23:37');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e2_date_end`
--

DROP TABLE IF EXISTS `unicat_items_s1_e2_date_end`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e2_date_end` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_items_s1_e2_date_end`
--

INSERT INTO `unicat_items_s1_e2_date_end` (`item_id`, `value`) VALUES
(6, NULL),
(7, NULL),
(9, NULL),
(10, NULL),
(8, '2011-12-22 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e2_date_start`
--

DROP TABLE IF EXISTS `unicat_items_s1_e2_date_start`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e2_date_start` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_items_s1_e2_date_start`
--

INSERT INTO `unicat_items_s1_e2_date_start` (`item_id`, `value`) VALUES
(7, NULL),
(8, NULL),
(9, NULL),
(10, NULL),
(6, '2011-12-16 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e2_img`
--

DROP TABLE IF EXISTS `unicat_items_s1_e2_img`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e2_img` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: img';

--
-- Дамп данных таблицы `unicat_items_s1_e2_img`
--

INSERT INTO `unicat_items_s1_e2_img` (`item_id`, `value`) VALUES
(6, 60),
(8, 63),
(10, 64);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e2_text`
--

DROP TABLE IF EXISTS `unicat_items_s1_e2_text`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e2_text` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: text';

--
-- Дамп данных таблицы `unicat_items_s1_e2_text`
--

INSERT INTO `unicat_items_s1_e2_text` (`item_id`, `value`) VALUES
(9, 'Четвертая');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e2_title`
--

DROP TABLE IF EXISTS `unicat_items_s1_e2_title`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e2_title` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_items_s1_e2_title`
--

INSERT INTO `unicat_items_s1_e2_title` (`item_id`, `value`) VALUES
(6, 'Первая'),
(7, 'Вторая'),
(8, 'Третья'),
(9, 'Четвертая'),
(10, 'Пятая 1');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e5_auto_start_datetime`
--

DROP TABLE IF EXISTS `unicat_items_s1_e5_auto_start_datetime`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e5_auto_start_datetime` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_items_s1_e5_auto_start_datetime`
--

INSERT INTO `unicat_items_s1_e5_auto_start_datetime` (`item_id`, `value`) VALUES
(11, '2012-01-19 00:00:00'),
(12, '2012-01-21 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e5_status`
--

DROP TABLE IF EXISTS `unicat_items_s1_e5_status`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e5_status` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: select';

--
-- Дамп данных таблицы `unicat_items_s1_e5_status`
--

INSERT INTO `unicat_items_s1_e5_status` (`item_id`, `value`) VALUES
(11, 'draft'),
(12, 'draft'),
(13, 'draft'),
(14, 'draft');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e5_subject`
--

DROP TABLE IF EXISTS `unicat_items_s1_e5_subject`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e5_subject` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_items_s1_e5_subject`
--

INSERT INTO `unicat_items_s1_e5_subject` (`item_id`, `value`) VALUES
(11, 'Рассылка 1'),
(12, 'Последние новости 2'),
(13, '78786897689689'),
(14, '3333333 2');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s1_e5_text`
--

DROP TABLE IF EXISTS `unicat_items_s1_e5_text`;
CREATE TABLE IF NOT EXISTS `unicat_items_s1_e5_text` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: text';

--
-- Дамп данных таблицы `unicat_items_s1_e5_text`
--

INSERT INTO `unicat_items_s1_e5_text` (`item_id`, `value`) VALUES
(11, '<p>Тема: &laquo;Сублимированный рейтинг в XXI веке&raquo;</p>\r\n<p>Взаимодействие корпорации и клиента амбивалентно. Агентская комиссия специфицирует мониторинг активности, используя опыт предыдущих кампаний. Ассортиментная политика предприятия развивает стратегический маркетинг, используя опыт предыдущих кампаний. Более того, взаимодействие корпорации и клиента искажает бренд, расширяя долю рынка.<br /><br /><br /></p>'),
(12, 'Опросная анкета упорядочивает из ряда вон выходящий портрет потребителя, учитывая результат предыдущих медиа-кампаний. Спонсорство, в рамках сегодняшних воззрений, однородно стабилизирует принцип восприятия, используя опыт предыдущих кампаний. Узнавание бренда осмысленно переворачивает повторный контакт, признавая определенные рыночные тенденции. Стимулирование сбыта амбивалентно.<br /><br />'),
(13, '56897689');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s2_e3_auto_start_datetime`
--

DROP TABLE IF EXISTS `unicat_items_s2_e3_auto_start_datetime`;
CREATE TABLE IF NOT EXISTS `unicat_items_s2_e3_auto_start_datetime` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_items_s2_e3_auto_start_datetime`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s2_e3_modify_datetime`
--

DROP TABLE IF EXISTS `unicat_items_s2_e3_modify_datetime`;
CREATE TABLE IF NOT EXISTS `unicat_items_s2_e3_modify_datetime` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: datetime';

--
-- Дамп данных таблицы `unicat_items_s2_e3_modify_datetime`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s2_e3_status`
--

DROP TABLE IF EXISTS `unicat_items_s2_e3_status`;
CREATE TABLE IF NOT EXISTS `unicat_items_s2_e3_status` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: select';

--
-- Дамп данных таблицы `unicat_items_s2_e3_status`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s2_e3_subject`
--

DROP TABLE IF EXISTS `unicat_items_s2_e3_subject`;
CREATE TABLE IF NOT EXISTS `unicat_items_s2_e3_subject` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: string';

--
-- Дамп данных таблицы `unicat_items_s2_e3_subject`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_s2_e3_text`
--

DROP TABLE IF EXISTS `unicat_items_s2_e3_text`;
CREATE TABLE IF NOT EXISTS `unicat_items_s2_e3_text` (
  `item_id` bigint(20) unsigned NOT NULL,
  `value` text,
  PRIMARY KEY (`item_id`),
  KEY `value` (`value`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: text';

--
-- Дамп данных таблицы `unicat_items_s2_e3_text`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_structures_relation`
--

DROP TABLE IF EXISTS `unicat_items_structures_relation`;
CREATE TABLE IF NOT EXISTS `unicat_items_structures_relation` (
  `item_id` int(20) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `structure_id` tinyint(3) NOT NULL DEFAULT '0',
  `category_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`,`entity_id`,`structure_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Связи записей с категориями структур (для фильтров)';

--
-- Дамп данных таблицы `unicat_items_structures_relation`
--

INSERT INTO `unicat_items_structures_relation` (`item_id`, `entity_id`, `structure_id`, `category_id`) VALUES
(1, 1, 0, 0),
(1, 1, 1, 1),
(1, 1, 1, 7),
(1, 1, 3, 1),
(1, 1, 3, 2),
(1, 1, 3, 3),
(2, 1, 0, 0),
(2, 1, 1, 1),
(3, 1, 1, 1),
(3, 1, 1, 3),
(3, 1, 1, 7),
(3, 1, 3, 1),
(3, 1, 3, 2),
(4, 1, 0, 0),
(4, 1, 1, 1),
(4, 1, 1, 7),
(4, 1, 3, 1),
(5, 1, 0, 0),
(5, 1, 1, 4),
(5, 1, 1, 6),
(6, 2, 0, 0),
(7, 2, 0, 0),
(8, 2, 0, 0),
(9, 2, 0, 0),
(10, 2, 0, 0),
(11, 5, 0, 0),
(12, 5, 0, 0),
(13, 5, 0, 0),
(14, 5, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_items_structures_relation_single`
--

DROP TABLE IF EXISTS `unicat_items_structures_relation_single`;
CREATE TABLE IF NOT EXISTS `unicat_items_structures_relation_single` (
  `item_id` int(20) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `structure_id` tinyint(3) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`item_id`,`entity_id`,`structure_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Связи записей с категориями структур (для учета одиночных)';

--
-- Дамп данных таблицы `unicat_items_structures_relation_single`
--

INSERT INTO `unicat_items_structures_relation_single` (`item_id`, `entity_id`, `structure_id`, `category_id`) VALUES
(1, 1, 1, 7),
(1, 1, 3, 3),
(2, 1, 1, 1),
(2, 1, 3, 0),
(3, 1, 1, 3),
(3, 1, 3, 2),
(4, 1, 1, 7),
(4, 1, 3, 1),
(5, 1, 1, 6),
(5, 1, 3, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_properties`
--

DROP TABLE IF EXISTS `unicat_properties`;
CREATE TABLE IF NOT EXISTS `unicat_properties` (
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
  `empty_as_null` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT 'Служебное имя',
  `title` varchar(255) DEFAULT NULL COMMENT 'Заголовок',
  `type` enum('string','text','date','datetime','img','file','select','multiselect','int','double','checkbox','password') NOT NULL DEFAULT 'string',
  `params` text COMMENT 'Валидаторы',
  `params_yaml` text COMMENT 'Параметры в формате YAML, пока так удобнее будет редактировать.',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Отображаемое имя',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`property_id`,`entity_id`,`site_id`),
  UNIQUE KEY `name` (`name`,`entity_id`,`site_id`),
  KEY `show_in_list` (`show_in_list`,`entity_id`,`site_id`),
  KEY `show_in_view` (`show_in_view`,`entity_id`,`site_id`),
  KEY `property_group_id` (`properties_group_id`,`entity_id`,`site_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Поля записей' AUTO_INCREMENT=29 ;

--
-- Дамп данных таблицы `unicat_properties`
--

INSERT INTO `unicat_properties` (`property_id`, `entity_id`, `site_id`, `is_active`, `pos`, `properties_group_id`, `is_required`, `show_in_admin`, `show_in_list`, `show_in_view`, `empty_as_null`, `name`, `title`, `type`, `params`, `params_yaml`, `create_datetime`, `owner_id`) VALUES
(1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 0, 'title', 'Заголовок', 'string', 'a:0:{}', '', '2011-10-19 03:32:47', 1),
(2, 1, 1, 1, 2, 1, 0, 0, 0, 1, 0, 'descr', 'Описание', 'string', 'a:0:{}', '', '2011-10-19 03:33:15', 1),
(4, 1, 1, 1, 3, 2, 0, 0, 1, 1, 0, 'model', 'Модель', 'string', 'a:0:{}', '', '2011-10-19 04:20:08', 1),
(5, 1, 1, 1, 4, 1, 0, 0, 1, 1, 0, 'img', 'Картинка', 'img', 'a:0:{}', '', '2011-11-29 03:17:24', 1),
(6, 2, 1, 1, 1, 3, 1, 1, 1, 1, 0, 'title', 'Заголовок', 'string', 'a:0:{}', '', '2011-12-03 22:56:12', 1),
(7, 2, 1, 1, 1, 3, 1, 1, 1, 1, 0, 'datetime', 'Дата', 'datetime', 'a:1:{s:7:"default";s:8:"datetime";}', 'default: datetime', '2011-12-03 22:56:13', 1),
(8, 2, 1, 1, 2, 3, 0, 1, 0, 0, 1, 'date_start', 'Дата начала', 'datetime', 'a:0:{}', '', '2011-12-03 22:56:13', 1),
(9, 2, 1, 1, 3, 3, 0, 1, 0, 0, 1, 'date_end', 'Дата окончания', 'datetime', 'a:0:{}', '', '2011-12-03 22:56:13', 1),
(10, 2, 1, 1, 4, 3, 0, 0, 1, 1, 0, 'announce', 'Аннотация', 'text', 'a:0:{}', '', '2011-12-03 22:56:13', 1),
(11, 2, 1, 1, 5, 3, 0, 0, 0, 1, 0, 'text', 'Полный текст', 'text', 'a:0:{}', '', '2011-12-03 22:56:13', 1),
(12, 2, 1, 1, 0, 3, 0, 0, 1, 1, 0, 'img', 'Картинка', 'img', 'a:2:{s:5:"width";s:2:"90";s:6:"height";s:2:"90";}', 'width: 90\r\nheight: 90', '2011-12-03 23:09:01', 1),
(14, 3, 2, 1, 0, 4, 1, 1, 1, 1, 0, 'subject', 'Тема выпуска', 'string', 'a:0:{}', '', '2011-12-07 14:39:19', 1),
(15, 3, 2, 1, 1, 4, 1, 1, 1, 1, 0, 'status', 'Статус', 'select', 'a:3:{s:7:"options";a:4:{s:5:"draft";s:16:"Черновик";s:10:"in_process";s:36:"В процессе рассылки";s:7:"stopped";s:20:"Остановлен";s:8:"finished";s:16:"Завершен";}s:7:"default";s:5:"draft";s:8:"disabled";b:1;}', 'options:\n  draft: Черновик\n  in_process: В процессе рассылки\n  stopped: Остановлен\n  finished: Завершен\ndefault: draft\ndisabled: true\n', '2011-12-07 14:39:19', 1),
(16, 3, 2, 1, 2, 4, 1, 1, 1, 1, 0, 'modify_datetime', 'Дата последнего изменения', 'datetime', 'a:1:{s:8:"disabled";b:1;}', 'disabled: true\n', '2011-12-07 14:39:19', 1),
(17, 3, 2, 1, 3, 4, 1, 1, 1, 1, 0, 'auto_start_datetime', 'Дата начала рассылки', 'datetime', 'a:0:{}', '', '2011-12-07 14:39:19', 1),
(18, 3, 2, 1, 4, 4, 0, 0, 0, 1, 0, 'text', 'Полный текст', 'text', 'a:0:{}', '', '2011-12-07 14:39:19', 1),
(24, 5, 1, 1, 0, 10, 1, 1, 1, 1, 0, 'subject', 'Тема выпуска', 'string', 'a:0:{}', '', '2012-01-10 07:37:38', 1),
(25, 5, 1, 1, 1, 10, 1, 1, 1, 1, 0, 'status', 'Статус', 'select', 'a:4:{s:7:"options";a:4:{s:5:"draft";s:16:"Черновик";s:10:"in_process";s:36:"В процессе рассылки";s:7:"stopped";s:20:"Остановлен";s:8:"finished";s:16:"Завершен";}s:7:"default";s:5:"draft";s:8:"disabled";s:1:"1";s:8:"readonly";s:1:"0";}', 'options:\r\n  draft: Черновик\r\n  in_process: В процессе рассылки\r\n  stopped: Остановлен\r\n  finished: Завершен\r\ndefault: draft\r\ndisabled: 1\r\nreadonly: 0', '2012-01-10 07:37:39', 1),
(27, 5, 1, 1, 3, 10, 1, 1, 1, 1, 0, 'auto_start_datetime', 'Дата начала рассылки', 'datetime', 'a:0:{}', '', '2012-01-10 07:37:39', 1),
(28, 5, 1, 1, 4, 10, 0, 0, 0, 1, 0, 'text', 'Полный текст', 'text', 'a:0:{}', '', '2012-01-10 07:37:39', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_properties_groups`
--

DROP TABLE IF EXISTS `unicat_properties_groups`;
CREATE TABLE IF NOT EXISTS `unicat_properties_groups` (
  `properties_group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT 'Заголовок',
  PRIMARY KEY (`properties_group_id`,`entity_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Группы полей' AUTO_INCREMENT=11 ;

--
-- Дамп данных таблицы `unicat_properties_groups`
--

INSERT INTO `unicat_properties_groups` (`properties_group_id`, `entity_id`, `site_id`, `pos`, `name`, `title`) VALUES
(1, 1, 1, 1, 'main', 'Основные свойства'),
(2, 1, 1, 2, 'bikes', 'Велосипеды'),
(3, 2, 1, 1, 'news', 'Новости'),
(4, 3, 2, 1, 'releases', 'Выпуски рассылок'),
(10, 5, 1, 1, 'releases', 'Выпуски рассылок');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_properties_groups_structures_relation`
--

DROP TABLE IF EXISTS `unicat_properties_groups_structures_relation`;
CREATE TABLE IF NOT EXISTS `unicat_properties_groups_structures_relation` (
  `properties_group_id` smallint(5) unsigned NOT NULL,
  `structure_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `category_id` int(10) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`properties_group_id`,`category_id`,`entity_id`,`site_id`,`structure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Привязка групп полей к структурам';

--
-- Дамп данных таблицы `unicat_properties_groups_structures_relation`
--

INSERT INTO `unicat_properties_groups_structures_relation` (`properties_group_id`, `structure_id`, `category_id`, `entity_id`, `site_id`) VALUES
(1, 0, 0, 1, 1),
(2, 0, 0, 1, 1),
(3, 0, 0, 2, 1),
(4, 0, 0, 3, 2),
(5, 0, 0, 4, 1),
(6, 0, 0, 4, 1),
(7, 0, 0, 4, 1),
(8, 0, 0, 4, 1),
(9, 0, 0, 4, 1),
(10, 0, 0, 5, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_properties_translation`
--

DROP TABLE IF EXISTS `unicat_properties_translation`;
CREATE TABLE IF NOT EXISTS `unicat_properties_translation` (
  `property_id` int(10) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`property_id`,`entity_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий полей';

--
-- Дамп данных таблицы `unicat_properties_translation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `unicat_structure_geo_base`
--

DROP TABLE IF EXISTS `unicat_structure_geo_base`;
CREATE TABLE IF NOT EXISTS `unicat_structure_geo_base` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uri_part` varchar(255) NOT NULL,
  `is_inheritance` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Включает записи вложенных категорий.',
  `title` varchar(255) NOT NULL,
  `meta` text,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'ID пользователя владельца категории',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `pid-uri_part` (`pid`,`uri_part`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='! ТЕСТОВАЯ! не брать за основу!' AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `unicat_structure_geo_base`
--

INSERT INTO `unicat_structure_geo_base` (`category_id`, `is_active`, `pos`, `pid`, `uri_part`, `is_inheritance`, `title`, `meta`, `owner_id`, `create_datetime`) VALUES
(1, 1, 0, 0, 'zapsib', 1, 'Западная Сибирь', NULL, 1, '0000-00-00 00:00:00'),
(2, 1, 0, 1, 'nso', 1, 'НСО', NULL, 1, '0000-00-00 00:00:00'),
(3, 1, 0, 2, 'nsk', 1, 'Новосибирск', NULL, 1, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `unicat_tags`
--

DROP TABLE IF EXISTS `unicat_tags`;
CREATE TABLE IF NOT EXISTS `unicat_tags` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uri_part` varchar(255) NOT NULL,
  `is_inheritance` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Включает записи вложенных категорий.',
  `title` varchar(255) NOT NULL,
  `meta` text,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'ID пользователя владельца категории',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `pid-uri_part` (`pid`,`uri_part`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `unicat_tags`
--


-- --------------------------------------------------------

--
-- Структура таблицы `z_unicat_entities_translation`
--

DROP TABLE IF EXISTS `z_unicat_entities_translation`;
CREATE TABLE IF NOT EXISTS `z_unicat_entities_translation` (
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`entity_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий экземпляров каталогов';

--
-- Дамп данных таблицы `z_unicat_entities_translation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `z_unicat_properties_groups_translation`
--

DROP TABLE IF EXISTS `z_unicat_properties_groups_translation`;
CREATE TABLE IF NOT EXISTS `z_unicat_properties_groups_translation` (
  `properties_group_id` smallint(5) unsigned NOT NULL,
  `entity_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`properties_group_id`,`entity_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий групп полей';

--
-- Дамп данных таблицы `z_unicat_properties_groups_translation`
--

