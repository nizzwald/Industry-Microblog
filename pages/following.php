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

if ($_USER) {
	global $sidebar;
	$sidebar = 'my_profile';

	$jk->title = __('Following');

	$jk->load('functions');
	$jk->load('header');

	if (isset($_GET['page'])) {
		$start = getStart($_GET['page']);
		$jk->current_page = (int) $_GET['page'];
	}
	else {
		$start = getStart(1);
		$jk->current_page = 1;
	}

	$jk->users_result = $db->getFollowing($_USER['ID'], $start, $jk->notes_per_page);
	$jk->users_count = $db->countFollowing($_USER['ID']);

	$jk->load('following');
	$jk->load('sidebar');
	$jk->load('footer');
}
else header('Location: '.$jk->base);

?>