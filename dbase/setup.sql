DROP TABLE IF EXISTS `phototags`;
CREATE TABLE IF NOT EXISTS `phototags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` varchar(25) NOT NULL,
  `item` varchar(100) NOT NULL,
  `user` INT NOT NULL,
  `username` varchar(100) NOT NULL,
  `author` varchar(100) NOT NULL,
  `xi` int not null,
  `yi` int not null,
  `xii` int not null,
  `yii` int not null,
  `added` datetime NOT NULL DEFAULT NOW(),
  `deleted` tinyint(1) DEFAULT 0
);
