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
global $jk;
global $globals;

if ($_USER) {
	if (checkAdminPermissions($_USER['ID'], 'can_panel')) {
		if ($_POST) {
			switch (PARAMS) {
			case 'misc':
				if (get_magic_quotes_gpc()) {
					$_POST['tos'] = stripslashes(nl2br($_POST['tos']));
					$_POST['homepage'] = stripslashes(nl2br($_POST['homepage']));
					$_POST['faq'] = stripslashes(nl2br($_POST['faq']));
				}
				
				foreach (array('tos', 'homepage', 'faq') as $a) $_POST[$a] = mysql_real_escape_string(str_replace("\r\n", '', str_replace("\r", '', str_replace("\n", '', nl2br($_POST[$a])))));
				
				$db->updateJiskoSettings(array(
						'tos_content' => $_POST['tos'],
						'homepage_content' => $_POST['homepage'],
						'faq_content' => $_POST['faq']
					));
				header('Location: '.coreLink(array('ok'), 'admin', 'misc'));
				break;
			case 'themes':
				if ($jk->default_theme != $_POST['default']) $return['default_theme'] = mysql_real_escape_string($_POST['default']);
				if ($jk->allowed_themes != $_POST['allowed']) $return['allowed_themes'] = mysql_real_escape_string(serialize($_POST['allowed']));

				if (count($return) > 0) $db->updateJiskoSettings($return);
				header('Location: '.coreLink(array('ok'), 'admin', 'themes'));
				break;
			case 'general':
				if ($jk->name != trim($_POST['name'])) {
					if (!empty($_POST['name'])) $return['name'] = trim($_POST['name']);
				}
				if ($jk->separator != $_POST['separator']) $return['separator'] = $_POST['separator'];
				if ($jk->base != trim($_POST['baseURL'])) {
					if (filter_var($_POST['baseURL'], FILTER_VALIDATE_URL)) $return['base_url'] = trim($_POST['baseURL']);
				}
				if ($jk->admin_mail != trim($_POST['admin_mail'])) {
					if (filter_var($_POST['admin_mail'], FILTER_VALIDATE_EMAIL)) $return['admin_mail'] = trim($_POST['admin_mail']);
				}
				if ($jk->abuse_mail != trim($_POST['abuse_mail'])) {
					if (filter_var($_POST['abuse_mail'], FILTER_VALIDATE_EMAIL)) $return['abuse_mail'] = trim($_POST['abuse_mail']);
				}
				if ($jk->cron_password != trim($_POST['cron_pw'])) {
					if (!empty($_POST['cron_pw'])) $return['cron_pw'] = $_POST['cron_pw'];
				}
				$clean_urls = ($_POST['cleanURLs'] == 'on' ? true : false);
				$enable_mbstring = ($_POST['enable_mbstring'] == 'on' ? true : false);
				$is_debug = ($_POST['is_debug'] == 'on' ? true : false);

				if ($jk->cleanUrls != $clean_urls) $return['clean_urls'] = $clean_urls;
				if ($jk->enable_mbstring != $enable_mbstring) $return['enable_mbstring'] = $enable_mbstring;
				if ($jk->is_debug != $is_debug) $return['is_debug'] = $is_debug;

				$denied_ext = explode(',', str_replace(' ', '', $_POST['denied_ext']));
				if ($jk->denied_extensions != $denied_ext) $return['denied_extensions'] = serialize($denied_ext);

				if (!empty($_FILES['logo']['tmp_name'])) {
					$extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
					if (in_array($extension, $globals['allowed_extensions'])) {
						if ($_FILES['logo']['error'] == 0) {
							$lname = 'logo_'.substr(md5(rand()), 0, 6).'.'.$extension;
							if (copy($_FILES['logo']['tmp_name'], PATH.'static/img/logos/'.$lname)) {
								$return['logo'] = $lname;
							}
						}
					}
				}

				if ($jk->meta_keywords != $_POST['meta_keywords']) $return['meta_keywords'] = $_POST['meta_keywords'];
				if ($jk->meta_description != $_POST['meta_description']) $return['meta_description'] = $_POST['meta_description'];
				if ($jk->meta_robots != $_POST['meta_robots']) $return['meta_robots'] = $_POST['meta_robots'];
				if ($jk->maintenance != $_POST['maintenance']) $return['maintenance'] = $_POST['maintenance'];

				if (count($return) > 0) $db->updateJiskoSettings($return);
				header('Location: '.coreLink(array('ok'), 'admin', 'general'));
				break;
			case 'environment':
				if ($jk->wait_until_repost != trim($_POST['wait_repost'])) {
					if (is_numeric($_POST['wait_repost'])) $return['wait_until_repost'] = (int)$_POST['wait_repost'];
				}
				if ($jk->wait_until_refollow != trim($_POST['wait_refollow'])) {
					if (is_numeric($_POST['wait_refollow'])) $return['wait_until_refollow'] = (int)$_POST['wait_refollow'];
				}
				if ($jk->notes_per_page != trim($_POST['notes_per_page'])) {
					if (is_numeric($_POST['notes_per_page'])) $return['notes_per_page'] = (int)$_POST['notes_per_page'];
				}
				if ($jk->ajax_refresh != trim($_POST['ajax_refresh'])) {
					if (is_numeric($_POST['ajax_refresh'])) $return['ajax_refresh'] = (int)$_POST['ajax_refresh'];
				}
				if ($jk->default_lang != $_POST['language']) $return['language'] = $_POST['language'];
				if ($jk->recaptcha_publickey != $_POST['recaptcha_public']) $return['recaptcha_publickey'] = $_POST['recaptcha_public'];
				if ($jk->recaptcha_privatekey != $_POST['recaptcha_secret']) $return['recaptcha_secretkey'] = $_POST['recaptcha_secret'];
				if ($jk->home_page != trim($_POST['home_page'])) $return['home_page'] = trim($_POST['home_page']);
				if ($jk->fb_apikey != $_POST['fb_apikey']) $return['fb_apikey'] = $_POST['fb_apikey'];
				if ($jk->fb_secretkey != $_POST['fb_secretkey']) $return['fb_secretkey'] = $_POST['fb_secretkey'];
				if ($jk->tw_consumerkey != $_POST['tw_consumerkey']) $return['tw_consumerkey'] = $_POST['tw_consumerkey'];
				if ($jk->tw_secretkey != $_POST['tw_secretkey']) $return['tw_secretkey'] = $_POST['tw_secretkey'];

				$new_user = ($_POST['new_user'] == 'on' ? true : false);
				$del_user = ($_POST['del_user'] == 'on' ? true : false);
				$confirm_email = ($_POST['confirm_email'] == 'on' ? false : true);
				$tos = ($_POST['tos'] == 'on' ? true : false);

				if ($jk->use_invitations != $use_invitations) $return['use_invitations'] = $use_invitations;
				if ($jk->alert_on_newuser != $new_user) $return['alert_on_newuser'] = $new_user;
				if ($jk->alert_on_deluser != $del_user) $return['alert_on_deluser'] = $del_user;
				if ($jk->no_confirmation_email != $confirm_email) $return['no_confirmation_email'] = $confirm_email;
				if ($jk->tos != $tos) $return['tos'] = $tos;

				if (count($return) > 0) $db->updateJiskoSettings($return);
				header('Location: '.coreLink(array('ok'), 'admin', 'environment'));
				break;
			case 'users':
				if ($_POST['userID']) {
					$userInfo = $db->getUserInfo((int)$_POST['userID']);
					if ($userInfo) {
						if ($_POST['username'] != $userInfo['username']) $return['username'] = $_POST['username'];
						if (!empty($_POST['password'])) {
							global $mailing;
							$salt = substr(md5(mt_rand()), 0, 5);
							$new_password = md5(md5($_POST['password']).md5($salt));
							$mailing->passwordChange($userInfo['email'], $userInfo['ID'], $_SERVER['REMOTE_ADDR']);
							$return['salt'] = $salt;
							$return['password'] = $new_password;
						}
						if ($_POST['email'] != $userInfo['email']) $return['email'] = $_POST['email'];
						if ($_POST['api'] != $userInfo['api']) $return['api'] = $_POST['api'];
						if ($_POST['status'] != $userInfo['status']) $return['status'] = $_POST['status'];
						if ($_POST['profile_name'] != $userInfo['realname']) $return['realname'] = $_POST['profile_name'];
						if ($_POST['profile_web'] != $userInfo['profile']['web']) $profile['url'] = $_POST['profile_web'];
						if ($_POST['profile_location'] != $userInfo['location']) $return['location'] = $_POST['profile_location'];
						if ($_POST['profile_bio'] != $userInfo['profile']['bio']) $profile['bio'] = $_POST['profile_bio'];
						if ($_POST['language'] != $userInfo['language']) $return['language'] = $_POST['language'];
						if ($_POST['theme'] != $userInfo['theme']) $return['theme'] = $_POST['theme'];
						if ($_POST['invitations'] != $userInfo['invitations']) $return['invitations'] = (int) $_POST['invitations'];
						if ($_POST['openid'] !== $userInfo['openid']) $return['openid'] = $_POST['openid'];
						if ($_POST['facebook'] !== $userInfo['facebook']) $return['facebook'] = $_POST['facebook'];

						if (count($profile) > 0) $db->updateProfile((int)$_POST['userID'], $profile);
						if (count($return) > 0) $db->updateUserOptions((int)$_POST['userID'], $return);
					}
				}

				header('Location: '.coreLink(array('id='.$_POST['userID']), 'admin', 'users'));
				break;
			case 'shorter_urls':
				if ($jk->allowed_shorter_service != $_POST['url_shorter']) $return['allowed_url_shorters'] = serialize($_POST['url_shorter']);
				if ($jk->default_shorter_service != $_POST['default']) $return['default_url_shorter'] = $_POST['default'];
				if ($jk->threely_apicode != $_POST['3ly']) $return['threely_apicode'] = $_POST['3ly'];
				if ($jk->bitly_login != $_POST['bitly_login']) $return['bitly_login'] = $_POST['bitly_login'];
				if ($jk->bitly_apicode != $_POST['bitly_apicode']) $return['bitly_apicode'] = $_POST['bitly_apicode'];

				if (count($return) > 0) $db->updateJiskoSettings($return);
				header('Location: '.coreLink(array('ok'), 'admin', 'shorter_urls'));
				break;
			default:
				header('Location: '.coreLink('admin'));
			}
		}
		else {
			switch (PARAMS) {
			case 'misc':
				showAdminHeader(__('Miscellaneous'));

				$tos_content = $db->getJiskoSettings(array('tos_content'));
				$faq_content = $db->getJiskoSettings(array('faq_content'));
				$hom_content = $db->getJiskoSettings(array('homepage_content'));
					
				if (isset($_GET['ok'])) echo '<div class="ok">'.__('Settings updated!').'</div>';
					
				echo '<form action="'.coreLink('admin', 'misc').'" method="post"><ul class="inputs" style="width:600px">
				<li style="height:300px">
					<div style="float:right"><textarea name="tos" rows="18" cols="44" style="padding:5px;border:1px solid #ddd">'.stripslashes(str_replace('<br>', "\n", str_replace('<br />', "\n", $tos_content['tos_content']))).'</textarea></div>
					'.__('ToS Content').'<br /><small>'.__('Text that will be displayed when checking Terms of Service. HTML allowed').'</small>
				</li>
				<li style="height:300px">
					<div style="float:right"><textarea name="faq" rows="18" cols="44" style="padding:5px;border:1px solid #ddd">'.stripslashes(str_replace('<br>', "\n", str_replace('<br />', "\n", $faq_content['faq_content']))).'</textarea></div>
					'.__('FAQ Content').'<br /><small>'.__('Text that will be displayed when checking the FAQ. HTML allowed').'</small>
				</li>
				<li style="height:300px">
					<div style="float:right"><textarea name="homepage" rows="18" cols="44" style="padding:5px;border:1px solid #ddd">'.stripslashes(str_replace('<br>', "\n", str_replace('<br />', "\n", $hom_content['homepage_content']))).'</textarea></div>
					'.__('Homepage content').'<br /><small>'.__('Text that will be displayed when checking the default homepage. HTML allowed').'</small>
				</li>
				</ul><br /><input type="submit" value="'.__('Save').'"><br /><br /></form>';

				showAdminFooter();
				break;
			case 'themes':
				showAdminHeader(__('Themes'));
				
				if (isset($_GET['ok'])) echo '<div class="ok">'.__('Settings updated!').'</div>';

				$dir = PATH.'themes/';
				if (is_dir($dir)) {
					if ($dirfd = opendir($dir)) {
						while (($file = readdir($dirfd)) !== false) {
							if (is_dir($dir.$file)) {
								if (is_file($dir.$file.'/info.xml')) {
									$fd = fopen($dir.$file.'/info.xml', 'r');
									$content = fread($fd, filesize($dir.$file.'/info.xml'));

									$xml = new SimpleXMLElement($content);
									if ($xml) {
										$them = array(
											'id' => $file,
											'name' => $xml->name,
											'license' => $xml->license
										);
										foreach ($xml->author as $author) {
											$them['author'][] = array(
												'name' => $author->name,
												'website' => $author->website,
												'email' => $author->email
											);
										}
										$themes[] = $them;
									}
								}
							}
						}
					}
					else echo __('There was an error while trying to open the themes folder');
				}
				else echo __('There is no themes folder');

				echo '<form action="'.coreLink('admin', 'themes').'" method="post">
				<ul class="inputs">';
				
				foreach ($themes as $theme) {
					echo '
					<li>
						<div style="float:right"><input type="radio" name="default" value="'.$theme['id'].'"';
					if ($jk->default_theme == $theme['id']) echo ' checked="checked"';
					echo '"><span style="font-size:.8em">'.__('Default theme').'</span>&nbsp;</div>
						'.$theme['name'].'<br /><small>';
					for ($i=0;$i <= (count($theme['author'])-1);$i++) {
						if ($theme['author'][$i]['email']) $email = '(<a href="mailto:'.$theme['author'][$i]['email'].'">'.$theme['author'][$i]['email'].'</a>)';
						else $email = '';
						if ($theme['author'][$i]['website']) $name = '<a href="'.$theme['author'][$i]['website'].'">'.$theme['author'][$i]['name'].'</a>';
						else $name = $theme['author'][$i]['name'];


						if ($i == 0) echo sprintf(__('by %s'), $name.' '.$email).'<br />';
						else echo sprintf(__('and %s'), $name.' '.$email).'<br />';
					}
					echo '</small>
					</li>
					';
				}
				echo '<li>';
				$allowed = $jk->allowed_themes;
				if (!$allowed) $allowed = array();
				echo '<fieldset id="shorters" style="border: 1px solid #ddd;-moz-border-radius:2px;-webkit-border-radius:2px"><legend>'.__('Allowed themes').'</legend><br /><ul>';
				$i = 0;
				
				$count = count($themes);

				foreach ($themes as $theme) {
					echo '<li';
					if (($count > 1) && ($i%2 == 0)) echo ' style="float:right;width:250px"';
					echo '><input type="checkbox" name="allowed[]" value="'.$theme['id'].'"';
					if (in_array($theme['id'], $allowed)) echo ' checked';
					echo '> '.$theme['name'].'</li>';
					$i++;
				}
				echo '</ul></fieldset>
				</li>
				</ul><br /><input type="submit" value="'.__('Save').'"><br /><br /></form>';

				showAdminFooter();
				break;
			case 'users':
				showAdminHeader(__('Users'));
				
				if (isset($_GET['ok'])) echo '<div class="ok">'.__('Settings updated!').'</div>';

				if (!$_GET['id']) {
					$i = 0;
					$array = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
					
					if (!$_GET['filter'] || (!in_array($_GET['filter'], $array))) $filter = 'a'; else $filter = $_GET['filter'];
					
					$query = $db->send('SELECT `ID`, `username` FROM `users` WHERE `username` LIKE \''.$filter.'%\'');
					
					echo '<ul class="filter">';
					foreach ($array as $letter) {
						echo '<li><a href="'.coreLink(array('filter='.$letter), 'admin', 'users').'"';
						if ($letter == $filter) echo ' style="color:red"';
						echo '>'.$letter.'</a></li>';
					}
					echo '</ul>
					<ul class="inputs">';
					
					$count = mysql_num_rows($query);
					while ($row = mysql_fetch_row($query)) {
						echo '<li';
						if (($count > 1) && ($i%2 == 0)) echo ' style="float:right;width:250px"';
						echo '><a href="'.coreLink(array('id='.$row[0]), 'admin', 'users').'">@'.$row[1].'</a></li>';
						$i++;
					}
					echo '</ul>';
				}
				else {
					$userInfo = $db->getUserInfo($_GET['id']);
					if ($userInfo) {
						echo '<form action="'.coreLink('admin', 'users').'" method="post"><input type="hidden" name="userID" value="'.$userInfo['ID'].'"><ul class="inputs">
						<li>
							<div style="float:right"><input type="text" name="username" class="input" value="'.$userInfo['username'].'"></div>
						'.__('Username').'<br /><small>'.__('Nickname of the user').'</small>
						</li>
						<li>
							<div style="float:right"><input type="text" name="pass" class="input" value=""></div>
						'.__('Change password').'<br /><small>'.__("Fill this input if you want to change it's password").'</small>
						</li>
						<li>
							<div style="float:right"><input type="text" name="email" class="input" value="'.$userInfo['email'].'"></div>
							'.__('Email').'<br /><small>'.__('Email of the user').'</small>
						</li>
						<li>
							<div style="float:right"><input type="text" name="api" class="input" value="'.$userInfo['api'].'"></div>
							'.__('API Code').'<br /><small>'.__('Used to access through the API').'</small>
						</li>
						<li>
							<div style="float:right"><select class="input" name="status" style="width:311px">';
						foreach (array('ok'=>__('Active'), 'nc'=>__('Not confirmed'), 'banned'=>__('Banned')) as $key=>$long) {
							echo '<option value="'.$key.'"';
							if ($key == $userInfo['status']) echo ' selected';
							echo '>'.$long.'</option>';
						}
						echo '</select>
							</div>
							'.__('Status').'<br /><small>'.__('It can be active, banned..').'</small>
						</li>
						<li>
							<fieldset id="shorters" style="border: 1px solid #ddd;-moz-border-radius:2px;-webkit-border-radius:2px"><legend>'.__('Profile').'</legend><br /><div style="padding-left:40px;margin-bottom:20px;width:110px">';
						$avatar = getAvatar($userInfo['ID'], '48');
						if (!$userInfo['gravatar'] && ($avatar != $jk->base."static/img/avatar/default_note.png")) echo '<div style="float:right;font-size:.8em"><input type="checkbox" name="avatar"> <img src="'.$jk->base.'static/img/trash.gif"></div>';
						echo '<img src="'.getAvatar($userInfo['ID'], '48').'" style="border: 1px solid #ddd"></div>
							<ul>
							<li>
								<div style="float:right"><input type="text" name="profile_name" class="input" value="'.$userInfo['realname'].'"></div>
								'.__('Name').'<br /><small>'.__('Real name of the user').'</small>
							</li>
							<li>
								<div style="float:right"><input type="text" name="profile_web" class="input" value="'.$userInfo['profile']['url'].'"></div>
								'.__('Website').'<br /><small>'.__('Website of the user').'</small>
							</li>
							<li>
								<div style="float:right"><input type="text" name="profile_location" class="input" value="'.$userInfo['location'].'"></div>
								'.__('Location').'<br /><small>'.__('Location of the user').'</small>
							</li>
							<li>
								<div style="float:right"><input type="text" name="profile_bio" class="input" value="'.$userInfo['profile']['bio'].'"></div>
								'.__('Bio').'<br /><small>'.__('Description of the user').'</small>
							</li>
							</ul></fieldset>
						</li>
						<li>
							<div style="float:right"><select class="input" name="language" style="width:311px">';
						foreach (return_languages() as $short=>$lang) {
							echo '<option value="'.$short.'"';
							if ($short == $userInfo['language']) echo ' selected';
							echo '>'.$lang.'</option>';
						}
						echo '</select>
							</div>
							'.__('Language').'<br /><small>'.__('Language of the user').'</small>
						</li>
						<li>
							<div style="float:right"><select class="input" name="theme" style="width:311px">';
						foreach ($jk->allowed_themes as $theme) {
							echo '<option value="'.$theme.'"';
							if ($theme == $userInfo['theme']) echo ' selected';
							echo '>'.$theme.'</option>';
						}
						echo '</select>
							</div>
							'.__('Theme').'<br /><small>'.__('Skin of Jisko').'</small>
						</li>
						<li>
							<div style="float:right"><input type="text" name="invitations" class="input" value="'.$userInfo['invitations'].'"></div>
							'.__('Number of invitations').'<br /><small>'.__('Number of invitations that the user has').'</small>
						</li>
						<li>
							<div style="float:right"><input type="text" name="openid" class="input" value="'.$userInfo['openid'].'"></div>
							'.__('OpenID').'<br /><small>'.__('Used to access Jisko trough an OpenID account').'</small>
						</li>
						<li>
							<div style="float:right"><input type="text" name="facebook" class="input" value="'.$userInfo['facebook'].'"></div>
							'.__('Facebook ID').'<br /><small>'.__('Used to access Jisko trough a Facebook account').'</small>
						</li>
						</ul>
						<br /><input type="submit" value="'.__('Save').'"><br /><br /></form>';
					}
					else header('Location: '.coreLink('admin', 'users'));
				}

				showAdminFooter();
				break;
			case 'shorter_urls':
				showAdminHeader(__('URL Shortening'));
				
				if (isset($_GET['ok'])) echo '<div class="ok">'.__('Settings updated!').'</div>';

				echo '<form action="'.coreLink('admin', 'shorter_urls').'" method="post">
				<ul class="inputs">
					<li>
						<div style="float:right"><select class="input" name="default" style="width:311px">';
				foreach (availableShorterServices() as $short=>$lang) {
					echo '<option value="'.$short.'"';
					if ($short == $jk->default_shorter_service) echo ' selected';
					echo '>'.$lang.'</option>';
				}
				echo '</select>
						</div>
						'.__('Default').'<br /><small>'.__('The default URL shortening service').'</small>
					</li>
					<li>';
				$allowed = load_url_shorters();
				echo '<fieldset id="shorters" style="border: 1px solid #ddd;-moz-border-radius:2px;-webkit-border-radius:2px"><legend>'.__('Allowed URL Shortening services').'</legend><br /><ul>';
				$i = 0;
				$sh_services = availableShorterServices();
				$count = count($sh_services);
				
				foreach ($sh_services as $short=>$lang) {
					echo '<li';
					if (($count > 1) && ($i%2 == 0)) echo ' style="float:right;width:250px"';
					echo '><input type="checkbox" name="url_shorter[]" value="'.$short.'"';
					if (array_key_exists($short, $allowed)) echo ' checked';
					echo '> '.$lang.'</li>';
					$i++;
				}
				echo '</ul></fieldset>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="3ly" value="'.$jk->threely_apicode.'"></div>
						'.__('3.ly API Code').'<br /><small>'.sprintf(__('Required to use 3.ly. More info at %s'), '<a href="http://3.ly">http://3.ly</a>').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="bitly_login" value="'.$jk->bitly_login.'"></div>
						'.__('bit.ly/j.mp login').'<br /><small>'.__('Required to use bit.ly or j.mp').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="bitly_apicode" value="'.$jk->bitly_apicode.'"></div>
						'.__('bit.ly/j.mp API Code').'<br /><small>'.__('Required to use bit.ly or j.mp').'</small>
					</li>
				</ul>
				<br /><input type="submit" value="'.__('Save').'"><br /><br />
				</form>';

				showAdminFooter();
				break;
			case 'environment':
				showAdminHeader(__('Environment'));
				
				if (isset($_GET['ok'])) echo '<div class="ok">'.__('Settings updated!').'</div>';

				echo '<form action="'.coreLink('admin', 'environment').'" method="post">
				<ul class="inputs">
					<li>
						<div style="float:right"><select class="input" name="language" style="width:311px">';
				foreach (return_languages() as $short=>$lang) {
					echo '<option value="'.$short.'"';
					if ($short == $jk->default_lang) echo ' selected';
					echo '>'.$lang.'</option>';
				}
				echo '</select>
						</div>
						'.__('Language').'<br /><small>'.__('The default language of your Jisko installation').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="home_page" value="'.$jk->home_page.'"></div>
						'.__('Default home page').'<br /><small>'.__('For the default home page type: home_page').'</small>
					</li>
					<li>';
				if ($jk->use_invitations == true) echo '<div style="float:right"><input type="checkbox" class="input" name="use_invitations" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="use_invitations"></div>';
				echo __('Use invitations').'<br /><small>'.__('Users will need an invitation in order to register on Jisko').'</small>
					</li>
					<li>';
				if ($jk->alert_on_newuser == true) echo '<div style="float:right"><input type="checkbox" class="input" name="new_user" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="new_user"></div>';
				echo __('Email when new user').'<br /><small>'.__('Send me an email when a user registers on Jisko').'</small>
					</li>
					<li>';
				if ($jk->alert_on_deluser == true) echo '<div style="float:right"><input type="checkbox" class="input" name="del_user" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="del_user"></div>';
				echo __('Email when deleted user').'<br /><small>'.__('Send me an email when a user deletes their account on Jisko').'</small>
					</li>
					<li>';
				if ($jk->no_confirmation_email == false) echo '<div style="float:right"><input type="checkbox" class="input" name="confirm_email" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="confirm_email"></div>';
				echo __('Confirm email').'<br /><small>'.__('Confirm email when an user registers on Jisko').'</small>
					</li>
					<li>';
				if ($jk->tos == true) echo '<div style="float:right"><input type="checkbox" class="input" name="tos" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="tos"></div>';
				echo __('Enable ToS').'<br /><small>'.__('Users will be prompted to accept the Terms of Service').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="wait_repost" value="'.$jk->wait_until_repost.'"></div>
						'.__('Seconds until another post').'<br /><small>'.__('Recommended is 10 seconds').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="wait_refollow" value="'.$jk->wait_until_refollow.'"></div>
						'.__('Seconds until another follow').'<br /><small>'.__('Recommended is 25 seconds').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="ajax_refresh" value="'.$jk->ajax_refresh.'" ></div>
						'.__('Seconds until AJAX refresh').'<br /><small>'.__('Recommended is 25 seconds').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="notes_per_page" value="'.$jk->notes_per_page.'"></div>
						'.__('Notes per page').'<br /><small>'.__('Amount of notes shown in each section of Jisko').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="recaptcha_public" value="'.$jk->recaptcha_publickey.'"></div>
						'.__('reCAPTCHA public Key').'<br /><small>'.sprintf(__('More info at %s'), '<a href="http://recaptcha.com">http://recaptcha.com</a>').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="recaptcha_secret" value="'.$jk->recaptcha_privatekey.'"></div>
						'.__('reCAPTCHA secret Key').'<br /><small>'.sprintf(__('More info at %s'), '<a href="http://recaptcha.com">http://recaptcha.com</a>').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="fb_apikey" value="'.$jk->fb_apikey.'"></div>
						'.__('Facebook API Key').'<br /><small>'.sprintf(__('If you want to support Facebook connect. More info at %s'), '<a href="http://developers.facebook.com/connect.php">http://developers.facebook.com/connect.php</a>').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="fb_secretkey" value="'.$jk->fb_secretkey.'"></div>
						'.__('Facebook secret Key').'<br /><small>'.sprintf(__('If you want to support Facebook connect. More info at %s'), '<a href="http://developers.facebook.com/connect.php">http://developers.facebook.com/connect.php</a>').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="tw_consumerkey" value="'.$jk->tw_consumerkey.'"></div>
						'.__('Twitter consumer Key').'<br /><small>'.sprintf(__('If you want to support Twitter integration. More info at %s'), '<a href="http://dev.twitter.com">http://dev.twitter.com</a>').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="tw_secretkey" value="'.$jk->tw_secretkey.'"></div>
						'.__('Twitter consumer secret Key').'<br /><small>'.sprintf(__('If you want to support Twitter integration. More info at %s'), '<a href="http://dev.twitter.com">http://dev.twitter.com</a>').'</small>
					</li>
				</ul>
				<br /><input type="submit" value="'.__('Save').'"><br /><br />
				</form>';

				showAdminFooter();
				break;
			case 'general':
			default:
				showAdminHeader(__('General'));
				
				if (isset($_GET['ok'])) echo '<div class="ok">'.__('Settings updated!').'</div>';

				echo '
				<script>
					$(document).ready(function() {
						$.getJSON(\'http://app.jisko.org/update.php?v=1&ver='.INTER_VERSION.'&callback=?\', function(data) {
							if (!data.error) {
								$("#update").html(\''.__('There is a new update available! Find more information about Jisko %version at  %url').'\'.replace(\'%version\', \'<strong>\'+data.name+\'</strong>\').replace(\'%url\', \'<a href="\'+data.url+\'">\'+data.url+\'</a>\'));
								$("#update").fadeIn();
							}
						});
					});
				</script>
				<form action="'.coreLink('admin', 'general').'" method="post" enctype="multipart/form-data">
				<ul class="inputs">
					<li>
						<div style="float:right"><input type="text" class="input" name="name" value="'.$jk->name.'"></div>
						'.__('Name').'<br /><small>'.__('Name of your Jisko installation').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="separator" value="'.$jk->separator.'"></div>
						'.__('Separator').'<br /><small>'.__('The symbol which separes the title and the name of Jisko').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="baseURL" value="'.str_replace('http://', '', str_replace('https://', '', substr($jk->base, 0, -1))).'" ></div>
						'.__('Base URL of Jisko').'<br /><small>'.__('The URL where Jisko is located. Without http://').'</small>
					</li>
					<li>
						<div style="float:right"><input type="file" class="input" name="logo"></div>
						'.__('Logo').'<br /><small>'.__('Logo of Jisko').'</small>
					</li>
					<li>';
				if ($jk->cleanUrls == true) echo '<div style="float:right"><input type="checkbox" class="input" name="cleanURLs" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="cleanURLs"></div>';

				echo __('Clean URLs').'<br /><small>'.__('Not supported by every server').'</small>
					</li>
					<li>';
				if ($jk->enable_mbstring == true) echo '<div style="float:right"><input type="checkbox" class="input" name="enable_mbstring" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="enable_mbstring"></div>';

				echo __('Enable MBString').'<br /><small>'.__('Fixes some problems when counting unicode characters').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="admin_mail" value="'.$jk->admin_mail.'"></div>
						'.__('Admin mail').'<br /><small>'.__('Used for the contact page...').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="abuse_mail" value="'.$jk->abuse_mail.'"></div>
						'.__('Abuse mail').'<br /><small>'.__('Used for abuse reports...').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="meta_keywords" value="'.$jk->meta_keywords.'"></div>
						'.__('Meta keywords').'<br /><small>'.__('Keywords that describe your website').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="meta_description" value="'.$jk->meta_description.'"></div>
						'.__('Meta description').'<br /><small>'.__('Description of your website').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="meta_robots" value="'.$jk->meta_robots.'"></div>
						'.__('Meta robots').'<br /><small>'.__('Actions for search robots on your website').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="cron_pw" value="'.$jk->cron_password.'"></div>
						'.__('Cron password').'<br /><small>'.__('The password to execute the cron script').'</small>
					</li>
					<li>';
				if ($jk->is_debug == true) echo '<div style="float:right"><input type="checkbox" class="input" name="is_debug" checked></div>';
				else echo '<div style="float:right"><input type="checkbox" class="input" name="is_debug"></div>';
				echo __('MySQL Debug').'<br /><small>'.__('Log MySQL Errors in debug.log').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="denied_ext" value="'.implode(',', $jk->denied_extensions).'"></div>
						'.__('Upload denied extensions').'<br /><small>'.__('Separated by commas').'</small>
					</li>
					<li>
						<div style="float:right"><input type="text" class="input" name="maintenance" value="'.$jk->maintenance.'"></div>
						'.__('Maintenance mode').'<br /><small>'.__('To enable it, fill the input with any text. To disable it, just clear the input').'</small>
					</li>
				</ul>
				<br /><input type="submit" value="'.__('Save').'"><br /><br />
				</form>';

				showAdminFooter();
			}
		}

	}
	else header('Location: '.$jk->base);
}
else header('Location: '.$jk->base);

?>