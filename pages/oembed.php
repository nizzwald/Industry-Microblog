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

if ($_GET['url']) {
	global $db, $jk;
	
	if ($jk->cleanUrls == true) {
		if ($jk->shared_host == true) {
			if (!$_SERVER['PATH_INFO']) $route = $_SERVER['ORIG_PATH_INFO'];
			else if (!$_SERVER['ORIG_PATH_INFO']) $route = $_SERVER['PHP_SELF'];
				else $route = $_SERVER['PATH_INFO'];
		}
		else $route = $_SERVER['PATH_INFO'];
		
		$route = preg_replace('|^\/|', '', $route);
		$route = preg_replace('|\/$|', '', $route);
		if (empty($route)) $route = 'home';
	}
	
	if ($jk->cleanUrls == true) @list($MODULE, $PARAMS, $EXTRA, $EXTRA2) = explode('/', $route, 4);
	else {
		$MODULE = $_GET['module'];
		$PARAMS = $_GET['params'];
		$EXTRA = $_GET['extra'];
		$EXTRA2 = $_GET['extra2'];
		if (!$MODULE) $MODULE = 'home';
	}

	if (!is_numeric($MODULE)) $user = $db->getIDFromUsername($MODULE);
	else $user = $MODULE;

	$check = $db->checkNote($PARAMS, $user);

	if ($check) {
		if ($_GET['format'] == 'xml') {
			$XMLWriter = new XMLWriter();
			$XMLWriter->openURI('php://output');
			$XMLWriter->startDocument('1.0', 'UTF-8');
			$XMLWriter->setIndent(true);

			$XMLWriter->startElement('oembed');
			$XMLWriter->writeElement('type', 'link');
			$XMLWriter->writeElement('version', '1.0');
			$XMLWriter->writeElement('title', "Note #$PARAMS");
			$XMLWriter->writeElement('url', $_GET['url']);
			$XMLWriter->writeElement('author_name', $db->getUsernameFromID($PARAMS));
			$XMLWriter->writeElement('author_url', coreLink($user));
			$XMLWriter->writeElement('provider', $jk->name);
			$XMLWriter->writeElement('provider_url', $jk->base);
			$XMLWriter->endElement();

			header('Content-Type: application/xml; charset=utf-8');

			$XMLWriter->flush();
		}
		elseif ($_GET['format'] == 'json') {
			$result = array(
				'version' => '1.0',
				'type' => 'link',
				'title' => "Note #$PARAMS",
				'url' => $_GET['url'],
				'author_name' => $db->getUsernameFromID($PARAMS),
				'author_url' => coreLink($user),
				'provider' => $jk->name,
				'provider_url' => $jk->base
			);

			header('Content-Type: text/javascript; charset=utf-8');

			echo json_encode($result);
		}
		else {
			header('HTTP/1.0 501 Not Implemented');
			die();
		}
	}
	else {
		header('HTTP/1.0 404 Not Found');
		die();
	}
}
else {
	header('HTTP/1.0 404 Not Found');
	die();
}

?>