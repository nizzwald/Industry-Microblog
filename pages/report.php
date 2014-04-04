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

global $db, $sidebar;
global $_USER, $globals;
global $mailing, $result;
global $jk, $error;

if ($_USER) {
	if (PARAMS == 'note') {
		if (is_numeric(EXTRA)) {
			$result = $db->getNoteCombined(EXTRA);
			if ($result['user_id'] == $_USER['ID']) {
				$jk->error = 'You should not report yourself';
			}
		}

		if ($_POST) {
			if (!$result) $jk->error = 'The note does not exists';
			else {
				if (!$_POST['auth'] || ($_POST['auth'] != md5($_USER['salt']))) header('Location: '.coreLink('report', 'note', PARAMS));
				else {
					if (recaptcha_enabled()) {
						import('recaptchalib');
						$human = recaptcha_check_answer($jk->recaptcha_privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
						if (!$human->is_valid) $jk->error = 'Incorrect reCAPTCHA code!';
					}
					else $mailing->reportAbuseNote($result, $_SERVER['REMOTE_ADDR']);
				}
			}

			$jk->title = __('Report note');
			$jk->page_module = 'after_note';
			$sidebar = 'my_profile';
			$jk->load('functions');
			$jk->load('header');
			$jk->load('report');
			$jk->load('sidebar');
			$jk->load('footer');
		}
		else {
			if (!$result) $jk->error = 'The note does not exists';

			$jk->title = __('Report note');
			$jk->page_module = 'before_note';
			$jk->reporting_noteid = EXTRA;
			$sidebar = 'my_profile';
			$jk->load('functions');
			$jk->load('header');
			$jk->load('report');
			$jk->load('sidebar');
			$jk->load('footer');
		}
	}
	elseif (PARAMS == 'user') {
		if (is_numeric(EXTRA)) {
			$username = $db->getUsernameFromID(EXTRA);
			$user_id = (int) EXTRA;
			$check = (bool) $username;
		}
		else {
			$username = EXTRA;
			$user_id = $db->getIDFromUsername(EXTRA);
			$check = (bool) $user_id;
		}

		if ($user_id == $_USER['ID']) {
			$jk->error = 'You should not report yourself';
		}

		if ($_POST) {
			if (!$check) $jk->error = 'The user does not exists';
			else {
				if (!$_POST['auth'] || ($_POST['auth'] != md5($_USER['salt']))) header('Location: '.coreLink('report', 'note', PARAMS));
				else {
					if (recaptcha_enabled()) {
						import('recaptchalib');
						$human = recaptcha_check_answer($jk->recaptcha_privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
						if (!$human->is_valid) $jk->error = 'Incorrect reCAPTCHA code!';

					}
					else $mailing->reportAbuse($user_id, $username, $_SERVER['REMOTE_ADDR']);
				}
			}

			$jk->title = __('Report user');
			$jk->page_module = 'after_user';
			$jk->reporting_username = $username;
			$sidebar = 'my_profile';
			$jk->load('functions');
			$jk->load('header');
			$jk->load('report');
			$jk->load('sidebar');
			$jk->load('footer');
		}
		else {
			if (!$check) $jk->error = 'The user does not exists';

			$jk->title = __('Report user');
			$jk->page_module = 'before_user';
			$jk->reporting_username = $username;
			$sidebar = 'my_profile';
			$jk->load('functions');
			$jk->load('header');
			$jk->load('report');
			$jk->load('sidebar');
			$jk->load('footer');
		}
	}
}
else header('Location: '.$jk->base);

?>