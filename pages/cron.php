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

ignore_user_abort(true);
set_time_limit(1800);

global $db;
global $_USER;
global $jk;

$PARAMS = PARAMS;

switch ($PARAMS) {
case 'clean':
	if ($_GET['p'] == $jk->cron_password) $db->cleanTwitter(); else die('<h1>Invalid password.</h1>');
	break;
case 'update':
	$userID = (int) $_GET['user'];
	if ($userID) {
		global $db;
		global $USER;
		$USER = $db->getUserOptions((int)$userID, array('twitter'));
		if ($USER) {
			$USER = array_merge($USER, array('ID'=>$userID));
			if (has_twitter($USER)) {
				
				processTweets($userID, 'twitter', retrieveTweets('statuses/friends_timeline'));
				processTweets($userID, 'twitter_reply', retrieveTweets('statuses/replies'));
			}
		}
	}
	break;
case 'twitter':
	if (!$_GET['note']) die("There's no note.");
	if (!$_GET['user_id']) die("There's no user."); else $user_id = $_GET['user_id'];

	$note = $db->getNoteInfo($_GET['note']);
	if (!$note) die("It doesn't exists. (note)");
	$user = $db->getUserInfo($user_id);
	if (!$user) die("It doesn't exists. (user)");
	if ($_GET['auth'] != md5($user['salt'])) die("403. Forbidden");
	else {

		if (!has_twitter($user)) return;

		//Removing note's format.
		$note['note'] = preg_replace('/\[(\*|\/|_|\-)(.+)\]/U', '$2', $note['note']);

		if ($note['attached_file']) {
			//The URL of the file.
			$url = coreLink('download', $note['ID'], $note['attached_file']);

			//We're trying to short the download URL.
			$shorter_service = unserialize(stripslashes($user['shorter_service']));
			if ($shorter_service['service'] == 'default') {
				if (!empty($jk->default_shorter_service)) $sh_service = $jk->default_shorter_service;
				else $sh_service = 'none';
			}
			else $sh_service = $shorter_service['service'];
			if ($sh_service != 'none') {
				$url = shorter_url(array($url), $sh_service);
			}

			if ((strlen($note['note']) + strlen($url)) > 140) {
				$calc = strlen($url) + 9;
				$note['note'] = substr($note['note'], 0, (strlen($note['note']) - $calc))." (...) - ".$url;
			}
			else $note['note'] = $note['note']." - $url";
		}
		
		import('twitter/toauth.class');
			
		//Calling the tOAuth class with the user keys
		$connection = new tOAuth($jk->tw_consumerkey, $jk->tw_secretkey, $user['twitter']['oauth_token'], $user['twitter']['oauth_token_secret']);

		//And we send the note to Twitter
		$connection->post('statuses/update', array('status' => $note['note']));
	}
	break;
}

?>