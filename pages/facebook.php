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
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

global $_USER;

if (!$_USER) {
	global $db, $jk;
	import('facebook/facebook');

	$facebook = new Facebook($jk->fb_apikey, $jk->fb_secretkey);

	if (!$_GET['auth_token']) {
		$desad = $facebook->expire_session();
		$facebook->set_user(null, null);
		$facebook->clear_cookie_state();
	}

	$user_id = $facebook->require_login();

	// Greet the currently logged-in user!
	$user_id2 = md5($user_id);

	$userInfo = $db->getUserInfo(false, false, false, false, $user_id2);
	if (!$userInfo) {
		header('Location: '.coreLink(array("fbid=$user_id2"), 'register'));
	}
	else {
		if ($userInfo['status'] == 'banned') header('Location: '.coreLink(array('error=banned'), 'login'));
		elseif ($userInfo['status'] == 'ok') {
			$SID = md5($userInfo['since'].$userInfo['salt'].$userInfo['api'].time());
			$db->newSession($userInfo['ID'], $SID);
			setcookie('jisko_'.md5($jk->base), $SID, time()+(86400*60));
			header('Location: '.$jk->base);
		}
		else header('Location: '.coreLink(array('error=nc'), 'login'));
	}
}
else {
	global $db, $jk;

	if ($_GET['auth'] != md5($_USER['salt'])) header('Location: '.coreLink('settings'));
	else {
		if (isset($_GET['link'])) {
			import('facebook/facebook');

			$facebook = new Facebook($jk->fb_apikey, $jk->fb_secretkey);

			if (!$_GET['auth_token']) {
				$desad = $facebook->expire_session();
				$facebook->set_user(null, null);
				$facebook->clear_cookie_state();
			}

			$user_id = $facebook->require_login();
			if (!$db->checkFacebook(md5($user_id))) {
				$db->updateUserOptions($_USER['ID'], array('facebook' => md5($user_id)));
				header('Location: '.coreLink(array('ok='.urlencode('Your account was successfully linked with Facebook')), 'settings', 'config'));
			}
			else header('Location: '.coreLink(array('error='.urlencode('This Facebook account is already linked with another account')), 'settings', 'config'));
		}
		elseif (isset($_GET['unlink'])) {
			$db->updateUserOptions($_USER['ID'], array('facebook' => null));
			header('Location: '.coreLink(array('ok='.urlencode('Your account is no more connected with Facebook')), 'settings', 'config'));
		}
		else header('Location: '.coreLink('settings'));
	}
}

?>