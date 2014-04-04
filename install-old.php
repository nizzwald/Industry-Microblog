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

define('PATH', dirname(__FILE__) . '/');
define('JISKO_VERSION', '3.0');

require PATH.'includes/gettext.php';
require PATH.'includes/streams.php';

//Avoiding to show notices in the page.
error_reporting(E_ALL ^ E_NOTICE);

global $gettext_tables;

$langExplode = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
$langExplode = explode(',', $langExplode[0]);

$file = PATH.'includes/languages/'.$langExplode[1].'/LC_MESSAGES/messages.mo';

if (!file_exists($file)) {
	$file = PATH.'includes/languages/'.$langExplode[1].'/LC_MESSAGES/messages.mo';
	if (file_exists($file)) {
		$gettext_tables = new gettext_reader(
			new CachedFileReader($file)
		);
		$gettext_tables->load_tables();
	}
}
else {
	$gettext_tables = new gettext_reader(
		new CachedFileReader($file)
	);
	$gettext_tables->load_tables();	
}

function __($string)
{
	global $gettext_tables;
	if (!$gettext_tables) return $string;
	else return $gettext_tables->translate($string);
}

function validUsername($username, $str = false)
{
	global $jk;
	global $db;

	$username = trim($username);

	$forbidden = array('home', 'login', 'register', 'logout', 'notes', 'drop', 'forgot', 'avatar', 'invite', 'preferences', 'follow', 'favorites', 'public', 'profile', 'rss', 'followers', 'following', 'search', 'cron', 'download', 'post', 'ajax', 'mobile', 'report', 'group', 'groups', 'direct_messages', 'account', 'trouble_login', 'resend_mail', 'tos', 'faq', 'admin');

	if (in_array($username, $forbidden)) return 'busy';
	elseif ((strlen($username) > 20) || (!preg_match('/^[a-z_\-0-9]{3,15}$/i', $username))) return 'invalid';
	else return 'valid';
}

function checkSession()
{
	if (isset($_SESSION['host']) && isset($_SESSION['port']) && isset($_SESSION['username']) && isset($_SESSION['password']) && $_SESSION['name'] && isset($_SESSION['type'])) return true;
	else return false;
}

function do_header($title)
{
	echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head>
<title>'.__('Installing Jisko').'</title><meta http-equiv="content-type" content="text/html; charset=utf-8" />
<style type="text/css">
.ok {background-color:green;color:white;padding:5px;margin:10px;}
.error {background-color:red;color:white;padding:5px;text-align:center;margin:10px;}
BODY{margin:auto;margin-top:50px;width:700px;font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 17px;}#contenedor .content{background:url(../themes/transparency/img/web_bg.png);border:1px solid #ddd;border-radius:4px;-moz-border-radius:4px;color:black;}#contenedor .content{padding:5px;padding-right:30px;}.footer{padding-top:5px;font-size:11px;color:#B9B7C0;font-family:"Lucida Grande",Arial,serif}.footer a{color:#B9B7D0}H3{font-style:italic;font-size:20px}.input{width:300px;}input[type=text],input[type=password]{height:31px;font-size:17px;padding-left:7px}li{margin-bottom:1.3em}.inputs{width:550px;list-style-type:none}.inputs small{font-size:.7em;color:#aaa}#menu ul{list-style-type:none;padding-right:50px;padding-top:10px;padding-bottom:10px}#menu ul li{display:inline;background-color:#08004D;padding:10px 12px}#menu ul li a{color:#ddd;text-decoration:none;color:white;font-size:16px}#menu ul{padding-top:2px;padding-bottom:5px;border-bottom: 5px solid #08004D;}.title{font-family:Georgia,Tahoma;color:#08004D;font-style:italic;font-weight:bold;padding-top:20px;padding-left:30px;font-size:20px}input[type=submit]{margin-left:40px}#shorters ul{list-style-type:none;}#shorters input{margin-top:1px}
</style><link rel="shortcut icon" href="favicon.ico" type="image/png" />
</head><body>
<div id="contenedor">
<img src="static/img/logos/jisko.png" style="border:0px" alt="Jisko" /></a><br /><br />
<div class="title"><h3>'.$title.'</h3></div><div class="content">';
}

