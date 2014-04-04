<?php
// Jisko: An open-source microblogging application
// Copyright (C) 2008-2010 Rubén Díaz <outime@gmail.com>
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.


class Upgrade20
{
	private $mysqlfd;

	function __construct($DB_HOST, $DB_PORT, $DB_USERNAME, $DB_PASSWORD, $DB_NAME)
	{
		$this->mysqlfd = mysql_connect($DB_HOST.':'.$DB_PORT, $DB_USERNAME, $DB_PASSWORD);
		if ($this->mysqlfd) {
			if (!mysql_select_db($DB_NAME)) {
				return 'mysql';
			}
		}
		return 'mysql';
	}

	function upgrade()
	{
		set_time_limit(0);
		ignore_user_abort(true);

		$queries = array(
			'ALTER TABLE `api` ADD `approved` INT(1) NOT NULL DEFAULT \'0\', ADD `timestamp` INT(11) NOT NULL',
			'ALTER TABLE `favorites` ADD `timestamp` INT(11) NOT NULL',
			"UPDATE `notes` SET `type`='public' WHERE `type`='personal'",
			"ALTER TABLE `notes` CHANGE `type` `type` ENUM('public','private') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'public'",
			"ALTER TABLE `notes` DROP `hash`",
			"ALTER TABLE `notes` DROP `serial`",
			"ALTER TABLE `notes` DROP INDEX  `twitter`",
			"ALTER TABLE `notes` DROP INDEX  `to`",
			"ALTER TABLE `notes` CHANGE `twitter` `twitter` INT(1) NOT NULL",
			"ALTER TABLE `notes` CHANGE `reply_user` `reply_user` INT( 11 ) NOT NULL",
			"ALTER TABLE `notes` CHANGE `read` `read` TINYINT( 1 ) NULL DEFAULT  '1'",
			"ALTER TABLE `notes` DROP INDEX `user_id`, ADD INDEX `user_id` (`user_id`, `type`, `twitter`)",
			"ALTER TABLE  `post2id` DROP  `private_profile`",
			"ALTER TABLE  `post2id` CHANGE  `timestamp`  `timestamp` INT( 11 ) NOT NULL , CHANGE  `reply_user`  `reply_user` INT( 11 ) NOT NULL",
			"ALTER TABLE  `post2id` CHANGE  `type`  `type` ENUM(  'public',  'twitter',  'twitter_reply' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'public'",
			"ALTER TABLE  `users` ADD  `twitter` TEXT",
			"ALTER TABLE  `users` ADD  `extras` TEXT",

			"ALTER TABLE  `users` ADD  `profile` TEXT",
			"ALTER TABLE  `users` ADD  `customize` TEXT",
			"ALTER TABLE  `users` CHANGE  `ignored`  `ignored` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL",
			"ALTER TABLE `post2id` DROP INDEX `from_2`, ADD UNIQUE `from` (`from`, `to`, `note_id`)",
			"ALTER TABLE  `relationships` CHANGE  `both`  `both` SMALLINT( 1 ) NOT NULL",
			"ALTER TABLE  `settings` ADD UNIQUE  `category` (  `category` )",
			"ALTER TABLE  `settings` ADD PRIMARY KEY (  `id` )",
			"ALTER TABLE  `settings` DROP INDEX  `id`",
			"ALTER TABLE  `tags_n` ADD PRIMARY KEY (  `id` )",
			"ALTER TABLE  `tags_n` DROP INDEX  `id`",
			"ALTER TABLE  `tags_c` ADD PRIMARY KEY (  `id` )",
			"ALTER TABLE  `tags_c` DROP INDEX  `id`",
			"ALTER TABLE  `users` ADD  `privacy` TEXT",
			"UPDATE `users` SET `theme`='transparency'",
			"ALTER TABLE  `users` ADD  `openid` VARCHAR( 255 ) NULL , ADD  `facebook` VARCHAR( 255 ) NULL",
			"ALTER TABLE  `users` ADD UNIQUE  `openid` (  `openid` )",
			"ALTER TABLE  `users` ADD UNIQUE  `facebook` (  `facebook` )",
			"ALTER TABLE `users` DROP INDEX `username`, ADD UNIQUE `username` (`username`)",
			"ALTER TABLE  `users` ADD  `gravatar` INT( 1 ) NOT NULL DEFAULT  '0'",
			"ALTER TABLE  `users` CHANGE  `username`  `username` VARCHAR( 22 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `password`  `password` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL",
			"ALTER TABLE  `relationships` ADD  `read` SMALLINT( 1 ) NOT NULL DEFAULT  '1'",
			"ALTER TABLE  `twitter` CHANGE  `ID`  `ID` INT( 11 ) NOT NULL AUTO_INCREMENT",
			"ALTER TABLE  `twitter` CHANGE  `hash`  `hash` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL",
			"ALTER TABLE  `twitter` DROP INDEX  `timestamp`",
			"ALTER TABLE `settings` CHANGE `value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL"
		);

		foreach ($queries as $query) {
			if (!mysql_query($query)) return 'query';
		}

		$query = mysql_query('SELECT `ID`,`to` FROM `notes` WHERE `type`=\'private\'');
		if (mysql_num_rows($query)) {
			while ($row = mysql_fetch_row($query)) {
				$uquery = mysql_query('SELECT `ID` FROM `users` WHERE `username`=\''.$row[1].'\'');
				if (mysql_num_rows($uquery)) {
					$result = mysql_result($uquery, 0);
					mysql_query('UPDATE `notes` SET `reply_user`='.$result.' WHERE `ID`=\''.$row[0].'\'');
				}
			}
		}
		mysql_query("ALTER TABLE `notes` DROP `to`");

		$query = mysql_query('SELECT `ID`, `show_twits`, `post_tweets`, `twitter_username`, `twitter_password`, `twitter_key`, `last_twitter_update` FROM `users`');
		if (mysql_num_rows($query)) {
			while ($row = mysql_fetch_row($query)) {
				$result = array();
				if ($row[1]) $result['combined_view'] = $row[1];
				if ($row[2]) $result['post_tweets'] = $row[2];
				if ($row[3]) $result['username'] = $row[3];
				if ($row[4]) $result['password'] = $row[4];
				if ($row[5]) $result['key'] = $row[5];
				if ($row[6]) $result['last_update'] = $row[6];

				if ($result) {
					$result = serialize($result);
					mysql_query('UPDATE `users` SET `twitter`=\''.mysql_real_escape_string($result).'\' WHERE `ID`=\''.$row[0].'\'');
				}
			}
		}
		mysql_query('ALTER TABLE `users` DROP `twitter_username`, DROP `twitter_password`, DROP `post_tweets`, DROP `last_twitter_update`, DROP `show_twits`, DROP `twitter_key`;');

		$query = mysql_query('SELECT `ID`, `background`, `background_style` FROM `users`');
		if (mysql_num_rows($query)) {
			while ($row = mysql_fetch_row($query)) {
				$result = array();
				if ($row[1]) $result['background'] = $row[1];
				if ($row[2]) $result['background_style'] = $row[2];

				if ($result) {
					$result = serialize($result);
					mysql_query('UPDATE `users` SET `customize`=\''.mysql_escape_string($result).'\' WHERE `ID`=\''.$row[0].'\'');
				}
			}
		}
		mysql_query('ALTER TABLE `users` DROP `background`, DROP `background_style`;');

		$query = mysql_query('SELECT `ID`, `bio`, `url` FROM `users`');
		if (mysql_num_rows($query)) {
			while ($row = mysql_fetch_row($query)) {
				$result = array();
				if ($row[1]) $result['bio'] = $row[1];
				if ($row[2]) $result['url'] = $row[2];

				if ($result) {
					$result = serialize($result);
					mysql_query('UPDATE `users` SET `profile`=\''.$result.'\' WHERE `ID`=\''.$row[0].'\'');
				}
			}
		}
		mysql_query('ALTER TABLE `users` DROP `bio`, DROP `url`;');

		$query = mysql_query('SELECT `ID`, `show_followers`, `private` FROM `users`');
		if (mysql_num_rows($query)) {
			while ($row = mysql_fetch_row($query)) {
				$result = array();
				if ($row[1] == 1) $result['show_followers'] = 3;
				else $result['show_followers'] = 0;
				if ($row[2] == 1) $result['show_notes'] = 1;
				else $result['show_notes'] = 3;

				if ($result) {
					$result = serialize($result);
					mysql_query('UPDATE `users` SET `privacy`=\''.$result.'\' WHERE `ID`=\''.$row[0].'\'');
				}
			}
		}
		mysql_query('ALTER TABLE `users` DROP `private`, DROP `show_followers`;');
		mysql_query('ALTER TABLE users DROP INDEX password');
		mysql_query('ALTER TABLE users DROP INDEX language');
		mysql_query('ALTER TABLE users DROP INDEX theme');
		mysql_query('ALTER TABLE users DROP INDEX salt');
		mysql_query('ALTER TABLE users DROP INDEX avatar');
		mysql_query('ALTER TABLE users DROP INDEX realname');
		mysql_query('ALTER TABLE users DROP INDEX location');

		$query = mysql_query('SELECT `ID`, `language` FROM `users`');
		if (mysql_num_rows($query)) {
			$languages = array_flip($this->list_isocode_languages());
			while ($row = mysql_fetch_row($query)) {
				$lang = $languages[$row[1]];
				mysql_query('UPDATE `users` SET `language`=\''.$lang.'\' WHERE `ID`=\''.$row[0].'\'');
			}
		}
		mysql_query('ALTER TABLE  `users` CHANGE  `language`  `language` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL');

		mysql_query('CREATE TABLE IF NOT EXISTS `permissions` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`userid` int(11) NOT NULL,
			`can_panel` int(1) DEFAULT \'0\',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		mysql_query('CREATE TABLE IF NOT EXISTS `openid` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`url` varchar(255) NOT NULL,
			`server` varchar(255) NOT NULL,
			KEY `id` (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		mysql_query('CREATE TABLE IF NOT EXISTS `mentions` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`note_id` int(11) DEFAULT NULL,
			`user_id` int(11) DEFAULT NULL,
			`timestamp` int(11) DEFAULT NULL,
			`read` tinyint(1) DEFAULT \'0\',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');

		require dirname(__FILE__) . '/../config.php';
		global $globals;

		mysql_query('INSERT INTO `settings` (`id`, `category`, `value`) VALUES
(1, \'faq_content\', \'<h2>What is %name%?</h2>\r\n<br />\r\n\r\n<p>%name% is based on <a href="http://jisko.org">Jisko</a>, which is an open-source microblogging application. It\'\'s source code can be obtained <a href="http://jisko.org">here</a>. You can report bugs or submit patches at their <a href="http://launchpad.net/jisko">Launchpad page.</a></p>\r\n<br />\r\n\r\n<h2>Using %name%</h2>\r\n\r\n<br />\r\n<p>You can update your %name% status from a few places:\r\n<br />\r\n<ul>\r\n<li>First, you can simply go to <a href="%url%">the main page</a>, log in using your username and password, and then you can write your note.</li>\r\n<br />\r\n\r\n<li>If the admin has enabled it, you can also update from your Jabber account through a Jabber bot.</li>\r\n<br />\r\n\r\n<li>Or you can also update from your mobile with the <a href="%mobile_url%">mobile adapted site</a>. If you have an iPhone or an iPod touch, you will see a nicer interface adapted to your fingers</li>\r\n<br />\r\n\r\n<li>If you have a Wordpress blog, you can use <a href="http://code.google.com/p/wp2jisko/">WP2Jisko</a>, an application that writes a note in %name% when a new post is published.</li>\r\n</ul>\r\n<br />\r\n\r\n<p>To reply one of your buddies\'\' status, you must first type <strong>@</strong> symbol, then the username of the person who you are going to reply, and then the message. Example: <i>@johndoe hey!</i>.</p>\r\n<br />\r\n\r\n<p>If you prefer to send a private message, you must first type <strong>!</strong> symbol instead of the <strong>@</strong>. It works like replying to a status, but with a different symbol. Example: <i>!johndoe ok, see you there!</i>.</p>\r\n<br />\r\n\r\n<h2>%name% & Twitter integration</h2>\r\n\r\n<br />\r\n<p>Have you got a Twitter account? Then you can keep informed your followers, at the same time as you read them. Just add your account at the Twitter tab in the settings page and you\'\'ll be able to do it.</p>\r\n\r\n<br />\r\n<p>You can reply to your Twitter friends\'\' notes just using the <strong>%</strong> symbol instead of the <strong>@</strong>. It works like replying to a status, but with a different symbol. Example: <i>%Marquitox that sounds good!</i>.</p>\r\n\r\n<br />\r\n<h2>Customize your profile</h2>\r\n\r\n<br />\r\n<p>All users can customize their profiles. Take a look at the settings page, where you can upload your favorite background image, your avatar or choose a different theme.</p>\r\n\r\n<br />\r\n<h2>Jisko apps</h2>\r\n<br />\r\n\r\n<p>If you want, you can send a note every time you update your WordPress blog. WP2Jisko can do it automagically. All you need to do is installing the plugin, type your username and API Key, and that\'\'s all. If you want you can customize the message.</p>\r\n<br />\r\n\r\n<p>You can also put your last %name% note inside your blog\'\'s sidebar, using <a href="http://rick.jinlabs.com/code/jisko/">Jisko for WordPress</a>.</p>\'),
(2, \'homepage_content\', \'<span style="font-size: 14px; font-family: Tahoma;">%name% it\'\'s a webpage that allows you to publish whatever you\'\'re doing at any moment in a message of only 140 characters. Usually this is enough to express your feelings or whatever you want to say.</span><br><br><img src="%url%static/img/p_statuses.png"><br><br><span style="font-size: 14px; font-family: Tahoma;">You can meet new people or add friends you already know. The more people you add, the more you\'\'ll like %name%, because you can easily comment what an user has written.<br><br>If you\'\'re not registered, then click on the orange button placed in the sidebar and in just 1 minute you\'\'ll start enjoying %name%!<br><br><a style="font-size: 19px;" href="%faq_url%">Have a look at the Frequently Asked Questions</a></span>\'),
(3, \'tos_content\', \'\'),
(4, \'name\', \''.mysql_real_escape_string(NAME).'\'),
(5, \'base_url\', \'localhost\'),
(6, \'admin_mail\', \''.mysql_real_escape_string(ADMIN_MAIL).'\'),
(7, \'abuse_mail\', \''.(defined('ABUSE_MAIL') ? mysql_real_escape_string(ABUSE_MAIL) : 'abuse@foo.com').'\'),
(8, \'cron_pw\', \''.mysql_real_escape_string(CRON_PW).'\'),
(9, \'meta_keywords\', \''.mysql_real_escape_string(META_KEYWORDS).'\'),
(10, \'meta_description\', \''.mysql_real_escape_string(META_DESCRIPTION).'\'),
(11, \'meta_robots\', \''.mysql_real_escape_string(META_ROBOTS).'\'),
(12, \'separator\', \''.mysql_real_escape_string(SEPARATOR).'\'),
(13, \'wait_until_repost\', \''.(int)WAIT_UNTIL_REPOST.'\'),
(14, \'wait_until_refollow\', \''.(int)WAIT_UNTIL_REFOLLOW.'\'),
(15, \'ajax_refresh\', \'30\'),
(16, \'language\', \'def\'),
(17, \'notes_per_page\', \''.(int)NOTES_PER_PAGE.'\'),
(18, \'clean_urls\', \'0\'),
(19, \'use_invitations\', \''.((defined('USE_INVITATION') && (NO_CONFIRMATION_EMAIL == true)) ? 1 : 0).'\'),
(20, \'enable_mbstring\', \''.((defined('ENABLE_MBSTRING') && (ENABLE_MBSTRING == true)) ? 1 : 0).'\'),
(21, \'alert_on_deluser\', \''.((defined('ALERT_ON_DELUSER') && (ALERT_ON_DELUSER == true)) ? 1 : 0).'\'),
(22, \'alert_on_newuser\', \''.((defined('ALERT_ON_NEWUSER') && (ALERT_ON_NEWUSER == true)) ? 1 : 0).'\'),
(23, \'allowed_url_shorters\', \''.mysql_real_escape_string(serialize($globals['allowed_shorter_services'])).'\'),
(24, \'default_url_shorter\', \''.mysql_real_escape_string(DEFAULT_SHORTER_SERVICE).'\'),
(25, \'default_theme\', \'transparency\'),
(26, \'allowed_themes\', \'a:1:{i:0;s:12:"transparency";}\'),
(27, \'threely_apicode\', \''.(defined('THREELY_APICODE') ? mysql_real_escape_string(THREELY_APICODE) : '').'\'),
(28, \'bitly_login\', \''.(defined('BITLY_LOGIN') ? mysql_real_escape_string(BITLY_LOGIN) : '').'\'),
(29, \'bitly_apicode\', \''.(defined('BITLY_APICODE') ? mysql_real_escape_string(BITLY_APICODE) : '').'\'),
(30, \'recaptcha_publickey\', \''.mysql_real_escape_string($globals['recaptcha_public_key']).'\'),
(31, \'recaptcha_secretkey\', \''.mysql_real_escape_string($globals['recaptcha_private_key']).'\'),
(32, \'denied_extensions\', \''.mysql_real_escape_string(serialize($globals['denied_extensions'])).'\'),
(33, \'home_page\', \''.mysql_real_escape_string($globals['main_page']).'\'),
(34, \'fb_apikey\', \'\'),
(35, \'fb_secretkey\', \'\'),
(36, \'tos\', \''.((defined('TOS') && (TOS == true)) ? 1 : 0).'\'),
(37, \'maintenance\', \'\'),
(38, \'logo\', \'logo.png\'),
(39, \'no_confirmation_email\', \''.((defined('NO_CONFIRMATION_EMAIL') && (NO_CONFIRMATION_EMAIL == true)) ? 1 : 0).'\'),
(40, \'tw_consumerkey\', \'\'),
(41, \'tw_secretkey\', \'\');');
	}

	function list_isocode_languages()
	{
		return array(
			'ar' => 'arabic',
			'an' => 'aragonese',
			'ast' => 'asturian',
			'eu' => 'basque',
			'pt_BR' => 'brazilian_portuguese',
			'bg' => 'bulgarian',
			'ca' => 'catalan',
			'zh_CN' => 'chinese',
			'hr' => 'croatian',
			'nl' => 'dutch',
			'en_GB' => 'english_united_kingdom',
			'eo' => 'esperanto',
			'fr' => 'french',
			'gl' => 'galician',
			'de' => 'german',
			'el' => 'greek',
			'it' => 'italian',
			'ja' => 'japanese',
			'nds' => 'low_german',
			'mn' => 'mongolian',
			'pl' => 'polish',
			'pt' => 'portuguese',
			'pt' => 'portuguese_portugal',
			'ro' => 'romanian',
			'ru' => 'russian',
			'es' => 'spanish',
			'sv' => 'swedish',
			'tr' => 'turkish',
			'vi' => 'vietnamese',
			'def' => 'english',
			'def' => 'default'
		);
	}
}

?>