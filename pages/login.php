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

global $_USER, $jk;
if ($_USER) header('Location: '.$jk->base);
else {
	global $db;
	global $_POST;
	if (isset($_GET['openid_verify'])) {
		import('openid/class.dopeopenid');
		$openid_url = $_GET['openid_identity'];
		if (!$openid_url) header('Location: '.coreLink(array('error=oidentity'), 'login'));
		else {
			$openid = new Dope_OpenID($openid_url);
			$validate_result = $openid->validateWithServer();
			if ($validate_result === TRUE) {
				$userInfo = $db->getUserInfo(false, false, false, $openid_url);
				if ($userInfo) {
					extract($userInfo);
					if ($status == 'banned') header('Location: '.coreLink(array('error=banned'), 'login'));
					elseif ($status == 'ok') {
						$SID = md5($since.$salt.$api.time());
						$db->newSession($userInfo['ID'], $SID);
						setcookie('jisko_'.md5($jk->base), $SID, time()+(86400*60));
						header('Location: '.$jk->base);
					}
					elseif ($status == 'nc') header('Location: '.coreLink(array('error=nc'), 'login'));
				}
				else {
					$info = $openid->filterUserInfo($_GET);
					header('Location: '.coreLink(array('nickname='.$info['nickname'], 'email='.$info['email'], 'openid='.$openid_url), 'register'));
				}
			}
			else if ($openid->isError() === TRUE) {
				$the_error = $openid->getError();
				header('Location: '.coreLink(array('error=oierror', 'st_error='.urlencode($the_error['description']), 'co_error='.(int)$the_error['code']), 'login'));
			}
			else header('Location: '.coreLink(array('error=oidentity'), 'login'));
		}
	}
	else if (isset($_GET['openid_mode']) && $_GET['openid_mode'] == "cancel") {
		header('Location: '.coreLink(array('error=oicancel'), 'login'));
	}
	else {
		if (!$_POST) {
			if ($_GET) {
				global $jk, $sidebar;
				$sidebar = 'login';

				$jk->title = __('Login');
				$jk->load('functions');
				$jk->load('header');
				$jk->load('login');

				if (isset($_GET['ok'])) {
					if (empty($_GET['ok'])) echo showStatus(__('Settings updated!'), 'ok');
					else echo showStatus(__(utf8_htmlentities($_GET['ok'])), 'ok');
				}
				else {
					if (!$error && $_GET['error']) $error = $_GET['error'];
					
					switch ($error) {
						case 'banned':
							$error = __('Your username is banned');
							break;
						case 'nc':
							$error = __('This account has not been confirmed yet');
							break;
						case 'pass':
							$error = __('Incorrect password');
							break;
						case 'noexist':
							$error = __('The username does not exist');
							break;
						case 'fields':
							$error = __('There are empty fields, fill them and try again');
							break;
						case 'oinvalid':
							$error = __('Invalid OpenID url.');
							break;
						case 'oierror':
							$error = sprintf(__('OpenID Error: %s (%s)'), $_GET['st_error'], $_GET['co_error']);
							break;
						case 'oicancel':
							$error = __('OpenID authorization canceled by user.');
							break;
						case 'oidentity':
							$error = __('Could not validate your OpenID Identity');
							break;
						case 'nologged':
							$error = __('Please login and then retry');
							break;
					}
					
					if ($error) echo showStatus($error, 'error');
				}

				$jk->load('sidebar');
				$jk->load('footer');
			}
			else header('Location: '.$jk->base);
		}
		else {
			$postusername = $_POST['username'];
			$postpassword = $_POST['password'];
			$ajax = (bool) $_POST['ajax'];
			$postopenid = trim($_POST['openid']);
			$mobile = (bool) $_POST['usemobile'];
	
			if ($mobile) define('NO_GUI', 1);
			if (!empty($postopenid) && ($postopenid != 'OpenID')) {
				import('openid/class.dopeopenid');
	
				if (filter_var($postopenid, FILTER_VALIDATE_URL)) {
					$openid = new Dope_OpenID($postopenid);
					$openid->setReturnURL(coreLink(array('openid_verify'), 'login'));
					$openid->SetTrustRoot($jk->base);
					$openid->setOptionalInfo(array('nickname', 'language', 'email'));
					$endpoint_url = $openid->getOpenIDEndpoint();
					if ($endpoint_url) {
						// If we find the endpoint, you might want to store it for later use.
						$_SESSION['openid_endpoint_url'] = $endpoint_url;
						// Redirect the user to their OpenID Provider
						$openid->redirect();
						// Call exit so the script stops executing while we wait to redirect.
						exit;
					}
					else {
						$the_error = $openid->getError();
						header('Location: '.coreLink(array('error=oierror', 'st_error='.urlencode($the_error['description']), 'co_error='.(int)$the_error['code']), 'login'));
					}
				}
				else header('Location: '.coreLink(array('error=oinvalid'), 'login'));
			}
			else {
				if (empty($postusername) or empty($postpassword)) {
					if ($ajax) echo json_encode(array('error'=>__('There are empty fields, fill them and try again')));
					elseif ($mobile) header('Location: '.coreLink(array('err=empty'), 'mobile', 'login'));
					else header('Location: '.coreLink(array('error=fields'), 'login'));
				} else {
					if (filter_var($postusername, FILTER_VALIDATE_EMAIL)) $userInfo = $db->getUserInfo(false, false, $postusername);
					else $userInfo = $db->getUserInfo(false, $postusername);
	
					if ($userInfo) {
						extract($userInfo);
						if ($userInfo['status'] == 'banned') {
							if ($ajax) echo json_encode(array('error'=>__('Your username is banned')));
							elseif ($mobile) header('Location: '.coreLink(array('err=ban'), 'mobile', 'login'));
							else header('Location: '.coreLink(array('error=banned'), 'login'));
							die();
						}
						$enc_password = md5(md5($postpassword).md5($userInfo['salt']));
						if (($userInfo['password'] == $enc_password) && ($userInfo['status'] == 'ok')) {
							$SID = md5($userInfo['since'].$userInfo['salt'].$userInfo['api'].time());
							$db->newSession($userInfo['ID'], $SID);
							setcookie('jisko_'.md5($jk->base), $SID, time()+(86400*60));
	
							if ($ajax) echo json_encode(array('ok'=>'ok'));
							elseif ($mobile) header('Location: '.coreLink('mobile', 'notes'));
							else header('Location: '.$jk->base);
						}
						elseif ($userInfo['status'] == 'nc') {
							if ($ajax) echo json_encode(array('error'=>__('This account has not been confirmed yet')));
							elseif ($mobile) header('Location: '.coreLink(array('err=nc'), 'mobile', 'login'));
							else header('Location: '.coreLink(array('error=nc'), 'login'));
						}
						else {
							if ($ajax) echo json_encode(array('error'=>__('Incorrect password')));
							elseif ($mobile) header('Location: '.coreLink(array('err=passwd'), 'mobile', 'login'));
							else header('Location: '.coreLink(array('error=pass'), 'login'));
						}
					} else {
						if ($ajax) echo json_encode(array('error'=>__('The username does not exist')));
						elseif ($mobile) header('Location: '.coreLink(array('err=noname'), 'mobile', 'login'));
						else header('Location: '.coreLink(array('error=noexist'), 'login'));
					}
				}
			}
		}
	}
}

?>