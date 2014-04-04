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

if (($_POST) and ($_USER)) {
	$result = $db->checkInvitations($_USER['ID']);
	if ($result) {
		$email = $_POST['email'];
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$token = substr(md5(rand()), 0, 8);
			$result = newInvitation($email, $token);
			header('Location: '.$_SERVER['HTTP_REFERER']);
		} else {
			header('Location: '.$_SERVER['HTTP_REFERER']);
		}
	} else {
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}
}

?>