CREATE TABLE `fa_alert` (
  `file` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_author` (
  `author` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`author`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_author_user` (
  `author` int(10) NOT NULL default '0',
  `user` int(10) NOT NULL default '0',
  `public` tinyint(1) unsigned NOT NULL default '1',
  `verify` varchar(255) default NULL,
  PRIMARY KEY  (`author`,`user`),
  KEY `user` (`user`),
  KEY `author` (`author`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_board` (
  `board` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`board`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_bookmark_author` (
  `user` int(10) NOT NULL default '0',
  `author` int(10) NOT NULL default '0',
  `public` tinyint(1) NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`user`,`author`),
  KEY `added` (`added`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_bookmark_search` (
  `search` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) NOT NULL default '0',
  `public` tinyint(1) NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL default '',
  `query` text NOT NULL default '',
  PRIMARY KEY  (`search`),
  KEY `added` (`added`),
  UNIQUE KEY `name` (`user`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_bookmark_series` (
  `user` int(10) NOT NULL default '0',
  `series` int(10) NOT NULL default '0',
  `public` tinyint(1) NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`user`,`series`),
  KEY `added` (`added`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_bookmark_story` (
  `user` int(10) NOT NULL default '0',
  `story` int(10) NOT NULL default '0',
  `public` tinyint(1) NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`user`,`story`),
  KEY `added` (`added`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_category` (
  `category` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`category`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_comment` (
  `comment` int(10) unsigned NOT NULL auto_increment,
  `type` enum('thread','file','story') NOT NULL default 'thread',
  `id` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `ipaddr` int(10) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `edited` tinyint(1) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `parent` int(10) unsigned NOT NULL default '0',
  `depth` int(10) unsigned NOT NULL default '0',
  `position` int(10) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `format` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`comment`),
  KEY `parent` (`parent`),
  KEY `position` (`position`),
  KEY `time` (`time`),
  KEY `type_id` (`type`,`id`),
  KEY `deleted` (`deleted`),
  KEY `type` (`type`),
  KEY `id` (`id`),
  KEY `depth` (`depth`),
  KEY `user` (`user`),
  FULLTEXT KEY `subject_body` (`subject`,`body`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_file` (
  `file` int(10) unsigned NOT NULL auto_increment,
  `story` int(10) unsigned NOT NULL default '0',
  `number` int(10) unsigned default NULL,
  `rating` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) default NULL,
  `size` int(10) unsigned NOT NULL default '0',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(255) default NULL,
  `notes` text,
  `review` varchar(255) default NULL,
  PRIMARY KEY  (`file`),
  UNIQUE KEY `chapter` (`story`,`number`),
  KEY `story` (`story`),
  KEY `number` (`number`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_last_comment` (
  `user` int(10) unsigned NOT NULL default '0',
  `type` enum('thread','file','story') NOT NULL default 'thread',
  `id` int(10) unsigned NOT NULL default '0',
  `comment` int(10) unsigned NOT NULL default '0',
  `replies` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user`,`type`,`id`),
  KEY `user_type` (`user`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_last_thread` (
  `user` int(10) unsigned NOT NULL default '0',
  `board` int(10) unsigned NOT NULL default '0',
  `thread` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user`,`board`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_nickname` (
  `nickname` int(10) unsigned NOT NULL auto_increment,
  `person` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`nickname`),
  UNIQUE KEY `name` (`name`),
  KEY `person` (`person`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_pairing` (
  `pairing` int(10) unsigned NOT NULL auto_increment,
  `prefix` varchar(245) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `person_ids` varchar(255) NOT NULL default '',
  `nickname_ids` varchar(255) NOT NULL default '',
  `genders` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`pairing`),
  UNIQUE KEY `full_name` (`prefix`,`nickname_ids`),
  KEY `person_ids` (`person_ids`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_pairing_nickname` (
  `pairing` int(10) unsigned NOT NULL default '0',
  `nickname` int(10) unsigned NOT NULL default '0',
  `position` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pairing`,`position`),
  KEY `nickname` (`nickname`),
  KEY `position` (`position`),
  KEY `pairing` (`pairing`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_person` (
  `person` int(10) unsigned NOT NULL auto_increment,
  `nickname` int(10) unsigned NOT NULL default '0',
  `gender` char(1) NOT NULL default '',
  PRIMARY KEY  (`person`),
  UNIQUE KEY `nickname` (`nickname`),
  KEY `gender` (`gender`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_rating` (
  `rating` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `age` int(10) unsigned NOT NULL default '0',
  `description` text,
  PRIMARY KEY  (`rating`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_series` (
  `series` int(10) unsigned NOT NULL auto_increment,
  `author` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`series`),
  UNIQUE KEY `author_name` (`author`,`name`),
  KEY `name` (`name`),
  KEY `author` (`author`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_session` (
  `session` varchar(255) NOT NULL default '',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `data` text NOT NULL,
  PRIMARY KEY  (`session`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_story` (
  `story` int(10) unsigned NOT NULL auto_increment,
  `author` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `summary` text NOT NULL,
  `chapter` varchar(255) default NULL,
  `series` int(10) unsigned default NULL,
  `series_order` int(10) unsigned default NULL,
  `rating` int(10) unsigned NOT NULL default '0',
  `updated` datetime default NULL,
  `size` int(10) unsigned NOT NULL default '0',
  `category_ids` varchar(255) NOT NULL default '',
  `pairing_person_ids` varchar(255) NOT NULL default '',
  `pairing_genders` varchar(255) NOT NULL default '',
  `pairing_names` text NOT NULL,
  `file_ids` text NOT NULL,
  `hidden` tinyint(3) unsigned NOT NULL default '0',
  `reviews` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`story`),
  UNIQUE KEY `author_name` (`author`,`name`),
  KEY `author` (`author`),
  KEY `series_order` (`series_order`),
  KEY `series` (`series`),
  KEY `updated` (`updated`),
  KEY `name` (`name`),
  KEY `hidden` (`hidden`),
  KEY `size` (`size`),
  KEY `rating` (`rating`),
  FULLTEXT KEY `all_text` (`name`,`summary`,`pairing_names`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_story_category` (
  `story` int(10) unsigned NOT NULL default '0',
  `category` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`story`,`category`),
  KEY `story` (`story`),
  KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_story_pairing` (
  `story` int(10) unsigned NOT NULL default '0',
  `pairing` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`story`,`pairing`),
  KEY `pairing` (`pairing`),
  KEY `story` (`story`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_subscription` (
  `user` int(10) NOT NULL default '0',
  `type` tinyint(3) NOT NULL default '0',
  `data` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user`,`type`,`data`),
  KEY `user` (`user`),
  KEY `target` (`type`,`data`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_thread` (
  `thread` int(10) unsigned NOT NULL auto_increment,
  `board` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `ipaddr` int(10) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `sticky` tinyint(3) unsigned NOT NULL default '0',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  `comment` int(10) unsigned NOT NULL default '0',
  `replies` int(10) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  PRIMARY KEY  (`thread`),
  KEY `board` (`board`),
  KEY `time` (`time`),
  KEY `deleted` (`deleted`),
  KEY `sticky` (`sticky`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `fa_user` (
  `user` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `dob` date NOT NULL default '0000-00-00',
  `type` enum('user','mod','admin','list','verified','disabled') NOT NULL default 'user',
  `options` text NOT NULL,
  `profile` text NOT NULL,
  `avatar` varchar(255) NOT NULL default '',
  `verify` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user`),
  UNIQUE KEY `name` (`name`),
  KEY `email` (`email`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO fa_rating (name,age) VALUES ('G',0),('PG',13),('PG-13',13),('R',18),('NC-17',18);
