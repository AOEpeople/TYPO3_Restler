#
# Table structure for table 'cache_restler'
#
CREATE TABLE cache_restler (
  id int(11) NOT NULL auto_increment,
  identifier varchar(250) NOT NULL default '',
  expires int(11) unsigned NOT NULL default '0',
  content mediumblob,
  PRIMARY KEY  (id),
  KEY cache_id (identifier(180),expires)
) ENGINE=InnoDB;

#
# Table structure for table 'cache_restler_tags'
#
CREATE TABLE cache_restler_tags (
  id int(11) NOT NULL auto_increment,
  identifier varchar(250) NOT NULL default '',
  tag varchar(250) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY cache_id (identifier(191)),
  KEY cache_tag (tag(191))

) ENGINE=InnoDB;
