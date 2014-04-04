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
	if (!$_GET['id'] || (empty($_GET['id']))) header('Location: '.coreLink('mobile'));
	else {
		$noteInfo = $db->getNoteCombined((int)$_GET['id']);
		if (!$noteInfo) header('Location: '.coreLink('mobile'));
		else {
			if ($noteInfo['type'] != 'public' && ($noteInfo['reply_user'] != $_USER['ID'])) header('Location: '.coreLink('mobile'));
			else {
				if ($noteInfo['type'] == 'public') $viewable = checkViewableUser($_USER['ID'], $noteInfo['user_id'], 'show_notes');
				else $viewable = true;

				if (!$viewable) header('Location: '.coreLink('mobile'));
				else {
					showMobileMenu();

					if ($noteInfo['type'] == 'public') $text = '@'.$noteInfo['username'].' ';
					else $text = '!'.$noteInfo['username'].' ';
					doNoteFormMobile((int)$_GET['id'], $text);

					echo '<ul class="n"><li><strong>'.__('In reply to:').'</strong></li>';
					showNoteMobile(array('type'=>$noteInfo['type'], 'id' => $noteInfo['ID']), false, true);
					echo '</ul>';
				}
			}
		}
	}
}

?>