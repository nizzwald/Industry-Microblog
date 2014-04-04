<?php
// Jisko: An open-source microblogging application
// Copyright (C) 2008-10 Rubén Díaz <outime@gmail.com>
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
// along with this program. If not, see <http://www.gnu.org/licenses/>.

global $db;
global $_USER;
global $jk;

if ($other && (countChars($other) <= 20)) {
	showMobileMenu();

	if (isset($_GET['page'])) $page = $_GET['page']; else $page = 1;
	$start = getStart($page);

	$tag = utf8_htmlentities(mysql_real_escape_string($other));

	doNoteFormMobile();

	$result = $db->getNotes('tag', $start, $jk->notes_per_page, $tag);
	$count = $db->countNotes('tag', $tag);

	echo '<ul class="n"><li><strong>'.__('Tag')." #$other".'</strong></li>';
	if (count($result)) {
		foreach ($result as $row) showNoteMobile($row);
	}
	else echo '<li>'.__('No notes were found').'</li>';
	echo '</ul>';
}
else header('Location: '.coreLink('mobile'));

?>