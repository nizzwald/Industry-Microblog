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

global $db;
global $_USER;

if ($_USER) {
	switch ($_GET['type']) {
	case 'note':
		$result = $db->getNoteCombined((int)$_GET['id']);
		if ($result) {
			if ($result['user_id'] != $_USER['ID']) {
				if ($_POST) {
					if (!$_POST['auth'] || ($_POST['auth'] != md5($_USER['salt']))) header('Location: '.coreLink(array('type=note', 'id='.$_GET['id']), 'mobile', 'report'));
					else {
						global $mailing;
						$mailing->reportAbuseNote($result, $_SERVER['REMOTE_ADDR']);
						header('Location: '.coreLink('mobile'));
					}
				}
				else {
					showMobileMenu();

					echo '<ul class="n"><li><strong>'.sprintf(__('Report the note #%s'), (int)$_GET['id']).'</strong></li></ul>';
					echo '<div style="padding-left:5px">'.__('Some reasons to report a note can be:').'<br><ul style="color:red"><li>'.__('SPAM').'</li><li>'.__('Offensive content').'</li><li>'.__('Duplicated account').'</li><li>'.__('Unlawful attached files').'</li><li>'.__('Illicit purposes').'</li></ul>'.__('You must be completely SURE that the user you are reporting is not following our Terms of Service. And remember this:').'<br><ul style="color:red"><li>'.__('You will remain in anonymity').'</li><li>'.__('When finishing this, you will not get any notification, but report has been sent').'</li><li>'.__('Fake or duplicated reports means the blocking of your account!').'</li></ul>'.__('If after reading this, you still want to report this account, go forward, we are grateful for your help!').'<br><br><form action="'.coreLink(array('type=note', 'id='.$_GET['id']). 'mobile', 'report').'" method="post"><input type="hidden" name="auth" value="'.md5($_USER['salt']).'"><input type="submit" value="'.__('Continue').'" class="b"></form></div>';
				}
			}
			else header('Location: '.coreLink('mobile'));
		}
		else header('Location: '.coreLink('mobile'));
		break;
	case 'user':
		$username = $db->getUsernameFromID((int)$_GET['uid']);
		$user_id = (int) $_GET['uid'];

		if ($username) {
			if ($user_id != $_USER['ID']) {
				if ($_POST) {
					if (!$_POST['auth'] || ($_POST['auth'] != md5($_USER['salt']))) header('Location: '.coreLink(array('type=user', 'uid='.$_GET['uid']), 'mobile', 'report'));
					else {
						global $mailing;
						$mailing->reportAbuse($user_id, $username, $_SERVER['REMOTE_ADDR']);
						header('Location: '.coreLink('mobile'));
					}
				}
				else {
					showMobileMenu();

					echo '<ul class="n"><li><strong>'.sprintf(__('Report the user %s'), $username).'</strong></li></ul>';
					echo '<div style="padding-left:5px">'.__('Some reasons to report an account can be:').'<br><ul style="color:red"><li>'.__('SPAM').'</li><li>'.__('Offensive content').'</li><li>'.__('Duplicated account').'</li><li>'.__('Unlawful attached files').'</li><li>'.__('Illicit purposes').'</li></ul>'.__('You must be completely SURE that the user you are reporting is not following our Terms of Service. And remember this:').'<br><ul style="color:red"><li>'.__('You will remain in anonymity').'</li><li>'.__('When finishing this, you will not get any notification, but report has been sent').'</li><li>'.__('Fake or duplicated reports means the blocking of your account!').'</li></ul>'.__('If after reading this, you still want to report this account, go forward, we are grateful for your help!').'<br><br><form action="'.coreLink(array('type=user', 'uid='.$_GET['uid']), 'mobile', 'report').'" method="post"><input type="hidden" name="auth" value="'.md5($_USER['salt']).'"><input type="submit" value="'.__('Continue').'" class="b"></form></div>';
				}
			}
			else header('Location: '.coreLink('mobile'));
		}
		else header('Location: '.coreLink('mobile'));
		break;
	default:
		header('Location: '.coreLink('mobile'));
	}
}
else header('Location: '.coreLink('mobile'));

?>