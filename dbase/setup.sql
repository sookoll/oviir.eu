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
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `persons`;
CREATE TABLE IF NOT EXISTS `persons` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `firstname` varchar(50) CHARACTER SET utf8 COLLATE utf8_estonian_ci NOT NULL,
  `lastname` varchar(50) CHARACTER SET utf8 COLLATE utf8_estonian_ci DEFAULT NULL,
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
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO `persons` (`firstname`, `lastname`, `birth`, `death`, `bound_with`, `bound_is`, `ancestor`, `address`, `email`, `phone`, `active`, `deleted`, `modified`) VALUES
('Carl', NULL, '1703', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2019-11-20 10:40:10'),
('Kaarel', NULL, '1730', NULL, 5, 'child', NULL, NULL, NULL, NULL, 0, 0, '2019-11-20 10:51:01'),
('Kutsari Mihkel', NULL, '1760', '1844', 6, 'child', NULL, NULL, NULL, NULL, 0, 0, '2019-11-20 10:53:24'),
('???', 'Sepp', NULL, NULL, 7, 'child', NULL, 'Juuru kihelkond, Härgla', NULL, NULL, 0, 0, '2019-11-20 10:54:43'),
('Kaarel', 'Ovir', '1799', '1860', 7, 'child', NULL, 'Noormaa talu, Nurtu', NULL, NULL, 0, 0, '2019-11-20 10:56:22'),
('Ann', 'Wolter', NULL, NULL, 9, 'partner', NULL, NULL, NULL, NULL, 0, 0, '2019-11-20 10:57:09'),
('Mihkel', 'Ovir', '1830', '1914', 9, 'child', NULL, 'Noormaa talu, Nurtu', NULL, NULL, 0, 0, '2019-11-20 10:58:45'),
('Kaarel (Carl)', 'Ovir', NULL, NULL, 9, 'child', NULL, 'Jõelähtme', NULL, NULL, 0, 0, '2019-11-20 11:01:34'),
('Lisa', 'Tihkani Jaani tütar', NULL, NULL, 12, 'partner', NULL, NULL, NULL, NULL, 0, 0, '2019-11-20 11:02:32')
;

-- data from old oviiride_kontaktid table
INSERT INTO `persons` (firstname, lastname, address, email, phone, ancestor, active, deleted, modified)
SELECT firstname, lastname, address, email, phone, ancestor, active, deleted, changed
FROM oviiride_kontaktid
