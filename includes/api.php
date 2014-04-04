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

ob_start();

$__microtime = microtime(true);

global $db;
global $_USER;
global $mailing;
global $jk;

$module = PARAMS;
$params = EXTRA;
$extra = EXTRA2;

/*
	Structure: http://myserver.com/$module[0].$module[1]/$params[0].$params[1]/$extra[0].$extra[1]

	$XXXXX[0] = the name , $XXXXX[1] = the extension
*/

$params = explode('.', $params);
$module = explode('.', $module);
$extra = explode('.', $extra);

// If we find a cookie, then we set $_USER
checkUserAPI();

// Because not all the applications can handle HTTP Errors, we allow them to overpass this limit
if (isset($_GET['suppress_response_codes'])) $no401 = false; else $no401 = true;

// Array containing the actions that an non-authentificated user can do
$public_actions = array('/api/statuses/public_timeline', '/api/statuses/user_timeline', '/api/statuses/show', '/api/statuses/friends', '/api/statuses/followers', '/api/help/test', '/api/users/show', '/api/friendships/exists', '/api/friendships/show', '/api/friends/ids', '/api/followers/ids');

if (!empty($params[0])) {
	if (!empty($extra[0])) {
		if (empty($extra[1])) $extension = 'basic';
		else $extension = $extra[1];
	}
	else {
		if (empty($params[1])) $extension = 'basic';
		else $extension = $params[1];
	}
}
else {
	if (empty($module[1])) $extension = 'basic';
	else $extension = $module[1];
}

$action = $_SERVER['REQUEST_URI'];

//We split $action in 2 strings, the action and the extension
$explode = explode('.', $action);

if ($_SERVER['PHP_AUTH_USER'] && $_SERVER['PHP_AUTH_PW']) {
	$userInfo = $db->getFromCookie($_COOKIE['jisko_'.md5($jk->base)]);
	if ($userInfo === false) {
		if (filter_var($_SERVER['PHP_AUTH_USER'], FILTER_VALIDATE_EMAIL)) $_USER = $db->getUserInfoAPI(false, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		else $_USER = $db->getUserInfoAPI($_SERVER['PHP_AUTH_USER'], false, $_SERVER['PHP_AUTH_PW']);
	}
	else $_USER = $userInfo;
}

if (!in_array(strstr($explode[0], '/api/'), $public_actions)) {
	if ($_USER) {
		//Because the user is banned, it shouldn't have access to the API
		if ($_USER['status'] == 'banned') die(apiError($extension, $action, 'User not allowed.', $no401));
		else extract($_USER);
	}
	else header('WWW-Authenticate: Basic realm="'.$jk->name.'"');
	
	//The user is not logged, so we return a 401 Error.
	if (!$_USER) {
		die(apiError($extension, $action, 'Could not authenticate you.', $no401));
	}
	else {
		$db->updateUserOptions($_USER['ID'], array('last_seen' => time()));
	}
}

//Provide a ?since_id parameter to load notes since that ID
if (is_numeric($_GET['since_id'])) $since_id = (int) $_GET['since_id']; else $since_id = false;
//Provide a ?since_timestamp parameter to load notes since that timestamp
if (is_numeric($_GET['since_timestamp'])) $since_timestamp = (int) $_GET['since_timestamp']; else $since_timestamp = false;
//Provide a ?max_id parameter to load notes to that maximum ID
if (is_numeric($_GET['max_id'])) $max_id = (int) $_GET['max_id']; else $max_id = false;

//So we load the $start variable, for the pages.
if (!$_GET['page']) $start = getStart(1); else $start = getStart($_GET['page']);

// If an application wants more than 25 notes, we allow it to load more than 25 notes.
if (isset($_GET['count']) && (is_numeric($_GET['count']))) {
	if ($_GET['count'] <= 200) $count = (int) $_GET['count'];
	else $count = (int) $jk->notes_per_page;
}
else $count = (int) $jk->notes_per_page;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if ($extra[0]) {
		if (is_numeric($extra[0])) $user_id = (int) $extra[0];
		else $username = $extra[0];
	}
	else {
		if ($_GET['user_id']) $user_id = (int) $_GET['user_id'];
		elseif ($_GET['screen_name']) $username = $_GET['screen_name'];
		elseif ($_GET['id']) {
			if (is_numeric($_GET['id'])) $user_id = (int) $_GET['id'];
			else $username = $_GET['id'];
		}
		else $user_id = (int) $_USER['ID'];
	}
}
else {
	if ($extra[0]) {
		if (is_numeric($extra[0])) $user_id = (int) $extra[0];
		else $username = $extra[0];
	}
	else {
		if ($_POST['user_id']) $user_id = (int) $_POST['user_id'];
		elseif ($_POST['screen_name']) $username = $_POST['screen_name'];
		elseif ($_POST['id']) {
			if (is_numeric($_POST['id'])) $user_id = (int) $_POST['id'];
			else $username = $_POST['id'];
		}
		elseif ($_GET['user_id']) $user_id = (int) $_GET['user_id'];
		elseif ($_GET['screen_name']) $username = $_GET['screen_name'];
		elseif ($_GET['id']) {
			if (is_numeric($_GET['id'])) $user_id = (int) $_GET['id'];
			else $username = $_GET['id'];
		}
		else $user_id = (int) $_USER['ID'];
	}
}

