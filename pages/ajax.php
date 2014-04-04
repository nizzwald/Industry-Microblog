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

if (isset($_GET['page'])) $start = getStart($_GET['page']); else $start = getStart(1);

if ($_USER['ignored']) $ignored = $_USER['ignored']; else $ignored = false;

switch (PARAMS) {
case 'twitter':
	echo json_encode(array());
	break;
case 'public':
case 'replies':
case 'friends':
case 'private':
case 'private_sent':
case 'archive':
case 'user':
case 'all':

	if ($_GET['since_id']) $since_id = (int) $_GET['since_id']; else $since_id = false;

	if (PARAMS == 'friends' || (PARAMS == 'all')) {
		$result = $db->getNotes('friends', $start, $jk->notes_per_page, $_USER['ID'], $ignored, $since_id);
	}
	elseif (PARAMS == 'replies') {
		$update = $db->updateUnreadReplies($_USER['ID']);
		$result = $db->getNotes(PARAMS, $start, $jk->notes_per_page, $_USER['ID'], $ignored, $since_id);
	}
	elseif (PARAMS == 'public') $result = $db->getNotes(PARAMS, $start, $jk->notes_per_page, false, $ignored, $since_id);
	elseif (PARAMS == 'user') {
		if ($_GET['user']) {
			$user_id = $db->getIDFromUsername($_GET['user']);
			$viewable = checkViewableUser($_USER['ID'], $user_id, 'show_notes');
			if ($viewable) $result = $db->getNotes('archive', $start, $jk->notes_per_page, $user_id, $ignored, $since_id);
			else $result = array();
		}
	}
	else $result = $db->getNotes(PARAMS, $start, $jk->notes_per_page, $_USER['ID'], false, $since_id);

	if (!$result) {
		if (!$since_id) echo json_encode(array('error' => 'No notes were found'));
		else echo json_encode(array());
	}
	else {
		$return = array();
		if ($_GET['touch']) import('mobile');
		foreach ($result as $note) {
			$returni = array();
			if (!$_GET['touch']) $returni['timestamp'] = $note['orderby'];
			$returni['id'] = $note['id'];
			$returni['type'] = $note['type'];

			if ($_GET['touch']) {
				$userID = $db->getPosterID($note['id']);
				if ($userID) {
					$returni['avatar'] = getAvatar($userID, 48);
				}
				$shownote = showNoteMobileTouch($note, true);
			}
			else $shownote = processNote($note, true);

			if ($shownote) {
				$returni = array_merge($returni, $shownote);
				$return[] = $returni;
			}
		}
		echo json_encode($return);
	}
	break;
case 'favorite':
	if ($_USER) {
		$noteID = (int) $_GET['id'];
		$return = array();
		if (!$noteID) {
			$return['error'] = __('The note does not exist');
		}
		else {
			$favorited = $db->checkFavorite($_USER['ID'], $noteID);
			if (!$favorited) {
				$db->newFavorite($_USER['ID'], $noteID);
				$return['favorited'] = true;
			} else {
				$db->deleteFavorite($_USER['ID'], $noteID);
				$return['favorited'] = false;
			}
		}
		echo json_encode($return);
	}
	break;
case 'logout':
	$status = closeSession($_USER['ID']);
	if ($status) echo json_encode(array('ok'=>'ok'));
	else echo json_encode(array('error'=> __('There was an error while trying to close your session')));
	break;
case 'post':
	if (!$_USER) die;
    if ($_POST) { 
        
    $tip = 0;
    $bill = 0;
        
    if(isset($_POST['tip'])) {
        $tip = $_POST['tip'];
    }
       
    if(isset($_POST['bill'])) {
        $bill = $_POST['bill'];
    }
       
		if (has_twitter()) {
			if ($_USER['twitter']['post_tweets'] == '1') $twitter = true;
			else $twitter = false;
		}
		else $twitter = false;

		if (is_numeric($_POST['in_reply_to'])) $in_reply_to = (int) $_POST['in_reply_to'];
		else $in_reply_to = false;

		if ($_POST['usemobile']) {
			if (!$_POST['note']) header('Location: '.coreLink('mobile'));
			$post = postNote($_POST['note'], $_USER, 'mobile', $_POST['auth'], false, $in_reply_to, false, $twitter, $tip, $bill);
		}
		else $post = postNote($_POST['note'], $_USER, 'web', $_POST['auth'], false, $in_reply_to, false, $twitter, $tip, $bill);
		if ($post) {
			switch ($post) {
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
				$error = __('The user doesn\'t exist');
				break;
				/*case 'INVALID_GROUP':
						$error = __('Group doesn\'t exist');
						break;*/
			case 'NOT_FOLLOWING':
				$error = __('The user isn\'t following you');
				break;
			case 'COWBOY':
				$error = __('Cowboy!');
				break;
			}
			
			if (!$error) echo json_encode(array('ok'=>'ok'));
			else echo json_encode(array('error'=>$error));
		}
	}
	break;
case 'note':
	if (is_numeric(EXTRA)) {
		$return = array();
		$return['type'] = 'note';
		$id = (int) EXTRA;
		$note = $db->getNoteCombined($id);

		if ($_GET['nav'] == 'link') {
			header('Location: '.coreLink($note['username'], $id));
			die;
		}
		else {
			$viewable = checkViewableUser($_USER['ID'], $note['user_id'], 'show_notes');
			if ($viewable) {
				if (!$note) $return['error'] = '';
				else {
					$return['text'] = utf8_htmlentities($note['note']);
					$return['text'] = preg_replace_callback('/\[(\*|\/|_|\-)(.+)\]/U',
						create_function('$matches', '
										switch ($matches[1]) {
											case "*":
												return "<b>" . $matches[2] . "</b>";
											case "/":
												return "<i>" . $matches[2] . "</i>";
											case "_":
												return "<u>" . $matches[2] . "</u>";
											case "-":
												return "<s>" . $matches[2] . "</s>";
										}
								'), $return['text']);
					$return['text'] = stripslashes(put_smileys($return['text']));
					//echo '<div style="background:transparent url('.$avatar.') no-repeat scroll left center; height:auto; padding:8px 0 0 30px;"><span class="tooltip_bold">'.__('In reply to:').'</span> '.stripslashes($text).'</div>';
				}
			}
			else $return['error'] = '';
			echo json_encode($return);
		}
	}
	else die('Note not found');
	break;
case 'profile':
	if (is_numeric(EXTRA)) {
		$profile = $db->getUserOptions(EXTRA, array('realname', 'profile', 'location', 'since'));
		$return = array();
		if ($profile) {
			$viewable = checkViewableUser($_USER['ID'], EXTRA, 'show_profile_info');
			if ($viewable) {
				if ($profile['realname']) $return['realname'] = utf8_htmlentities($profile['realname']);
				if ($profile['profile']['url']) $return['url'] = str_replace(array('http://', 'www.'), '', utf8_htmlentities($profile['profile']['url']));
				if ($profile['location']) $return['location'] = utf8_htmlentities($profile['locations']);
				if ($profile['profile']['bio']) $return['bio'] = utf8_htmlentities($profile['profile']['bio']);
				$return['since'] = date('d/m/Y', $profile['since']);
			}
			else $return['error'] = __("You are not allowed to see the profile of this user");
		}
		else $return['error'] = __("This user doesn't exists");
		echo json_encode(array_merge(array('type' => 'profile'), $return));
	}
	break;
case 'mainpage':
	$return = array();
	$privates = $db->countNotes('private', $_USER['ID']);
	$notes = $db->countNotes('archive', $_USER['ID']);
	if ($_GET['privates'] != $privates) $return['privates'] = $privates;
	if ($_GET['notes'] != $notes) $return['notes'] = $notes;
	echo json_encode($return);
	break;
case 'delete':
	if ($_GET['id']) {
		$check = $db->deleteNote($_GET['id'], $_USER['ID']);
		if (!$check) echo json_encode(array('error' => __("You are not allowed to erase others' notes")));
		else echo json_encode(array('ok' => __("The note was successfully deleted")));
	}
	else echo json_encode(array('error' => __("You must specify a note ID")));
	break;
case 'follow':
	if ($_USER) {
		if ($_POST['who']) {
			$who = (int) $_POST['who'];
			$follows = (bool) $db->checkFollowing($_USER['ID'], $who);
			if (!$follows) {
				$diff = time() - $_USER['last_follow'];
				if ($diff < $jk->wait_until_refollow) $json = array('error'=>__('Cowboy!'));
				else {
					$ignored = (bool) in_array($who, $_USER['ignored']);
					if (!$ignored) {
						$userInfo = $db->getUserOptions($who, array('username', 'notification_level', 'ignored'));
						if (!in_array($_USER['ID'], $userInfo['ignored'])) {
							if ($userInfo['notification_level'] == 1 || ($userInfo['notification_level'] >= 4)) {
								if ($_USER['realname']) $content = $_USER['realname'].' ('.$_USER['username'].')';
								else $content = $_USER['username'];
								$mailing->newFollower($who, $content);
							}
							$db->dumpRelationship($_USER['ID'], $who, false);
							$json = array('following'=>true);
						}
						else $json = array('error'=>__('The user you are trying to follow is ignoring you'));
					}
					else $json = array('error'=>__('You are ignoring the user'));
				}
			} else {
				$db->removeRelationship($_USER['ID'], $who);
				$json = array('following'=>false);
			}
		}
		else $json = array('error' => __('There was an error while trying to follow the user. Please retry'));
	}
	else $json = array('error' => __('You are not logged in'));

	echo json_encode($json);
	break;
case 'ignore':
	if (!$_USER || (!$_POST['who'])) die();
	$ignored = (bool) in_array($_POST['who'], $_USER['ignored']);
	if ($ignored) {
		$return = array_diff($_USER['ignored'], array($_POST['who']));
		$json = array('ignored' => false);
	}
	else {
		$db->removeRelationship($_USER['ID'], $_POST['who']);
		array_push($_USER['ignored'], $_POST['who']);
		$return = $_USER['ignored'];
		$json = array('ignored' => true);
	}
	$db->updateUserOptions($_USER['ID'], array('ignored' => serialize(array_unique($return))));
	echo json_encode($json);
	break;
case 'short_urls':
	if ($_USER) {
		if ($_GET['note']) {
			$note = urldecode($_GET['note']);

			$shorter_service = $_USER['shorter_service'];
			preg_match_all("#https?://[^.\s]+\.[^\s]+#ix", $note, $matches);

			foreach ($matches[0] as $uri) {
				if ($shorter_service['service'] == 'default') {
					if (!empty($jk->default_shorter_service)) $sh_service = $jk->default_shorter_service;
					else $sh_service = 'none';
				}
				else $sh_service = $shorter_service['service'];

				if ($sh_service != 'none') {
					if (strlen($uri) > 21) $note = str_replace($uri, shorter_url(array($uri), $sh_service), $note);
				}
			}

			echo json_encode(array('note' => $note));
		}
		else echo json_encode(array('error' => 'No note specified'));
	}
	else echo json_encode(array('error' => 'Not logged'));
}

?>