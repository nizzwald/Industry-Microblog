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
if ($_POST) {
	set_time_limit(200);
    
	if (has_twitter() && ($_USER['twitter']['post_tweets'])) $twitter = true;
	else $twitter = false;

	if (isset($_POST['in_reply_to'])) $replying = (int) $_POST['in_reply_to'];

	if ($_POST['usemobile']) $post = postNote($_POST['note'], $_USER, 'mobile', $_POST['auth'], false, $replying, false, $twitter, $_POST['tip'], $_POST['bill']);
	else $post = postNote($_POST['note'], $_USER, 'web', $_POST['auth'], $_FILES, $replying, false, $twitter, $_POST['tip'], $_POST['bill']);

	if ($post) {
		switch ($post) {
		case 'BIG_FILE':
			$error = __('The uploaded file is too big');
			break;
		case 'FILE_NOT_ALLOWED':
			$error = __('The uploaded file is not allowed');
			break;
		case 'ERROR_UPLOAD':
			$error = __('There was a problem while trying to upload the attached file');
			break;
		case 'SHORT_NOTE':
			$error = __('The note is too short');
			break;
		case 'LONG_NOTE':
			$error = __('The note is too long');
			break;
		case 'INVALID':
			$error = __('You are not logged in correctly.');
			break;
		case 'INVALID_USER':
			$error = __('The user does not exist');
			break;
			/*case 'INVALID_GROUP':
				$error = __('Group doesn\'t exist');
				break;*/
		case 'NOT_FOLLOWING':
			$error = __('The user is not following you');
			break;
		case 'CANT_SEND_OWN_USER':
			$error = __("You can't send a private note to yourself");
			break;
		case 'COWBOY':
			$error = __('Cowboy!');
			break;
		}

		if (!$_POST['usemobile']) {
			if ($error) {
				global $jk, $sidebar;
				$sidebar = 'my_profile';
				$jk->load('functions');
				$jk->load('header');
				echo showStatus($error, 'error');
				$jk->load('sidebar');
				$jk->load('footer');

			} else {
				header('Location: '.$_SERVER['HTTP_REFERER']);
			}
		}
		else {
			if ($error) header('Location: '.coreLink(array('error='.urlencode($error)), 'mobile', 'notes'));
			else header('Location: '.coreLink('mobile'));
		}
	}
}

?>