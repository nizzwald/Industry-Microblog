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

if (!is_numeric($other)) header('Location: '.coreLink('mobile'));
else {
	showMobileMenu();

	$noteInfo = $db->getNoteCombined((int)$other);
	if ($noteInfo) {
		echo '<ul class="n"><li><strong>'.sprintf(__('Note %s'), '#'.(int)$other).'</strong></li>';
		if ($noteInfo['user_id'] != $_USER['ID']) showNoteMobile(array('type' => $noteInfo['type'], 'id'=>$other), true);
		else showNoteMobile(array('type' => $noteInfo['type'], 'id'=>$other), true, true);
		echo '</ul>';
		echo '<br /><div style="width:200px;margin:auto;" id="n_a">';
		if ($noteInfo['user_id'] != $_USER['ID']) echo '<a href="'.coreLink(array('type=note', 'id='.$other), 'mobile', 'report').'" style="background-color:#660000;margin-right:5px;">'.__('Report note').'</a>';
		else echo '<a href="'.coreLink(array('mobile'), 'notes', 'delete', $other).'" style="background-color:#660000">'.__('Delete note').'</a>';
		echo '<a href="'.coreLink(array('id='.$other), 'favorite').'">';
		if (!$db->checkFavorite($_USER['ID'], (int)$other)) echo __('Favorite');
		else echo __('Remove favorite');
		echo '</a>';
		echo '<br/><br />';

		echo '</div>';
		echo '<br /><br />';
	}
	else {
		echo '<br /><span>'.__('No notes were found').'</span><br /><br />';
	}
}

?>
