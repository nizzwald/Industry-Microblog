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

global $db, $jk, $_USER;

$page = (int)$_GET['page'];
$user = $_GET['u'];
$params = PARAMS;

if ($params == 'profile') {
	if ($_GET['u']) {
		$userid = $db->getIDFromUsername($_GET['u']);
		if (!$userid) die(__('ERROR: The provided username does not exists'));
	}
	elseif ($_GET['id']) {
		$user = $db->getUsernameFromID($_GET['id']);
		if (!$user) die(__('ERROR: The provided user ID is not valid'));
		$userid = (int) $_GET['id'];
	}
	else die(__('ERROR: You must provide either an username or an user ID'));

	$userInfo = $db->getUserOptions($userid, array('privacy'));

	if ($userInfo['privacy']['allow_read_rss'] == 3) {
		$result = $db->getNotes('archive', getStart($page), $jk->notes_per_page, $userid);
		if (!$result) $items = array();

		$title = __('RSS notes feed for: ') . $user;
		$desc = __('Notes of the selected user');
		$link = 'rss/profile?u='.urlencode($user);

		createRSS($result, $title, $desc, $link);
	}
	else die(__("ERROR: The user doesn't allows you to access it's rss"));
} elseif ($params == 'friends') {	
	if ($_USER['ID'] != $userid) die('ERROR: You are not allowed to read this RSS');
	else {
		$result = $db->getNotes('friendsof', getStart($page), $jk->notes_per_page, $_USER['ID']);
		if (!$result) $items = array();

		$title = __('RSS notes feed for the friends timeline of: ') . $_USER['username'];
		$desc = __('Notes of the friends of the selected user');
		$link = 'rss/friends';

		createRSS($result, $title, $desc, $link);
	}
} elseif ($params == 'tag') {
	$extra = EXTRA;
	if (!$db->checkTag($extra)) die(__('ERROR: There are no notes with this tag'));
	else {
		$result = $db->getNotes('tag', getStart($page), $jk->notes_per_page, $extra);

		$title = __('RSS notes feed for the tag: #').$extra;
		$desc = __('Notes that contain the tag #').$extra;
		$link = 'rss/tag/'.urlencode($extra);

		createRSS($result, $title, $desc, $link);
	}
}

?>