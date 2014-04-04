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

class router
{
	function router()
	{
		global $jk;
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
		$this->route_request($route);
	}

	function route_request($route)
	{
		global $jk;
		if ($jk->cleanUrls == true) @list($MODULE, $PARAMS, $EXTRA, $EXTRA2) = explode('/', $route, 4);
		else {
			$MODULE = $_GET['module'];
			$PARAMS = $_GET['params'];
			$EXTRA = $_GET['extra'];
			$EXTRA2 = $_GET['extra2'];
			if (!$MODULE) $MODULE = 'home';
		}
		
		define('MODULE', $MODULE);
		define('PARAMS', $PARAMS);
		define('EXTRA', $EXTRA);
		define('EXTRA2', $EXTRA2);

		if ($jk->maintenance && (MODULE != 'admin')) {
			global $maintenance;
			$maintenance = $jk->maintenance;
			$file = 'pages/maintenance.php';
		}
		else {
			$pages = array('home', 'register', 'login', 'logout', 'notes', 'public', 'opensearch', 'settings', 'sioc', 'foaf', 'maintenance', 'oexchange', 'ajax', 'post', 'ignore', 'tag', 'api', 'download', 'cron', 'search', 'following', 'my_followers', 'follow', 'facebook', 'contact', 'favorite', 'rss', 'invite', 'oembed', 'report', 'mobile', 'faq', 'stats', 'drop_account', 'tos', 'trouble_login', 'openid', 'admin', 'twitter');
			switch (MODULE) {
			case 'home':
				$file = 'pages/homepage.php';
				break;
			case 'public':
				$file = 'pages/notes.php';
				break;
			case 'api':
				$file = 'includes/api.php';
				break;
			case 'my_followers':
				$file = 'pages/followers.php';
				break;
			case 'openid':
				if (!PARAMS) $file = 'includes/openid/index.php';
				else {
					if (PARAMS == 'login') $file = 'includes/openid/login.php';
					elseif (PARAMS == 'logout') $file = 'includes/openid/logout.php';
				}
				break;
			default:
				if (in_array(MODULE, $pages)) {
					$file = PATH.'pages/'.MODULE.'.php';
					if (!file_exists($file) || (!is_readable($file))) $no_exist = '';
				}
				else $no_exist = '';
				break;
			}
		}

		if (is_null($no_exist)) require $file;
		else require 'pages/user.php';
	}
}

?>