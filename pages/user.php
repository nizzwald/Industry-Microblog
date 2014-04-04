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
global $sidebar;

if (isset($_GET['page'])) {
	$jk->current_page = $_GET['page'];
	$start = getStart($_GET['page']);
}
else {
	$jk->current_page = 1;
	$start = getStart(1);
}

if (is_numeric(MODULE)) $userInfo = $db->getUserInfo(MODULE);
else $userInfo = $db->getUserInfo(false, MODULE);

if (!$userInfo) {
	header('HTTP/1.0 404 Not Found');
	$jk->title = __('Not found');
	$jk->load('functions');
	$jk->load('header');
	$jk->load('404');

	$sidebar = 'my_profile';
}
else {
	if ($userInfo['ID'] == $_USER['ID']) {
		$sidebar = 'my_profile';
	}
	else {
		$jk->viewable_profile = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_profile_info');
		$sidebar = 'profile';
		$jk->selectUser($userInfo['ID'], true);
	}

	$jk->title = str_replace('%username', $userInfo['username'], __('%username\'s profile'));

	//If the user is ignoring somebody, we set $ignored with the array, if not, we set 'false'
	if ($_USER['ignored']) $ignored = $_USER['ignored']; else $ignored = false;
	
	if (MODULE == 'notes') $allowed = array('favorites', 'replies', 'friends', 'public');
	else $allowed = array('favorites', 'following', 'followers');
	
	if (in_array(strtolower(PARAMS), $allowed)) $params = PARAMS;
	else $params = 'default';
	
	$jk->module_page = $params;

	switch ($params) {
	case 'replies':
		$jk->is_viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_notes');
		if ($jk->is_viewable) {
			$jk->notes_result = $db->getNotes('mentions', $start, $jk->notes_per_page, $userInfo['ID'], $ignored);
			$jk->notes_count = $db->countNotes('mentions', $userInfo['ID'], $ignored);
		}
		break;
	case 'friends':
		$jk->notes_result = $db->getNotes('friendsof', $start, $jk->notes_per_page, $userInfo['ID'], $ignored);
		$jk->notes_count = $db->countNotes('friendsof', $userInfo['ID'], $ignored);
		break;
	case 'favorites':
		$jk->is_viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_favorite');

		if ($jk->is_viewable) {
			$jk->notes_result = $db->getNotes('favorites', $start, $jk->notes_per_page, $userInfo['ID']);
			$jk->notes_count = $db->countNotes('favorites', $userInfo['ID']);
		}
		else header('HTTP/1.0 403 Not Authorized');
		break;
	case 'followers':
		$jk->is_viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_followers');

		if ($jk->is_viewable) {
			$jk->users_result = $db->getFollowers($userInfo['ID'], $start, $jk->notes_per_page);
			$jk->users_count = $db->countFollowers($userInfo['ID']);
		}
		else header('HTTP/1.0 403 Not Authorized');
		break;
	case 'following':
		$jk->is_viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_followings');

		if ($jk->is_viewable) {
			$jk->users_result = $db->getFollowing($userInfo['ID'], $start, $jk->notes_per_page);
			$jk->users_count = $db->countFollowing($userInfo['ID']);
		}
		else header('HTTP/1.0 403 Not Authorized');
		break;
	default:
		$jk->is_viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_notes');

		if (is_numeric(PARAMS)) {
			$jk->module_page = 'permalink';
			$jk->notes_count = 1;

			if ($jk->is_viewable) {
				if (!is_numeric(MODULE)) $user = $db->getIDFromUsername(MODULE);
				else $user = MODULE;
				$check = $db->checkNote(PARAMS, $user);
				if ($check) {
					$jk->note_id = (int) PARAMS;
					$jk->result_replies = $db->getRepliesNote(PARAMS);
					$jk->count_replies = (int) count($jk->result_replies);
				}
			}
			else header('HTTP/1.0 403 Not AUthorized');
		}
		else {
			if ($jk->is_viewable) {
				$jk->module_page = 'default';
				$jk->notes_result = $db->getNotes('archive', $start, $jk->notes_per_page, $userInfo['ID'], $ignored);
				$jk->notes_count = $db->countNotes('archive', $userInfo['ID']);
			}
			else header('HTTP/1.0 403 Not Authorized');
		}
		break;
	}

	$jk->load('functions');
	$jk->load('header');
	$jk->load('user');
}

$jk->load('sidebar');
$jk->load('footer');

?>