DROP TABLE IF EXISTS `phototags`;
CREATE TABLE IF NOT EXISTS `phototags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` varchar(25) NOT NULL,
  `item` varchar(100) NOT NULL,
  `user` INT NOT NULL,
  `username` varchar(100) NOT NULL,
  `author` varchar(100) NOT NULL,
  `xi` float not null,
  `yi` float not null,
  `xii` float not null,
  `yii` float not null,
  `deleted` tinyint(1) DEFAULT 0,
  `modified` timestamp NULL DEFAULT current_timestamp()
);

DROP TABLE IF EXISTS `persons`;
CREATE TABLE IF NOT EXISTS `persons` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `firstname` varchar(50) CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL,
  `lastname` varchar(50) CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL,
  `birth` varchar(10) DEFAULT NULL,
  `death` varchar(10) DEFAULT NULL,
  `bound_with` int,
  `bound_is` varchar(10),-- child, partner
  `ancestor` varchar(15) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT NULL,
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT NULL,
  `phone` varchar(100) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `deleted` tinyint(1) DEFAULT 0,
  `modified` timestamp NULL DEFAULT current_timestamp()
);
