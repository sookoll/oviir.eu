DROP TABLE IF EXISTS dev_2012_events;
CREATE TABLE IF NOT EXISTS dev_2012_events (
  event_id int NOT NULL,
  year int NOT NULL PRIMARY KEY,
  category varchar(25) DEFAULT 'kokkutulek',
  event_time varchar(50) DEFAULT NULL,
  event_location varchar(200) DEFAULT NULL,
  event_organizer varchar(200) DEFAULT NULL,
  title varchar(255) DEFAULT NULL,
  picture varchar(255) DEFAULT NULL,
  content longtext DEFAULT NULL,
  status varchar(20) NOT NULL DEFAULT 'done',
  author bigint(20) NOT NULL DEFAULT 0,
  added datetime NOT NULL,
  modified datetime NOT NULL,
  deleted tinyint(1) NOT NULL DEFAULT 0
);

// status 'done','deal','wish'

// posts from wp-posts table (kokkutulekud)
INSERT INTO dev_2012_events (event_id,year,event_time,event_location,event_organizer,title,picture,author,added,modified)
SELECT 
CAST(SUBSTRING_INDEX(post_title,' ',1) AS DECIMAL),
CAST(SUBSTRING_INDEX(post_title,' ',-1) AS DECIMAL),
SUBSTRING_INDEX(SUBSTRING_INDEX(post_content,'</td>',4),'<td>',-1),
SUBSTRING_INDEX(SUBSTRING_INDEX(post_content,'</td>',6),'<td>',-1),
SUBSTRING_INDEX(SUBSTRING_INDEX(post_content,'</td>',2),'<td>',-1),
post_title,
SUBSTRING_INDEX(SUBSTRING_INDEX(post_content,'&size=',1),'&item=',-1),
55,post_date,post_modified
FROM `wp_posts` WHERE post_parent=36 AND post_type='page' ORDER BY CAST(SUBSTRING_INDEX(post_title,' ',-1) AS DECIMAL)