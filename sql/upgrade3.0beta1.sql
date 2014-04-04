ALTER TABLE  `notes` CHANGE  `twitter`  `twitter` INT( 1 ) NOT NULL;
ALTER TABLE  `settings` CHANGE  `category`  `category` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , CHANGE  `value`  `value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `tags_c` CHANGE  `founder`  `founder` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `tags_n` CHANGE  `tag`  `tag` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `users` CHANGE  `password`  `password` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , CHANGE  `language`  `language` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `openid` CHANGE  `url`  `url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , CHANGE  `server`  `server` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `permissions` CHANGE  `can_panel`  `can_panel` INT( 1 ) NULL DEFAULT  '0';
ALTER TABLE  `tags_c` DROP INDEX  `id` , ADD INDEX  `id` (  `id` )
ALTER TABLE  `tags_n` CHANGE  `id`  `id` INT( 11 ) NOT NULL DEFAULT NULL AUTO_INCREMENT
ALTER TABLE  `twitter` CHANGE  `hash`  `hash` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
ALTER TABLE  `tags_c` ADD PRIMARY KEY (  `id` )
ALTER TABLE  `settings` ADD PRIMARY KEY (  `id` )
ALTER TABLE  `tags_n` ADD PRIMARY KEY (  `id` )
ALTER TABLE  `tags_n` DROP INDEX  `id`
ALTER TABLE  `tags_c` DROP INDEX  `id`
ALTER TABLE  `settings` DROP INDEX  `id`
ALTER TABLE  `notes` DROP INDEX  `twitter`
ALTER TABLE  `notes` CHANGE  `reply_user`  `reply_user` INT( 11 ) NOT NULL
ALTER TABLE  `relationships` CHANGE  `read`  `read` SMALLINT( 1 ) NOT NULL DEFAULT  '1'
INSERT INTO `settings` (`id`, `category`, `value`) VALUES (40, 'tw_consumerkey', 'luvmffKg24NhCnTzQf0vGA'),(41, 'tw_secretkey', '0TVlWXwzhw91Tjwkv5KGxsnSebvFkSe2Kdi6dKuow')