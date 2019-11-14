DROP TABLE IF EXISTS dev_2013_subforums;
CREATE TABLE IF NOT EXISTS dev_2013_subforums (
  id bigint(20) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  category int NOT NULL DEFAULT 0,
  title varchar(255) NOT NULL,
  added datetime NOT NULL,
  deleted int(1) DEFAULT 0
);

DROP TABLE IF EXISTS dev_2013_topics;
CREATE TABLE IF NOT EXISTS dev_2013_topics (
  id bigint(20) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  subforum int DEFAULT NULL,
  type varchar(6) DEFAULT 'topic' NOT NULL,
  topic bigint(20) DEFAULT NULL,
  sticky int(1) DEFAULT 0,
  author bigint(20) NOT NULL DEFAULT 0,
  title varchar(255) NOT NULL,
  content longtext NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'published',
  added datetime NOT NULL,
  modified datetime NOT NULL,
  lastpost datetime DEFAULT NULL
);

-- type topic, answer
-- status published, deleted, closed