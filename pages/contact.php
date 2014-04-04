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

global $jk;

if ($_POST) {
	if (recaptcha_enabled()) import('recaptchalib');

	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) header('Location: '.coreLink(array('error=email'), 'contact'));
	else {
		if (countChars(trim($_POST['message'])) < 10) header('Location: '.coreLink(array('error=short'), 'contact'));
		else {
			$human = recaptcha_check_answer($jk->recaptcha_privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
			if (recaptcha_enabled() && !$human->is_valid) header('Location: '.coreLink(array('error=captcha'), 'contact'));
			else {
				global $mailing;
				global $_USER;

				$receiveCopy = (bool) $_POST['copy'];

				if ($_USER) $mailing->sendMessage($_POST['email'], $_POST['message'], $_SERVER['REMOTE_ADDR'], $_USER['username'], $receiveCopy);
				else $mailing->sendMessage($_POST['email'], $_POST['message'], $_SERVER['REMOTE_ADDR'], false, $receiveCopy);
				header('Location: '.coreLink(array('ok'), 'contact'));
			}
		}
	}
}
else {
	global $sidebar;
	global $_USER;

	$jk->title = __('Contact');
	if ($_USER) $sidebar = 'my_profile';
	else $sidebar = 'login';

	$jk->load('functions');
	$jk->load('header');

	if (isset($_GET['ok'])) echo showStatus(__('Your message was successfully sent to the administrator'), 'ok');
	else {
		if ($_GET['error']) {
			$error = $_GET['error'];

			switch ($error) {
				case 'email':
					$error = __('Invalid email');
					break;
				case 'short':
					$error = __('The provided message is too short');
					break;
				case 'recaptcha':
					$error = __('Incorrect reCAPTCHA code!');
					break;
			}

			if ($error) echo showStatus($error, 'error');
		}
	}

	$jk->load('contact');
	$jk->load('sidebar');
	$jk->load('footer');
}

?>