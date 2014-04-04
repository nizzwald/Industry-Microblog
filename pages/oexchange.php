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

$ctype = $_GET['ctype'];

//BTW we only support the link spec
if (!$ctype) $ctype = 'link';
if ($ctype != 'link') die('Not supported');
else {
	if ($_GET['url']) $url = $_GET['url'];
	elseif ($_SERVER['HTTP_REFERER']) $url = $_SERVER['HTTP_REFERER'];
	else die('No URL provided');

	if ($url) {
		global $_USER;

		$title = $_GET['title'];
		$tags = $_GET['tags'];

		$content = "$title ($url) ";
		if ($tags) {
			$tags = explode(',', $tags);
			foreach ($tags as $tag) $content .= "#$tag ";
		}

		if ($_USER) header('Location: '.coreLink(array('note='.urlencode(trim($content))), 'notes'));
		else header('Location: '.coreLink(array('error=nologged'), 'login'));
	}
}


?>