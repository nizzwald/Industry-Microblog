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

global $db;
global $_USER;
global $mailing, $jk;

if ($_USER) header('Location: '.$jk->base);
else {
	global $allowed, $sidebar;
	$sidebar = 'login';

	$allowed = check_invitation($_REQUEST['token']);

	if (!$_POST) {
		$jk->title = __('Register');

		$jk->load('functions');
		$jk->load('header');

		if ($_GET) {
			if (isset($_GET['ok'])) {
				if (!isEmailConfirmationEnabled()) {
					echo showStatus(__('Check your email (including SPAM) to activate your account'), 'ok');
				}
				else echo showStatus(__('You can access now with your account, thank you!'), 'ok');
			}
			elseif (($_GET['key']) and ($_GET['uid'])) {
				$result = $db->checkRegKey($_GET['key'], $_GET['uid']);
				if ($result) {
					echo showStatus(__('You can access now with your account, thank you!'), 'ok');
					$db->updateUserOptions($_GET['uid'], array('status' => 'ok'));
					$db->deleteKey($_GET['key'], $_GET['uid']);
					$userInfo = $db->getUserInfo($_GET['uid']);
					$mailing->registrationSuccess($userInfo['email'], $userInfo['ID']);
					if ($jk->alert_on_newuser == true) $mailing->alertNewUser($userInfo['username']);
				} else {
					echo showStatus(__('Invalid confirmation code!'), 'error');
				}
			}
		}
		if ($_GET['error']) {
			$error = $_GET['error'];

			switch ($error) {
				case 'invalidtoken':
					$error = __('You need a valid token to register an account!');
					break;
				case 'invalidmail':
					$error = __('Sorry! The provided email is invalid');
					break;
				case 'tos':
					$error = __('You must accept the Terms of Service (TOS)');
					break;
				case 'takenuser':
					$error = __('Taken username, please choose another!');
					break;
				case 'takenmail':
					$error = __('Email is taken, please choose another!');
					break;
				case 'pass':
					$error = __("Passwords don't match!");
					break;
				case 'recaptcha':
					$error = __('Incorrect reCAPTCHA code!');
					break;
				case 'user':
					$error = __("Sorry! Your choosen username appears to be invalid");
					break;
				case 'create':
					$error = __('There was a problem while trying to create your user');
					break;
			}

			if ($error) echo showStatus($error, 'error');
		}

		$jk->load('register');
		$jk->load('sidebar');
		$jk->load('footer');
	}
	else {
		if ($allowed) {
			global $mailing;

			if (recaptcha_enabled()) import('recaptchalib');
			$token = $_POST['token'];

			if (!check_invitation($token)) header('Location: '.coreLink(array('error=invalidtoken'), 'register'));
			else {
				if (!$_POST['legal'] && ($jk->tos == true)) header('Location: '.coreLink(array('error=tos'), 'register'));
				else {
					$username = $_POST['username'];
					$email = $_POST['email'];
					$salt = substr(md5(rand()), 0, 5);
					$api = substr(md5($_POST['username'].rand()), 0, 16);
					$password = md5(md5($_POST['password']).md5($salt));
					$password2 = md5(md5($_POST['password2']).md5($salt));
					$ip = $_SERVER['REMOTE_ADDR'];
					$language = $_POST['language'];

					if ($_POST['openid']) {
						if (filter_var($_POST['openid'], FILTER_VALIDATE_URL)) {
							if (!$db->checkOpenID($_POST['openid'])) {
								import('openid/functions');
								import('openid/class.dopeopenid');
								$openid = new Dope_OpenID($_POST['openid']);
								$openid->setReturnURL($jk->base);
								$openid->SetTrustRoot($jk->base);
								$openid->setOptionalInfo(array('nickname', 'language', 'email'));
								$endpoint_url = $openid->getOpenIDEndpoint();
								if ($endpoint_url) $openidz = $openid->getIdentity();
								else $openidz = false;
							}
						}
						else $openidz = false;
					}
					else $openidz = false;
					if ($_POST['fbid']) {
						if (!empty($_POST['fbid'])) {
							if (!$db->checkFacebook($_POST['fbid'])) $fbidz = $_POST['fbid'];
							else $fbidz = false;
						}
						else $fbidz = false;
					}
					else $fbidz = false;

					if (!isEmailConfirmationEnabled()) $noc = false; else $noc = true;
					if (recaptcha_enabled()) $human = recaptcha_check_answer($jk->recaptcha_privatekey, $ip, $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
					
					$validUsr = validUsername($username, true);
					
					if ($validUsr != 'valid') {
						if ($validUsr == 'busy') header('Location: '.coreLink(array('error=takenuser'), 'register'));
						elseif ($validUsr == 'invalid') header('Location: '.coreLink(array('error=user'), 'register'));
					}
					else {
						if ($db->checkEmail($email)) header('Location: '.coreLink(array('error=takenmail'), 'register'));
						else {
							if (!$fbidz && !$openidz && ($password != $password2)) header('Location: '.coreLink(array('error=pass'), 'register'));
							else {
								if (recaptcha_enabled() && !$human->is_valid) header('Location: '.coreLink(array('error=recaptcha'), 'register'));
								else {
									if (!filter_var($email, FILTER_VALIDATE_EMAIL)) header('Location: '.coreLink(array('error=invalidmail'), 'register'));
									else {
										if (!mkdir(PATH."users_files/$username", 0777) || (!mkdir(PATH."users_files/$username/img", 0777) || (!mkdir(PATH."users_files/$username/img/avatar", 0777) || (!mkdir(PATH."users_files/$username/img/background", 0777) || (!mkdir(PATH."users_files/$username/files", 0777)))))) {
											header('Location: '.coreLink(array('error=create'), 'register'));
										}
										else {
											$tmpKey = substr(md5(rand()), 0, 6);

											if ($fbidz || ($openidz)) $password = '';

											$newUser = $db->newUser(
												$username,
												$password,
												$api,
												$salt,
												$language,
												$jk->default_theme,
												$email,
												$ip,
												$tmpKey,
												$noc,
												$openidz,
												$fbidz
											);

											if (!empty($token)) $db->deleteToken($token);

											if ($noc == false) {
												$mailing->confirmRegistration($email, $newUser, $tmpKey);
											}

											header('Location: '.coreLink(array('ok'), 'register'));
										}
									}
								}
							}
						}
					}
				}
			}
		}
		else {
			header('Location: '.coreLink(array('error=invalidtoken'), 'register'));
		}
	}
}

?>