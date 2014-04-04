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

global $db;
global $jk;

$jk->load('functions');

if (!$PARAMS) {
	global $sidebar;
	$sidebar = 'my_profile';
	$jk->page_module = 'stats';

	$jk->title = __('Tags statistics');
	$jk->load('header');
}
else {
	if (strlen($PARAMS) > 20) {
		header('Location: '.coreLink('home'));
		die();
	}
	else {
		global $sidebar;
		$sidebar = 'tags';

		$jk->tag_name = $PARAMS;
		$jk->title = __('Tag')." #$PARAMS";
		$jk->load('header');

		if ($db->checkTag(utf8_htmlentities(mysql_real_escape_string($PARAMS)))) {
			if (isset($_GET['page'])) {
				$start = getStart($_GET['page']);
				$jk->current_page = (int) $_GET['page'];
			}
			else {
				$start = getStart(1);
				$jk->current_page = 1;
			}

			$jk->selectTag($PARAMS);
			$jk->notes_result = $db->getNotes('tag', $start, $jk->notes_per_page, $PARAMS);
			$jk->notes_count = $db->countNotes('tag', $PARAMS);
		}
		else {
			header('Location: '.coreLink('home'));
			die();
		}
	}
}



$jk->load('tag');

$jk->load('sidebar');
$jk->load('footer');

?>