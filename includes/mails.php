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

class mailing
{
	var $email;
	var $headers;
	var $subject;
	var $text;

	function sendMail($email, $subject, $html_subject, $text, $notifications = false, $admin = false, $language = false)
	{
		global $jk;

		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8\r\n";
		$headers .= "From: ".$jk->name." <".$jk->admin_mail.">";

		$text = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" /></head><body><style type="text/css"> .footer a{color:#B9B7D0}</style><div id="contenedor"><a href="'.$jk->base.'"><img src="'.$jk->base.'static/img/logos/'.$jk->logo.'" style="border:0px;padding-left:30px;" alt="'.$jk->name.'" /></a><br /><br />
<div class="title" style="padding:5px;padding-left:30px;padding-right:30px;font-family:Georgia,Tahoma;color:#08004D;height:40px"><h3 style="font-style:italic;font-size:20px">'.$html_subject.'</h3></div><div class="content" style="font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 17px;background:url(../themes/transparency/img/web_bg.png);border:1px solid #ddd;border-radius:4px;-moz-border-radius:4px;color:black;padding:5px;padding-left:30px;padding-right:30px;"> '.$text.'</div></div>';

		$text .= $this->bottomMail($notifications, $admin, $language);

		$text .= '</body></html>';

		mail($email, $jk->name.' // '.$subject, $text, $headers);
	}

	function bottomMail($notifications = false, $admin = false, $language = false)
	{
		global $gettext_tables;
		global $jk;
		
		$return = '<div class="footer" style="padding-top:5px;font-size:11px;color:#B9B7C0;font-family:"Lucida Grande",Arial,serif"><div style="float:right">Powered by <a href="http://www.jisko.org">Jisko</a></div>';

		if ($language != false) {
			if (file_exists(PATH.'locale/'.deflang($language).'/LC_MESSAGES/messages.mo')) {
				$gettext_tables = new gettext_reader(
					new CachedFileReader(PATH.'locale/'.deflang($language).'/LC_MESSAGES/messages.mo')
				);
				$gettext_tables->load_tables();
			}
		}

		if ($admin == false) {
			if ($notifications) $return .= sprintf(__('Turn off this notifications at %s'), '<a href="'.coreLink('settings', 'config').'">'.coreLink('settings', 'config').'</a>')."<br />";

			$return .= sprintf(__('Have you got any problem? Contact the administrator at %s'), '<a href="'.coreLink('contact').'">'.coreLink('contact').'</a>').'<br />';
			if ($jk->tos == true) $return .= '<br />'.sprintf(__('Since you got registered on %s, you agree with the Terms of Service (TOS) placed in %s'), $jk->name, '<a href="'.coreLink('tos').'">'.coreLink('tos').'</a>').'<br />';
		}

		if ($language != false) {
			if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
				$gettext_tables = new gettext_reader(
					new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
				);
				$gettext_tables->load_tables();
			}
		}

