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

global $jk;
global $db;
global $_USER;
global $unread_privates, $unread_replies;

$module = MODULE;
$params = PARAMS;

if ($module == 'public' || ($params == 'public')) $params = 'public';
else {
	if (!$_USER) header('Location: '.coreLink('home'));
}

if (isset($_GET['page'])) $jk->current_page = (int) $_GET['page'];
else $jk->current_page = 1;

$start = getStart($jk->current_page);

$jk->title = __('Notes');

if (!$params) $params = 'friends';

if (has_twitter()) {
	if ($_USER['twitter']['combined_view'] == '1') {
		if ($params == 'friends') $params = 'all';
	}
}

//If ?status it's defined, then we fill the textarea with the content
if (isset($_GET['note'])) $jk->textNote = $_GET['note'];
else $jk->textNote = '';

//If ?in_reply_to it's defined, then we reply to that note
if (isset($_GET['in_reply_to'])) $jk->in_reply_to = (int) $_GET['in_reply_to'];
else $jk->in_reply_to = '';

switch ($params) {
case 'private':
	$update = $db->updateUnreadPrivates($_USER['ID']);
case 'private_sent':
case 'twitter':
case 'twitter_replies':
	updateTwitterNotes();
case 'archive':
case 'favorites':
	$jk->notes_result = $db->getNotes($params, $start, $jk->notes_per_page, $_USER['ID']);
	if ($params == 'twitter_replies') $jk->notes_count = $db->countNotes('twitter_reply', $_USER['ID']);
	else $jk->notes_count = $db->countNotes($params, $_USER['ID']);
	break;
case 'delete':
	if (is_numeric(EXTRA)) $db->deleteNote((int)EXTRA, $_USER['ID']);
	if (!isset($_GET['mobile'])) header('Location: '.$_SERVER['HTTP_REFERER']);
	else header('Location: '.coreLink('mobile'));
	die;
	break;
case 'public':
	$jk->notes_result = $db->getNotes('public', $start, $jk->notes_per_page, false, $_USER['ignored']);
	$jk->notes_count = $db->countNotes('public', '', $_USER['ignored']);
	break;
case 'replies':
	$db->updateUnreadReplies($_USER['ID']);
case 'all':
case 'friends':
default:
	if (!isset($jk->notes_result)) $jk->notes_result = $db->getNotes($params, $start, $jk->notes_per_page, $_USER['ID'], $_USER['ignored']);
	if (!isset($jk->notes_count)) $jk->notes_count = $db->countNotes($params, $_USER['ID'], $_USER['ignored']);
	if ($params == 'all' || ($params == 'friends')) {
		if ($_USER) updateTwitterNotes();
	}
}

//We count the unread privates.
$unread_privates = $db->countNotes('unread_private', $_USER['ID']);
//And the unread replies.
$unread_replies = $db->countNotes('unread_reply', $_USER['ID']);

$jk->params_page = $params;

$jk->load('functions');
$jk->load('header');
$jk->load('notes');

global $sidebar;
$sidebar = 'my_profile';

$jk->load('sidebar');
$jk->load('footer');

?>