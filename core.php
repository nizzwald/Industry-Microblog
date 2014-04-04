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

define('JISKO_VERSION', '3.0RC1');
define('INTER_VERSION', '3.02');

global $gettext_tables;

//Avoiding to show notices in the page.
error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(E_ALL ^ E_STRICT);

define('PATH', dirname(__FILE__) . '/');

if (file_exists(PATH.'install.php')) {
	header('Location: install.php');
	die();
}
else {
	require PATH.'includes/jisko.php';
	require PATH.'includes/functions.php';
	$jk = new Jisko;
	
	if (!file_exists(PATH.'config.php')) {
		global $maintenance;
		$maintenance = __('The config.php file does not exist. Please create it and retry');
		require 'pages/maintenance.php';
		die();
	}
	else {
		require PATH.'config.php';
		require PATH.'includes/db.php';
		
		global $globals;
		$globals['allowed_extensions'] = array('jpg', 'jpeg', 'png', 'gif'); // avatar & backgrounds
		
		//connect to the database.
		$db = new DB(DB_HOST, DB_PORT, DB_USER, DB_PASSWORD);
		
		import('streams');
		import('gettext');
		
		if ($db->connected === false) {
			if (file_exists(PATH.'includes/languages/'.$jk->default_lang.'/LC_MESSAGES/messages.mo')) {
				$gettext_tables = new gettext_reader(
					new CachedFileReader(PATH.'includes/languages/'.$jk->default_lang.'/LC_MESSAGES/messages.mo')
				);
				$gettext_tables->load_tables();
			}
			global $maintenance;
			$maintenance = __('There was an error while trying to contact the database');
			require PATH.'pages/maintenance.php';
			die();
		}
		else {
			if ($db->select(DB_NAME) === false) {
				if (file_exists(PATH.'includes/languages/'.$jk->default_lang.'/LC_MESSAGES/messages.mo')) {
					$gettext_tables = new gettext_reader(
						new CachedFileReader(PATH.'includes/languages/'.$jk->default_lang.'/LC_MESSAGES/messages.mo')
					);
					$gettext_tables->load_tables();
				}
				global $maintenance;
				$maintenance = __('There was an error while trying to select the database');
				require PATH.'pages/maintenance.php';
				die();
			}
			else {
				$jk->loadConfig();
				if (isset($_SERVER['HTTPS'])) $jk->base = 'https://'.$jk->base.'/';
				else $jk->base = 'http://'.$jk->base.'/';

				import('router');
				import('forms');
				import('mails');
				import('themes');
				import('openid/class.dopeopenid');
		
				$mailing = new mailing();
		
				checkUser();
		
				$jk->selectSelfUser();
		
				global $_USER;
				if ($_USER) {
					$db->updateUserOptions($_USER['ID'], array('last_seen' => time()));
				}
			}
		}
	}
}

?>