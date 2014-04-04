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
global $db;
global $jk;

$extra = EXTRA;
if (strlen($extra) > 0 && ($extra != 'public' && (!$_USER))) header('Location: '.coreLink('mobile'));
else {
	if (!$extra) {
		if (!$_USER) $extra = 'public';
		else $extra = '';
	}
	
	$page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
	$start = getStart($page);
	$ignored = ($_USER['ignored'] ? $_USER['ignored'] : false);

	
	switch ($extra) {
	case 'private':
		$update = $db->updateUnreadPrivates($_USER['ID']);
		$name = __('Private notes (Received)');
	case 'private_sent':
		echo '<div class="p">';
		echo '<a href="'.coreLink('mobile', 'notes', 'private').'">'.__('Received').'</a> - <a href="'.coreLink('mobile', 'notes', 'private_sent').'">'.__('Sent').'</a>';
		echo '</div>';
		if (!$name) $name = __('Private notes (Sent)');
	case 'twitter':
		if (!$name) {
			$name = __('Twitter');
			updateTwitterNotes();
		}
			
		$result = $db->getNotes($extra, $start, $jk->notes_per_page, $_USER['ID']);
		$count = $db->countNotes($extra, $_USER['ID']);
		break;
	case 'replies':
		$name = '@'.$_USER['username'];
		$result = $db->getNotes('mentions', $start, $jk->notes_per_page, $_USER['ID'], $ignored);
		$count = $db->countNotes('mentions', $_USER['ID'], $ignored);
		break;
	case 'all':
		$name = __('Friends + Twitter');
		updateTwitterNotes();
	case 'friends':
		if (!$name) $name = __('Friends');
		$result = $db->getNotes('all', $start, $jk->notes_per_page, $_USER['ID'], $ignored);
		$count = $db->countNotes('all', $_USER['ID'], $ignored);
		
		break;
	case 'public':
		$name = __('Public notes');
		$result = $db->getNotes('public', $start, $jk->notes_per_page, false, $ignored);
		$count = $db->countNotes('public', '', $ignored);
		updateTwitterNotes();
		break;
	default:
		if (has_twitter()) {
			if ($_USER['twitter']['combined_view'] == 0) {
				$name = __('Friends');
				$extra = 'friends';
			}
			else {
				$name = __('Friends + Twitter');
				$extra = 'all';
			}
		}
		else {
			$name = __('Friends');
			$extra = 'friends';
		}
		$result = $db->getNotes($extra, $start, $jk->notes_per_page, $_USER['ID'], $ignored);
		$count = $db->countNotes($extra, $_USER['ID'], $ignored);
		break;
	}
	echo '<div class="sn"><strong>'.$name.'</strong></div>';
	echo '<table class="n" id="n">';
	
	if (count($result)) {
		foreach ($result as $row) showNoteMobileTouch($row);
	}
	else echo '<tr><td style="background:#fff">'.__('No notes were found').'</td></tr>';
	
	echo '</table>';
	
	if ($count > $jk->notes_per_page) {
		echo getPaginationStringMobileTouch($extra);
	}
}

?>