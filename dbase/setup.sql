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
  `added` datetime NOT NULL DEFAULT NOW(),
  `deleted` tinyint(1) DEFAULT 0
);
