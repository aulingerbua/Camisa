ALTER TABLE `members` ADD COLUMN
  ( `name` varchar(40) NOT NULL DEFAULT '',
  `street` varchar(100) DEFAULT NULL,
  `areacode` varchar(5) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `tel` varchar(40) DEFAULT NULL,
  `mobile` varchar(40) DEFAULT NULL,
  `homepage` varchar(40) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `picture` varchar(100) DEFAULT NULL,
  `options` varchar(100) DEFAULT NULL,
  `since` datetime DEFAULT NULL );

ALTER TABLE `members` ADD CONSTRAINT
      unique_email UNIQUE (email);

UPDATE members t1
JOIN profiles t2
    ON t1.uid = t2.uid
SET t1.homepage = t2.homepage,
    t1.name = t2.name,
    t1.street = t2.street,
    t1.areacode = t2.plz,
    t1.city = t2.city,
    t1.tel = t2.tel,
    t1.mobile = t2.mobile,
    t1.description = t2.description,
    t1.picture = t2.picture;

DROP TABLE profiles;

ALTER TABLE textpages ADD `options` VARCHAR (100) DEFAULT NULL;