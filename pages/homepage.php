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

define('HOME', true);

if ($_USER) header('Location: '.coreLink('notes'));
else {
	if ($jk->home_page !== false) {
		$page = PATH.'pages/'.$jk->home_page.'.php';
		if (file_exists($page)) require $page;
		else require 'pages/public_notes.php';
	}
	else require 'pages/public_notes.php';

}

?>