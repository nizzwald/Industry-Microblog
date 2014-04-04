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

$tag = EXTRA;

if (!$tag || strlen($tag) > 20) {
	header('Location: '.coreLink('mobile'));
}
else {
	$result = $db->getNotes('tag', getStart(1), $jk->notes_per_page, $tag);
	$count = $db->countNotes('tag', $tag);
	
	echo '<div class="sn"><strong>'.__('Tag').' #'.$tag.'</strong></div>';
	echo '<table class="n" id="n">';
	
	if (count($result)) {
		foreach ($result as $row) showNoteMobileTouch($row);
	}
	else echo '<tr><td style="background:#fff">'.__('No notes were found').'</td></tr>';
	
	echo '</table>';
	
/*
	if ($count > $jk->notes_per_page) {
		echo getPaginationStringMobileTouch($extra);
	}
*/

}

?>