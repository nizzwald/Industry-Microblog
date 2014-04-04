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
if ($_USER) header('Location: '.coreLink('notes'));

global $db;
global $mailing;
global $jk;

$jk->title = __('Having trouble while logging in?');
$jk->load('functions');
$jk->load('header');

if ($_POST) {
	if (recaptcha_enabled()) {
		import('recaptchalib');
		$human = recaptcha_check_answer($jk->recaptcha_privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		if (!$human->is_valid) echo showStatus(__("Incorrect reCAPTCHA code!"), 'error');
	}
	else {
		if (!empty($_POST['forgot'])) {
			if (!empty($_POST['group'])) {
				if ($_POST['group'] == 'email') {
					$userInfo = $db->getUserInfo(false, false, $_POST['forgot']);
					if (!$userInfo) echo showStatus(__('That email doesn\'t exist in our database'), 'error');
				}
				elseif ($_POST['group'] == 'username') {
					$userInfo = $db->getUserInfo(false, $_POST['forgot']);
					if (!$userInfo) echo showStatus(__("There's no user with that username"), 'error');
				}

				if ($userInfo) {
					$token = substr(md5(rand()), 0, 12);
					$db->deleteKeys($userInfo['ID'], 'password');
					$db->newKey($userInfo['ID'], 'password', $token);
					$mailing->forgottenPassword($userInfo['email'], $userInfo['ID'], $token);
					echo showStatus(__('We have just sent an e-mail with instructions!'), 'ok');
				}
			}
			else {
				echo showStatus(__("Please fill all the inputs"), 'error');
			}
		}
		elseif (!empty($_POST['resend'])) {
			$userInfo = $db->getUserInfo(false, $_POST['resend']);
			if (!$userInfo) echo showStatus(__("There's no user with that username"), 'error');
			else {
				if ($userInfo['status'] != 'nc') echo showStatus(__('The account was not waiting for an activation email'), 'error');
				else {
					$query = $db->send("SELECT `token` FROM `keys` WHERE `keys`.`user_id` = ".(int)$userInfo['ID']." AND `keys`.`type` = 'activation' LIMIT 1");
					if (!mysql_num_rows($query)) {
						$key = $db->newKey($userInfo['ID'], 'activation', substr(md5(rand()), 0, 6));
						echo showStatus(__("There was a problem while trying to obtain your key. Please retry"), 'warning');
					}
					else {
						$key = mysql_result($query, 0);

						$mailing->confirmRegistration($userInfo['email'], $userInfo['ID'], $key);

						echo showStatus(__('Check your email (including SPAM) to activate your account'), 'ok');
					}
				}
			}
		}
		else {
			echo showStatus(__("Please fill all the inputs"), 'error');
		}
	}
}
else {
	if ($_GET) {
		if (($_GET['key']) && ($_GET['uid'])) {
			$check = $db->checkForgotKey($_GET['key'], $_GET['uid']);
			if ($check) {
				$userInfo = $db->getUserInfo($_GET['uid']);
				$salt = $userInfo['salt'];

				if ($userInfo) {
					$new_password = substr(md5(mt_rand()), 5, 15);
					$salt = substr(md5(mt_rand()), 0, 5);
					$password = md5(md5($new_password).md5($salt));
					$db->updateUserOptions($userInfo['ID'], array(
							'password' => $password,
							'salt' => $salt
						));
					$mailing->resetPassword($userInfo['email'], $new_password, $userInfo['ID']);
					$db->deleteKey($_GET['key'], $userInfo['ID']);
					echo showStatus(__('We have just sent you an e-mail with your new password'), 'ok');
				} else {
					echo showStatus(__('Invalid key'), 'error');
				}
			} else {
				echo showStatus(__('Invalid key'), 'error');
			}
		}
	}
}

$jk->load('trouble_login');

$jk->load('sidebar');
$jk->load('footer');



?>