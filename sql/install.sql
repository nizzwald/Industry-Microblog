CREATE TABLE IF NOT EXISTS `api` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `url` varchar(128) NOT NULL,
  `approved` int(1) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`),
  KEY `type` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `favorites` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `user_id` (`user_id`,`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `invitations` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(8) NOT NULL,
  `email` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `token` (`token`,`user_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `keys` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` char(12) NOT NULL,
  `type` enum('activation','email','password','drop') NOT NULL,
  `email` varchar(64) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mentions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('public','private') NOT NULL DEFAULT 'public',
  `twitter` int(1) NOT NULL,
  `note` text NOT NULL,
  `attached_file` text,
  `from` varchar(10) NOT NULL,
  `replying` int(11) DEFAULT NULL,
  `timestamp` int(10) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `reply_user` int(11) NOT NULL,
  `read` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `user_id` (`user_id`,`type`,`twitter`),
  KEY `type` (`type`),
  KEY `from` (`from`),
  KEY `replying` (`replying`),
  KEY `timestamp` (`timestamp`),
  KEY `ID` (`ID`,`type`),
  KEY `type_2` (`type`,`timestamp`),
  KEY `type_3` (`type`,`user_id`),
  KEY `reply_user` (`reply_user`),
  KEY `read` (`read`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `openid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `server` varchar(255) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `can_panel` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `post2id` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) DEFAULT NULL,
  `note_id` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `reply_user` int(11) NOT NULL,
  `type` enum('public','twitter','twitter_reply') NOT NULL DEFAULT 'public',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `from` (`from`,`to`,`note_id`),
  KEY `to` (`to`,`note_id`),
  KEY `timestamp` (`timestamp`),
  KEY `note_id` (`note_id`),
  KEY `to_2` (`to`,`timestamp`),
  KEY `reply_user` (`reply_user`),
  KEY `to_3` (`to`,`timestamp`,`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `relationships` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL,
  `who` int(11) NOT NULL,
  `both` smallint(1) NOT NULL,
  `read` smallint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `creator` (`creator`,`who`),
  KEY `who` (`who`),
  KEY `both` (`both`,`creator`),
  KEY `who_2` (`who`,`both`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sessions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `time` int(10) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`ID`),
  KEY `user_id` (`user_id`,`time`),
  KEY `hash` (`hash`),
  KEY `time` (`time`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category` (`category`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tags_c` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `founder` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tags_n` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `tag` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `poster` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `twitter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) NOT NULL,
  `note` text NOT NULL,
  `serial` text NOT NULL,
  `hash` varchar(40) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(22) NOT NULL,
  `password` varchar(40) NOT NULL,
  `api` varchar(16) NOT NULL,
  `salt` char(5) NOT NULL,
  `avatar` varchar(32) DEFAULT NULL,
  `language` varchar(5) DEFAULT NULL,
  `theme` varchar(255) NOT NULL,
  `realname` varchar(64) DEFAULT NULL,
  `email` varchar(64) NOT NULL,
  `jabber` varchar(64) DEFAULT NULL,
  `location` varchar(64) DEFAULT NULL,
  `invitations` int(11) DEFAULT NULL,
  `status` enum('ok','nc','banned') NOT NULL DEFAULT 'ok',
  `since` int(10) NOT NULL,
  `last_seen` int(10) NOT NULL,
  `last_follow` int(10) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `jabber_notifications` int(1) NOT NULL DEFAULT '1',
  `notification_level` int(1) NOT NULL DEFAULT '2',
  `ignored` text,
  `last_note` int(10) NOT NULL,
  `shorter_service` varchar(255) NOT NULL DEFAULT 'a:3:{s:7:"service";s:7:"default";s:4:"data";s:0:"";s:7:"preview";b:0;}',
  `gravatar` int(1) NOT NULL DEFAULT '0',
  `openid` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` text,
  `extras` text,
  `profile` text,
  `customize` text,
  `privacy` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `openid` (`openid`),
  UNIQUE KEY `facebook` (`facebook`),
  KEY `api` (`api`),
  KEY `email` (`email`),
  KEY `since` (`since`),
  KEY `jabber` (`jabber`),
  KEY `last_change` (`last_follow`),
  KEY `last_note` (`last_note`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`id`, `category`, `value`) VALUES (1, 'faq_content', '<h2>What is %name%?</h2>\r\n<br />\r\n\r\n<p>%name% is based on <a href="http://jisko.org">Jisko</a>, which is an open-source microblogging application. It''s source code can be obtained <a href="http://jisko.org">here</a>. You can report bugs or submit patches at their <a href="http://launchpad.net/jisko">Launchpad page.</a></p>\r\n<br />\r\n\r\n<h2>Using %name%</h2>\r\n\r\n<br />\r\n<p>You can update your %name% status from a few places:\r\n<br />\r\n<ul>\r\n<li>First, you can simply go to <a href="%url%">the main page</a>, log in using your username and password, and then you can write your note.</li>\r\n<br />\r\n\r\n<li>If the admin has enabled it, you can also update from your Jabber account through a Jabber bot.</li>\r\n<br />\r\n\r\n<li>Or you can also update from your mobile with the <a href="%mobile_url%">mobile adapted site</a>. If you have an iPhone or an iPod touch, you will see a nicer interface adapted to your fingers</li>\r\n<br />\r\n\r\n<li>If you have a Wordpress blog, you can use <a href="http://code.google.com/p/wp2jisko/">WP2Jisko</a>, an application that writes a note in %name% when a new post is published.</li>\r\n</ul>\r\n<br />\r\n\r\n<p>To reply one of your buddies'' status, you must first type <strong>@</strong> symbol, then the username of the person who you are going to reply, and then the message. Example: <i>@johndoe hey!</i>.</p>\r\n<br />\r\n\r\n<p>If you prefer to send a private message, you must first type <strong>!</strong> symbol instead of the <strong>@</strong>. It works like replying to a status, but with a different symbol. Example: <i>!johndoe ok, see you there!</i>.</p>\r\n<br />\r\n\r\n<h2>%name% & Twitter integration</h2>\r\n\r\n<br />\r\n<p>Have you got a Twitter account? Then you can keep informed your followers, at the same time as you read them. Just add your account at the Twitter tab in the settings page and you''ll be able to do it.</p>\r\n\r\n<br />\r\n<p>You can reply to your Twitter friends'' notes just using the <strong>%</strong> symbol instead of the <strong>@</strong>. It works like replying to a status, but with a different symbol. Example: <i>%Marquitox that sounds good!</i>.</p>\r\n\r\n<br />\r\n<h2>Customize your profile</h2>\r\n\r\n<br />\r\n<p>All users can customize their profiles. Take a look at the settings page, where you can upload your favorite background image, your avatar or choose a different theme.</p>\r\n\r\n<br />\r\n<h2>Jisko apps</h2>\r\n<br />\r\n\r\n<p>If you want, you can send a note every time you update your WordPress blog. WP2Jisko can do it automagically. All you need to do is installing the plugin, type your username and API Key, and that''s all. If you want you can customize the message.</p>\r\n<br />\r\n\r\n<p>You can also put your last %name% note inside your blog''s sidebar, using <a href="http://rick.jinlabs.com/code/jisko/">Jisko for WordPress</a>.</p>'),
(2, 'homepage_content', '<span style="font-size: 14px; font-family: Tahoma;">%name% it''s a webpage that allows you to publish whatever you''re doing at any moment in a message of only 140 characters. Usually this is enough to express your feelings or whatever you want to say.</span><br><br><img src="%url%static/img/p_statuses.png"><br><br><span style="font-size: 14px; font-family: Tahoma;">You can meet new people or add friends you already know. The more people you add, the more you''ll like %name%, because you can easily comment what an user has written.<br><br>If you''re not registered, then click on the orange button placed in the sidebar and in just 1 minute you''ll start enjoying %name%!<br><br><a style="font-size: 19px;" href="%faq_url%">Have a look at the Frequently Asked Questions</a></span>'),(3, 'tos_content', ''),(4, 'name', 'Jisko'),(5, 'base_url', 'localhost'),(6, 'admin_mail', 'foo@bar.com'),(7, 'abuse_mail', 'foo@bar.com'),(8, 'cron_pw', '1234'),(9, 'meta_keywords', '%name%, microblogging, nanoblogging, %language%'),(10, 'meta_description', '%name% microblogging site powered by Jisko, an open-source microblogging application.'),
(11, 'meta_robots', 'noodp,noydir'),(12, 'separator', '//'),(13, 'wait_until_repost', '10'),(14, 'wait_until_refollow', '15'),(15, 'ajax_refresh', '30'),(16, 'language', 'def'),(17, 'notes_per_page', '25'),(18, 'clean_urls', '0'),(19, 'use_invitations', '0'),(20, 'enable_mbstring', '0'),(21, 'alert_on_deluser', '0'),(22, 'alert_on_newuser', '0'),(23, 'allowed_url_shorters', 'a:19:{i:0;s:4:"none";i:1;s:4:"3.ly";i:2;s:7:"ves.cat";i:3;s:6:"pic.gd";i:4;s:5:"is.gd";i:5;s:6:"bit.ly";i:6;s:4:"j.mp";i:7;s:9:"urlal.com";i:8;s:4:"u.nu";i:9;s:5:"ta.gd";i:10;s:11:"tinyurl.com";i:11;s:7:"wipi.es";i:12;s:6:"xrl.us";i:13;s:8:"tinyarro";i:14;s:7:"cort.as";i:15;s:6:"url.ba";i:16;s:5:"ir.pe";i:17;s:7:"urli.nl";i:18;s:11:"recorta.com";}'),(24, 'default_url_shorter', 'ves.cat'),(25, 'default_theme', 'transparency'),(26, 'allowed_themes', 'a:1:{i:0;s:12:"transparency";}'),(27, 'threely_apicode', ''),(28, 'bitly_login', ''),(29, 'bitly_apicode', ''),(30, 'recaptcha_publickey', ''),(31, 'recaptcha_secretkey', ''),(32, 'denied_extensions', 'a:6:{i:0;s:3:"exe";i:1;s:3:"bat";i:2;s:3:"php";i:3;s:4:"html";i:4;s:3:"com";i:5;s:7:"torrent";}'),(33, 'home_page', 'home_page'),(34, 'fb_apikey', ''),(35, 'fb_secretkey', ''),(36, 'tos', '0'),(37, 'maintenance', ''),(38, 'logo', 'logo.png'),(39, 'no_confirmation_email', '0'),(40, 'tw_consumerkey', ''),(41, 'tw_secretkey', '');