//Here starts the real API code
if ($module[0] == 'statuses') {
	switch ($params[0]) {
	case 'retweeted_by_me':
	case 'retweeted_to_me':
	case 'retweets_to_me':
		switch ($params[1]) {
		case 'xml':
			header('Content-Type: application/xml; charset=utf-8');
			echo '<?xml version="1.0" encoding="UTF-8"?><nilclasses type="array"></nilclasses>';
			break;
		case 'json':
			header('Content-Type: text/javascript; charset=utf-8');
			echo '[]';
			break;
		}
		die();
		break;
	case 'public_timeline':
		$result = $db->getNotes('public', $start, $count, false, $_USER['ignored'], $since_id, $since_timestamp, $max_id);
		$rss = __('Public notes from all the users');
	case 'friends_timeline':
	case 'home_timeline':
		if (!isset($result)) {
			$result = $db->getNotes('friends', $start, $count, $_USER['ID'], $_USER['ignored'], $since_id, $since_timestamp, $max_id);
			$rss = sprintf(__("Notes of %s + it's followings"), $_USER['username']);
		}
	case 'user_timeline':
		if (!isset($result)) {
			if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
			else {
				if ($user_id != (int)$_USER['ID'] || ($username != $_USER['username'])) {
					if (!$user_id) $user_id = $db->getIDFromUsername($username);
					if (!$username) $username = $db->getUsernameFromID($user_id);
					$check = $db->checkUserID($user_id);
				}
				else {
					$user_id = $_USER['ID'];
					$username = $_USER['username'];
					$check = true;
				}

				if (!$check) die(apiError($extension, $action, 'Not found'));
				else {
					if ($user_id == $_USER['ID']) $viewable = true;
					else {
						$viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_notes');
					}

					if ($viewable) {
						$result = $db->getNotes('archive', $start, $count, $user_id, false, $since_id, $since_timestamp, $max_id);
						if ($username) $rss = sprintf(__('Notes of %s'), $username);
						else $rss = sprintf(__('Notes of %s'), $db->getUsernameFromID($user_id));
					}
					else die(apiError($extension, $action, 'Not authorized'));
				}
			}
		}
	case 'mixed_timeline':
		if (!isset($result)) {
			$result = $db->getNotes('all', $start, $count, $_USER['ID'], false, $since_id, $since_timestamp, $max_id);
		}
	case 'twitter':
		if (!isset($result)) {
			if (has_twitter()) $result = $db->getNotes('twitter', $start, $count, $_USER['ID'], false, $since_id, $since_timestamp, $max_id);
			else die(apiError($extension, $action, 'There is no twitter account for this user.'));
			$rss = sprintf(__('Twitter followings of %s'), $_USER['username']);
		}
	case 'replies':
	case 'mentions':
		if (!isset($result)) {
			$result = $db->getNotes('mentions', $start, $count, $_USER['ID'], $_USER['ignored'], $since_id, $since_timestamp, $max_id);
			$rss = sprintf(__('Replies for %s'), $_USER['username']);
		}

		if (count($result) > 0) header('Last-modified: '.gmdate('D, d M Y H:i:s \G\M\T', $result[0]['orderby']));
		else header('Last-modified: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
		header('X-Ratelimit-Limit: 150');
		header('X-Revision: DEV');
		header('X-RateLimit-Reset: 1577836800');

		$odd = microtime(true) - $__microtime;
		header('X-Runtime: '.round($odd, 5));
		header('Last-modified: '.gmdate('D, d M Y H:i:s \G\M\T', time()+60));

		switch ($extension) {
		case 'xml':
			if ($params[0] == 'twitter') createXML('twitter', $result);
			elseif ($params[0] == 'mixed_timeline') createXML('notes+t', $result);
			else createXML('notes', $result);
			break;
		case 'json':
			if ($params[0] == 'twitter') createJSON('twitter', $result, $_GET['callback']);
			elseif ($params[0] == 'mixed_timeline') createJSON('notes+t', $result, $_GET['callback']);
			else createJSON('notes', $result, $_GET['callback']);
			break;
		case 'rss':
			if ($rss) createRSS($result, $rss, $rss, $module[0].'/'.$params[0].'.rss');
			break;
		}
		break;
	case 'followers':
	case 'friends':
		if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
		else {
			if ($user_id != (int)$_USER['ID'] || ($username != $_USER['username'])) {
				if (!$user_id) $user_id = $db->getIDFromUsername($username);
				if (!$username) $username = $db->getUsernameFromID($user_id);
				$check = $db->checkUserID($user_id);
			}
			else {
				$user_id = $_USER['ID'];
				$username = $_USER['username'];
				$check = true;
			}

			if (!$check) die(apiError($extension, $action, 'Not found'));
			else {
				if ($user_id == $_USER['ID']) $viewable = true;
				else {
					$viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_followers');
				}

				if ($viewable) {
					if ($params[0] == 'followers') $result = $db->getFollowersID($user_id, $start, $count);
					elseif ($params[0] == 'friends') $result = $db->getFollowingID($user_id, $start, $count);

					switch ($extension) {
					case 'xml':
						createXML('users', $result);
						break;
					case 'json':
						createJSON('users', $result, $_GET['callback']);
						break;
					}
				}
				else die(apiError($extension, $action, 'Not authorized'));
			}
		}
		break;
	}
	if ($params[0] == 'show') {
		if (!$extra[0]) die(apiError($extension, $action, 'No status found with that ID.', $no401));
		else {
			$result = $db->getNoteCombined($extra[0]);
			if (!$result || ($result['type'] == 'private')) die(apiError($extension, $action, 'No status found with that ID.', $no401));
			else {
				if ($result['user_id'] == $_USER['ID']) $viewable = true;
				else {
					$viewable = checkViewableUser($_USER['ID'], $result['user_id'], 'show_notes');
				}
				if ($viewable) {
					switch ($extra[1]) {
					case 'xml':
						createXML('notess', array('id'=>$extra[0]));
						break;
					case 'json':
						createJSON('notess', array('id'=>$extra[0]), $_GET['callback']);
					}
				}
				else die(apiError($extension, $action, 'Not authorized'));
			}
		}
	} elseif ($params[0] == 'update') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') die(apiError($extension, $action, 'This method requires a POST.', $no401));
		else {
			if ($_GET['status']) $status = $_GET['status'];
			else $status = $_POST['status'];
			
			if (!$status) die(apiError($extension, $action, "Client must provide a 'status' parameter with a value.", $no401));
			else {
				if ((time() - $_USER['last_note']) < $jk->wait_until_repost) die(apiError($extension, $action, 'Relax...', $no401));

				if (countChars($status) < 2 || (countChars($status) > 140)) die(apiError($extension, $action, 'The note needs to be between 2 and 140 characters'));
				else {
					global $skipauth;
					$skipauth = 1;
					if ($_GET['source']) $source = $_GET['source'];
					else $source = $_POST['source'];
					if (empty($source)) $source = 'api'; else $source = $source;

					if (has_twitter() && $_USER['twitter']['post_tweets']) $twitter = true;
					else $twitter = false;

					$result = postNote($status, $_USER, $source, false, false, $_POST['in_reply_to_status_id'], true, $twitter);
					switch ($params[1]) {
					case 'xml':
						createXML('notess', array('id'=>$result));
						break;
					case 'json':
						createJSON('notess', array('id'=>$result), $_GET['callback']);
					}
				}
			}
		}

	} elseif ($params[0] == 'destroy') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST' && ($_SERVER['REQUEST_METHOD'] != 'DELETE')) die(apiError($extension, $action, 'This method requires a POST or DELETE.'));
		else {
			if (!$extra[0]) die(apiError($extension, $action, 'No status found with that ID.'));
			else {
				$result = $db->getNoteCombined($extra[0]);
				if (!$result) die(apiError($extension, $action, 'No status found with that ID.'));
				else {
					if ($result['user_id'] != $_USER['ID']) die(apiError($extension, $action, "You may not delete another user's status.", $no401));

					switch ($extra[1]) {
					case 'xml':
						createXML('notess', array('id'=>$extra[0]));
						break;
					case 'json':
						createJSON('notess', array('id'=>$extra[0]));
					}

					$result = $db->deleteNote($extra[0], $_USER['ID']);
				}
			}
		}
	}

} elseif ($module[0] == 'direct_messages') {
	if (!$params[0] || ($params[0] == 'sent')) {
		switch ($params[0]) {
		case 'sent':
			$result = $db->getNotes('private_sent', $start, $count, $_USER['ID'], false, $since_id, $since_timestamp);
			$rss = array(sprintf(__('Private notes sent by %s'), $_USER['username']), 'direct_messages/sent.rss');
		default:
			if (!isset($result)) $result = $db->getNotes('private', $start, $count, $_USER['ID'], false, $since_id, $since_timestamp);
			if (!isset($rss)) $rss = array(sprintf(__('Private notes of %s'), $_USER['username']), 'direct_messages.rss');

			switch ($extension) {
			case 'xml':
				createXML('direct_messages', $result);
				break;
			case 'json':
				createJSON('direct_messages', $result, $_GET['callback']);
				break;
			case 'rss':
				createRSS($result, $rss[0], $rss[0], $rss[1]);
			}
		}
	}
	else {
		if ($params[0] == 'new') {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') die(apiError($extension, $action, 'This method requires a POST.'));
			else {
				if (!$_POST['text'] || (!$_POST['user'])) die(apiError($extension, $action, 'Invalid request.'));
				else {
					if (!is_numeric($_POST['user'])) $userID = $db->getIDFromUsername($_POST['user']);
					else $userID = $db->checkUserID($_POST['user']);

					$userInfo = $db->getUserOptions($userID, array('notification_level', 'language', 'email'));

					if (!$userInfo) die(apiError($extension, $action, 'Not found.'));
					else {
						if ($_USER['ID'] != $userID) $check = $db->checkFollowing($userID, $_USER['ID']);
						else $check = true;

						if ($check) {
							if (countChars(utf8_decode(trim($_POST['text']))) > 140) die(apiError($extension, $action, 'The note is longer than 140 characters.'));
							else {
								if ($_GET['source']) $source = $_GET['source'];
								else $source = $_POST['source'];

								if (empty($source)) $source = 'api'; else $source = $source;

								$newPrivateNote = $db->newPrivateNote($_USER['ID'], $userID, $_POST['text'], false, $source, $_SERVER['REMOTE_ADDR']);
								if (!$newPrivateNote) die(apiError($extension, $action, 'There was an error while trying to send the note.'));
								else {
									if ($userInfo['notification_level'] >= 2) {
										$note = preg_replace('/(\s|\A)(!){1}(\w+)(\s|^)/', '', $_POST['text']);
										$mailing->newPrivateNote($userInfo, utf8_htmlentities($note), $_USER);
									}

									switch ($params[1]) {
									case 'xml':
										createXML('direct_message', array(array('id'=>$newPrivateNote)));
										break;
									case 'json':
										createJSON('direct_message', array(array('id'=>$newPrivateNote)), $_GET['callback']);
									}
								}
							}
						}
						else die(apiError($extension, $action, 'You cannot send messages to users who are not following you.'));
					}
				}
			}
		} elseif ($params[0] == 'destroy') {
			if ($_SERVER['REQUEST_METHOD'] != 'POST' && ($_SERVER['REQUEST_METHOD'] != 'DELETE')) die(apiError($extension, $action, 'This method requires a POST or a DELETE.'));
			else {
				if (!$extra[0]) die(apiError($extension, $action, 'No direct message with that ID found.'));
				else {
					$result = $db->getNoteCombined($extra[0]);
					if (!$result) die(apiError($extension, $action, 'No direct message with that ID found.'));
					else {
						if ($result['type'] != 'private') die(apiError($extension, $action, 'No direct message with that ID found.'));
						else {
							if ($result['user_id'] != $_USER['ID']) die(apiError($extension, $action, "You may only delete direct messages you've sent or received."));
							else {
								switch ($extension) {
								case 'xml':
									createXML('direct_message', array(array('id'=>$extra[0])));
									break;
								case 'json':
									createJSON('direct_message', array(array('id'=>$extra[0])));
								}

								$db->deleteNote($extra[0], $_USER['ID']);
							}
						}
					}
				}
			}

		} elseif ($params[0] == 'show') {
			if (!$extra[0]) die(apiError($extension, $action, 'Not found.', $no401));
			else {
				$result = $db->getNoteCombined($extra[0]);
				if (!$result) die(apiError($extension, $action, 'Not found.', $no401));
				else {
					if ($result['user_id'] != $_USER['ID']) die(apiError($extension, $action, 'Not authorized'));
					else {
						switch ($extra[1]) {
						case 'xml':
							createXML('direct_message', array(array('id'=>$extra[0])));
							break;
						case 'json':
							createJSON('direct_message', array(array('id'=>$extra[0])), $_GET['callback']);
						}
					}
				}
			}
		}
	}

} elseif ($module[0] == 'friendships') {
	if ($params[0] == 'create') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') die(apiError($extension, $action, 'This method requires a POST.'));
		else {
			if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
			else {
				if ($user_id != $_USER['ID'] || ($username != $_USER['username'])) {
					$user_id = $db->getIDFromUsername($username);
					$check = $db->checkUserID($user_id);
				}
				else {
					$user_id = $_USER['ID'];
					$username = $_USER['username'];
					$check = true;
				}
				if ($user_id != $_USER['ID']) {
					if ($check) {
						$following = $db->checkFollowing($_USER['ID'], $user_id);
						if (!$following) {
							$userInfo = $db->getUserOptions($user_id, array('email', 'notification_level', 'ignored'));

							$viewable = checkViewableUser($_USER['ID'], $user_id, 'show_notes');

							if ($viewable === true) {
								$diff = time() - $_USER['last_follow'];
								if ($diff > $jk->wait_until_refollow) {
									if (!in_array($_USER['ID'], $userInfo['ignored'])) {
										if ($userInfo['notification_level'] == 1 || ($userInfo['notification_level'] >= 4)) {
											if ($_USER['realname']) $content = $_USER['realname'].' ('.$_USER['username'].')';
											else $content = $_USER['username'];

											$mailing->newFollower($user_id, $content);
											$db->dumpRelationship($_USER['ID'], $user_id, false);

											switch ($extension) {
											case 'xml':
												createXML('user', array($user_id));
												break;
											case 'json':
												createJSON('user', array($user_id), $_GET['callback']);
											}
										}
									}
									else die(apiError($extension, $action, "Could not follow user: You have been blocked from following this account at the request of the user."));
								}
								else die(apiError($extension, $action, "Could not follow user: Please wait ".(int)($jk->wait_until_refollow - $diff)." seconds until your next following"));
							}
							else die(apiError($extension, $action, "Could not follow user: You've already requested to follow $username"));
						}
						else die(apiError($extension, $action, "Could not follow user: $username is already on your list."));
					}
					else die(apiError($extension, $action, 'Not found', $no401));
				}
				else die(apiError($extension, $action, "Could not follow user: You can't follow yourself!", $no401));
			}
		}
	} elseif ($params[0] == 'destroy') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST' && ($_SERVER['REQUEST_METHOD'] != 'DELETE')) die(apiError($extension, $action, 'This method requires a POST or a DELETE.'));
		else {
			if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
			else {
				if ($user_id != $_USER['ID'] || ($username != $_USER['username'])) {
					$user_id = $db->getIDFromUsername($username);
					$check = $db->checkUserID($user_id);
				}
				else {
					$user_id = $_USER['ID'];
					$username = $_USER['username'];
					$check = true;
				}

				if ($check) {
					$check = $db->checkFollowing($_USER['ID'], $user_id);
					if ($check) {
						$db->removeRelationship($_USER['ID'], $user_id);

						switch ($extension) {
						case 'xml':
							createXML('user', array($user_id));
							break;
						case 'json':
							createJSON('user', array($user_id), $_GET['callback']);
						}
					}
					else die(apiError($extension, $action, 'You are not friends with the specified user.', $no401));
				}
				else die(apiError($extension, $action, 'Not found', $no401));
			}
		}
	} elseif ($params[0] == 'exists') {
		if ($_SERVER['REQUEST_METHOD'] != 'GET') die(apiError($extension, $action, 'This method requires a GET.'));
		else {
			$user_a = $_GET['user_a'];
			$user_b = $_GET['user_b'];

			if (!$user_a || (!$user_b)) die(apiError($extension, $action, 'Two user ids or screen_names must be supplied.'));
			else {
				if (is_numeric($user_a)) $id_a = $db->checkUserID($user_a);
				else $id_a = $db->getIDFromUsername($user_a);

				if (is_numeric($user_b)) $id_b = $db->checkUserID($user_b);
				else $id_b = $db->getIDFromUsername($user_b);

				if (!$id_a || (!$id_b)) die(apiError($extension, $action, 'Could not find both specified users.'));
				else {
					if ($id_a == $_USER['ID']) $viewable_a = true;
					else {
						$viewable_a = checkViewableUser($id_a, $id_b, 'show_notes');
					}
					if ($id_b == $_USER['ID']) $viewable_b = true;
					else {
						$viewable_b = checkViewableUser($id_b, $id_a, 'show_notes');
					}

					if ($viewable_a && $viewable_b) {
						$following = ($db->checkFollowing($id_a, $id_b) ? 'true' : 'false');

						switch ($extension) {
						case 'xml':
							echo "<friends>$following</friends>";
							break;
						case 'json':
							echo 'true';
						}
					}
					else die(apiError($extension, $action, 'Not authorized'));
				}
			}
		}
	} elseif ($params[0] == 'show') {
		$target_id = (int) $_GET['target_id'];
		$target_username = $_GET['target_screen_name'];

		if (!$target_id && !$target_username) die(apiError($extension, $action, 'Target user not specified.'));
		else {
			if ($target_id) $id = $db->checkUserID($target_id);
			else $id = $db->getIDFromUsername($target_username);

			if (!$id) die(apiError($extension, $action, 'Not found.'));
			else {
				if ($id === true) $id = $target_id;
				if ($id == $_USER['ID']) $viewable = true;
				else {
					$viewable = checkViewableUser($_USER['ID'], $id, 'show_notes');
				}

				if ($viewable) {
					switch ($extension) {
					case 'xml':
						createXML('relationship', array(array($_USER['ID'], $id)));
						break;
					case 'json':
						createJSON('relationship', array(array($_USER['ID'], $id)), $_GET['callback']);
						break;
					}
				}
				else die(apiError($extension, $action, 'Not authorized'));
			}
		}
	}
} elseif ($module[0] == 'users') {
	if ($params[0] == 'show') {
		if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
		else {
			if ($user_id != $_USER['ID'] || ($username != $_USER['username'])) {
				$user_id = $db->getIDFromUsername($username);
				$check = $db->checkUserID($user_id);
			}
			else {
				$user_id = $_USER['ID'];
				$check = true;
			}

			if (!$check) die(apiError($extension, $action, 'Not found'));
			else {
				if ($result['user_id'] == $_USER['ID']) $viewable = true;
				else {
					$viewable = checkViewableUser($_USER['ID'], $result['user_id'], 'show_profile_info');
				}

				if ($viewable) {
					switch ($extension) {
					case 'xml':
						createXML('user', array('id'=>$user_id));
						break;
					case 'json':
						createJSON('user', array($user_id), $_GET['callback']);
					}
				}
				else die(apiError($extension, $action, 'Not authorized'));
			}
		}
	} elseif ($params[0] == 'search') {
		$q = $_GET['q'];
		if ($q) {
			if ($_GET['per_page']) $per_page = (int) $_GET['per_page'];
			else $per_page = (int) $jk->notes_per_page;

			if (filter_var($q, FILTER_VALIDATE_EMAIL)) $result = $db->searchUser($q, getStart($page, $per_page), $per_page, true);
			else $result = $db->searchUser($q, getStart($page, $per_page), $per_page);

			switch ($params[1]) {
			case 'xml':
				createXML('users', $result);
				break;
			case 'json':
				createJSON('users', $result, $_GET['callback']);
			}
		}
		else die(apiError($extension, $action, "Client must provide a 'q' parameter with a value."));
	}
} elseif ($module[0] == 'blocks') {
	if ($params[0] == 'create') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') apiError($extension, $action, 'This method requires a POST.', $no401);
		else {
			if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
			else {
				if ($user_id != $_USER['ID'] || ($username != $_USER['username'])) {
					$user_id = $db->getIDFromUsername($username);
					$check = $db->checkUserID($user_id);
				}
				else {
					$user_id = $_USER['ID'];
					$check = true;
				}

				if (!$check) die(apiError($extension, $action, 'Not found'));
				else {
					if ($_USER['ID'] != $user_id) {
						$ignored = (bool) in_array($user_id, $_USER['ignored']);
						if (!$ignored) {
							$db->removeRelationship($_USER['ID'], $user_id);
							array_push($_USER['ignored'], $user_id);
							$db->updateUserOptions($_USER['ID'], array('ignored' => serialize(array_unique($_USER['ignored']))));
						}

						switch ($extension) {
						case 'xml':
							createXML('user', array($user_id));
							break;
						case 'json':
							createJSON('user', array($user_id), $_GET['callback']);
						}
					}
					else die(apiError($extension, $action, "Sorry, you can't block yourself!"));
				}
			}
		}
	} elseif ($params[0] == 'exists') {
		if ($_SERVER['REQUEST_METHOD'] != 'GET') apiError($extension, $action, 'This method requires a GET.', $no401);
		else {
			if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
			else {
				if ($user_id != $_USER['ID'] || ($username != $_USER['username'])) {
					$user_id = $db->getIDFromUsername($username);
					$check = $db->checkUserID($user_id);
				}
				else {
					$user_id = $_USER['ID'];
					$check = true;
				}

				if (!$check) die(apiError($extension, $action, 'Not found'));
				else {
					$ignored = (bool) in_array($user_id, $_USER['ignored']);
					if (!$ignored) die(apiError($extension, $action, 'You are not blocking this user.'));
					else {
						switch ($extension) {
						case 'xml':
							createXML('user', array($user_id));
							break;
						case 'json':
							createJSON('user', array($user_id), $_GET['callback']);
						}
					}
				}
			}
		}
	} elseif ($params[0] == 'blocking') {
		if (!$extra[0]) {
			$output = array_slice($_USER['ignored'], ($page * 20), 20);
			switch ($params[1]) {
			case 'xml':
				createXML('users', $output);
				break;
			case 'json':
				createJSON('users', $output, $_GET['callback']);
			}
		}
		elseif ($extra[0] == 'ids') {
			switch ($extra[1]) {
			case 'xml':
				createXML('ids', $_USER['ignored']);
				break;
			case 'json':
				createJSON('ids', $_USER['ignored'], $_GET['callback']);
			}
		}
	} elseif ($params[0] == 'destroy') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST' && ($_SERVER['REQUEST_METHOD'] != 'DELETE')) die(apiError($extension, $action, 'This method requires a POST or DELETE.', $no401));
		else {
			if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
			else {
				if ($user_id != $_USER['ID'] || ($username != $_USER['username'])) {
					$user_id = $db->getIDFromUsername($username);
					$check = $db->checkUserID($user_id);
				}
				else {
					$user_id = $_USER['ID'];
					$check = true;
				}

				if (!$check) die(apiError($extension, $action, 'Not found'));
				else {
					$ignored = (bool) in_array($user_id, $_USER['ignored']);
					if ($ignored) {
						$return = array_diff($_USER['ignored'], $user_id);
						$db->updateUserOptions($_USER['ID'], array('ignored' => serialize(array_unique($return))));
					}

					switch ($extension) {
					case 'xml':
						createXML('user', array($user_id));
						break;
					case 'json':
						createJSON('user', array($user_id), $_GET['callback']);
					}
				}
			}
		}
	}
} elseif ($module[0] == 'favorites') {
	if ($extra[0] == 'create') $note = $params[0];
	elseif ($params[0] == 'create') $note = $extra[0];
	
	if ($note) {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') apiError($extension, $action, 'This method requires a POST.', $no401);
		else {
			if ($note) {
				$result = $db->getNoteCombined($note);
	
				if (!$result) apiError($extension, $action, 'Not found', $no401);
				else {
					$favorited = $db->checkFavorite($_USER['ID'], $note);
					if (!$favorited) {
						$db->newFavorite($_USER['ID'], $note);
	
						switch ($extra[1]) {
						case 'xml':
							createXML('notess', array('id'=>$note));
							break;
						case 'json':
							createJSON('notess', array('id'=>$note), $_GET['callback']);
						}
					}
					else {
						apiError($extension, $action, 'You have already favorited this status.', $no401, $no401);
					}
				}
			}
		}

	} elseif ($params[0] == 'destroy') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST' && ($_SERVER['REQUEST_METHOD'] != 'DELETE')) die(apiError($extension, $action, 'This method requires a POST or DELETE.', $no401));
		else {
			if ($_GET['id']) $note = (int)$_GET['id'];
			else $note = $extra[0];
			
			if (!$note) die(apiError($extension, $action, 'This method requires a GET.'));
			else {
				$result = $db->getNoteCombined($note);

				if (!$result) die(apiError($extension, $action, 'Not found', $no401));
				else {
					$favorited = $db->checkFavorite($_USER['ID'], $note);
					if ($favorited) $db->deleteFavorite($_USER['ID'], $note);

					switch ($extension) {
					case 'xml':
						createXML('notess', array('id'=>$note));
						break;
					case 'json':
						createJSON('notess', array('id'=>$note), $_GET['callback']);
					}
				}
			}
		}
	} else {
		if (!$params[0]) $result = $db->getNotes('favorites', $start, $count, $_USER['ID']);
		else {
			if (!is_numeric($params[0])) $params[0] = $db->getIDFromUsername($params[0]);
			$result = $db->getNotes('favorites', $start, $count, $params[0]);
		}

		switch ($extension) {
		case 'xml':
			createXML('notes', $result);
			break;
		case 'json':
			createJSON('notes', $result, $_GET['callback']);
			break;
		case 'rss':
			if (!$params[0]) $title = __('Favorite notes of ').$_USER['username'];
			else $title = __('Favorite notes of ').$params[0];

			createRSS($result, $title, $title, substr($action, 1));
		}
	}

} elseif ($module[0] == 'followers' || ($module[0] == 'friends')) {
	if ($params[0] == 'ids') {
		if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
		else {
			if ($user_id && ($user_id != $_USER['ID'])) {
				$check = $db->checkUserID($user_id);
			}
			elseif ($username && ($username != $_USER['username'])) {
				$user_id = $db->getIDFromUsername($username);
				$check = (bool)$user_id;
			}
			else {
				$user_id = $_USER['ID'];
				$check = true;
			}

			if (!$check) die(apiError($extension, $action, 'Not found'));
			else {
				if ($result['user_id'] == $_USER['ID']) $viewable = true;
				else {
					if ($module[0] == 'followers') {
						$viewable = checkViewableUser($_USER['ID'], $result['user_id'], 'show_followers');
					}
					elseif ($module[0] == 'friends') {
						$viewable = checkViewableUser($_USER['ID'], $result['user_id'], 'show_followings');
					}
				}

				if ($viewable) {
					if ($module[0] == 'followers') $result = $db->getFollowers($_USER['ID'], $start, $count);
					else $result = $db->getFollowing($_USER['ID'], $start, $count);

					switch ($params[1]) {
					case 'xml':
						createXML('ids', $result);
						break;
					case 'json':
						createJSON('ids', $result, $_GET['callback']);
					}
				}
				else die(apiError($extension, $action, 'Not authorized'));
			}
		}
	}
} elseif ($module[0] == 'report_spam') {
	if ($params[0]) {
		if (is_numeric($extra[0])) $user_id = (int) $params[0];
		else $username = $params[0];
	}

	if (!$user_id && !$username) die(apiError($extension, $action, 'Not found', $no401));
	else {
		if ($user_id != $_USER['ID'] || ($username != $_USER['username'])) {
			$user_id = $db->getIDFromUsername($username);
			$check = $db->checkUserID($user_id);
		}
		else {
			$user_id = $_USER['ID'];
			$check = true;
		}

		if (!$check) die(apiError($extension, $action, 'Not found'));
		else {
			global $mailing;
			$mailing->reportAbuseNote($user_id);
			switch ($extension) {
			case 'xml':
				createXML('user', array($user_id));
				break;
			case 'json':
				createJSON('user', array($user_id), $_GET['callback']);
			}
		}
	}
} elseif ($module[0] == 'account') {
	if ($params[0] == 'verify_credentials') {
		switch ($params[1]) {
		case 'xml':
			createXML('user', array($_USER['ID']));
			break;
		case 'json':
			createJSON('user', array($_USER['ID']), $_GET['callback']);
			break;
		default:
			echo 'Authorized';
		}

	} elseif ($params[0] == 'rate_limit_status') {
		if ($params[1] == 'xml') {
			header('HTTP/1.0 200 OK');
			header('Content-Type: text/xml charset=UTF-8');

			$XMLWriter = new XMLWriter();
			$XMLWriter->openURI('php://output');
			$XMLWriter->startDocument('1.0', 'UTF-8');

			$XMLWriter->startElement('hash');
			$XMLWriter->startElement('reset-time');
			$XMLWriter->writeAttribute('type', 'datetime');
			$XMLWriter->text('2020-01-01T00:00:00+00:00');
			$XMLWriter->endElement();
			$XMLWriter->startElement('remaining-hits');
			$XMLWriter->writeAttribute('type', 'integer');
			$XMLWriter->text('10000');
			$XMLWriter->endElement();
			$XMLWriter->startElement('hourly-limit');
			$XMLWriter->writeAttribute('type', 'integer');
			$XMLWriter->text('10000');
			$XMLWriter->endElement();
			$XMLWriter->startElement('reset-time-in-seconds');
			$XMLWriter->writeAttribute('type', 'integer');
			$XMLWriter->text('1577836800');
			$XMLWriter->endElement();
			$XMLWriter->endElement();
			$XMLWriter->flush();

		} elseif ($params[1] == 'json') {
			header('Content-Type: text/javascript; charset=utf-8');

			echo json_encode(array(
					'hourly_limit' => 10000,
					'reset_time_in_seconds' => 1577836800,
					'reset_time' => 'Mon Jan 1 00:00:00 +0000 2012',
					'remaining_hits' => 10000
				));
		}

	} elseif ($params[0] == 'end_session') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') die(apiError($extension, $action, 'This method requires a POST.', $no401));
		else {
			closeSession($_USER['ID']);
			die(apiError($extension, $action, 'Logged out.'));
		}
	} elseif ($params[0] == 'update_profile_image') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') die(apiError($extension, $action, 'This method requires a POST.', $no401));
		else {
			if (!empty($_FILES['image']['tmp_name'])) {
				$upload = uploadAvatar($_FILES['image']);
			}

			switch ($params[1]) {
			case 'xml':
				createXML('user', array('id'=>$_USER['ID']));
				break;
			case 'json':
				createJSON('user', array($_USER['ID']));
			}
		}
	} elseif ($params[0] == 'update_profile_background_image') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') die(apiError($extension, $action, 'This method requires a POST.', $no401));
		else {
			$upload = uploadBackground($_FILES['image']);

			if ($_POST['tile'] === true) $db->updateCustomizeOptions($_USER['ID'], array('background_style'=>'repeat'));

			switch ($params[1]) {
			case 'xml':
				createXML('user', array('id'=>$_USER['ID']));
				break;
			case 'json':
				createJSON('user', array($_USER['ID']));
			}
		}
	} elseif ($params[0] == 'update_profile') {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') die(apiError($extension, $action, 'This method requires a POST.', $no401));
		else {
			if ($_POST['location']) {
				if (countChars(utf8_decode(trim($_POST['location']))) > 30) die(apiError($extension, $action, 'Account update failed: Location is too long (maximum is 30 characters)'));
				else {
					$return['location'] = trim($_POST['location']);
				}
			}

			if ($_POST['name']) {
				if (countChars(utf8_decode(trim($_POST['name']))) > 20) die(apiError($extension, $action, 'Account update failed: Name is too long (maximum is 20 characters)'));
				else {
					$return['realname'] = trim($_POST['name']);
				}
			}

			if ($_POST['description']) {
				if (countChars(utf8_decode(trim($_POST['description']))) > 140) die(apiError($extension, $action, 'Account update failed: Description is too long (maximum is 140 characters)'));
				else {
					$return['profile'] = $_USER['profile'];
					$return['profile']['bio'] = trim($_POST['description']);
				}
			}

			if ($_POST['url']) {
				if (substr($_POST['url'], 0, 7) != 'http://') $_POST['url'] = 'http://'.$_POST['url'];

				if (countChars(utf8_decode(trim($_POST['url']))) > 100) die(apiError($extension, $action, 'Account update failed: Url is too long (maximum is 100 characters)'));
				else {
					if (!$return['profile']) $return['profile'] = $_USER['profile'];
					$return['profile']['url'] = trim($_POST['url']);
				}
			}

			if ($return['profile']) $return['profile'] = serialize($return['profile']);

			if (isset($return)) $db->updateUserOptions($_USER['ID'], $return);

			switch ($params[1]) {
			case 'xml':
				createXML('user', array('id'=>$_USER['ID']));
				break;
			case 'json':
				createJSON('user', array($_USER['ID']));
			}
		}
	}
} elseif ($module[0] == 'help') {
	if ($params[0] == 'test') {
		if ($params[1] == 'xml') echo '<ok>true</ok>';
		elseif ($params[1] == 'json') echo '"ok"';
		else echo 'ok';
	}
}

?>