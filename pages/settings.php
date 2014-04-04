<?php
// Jisko: An open-source microblogging application
// Copyright (C) 2008 Rub√©n D√≠az <outime@gmail.com>
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
global $db;
global $mailing;
global $jk;

if (!$_USER) header('Location: '.$jk->base);
else {

	$jk->title = __('Settings');
	$jk->page_module = PARAMS;

	global $sidebar;
	$sidebar = 'no_sidebar';

	switch ($jk->page_module) {
	default:
		$jk->page_module = 'profile';
	case 'profile':
		if ($_POST) {
			if ($_POST['auth'] != md5($_USER['salt'])) header('Location: '.coreLink('settings', 'profile'));
			else {
				if ($_POST['url'] != $_USER['profile']['url'] || ($_POST['bio'] != $_USER['profile']['bio'])) {
					$db->updateProfile($_USER['ID'], array(
							'url' => filter_var($_POST['url'], FILTER_VALIDATE_URL),
							'bio' => $_POST['bio'],
						));
				}

				$db->updateUserOptions($_USER['ID'], array(
						'location' => $_POST['location'],
						'realname' => $_POST['realname'],
						'gravatar' => ($_POST['use_gravatar'] == 'on') ? '1' : '0',
						'language' => $_POST['language'],
					));

				if (!empty($_FILES['avatar']['tmp_name'])) {
					$upload = uploadAvatar($_FILES['avatar']);
					switch ($upload) {
					case 'INVALID_EXTENSION':
						$error = 'invalid_ext';
						break;
					case 'BIG_FILE':
						$error = 'big_file';
						break;
					case 'ERROR_COPY':
						$error = 'error_copy';
						break;
					case 'CANT_DELETE':
						$error = 'cant_delete';
						break;
					}
				}

				if (!isset($error)) header('Location: '.coreLink(array('ok'), 'settings', 'profile'));
				else header('Location: '.coreLink(array('error='.$error), 'settings', 'profile'));
			}
		}
		else {
			if ($_GET['action'] == 'delete_avatar') {
				if ($_GET['auth'] != md5($_USER['salt'])) $error = __('You are not allowed to perform this operation');
				else {
					$avatar = $_USER['avatar'];
					$username = $_USER['username'];
					$avatar_info = pathinfo(PATH."users_files/$username/img/avatar/$avatar");
					$path = PATH.'users_files/'.$username.'/img/avatar';
					if (!@unlink($path."/$avatar") || (!@unlink($path.'/'.$avatar_info['filename'].'_side.'.$avatar_info['extension']) || (!@unlink($path.'/'.$avatar_info['filename'].'_note.'.$avatar_info['extension']) || (!@unlink($path.'/'.$avatar_info['filename'].'_follow.'.$avatar_info['extension']))))) $error = __('There was a problem while trying to delete your avatar');
					else {
						$db->updateUserOptions($_USER['ID'], array('avatar' => null));
						$ok = '';
						$jk->updateUser('avatar', false);
						$_USER['avatar'] = '';
					}
				}
			}

			$jk->load('functions');
			$jk->load('header');

			if (!isset($error) && (!isset($_GET['error']))) {
				$ok = (isset($ok) ? $ok : $_GET['ok']);
				if (isset($ok) && (empty($ok))) echo showStatus(__('Settings updated!'), 'ok');
			}
			else {
				if ($_GET['error']) {
					switch ($_GET['error']) {
						case 'invalid_ext':
							$error = __('Invalid extension!');
							break;
						case 'big_file':
							$error = __('File too big!');
							break;
						case 'error_copy':
							$error = __('There was a problem while trying to upload your avatar');
							break;
						case 'cant_delete':
							$error = __('There was a problem while trying to delete your previous avatar');
					}
				}
				echo showStatus($error, 'error');
			}
			$jk->load('settings');
		}
		break;
	case 'config':
		if ($_POST) {
			//extract($_POST);

			$return = array();
			if ($_USER['password']) {
				$test = md5(md5($_POST['current_password']).md5($_USER['salt']));
				if ($test == $_USER['password']) $test = true;
				else $test = false;
			}
			else {
				if (!$_USER['password'] && ($_USER['facebook'] || $_USER['openid'])) $test = true;
				else $test = false;
			}

			if ($test == true) {
				if (!empty($_POST['new_username']) && ($_POST['new_username'] != $_USER['username'])) {
					
					$validUsr = validUsername($_POST['new_username'], true);
					
					if ($validUsr != 'valid') {
						if ($validUsr == 'busy') header('Location: '.coreLink(array('error=taken_user'), 'settings', 'config'));
						elseif ($validUsr == 'invalid') header('Location: '.coreLink(array('error=invalid_user'), 'settings', 'config'));
						die();
					}
					else {
						$res = rename(PATH.'users_files/'.$_USER['username'], PATH.'users_files/'.trim($_POST['new_username']));
						if ($res) $return['username'] = trim($_POST['new_username']);
						else {
							header('Location: '.coreLink(array('error=dir'), 'settings', 'config'));
							die();
						}
					}
				}

				if ($_USER['email'] != $_POST['email']) {
					if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
						header('Location: '.coreLink(array('error=invalid_mail'), 'settings', 'config'));
						die();
					}
					else {
						if ($db->checkEmail($_POST['email'], $_USER['ID'])) {
							header('Location: '.coreLink(array('error=taken_mail'), 'settings', 'config'));
							die();
						}
						else {
							if (isEmailConfirmationEnabled()) {
								$return['email'] = $_POST['email'];
							}
							else {
								$db->deleteKeys($_USER['ID'], 'email');
								$token = substr(md5(rand()), 0, 12);
								$db->newKey($_USER['ID'], 'email', $token, $_POST['email']);
								$mailing->emailChange($_USER['email'], $_USER['ID'], $_POST['email'], $token);
								header('Location: '.coreLink(array('ok=mail'), 'settings', 'config'));
								die();
							}
						}
					}
				}

				if ($_USER['jabber'] != $_POST['jabber']) {
					if (!filter_var($_POST['jabber'], FILTER_VALIDATE_EMAIL)) header('Location: '.coreLink(array('error=invalid_jabber'), 'settings', 'config'));
					else {
						if (!$db->checkJabber($_POST['jabber'], $_USER['ID'])) header('Location: '.coreLink(array('error=taken_jabber'), 'settings', 'config'));
						else $return['jabber'] = $_POST['jabber'];
					}
				}

				if (!empty($_POST['new_password'])) {
					if ($_POST['new_password'] != $_POST['new_password2']) header('Location: '.coreLink(array('error=pass'), 'settings', 'config'));
					else {
						$salt = substr(md5(mt_rand()), 0, 5);
						$new_password = md5(md5($_POST['new_password']).md5($salt));
						$mailing->passwordChange($_USER['email'], $_USER['ID'], $_SERVER['REMOTE_ADDR']);
						$return['salt'] = $salt;
						$return['password'] = $new_password;
					}
				}

				if ($_POST['new_api'] == 'on') {
					$newapi = substr(md5($_USER['username'].rand()), 0, 16);
					$return['api'] = $newapi;
				}

				if ($_POST['notification_level'] != $_USER['notification_level']) {
					$notification_level = (int) $_POST['notification_level'];
					if ($notification_level > 5) $notification_level = 5;
					$return['notification_level'] = $notification_level;
				}

				if ($_POST['openid'] != $_USER['openid']) {
					if (filter_var($openid, FILTER_VALIDATE_URL)) {
						import('openid/functions');
						import('openid/class.dopeopenid');
						$openid = new Dope_OpenID($openid);
						$openid->setReturnURL($jk->base);
						$openid->SetTrustRoot($jk->base);
						$endpoint_url = $openid->getOpenIDEndpoint();
						if ($endpoint_url) {
							if (!$db->checkOpenID($openid->getIdentity())) {
								$return['openid'] = $openid->getIdentity();
							}
							else header('Location: '.coreLink(array('error=taken_openid'), 'settings', 'config'));
						}
						else header('Location: '.coreLink(array('error=openid_conn'), 'settings', 'config'));
					}
					else header('Location: '.coreLink(array('error=openid_invalid'), 'settings', 'config'));
				}

				if ($_POST['shorter_service'] != $_USER['shorter_service']['service'] || ((bool)$_POST['shorted_preview'] != $_USER['shorter_service']['preview'])) {
					$short_array['service'] = $_POST['shorter_service'];
					$short_array['preview'] = (bool) $_POST['shorted_preview'];

					$return['shorter_service'] = serialize($short_array);
				}

				if (count($return) > 0) {
					$db->updateUserOptions($_USER['ID'], $return);
					if (isset($ok)) header('Location: '.coreLink(array('ok='.urlencode($ok)), 'settings', 'config'));
					else header('Location: '.coreLink(array('ok'), 'settings', 'config'));
				}
				else header('Location: '.coreLink(array('ok=nc'), 'settings', 'config'));
			}
			else {
				header('Location: '.coreLink(array('error=verification'), 'settings', 'config'));
				die();
			}
		}
		else {
			if (isset($_GET['confirm']) && $_GET['key']) {
				$check = $db->checkEmailKey($_GET['key'], $_USER['ID']);
				if ($check) {
					$newEmail = $db->getEmailFromKey($_GET['key'], $_USER['ID']);
					$_USER['email'] = $newEmail;
					$db->updateUserOptions($_USER['ID'], array('email' => $newEmail));
					$db->deleteKey($_GET['key'], $_USER['ID']);
					$ok = __('We have changed successfully your email');
					$jk->selectSelfUser();
				}
			}

			$jk->load('functions');
			$jk->load('header');
			
			if (isset($_GET['ok'])) {
				$ok = $_GET['ok'];
			}

			if (!isset($_GET['error']) && isset($ok)) {
				if (empty($ok)) echo showStatus(__('Settings updated!'), 'ok');
				else {
					switch ($ok) {
						case 'nc':
							$ok = __('Nothing changed...');
							break;
						case 'mail':
							$ok = __('We have sent you a confirm message to the new mail!');
							break;
					}
					echo showStatus($ok, 'ok');
				}
			}
			else {
				switch ($_GET['error']) {
					case 'taken_user':
						$error = __('Taken username, please choose another!');
						break;
					case 'invalid_user':
						$error = __("Sorry! Your choosen username appears to be invalid");
						break;
					case 'invalid_mail':
						$error = __('Not valid email');
						break;
					case 'taken_mail':
						$error = __('E-mail has already been taken');
						break;
					case 'invalid_jabber':
						$error = __('Not valid jabber account');
						break;
					case 'taken_jabber':
						$error = __('Jabber has already been taken');
						break;
					case 'pass':
						$error = __("Passwords don't match");
						break;
					case 'taken_openid':
						$error = __('The provided OpenID url was already linked with another account');
						break;
					case 'openid_conn':
						$error = __('Error while trying to contact the OpenID server');
						break;
					case 'openid_invalid':
						$error = __('Invalid OpenID url.');
						break;
					case 'verification':
						$error = __('Invalid password/login verification');
						break;
					case 'dir':
						$error = __('There was an error while trying to migrate your user data');
						break;
				}
				if (isset($error)) echo showStatus($error, 'error');
			}
			$jk->load('settings');
		}

		break;
	case 'customize':
		if ($_POST) {
			$return = array();

			if (!empty($_FILES['background']['name'])) {
				$upload = uploadBackground($_FILES['background']);
				switch ($upload) {
				case 'INVALID_EXTENSION':
					header('Location: '.coreLink(array('error=invalid_ext'), 'settings', 'customize'));
					die();
					break;
				case 'TOO_BIG':
					header('Location: '.coreLink(array('error=too_big'), 'settings', 'customize'));
					die();
					break;
				case 'ERROR_DELETE_PREV':
					header('Location: '.coreLink(array('error=error_delete_prev'), 'settings', 'customize'));
					die();
					break;
				case 'ERROR_UPLOAD':
					header('Location: '.coreLink(array('error=error_upload'), 'settings', 'customize'));
					die();
					break;
				default:
					$_USER['customize']['background'] = $upload;
				}
			}

			if ($_POST['text_color'] != $_USER['customize']['profile_text_color']) {
				if (preg_match('/^#[a-f0-9]{6}$/i', $_POST['text_color']) || empty($_POST['text_color'])) {
					$_USER['customize']['profile_text_color'] = $_POST['text_color'];
				}
			}

			if ($_POST['background_color'] != $_USER['customize']['profile_background_color']) {
				if (preg_match('/^#[a-f0-9]{6}$/i', $_POST['background_color']) || empty($_POST['background_color'])) {
					$_USER['customize']['profile_background_color'] = $_POST['background_color'];
				}
			}

			if ($_POST['sidebar_color'] != $_USER['customize']['profile_sidebar_color']) {
				if (preg_match('/^#[a-f0-9]{6}$/i', $_POST['sidebar_color']) || empty($_POST['sidebar_color'])) {
					$_USER['customize']['profile_sidebar_color'] = $_POST['sidebar_color'];
				}
			}

			if ($_POST['links_color'] != $_USER['customize']['profile_links_color']) {
				if (preg_match('/^#[a-f0-9]{6}$/i', $_POST['links_color']) || empty($_POST['link_color'])) {
					$_USER['customize']['profile_links_color'] = $_POST['links_color'];
				}
			}

			if ($_POST['sidebar_text_color'] != $_USER['customize']['profile_sidebar_text_color']) {
				if (preg_match('/^#[a-f0-9]{6}$/i', $_POST['sidebar_text_color']) || empty($_POST['sidebar_text_color'])) {
					$_USER['customize']['profile_sidebar_text_color'] = $_POST['sidebar_text_color'];
				}
			}

			if ($_POST['style'] != $_USER['customize']['background_style']) {
				$allowed_styles = array('normal', 'repeat', 'centered', 'fixed');
				if (!in_array($_POST['style'], $allowed_styles)) $_POST['style'] = 'normal';
				$_USER['customize']['background_style'] = $_POST['style'];
			}

			if ($_POST['theme'] != $_USER['theme']) {
				if (in_array($_POST['theme'], $jk->allowed_themes)) $return['theme'] = $_POST['theme'];
			}
			
			$return['customize'] = serialize($_USER['customize']);

			if (count($return) > 0) {
				$db->updateUserOptions($_USER['ID'], $return);
			}
			if (isset($ok)) header('Location: '.coreLink(array('ok='.urlencode($ok)), 'settings', 'customize'));
			else header('Location: '.coreLink(array('ok'), 'settings', 'customize'));
		}
		else {
			if ($_GET['action'] == 'delete') {
				if ($_GET['auth'] != md5($_USER['salt'])) $error = __('You are not allowed to perform this operation');
				else {
					if (!@unlink(PATH.'/users_files/'.$_USER['username'].'/img/background/bg.'.$_USER['customize']['background'])) $error = __('There was a problem while trying to delete your background');
					else {
						$_USER['customize']['background'] = '';
						$jk->updateUser('customize_background', false);
						$db->updateUserOptions($_USER['ID'], array('customize' => serialize($_USER['customize'])));
						$ok = '';
					}
				}
			}

			$jk->load('functions');
			$jk->load('header');

			if (!isset($error) && (!isset($_GET['error']))) {
				if (isset($_GET['ok'])) {
					if (empty($_GET['ok'])) echo showStatus(__('Settings updated!'), 'ok');
					else echo showStatus(__(utf8_htmlentities($_GET['ok'])), 'ok');
				}
				elseif (isset($ok)) echo showStatus(__('Settings updated!'), 'ok');
			}
			else {
				if ($_GET['error']) $error = $_GET['error'];
				switch ($error) {
					case 'invalid_ext':
						$error = __('Invalid background extension');
						break;
					case 'too_big':
						$error = __('Background image too big!');
						break;
					case 'error_delete_prev':
						$error = __('There was a problem while trying to delete your previous background');
						break;
					case 'error_upload':
						$error = __('There was a problem while trying to upload your background');
						break;
				}
				
				if ($error) echo showStatus($error, 'error');
			}
			$jk->load('settings');
		}

		break;
	case 'twitter':
		if ($_POST) {
			if ($_POST['auth'] != md5($_USER['salt'])) $error = __('You are not allowed to perform this operation');
			else {
				$db->updateTwitterOptions($_USER['ID'], array(
						'post_tweets' => (bool) $_POST['post_tweets'],
						'combined_view' => (bool) $_POST['combined_view']
					));

				header('Location: '.coreLink(array('ok'), 'settings', 'twitter'));
			}
		} elseif ($_GET['action'] == 'delete') {
			if ($_GET['auth'] != md5($_USER['salt'])) $error = __('You are not allowed to perform this operation');
			else {
				$db->updateUserOptions($_USER['ID'], array('twitter'=>null));

				header('Location: '.coreLink(array('ok=disabled'), 'settings', 'twitter'));
			}
		}

		$jk->load('functions');
		$jk->load('header');

		if (!isset($error) && (!isset($_GET['error']))) {
			if (isset($_GET['ok'])) {
				switch ($_GET['ok']) {
					case 'disabled':
						$ok = __('Disabled twitter integration with success');
						break;
					default:
						$ok = __('Settings updated!');
						break;
				}
				echo showStatus($ok, 'ok');
			}
		}
		else {
			if (isset($_GET['error'])) {
				switch ($_GET['error']) {
					case 'token':
						$error = __('There was a problem while trying to link your account with Twitter');
						break;
					case 'auth':
						$error = __('You are not allowed to perform this operation');
						break;
				}
			}
			
			if ($error) echo showStatus($error, 'error');
		}
		$jk->load('settings');
		break;
	case 'ignores':
		$jk->load('functions');
		$jk->load('header');
		$jk->load('settings');
		break;
	case 'privacy':
		if ($_POST) {
			if ($_POST['show_followings'] != $_USER['privacy']['show_followings']) {
				$_USER['privacy']['show_followings'] = (int) $_POST['show_followings'];
			}

			if ($_POST['show_followers'] != $_USER['privacy']['show_followers']) {
				$_USER['privacy']['show_followers'] = (int) $_POST['show_followers'];
			}

			if ($_POST['show_notes'] != $_USER['privacy']['show_notes']) {
				$_USER['privacy']['show_notes'] = (int) $_POST['show_notes'];
			}

			if ($_POST['show_favorite'] != $_USER['privacy']['show_favorite']) {
				$_USER['privacy']['show_favorite'] = (int) $_POST['show_favorite'];
			}

			if ($_POST['show_profile_info'] != $_USER['privacy']['show_profile_info']) {
				$_USER['privacy']['show_profile_info'] = (int) $_POST['show_profile_info'];
			}

			if ($_POST['allow_read_rss'] != $_USER['privacy']['allow_read_rss']) {
				$_USER['privacy']['allow_read_rss'] = (int) $_POST['allow_read_rss'];
			}

			$db->updateUserOptions($_USER['ID'], array('privacy' => serialize($_USER['privacy'])));
			header('Location: '.coreLink(array('ok'), 'settings', 'privacy'));
		}
		else {
			$jk->load('functions');
			$jk->load('header');

			if (isset($_GET['ok'])) echo showStatus(__('Settings updated!'), 'ok');

			$jk->load('settings');
		}
	}

	$jk->load('sidebar');
	$jk->load('footer');
}

?>