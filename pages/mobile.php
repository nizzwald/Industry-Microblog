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
global $_USER;

define('NO_GUI', 1);

import('mobile');

$module = PARAMS;
$other = EXTRA;

if (empty($module)) $module = 'home';

$touch_ui = array('iPhone', 'iPod', 'Android');

if (preg_match('/('.implode('|', $touch_ui).')/', $_SERVER['HTTP_USER_AGENT'])) {
	switch ($module) {
	case 'login':
		if (!$_USER) $file = 'mobile/touch/login.php';
		else header('Location: '.coreLink('mobile', 'notes'));
		break;
	case 'notes':
	case 'home':
		$file = 'mobile/touch/notes.php';
		break;
	case 'tag':
		$file = 'mobile/touch/tag.php';
		break;
	case 'status':
		$file = 'mobile/touch/statuses.php';
		break;
	case 'user':
		if (EXTRA) {
			$userInfo = $db->getUserInfo(false, EXTRA);
			if (!$userInfo) header('Location: '.coreLink('mobile', 'notes'));
			else {
				if ($_USER) {
					if ($_USER['username'] == $userInfo['username']) header('Location: '.coreLink('mobile', 'notes'));
					else $file = 'mobile/touch/user.php';
				}
				else $file = 'mobile/touch/user.php';
			}
		}
		else header('Location: '.coreLink('mobile', 'notes'));
		break;
	case 'report':
		if (EXTRA) {
			$userInfo = $db->getUserInfo(false, EXTRA);
			if (!$userInfo) header('Location: '.coreLink('mobile', 'notes'));
			else {
				if ($_USER) {
					if ($_USER['username'] == $userInfo['username']) header('Location: '.coreLink('mobile', 'notes'));
					else $file = 'mobile/touch/report.php';
				}
				else $file = 'mobile/touch/report.php';
			}
		}
		else header('Location: '.coreLink('mobile', 'notes'));
		break;
	case 'reportok':
		if (EXTRA) {
			$userInfo = $db->getUserInfo(false, EXTRA);
			if (!$userInfo) header('Location: '.coreLink('mobile', 'notes'));
			else {
				if ($_USER) {
					if ($_USER['username'] == $userInfo['username']) header('Location: '.coreLink('mobile', 'notes'));
					else {
						global $mailing;
						$mailing->reportAbuse(EXTRA);
						header('Location: '.coreLink('mobile', 'notes'));
					}
				}
				else header('Location: '.coreLink('mobile', 'notes'));
			}
		}
		else header('Location: '.coreLink('mobile', 'notes'));
		break;
	default:
		$userInfo = $db->getUserInfo(false, PARAMS);
		$file = 'mobile/touch/user.php';
		break;
	}
	if ($file) {
		require 'mobile/touch/template/header.php';
		require $file;
		require 'mobile/touch/template/footer.php';
	}
	else header('Location: '.coreLink('mobile'));	
}
else {
	$pages = array('drop', 'login', 'reply', 'status', 'public', 'tag', 'report', 'favorite', 'follow', 'notes', 'home');

	switch ($module) {
	case 'notes':
	case 'home':
		if ($_USER) $file = 'mobile/notes.php';
		else $file = 'mobile/public.php';
		break;
	default:
		if (in_array($module, $pages)) {
			$file = PATH.'pages/mobile/'.$module.'.php';
			if (!file_exists($file) || (!is_readable($file))) $no_exist = '';
		}
		else $no_exist = '';
	}

	if (is_null($no_exist)) {
		require 'mobile/template/header.php';
		require $file;
		require 'mobile/template/footer.php';
	}
	else {
		require 'mobile/template/header.php';
		$username = trim($module);
		require 'mobile/user.php';
		require 'mobile/template/footer.php';
	}
}

?>
