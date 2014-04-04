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

if ($_USER) {
	$who = (int) $_POST['id'];
	if ($who) {
		$ignored = (bool) in_array($who, $_USER['ignored']);
		if ($ignored) {
			$return = array_diff($_USER['ignored'], array($who));
		}
		else {
			$db->removeRelationship($_USER['ID'], $who);
			array_push($_USER['ignored'], $who);
			$return = $_USER['ignored'];
		}
		$db->updateUserOptions($_USER['ID'], array('ignored' => serialize(array_unique($return))));
	}

	header('Location: '.$_SERVER['HTTP_REFERER']);
}
else header('Location: '.$_SERVER['HTTP_REFERER']);

?>