DROP TABLE IF EXISTS dev_2013_documents;
CREATE TABLE IF NOT EXISTS dev_2013_documents (
  id int AUTO_INCREMENT PRIMARY KEY,
  type varchar(8) NOT NULL,
  content longtext DEFAULT NULL,
  related_with bigint(20) DEFAULT NULL,
  title varchar(50) DEFAULT NULL,
  description longtext DEFAULT NULL,
  author bigint(20) NOT NULL DEFAULT 1,
  added datetime NOT NULL,
  modified_by bigint(20) DEFAULT NULL,
  modified datetime DEFAULT NULL,
  deleted tinyint(1) NOT NULL DEFAULT 0
);

-- type values can be document, link, video, sound, picture