		return $return.'</div>';
	}

	function forgottenPassword($email, $uid, $token)
	{
		global $gettext_tables;
		global $db;

		$userInfo = $db->getUserOptions($uid, array('username', 'language'));

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$text = str_replace('%username', '<a href="'.coreLink($userInfo['username']).'">'.$userInfo['username'].'</a>', __("Someone (probably you) has just requested a password reset for the account with %username as the username. Because we need to confirm the request, please click the link placed below in order to continue the process:")).'<br /><br /><a href="'.coreLink(array('uid='.$uid, 'key='.$token), 'trouble_login').'">'.coreLink(array('uid='.$uid, 'key='.$token), 'trouble_login').'</a><br /><br />'.__("Please remember that this link will expire in 24h, so if you didn't request a password change, simply ignore this email");

		$this->sendMail($email, __('Password reset instructions'), __('Password reset'), $text, false, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function resetPassword($email, $new_password, $userID)
	{
		global $gettext_tables;
		global $db;

		$userInfo = $db->getUserOptions($userID, array('language'));

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$text = __("We have received your request for changing your password and here is your new password:").'<br /><br />';
		$text .= '<div style="padding: 10px;background-color:#D9D9D9">'.$new_password.'</div><br />';
		$text .= sprintf(__('Remember that you can change your password at %s'), '<a href="'.coreLink('settings', 'config').'">'.coreLink('settings', 'config').'</a>');

		$this->sendMail($email, __('Your new password'), __('Your new password'), $text, false, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function newFollower($userID, $content)
	{
		global $gettext_tables;
		global $_USER;
		global $db;

		$userInfo = $db->getUserOptions($userID, array('language', 'email'));

		if (file_exists(PATH.'locale/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'locale/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$notes = $db->countNotes('archive', $_USER['ID']);
		$followers = $db->countFollowers($_USER['ID']);
		$following = $db->countFollowing($_USER['ID']);

		$subject = __("%real_or_username is now following you");
		$text = str_replace('%real_or_username', '<a href="'.coreLink($_USER['username']).'">'.utf8_htmlentities($content).'</a>', str_replace('%name', $jk->name, __("%real_or_username is now following your updates on %name"))).'<br /><br />';

		$text .= '<div style="padding: 10px;padding-bottom:20px;background-color:#D9D9D9"><div style="float:right;width:90%;margin-bottom:5px">'.$notes.' '.__('notes').'<br />'.$followers.' '.__('followers').'<br />'.sprintf(__('following %s users'), $following).'</div><img src="'.getAvatar($_USER['ID'], 48).'" height="48" width="48"></div><br /><br />'.sprintf(__('You can check his/her profile at %s'), coreLink($_USER['username']));

		$subject = str_replace('%real_or_username', $content, $subject);
		$text = str_replace('%name', $jk->name, $text);

		$this->sendMail($userInfo['email'], $subject, __('New follower'), $text, true, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function newPrivateNote($userInfo, $note, $USER, $attached_file = false, $id = false)
	{
		global $gettext_tables;

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$subject = __("Private message from %real_or_username");

		if ($USER['realname']) $content = $USER['realname'].' ('.$USER['username'].')';
		else $content = $USER['username'];

		$text = str_replace('%real_or_username', '<a href="'.coreLink($_USER['username']).'">'.$content.'</a>', __('%real_or_username has sent you a private message:'))."<br /><br />";
		$text .= '<div style="padding: 10px;background-color:#D9D9D9">'.utf8_htmlentities($note).'</div><br />';
		if ($attached_file) {
			$text .= sprintf(__('With the following attachment: %s'), coreLink('download', $id, $attached_file))."<br /><br />";
		}
		$text .= str_replace('%username', $USER['username'], str_replace('%base', coreLink('notes', 'private'), __("You can reply to this message by sending a note with \"!%username <your message>\" or through the web interface at %base")));

		$subject = str_replace('%real_or_username', $content, $subject);

		$this->sendMail($userInfo['email'], $subject, __('New private message'), $text, true, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function newReplyNote($userInfo, $note, $note1, $note2 = false, $attached_file = false)
	{
		global $gettext_tables;
		global $db;
		global $_USER;

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		if ($_USER['realname']) $content = $_USER['realname'].' ('.$_USER['username'].')';
		else $content = $_USER['username'];

		$subject = str_replace('%real_or_username', $content, __("Reply from %real_or_username"));

		$text = str_replace('%real_or_username', $content, __('%real_or_username has replied or mentioned you in the following note:'))."<br /><br />";
		$text .= '<div style="padding: 10px;background-color:#D9D9D9">'.utf8_htmlentities(stripslashes($note)).'</div><br />';
		if ($attached_file) $text .= sprintf(__('With the following attachment: %s'), coreLink('download', $note1, $attached_file))."<br /><br />";
		$text .= __('Permalink:').' <a href="'.coreLink($_USER['username'], $note1).'">'.coreLink($_USER['username'], $note1).'</a>';
		if ($note2) {
			$noteInfo = $db->getTextFromNoteID($note2);
			$text .= '<br /><br />'.__("This note was replying to:").'<br /><br /><div style="padding: 10px;background-color:#D9D9D9">'.utf8_htmlentities(stripslashes($db->getTextFromNoteID($note2))).'</div><br />'.__('Permalink:').' <a href="'.coreLink($userInfo['username'], $note2).'">'.coreLink($userInfo['username'], $note2).'</a>';
		}

		$this->sendMail($userInfo['email'], $subject, $subject, $text, true, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function confirmRegistration($email, $user_id, $token)
	{
		global $gettext_tables;
		global $db, $jk;

		$userInfo = $db->getUserOptions($user_id, array('language'));

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$subject = str_replace('%name', $jk->name, __('Confirmation for %name account'));

		$text = __('Someone (probably you) has requested an account in %name. However, we need to know if you are the owner of this mail account.');
		$text .= '<br />'.__('To confirm it, please click in the link placed below:').'<br /><br /><a href="'.coreLink(array('uid='.$user_id, 'key='.$token), 'register').'">'.coreLink(array('uid='.$user_id, 'key='.$token), 'register').'</a><br /><br />';
		$text .= __("If you didn't request an account at %name, ignore this mail and apologies for the inconvenience.");

		$text = str_replace('%name', $jk->name, $text);

		$this->sendMail($email, $subject, __('Account confirmation'), $text, false, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function emailChange($email, $user_id, $new_email, $token)
	{
		global $gettext_tables;
		global $db, $jk;

		$userInfo = $db->getUserInfo($user_id);

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$text = str_replace('%name', $jk->name, __("Someone (probably you) has requested a change of the email assigned to your account in %name. Your new email will be:")).'<br /><br /><div style="padding: 10px;background-color:#D9D9D9">'.$new_email.'</div><br /><br />'.__('If you requested this change, then confirm it by clicking the link placed below:').'<br /><br/><a href="'.coreLink(array('confirm', 'key='.$token), 'settings', 'config').'">'.coreLink(array('confirm', 'key='.$token), 'settings', 'config').'</a><br /><br />'.__("Otherwise, if you didn't request this change, then simply ignore this message, because this link will expire in 24h");

		$this->sendMail($email, __('Confirm email change'), __('Email change'), $text, false, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function registrationSuccess($email, $username)
	{
		global $gettext_tables;
		global $db, $jk;

		$userInfo = $db->getUserOptions($userID, array('language'));

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$text = __("Welcome to %name").'<br /><br />';
		$text .= str_replace('%p_url', '<a href="'.coreLink('settings', 'profile').'">'.coreLink('settings', 'profile').'</a>', __('You have just confirmed your account in %name, so now you can start personalizing your profile at %p_url!'));
		$text .= '<br /><br />'.__('%name is based on Jisko, an open-source microblogging platform. You can find more information about Jisko at http://www.jisko.org').'<br /><br />'.__('Thank you very much for signing up in %name and for giving us a try!');

		$text = str_replace('%name', $jk->name, $text);

		$this->sendMail($email, __('Welcome!'), __('Welcome!'), $text, false, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function alertNewUser($username)
	{
		global $jk;

		$subject = __("There's a new user on %name");

		$text = __("A new user called %username has joined %name.").'<br /><br />'.__("It should be empty, but if you want, you can check his profile at %url");

		$subject = str_replace('%name', $jk->name, $subject);
		$text = str_replace('%username', '<span style="text-decoration:underline">'.$username.'</span>', str_replace('%name', $jk->name, $text));
		$text = str_replace('%url', '<a href="'.coreLink($username).'">'.coreLink($username).'</a>', $text);

		$this->sendMail($jk->admin_mail, $subject, __('New account'), $text, false, true);
	}

	function alertDelUser($uid)
	{
		global $db;
		global $jk;

		$uinfo = $db->getUserOptions($uid, array('username', 'email'));

		$subject = __("The user %username has deleted his account from %name");

		$text = __("The user %username has deleted his account from %name.").'<br /><br/>'.__("If you want to contact him, his email is %email");

		$subject = str_replace('%name', $jk->name, str_replace('%username', $uinfo['username'], $subject));
		$text = str_replace('%username', '<span style="text-decoration:underline">'.$username.'</span>', str_replace('%name', $jk->name, $text));
		$text = str_replace('%email', $uinfo['email'], $text);

		$this->sendMail($jk->admin_mail, $subject, __('Deleted account'), $text, false, true);
	}

	function newInvitation($email, $token)
	{
		global $gettext_tables;
		global $_USER, $jk;

		$subject = __('%username has invited you to %name!');

		$text = __("Hey!").'<br /><br />'.__("%username just sent you an invitation for using %name! %name is a microblogging portal where you can share your own experiences and your feelings about everything.").'<br/><br />'.__("Why don't you give %name a try and register?").'<br/><br />'.__('Click on the following link in order to use your invitation to %name').'<br /><br /><div style="padding: 10px;background-color:#D9D9D9"><a href="'.coreLink(array('token='.$token), 'register').'">'.coreLink(array('token='.$token), 'register').'</a></div><br /><br />'.__('Thank you and have fun on %name!');

		$subject = str_replace('%username', $_USER['username'], str_replace('%name', $jk->name, $subject));
		$text = str_replace('%username', '<a href="'.coreLink($_USER['username']).'">'.$_USER['username'].'</a>', str_replace('%name', $jk->name, $text));

		$this->sendMail($email, $subject, sprintf(__('Invitation to %s'), $jk->name), $text);
	}

	function passwordChange($email, $userID, $ip)
	{
		global $gettext_tables;
		global $db, $jk;

		$userInfo = $db->getUserOptions($userID, array('language'));

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$text = str_replace('%name', $jk->name, __("We send this mail to tell you that your password on %name has been changed.")).'<br /><br />';
		$text .= str_replace('%ip', $ip, str_replace('%contact_page', '<a href="'.coreLink('contact').'">'.coreLink('contact').'</a>', __('This change was requested from the ip %ip. If you were not the user who changed your password, please contact the administrator at %contact_page')));

		$this->sendMail($email, __('Your password has been changed'), __('Your password has been changed'), $text, false, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function confirmDrop($email, $username, $userID, $token)
	{
		global $gettext_tables;
		global $db, $jk;

		$userInfo = $db->getUserOptions($userID, array('language'));

		if (file_exists(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang($userInfo['language']).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		$text = str_replace('%name', $jk->name, str_replace('%contact_page', coreLink('contact'), __("Are you sure you want to delete your %name account? If you are having problems with %name, remember that you can contact us through %contact_page and we will take notes about your issues/suggestions.")));
		$text .= '<br /><br />'.__("If you're are completely sure of your decision (remember, this is NOT REVERSIBLE), please click the link placed below:");
		$text .= str_replace('%drop_url', '<a href="'.coreLink(array('key='.$token, 'confirm'), 'drop_account').'">'.coreLink(array('key='.$token, 'confirm'), 'drop_account').'</a>', '<br /><br />%drop_url<br /><br />').__('You must be logged in order to delete your account and remember that this request will expire in 24h.').'<br /><br />'.__('We will miss you!');

		$this->sendMail($email, __('Delete account confirmation'), __('Delete account confirmation'), $text, false, false, $userInfo['language']);

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
	}

	function sendMessage($email, $message, $ip, $username = false, $copy = false)
	{
		global $gettext_tables;
		global $jk;

		if (file_exists(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')) {
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.deflang(LANG).'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}

		if (!$username) $subject = __('Someone has sent you a message through the Contact page');
		else $subject = sprintf(__('%s has sent you a message through the Contact page'), $username);

		$text = __('Someone has decided to contact you through the contact page of your Jisko installation. Here are the details:')."<br /><br />";
		if ($username) $text .= sprintf('Username: %s', '<a href="'.coreLink($username).'">'.$username.'</a>')." - ";
		$text .= sprintf('IP: %s', '<a href="http://www.geoip.co.uk/?IP='.$ip.'">'.$ip.'</a>')." - ";
		$text .= sprintf('Email: %s', $email);

		$text .= '<br /><br /><div style="padding: 10px;background-color:#D9D9D9">'.utf8_htmlentities($message).'</div><br />';
		$text .= __("Please remember that you can only reply this message through the email provided. Otherwise, he won't see your answer");

		$this->sendMail($jk->admin_mail, $subject, __('New message received'), $text, false, true);
		if ($copy) $this->sendMail($email, $subject, __('New message received'), $text, false, true);
	}

	function reportAbuse($user_id, $reported_username, $ip)
	{
		global $gettext_tables;
		global $db;
		global $_USER;
		global $jk;

		$subject = str_replace('%username', $_USER['username'], str_replace('%r_username', $reported_username, __("%username reported the user %r_username")));

		$text = __("The user %username (ID: %id) from the IP %ip reported the user %r_username (ID: %r_id).");

		$text = str_replace('%username', '<a href="'.coreLink($_USER['username']).'">'.$_USER['username'].'</a>', str_replace('%r_username', '<a href="'.coreLink($reported_username).'">'.$reported_username.'</a>', $text));
		$text = str_replace('%id', $_USER['ID'], str_replace('%ip', '<a href="http://www.geoip.co.uk/?IP='.$ip.'">'.$ip.'</a>', $text));
		$text = str_replace('%r_id', $user_id, $text);

		$this->sendMail($jk->abuse_mail, $subject, __('New abuse report'), $text, false, true);
	}

	function reportAbuseNote($result, $ip)
	{
		global $gettext_tables;
		global $db;
		global $_USER;
		global $jk;

		$subject = __("%username reported the note %r_note");

		$text = __("The user %username (ID: %id) from the IP %ip reported the note %r_note made by %r_username (ID: %r_id).")."<br /><br />";
		$text .= '<div style="padding: 10px;background-color:#D9D9D9">%content</div><br /><br />'.__('Permalink:').' %link';

		$subject = str_replace('%username', $_USER['username'], str_replace('%r_note', '#'.$result['ID'], $subject));
		$text = str_replace('%username', '<a href="'.coreLink($_USER['username']).'">'.$_USER['username'].'</a>', str_replace('%id', $_USER['ID'], $text));
		$text = str_replace('%ip', '<a href="http://www.geoip.co.uk/?IP='.$ip.'">'.$ip.'</a>', str_replace('%r_note', '<a href="'.coreLink($result['username'], $result['ID']).'">#'.$result['ID'].'</a>', $text));
		$text = str_replace('%r_id', $result['ID'], str_replace('%r_username', '<a href="'.coreLink($result['username']).'">'.$result['username'].'</a>', $text));
		$text = str_replace('%content', utf8_htmlentities(put_smileys($result['note'])), str_replace('%link', coreLink($result['username'], $result['ID']), $text));

		$this->sendMail($jk->abuse_mail, $subject, __('New abuse report'), $text, false, true);
	}
}

?>