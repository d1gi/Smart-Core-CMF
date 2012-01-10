-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Хост: localhost:3309
-- Время создания: Янв 10 2012 г., 11:09
-- Версия сервера: 5.1.45
-- Версия PHP: 5.2.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `smart_core`
--

-- --------------------------------------------------------

--
-- Структура таблицы `2gis_counter`
--

DROP TABLE IF EXISTS `2gis_counter`;
CREATE TABLE IF NOT EXISTS `2gis_counter` (
  `2gis_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `city` varchar(30) DEFAULT NULL,
  `ip` varchar(40) NOT NULL,
  `browser` varchar(20) NOT NULL,
  `browser_version` varchar(50) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `user_agent` text NOT NULL,
  PRIMARY KEY (`2gis_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `2gis_counter`
--


-- --------------------------------------------------------

--
-- Структура таблицы `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `id` varchar(32) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `element` enum('node_data','node_html','page') NOT NULL COMMENT 'Элемент который кешируется.',
  `valid_to_timestamp` int(10) unsigned NOT NULL COMMENT 'Валидна до даты',
  PRIMARY KEY (`id`,`site_id`,`element`),
  KEY `valid_to_datetime` (`valid_to_timestamp`,`site_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Закешированные элементы';

--
-- Дамп данных таблицы `cache`
--


-- --------------------------------------------------------

--
-- Структура таблицы `cache_relations`
--

DROP TABLE IF EXISTS `cache_relations`;
CREATE TABLE IF NOT EXISTS `cache_relations` (
  `id` varchar(32) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `element` enum('node_data','node_html','page') NOT NULL COMMENT 'Элемент который кешируется.',
  `object` enum('node','folder','page') NOT NULL COMMENT 'Объект от которого записит кеш',
  `object_id` varchar(40) NOT NULL,
  PRIMARY KEY (`id`,`site_id`,`element`,`object`,`object_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Зависимости блоков кеша от объектов системы';

--
-- Дамп данных таблицы `cache_relations`
--


-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Ответ на каментарий',
  `post_id` bigint(20) unsigned NOT NULL COMMENT 'ИД комментируемой записи',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `status` varchar(20) DEFAULT NULL COMMENT 'premoderate, postmoderate, spam, spam_suspicion',
  `user_id` int(10) unsigned NOT NULL,
  `user_nickname` varchar(50) NOT NULL COMMENT 'Псевдоним пользователя',
  `user_email` varchar(100) NOT NULL COMMENT 'e-mail пользователя',
  `user_homepage` varchar(255) DEFAULT NULL COMMENT 'Домашняя страница пользователя',
  `user_ip` varchar(40) NOT NULL,
  `user_agent` varchar(255) NOT NULL COMMENT 'Браузер пользователя',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `update_datetime` datetime DEFAULT NULL COMMENT 'Дата изменения',
  `rating` smallint(6) NOT NULL DEFAULT '0',
  `subject` varchar(100) DEFAULT NULL COMMENT 'Тема комментария',
  `content` text NOT NULL COMMENT 'Текст комментария',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `comments`
--


-- --------------------------------------------------------

--
-- Структура таблицы `engine_components`
--

DROP TABLE IF EXISTS `engine_components`;
CREATE TABLE IF NOT EXISTS `engine_components` (
  `component_id` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `descr` text COMMENT 'Описание',
  PRIMARY KEY (`component_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Компоненты';

--
-- Дамп данных таблицы `engine_components`
--

INSERT INTO `engine_components` (`component_id`, `title`, `descr`) VALUES
('Media', 'Медиа хранилище', 'Хранение файлов на распределённых ресурсах.');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_containers`
--

DROP TABLE IF EXISTS `engine_containers`;
CREATE TABLE IF NOT EXISTS `engine_containers` (
  `container_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pos` smallint(5) NOT NULL DEFAULT '0',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `descr` varchar(100) NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`container_id`,`site_id`),
  UNIQUE KEY `name` (`name`,`site_id`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Контейнеры нод' AUTO_INCREMENT=9 ;

--
-- Дамп данных таблицы `engine_containers`
--

INSERT INTO `engine_containers` (`container_id`, `pos`, `site_id`, `name`, `descr`, `create_datetime`, `owner_id`) VALUES
(1, 1, 1, 'content', 'Рабочая область', '0000-00-00 00:00:00', 1),
(1, 1, 2, 'content', 'Рабочая область', '0000-00-00 00:00:00', 1),
(2, 2, 1, 'v-menu', 'Вертикальное меню', '0000-00-00 00:00:00', 1),
(3, 9, 1, 'footer', 'Нижняя часть (сквозная)', '0000-00-00 00:00:00', 1),
(5, 5, 1, 'auth-light', 'Быстрая авторизация', '0000-00-00 00:00:00', 1),
(6, 6, 1, 'breadcrumbs', 'Хлебные крошки', '0000-00-00 00:00:00', 1),
(7, 0, 2, 'breadcrumbs', 'Хлебные крошки', '0000-00-00 00:00:00', 1),
(8, 2, 1, 'content-right-column', 'Правая колонка в контейнере', '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `engine_containers_inherit`
--

DROP TABLE IF EXISTS `engine_containers_inherit`;
CREATE TABLE IF NOT EXISTS `engine_containers_inherit` (
  `folder_id` int(10) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `container_id` smallint(5) NOT NULL,
  PRIMARY KEY (`folder_id`,`container_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Наследование контейнеров';

--
-- Дамп данных таблицы `engine_containers_inherit`
--

INSERT INTO `engine_containers_inherit` (`folder_id`, `site_id`, `container_id`) VALUES
(1, 1, 2),
(1, 1, 3),
(1, 1, 5),
(1, 1, 6),
(1, 2, 7);

-- --------------------------------------------------------

--
-- Структура таблицы `engine_crontab`
--

DROP TABLE IF EXISTS `engine_crontab`;
CREATE TABLE IF NOT EXISTS `engine_crontab` (
  `task_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL COMMENT 'Заголовок',
  `cron` varchar(50) NOT NULL COMMENT 'Строка cron',
  `tmp_period_in_min` int(11) NOT NULL DEFAULT '60' COMMENT '@ ВРЕМЕННО - Период в минутах для запуска',
  `params` text NOT NULL COMMENT 'Параметры',
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Расписание задач (@ в разработке)' AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `engine_crontab`
--

INSERT INTO `engine_crontab` (`task_id`, `is_active`, `title`, `cron`, `tmp_period_in_min`, `params`) VALUES
(1, 1, 'Почтовая рассылка (Каждую минуту)', '*/5 * * * *', 1, 'a:2:{s:4:"exec";s:5:"class";s:5:"class";s:8:"Maillist";}'),
(2, 1, 'Проверка обновлений ядра', '', 1440, 'a:5:{s:4:"exec";s:4:"zzzz";s:4:"file";s:24:"{DIR_SYSTEM}maillist.php";s:5:"class";s:11:"CheckUpdate";s:7:"node_id";i:0;s:7:"site_id";i:0;}'),
(3, 1, 'Опрос модуля вебформы', '', 2, 'a:3:{s:4:"exec";s:4:"node";s:7:"node_id";i:45;s:7:"site_id";i:1;}');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_database_resources`
--

DROP TABLE IF EXISTS `engine_database_resources`;
CREATE TABLE IF NOT EXISTS `engine_database_resources` (
  `database_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `lib` enum('PDO','Simple') NOT NULL DEFAULT 'PDO' COMMENT 'Используемая библиотека',
  `driver` enum('mysql','mysqli','sqlite','pgsql','oracle') NOT NULL DEFAULT 'mysql' COMMENT 'Тип или адаптер БД',
  `db_host` varchar(40) NOT NULL DEFAULT 'localhost',
  `db_port` mediumint(7) NOT NULL DEFAULT '3306',
  `db_name` varchar(50) NOT NULL,
  `db_user` varchar(50) NOT NULL,
  `db_password` varchar(50) NOT NULL,
  `db_persist` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`database_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Ресурсы к дополнительным базам данных' AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `engine_database_resources`
--

INSERT INTO `engine_database_resources` (`database_id`, `name`, `title`, `lib`, `driver`, `db_host`, `db_port`, `db_name`, `db_user`, `db_password`, `db_persist`) VALUES
(1, 'unicat', 'Каталог', 'PDO', 'mysql', 'localhost', 3309, 'unicat', 'smart_core', '123', 1),
(2, 'unicat_old', 'Новости и каталог на базе unicat OLD!!!', 'PDO', 'mysql', 'localhost', 3309, 'unicat_old', 'smart_core', '123', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `engine_folders`
--

DROP TABLE IF EXISTS `engine_folders`;
CREATE TABLE IF NOT EXISTS `engine_folders` (
  `folder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `pos` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uri_part` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Удалена ли папка? (для истории)',
  `is_file` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'является ли папка файлом',
  `title` varchar(255) NOT NULL,
  `descr` varchar(255) DEFAULT NULL,
  `meta` text,
  `redirect_to` varchar(255) DEFAULT NULL,
  `parser_node_id` int(10) unsigned DEFAULT NULL COMMENT 'Нода, которой передаётся дальнейший парсинг URI ',
  `transmit_nodes` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'В этой папке ЕСТЬ ноды, которые наследуются.',
  `owner_id` int(10) unsigned DEFAULT NULL COMMENT 'id создателя',
  `permissions` text COMMENT 'Права доступа',
  `nodes_blocks` text,
  `layout` varchar(30) DEFAULT NULL COMMENT 'Применяемый макет темы',
  `views` text COMMENT 'Применяемые представления в макете',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  PRIMARY KEY (`folder_id`,`site_id`),
  UNIQUE KEY `pid-uri_part` (`pid`,`uri_part`,`site_id`),
  KEY `pos` (`pos`),
  KEY `is_active` (`is_active`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=1 ROW_FORMAT=COMPACT COMMENT='Папки' AUTO_INCREMENT=58 ;

--
-- Дамп данных таблицы `engine_folders`
--

INSERT INTO `engine_folders` (`folder_id`, `site_id`, `pid`, `pos`, `uri_part`, `is_active`, `is_deleted`, `is_file`, `title`, `descr`, `meta`, `redirect_to`, `parser_node_id`, `transmit_nodes`, `owner_id`, `permissions`, `nodes_blocks`, `layout`, `views`, `create_datetime`) VALUES
(1, 1, 0, 0, '', 1, 0, 0, 'Главная', 'Smart Core CMF', 'a:5:{s:8:"keywords";s:12:"123 ффыв";s:11:"description";s:0:"";s:6:"robots";s:3:"all";s:8:"language";s:5:"ru-RU";s:6:"author";s:10:"Артём";}', '', NULL, 1, 1, NULL, NULL, 'main', 'a:1:{s:7:"content";s:12:"full_content";}', '0000-00-00 00:00:00'),
(1, 2, 0, 0, '', 1, 0, 0, 'Демо сайт', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'main', NULL, '0000-00-00 00:00:00'),
(1, 3, 0, 0, '', 1, 0, 0, 'Smart Core', 'Smart Core', NULL, NULL, NULL, 0, 1, NULL, NULL, 'main', NULL, '0000-00-00 00:00:00'),
(2, 2, 1, 0, 'user', 1, 0, 0, 'Пользователь', NULL, NULL, NULL, NULL, 0, 1, '0|read:1,write:0,view:1;', NULL, NULL, NULL, '0000-00-00 00:00:00'),
(3, 1, 1, 10, 'feedback', 1, 0, 0, 'Обратная связь', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(3, 2, 2, 1, 'login', 0, 0, 0, 'Авторизация', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(4, 2, 2, 2, 'logout', 0, 0, 0, 'Выход из системы', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(5, 1, 1, 100, 'forum', 1, 0, 0, 'Форум', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(5, 2, 2, 0, 'restore', 1, 0, 0, 'Восстановление пароля', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(6, 1, 1, 2, 'downloads', 1, 0, 0, 'Скачать', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(6, 2, 2, 0, 'profile', 0, 0, 0, 'Редактирование профиля', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(7, 2, 2, 0, 'registration', 1, 0, 0, 'Регистрация', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(11, 1, 19, 3, 'screenshots', 1, 0, 0, 'Скриншоты ;)', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(13, 1, 18, 1, 'glossary', 1, 0, 0, 'Глоссарий', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(18, 1, 1, 4, 'documentation', 1, 0, 0, 'Документация', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(19, 1, 1, 2, 'about', 1, 0, 0, 'О системе', '', 'a:1:{s:11:"description";s:17:"О системе";}', '', NULL, 0, 1, NULL, 'a:1:{s:7:"inherit";s:1:"1";}', NULL, 'a:1:{s:7:"content";s:8:"3columns";}', '0000-00-00 00:00:00'),
(20, 1, 19, 1, 'specification', 1, 0, 0, 'Характеристики', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(21, 1, 19, 2, 'license', 1, 0, 0, 'Лицензия', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(22, 1, 1, 80, 'blog', 1, 0, 0, 'Блог', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(23, 1, 57, 1, 'tech', 1, 0, 0, 'В мире технологий', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(28, 1, 55, 202, 'recover', 1, 0, 0, 'Восстановление пароля', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(29, 1, 55, 2, 'registration', 1, 0, 0, 'Регистрация 2', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(30, 1, 55, 3, 'profile', 1, 0, 0, 'Профили', NULL, NULL, '', NULL, 0, 1, '0|read:0,write:0,view:0;', NULL, NULL, NULL, '0000-00-00 00:00:00'),
(31, 1, 18, 2, '31', 0, 0, 0, 'Введение', 'Краткое описание', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(32, 1, 1, 2, 'gallery', 1, 0, 0, 'Фотогалерея', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(33, 1, 20, 1, 'requirements', 1, 0, 0, 'Требования', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(34, 1, 33, 1, 'php', 1, 0, 0, 'php', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(35, 1, 1, 103, 'system2', 1, 0, 0, 'system', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(36, 1, 1, 104, '11', 1, 0, 0, '11', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(39, 2, 1, 1, 'news', 1, 0, 0, 'Новости', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(40, 1, 35, 1, 'Kernel', 1, 0, 0, 'Kernel', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(40, 2, 1, 2, 'subscribe', 1, 0, 0, 'Рассылка', '', NULL, '', 87, 0, 1, NULL, NULL, NULL, NULL, '2011-12-05 10:11:29'),
(41, 1, 57, 2, 'zzz', 1, 0, 0, 'zzz', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(43, 1, 36, 1, '111222222', 1, 0, 0, '111-12', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(45, 1, 1, 1, 'filemanager', 1, 0, 0, 'Файловый менеджер', NULL, NULL, '', 62, 0, 1, '0|read:0,write:0,view:0;2|read:0,write:0,view:0;', 'a:1:{s:6:"except";s:2:"62";}', 'blank', NULL, '0000-00-00 00:00:00'),
(46, 1, 1, 106, 'subscribe', 1, 0, 0, 'Рассылка', NULL, NULL, '', 63, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(47, 1, 1, 3, 'lag', 1, 0, 0, 'lag', NULL, NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(48, 1, 1, 3, 'news_old', 0, 0, 0, 'Новости old', 'Новости на базе нового юниката', 'a:1:{s:11:"description";s:14:"Новости";}', '', NULL, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(50, 1, 1, 1, 'captcha', 1, 0, 0, 'CAPTCHA', NULL, NULL, '', NULL, 0, 1, NULL, 'a:1:{s:6:"except";s:2:"67";}', NULL, NULL, '0000-00-00 00:00:00'),
(51, 1, 1, 1, 'catalog_old', 1, 0, 0, 'Каталог OLD', 'Каталог', 'a:1:{s:11:"description";s:33:"Каталог продукции";}', '', 68, 0, 1, NULL, NULL, NULL, 'a:1:{s:7:"content";s:8:"2columns";}', '0000-00-00 00:00:00'),
(52, 1, 1, 3, 'tag', 1, 0, 0, 'Тэги', NULL, NULL, '', 70, 0, 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00'),
(53, 1, 1, 1, '2gis', 1, 0, 0, '2gis', NULL, NULL, '', NULL, 0, 1, NULL, 'a:1:{s:6:"except";s:2:"73";}', NULL, NULL, '0000-00-00 00:00:00'),
(54, 1, 1, 3, 'zzz2', 1, 0, 0, 'zzz2', '', NULL, '', NULL, 0, 1, NULL, NULL, NULL, NULL, '2011-07-17 19:04:13'),
(55, 1, 1, 1, 'user', 1, 0, 0, 'Аккаунт пользователя', NULL, 'a:1:{s:6:"author";s:6:"digi 2";}', '', NULL, 0, 1, '0|read:1,write:0,view:0;', NULL, NULL, NULL, '2011-09-19 19:32:41'),
(56, 1, 1, 3, 'catalog', 1, 0, 0, 'Каталог', '', 'a:1:{s:8:"keywords";s:14:"каталог";}', '', 83, 0, 1, NULL, NULL, NULL, 'a:1:{s:7:"content";s:8:"2columns";}', '2011-10-14 02:56:28'),
(57, 1, 1, 3, 'news', 1, 0, 0, 'Новости', '', NULL, '', 86, 0, 1, NULL, NULL, NULL, NULL, '2011-12-03 22:49:41');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_folders_translation`
--

DROP TABLE IF EXISTS `engine_folders_translation`;
CREATE TABLE IF NOT EXISTS `engine_folders_translation` (
  `folder_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(8) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `descr` varchar(255) DEFAULT NULL,
  `meta` text COMMENT 'Мета-данные',
  PRIMARY KEY (`folder_id`,`language_id`,`site_id`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы заголовков и описаний папок.';

--
-- Дамп данных таблицы `engine_folders_translation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `engine_languages`
--

DROP TABLE IF EXISTS `engine_languages`;
CREATE TABLE IF NOT EXISTS `engine_languages` (
  `language_id` varchar(8) NOT NULL,
  `pos` tinyint(2) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `uri_part` varchar(10) NOT NULL COMMENT '@todo может не нужно т.е. как ури парт рассматривать ID языка',
  PRIMARY KEY (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Языки';

--
-- Дамп данных таблицы `engine_languages`
--

INSERT INTO `engine_languages` (`language_id`, `pos`, `name`, `uri_part`) VALUES
('en', 2, 'English', 'en'),
('ru', 1, 'Русский', 'ru');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_modules`
--

DROP TABLE IF EXISTS `engine_modules`;
CREATE TABLE IF NOT EXISTS `engine_modules` (
  `module_id` varchar(100) NOT NULL,
  `template` enum('auto','system','user') NOT NULL DEFAULT 'auto' COMMENT '@todo вынести отсюда...',
  `database_id` smallint(5) NOT NULL DEFAULT '0',
  `descr` varchar(255) NOT NULL COMMENT 'Краткое описание модуля',
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Общий список модулей со сводной информацией';

--
-- Дамп данных таблицы `engine_modules`
--

INSERT INTO `engine_modules` (`module_id`, `template`, `database_id`, `descr`) VALUES
('2GisCounter', 'auto', 0, 'Счетчик посещений с 2гис'),
('Breadcrumbs', 'auto', 0, 'Хлебные крошки'),
('Captcha', 'auto', 0, 'CAPTCHA'),
('Catalog', 'auto', 0, 'Каталог для тестирования компонента UnicatAdvanced'),
('CatalogOld', 'auto', 0, 'Каталог'),
('Comments', 'auto', 0, 'Комментарии'),
('Filemanager', 'auto', 0, 'Файловый менеджер'),
('Gallery', 'auto', 0, 'Фотогалерея'),
('GoogleMap', 'auto', 0, 'Карта Google'),
('Menu', 'auto', 0, 'Меню'),
('News', 'user', 0, 'Лента новостей'),
('Reflex', 'auto', 0, 'Вытягивание хуков с модулей и их отображение.'),
('Subscribe', 'auto', 0, 'Подсписка на рассылку'),
('Taxonomy', 'auto', 0, 'Taxonomy'),
('TestHook', 'auto', 0, ''),
('Texter', 'auto', 0, 'Статические тексты.'),
('UserAccount', 'auto', 0, 'Новый модуль по работе с юзерами'),
('UserProfile', 'auto', 0, 'Профиль пользователя'),
('UserRecover', 'auto', 0, 'Восстановление пароля'),
('UserRegistration', 'auto', 0, 'Регистрация пользователя'),
('UserWelcome', 'auto', 0, 'Быстрая авторизация 2'),
('VideoPlayer', 'auto', 0, 'Видеопроигрыватель'),
('WebForm', 'auto', 0, 'Веб-формы');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_modules_local`
--

DROP TABLE IF EXISTS `engine_modules_local`;
CREATE TABLE IF NOT EXISTS `engine_modules_local` (
  `module_id` varchar(100) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `install_datetime` datetime NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`module_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Подключенные к сайтам модули';

--
-- Дамп данных таблицы `engine_modules_local`
--

INSERT INTO `engine_modules_local` (`module_id`, `site_id`, `install_datetime`, `user_id`) VALUES
('Texter', 1, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `engine_modules_permissions_zzz`
--

DROP TABLE IF EXISTS `engine_modules_permissions_zzz`;
CREATE TABLE IF NOT EXISTS `engine_modules_permissions_zzz` (
  `module_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'действие'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Права модулей';

--
-- Дамп данных таблицы `engine_modules_permissions_zzz`
--


-- --------------------------------------------------------

--
-- Структура таблицы `engine_nodes`
--

DROP TABLE IF EXISTS `engine_nodes`;
CREATE TABLE IF NOT EXISTS `engine_nodes` (
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `container_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pos` smallint(5) unsigned NOT NULL DEFAULT '0',
  `module_id` varchar(50) NOT NULL,
  `is_cached` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Может ли эта нода кешироваться.',
  `cache_params` text COMMENT 'Параметры кеширования',
  `cache_params_yaml` text COMMENT '@ ВРЕМЕННО - на этапе разработки',
  `params` text,
  `plugins` text COMMENT 'Плагины',
  `owner_id` int(10) unsigned DEFAULT NULL COMMENT 'ид создателя',
  `permissions` text COMMENT 'Права доступа',
  `database_id` smallint(5) NOT NULL DEFAULT '0',
  `node_action_mode` enum('popup','built-in','ajax') NOT NULL DEFAULT 'popup',
  `descr` varchar(255) DEFAULT NULL COMMENT 'Краткий комментарий о сути ноды',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  PRIMARY KEY (`node_id`,`folder_id`,`site_id`),
  KEY `is_active` (`is_active`),
  KEY `pos` (`pos`),
  KEY `container_id` (`container_id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=89 ;

--
-- Дамп данных таблицы `engine_nodes`
--

INSERT INTO `engine_nodes` (`node_id`, `site_id`, `is_active`, `folder_id`, `container_id`, `pos`, `module_id`, `is_cached`, `cache_params`, `cache_params_yaml`, `params`, `plugins`, `owner_id`, `permissions`, `database_id`, `node_action_mode`, `descr`, `create_datetime`) VALUES
(1, 1, 1, 1, 3, 0, 'Texter', 1, 'a:2:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"7000";}', 'type: html\r\nlifetime: 7000', 'a:2:{s:12:"text_item_id";s:1:"1";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Футер', '0000-00-00 00:00:00'),
(2, 1, 1, 1, 1, 0, 'Texter', 1, 'a:2:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"3000";}', 'type: html\r\nlifetime: 3000\r\n', 'a:2:{s:12:"text_item_id";s:1:"2";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'built-in', 'Текстовка на главной', '0000-00-00 00:00:00'),
(3, 1, 1, 3, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:1:"3";}', NULL, 1, 'u4|read:1,write:1;', 0, 'popup', 'каменты для обратной связи', '0000-00-00 00:00:00'),
(4, 1, 0, 1, 1, 1, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:1:"4";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'какой-то текстер...', '0000-00-00 00:00:00'),
(8, 1, 1, 1, 2, 1, 'Menu', 1, 'a:3:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"3000";s:2:"id";a:2:{s:17:"current_folder_id";s:0:"";s:11:"user_groups";s:0:"";}}', '# Тип кеширования (html или data).\r\ntype: html\r\n# На сколько секунд кешировать.\r\nlifetime: 3000\r\n# Формат (или шаблон) ключа кеша, система автоматически подставит следующие значения.\r\nid:\r\n  # Текущая папка.\r\n  current_folder_id:\r\n  # Список групп, в которые входит юзер.\r\n  user_groups:\r\n', 'a:5:{s:13:"menu_group_id";s:1:"1";s:9:"max_depth";s:1:"3";s:9:"css_class";s:1:"a";s:20:"selected_inheritance";s:1:"1";s:3:"tpl";s:4:"Menu";}', NULL, 1, NULL, 0, 'popup', 'Главное меню', '0000-00-00 00:00:00'),
(9, 1, 1, 18, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:1:"7";}', NULL, 1, '5|read:1,write:1;', 0, 'popup', 'Документация :)', '0000-00-00 00:00:00'),
(11, 1, 0, 1, 3, 2, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:1:"8";}', NULL, 1, NULL, 0, 'popup', 'ПРобоник', '0000-00-00 00:00:00'),
(12, 1, 1, 19, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:1:"9";}', NULL, 1, 'u2|read:1,write:1;', 0, 'popup', 'О системе', '0000-00-00 00:00:00'),
(13, 1, 1, 6, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:2:"10";}', NULL, 1, NULL, 0, 'built-in', 'скачать', '0000-00-00 00:00:00'),
(16, 1, 0, 30, 1, 2, 'UserProfile', 1, NULL, NULL, 'a:2:{s:15:"welcome_node_id";s:2:"17";s:13:"login_node_id";s:2:"10";}', NULL, 1, '3|read:1,write:1;2|read:1,write:1;', 0, 'popup', '', '0000-00-00 00:00:00'),
(22, 1, 1, 29, 1, 2, 'UserRegistration', 0, NULL, NULL, 'a:2:{s:15:"account_node_id";s:2:"79";s:15:"captcha_node_id";s:1:"0";}', NULL, 1, '0|read:1,write:1;', 0, 'popup', '17', '0000-00-00 00:00:00'),
(23, 1, 1, 29, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:2:"12";}', NULL, 1, NULL, 0, 'popup', 'Доп инфа по регистрации', '0000-00-00 00:00:00'),
(24, 1, 1, 30, 1, 1, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:2:"13";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Редактирование личного профиля', '0000-00-00 00:00:00'),
(26, 1, 1, 1, 6, 1, 'Breadcrumbs', 1, NULL, NULL, 'a:2:{s:9:"delimiter";s:2:"»";s:17:"hide_if_only_home";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Хлебные крошки', '0000-00-00 00:00:00'),
(28, 1, 1, 19, 1, 2, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:2:"15";}', NULL, 1, NULL, 0, 'popup', '123', '0000-00-00 00:00:00'),
(29, 1, 1, 19, 1, 3, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:2:"16";}', NULL, 1, NULL, 0, 'popup', 'zxczx', '0000-00-00 00:00:00'),
(30, 1, 1, 1, 1, 2, 'Texter', 1, 'a:2:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"3000";}', 'type: html\r\nlifetime: 3000', 'a:2:{s:12:"text_item_id";s:2:"17";s:6:"editor";s:1:"1";}', NULL, 1, '2|read:1,write:1,view:1;', 0, 'popup', 'Что-то еще на главной :)', '0000-00-00 00:00:00'),
(33, 1, 1, 32, 1, 1, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:2:"18";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Фотогалерея', '0000-00-00 00:00:00'),
(34, 1, 1, 20, 1, 1, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:2:"19";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'specs', '0000-00-00 00:00:00'),
(35, 1, 1, 32, 1, 2, 'Gallery', 1, NULL, NULL, 'a:2:{s:19:"media_collection_id";s:1:"2";s:10:"gallery_id";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Фотогалерея', '0000-00-00 00:00:00'),
(36, 1, 1, 18, 1, 2, 'GoogleMap', 1, NULL, NULL, 'a:5:{s:10:"google_key";s:0:"";s:5:"scale";s:2:"15";s:11:"info_window";s:122:"Новосибирский Государственный Академический Театр Оперы И Балета";s:9:"longitude";s:8:"55.03035";s:8:"latitude";s:8:"82.92444";}', NULL, 1, NULL, 0, 'popup', 'Карта гугле', '0000-00-00 00:00:00'),
(37, 1, 1, 35, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:2:"20";}', NULL, 1, NULL, 0, 'popup', 'asd', '0000-00-00 00:00:00'),
(39, 2, 1, 2, 1, 0, 'UserAccount', 1, NULL, NULL, 'a:5:{s:15:"profile_node_id";s:1:"0";s:15:"recover_node_id";s:1:"0";s:16:"register_node_id";s:2:"42";s:15:"captcha_node_id";s:1:"0";s:13:"enable_openid";s:1:"0";}', NULL, 1, '0|read:1,write:1;2|read:1,write:1;', 0, 'popup', 'Авторизация', '0000-00-00 00:00:00'),
(41, 2, 1, 6, 1, 0, 'UserProfile', 1, NULL, NULL, 'a:2:{s:15:"welcome_node_id";s:2:"39";s:13:"login_node_id";s:2:"38";}', NULL, 1, '3|read:1,write:1;2|read:1,write:1;', 0, 'popup', NULL, '0000-00-00 00:00:00'),
(42, 2, 1, 7, 1, 0, 'UserRegistration', 0, NULL, NULL, 'a:2:{s:15:"account_node_id";s:1:"0";s:15:"captcha_node_id";s:1:"0";}', NULL, 1, '0|read:1,write:1;', 0, 'popup', '', '0000-00-00 00:00:00'),
(43, 2, 1, 1, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";i:21;}', NULL, 1, NULL, 0, 'popup', 'главная', '0000-00-00 00:00:00'),
(44, 2, 1, 1, 7, 1, 'Breadcrumbs', 1, NULL, NULL, 'a:2:{s:9:"delimiter";s:2:"»";s:17:"hide_if_only_home";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Хлебные крошки', '0000-00-00 00:00:00'),
(45, 1, 1, 3, 1, 2, 'WebForm', 0, NULL, NULL, 'a:6:{s:7:"form_id";s:1:"1";s:9:"css_class";s:25:"default-form contact-form";s:8:"email_to";s:15:"artemsg@mail.ru";s:10:"email_from";s:57:"Новые результаты <no-reply@smart-core.org>";s:20:"is_email_immediately";s:1:"1";s:15:"reminder_period";s:4:"1440";}', NULL, 1, '0|read:1,write:1;', 0, 'popup', 'WebForm', '0000-00-00 00:00:00'),
(49, 1, 1, 28, 1, 1, 'UserRecover', 0, NULL, NULL, 'a:2:{s:15:"account_node_id";s:2:"79";s:15:"captcha_node_id";s:1:"0";}', NULL, 1, '0|read:1,write:1;', 0, 'popup', 'Восстановление пароля', '0000-00-00 00:00:00'),
(59, 1, 1, 20, 1, 2, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:2:"30";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'хар21', '0000-00-00 00:00:00'),
(61, 1, 1, 43, 1, 1, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";s:2:"31";}', NULL, 1, NULL, 0, 'popup', '111', '0000-00-00 00:00:00'),
(62, 1, 1, 45, 1, 1, 'Filemanager', 1, NULL, NULL, 'a:1:{s:14:"filemanager_id";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'файл менеджер', '0000-00-00 00:00:00'),
(63, 1, 1, 46, 1, 1, 'Subscribe', 0, NULL, NULL, 'a:7:{s:14:"items_per_page";s:2:"10";s:18:"unicat_database_id";s:1:"1";s:9:"entity_id";s:1:"5";s:19:"media_collection_id";s:1:"0";s:16:"unicat_db_prefix";s:7:"unicat_";s:19:"activate_from_email";s:102:"Активация подписки на сайте mysite.ru <subscribe-activation-noreply@mysite.ru>";s:27:"activate_from_email_subject";s:56:"Подписка на рыссылки с сайта loc";}', NULL, 1, '0|read:1,write:1,view:1;2|read:1,write:1,view:1;', 0, 'popup', '', '0000-00-00 00:00:00'),
(64, 1, 1, 1, 2, 4, 'Reflex', 1, 'a:3:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"7000";s:2:"id";a:1:{s:17:"current_folder_id";s:0:"";}}', 'type: html\r\nlifetime: 7000\r\nid:\r\n  current_folder_id:', 'a:5:{s:12:"hook_node_id";s:2:"63";s:11:"hook_method";s:12:"getQuickForm";s:9:"hook_args";s:0:"";s:8:"hook_tpl";s:9:"QuickForm";s:20:"hook_output_data_key";s:0:"";}', NULL, 1, NULL, 0, 'popup', 'быстрая форма подписки через Reflex', '0000-00-00 00:00:00'),
(65, 1, 1, 1, 2, 3, 'Texter', 1, 'a:2:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"7777";}', 'type: html\r\nlifetime: 7777', 'a:2:{s:12:"text_item_id";s:2:"32";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Заголовок подписки', '0000-00-00 00:00:00'),
(67, 1, 1, 50, 1, 1, 'Captcha', 0, NULL, NULL, NULL, NULL, 1, NULL, 0, 'popup', 'captcha', '0000-00-00 00:00:00'),
(68, 1, 1, 51, 1, 1, 'CatalogOld', 1, NULL, NULL, 'a:5:{s:14:"items_per_page";s:2:"20";s:12:"class_prefix";s:5:"news_";s:9:"entity_id";s:1:"2";s:19:"media_collection_id";s:1:"5";s:16:"unicat_db_prefix";s:11:"unicat_old_";}', NULL, 1, NULL, 2, 'popup', 'Каталог', '0000-00-00 00:00:00'),
(69, 1, 1, 51, 8, 2, 'Reflex', 1, NULL, NULL, 'a:5:{s:12:"hook_node_id";s:2:"68";s:11:"hook_method";s:17:"getCategoriesTree";s:9:"hook_args";s:0:"";s:8:"hook_tpl";s:7:"CatTree";s:20:"hook_output_data_key";s:15:"categories_list";}', NULL, 1, NULL, 0, 'popup', 'Категории каталога', '0000-00-00 00:00:00'),
(70, 1, 1, 52, 1, 1, 'Taxonomy', 1, NULL, NULL, 'a:3:{s:14:"items_per_page";s:2:"10";s:15:"catalog_node_id";s:2:"68";s:12:"class_prefix";s:5:"news_";}', NULL, 1, NULL, 0, 'popup', 'Таксономия', '0000-00-00 00:00:00'),
(71, 1, 1, 51, 8, 5, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:2:"33";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Надпись "облако тэгов"', '0000-00-00 00:00:00'),
(72, 1, 1, 51, 8, 6, 'Reflex', 1, NULL, NULL, 'a:5:{s:12:"hook_node_id";s:2:"70";s:11:"hook_method";s:12:"getTagsCloud";s:9:"hook_args";s:0:"";s:8:"hook_tpl";s:9:"TagsCloud";s:20:"hook_output_data_key";s:4:"tags";}', NULL, 1, NULL, 0, 'popup', 'Облако тэгов', '0000-00-00 00:00:00'),
(73, 1, 1, 53, 1, 1, '2GisCounter', 1, NULL, NULL, NULL, NULL, 1, NULL, 0, 'popup', 'счетчик 2гис', '0000-00-00 00:00:00'),
(74, 1, 1, 41, 1, 1, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:1:"1";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'Тестирование дубликата', '0000-00-00 00:00:00'),
(75, 1, 1, 19, 1, 3, 'VideoPlayer', 1, NULL, NULL, 'a:3:{s:3:"uri";s:40:"http://www.youtube.com/embed/LZ5E9Oh4O4U";s:5:"width";s:3:"430";s:6:"height";s:3:"300";}', NULL, 1, NULL, 0, 'popup', 'VideoPlayer', '0000-00-00 00:00:00'),
(76, 1, 1, 57, 1, 2, 'Comments', 1, NULL, NULL, 'a:2:{s:14:"source_node_id";s:2:"86";s:18:"is_only_authorized";s:1:"1";}', NULL, 1, NULL, 0, 'popup', '', '0000-00-00 00:00:00'),
(77, 1, 1, 20, 1, 3, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";i:35;}', NULL, 1, NULL, 0, 'popup', '123123123', '0000-00-00 00:00:00'),
(78, 1, 1, 41, 1, 2, 'Texter', 1, NULL, NULL, 'a:2:{s:12:"text_item_id";s:2:"36";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', 'тест даты создания ноды...', '2011-07-17 19:22:25'),
(79, 1, 1, 55, 1, 1, 'UserAccount', 0, NULL, NULL, 'a:5:{s:15:"profile_node_id";s:1:"0";s:15:"recover_node_id";s:2:"49";s:16:"register_node_id";s:2:"22";s:15:"captcha_node_id";s:1:"0";s:13:"enable_openid";s:1:"0";}', NULL, 1, '0|read:1,write:1;2|read:1,write:1;', 0, 'popup', '', '2011-09-19 19:32:56'),
(80, 1, 1, 1, 5, 0, 'UserWelcome', 1, 'a:3:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"7000";s:2:"id";a:1:{s:7:"user_id";s:0:"";}}', 'type: html\r\nlifetime: 7000\r\nid:\r\n  user_id:', 'a:2:{s:15:"account_node_id";s:2:"79";s:16:"register_node_id";s:2:"22";}', NULL, 1, NULL, 0, 'popup', 'Быстрая авторизация 2', '2011-09-19 22:49:45'),
(82, 1, 1, 28, 1, 0, 'Texter', 1, NULL, NULL, 'a:1:{s:12:"text_item_id";i:37;}', NULL, 1, NULL, 0, 'popup', 'Восстановление пароля', '2011-09-22 00:00:43'),
(83, 1, 1, 56, 1, 1, 'Catalog', 1, 'a:3:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"5000";s:2:"id";a:1:{s:11:"request_uri";s:0:"";}}', 'type: html\r\nlifetime: 5000\r\nid:\r\n  #parser_data:\r\n  request_uri:', 'a:5:{s:14:"items_per_page";s:2:"20";s:12:"class_prefix";s:4:"cat_";s:9:"entity_id";s:1:"1";s:19:"media_collection_id";s:1:"3";s:16:"unicat_db_prefix";s:7:"unicat_";}', NULL, 1, NULL, 1, 'popup', 'Каталог 2', '2011-10-16 20:59:34'),
(84, 1, 1, 56, 8, 2, 'Reflex', 1, 'a:3:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"7000";s:2:"id";a:1:{s:11:"request_uri";s:0:"";}}', 'type: html\r\nlifetime: 7000\r\nid:\r\n  request_uri:', 'a:5:{s:12:"hook_node_id";s:2:"83";s:11:"hook_method";s:17:"getCategoriesList";s:9:"hook_args";s:56:"structure_id: 1\r\ncategory_id: 0\r\nuse_parcer_node_data: 0";s:8:"hook_tpl";s:7:"CatTree";s:20:"hook_output_data_key";s:15:"categories_list";}', NULL, 1, NULL, 0, 'popup', 'Дерево категорий', '2011-10-29 03:35:33'),
(85, 1, 0, 1, 1, 5, 'TestHook', 1, NULL, NULL, NULL, NULL, 1, NULL, 0, 'popup', '', '2011-11-03 01:19:40'),
(86, 1, 1, 57, 1, 1, 'News', 1, 'a:3:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"5000";s:2:"id";a:1:{s:11:"request_uri";s:0:"";}}', 'type: html\r\nlifetime: 5000\r\nid:\r\n  #parser_data:\r\n  request_uri:', 'a:6:{s:14:"items_per_page";s:1:"3";s:12:"class_prefix";s:5:"news_";s:9:"entity_id";s:1:"2";s:19:"media_collection_id";s:1:"1";s:16:"unicat_db_prefix";s:7:"unicat_";s:22:"use_publication_period";s:1:"1";}', NULL, 1, NULL, 1, 'popup', 'Новости', '2011-12-03 22:55:28'),
(87, 2, 1, 40, 1, 1, 'Subscribe', 1, NULL, NULL, 'a:7:{s:14:"items_per_page";s:2:"10";s:18:"unicat_database_id";s:1:"1";s:9:"entity_id";s:1:"3";s:19:"media_collection_id";s:1:"0";s:16:"unicat_db_prefix";s:7:"unicat_";s:19:"activate_from_email";s:25:"subscribe-activation@loc2";s:27:"activate_from_email_subject";s:57:"Подписка на рыссылки с сайта loc2";}', NULL, 1, '0|read:1,write:1,view:1;2|read:1,write:1,view:1;', 0, 'popup', 'Рассылки', '2011-12-05 10:19:19'),
(88, 1, 1, 1, 2, 6, 'Texter', 1, 'a:2:{s:4:"type";s:4:"html";s:8:"lifetime";s:4:"7777";}', 'type: html\r\nlifetime: 7777', 'a:2:{s:12:"text_item_id";s:2:"38";s:6:"editor";s:1:"1";}', NULL, 1, NULL, 0, 'popup', '123', '2012-01-06 06:28:22');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_permissions`
--

DROP TABLE IF EXISTS `engine_permissions`;
CREATE TABLE IF NOT EXISTS `engine_permissions` (
  `object` varchar(50) NOT NULL,
  `action` varchar(20) NOT NULL,
  `default_access` tinyint(1) NOT NULL,
  `descr` varchar(255) NOT NULL COMMENT 'краткое описание доступа',
  PRIMARY KEY (`object`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `engine_permissions`
--

INSERT INTO `engine_permissions` (`object`, `action`, `default_access`, `descr`) VALUES
('folder', 'read', 1, 'Может ли пользовать запросить папку. Если нет, то генерируется ошибка 403 (доступ запрещен).'),
('folder', 'view', 1, 'Нигде не отображается и скрываются ссылки на скрытые папки, но доступ к скрытым папкам остаётся как обычно.'),
('folder', 'write', 0, ''),
('news', 'delete', 0, 'Право удалять новости'),
('node', 'read', 1, 'Отображается нода или нет, соответственно обрабатывает её движок или нет.'),
('node', 'write', 0, 'Возможность передачи ноде POST данных. ');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_permissions_defaults`
--

DROP TABLE IF EXISTS `engine_permissions_defaults`;
CREATE TABLE IF NOT EXISTS `engine_permissions_defaults` (
  `object` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `user_group_id` smallint(5) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `access` tinyint(1) NOT NULL DEFAULT '0',
  `descr` varchar(255) NOT NULL,
  PRIMARY KEY (`object`,`action`,`user_group_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переопределения прав для различных групп';

--
-- Дамп данных таблицы `engine_permissions_defaults`
--

INSERT INTO `engine_permissions_defaults` (`object`, `action`, `user_group_id`, `site_id`, `access`, `descr`) VALUES
('folder', 'write', 3, 0, 1, 'Для администраторов разрешаем запись в папки.'),
('node', 'write', 3, 1, 1, 'Запись в ноды для админов');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_scripts_lib`
--

DROP TABLE IF EXISTS `engine_scripts_lib`;
CREATE TABLE IF NOT EXISTS `engine_scripts_lib` (
  `script_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `pos` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Приоритет, чем выше, тем раньше будет подключена либа',
  `related_by` varchar(255) DEFAULT NULL COMMENT 'Зависит от',
  `title` varchar(200) NOT NULL,
  `current_version` varchar(20) NOT NULL,
  `default_profile` varchar(50) NOT NULL COMMENT 'Будет применент, в случае если отсутствует запрошенный профиль',
  `homepage` varchar(200) NOT NULL,
  `files` text COMMENT 'Файлы',
  `descr` text,
  PRIMARY KEY (`script_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Библиотека скриптов' AUTO_INCREMENT=14 ;

--
-- Дамп данных таблицы `engine_scripts_lib`
--

INSERT INTO `engine_scripts_lib` (`script_id`, `name`, `pos`, `related_by`, `title`, `current_version`, `default_profile`, `homepage`, `files`, `descr`) VALUES
(1, 'jquery', 1000, '', 'jQuery is a new kind of JavaScript Library.', '1.6.4', 'local', 'http://jquery.com/', 'jquery.min.js', 'jQuery is a fast and concise JavaScript Library that simplifies HTML document traversing, event handling, animating, and Ajax interactions for rapid web development. jQuery is designed to change the way that you write JavaScript.'),
(2, 'tinymce', 0, '', 'TinyMCE - Javascript WYSIWYG Editor', '3.4.5', 'local', 'http://tinymce.moxiecode.com/', 'tiny_mce.js', 'TinyMCE is a platform independent web based Javascript HTML WYSIWYG editor control released as Open Source under LGPL by Moxiecode Systems AB. It has the ability to convert HTML TEXTAREA fields or other HTML elements to editor instances. TinyMCE is very easy to integrate into other Content Management Systems. '),
(3, 'extjs', 0, '', 'Ext JS is a cross-browser JavaScript library for building rich internet applications.', '3.2.1', 'local', 'http://www.sencha.com/products/js/', '', NULL),
(4, 'highslide', 0, '', 'Highslide JS - JavaScript thumbnail viewer', '4.1.4', 'local', 'http://highslide.com/', '', 'Highslide JS is an image, media and gallery viewer written in JavaScript.'),
(5, 'ckeditor', 0, '', 'CKEditor - WYSIWYG Text and HTML Editor for the Web', '3.4.1', 'local', 'http://ckeditor.com/', '', 'CKEditor is a text editor to be used inside web pages. It''s a WYSIWYG  editor, which means that the text being edited on it looks as similar as possible to the results users have when publishing it. It brings to the web common editing features found on desktop editing applications like Microsoft Word and OpenOffice.'),
(6, 'jquery-ui', 0, '', 'jQuery user interface', '1.8.5', 'local', 'http://jqueryui.com/', '', 'jQuery UI provides abstractions for low-level interaction and animation, advanced effects and high-level, themeable widgets, built on top of the jQuery JavaScript Library, that you can use to build highly interactive web applications.'),
(7, 'mediabox', 0, '', 'Mediabox Advanced', '1.3.4', 'local', 'http://iaian7.com/webcode/mediaboxAdvanced', '', 'Based on Lightbox, Slimbox, and the Mootools javascript library, mediaboxAdvanced  is a modal overlay that can handle images, videos, animations, social video sites, twitter media links, inline elements, and external pages with ease.'),
(8, 'mootools', 0, '', 'MooTools JS Framework', '1.2.5', 'local', 'http://mootools.net/', 'mootools.min.js', 'MooTools is a compact, modular, Object-Oriented JavaScript framework designed for the intermediate to advanced JavaScript developer. It allows you to write powerful, flexible, and cross-browser code with its elegant, well documented, and coherent API.'),
(9, 'scriptaculous', 99, 'prototype', 'script.aculo.us - web 2.0 javascript', '1.9.0', 'local', 'http://script.aculo.us/', 'scriptaculous.js', 'script.aculo.us provides you with\r\neasy-to-use, cross-browser user\r\ninterface JavaScript libraries to make\r\nyour web sites and web applications fly.'),
(10, 'prototype', 100, '', 'JavaScript Framework', '1.7.0', 'local', 'http://www.prototypejs.org/', 'prototype.min.js', 'Prototype is a JavaScript Framework that aims to ease development of dynamic web applications.'),
(11, 'lightview', 0, 'scriptaculous', 'Lightview', '2.7.4', 'local', 'http://www.nickstakenburg.com/projects/lightview/', 'css/lightview.css,js/lightview.js', NULL),
(12, 'jquery.cookie', 0, 'jquery', 'Cookie', '1.0', 'local', 'http://plugins.jquery.com/project/cookie', 'jquery.cookie.min.js', 'A simple, lightweight utility plugin for reading, writing and deleting cookies.'),
(13, 'less', 0, NULL, 'The dynamic stylesheet language.', '1.1.5', 'local', 'http://lesscss.org/', 'less.min.js', 'LESS extends CSS with dynamic behavior such as variables, mixins, operations and functions. LESS runs on both the client-side (IE 6+, Webkit, Firefox) and server-side, with Node.js.');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_scripts_paths`
--

DROP TABLE IF EXISTS `engine_scripts_paths`;
CREATE TABLE IF NOT EXISTS `engine_scripts_paths` (
  `script_id` mediumint(8) unsigned NOT NULL,
  `profile` varchar(50) NOT NULL,
  `version` varchar(10) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`script_id`,`profile`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Пути к скриптам';

--
-- Дамп данных таблицы `engine_scripts_paths`
--

INSERT INTO `engine_scripts_paths` (`script_id`, `profile`, `version`, `path`) VALUES
(1, 'google', '1.6.4', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/'),
(1, 'local', '1.4.2', 'jquery/1.4.2/'),
(1, 'local', '1.6.4', 'jquery/1.6.4/'),
(1, 'yandex', '1.4.2', 'http://yandex.st/jquery/1.4.2/'),
(1, 'yandex', '1.6.4', 'http://yandex.st/jquery/1.6.4/'),
(2, 'local', '3.3.9.4', 'tinymce/3.3.9.4/'),
(2, 'local', '3.4.5', 'tinymce/3.4.5/'),
(9, 'local', '1.9.0', 'scriptaculous/1.9.0/src/'),
(9, 'yandex', '1.9.0', 'http://yandex.st/scriptaculous/1.9.0/min/'),
(10, 'local', '1.7.0', 'prototype/1.7.0/'),
(10, 'yandex', '1.7.0', 'http://yandex.st/prototype/1.7.0.0/'),
(11, 'local', '2.7.4', 'lightview/2.7.4/'),
(12, 'local', '1.0', 'jquery/plugins/cookie/'),
(13, 'local', '1.1.5', 'less/1.1.5/');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_settings`
--

DROP TABLE IF EXISTS `engine_settings`;
CREATE TABLE IF NOT EXISTS `engine_settings` (
  `variable` varchar(100) NOT NULL,
  `group_id` tinyint(3) NOT NULL DEFAULT '0',
  `default_value` text NOT NULL COMMENT 'Значение по уполчанию',
  `optioncode` text NOT NULL COMMENT 'Код отображения для удобства редактирования в админке.',
  `datatype` enum('free','number','bool') NOT NULL DEFAULT 'number' COMMENT 'Тип данных',
  `descr` text NOT NULL COMMENT 'Описание параметра',
  PRIMARY KEY (`variable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Настройки';

--
-- Дамп данных таблицы `engine_settings`
--

INSERT INTO `engine_settings` (`variable`, `group_id`, `default_value`, `optioncode`, `datatype`, `descr`) VALUES
('admin_group_id', 0, '3', '', 'number', 'Группа адмнистраторов'),
('component.editor.editor_css', 0, '/resources/styles/editor.css', '', 'free', 'CSS стиль для визульного редактора.'),
('default_group_id', 0, '2', '', 'number', 'Группа пользовалелей по умолчанию, задаётся при регистрации новых пользователей.'),
('http_compression_level', 0, '0', '', 'number', 'HTTP сжатие (0 - 9).'),
('module.texter.filemanager_path', 0, 'filemanager/', '', 'free', ''),
('root_group_id', 0, '1', '', 'number', 'Группа пользователей, которая считатется рутовой, для неё всегда будут доступны все привелегии и запретить их будет нельзя.'),
('scripts_profiles', 0, 'local', '', 'free', 'Список профилей для скриптов, которые будут применяться.\r\n\r\nНапример можно указать: "yandex, google" тогда будет случайным образом выбран один из профилей.'),
('send_welcome_email', 0, '1', '', 'bool', 'Посылать зарегистрированному пользователю E-mail'),
('time_format', 0, '%d %B %Y, %H:%M:%S', '', 'free', 'Формат отображени даты'),
('time_offset', 0, '0', '', 'number', 'Разница во времени сервера.');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_settings_groups`
--

DROP TABLE IF EXISTS `engine_settings_groups`;
CREATE TABLE IF NOT EXISTS `engine_settings_groups` (
  `group_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Группы настроек сайта' AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `engine_settings_groups`
--

INSERT INTO `engine_settings_groups` (`group_id`, `name`) VALUES
(1, 'Настройки доменов');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_settings_values`
--

DROP TABLE IF EXISTS `engine_settings_values`;
CREATE TABLE IF NOT EXISTS `engine_settings_values` (
  `variable` varchar(100) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`variable`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `engine_settings_values`
--

INSERT INTO `engine_settings_values` (`variable`, `site_id`, `value`) VALUES
('admin_group_id', 1, '3'),
('component.editor.editor_css', 1, '/SmartCore/resources/styles/editor.css'),
('component.editor.editor_css', 2, '/SmartCore/resources/styles/editor.css'),
('default_group_id', 1, '2'),
('http_compression_level', 1, '0'),
('module.texter.filemanager_path', 1, 'filemanager/'),
('root_group_id', 1, '1'),
('scripts_profiles', 1, 'local'),
('send_welcome_email', 1, '1'),
('time_format', 1, '%d %B %Y, %H:%M:%S'),
('time_offset', 1, '0');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_sites`
--

DROP TABLE IF EXISTS `engine_sites`;
CREATE TABLE IF NOT EXISTS `engine_sites` (
  `site_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` varchar(8) DEFAULT NULL COMMENT 'Язык сайта по умолчанию',
  `theme_id` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'ID темы по умолчанию',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `properties` text COMMENT 'Свойства сайта в виде сериализованного массива.',
  PRIMARY KEY (`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Сайты' AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `engine_sites`
--

INSERT INTO `engine_sites` (`site_id`, `language_id`, `theme_id`, `create_datetime`, `properties`) VALUES
(1, 'ru', 1, '0000-00-00 00:00:00', 'a:14:{s:10:"short_name";s:14:"Smart Core CMF";s:9:"full_name";s:58:"Smart Core CMF (PHP/MySQL CMS/Content Managment Framework)";s:8:"timezone";s:16:"Asia/Novosibirsk";s:13:"cookie_prefix";s:15:"Smart_Core_CMF_";s:12:"session_name";s:19:"SMART_CORE_SESSION2";s:10:"robots_txt";s:53:"User-Agent: *\r\nDisallow: /cgi-bin/\r\nDisallow: /admin/";s:14:"multi_language";s:1:"0";s:12:"cache_enable";s:1:"1";s:15:"dir_application";s:12:"application/";s:10:"dir_themes";s:7:"themes/";s:11:"root_layout";s:0:"";s:9:"root_view";s:0:"";s:7:"layouts";s:13:"main:\r\nblank:";s:5:"views";s:51:"content:\r\n  full_content:\r\n  3columns:\r\n  2columns:";}'),
(2, 'ru', 1, '0000-00-00 00:00:00', 'a:14:{s:10:"short_name";s:17:"Демо сайт";s:9:"full_name";s:17:"Демо сайт";s:8:"timezone";s:16:"America/New_York";s:13:"cookie_prefix";s:20:"Smart_Core_CMF_Demo_";s:12:"session_name";s:19:"SMART_CORE_SESSION3";s:10:"robots_txt";s:53:"User-Agent: *\r\nDisallow: /cgi-bin/\r\nDisallow: /admin/";s:14:"multi_language";s:1:"0";s:12:"cache_enable";s:1:"1";s:15:"dir_application";s:12:"application/";s:10:"dir_themes";s:7:"themes/";s:11:"root_layout";s:0:"";s:9:"root_view";s:0:"";s:7:"layouts";s:13:"main:\r\nblank:";s:5:"views";s:51:"content:\r\n  full_content:\r\n  3columns:\r\n  2columns:";}'),
(3, 'ru', 1, '0000-00-00 00:00:00', 'a:13:{s:10:"short_name";s:14:"Smart Core CMF";s:9:"full_name";s:58:"Smart Core CMF (PHP/MySQL CMS/Content Managment Framework)";s:8:"timezone";s:16:"Asia/Novosibirsk";s:13:"cookie_prefix";s:15:"Smart_Core_CMF_";s:12:"session_name";s:19:"SMART_CORE_SESSION2";s:10:"robots_txt";s:53:"User-Agent: *\r\nDisallow: /cgi-bin/\r\nDisallow: /admin/";s:14:"multi_language";s:1:"0";s:15:"dir_application";s:12:"application/";s:10:"dir_themes";s:7:"themes/";s:11:"root_layout";s:0:"";s:9:"root_view";s:0:"";s:7:"layouts";s:13:"main:\r\nblank:";s:5:"views";s:51:"content:\r\n  full_content:\r\n  3columns:\r\n  2columns:";}');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_sites_domains`
--

DROP TABLE IF EXISTS `engine_sites_domains`;
CREATE TABLE IF NOT EXISTS `engine_sites_domains` (
  `domain` varchar(255) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `descr` varchar(255) NOT NULL COMMENT 'Описание.',
  `language_id` varchar(8) DEFAULT NULL COMMENT 'Если не NULL, то сайт переключается на другой язык',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`domain`),
  KEY `site_id` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `engine_sites_domains`
--

INSERT INTO `engine_sites_domains` (`domain`, `site_id`, `descr`, `language_id`, `create_datetime`) VALUES
('demo.smart-core.loc', 2, 'Тестовый домен для демки.', NULL, '0000-00-00 00:00:00'),
('demo.smart-core.org', 2, 'Демо сайт', NULL, '0000-00-00 00:00:00'),
('en.smart-core.loc', 2, '', 'en', '0000-00-00 00:00:00'),
('loc', 1, 'http://loc/SmartCore/', NULL, '0000-00-00 00:00:00'),
('loc2', 2, 'http://loc2/SmartCore/', NULL, '0000-00-00 00:00:00'),
('localhost', 1, '', NULL, '0000-00-00 00:00:00'),
('smart-core.loc', 3, '', NULL, '0000-00-00 00:00:00'),
('test', 1, '', NULL, '0000-00-00 00:00:00'),
('test.loc', 1, '', NULL, '0000-00-00 00:00:00'),
('xn--d1abbgf6aiiy.xn--p1ai', 1, 'http://президент.рф/ - домен в формате IDN.', NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_themes`
--

DROP TABLE IF EXISTS `engine_themes`;
CREATE TABLE IF NOT EXISTS `engine_themes` (
  `theme_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL COMMENT '@todo сейчас не используется, может вообще убрать?',
  `path` varchar(255) NOT NULL,
  `doctype` enum('XHTML11','XHTML1_STRICT','XHTML1_TRANSITIONAL','XHTML1_FRAMESET','XHTML_BASIC1','HTML4_STRICT','HTML4_LOOSE','HTML4_FRAMESET','HTML5','CUSTOM') NOT NULL DEFAULT 'XHTML1_STRICT' COMMENT '@todo вынести в theme.ini',
  `content_language` varchar(10) NOT NULL DEFAULT 'en' COMMENT '@todo убрать',
  `descr` varchar(255) NOT NULL COMMENT 'Краткое описание темы (техническое)',
  PRIMARY KEY (`theme_id`,`site_id`),
  UNIQUE KEY `name` (`name`,`site_id`),
  UNIQUE KEY `path` (`path`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `engine_themes`
--

INSERT INTO `engine_themes` (`theme_id`, `site_id`, `name`, `path`, `doctype`, `content_language`, `descr`) VALUES
(1, 1, 'default', 'default/', 'XHTML1_STRICT', 'ru', 'Основная тема сайта'),
(1, 2, 'demo', 'demo/', 'XHTML1_STRICT', 'ru', ''),
(1, 3, 'public', 'public/', 'XHTML1_STRICT', 'ru', '');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_themes_extras`
--

DROP TABLE IF EXISTS `engine_themes_extras`;
CREATE TABLE IF NOT EXISTS `engine_themes_extras` (
  `extras_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`extras_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Дополнительные данные к теме' AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `engine_themes_extras`
--

INSERT INTO `engine_themes_extras` (`extras_id`, `site_id`, `name`, `comment`) VALUES
(1, 1, 'favicon', 'Основная иконка');

-- --------------------------------------------------------

--
-- Структура таблицы `engine_themes_extras_values`
--

DROP TABLE IF EXISTS `engine_themes_extras_values`;
CREATE TABLE IF NOT EXISTS `engine_themes_extras_values` (
  `folder_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `theme_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `extras_id` smallint(5) unsigned NOT NULL,
  `value` text,
  `transmit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`folder_id`,`site_id`,`theme_id`,`extras_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Значения доволнительных данных к теме';

--
-- Дамп данных таблицы `engine_themes_extras_values`
--

INSERT INTO `engine_themes_extras_values` (`folder_id`, `site_id`, `theme_id`, `extras_id`, `value`, `transmit`) VALUES
(1, 1, 1, 1, 'favicon.ico', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `filemanager`
--

DROP TABLE IF EXISTS `filemanager`;
CREATE TABLE IF NOT EXISTS `filemanager` (
  `filemanager_id` smallint(5) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `descr` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Кофигурации файловых менеджеров';

--
-- Дамп данных таблицы `filemanager`
--


-- --------------------------------------------------------

--
-- Структура таблицы `galleries`
--

DROP TABLE IF EXISTS `galleries`;
CREATE TABLE IF NOT EXISTS `galleries` (
  `gallery_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `media_collection_id` int(10) unsigned NOT NULL,
  `thumbnail_width` smallint(4) unsigned NOT NULL COMMENT 'Ширина миниатюры',
  `thumbnail_height` smallint(4) unsigned NOT NULL COMMENT 'Высота миниатюры',
  PRIMARY KEY (`gallery_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `galleries`
--

INSERT INTO `galleries` (`gallery_id`, `site_id`, `user_id`, `media_collection_id`, `thumbnail_width`, `thumbnail_height`) VALUES
(1, 1, 1, 2, 180, 180);

-- --------------------------------------------------------

--
-- Структура таблицы `galleries_albums`
--

DROP TABLE IF EXISTS `galleries_albums`;
CREATE TABLE IF NOT EXISTS `galleries_albums` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `gallery_id` int(10) unsigned NOT NULL,
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `uri_part` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `descr` text COMMENT 'Описание',
  `thumbnail_image_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ид картинки, которая выступает в качестве миниатюры.',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`album_id`,`site_id`),
  UNIQUE KEY `album-site-uri` (`album_id`,`site_id`,`uri_part`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Альбомы' AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `galleries_albums`
--

INSERT INTO `galleries_albums` (`album_id`, `site_id`, `gallery_id`, `pos`, `uri_part`, `title`, `descr`, `thumbnail_image_id`, `create_datetime`, `last_update_datetime`) VALUES
(1, 1, 1, 1, '1', 'Альбом №1', 'Рисунки', 41, '0000-00-00 00:00:00', '2011-07-18 16:07:59');

-- --------------------------------------------------------

--
-- Структура таблицы `galleries_images`
--

DROP TABLE IF EXISTS `galleries_images`;
CREATE TABLE IF NOT EXISTS `galleries_images` (
  `image_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `album_id` int(10) unsigned NOT NULL,
  `gallery_id` int(10) unsigned NOT NULL,
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `descr` text,
  PRIMARY KEY (`image_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Фотки';

--
-- Дамп данных таблицы `galleries_images`
--

INSERT INTO `galleries_images` (`image_id`, `site_id`, `album_id`, `gallery_id`, `create_datetime`, `descr`) VALUES
(40, 1, 1, 1, '2011-07-18 16:06:56', ''),
(41, 1, 1, 1, '2011-07-18 16:07:59', '');

-- --------------------------------------------------------

--
-- Структура таблицы `google_map_keys`
--

DROP TABLE IF EXISTS `google_map_keys`;
CREATE TABLE IF NOT EXISTS `google_map_keys` (
  `domain` varchar(100) NOT NULL,
  `key` varchar(255) NOT NULL,
  PRIMARY KEY (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ переделать в geomap_key с поддержкой яндекса';

--
-- Дамп данных таблицы `google_map_keys`
--

INSERT INTO `google_map_keys` (`domain`, `key`) VALUES
('demo1.smart-core.org', 'ABQIAAAAU5CAyNLuHxcSKN7auHRTQxRR9inn2m13BljtMMPNH7vTk5kJIRSvKGB2T3HOVL71m7AxiY3t3D9g3A'),
('loc', 'ABQIAAAAU5CAyNLuHxcSKN7auHRTQxRhkU_XGR0OZPpJ2vMxxDIwA559yhSvYye6pk4fxhwza0x7fXQyFoWqTQ'),
('localhost', 'ABQIAAAAU5CAyNLuHxcSKN7auHRTQxT2yXp_ZAY8_ufC3CFXhHIE1NvwkxTg_ANKKHXkui53qqBt5OcgO6gdOA'),
('smart-core.org', 'ABQIAAAAU5CAyNLuHxcSKN7auHRTQxTHDqjFYzAlNNVeCksK-5q2-agG6xRevcI28HxfCG1CQ89Ib-LqRnZWOA'),
('test', 'ABQIAAAAU5CAyNLuHxcSKN7auHRTQxQhL6vn9PNcXa7eSPja3EjRwozD8hTE33QcUrArlWvtUZ-InYYrJomIUQ'),
('test.loc', 'ABQIAAAAU5CAyNLuHxcSKN7auHRTQxSgnAS7gFOxJr0PZkeJSMT-2u9DPRT4oPh3_fcwUSC-0rTXiaOI-aGlyw');

-- --------------------------------------------------------

--
-- Структура таблицы `log_access_errors`
--

DROP TABLE IF EXISTS `log_access_errors`;
CREATE TABLE IF NOT EXISTS `log_access_errors` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `error_code` varchar(30) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `requested_uri` text NOT NULL,
  `referer` text,
  `ip` varchar(40) NOT NULL,
  `browser` varchar(20) NOT NULL,
  `browser_version` varchar(50) NOT NULL,
  `platform` varchar(50) NOT NULL COMMENT 'Операционная система',
  `user_agent` text NOT NULL,
  PRIMARY KEY (`log_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Лог ошибок запросов' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `log_access_errors`
--


-- --------------------------------------------------------

--
-- Структура таблицы `log_cron`
--

DROP TABLE IF EXISTS `log_cron`;
CREATE TABLE IF NOT EXISTS `log_cron` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` smallint(5) unsigned NOT NULL,
  `datetime_start` datetime NOT NULL,
  `datetime_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message` text COMMENT 'Сообщение коротое может вернуть задача',
  PRIMARY KEY (`log_id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Лог запуска задач по расписанию' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `log_cron`
--


-- --------------------------------------------------------

--
-- Структура таблицы `log_system`
--

DROP TABLE IF EXISTS `log_system`;
CREATE TABLE IF NOT EXISTS `log_system` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `message_hash` varchar(32) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `requested_uri` text NOT NULL,
  `referer` text,
  `ip` varchar(40) NOT NULL,
  `browser` varchar(20) NOT NULL,
  `browser_version` varchar(50) NOT NULL,
  `platform` varchar(50) NOT NULL COMMENT 'Операционная система',
  `user_agent` text NOT NULL,
  PRIMARY KEY (`log_id`,`site_id`),
  KEY `message_hash` (`message_hash`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Системный лог' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `log_system`
--


-- --------------------------------------------------------

--
-- Структура таблицы `log_user_auths`
--

DROP TABLE IF EXISTS `log_user_auths`;
CREATE TABLE IF NOT EXISTS `log_user_auths` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `login` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `referer` text,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(40) NOT NULL,
  `browser` varchar(50) NOT NULL,
  `browser_version` varchar(50) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `user_agent` text NOT NULL,
  PRIMARY KEY (`log_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Лог авторизаций пользователей' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `log_user_auths`
--


-- --------------------------------------------------------

--
-- Структура таблицы `maillist`
--

DROP TABLE IF EXISTS `maillist`;
CREATE TABLE IF NOT EXISTS `maillist` (
  `maillist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_archived` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `email_from` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `length` int(10) DEFAULT NULL COMMENT 'Размер тела письма в байтах.',
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datetime_end` datetime DEFAULT NULL,
  `priority` smallint(5) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `emails_count` smallint(5) unsigned NOT NULL COMMENT 'Кол-во емаилов для рассылки',
  PRIMARY KEY (`maillist_id`,`site_id`),
  KEY `is_archived` (`is_archived`),
  KEY `datetime` (`datetime`),
  KEY `priority` (`priority`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `maillist`
--


-- --------------------------------------------------------

--
-- Структура таблицы `maillist_emails`
--

DROP TABLE IF EXISTS `maillist_emails`;
CREATE TABLE IF NOT EXISTS `maillist_emails` (
  `maillist_id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `params` text COMMENT 'Параметры, например макросы для шаблонов',
  PRIMARY KEY (`maillist_id`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `maillist_emails`
--


-- --------------------------------------------------------

--
-- Структура таблицы `maillist_email_archive`
--

DROP TABLE IF EXISTS `maillist_email_archive`;
CREATE TABLE IF NOT EXISTS `maillist_email_archive` (
  `maillist_id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL,
  `params` text COMMENT 'Параметры, например макросы для шаблонов',
  PRIMARY KEY (`maillist_id`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `maillist_email_archive`
--


-- --------------------------------------------------------

--
-- Структура таблицы `media_categories`
--

DROP TABLE IF EXISTS `media_categories`;
CREATE TABLE IF NOT EXISTS `media_categories` (
  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `collection_id` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY (`category_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Категории' AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `media_categories`
--

INSERT INTO `media_categories` (`category_id`, `site_id`, `collection_id`, `pid`, `name`, `title`) VALUES
(1, 1, 2, 0, 'images', 'Картинки'),
(2, 1, 2, 0, 'files', 'Файлы');

-- --------------------------------------------------------

--
-- Структура таблицы `media_collections`
--

DROP TABLE IF EXISTS `media_collections`;
CREATE TABLE IF NOT EXISTS `media_collections` (
  `collection_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `default_storage_id` mediumint(8) unsigned NOT NULL,
  `relative_path` varchar(255) NOT NULL DEFAULT '' COMMENT 'Относительный путь в хранилище.',
  `name` varchar(50) NOT NULL COMMENT 'Используется в качестве uri_part например в файлменеджере',
  `password` varchar(50) NOT NULL COMMENT '@Пароль для более секьюрного подключения.',
  `title` varchar(50) DEFAULT NULL COMMENT 'Заголовок',
  `descr` text NOT NULL,
  `params` text COMMENT 'Параметры',
  PRIMARY KEY (`collection_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Коллекции' AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `media_collections`
--

INSERT INTO `media_collections` (`collection_id`, `site_id`, `default_storage_id`, `relative_path`, `name`, `password`, `title`, `descr`, `params`) VALUES
(1, 1, 1, 'images/news/', 'news', '', 'Новости', 'Миниатюры и картинки загружаемых файлов новостей.', 'a:11:{s:15:"use_type_prefix";s:1:"1";s:21:"compress_original_img";s:1:"1";s:18:"file_relative_path";s:9:"%Y/%m/%d/";s:9:"file_mask";s:15:"%H_%i_%RAND(10)";s:11:"allow_types";s:3:"img";s:21:"original_resize_width";s:4:"1000";s:22:"original_resize_height";s:3:"700";s:23:"original_resize_quality";s:2:"82";s:19:"original_resize_fit";s:6:"inside";s:21:"original_resize_scale";s:4:"down";s:19:"original_convert_to";s:3:"jpg";}'),
(2, 1, 1, 'images/gallery/', 'gallery', '', 'Фотогалерея', 'Медиа-файлы', 'a:11:{s:15:"use_type_prefix";s:1:"1";s:21:"compress_original_img";s:1:"1";s:18:"file_relative_path";s:9:"%Y/%m/%d/";s:9:"file_mask";s:15:"%H_%i_%RAND(10)";s:11:"allow_types";s:3:"all";s:21:"original_resize_width";s:4:"1920";s:22:"original_resize_height";s:4:"1200";s:23:"original_resize_quality";s:2:"80";s:19:"original_resize_fit";s:6:"inside";s:21:"original_resize_scale";s:4:"down";s:19:"original_convert_to";s:0:"";}'),
(5, 1, 1, 'images/catalog/', 'catalog', '', 'Каталог', '', 'a:11:{s:15:"use_type_prefix";s:1:"1";s:21:"compress_original_img";s:1:"1";s:18:"file_relative_path";s:9:"%Y/%m/%d/";s:9:"file_mask";s:15:"%H_%i_%RAND(10)";s:11:"allow_types";s:3:"all";s:21:"original_resize_width";s:4:"1600";s:22:"original_resize_height";s:4:"1200";s:23:"original_resize_quality";s:2:"80";s:19:"original_resize_fit";s:6:"inside";s:21:"original_resize_scale";s:4:"down";s:19:"original_convert_to";s:0:"";}');

-- --------------------------------------------------------

--
-- Структура таблицы `media_files`
--

DROP TABLE IF EXISTS `media_files`;
CREATE TABLE IF NOT EXISTS `media_files` (
  `file_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `storage_id` mediumint(8) unsigned NOT NULL,
  `collection_id` int(10) unsigned NOT NULL,
  `relative_path` varchar(255) NOT NULL COMMENT 'Относительный путь в коллекции.',
  `filename` varchar(100) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `upload_datetime` datetime NOT NULL,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(10) DEFAULT NULL COMMENT 'img, file, doc, video, audio, flash, archive',
  `mime_type` varchar(100) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `resized_size_sum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Обший объём ресайзнутых картинок',
  `meta` text,
  `hash` varchar(32) DEFAULT NULL COMMENT 'md5 hash',
  PRIMARY KEY (`file_id`,`site_id`,`collection_id`),
  KEY `collection_id-filename` (`collection_id`,`filename`,`site_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Файлы' AUTO_INCREMENT=65 ;

--
-- Дамп данных таблицы `media_files`
--

INSERT INTO `media_files` (`file_id`, `site_id`, `is_deleted`, `storage_id`, `collection_id`, `relative_path`, `filename`, `original_filename`, `upload_datetime`, `owner_id`, `type`, `mime_type`, `size`, `resized_size_sum`, `meta`, `hash`) VALUES
(22, 1, 0, 1, 2, '2011/03/21/', '12_36_2848b3187c.jpg', 'a_92913ba0.jpg', '2011-03-21 12:36:56', 0, 'img', NULL, 0, 0, 'a:1:{s:14:"resized_images";a:1:{s:7:"w90-h90";i:1747;}}', NULL),
(40, 1, 0, 1, 2, '2011/07/18/', '16_06_8ea0ad7b6d.jpg', 'kubik.jpg', '2011-07-18 16:06:56', 0, 'img', NULL, 0, 0, 'a:1:{s:14:"resized_images";a:2:{s:7:"w90-h90";i:1787;s:9:"w180-h180";i:3750;}}', NULL),
(41, 1, 0, 1, 2, '2011/07/18/', '16_07_6a32b2e60c.jpg', 'chvoia.jpg', '2011-07-18 16:07:59', 0, 'img', NULL, 0, 0, 'a:1:{s:14:"resized_images";a:2:{s:7:"w90-h90";i:1965;s:9:"w180-h180";i:4535;}}', NULL),
(60, 1, 0, 1, 1, 'img/2011/12/03/', '22_35_ff66a7384f.jpg', 'logo_1.gif', '2011-12-03 23:35:57', 1, 'img', 'image/gif', 5905, 0, 'a:1:{s:14:"resized_images";a:1:{s:7:"w90-h90";i:3111;}}', NULL),
(63, 1, 0, 1, 1, 'img/2011/12/04/', '23_36_d5cc3dc2a5.jpg', 'Iiyama-right-1920x1080_300.jpg', '2011-12-05 00:36:49', 1, 'img', 'image/jpeg', 53307, 0, 'a:1:{s:14:"resized_images";a:1:{s:7:"w90-h90";i:3117;}}', NULL),
(64, 1, 0, 1, 1, 'img/2011/12/05/', '03_27_e54d16b64c.jpg', 'sc_logo.png', '2011-12-05 04:27:17', 1, 'img', 'image/png', 3754, 0, 'a:1:{s:14:"resized_images";a:1:{s:7:"w90-h90";i:2526;}}', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `media_files_categiries_relation`
--

DROP TABLE IF EXISTS `media_files_categiries_relation`;
CREATE TABLE IF NOT EXISTS `media_files_categiries_relation` (
  `file_id` bigint(20) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `collection_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`file_id`,`site_id`,`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Вхождения файлов в категории';

--
-- Дамп данных таблицы `media_files_categiries_relation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `media_storages`
--

DROP TABLE IF EXISTS `media_storages`;
CREATE TABLE IF NOT EXISTS `media_storages` (
  `storage_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `path` varchar(100) NOT NULL,
  `title` varchar(50) NOT NULL,
  `descr` varchar(255) NOT NULL,
  PRIMARY KEY (`storage_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Медиа хранилища' AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `media_storages`
--

INSERT INTO `media_storages` (`storage_id`, `site_id`, `name`, `path`, `title`, `descr`) VALUES
(1, 1, 'local', '', 'Локальное хранилище', ''),
(2, 1, 'remote', 'http://static.site.ru/img/', 'Удалённое хранилище', 'Прокомментировать где');

-- --------------------------------------------------------

--
-- Структура таблицы `media_tags`
--

DROP TABLE IF EXISTS `media_tags`;
CREATE TABLE IF NOT EXISTS `media_tags` (
  `tag_id` mediumint(8) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `collection_id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `media_tags`
--


-- --------------------------------------------------------

--
-- Структура таблицы `media_tags_files_relation`
--

DROP TABLE IF EXISTS `media_tags_files_relation`;
CREATE TABLE IF NOT EXISTS `media_tags_files_relation` (
  `tag_id` mediumint(8) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `file_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `media_tags_files_relation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `menu_groups`
--

DROP TABLE IF EXISTS `menu_groups`;
CREATE TABLE IF NOT EXISTS `menu_groups` (
  `group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pos` smallint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `descr` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`,`site_id`),
  UNIQUE KEY `name` (`name`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Группы меню' AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `menu_groups`
--

INSERT INTO `menu_groups` (`group_id`, `site_id`, `pos`, `name`, `descr`) VALUES
(1, 1, 1, 'main-menu', 'Главное меню'),
(2, 1, 2, 'about', 'О системе'),
(4, 1, 0, 'test', 'test');

-- --------------------------------------------------------

--
-- Структура таблицы `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
CREATE TABLE IF NOT EXISTS `menu_items` (
  `item_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pid` mediumint(8) unsigned NOT NULL,
  `pos` mediumint(8) unsigned NOT NULL,
  `group_id` smallint(5) unsigned NOT NULL,
  `folder_id` int(10) unsigned DEFAULT NULL,
  `cached_uri` varchar(255) DEFAULT NULL,
  `suffix` varchar(255) DEFAULT NULL COMMENT 'для меток типа #new',
  `direct_link` varchar(255) DEFAULT NULL COMMENT 'может быть переименовать в direct_uri?',
  `title` varchar(255) DEFAULT NULL,
  `descr` varchar(255) DEFAULT NULL,
  `options` text,
  PRIMARY KEY (`item_id`,`site_id`),
  KEY `pid` (`pid`),
  KEY `pos` (`pos`),
  KEY `is_active` (`is_active`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=37 ;

--
-- Дамп данных таблицы `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `site_id`, `is_active`, `pid`, `pos`, `group_id`, `folder_id`, `cached_uri`, `suffix`, `direct_link`, `title`, `descr`, `options`) VALUES
(3, 1, 1, 0, 2, 1, 19, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, 1, 0, 7, 1, 18, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 1, 1, 35, 0, 1, 41, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 1, 0, 0, 9, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 1, 1, 0, 11, 1, 25, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 1, 1, 3, 1, 1, 20, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 1, 1, 18, 1, 1, 33, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 1, 1, 0, 3, 1, 32, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 1, 1, 19, 0, 1, 34, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 1, 1, 0, 5, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 1, 1, 0, 8, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 1, 1, 0, 13, 1, 45, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 1, 1, 0, 14, 1, 46, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 1, 1, 0, 6, 1, 51, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 1, 1, 0, 5, 1, 4, NULL, NULL, 'http://ya.ru/', 'Внешняя ссылка на ya.ru', NULL, NULL),
(32, 1, 1, 0, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 1, 1, 0, 123, 1, 55, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 1, 1, 0, 5, 1, 56, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 1, 1, 0, 3, 1, 57, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 1, 1, 28, 15, 1, 58, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `menu_items_translation`
--

DROP TABLE IF EXISTS `menu_items_translation`;
CREATE TABLE IF NOT EXISTS `menu_items_translation` (
  `item_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`item_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `menu_items_translation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `private_messages`
--

DROP TABLE IF EXISTS `private_messages`;
CREATE TABLE IF NOT EXISTS `private_messages` (
  `message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_readed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `from_user_id` int(10) unsigned NOT NULL,
  `to_user_id` int(10) unsigned NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL COMMENT 'Текст сообщения',
  `attachments` text COMMENT 'Прикреплённые файлы',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `from_user_id` (`from_user_id`,`to_user_id`,`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Приватные сообщения пользователей' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `private_messages`
--


-- --------------------------------------------------------

--
-- Структура таблицы `profiles`
--

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE IF NOT EXISTS `profiles` (
  `profile_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Название',
  `name` varchar(255) NOT NULL COMMENT 'Техническое имя',
  `create_datetime` datetime NOT NULL,
  PRIMARY KEY (`profile_id`,`site_id`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='@ Профили пользователей' AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `profiles`
--

INSERT INTO `profiles` (`profile_id`, `site_id`, `title`, `name`, `create_datetime`) VALUES
(1, 1, 'Профайл форума', 'forum', '0000-00-00 00:00:00'),
(2, 1, 'Социальная сеть', 'social', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Структура таблицы `profiles_followers`
--

DROP TABLE IF EXISTS `profiles_followers`;
CREATE TABLE IF NOT EXISTS `profiles_followers` (
  `profile_id` mediumint(8) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `user_profile_id` int(10) unsigned NOT NULL COMMENT 'Использовать который подписался',
  `user_profile_follower_id` int(10) unsigned NOT NULL COMMENT 'Пользователь на которого сделана подписка',
  `create_datetime` datetime NOT NULL,
  PRIMARY KEY (`profile_id`,`site_id`,`user_profile_id`,`user_profile_follower_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Подписчики';

--
-- Дамп данных таблицы `profiles_followers`
--


-- --------------------------------------------------------

--
-- Структура таблицы `profiles_friends`
--

DROP TABLE IF EXISTS `profiles_friends`;
CREATE TABLE IF NOT EXISTS `profiles_friends` (
  `profile_id` mediumint(8) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `from_user_profile_id` int(10) unsigned NOT NULL COMMENT 'Юзер который запросил дружбу',
  `to_user_profile_id` int(10) unsigned NOT NULL COMMENT 'Юзер у которого запросили дружбу',
  `create_datetime` datetime NOT NULL COMMENT 'Дата создания связи дружбы',
  PRIMARY KEY (`profile_id`,`site_id`,`from_user_profile_id`,`to_user_profile_id`),
  KEY `create_datetime` (`create_datetime`,`profile_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Связи друзья';

--
-- Дамп данных таблицы `profiles_friends`
--

INSERT INTO `profiles_friends` (`profile_id`, `site_id`, `from_user_profile_id`, `to_user_profile_id`, `create_datetime`) VALUES
(1, 1, 1, 2, '2011-11-26 15:22:47');

-- --------------------------------------------------------

--
-- Структура таблицы `profiles_friends_requests`
--

DROP TABLE IF EXISTS `profiles_friends_requests`;
CREATE TABLE IF NOT EXISTS `profiles_friends_requests` (
  `profile_id` mediumint(8) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `from_user_profile_id` int(10) unsigned NOT NULL COMMENT 'Юзер который запросил дружбу',
  `to_user_profile_id` int(10) unsigned NOT NULL COMMENT 'Юзер у которого запросили дружбу',
  `status` enum('pending','accept','reject') NOT NULL DEFAULT 'pending',
  `create_datetime` datetime NOT NULL COMMENT 'Дата создания связи дружбы',
  `close_datetime` datetime DEFAULT NULL COMMENT 'Дата закрытия заявки',
  KEY `profile_id` (`profile_id`,`site_id`,`status`),
  KEY `from_user_profile_id` (`from_user_profile_id`,`profile_id`,`site_id`),
  KEY `to_user_profile_id` (`to_user_profile_id`,`profile_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Заявки на дружбу';

--
-- Дамп данных таблицы `profiles_friends_requests`
--


-- --------------------------------------------------------

--
-- Структура таблицы `profiles_groups`
--

DROP TABLE IF EXISTS `profiles_groups`;
CREATE TABLE IF NOT EXISTS `profiles_groups` (
  `group_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` mediumint(8) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `pos` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL COMMENT 'Название группы свойств',
  `help` text COMMENT 'Справка по группе свойств профиля',
  PRIMARY KEY (`group_id`,`profile_id`,`site_id`),
  KEY `group_id` (`profile_id`,`site_id`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Группы профилей' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `profiles_groups`
--


-- --------------------------------------------------------

--
-- Структура таблицы `profiles_properties`
--

DROP TABLE IF EXISTS `profiles_properties`;
CREATE TABLE IF NOT EXISTS `profiles_properties` (
  `property_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `site_id` mediumint(8) unsigned NOT NULL,
  `pos` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `help` text COMMENT 'Справка по свойству профиля',
  PRIMARY KEY (`property_id`,`group_id`,`site_id`),
  KEY `site_id` (`site_id`,`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Свойства профилей' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `profiles_properties`
--


-- --------------------------------------------------------

--
-- Структура таблицы `profiles_users`
--

DROP TABLE IF EXISTS `profiles_users`;
CREATE TABLE IF NOT EXISTS `profiles_users` (
  `user_profile_id` int(10) unsigned NOT NULL,
  `profile_id` mediumint(8) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `status` enum('active','locked','deleted','banned') NOT NULL DEFAULT 'active',
  `name` varchar(30) DEFAULT NULL COMMENT 'часть УРИ юзера ',
  `title` varchar(100) NOT NULL COMMENT 'Имя юзера в профиле',
  `photo` varchar(255) NOT NULL COMMENT 'Фотка в профиле',
  `create_datetime` datetime NOT NULL COMMENT 'Дата регистрации юзера в профиле',
  PRIMARY KEY (`user_profile_id`,`site_id`,`user_id`,`profile_id`),
  UNIQUE KEY `name` (`name`,`site_id`,`profile_id`),
  KEY `title` (`title`,`site_id`,`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Пользователи профилей';

--
-- Дамп данных таблицы `profiles_users`
--


-- --------------------------------------------------------

--
-- Структура таблицы `profiles_users_data`
--

DROP TABLE IF EXISTS `profiles_users_data`;
CREATE TABLE IF NOT EXISTS `profiles_users_data` (
  `user_profile_id` int(10) unsigned NOT NULL,
  `profile_id` mediumint(8) unsigned NOT NULL,
  `property_id` mediumint(8) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`user_profile_id`,`profile_id`,`property_id`,`site_id`),
  KEY `site_id` (`site_id`,`value`(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='@ Данные свойств пользователей';

--
-- Дамп данных таблицы `profiles_users_data`
--


-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` varchar(40) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `token` varchar(128) DEFAULT NULL COMMENT 'Для авторизации через куки',
  `create_datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания сессии',
  `last_activity_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` text,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_data` text COMMENT 'Базовые данные пользователя.',
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`session_id`,`site_id`),
  KEY `is_active` (`is_active`),
  KEY `last_activity_datetime` (`last_activity_datetime`),
  KEY `token-user_id` (`token`,`user_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Сессии';

--
-- Дамп данных таблицы `sessions`
--


-- --------------------------------------------------------

--
-- Структура таблицы `subscribers`
--

DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE IF NOT EXISTS `subscribers` (
  `subscriber_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL,
  `params` text COMMENT 'Параметры, например имя пользователя и как в нему обращаться.',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания подписчика',
  `activate_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата активации',
  PRIMARY KEY (`subscriber_id`,`site_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Подписчики' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `subscribers`
--


-- --------------------------------------------------------

--
-- Структура таблицы `subscribers_activation`
--

DROP TABLE IF EXISTS `subscribers_activation`;
CREATE TABLE IF NOT EXISTS `subscribers_activation` (
  `subscriber_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания запроса на активацию',
  `action` enum('subscribe','delete','update') NOT NULL DEFAULT 'subscribe',
  `code` varchar(128) NOT NULL,
  `rubrics_list` text NOT NULL COMMENT 'Сериализованный список рубрик',
  PRIMARY KEY (`subscriber_id`,`site_id`,`action`),
  UNIQUE KEY `site_id` (`code`,`site_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Подтверждение подсписчиков. ';

--
-- Дамп данных таблицы `subscribers_activation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `subscribers_rubrics_relation`
--

DROP TABLE IF EXISTS `subscribers_rubrics_relation`;
CREATE TABLE IF NOT EXISTS `subscribers_rubrics_relation` (
  `subscriber_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `rubric_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`subscriber_id`,`site_id`,`rubric_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Связи на какие рубрики подписаны подписчики. ';

--
-- Дамп данных таблицы `subscribers_rubrics_relation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `text_items`
--

DROP TABLE IF EXISTS `text_items`;
CREATE TABLE IF NOT EXISTS `text_items` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `language_id` varchar(8) NOT NULL,
  `text` mediumtext NOT NULL,
  `meta` text COMMENT 'Мета-данные',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL COMMENT 'id создателя',
  PRIMARY KEY (`item_id`,`site_id`,`language_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=39 ;

--
-- Дамп данных таблицы `text_items`
--

INSERT INTO `text_items` (`item_id`, `site_id`, `language_id`, `text`, `meta`, `create_datetime`, `owner_id`) VALUES
(1, 1, 'ru', 'Футер 2.', NULL, '0000-00-00 00:00:00', 0),
(2, 1, 'ru', '<h1>Главная страница!</h1>\r\n<p>С точки зрения банальной эрудиции каждый индивидуум, критически мотивирующий абстракцию, не может игнорировать критерии утопического субъективизма, концептуально интерпретируя общепринятые дефанизирующие поляризаторы, поэтому консенсус, достигнутый диалектической материальной классификацией всеобщих мотиваций в парадогматических связях предикатов, решает проблему усовершенствования формирующих геотрансплантационных квазипузлистатов всех кинетически коррелирующих аспектов. Исходя из этого, мы пришли к выводу, что каждый произвольно выбранный предикативно абсорбирующий объект. 3</p>', NULL, '0000-00-00 00:00:00', 0),
(3, 1, 'ru', '<h2>Обратная связь</h2>', NULL, '0000-00-00 00:00:00', 0),
(4, 1, 'ru', 'админ ))', NULL, '0000-00-00 00:00:00', 0),
(7, 1, 'ru', '', 'a:1:{s:11:"description";s:65:"Документация. Описание из текстера.";}', '0000-00-00 00:00:00', 0),
(8, 1, 'ru', 'цупа', NULL, '0000-00-00 00:00:00', 0),
(9, 1, 'ru', 'кратко о системе.<br />', NULL, '0000-00-00 00:00:00', 0),
(10, 1, 'ru', 'скачать 2<br />', NULL, '0000-00-00 00:00:00', 0),
(12, 1, 'ru', '<h1>Регистрация на сайте</h1>', NULL, '0000-00-00 00:00:00', 0),
(13, 1, 'ru', '<h1>Редактирование личного профиля</h1>', NULL, '0000-00-00 00:00:00', 0),
(15, 1, 'ru', 'asdas das d', NULL, '0000-00-00 00:00:00', 0),
(16, 1, 'ru', '<br /><br />Ниже вставлен ролик через модуль VideoPlayer<br />', NULL, '0000-00-00 00:00:00', 0),
(17, 1, 'ru', '<p>Что-то еще на главной :) дада:)) "a2s"&nbsp; 3</p>', NULL, '0000-00-00 00:00:00', 0),
(18, 1, 'ru', '<h2>Фотогалерея</h2>', NULL, '0000-00-00 00:00:00', 0),
(19, 1, 'ru', '<p>spec</p>\r\n<p>&nbsp;3</p>', NULL, '0000-00-00 00:00:00', 0),
(20, 1, 'ru', 'asd asdasd asd asd asd<br />', NULL, '0000-00-00 00:00:00', 0),
(21, 2, 'ru', '<h1>Bike Riders News</h1>\r\n<p>This is a template designed by <strong>free website templates</strong> for you for free you can replace all the text by your own text. This is just a place holder so you can see how the site would look like. If you''re having problems editing the template please don''t hesitate to ask for help on the forum. You will get help as soon as possible. You can also use the forum to tell us what you like or dislike and what you would like to see more. Your feedback is very important to us and we will do everything to fulfil your wishes. more ...</p>\r\n<p>&nbsp;</p>\r\n<p><a href="user/">user/</a></p>\r\n<p><a href="subscribe/">subscribe/</a></p>', NULL, '0000-00-00 00:00:00', 0),
(26, 1, 'ru', '', NULL, '0000-00-00 00:00:00', 0),
(30, 1, 'ru', 'ыв', NULL, '0000-00-00 00:00:00', 0),
(31, 1, 'ru', '', NULL, '0000-00-00 00:00:00', 0),
(32, 1, 'ru', '<br /> Подписка на рассылку', NULL, '0000-00-00 00:00:00', 0),
(33, 1, 'ru', '<br />Облако тэгов:', NULL, '0000-00-00 00:00:00', 0),
(34, 1, 'ru', 'Тестирование дубликата', NULL, '0000-00-00 00:00:00', 0),
(35, 1, 'ru', '', NULL, '0000-00-00 00:00:00', 0),
(36, 1, 'ru', '', NULL, '0000-00-00 00:00:00', 0),
(37, 1, 'ru', '<h1>Восстановление пароля 2</h1>', NULL, '2011-09-22 00:00:43', 1),
(38, 1, 'ru', '1123', NULL, '2012-01-06 06:28:22', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `text_items_history`
--

DROP TABLE IF EXISTS `text_items_history`;
CREATE TABLE IF NOT EXISTS `text_items_history` (
  `history_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `language_id` varchar(8) NOT NULL,
  `item_id` int(10) NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text_archive` mediumblob NOT NULL,
  `unpack_length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Длина распакованной строки в байтах.',
  PRIMARY KEY (`history_id`,`site_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='История изменения текстов' AUTO_INCREMENT=18 ;

--
-- Дамп данных таблицы `text_items_history`
--

INSERT INTO `text_items_history` (`history_id`, `site_id`, `is_deleted`, `language_id`, `item_id`, `user_id`, `update_time`, `text_archive`, `unpack_length`) VALUES
(1, 1, 0, 'ru', 17, 1, '2011-09-24 12:56:32', 0x78dab329b0bbb0fc62d3857dba2042e1c2d68b9d17b62a5cd87b6183c285cd17765fd870611390b3efc24e052b4d850b5b807c20b6d2d454504a342a5652cb4b2a2eb05630b6d12fb003006b0226f7, 74),
(2, 1, 0, 'ru', 17, 1, '2011-09-25 01:06:36', 0x78dab329b0bbb0fc62d3857dba2042e1c2d68b9d17b62a5cd87b6183c285cd17765fd870611390b3efc24e052b4d850b5b807c20b6d2d454504a342a5652cb4b2a2eb05630b2d12fb003006afd26f6, 74),
(3, 1, 0, 'ru', 37, 1, '2011-12-03 13:28:49', 0x78dab3c930b4bb30e9c2be8b8d40d87461c385bd17f65dd87461f785ad40d68e0b5b152eecbfb0e162035070f7c57e05231b7da07a007bdc204f, 52),
(4, 2, 0, 'ru', 21, 1, '2011-12-05 10:11:17', 0x78da65523d4fc4300cdd91f80fd60ddcd68a99d2818d8501b130a68ddb84a67114a757eedfe324f73120554a6c3f3fbfe7b433cffd9b5d103eadc6c8f0813b77ad241f1fbad07f19cb209f82846b702a2168643b7bd4309ca1e314c9cffd141161c781ad00ae48a1b99461a20867daca59b03918958788021d119473904ceefd4d9958ea1168f725d1c055c6cfc649b4d41e434e1403d38d8d3167f7c254a4ecb4390d8e6801271e1b789f32f81805a74ed6cf10220d0e5706d436e5445571f11a1c2a16cbe48f098c184f399b08142fc58c4117807ce992785b1bf81631bb153f33a65a572c2205246720662b032b2c6b564e0c6c32e44691f9130ac1c6b01b958abd2c1f64a0b65caecaeb7bf16233e7a5376f61a55867c8be11f5a0c625efef84f10c760d1493f229836546a1c2aa5913600625537641306d6eb2aebec76e5976d01472689aa66b43fd4b9efcc0e1e516760a4cc4e9f520b6627be8cbd1b5aaff8fe06de031da010576bf5fb17fe39de908, 668),
(5, 1, 0, 'ru', 17, 1, '2011-12-23 06:07:08', 0x78dab329b0bbb0fc62d3857dba2042e1c2d68b9d17b62a5cd87b6183c285cd17765fd870611390b3efc24e052b4d850b5b807c20b6d2d454504a342a5652cb4b2a2eb05630b5d12fb003006b0c26f9, 74),
(6, 1, 0, 'ru', 17, 1, '2011-12-23 06:11:33', 0x78dab329b0bbb0fc62d3857dba2042e1c2d68b9d17b62a5cd87b6183c285cd17765fd870611390b3efc24e052b4d850b5b807c20b6d2d454504a342a5652cb4b2a2eb056b0d12fb00300445826c4, 73),
(7, 1, 0, 'ru', 17, 1, '2011-12-27 17:40:10', 0x78dab329b0bbb0fc62d3857dba2042e1c2d68b9d17b62a5cd87b6183c285cd17765fd870611390b3efc24e052b4d850b5b807c20b6d2d454504a342a5652cb4b2a2eb05630b2d12fb003006afd26f6, 74),
(8, 1, 0, 'ru', 17, 1, '2012-01-04 14:52:44', 0x78dab329b0bbb0fc62d3857dba2042e1c2d68b9d17b62a5cd87b6183c285cd17765fd870611390b3efc24e052b4d850b5b807c20b6d2d454504a342a5652cb4b2a2eb05630b6d12fb003006b0226f7, 74),
(9, 1, 0, 'ru', 38, 1, '2012-01-06 06:31:18', 0x78da33343432060001f200c8, 4),
(10, 1, 0, 'ru', 38, 1, '2012-01-06 06:31:33', 0x78da3334343256303206000541014d, 7),
(11, 1, 0, 'ru', 38, 1, '2012-01-06 06:31:54', 0x78da33343432060001f200c8, 4),
(12, 1, 0, 'ru', 38, 1, '2012-01-06 06:36:15', 0x78da3334343256482c4e010007ff0220, 8),
(13, 1, 0, 'ru', 38, 1, '2012-01-06 06:36:22', 0x78da333434325648040004230149, 6),
(14, 1, 0, 'ru', 38, 1, '2012-01-06 06:42:03', 0x78da33343432564834030005a2017f, 7),
(15, 1, 0, 'ru', 38, 1, '2012-01-06 06:45:04', 0x78da33343432060001f200c8, 4),
(16, 1, 0, 'ru', 38, 1, '2012-01-06 06:45:42', 0x78da3334343236010002ee00fc, 5),
(17, 1, 0, 'ru', 38, 1, '2012-01-06 07:48:44', 0x78da33343432060001f200c8, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `users_accounts`
--

DROP TABLE IF EXISTS `users_accounts`;
CREATE TABLE IF NOT EXISTS `users_accounts` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `nickname` varchar(100) NOT NULL COMMENT 'Псевдоним',
  `fullname` varchar(100) DEFAULT NULL COMMENT 'Полное имя',
  `email` varchar(128) DEFAULT NULL,
  `dob` date DEFAULT NULL COMMENT 'Дата рождения',
  `gender` enum('M','F') DEFAULT NULL COMMENT 'Пол',
  `language` varchar(2) DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL COMMENT 'Временная зона.',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания аккаунта',
  `create_on_site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'С какого проекта создан аккаунт',
  `zzz_last_login_datetime` datetime DEFAULT NULL COMMENT 'Дата последней авторизации',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Учетные записи пользователей' AUTO_INCREMENT=102 ;

--
-- Дамп данных таблицы `users_accounts`
--

INSERT INTO `users_accounts` (`user_id`, `is_active`, `nickname`, `fullname`, `email`, `dob`, `gender`, `language`, `timezone`, `create_datetime`, `create_on_site_id`, `zzz_last_login_datetime`) VALUES
(1, 1, 'Администратор', 'Артём', 'admin@localhost.org', '1111-11-23', 'M', 'ru', 'Asia/Novosibirsk', '2011-05-03 00:00:00', 1, NULL),
(101, 1, 'test', NULL, 'test@localhost.org', NULL, NULL, NULL, NULL, '2011-06-03 23:19:33', 1, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `users_accounts_activation`
--

DROP TABLE IF EXISTS `users_accounts_activation`;
CREATE TABLE IF NOT EXISTS `users_accounts_activation` (
  `login` varchar(255) NOT NULL,
  `start_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `key` varchar(128) NOT NULL,
  PRIMARY KEY (`login`),
  UNIQUE KEY `key` (`key`),
  KEY `end_datetime` (`end_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Ожидающие активации';

--
-- Дамп данных таблицы `users_accounts_activation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `users_accounts_logins`
--

DROP TABLE IF EXISTS `users_accounts_logins`;
CREATE TABLE IF NOT EXISTS `users_accounts_logins` (
  `user_id` int(10) unsigned NOT NULL,
  `login` varchar(150) NOT NULL,
  `password` varchar(128) DEFAULT NULL,
  `salt` varchar(8) DEFAULT NULL,
  `hash_version` tinyint(3) unsigned DEFAULT NULL COMMENT 'Версия алгоритма хеширования',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания логина',
  `create_on_site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'С какого проекта создан логин',
  PRIMARY KEY (`login`),
  KEY `password` (`password`),
  KEY `is_active` (`is_active`),
  KEY `hash_version` (`hash_version`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Логины пользователей';

--
-- Дамп данных таблицы `users_accounts_logins`
--

INSERT INTO `users_accounts_logins` (`user_id`, `login`, `password`, `salt`, `hash_version`, `is_active`, `create_datetime`, `create_on_site_id`) VALUES
(1, 'http://artem.id.mail.ru', NULL, NULL, NULL, 1, '2011-00-00 00:00:00', 0),
(1, 'root', 'd9b1d7db4cd6e70935368a1efb10e377', '413a86af', 2, 1, '2011-00-00 00:00:00', 0),
(101, 'test', 'd9b1d7db4cd6e70935368a1efb10e377', 'b120a00e', 2, 1, '2011-06-03 23:19:33', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `users_accounts_tokens`
--

DROP TABLE IF EXISTS `users_accounts_tokens`;
CREATE TABLE IF NOT EXISTS `users_accounts_tokens` (
  `user_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `login` varchar(255) NOT NULL,
  `token` varchar(128) NOT NULL,
  `valid_to_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Валиден до даты',
  PRIMARY KEY (`site_id`,`token`),
  KEY `valid_to_datetime` (`valid_to_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Токены';

--
-- Дамп данных таблицы `users_accounts_tokens`
--

INSERT INTO `users_accounts_tokens` (`user_id`, `site_id`, `login`, `token`, `valid_to_datetime`) VALUES
(1, 1, 'root', '0355c6e5fadbc8a976609069207551a4b360b0c8f4beba679c7cf7cc4df1f832', '2012-02-10 16:45:22'),
(1, 1, 'root', '059a4a060db34aff4cb8860965e699ca15b40ccf9802e1feba77534bd38c6e07', '2011-12-24 05:21:11'),
(1, 1, 'root', '0a27eda8f40f0de9e5e70fae89fce60a3d2c86caeaf850beab73fe3993ea50d7', '2012-02-10 19:01:54'),
(1, 1, 'root', '0b8e7bd48b7d0aeadfed4fd6753f7866e1d4350e7cca86c7b4e085f3f781ee30', '2011-12-28 21:58:09'),
(1, 1, 'root', '233b7630346fc77e959955518f0fd3a18bc4e64aed7b71bd5fc17f3947b9b018', '2012-02-10 16:42:28'),
(1, 1, 'root', '25a38159a639a0192523b130df81a2d82a8b7ec5b24ecd6cdde96008a7295150', '2012-02-24 06:41:32'),
(1, 1, 'root', '37ba0c094842ca6dbe0c8211cd02bf1388a382d3bec079170d887535439da6ad', '2011-12-20 01:46:49'),
(1, 1, 'root', '3dc34c1c4f3d0cf9b20b0030f856e36746ef3d142474e8d8796130d659626b7b', '2012-02-10 16:45:16'),
(1, 1, 'root', '5910c363966fd0da485f6b4daac6b187d8223a51c178464c3901c92d56d9238d', '2012-02-06 08:51:29'),
(1, 1, 'root', '78f8587c69d347fba7a0a8ead10c78faaad984fb30cbc89037ff0a94290ed1fb', '2012-02-10 16:45:37'),
(1, 1, 'root', 'aa8d20cf75e03ba5802b6fb81beffa44f6922618949f7e69201884ddb8ea785d', '2011-12-20 02:24:48'),
(1, 1, 'root', 'b46c2da7ae7cf1551b300c816943a64b0edc82c3cf7fd8c41b30e2665cb4ce3f', '2012-02-06 08:52:47'),
(1, 1, 'root', 'b4d14fb049e5dff90590fb390049814092ba55ea02b0499b649d17970b6bcba9', '2012-02-06 08:50:33'),
(1, 1, 'root', 'c8fbca62e77e263db7238c2967d6459760c4354b972df44f192ac0ea63793cf5', '2012-02-10 19:04:13'),
(1, 1, 'root', 'cf1f7cf0cf1b2c2c11b8153c7f9ae1cd6fc0e293ee674685496cd46d97b4863f', '2012-02-10 16:46:16'),
(1, 1, 'root', 'dcc704c245bcea5fe34284dabc78cec2449c762c7eb6326d0c981116262e9e44', '2012-02-06 21:29:26'),
(1, 1, 'root', 'dcce775a69a5484389175801a13e72be773e5b7af2c909557951073058d97782', '2012-02-06 08:51:51'),
(1, 1, 'root', 'fa990f9906f853d645fd5d6cd5b69fd96769364620ceb3db26e93644b82ede5f', '2012-02-06 08:52:14'),
(1, 1, 'root', 'fb4acd3c3986558ae833054765bc63c8436ff77fe3854233272c16ff03cb62f6', '2012-02-06 08:49:53'),
(1, 2, 'root', '1423a88e623bbcf9308f049d3c17f578ba71ef6fb03c614af99af7f40c085bd2', '2012-02-23 04:47:19'),
(1, 2, 'root', '261f347a321f1a090ef84069444bb32bf2d66eafe60f4abc5ba5e2b63967c680', '2011-12-19 16:55:55'),
(1, 2, 'root', '5ab8f6b3411e559f2498eb952a2f6cf50a084feb0c9281d77c5abb83e1a2d99b', '2012-01-05 21:50:22');

-- --------------------------------------------------------

--
-- Структура таблицы `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE IF NOT EXISTS `users_groups` (
  `group_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pos` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `descr` varchar(255) NOT NULL COMMENT 'Краткое описание группы юзеров',
  `session_lifetime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`site_id`),
  UNIQUE KEY `name` (`name`,`site_id`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Группы пользователей' AUTO_INCREMENT=12 ;

--
-- Дамп данных таблицы `users_groups`
--

INSERT INTO `users_groups` (`group_id`, `site_id`, `pos`, `name`, `descr`, `session_lifetime`) VALUES
(0, 1, 0, 'guest', 'Анонимные пользователи', 0),
(1, 1, 1, 'root', 'Суперадмин', 0),
(1, 2, 1, 'root', 'Суперадмин', 0),
(2, 1, 2, 'registered', 'Зарегистрированные пользователи', 0),
(2, 2, 2, 'registered', 'Зарегистрированные пользователи', 0),
(3, 1, 3, 'admin', 'Администраторы', 0),
(3, 2, 3, 'admin', 'Администраторы', 0),
(4, 1, 4, 'editors', 'Редакторы', 0),
(5, 1, 5, 'newsmakers', 'Новостеписатели', 0),
(6, 1, 6, 'publicators', 'Публикаторы', 0),
(11, 2, 0, 'guest', 'Анонимные пользователи', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users_groups_includes`
--

DROP TABLE IF EXISTS `users_groups_includes`;
CREATE TABLE IF NOT EXISTS `users_groups_includes` (
  `group_id` smallint(5) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `parent_group_id` smallint(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Включение и наследование групп.';

--
-- Дамп данных таблицы `users_groups_includes`
--

INSERT INTO `users_groups_includes` (`group_id`, `site_id`, `parent_group_id`) VALUES
(5, 1, 2),
(6, 1, 2),
(4, 1, 5),
(4, 1, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `users_groups_relation`
--

DROP TABLE IF EXISTS `users_groups_relation`;
CREATE TABLE IF NOT EXISTS `users_groups_relation` (
  `user_id` int(10) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `group_id` smallint(5) NOT NULL,
  PRIMARY KEY (`user_id`,`site_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Вхождения юзеров в группы.';

--
-- Дамп данных таблицы `users_groups_relation`
--

INSERT INTO `users_groups_relation` (`user_id`, `site_id`, `group_id`) VALUES
(1, 1, 1),
(1, 2, 1),
(101, 1, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `users_groups_translation`
--

DROP TABLE IF EXISTS `users_groups_translation`;
CREATE TABLE IF NOT EXISTS `users_groups_translation` (
  `group_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `descr` varchar(255) DEFAULT NULL,
  UNIQUE KEY `group_language` (`group_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы заголовков и описаний групп пользователей.';

--
-- Дамп данных таблицы `users_groups_translation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `users_local`
--

DROP TABLE IF EXISTS `users_local`;
CREATE TABLE IF NOT EXISTS `users_local` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `nickname` varchar(100) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `dob` date DEFAULT NULL COMMENT 'Дата рождения',
  `gender` enum('M','F') DEFAULT NULL COMMENT 'Пол',
  `language` varchar(2) DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL COMMENT 'Временная зона.',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания аккаунта',
  `last_activity_datetime` datetime DEFAULT NULL COMMENT 'Дата последней авторизации',
  PRIMARY KEY (`user_id`,`site_id`),
  UNIQUE KEY `email-site_id` (`email`,`site_id`),
  KEY `is_active` (`is_active`),
  KEY `last_activity_datetime` (`last_activity_datetime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Локальные пользователи платформы.' AUTO_INCREMENT=102 ;

--
-- Дамп данных таблицы `users_local`
--

INSERT INTO `users_local` (`user_id`, `site_id`, `is_active`, `nickname`, `fullname`, `email`, `dob`, `gender`, `language`, `timezone`, `create_datetime`, `last_activity_datetime`) VALUES
(1, 1, 1, 'Администратор', 'Артём', 'admin@localhost.org', '1111-11-23', 'M', 'ru', 'Asia/Novosibirsk', '2011-05-03 00:00:00', '2012-01-10 06:41:32'),
(1, 2, 1, 'Администратор', 'Артём', 'admin@localhost.org', '1111-11-23', 'M', 'ru', 'Asia/Novosibirsk', '0000-00-00 00:00:00', '2012-01-09 04:47:19'),
(101, 1, 1, 'test', NULL, 'test@localhost.org', NULL, NULL, NULL, NULL, '2011-06-03 23:19:33', '2011-12-28 00:39:22');

-- --------------------------------------------------------

--
-- Структура таблицы `users_recover`
--

DROP TABLE IF EXISTS `users_recover`;
CREATE TABLE IF NOT EXISTS `users_recover` (
  `code` varchar(128) NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `email` varchar(128) NOT NULL,
  `create_datetime` datetime NOT NULL COMMENT 'Время создания запроса.',
  `valid_to_datetime` datetime NOT NULL COMMENT 'Колюч действителен до указанного времени',
  PRIMARY KEY (`code`),
  KEY `email` (`email`),
  KEY `valid_to_datetime` (`valid_to_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица ключей для восстановления паролей';

--
-- Дамп данных таблицы `users_recover`
--


-- --------------------------------------------------------

--
-- Структура таблицы `webforms`
--

DROP TABLE IF EXISTS `webforms`;
CREATE TABLE IF NOT EXISTS `webforms` (
  `form_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `use_captcha` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Использовать ли каптчу, указывается ИД ноды карптчи',
  `params` text,
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`form_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Cписок форм в системе' AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `webforms`
--

INSERT INTO `webforms` (`form_id`, `site_id`, `name`, `use_captcha`, `params`, `create_datetime`, `owner_id`) VALUES
(1, 1, 'contacts_feedback', 0, NULL, '0000-00-00 00:00:00', 1),
(5, 1, 'test', 0, NULL, '2011-07-18 21:20:27', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `webforms_fields`
--

DROP TABLE IF EXISTS `webforms_fields`;
CREATE TABLE IF NOT EXISTS `webforms_fields` (
  `field_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `form_id` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT 'string',
  `default_value` text COMMENT 'Значение по умолчанию',
  `attrs` text COMMENT 'Атрибуты',
  `params` text COMMENT 'Параметры',
  `validators` text COMMENT 'Валидаторы',
  `name` varchar(50) NOT NULL COMMENT 'Служебное имя',
  `title` varchar(255) NOT NULL,
  `descr` varchar(255) DEFAULT NULL,
  `is_required` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Обязателен для заполнения?',
  `service_comment` varchar(255) DEFAULT NULL COMMENT 'Служебный комментарий',
  `create_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата создания',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'id создателя',
  PRIMARY KEY (`field_id`,`site_id`,`form_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Поля форм' AUTO_INCREMENT=9 ;

--
-- Дамп данных таблицы `webforms_fields`
--

INSERT INTO `webforms_fields` (`field_id`, `site_id`, `form_id`, `is_active`, `pos`, `type`, `default_value`, `attrs`, `params`, `validators`, `name`, `title`, `descr`, `is_required`, `service_comment`, `create_datetime`, `owner_id`) VALUES
(1, 1, 1, 1, 1, 'string', NULL, NULL, NULL, NULL, 'name', 'Ваше имя', NULL, 1, 'Имя', '0000-00-00 00:00:00', 1),
(2, 1, 1, 0, 4, 'text', NULL, NULL, '', '', 'contacts', 'Контакты', NULL, 0, 'Контакты', '0000-00-00 00:00:00', 1),
(3, 1, 1, 1, 5, 'text', NULL, NULL, '', '', 'text', 'Текст сообщения', NULL, 1, 'Текст сообщения', '0000-00-00 00:00:00', 1),
(6, 1, 1, 1, 2, 'string', '', '', '', 'email:', 'email', 'Email', '', 0, 'Ваш емаил для связи.', '2011-07-19 00:56:50', 1),
(8, 1, 1, 1, 3, 'select', '', '', 'не указан;мужской;женский', '', 'sex', 'Пол', '', 1, '', '2011-07-19 01:21:23', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `webforms_fields_translation`
--

DROP TABLE IF EXISTS `webforms_fields_translation`;
CREATE TABLE IF NOT EXISTS `webforms_fields_translation` (
  `field_id` int(10) unsigned NOT NULL,
  `site_id` smallint(5) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `descr` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`field_id`,`language_id`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Переводы названий полей форм';

--
-- Дамп данных таблицы `webforms_fields_translation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `webforms_results`
--

DROP TABLE IF EXISTS `webforms_results`;
CREATE TABLE IF NOT EXISTS `webforms_results` (
  `result_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL,
  `form_id` int(10) unsigned NOT NULL,
  `is_readed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Статус прочтения',
  `language_id` varchar(2) NOT NULL,
  `reader_user_id` int(10) unsigned DEFAULT NULL COMMENT 'ID юзера прочитавшего результат',
  `readed_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата прочтения',
  `datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата отправления',
  `sender_user_id` int(10) unsigned NOT NULL COMMENT 'Ид юзера отправителя',
  `result_data` mediumtext NOT NULL COMMENT 'Сериализованное сообщение со всеми полями.',
  `ip` varchar(40) NOT NULL,
  `browser` varchar(50) NOT NULL,
  `browser_version` varchar(50) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `user_agent` text NOT NULL,
  PRIMARY KEY (`result_id`,`site_id`),
  KEY `form_id-site_id` (`form_id`,`site_id`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Результаты отправленных вебформ.' AUTO_INCREMENT=13 ;

--
-- Дамп данных таблицы `webforms_results`
--

INSERT INTO `webforms_results` (`result_id`, `site_id`, `form_id`, `is_readed`, `language_id`, `reader_user_id`, `readed_datetime`, `datetime`, `sender_user_id`, `result_data`, `ip`, `browser`, `browser_version`, `platform`, `user_agent`) VALUES
(1, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 11:04:21', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:8:"фыва";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:12:"фыаыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(2, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 11:57:47', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фываыва";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:25:"ыва фыва фыва ";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(3, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 11:58:12', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фываыва";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:25:"ыва фыва фыва ";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(4, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:01:05', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фывафыа";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:8:"фыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(5, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:06:08', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фывафыа";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:8:"фыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(6, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:06:50', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фывафыа";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:8:"фыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(7, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:07:01', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фывафыа";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:8:"фыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(8, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:07:18', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фывафыа";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:8:"фыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(9, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:07:44', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фывафыа";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:8:"фыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(10, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:12:17', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:14:"фывафыа";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:8:"фыва";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(11, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-11-19 12:12:38', 1, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:35:"фывафыва фыва фыва ";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:16:"aasdf@sdfsdf.com";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:26:"фывафыва фыва ";}}', '127.0.0.1', 'Firefox', '3.6.22', 'Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22'),
(12, 1, 1, 0, 'ru', NULL, '0000-00-00 00:00:00', '2011-12-25 08:44:16', 0, 'a:4:{i:1;a:3:{s:4:"name";s:4:"name";s:5:"title";s:15:"Ваше имя";s:7:"content";s:3:"dfg";}i:6;a:3:{s:4:"name";s:5:"email";s:5:"title";s:5:"Email";s:7:"content";s:0:"";}i:8;a:3:{s:4:"name";s:3:"sex";s:5:"title";s:6:"Пол";s:7:"content";s:17:"не указан";}i:3;a:3:{s:4:"name";s:4:"text";s:5:"title";s:29:"Текст сообщения";s:7:"content";s:4:"sdfg";}}', '127.0.0.1', 'Chrome', '16.0.912.63', 'Windows', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.63 Safari/535.7');

-- --------------------------------------------------------

--
-- Структура таблицы `webforms_translation`
--

DROP TABLE IF EXISTS `webforms_translation`;
CREATE TABLE IF NOT EXISTS `webforms_translation` (
  `form_id` int(10) unsigned NOT NULL,
  `site_id` mediumint(8) unsigned NOT NULL,
  `language_id` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `submit_title` varchar(50) NOT NULL DEFAULT 'Send' COMMENT 'Надпись на кнопке сохраняющей результаты формы.',
  `descr` text COMMENT 'Описание для пользователей',
  `success_message` text COMMENT 'Текст после успешно отправленной формы.',
  PRIMARY KEY (`form_id`,`site_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='переводы названий форм';

--
-- Дамп данных таблицы `webforms_translation`
--

INSERT INTO `webforms_translation` (`form_id`, `site_id`, `language_id`, `title`, `submit_title`, `descr`, `success_message`) VALUES
(1, 1, 'ru', 'Форма обратной связи', 'Отправить', 'Заполните необходимые поля в приведенной ниже форме и нажмите кнопку "Отправить". \r\nМы свяжемся с вами удобным для вас способом, в удобное для вас время.', 'Ваш запрос успешно принят.'),
(5, 1, 'ru', 'Тестовая веб-форма', 'Отправить', '', NULL);
