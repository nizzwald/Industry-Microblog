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
global $mailing;
global $jk;

if ($_USER) {
	if ($_POST) {
		if (!empty($_POST['openid']) && ($_POST['openid'] != 'OpenID')) {
			if (filter_var($_POST['openid'], FILTER_VALIDATE_URL)) {
				import('openid/class.dopeopenid');
				$openid = new Dope_OpenID($_POST['openid']);
				$openid->setReturnURL($jk->base);
				$openid->SetTrustRoot($jk->base);
				$endpoint_url = $openid->getOpenIDEndpoint();
				if ($endpoint_url) {
					if ($db->checkOpenID($openid->getOpenIDEndpoint()) == $_USER['openid']) {
						$db->deleteKeys($_USER['ID'], 'drop');
						$token = substr(md5(rand()), 0, 12);
						$db->newKey($_USER['ID'], 'drop', $token);
						if (isEmailConfirmationEnabled()) {
							if (removeDir(PATH.'users_files/'.$_USER['username'])) {
								if ($jk->alert_on_deluser == true) $mailing->alertDelUser($_USER['ID']);
								closeSession($_USER['ID']);
								$db->deleteUser($_USER['ID']);
								header('Location: '.$jk->base);
							}
							else $error = 'dir';
						}
						else {
							$mailing->confirmDrop($_USER['email'], $_USER['username'], $_USER['ID'], $token);
							header('Location: '.coreLink(array('ok'), 'drop_account'));
						}
					}
					else header('Location: '.coreLink(array('error=oinotlinked'), 'drop_account'));
				}
				else header('Location: '.coreLink(array('error=oiserver'), 'drop_account'));
			}
			else header('Location: '.coreLink(array('error=oinvalid'), 'drop_account'));
		}
		else {
			$postPassword = md5(md5($_POST['password']).md5($_USER['salt']));
			if ($_USER['password'] == $postPassword) {
				if (isEmailConfirmationEnabled()) {
					if (removeDir(PATH.'users_files/'.$_USER['username'])) {
						if ($jk->alert_on_deluser == true) $mailing->alertDelUser($_USER['ID']);
						closeSession($_USER['ID']);
						$db->deleteUser($_USER['ID']);
						header('Location: '.$jk->base);
					}
					else $error = 'dir';
				}
				else {
					$db->deleteKeys($_USER['ID'], 'drop');
					$token = substr(md5(rand()), 0, 12);
					$db->newKey($_USER['ID'], 'drop', $token);
					$mailing->confirmDrop($_USER['email'], $_USER['username'], $_USER['ID'], $token);
					header('Location: '.coreLink(array('ok'), 'drop_account'));
				}
			}
			else header('Location: '.coreLink(array('error=pass'), 'drop_account'));
		}
	}
	else {
		if (isset($_GET['facebook'])) {
			if ($_GET['auth'] == md5($_USER['salt'])) {
				import('facebook/facebook');

				$facebook = new Facebook($jk->fb_apikey, $fb->fb_secretkey);

				if (!$_GET['auth_token']) {
					$desad = $facebook->expire_session();
					$facebook->set_user(null, null);
					$facebook->clear_cookie_state();
				}

				$user_id = $facebook->require_login();

				if ($db->checkFacebook(md5($user_id)) == $_USER['ID']) {
					if (isEmailConfirmationEnabled()) {
						if (removeDir(PATH.'users_files/'.$_USER['username'])) {
							if ($jk->alert_on_deluser == true) $mailing->alertDelUser($_USER['ID']);
							closeSession($_USER['ID']);
							$db->deleteUser($_USER['ID']);
							header('Location: '.$jk->base);
						}
						else $error = 'dir';
					}
					else {
						$db->deleteKeys($_USER['ID'], 'drop');
						$token = substr(md5(rand()), 0, 12);
						$db->newKey($_USER['ID'], 'drop', $token);
						$mailing->confirmDrop($_USER['email'], $_USER['username'], $_USER['ID'], $token);
						$ok = '';
					}
				}
				else $error = 'fbnotlinked';
			}
			else $error = '403';
		}
		global $sidebar;
		$sidebar = 'my_profile';

		if (isset($_GET['confirm']) && !empty($_GET['key'])) {
			if ($db->checkDropKey($_GET['key'], $_USER['ID'])) {
				if ($jk->alert_on_deluser == true) $mailing->alertDelUser($_USER['ID']);
				if (removeDir(PATH.'users_files/'.$_USER['username'])) {
					$db->deleteUser($_USER['ID']);
					closeSession($_USER['ID']);
					$ok = __('Your account was successfully deleted.').'<br />'.__("Please remember that this action was not reversible, so your account data can't be restored.");
				}
				else $error = 'dir';
			}
			else $error = 'invalidcode';
		}

		echo '

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.LANG.'" lang="'.LANG.'"><head>
<title>'.__('Delete account').' // '.$jk->name.'</title><meta http-equiv="content-type" content="text/html; charset=utf-8" />
<style type="text/css">
.ok {background-color:green;color:white;padding:5px;margin:10px;}
.error {background-color:red;color:white;padding:5px;text-align:center;margin:10px;}
BODY{margin:auto;margin-top:50px;width:700px;}#contenedor .content{ font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 17px;background:url(../themes/transparency/img/web_bg.png);border:1px solid #ddd;border-radius:4px;-moz-border-radius:4px;color:black;}#contenedor .content,.title{padding:5px;padding-left:30px;padding-right:30px;}.footer{padding-top:5px;font-size:11px;color:#B9B7C0;font-family:"Lucida Grande",Arial,serif}.title{font-family:Georgia,Tahoma;color:#08004D;height:50px}.footer a{color:#B9B7D0}H3{font-style:italic;font-size:20px}.input{height:40px;width:300px;font-size:20px}
</style><link rel="shortcut icon" href="'.$jk->base.'favicon.ico" type="image/png" />
</head><body>
<div id="contenedor">
<a href="'.$jk->base.'"><img src="'.$jk->base.'static/img/logos/'.$jk->logo.'" style="border:0px" alt="'.$jk->name.'" /></a><br /><br />
<div class="title"><h3>'.__('Delete account').'</h3></div><div class="content">

		';

		if ((isset($ok) || isset($_GET['ok'])) && (!isset($error) && !isset($_GET['error']))) {
			if (isset($_GET['ok'])) {
				echo '<div class="ok">'.__('We have sent you a confirmation email to the email linked to this account. If you want to continue deleting your user, please follow the instructions placed in the email.').'<br /></div>';
			}
			elseif (isset($ok)) {
				echo '<div class="ok">'.$ok.'</div>';
			}
		}
		else {
			if (!$error && $_GET['error']) $error = $_GET['error'];

			switch ($error) {
				case 'oinotlinked':
					$error = __('The provided OpenID URL is not linked with your account');
					break;
				case 'oiserver':
					$error = __('Error while trying to contact the OpenID server');
					break;
				case 'oinvalid':
					$error = __('Invalid OpenID url.');
					break;
				case 'pass':
					$error = __("Incorrect password");
					break;
				case 'fbnotlinked':
					$error = __('This Facebook account is not linked with your account');
					break;
				case '403':
					$error = __('You are not allowed to perform this operation');
					break;
				case 'invalidcode':
					$error = __('Invalid/Expired authorization code');
					break;
				case 'dir':
					$error = __('There was an error while trying to delete your user');
					break;
			}

			if ($error) echo '<div class="error">'.$error.'</div>';

			echo '

<form action="'.coreLink('drop_account').'" method="post"><p>'.sprintf(__('In order to delete your account from %s, you have to enter your password in the next form. Then you will receive an email with the instructions to complete the process.'), $jk->name).'</p><p><input name="password" type="password" class="input" style="background: url(\''.$jk->base.'static/img/key.png\') no-repeat white; background-position:3% 50%;padding-left: 30px;padding-top: 5px;"/> </p><p>'.__("If you don't have a password but you have an OpenID or Facebook linked account, then select your prefered type of authentication to continue the process:").'</p>
<div style="width:550px;"><div style="float:right;padding-top:10px;">';
			if (isFacebookEnabled()) echo '<a href="'.coreLink(array('facebook', 'auth='.$_USER['salt']), 'drop_account').'"><img src="'.$jk->base.'static/img/fb_connect.png" style="border:0px" alt="'.__('Connect with Facebook').'" /></a>';

			echo '
</div>
<p><input name="openid" type="text" value="OpenID" class="input" onblur="if(this.value==\'\' || this.value==\'http://\') this.value=\'OpenID\';" onfocus="if(this.value==\'OpenID\') this.value=\'http://\';" style="background: url('.$jk->base.'static/img/openid.png) no-repeat white; background-position:3% 50%;padding-left: 30px;padding-top: 5px;"/> </p></div>
</form> <br />

			';

			if (isEmailConfirmationEnabled()) echo '<p style="font-weight:bold">'.__('Please remember that after clicking the button or logging into Facebook, your account will no longer exist').'</p>';
			else echo '<p>'.__("Your account won't be deleted until you confirm your decission through your email").'</p>';

			echo '<p><input name="submit" type="submit" value="'.__('Continue').'" class="submit" /></p><br /></div>';
		}

		echo '

</div>
<div class="footer">
<div style="float:right">
Powered by <a href="http://www.jisko.org">Jisko</a>
</div>'.sprintf(__('If you are having problems with Jisko contact us at %s'), '<a href="http://answers.launchpad.net/jisko">http://answers.launchpad.net/jisko</a>').'</div></body></html>

		';
	}
}
else header('Location: '.$jk->base);

?>