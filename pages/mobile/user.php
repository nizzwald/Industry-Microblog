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

global $_USER;
global $db, $jk;

if (isset($_GET['page'])) $page = $_GET['page']; else $page = 1;
$start = getStart($page);
if (is_numeric($username)) $userInfo = $db->getUserInfo($username);
else $userInfo = $db->getUserInfo(false, $username);

if (!$userInfo) {
	echo '<strong>';
	echo __('Not found');
	echo '</strong>';
	require 'template/footer.php';
	die;
}

if ($_USER && $_USER['username'] == $userInfo['username'] && !is_numeric($other)) {
	header('Location: '.coreLink('mobile', 'notes'));
	die;
}

$viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_notes');

showMobileMenu();

$result = $db->getNotes('archive', $start, $jk->notes_per_page, $userInfo['ID']);
$count = $db->countNotes('archive', $userInfo['ID']);
$tmp_string = __('%username\'s profile');
$string = str_replace('%username', $userInfo['username'], $tmp_string);
echo '<ul class="n">
<li><strong>'.$string.'</strong></li></ul>';

$viewable_uinfo = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_profile_info');

if ($viewable_uinfo) {
	if ($userInfo['realname']) $desc = '<strong>Name</strong>: '.$userInfo['realname'].' ';
	if ($userInfo['profile']['location']) $desc .= ' | <strong>Location</strong>: '.$userInfo['profile']['location'];
	if ($userInfo['profile']['web']) $desc .= ' | <strong>Webpage</strong>: '.$userInfo['profile']['web'];
	if ($userInfo['profile']['bio']) $desc .= ' | <strong>Bio</strong>: '.$userInfo['profile']['bio'];

	echo '<div style="display:block;float:left;padding-left:5px;"><div style="text-align:left;float:right;display:block;padding-left:10px;">'.$desc.'</div><img src="'.getAvatar($userInfo['ID'], 24).'"></div> ';
	echo '<br />';
}

if ($_USER) echo '<div style="width:98%;float:left;padding-left:5px;"><a href="'.coreLink(array('type=user', 'uid='.$userInfo['ID']), 'mobile', 'report').'" style="float:right">'.__('Report').'</a> <form style="float:left" method="post" action="'.coreLink('follow').'"><input type="hidden" name="id" value="'.$userInfo['ID'].'"><input type="submit" style="float:left" value="'.($db->checkFollowing($_USER['ID'], $userInfo['ID']) ? __('Unfollow') : __('Follow')).'" class="b"></form><form method="post" style="float:left" action="'.coreLink('ignore').'"><input type="hidden" name="id" value="'.$userInfo['ID'].'"><input type="submit" value="'.(in_array($userInfo['ID'], $_USER['ignored']) ? __('Stop ignoring') : __('Ignore')).'" class="b" style="float:left"></form></div><br />';

if (!$viewable) {
	echo '<br /><div style="padding-left:5px;"><strong>';
	echo __('Not allowed');
	echo '</strong>';
	echo '<br />';
	echo __("You aren't allowed to see the notes of this user");
	echo '<br /><br /></div>';
	require 'template/footer.php';
	die;
}
else {
	echo '<ul class="n">';
	if (count($result)) {
		foreach ($result as $row) showNoteMobile($row);
	}
	else echo '<li>' . __('No notes were found') . '</li>';
	echo '</ul>';
}

?>