function do_footer()
{
	echo '</div>
<div class="footer">
<div style="float:right">
Powered by <a href="http://www.jisko.org">Jisko</a> '.JISKO_VERSION.'
</div>'.sprintf(__('If you are having problems with Jisko contact us at %s'), '<a href="http://answers.launchpad.net/jisko">http://answers.launchpad.net/jisko</a>').'</div></body></html>';
}

session_start();

$step = (isset($_GET['step']) ? (int)$_GET['step'] : 0);

switch ($step) {
case 4:
	if (checkSession()) {
		do_header(__('Thank you for using Jisko'));
		
		if ($_SESSION['type'] == 'new') {
			$fd = fopen('config.php', 'w+');
			if ($fd) {
				fwrite($fd, "<?php\n\n//Created by Jisko ".JISKO_VERSION." auto-installer\ndefine('DB_HOST', '".$_SESSION['host']."');\ndefine('DB_PORT', '".$_SESSION['port']."');\ndefine('DB_USER', '".$_SESSION['username']."');\ndefine('DB_PASSWORD', '".$_SESSION['password']."');\ndefine('DB_NAME', '".$_SESSION['name']."');\n\n".'$globals[\'menubar_links\'] = array();'."\n?>");
				echo '<div class="ok">'.__('The file config.php was created without any problems').'</div>';
			}
			else echo '<div class="error">'.__('There was a problem while creating the config.php file, please rename config.sample.php to config.php, and then edit it with your database credentials').'</div>';

			echo '<div style="padding:10px">'.__('Congratulations! You have now installed Jisko in your host.').'<br /><br />'.__('Now please remove the install.php file in order to test your Jisko installation').'<br /><br />'.sprintf(__('Remember to visit %s to see the latest updates'), '<a href="http://jisko.org">http://jisko.org</a>').'</div>';
		}
		else {
			echo '<div style="padding:10px">'.sprintf(__('Congratulations! You have now updated to Jisko %s.'), JISKO_VERSION).'<br /><br />'.__('Now please remove the install.php file in order to test your new version of Jisko').'<br /><br />'.sprintf(__('Remember to visit %s to see the latest updates'), '<a href="http://jisko.org">http://jisko.org</a>').'</div>';
		}
		do_footer();
	}
	break;
case 3:
	if ($_POST) {
		if (checkSession()) {
			$mysqlfd = mysql_connect($_SESSION['host'].':'.$_SESSION['port'], $_SESSION['username'], $_SESSION['password']);
			if ($mysqlfd) {
				$db = mysql_select_db($_SESSION['name']);
				if ($db) {
					if (empty($_POST['username']) && empty($_POST['password']) && empty($_POST['email'])) header('Location: install.php?step=3&error=empty');
					else {
						$check = validUsername($_POST['username']);
						if ($check != 'valid') {
							if ($check == 'busy') header('Location: install.php?step=3&error=forbid');
							elseif ($check == 'invalid') header('Location: install.php?step=3&error=user');
						}
						else {
							$query = mysql_query('SELECT `ID` FROM `users` WHERE `username`=\''.mysql_real_escape_string($_POST['username']).'\'');
							if (mysql_num_rows($query)) {
								$id = mysql_insert_id();
								$query = mysql_query('INSERT INTO `permissions` SET `userid`=\''.$id.'\', `can_panel`=\'1\'');
								if ($query) header('Location: install.php?step=4');
								else header('Location: install.php?step=3&error=query');
							}
							else {
								if (!mkdir(PATH."users_files/".$_POST['username'], 0777) || (!mkdir(PATH."users_files/".$_POST['username']."/img", 0777) || (!mkdir(PATH."users_files/".$_POST['username']."/img/avatar", 0777) || (!mkdir(PATH."users_files/".$_POST['username']."/img/background", 0777) || (!mkdir(PATH."users_files/".$_POST['username']."/files", 0777)))))) header('Location: install.php?step=3&error=dir');
								else {
									$salt = substr(md5(rand()), 0, 5);
									$query = mysql_query("INSERT INTO `users` (`username`, `password`, `api`, `salt`, `language`, `theme`, `email`, `status`, `since`, `last_seen`, `ip`, `notification_level`) values ('".$_POST['username']."', '".md5(md5($_POST['password']).md5($salt))."', '".substr(md5($_POST['username'].rand()), 0, 16)."', '".$salt."', 'def', 'transparency', '".$_POST['email']."', 'ok', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '".$_SERVER['REMOTE_ADDR']."', 4)");
									$id = mysql_insert_id();
									$query = mysql_query('INSERT INTO `permissions` SET `userid`=\''.$id.'\', `can_panel`=\'1\'');
									if ($query) header('Location: install.php?step=4');
									else header('Location: install.php?step=3&error=query');
								}
							}
						}
					}
				}
				else header('Location: install.php?step=3&error=db');
			}
			else header('Location: install.php?step=3&error=mysql');
		}
		else header('Location: install.php?step=1');
	}
	else {
		if (checkSession()) {
			if ($_SESSION['type'] == 'new') do_header(__('Installing Jisko (step 3/3)'));
			else do_header(__('Updating Jisko (step 3/3)'));

			$error = (isset($_GET['error']) ? $_GET['error'] : '');

			switch ($error) {
			case 'mysql':
				$error = "Couldn't connect to the mysql database";
				break;
			case 'db':
				$error = "Couldn't select the database";
				break;
			case 'query':
				$error = 'There was a problem while trying to execute a query';
				break;
			case 'forbid':
				$error = "The username you entered cannot be used";
				break;
			case 'user':
				$error = 'Username not valid. It has to be less than 20 characters';
				break;
			case 'empty':
				$error = 'There are empty fields. Fill them and try again';
				break;
			case 'dir':
				$error = "Couldn't create the upload folders for the admin user";
			}

			if ($error) echo '<div class="error">'.__($error).'</div>';

			echo '
	<form action="?step=3" method="post">
	<ul class="inputs" style="width:600px">
		<li>
			<div style="float:right"><input type="text" class="input" name="username" value="oper"></div>
			'.__('Username').'<br /><small>'.__('Username of the admin user. Less than 20 characters').'</small>
		</li>
		<li>
			<div style="float:right"><input type="password" class="input" name="password"></div>
			'.__('Password').'<br /><small>'.__('Password of the admin user').'</small>
		</li>
		<li>
			<div style="float:right"><input type="text" class="input" name="email"></div>
			'.__('Email').'<br /><small>'.__('Email of the admin user').'</small>
		</li>
	</ul>
	<p><input name="submit" type="submit" value="'.__('Continue').'" class="submit" /></p><br />
	</form>
				';

			do_footer();
		}
		else header('Location: install.php?step=1');
	}
	break;
case 2:
	if ($_POST) {
		if (checkSession()) {
			$mysqlfd = mysql_connect($_SESSION['host'].':'.$_SESSION['port'], $_SESSION['username'], $_SESSION['password']);
			if ($mysqlfd) {
				$db = mysql_select_db($_SESSION['name']);
				if ($db) {
					if (get_magic_quotes_gpc()) {
						$_POST['base_url'] = stripslashes($_POST['base_url']);
						$_POST['name'] = stripslashes($_POST['name']);
						$_POST['admin_mail'] = stripslashes($_POST['admin_mail']);
						$_POST['abuse_mail'] = $_POST['admin_mail'];
					}

					$queries = array(
						'base_url' => mysql_real_escape_string($_POST['base_url']),
						'name' => mysql_real_escape_string($_POST['name']),
						'admin_mail' => mysql_real_escape_string($_POST['admin_mail']),
						'abuse_mail' => mysql_real_escape_string($_POST['admin_mail'])
					);

					foreach ($queries as $cat=>$val) {
						$query = mysql_query('UPDATE `settings` SET `value`=\''.$val.'\' WHERE `category`=\''.$cat.'\'');
						if (!$query) header('Location: install.php?step=2&error=query');
					}

					header('Location: install.php?step=3');
				}
				else header('Location: install.php?step=2&error=db');
			}
			else header('Location: install.php?step=2&error=mysql');
		}
		else header('Location: install.php?step=1');
	}
	else {
		if (checkSession()) {
			if ($_SESSION['type'] == 'new') do_header(__('Installing Jisko (step 2/3)'));
			else do_header(__('Updating Jisko (step 2/3)'));

			$error = (isset($_GET['error']) ? $_GET['error'] : '');

			switch ($error) {
			case 'mysql':
				$error = "Couldn't connect to the mysql database";
				break;
			case 'db':
				$error = "Couldn't select the database";
				break;
			case 'query':
				$error = 'There was a problem while trying to execute a query';
			}

			if ($error) echo '<div class="error">'.__($error).'</div>';

			echo '
<form action="?step=2" method="post">
<ul class="inputs" style="width:600px">
	<li>
		<div style="float:right"><input type="text" class="input" name="base_url" value=""></div>
		'.__('Base URL').'<br /><small>'.__('The URL where Jisko is located. Without http://').'</small>
	</li>
	<li>
		<div style="float:right"><input type="text" class="input" name="name" value=""></div>
		'.__('Name').'<br /><small>'.__('Name of your Jisko installation').'</small>
	</li>
	<li>
		<div style="float:right"><input type="text" class="input" name="admin_mail" value=""></div>
		'.__('Admin mail').'<br /><small>'.__('Used for the contact page...').'</small>
	</li>
</ul>
<p><input name="submit" type="submit" value="'.__('Continue').'" class="submit" /></p><br />
</form>
			';

			do_footer();
		}
		else header('Location: install.php?step=1');
	}
	break;
case 0:
	if ($_POST) {
		$val = array('new', '2.0', '3.0beta1', '3.0beta2');
		if (in_array($_POST['opt'], $val)) {
			$_SESSION['type'] = $_POST['opt'];
			header('Location: install.php?step=1');
		}
		else header('Location: install.php?step=0');
	}
	else {
		do_header('Updating/Installing Jisko');

		echo '
			<p style="padding:0px 40px">'.__('If you have a previous version of Jisko installed on your server, then you may upgrade your existing installation. Otherwise you can do a clean installation.').'</p>
<form action="?step=0" method="post">
<ul class="inputs" style="width:600px">
	<li>
		<input type="radio" name="opt" value="new"/> '.__('Install Jisko').'<br /><small>'.__('It will do a new installation of Jisko in your server').'</small>
	</li>
	<li>
		<input type="radio" name="opt" value="2.0"/> '.__('Upgrade from Jisko 2.0').'<br /><small>'.__('It will update your database to the new version').'</small>
	</li>
	<li>
		<input type="radio" name="opt" value="3.0beta1"/> '.__('Upgrade from Jisko 3.0beta1').'<br /><small>'.__('It will update your database to the new version').'</small>
	</li>
	<li>
		<input type="radio" name="opt" value="3.0beta2"/> '.__('Upgrade from Jisko 3.0beta2').'<br /><small>'.__('It will update your database to the new version').'</small>
	</li>
</ul>
<p><input name="submit" type="submit" value="'.__('Continue').'" class="submit" /></p><br />
</form>
			';

		do_footer();
	}
	break;
case 1:
	if ($_POST) {
		$mysqlfd = mysql_connect($_POST['host'].':'.(int)$_POST['port'], $_POST['username'], $_POST['password']);
		if ($mysqlfd) {
			$_SESSION['host'] = $_POST['host'];
			$_SESSION['port'] = (int) $_POST['port'];
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['password'] = $_POST['password'];
			$_SESSION['name'] = $_POST['name'];
			mysql_close($mysqlfd);

			if ($_SESSION['type'] == '3.0beta2') {
				require dirname(__FILE__).'/sql/upgrade3.0beta2.php';
				$upd = new Upgrade30beta2($_POST['host'], $_POST['port'], $_POST['username'], $_POST['password'], $_POST['name']);
				if ($upd == 'mysql') header('Location: install.php?step=1&error=mysql');
				else {
					$status = $upd->upgrade();
					if ($status == 'query') header('Location: install.php?step=1&error=query');
					else header('Location: install.php?step=4');
				}
			}
			if ($_SESSION['type'] == '3.0beta1') {
				require dirname(__FILE__).'/sql/upgrade3.0beta1.php';
				$upd = new Upgrade30beta1($_POST['host'], $_POST['port'], $_POST['username'], $_POST['password'], $_POST['name']);
				if ($upd == 'mysql') header('Location: install.php?step=1&error=mysql');
				else {
					$status = $upd->upgrade();
					if ($status == 'query') header('Location: install.php?step=1&error=query');
					else header('Location: install.php?step=4');
				}
			}
			elseif ($_SESSION['type'] == '2.0') {
				require dirname(__FILE__).'/sql/upgrade2.0.php';
				$upd = new Upgrade20($_POST['host'], $_POST['port'], $_POST['username'], $_POST['password'], $_POST['name']);
				if ($upd == 'mysql') header('Location: install.php?step=1&error=mysql');
				else {
					$status = $upd->upgrade();
					if ($status == 'query') header('Location: install.php?step=1&error=query');
					else header('Location: install.php?step=3');
				}
			}
			elseif ($_SESSION['type'] == 'new') {
				require dirname(__FILE__).'/sql/install.php';
				$upd = new Install($_POST['host'], $_POST['port'], $_POST['username'], $_POST['password'], $_POST['name']);
				if ($upd == 'mysql') header('Location: install.php?step=1&error=mysql');
				else {
					$status = $upd->upgrade();
					if ($status == 'query') header('Location: install.php?step=1&error=query');
					else header('Location: install.php?step=2');
				}
			}
		}
		else header('Location: install.php?step=1&error=mysql');
	}
	else {
		if ($_SESSION['type'] == 'new') do_header(__('Installing Jisko (step 1/3)'));
		else do_header(__('Updating Jisko (step 1/3)'));

		$error = (isset($_GET['error']) ? $_GET['error'] : '');

		switch ($error) {
		case 'mysql':
			$error = "Couldn't connect to the mysql database";
			break;
		case 'db':
			$error = "Couldn't select the database";
			break;
		case 'query':
			$error = 'There was a problem while trying to execute a query';
		}

		if ($error) echo '<div class="error">'.__($error).'</div>';

		echo '
<form action="?step=1" method="post">
<ul class="inputs" style="width:600px">
	<li>
		<div style="float:right"><input type="text" class="input" name="host" value="localhost"></div>
		'.__('Database host').'<br /><small>'.__('The host where the mysql server is').'</small>
	</li>
	<li>
		<div style="float:right"><input type="text" class="input" name="port" value="3306"></div>
		'.__('Database port').'<br /><small>'.__('The port of the mysql server').'</small>
	</li>
	<li>
		<div style="float:right"><input type="text" class="input" name="username"></div>
		'.__('Database username').'<br /><small>'.__('The username to access the mysql server').'</small>
	</li>
	<li>
		<div style="float:right"><input type="text" class="input" name="password"></div>
		'.__('Database password').'<br /><small>'.__('The password of the username').'</small>
	</li>
	<li>
		<div style="float:right"><input type="text" class="input" name="name"></div>
		'.__('Database name').'<br /><small>'.__('The name of the database where you want to install Jisko').'</small>
	</li>
</ul>
<p><input name="submit" type="submit" value="'.__('Continue').'" class="submit" /></p><br />
</form>
			';
		do_footer();
	}
	break;
}

?>