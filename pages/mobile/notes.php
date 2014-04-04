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

if (!$_USER) header('Location: '.coreLink('mobile'));
else {
	$extra = EXTRA;

	showMobileMenu();

	if (isset($_GET['page'])) $page = $_GET['page']; else $page = 1;
	$start = getStart($page);

	switch ($extra) {
	case 'private':
		$update = $db->updateUnreadPrivates($_USER['ID']);
		$name = __('Received messages');
	case 'private_sent':
		if (!$name) $name = __('Sent messages');
	case 'twitter':
		if (!$name) {
			$name = __('Twitter');
			updateTwitterNotes();
		}
		$result = $db->getNotes($extra, $start, $jk->notes_per_page, $_USER['ID']);
		$count = $db->countNotes($extra, $_USER['ID']);
		break;
	case 'replies':
		$db->updateUnreadReplies($_USER['ID']);
		$name = '@'.$_USER['username'];
	case 'archive':
		if (!$name) $name = __('Archive');
	case 'all':
		if (!$name) $name = __('Friends + Twitter');
	case 'friends':
		if (!$name) $name = __('Friends');
	default:
		if (!$name) {
			$extra = 'all';
			$name = __('Friends');
		}

		$result = $db->getNotes($extra, $start, $jk->notes_per_page, $_USER['ID'], $_USER['ignored']);
		$count = $db->countNotes($extra, $_USER['ID'], $_USER['ignored']);
		if ($extra == 'all') updateTwitterNotes();
		break;
	}

	doNoteFormMobile();

	echo '<ul class="n"><li><strong>'.$name.'</strong></li>';
	if (count($result)) {
		foreach ($result as $row) showNoteMobile($row);
	}
	else echo '<li>'.__('No notes were found').'</li>';
	echo '</ul>';
	
	if ($count > $jk->notes_per_page) echo getPaginationStringMobile(array('mobile', 'notes', $extra, $params), $count, $page);
}

?>