DROP TABLE IF EXISTS dev_tree;
CREATE TABLE dev_tree (
	id INT NOT NULL PRIMARY KEY,
	firstname VARCHAR(64) NOT NULL,
	lastname VARCHAR(64) NOT NULL,
	whois INT NOT NULL DEFAULT 1,
	boundwith INT,
	generation INT
);

INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (1,'Endel','Oviir',1,-1,1);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (2,'Helgi','Oviir',2,1,null);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (3,'Linda','Oviir',2,1,null);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (4,'Aino','Oviir',1,1,2);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (5,'Kaljo','Oviir',1,1,2);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (6,'Ülle','Hermann',2,5,null);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (7,'Rutt','Oviir',2,5,null);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (8,'Mihkel','Oviir',1,5,3);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (9,'Kätlin','Veber',2,8,null);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (10,'Kati','Oviir',1,NULL,3);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (11,'Marko','Perek',2,10,null);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (12,'Margus','Oviir',1,10,4);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (13,'Eve','Oviir',1,5,3);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (14,'Taavi','Keegi',2,13,null);
INSERT INTO dev_tree (id,firstname,lastname,whois,boundwith,generation) VALUES (15,'Elenor','Oviir',1,NULL,4);