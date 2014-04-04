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

/**
 * It imports a file from the includes directory
 *
 * @param string $name 
 * @return void
 * @author Ruben Díaz
 */
function import($name)
{
	$file = PATH.'includes/'.$name.'.php';
	if (file_exists($file)) require_once $file;
	else return false;
}

/**
 * It writes a string in the debug.log file
 *
 * @param string $string 
 * @param string $type 
 * @return void
 * @author Ruben Díaz
 */
function write_debug($string, $type)
{
	$file = 'debug.log';
	$date = date('l jS F Y h:i:s A');
	$fp = fopen($file, 'a');
	switch ($type) {
	case 'mysql':
		fputs($fp, "\n[MYSQL -- $date]\n$string");
		break;
	}
	fclose($fp);
}

/* GETTEXT STUFF (WORDPRESS) */
function __($string, $js = false)
{
	global $gettext_tables;
	if (!$gettext_tables) {
		if ($js) return str_replace("'", "\\"."'", $string);
		else return $string;
	}
	else {
		if ($js) return str_replace("'", "\\"."'", $gettext_tables->translate($string));
		else return $gettext_tables->translate($string);
	}
}

/**
 * It loads the $_USER global variable, and sets the language of the environment
 *
 * @return void
 * @author Ruben Díaz
 * @author Marcos García
 */
function checkUser()
{
	global $db;
	global $_USER;
	global $gettext_tables;
	global $jk;

	$default_lang = $jk->default_lang;
	define('THEME', $jk->default_theme);
	$userInfo = $db->getFromCookie($_COOKIE['jisko_'.md5($jk->base)]);
	if ($userInfo === false) {
		$_USER = false;

		$langExplode = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$langExplode = explode(',', $langExplode[0]);

		$file = PATH.'includes/languages/'.$langExplode[0].'/LC_MESSAGES/messages.mo';
		if (!file_exists($file)) {
			if ($langExplode[1]) {
				$file = PATH.'includes/languages/'.$langExplode[1].'/LC_MESSAGES/messages.mo';
				if (!file_exists($file)) {
					if ($jk->default_lang != 'def') {
						$file = PATH.'includes/languages/'.$jk->default_lang.'/LC_MESSAGES/messages.mo';
						if (!file_exists($file)) $file = false;
					}
					else $file = false;
					
					$default_lang = $jk->default_lang;
				}
				else $default_lang = $langExplode[1];
			}
			else {
				if ($jk->default_lang != 'def') {
					$file = PATH.'includes/languages/'.$jk->default_lang.'/LC_MESSAGES/messages.mo';
					if (!file_exists($file)) $file = false;
				}
				else $file = false;
				
				$default_lang = $jk->default_lang;
			}
		}
		else $default_lang = $langExplode[0];
		
		if ($file) {
			define('LANG', $default_lang);
			$gettext_tables = new gettext_reader(
				new CachedFileReader($file)
			);
			$gettext_tables->load_tables();
		}
	} else {
		$_USER = $userInfo;
		define('LANG', $_USER['language']);
		define('THEME', $_USER['theme']);

		if (file_exists(PATH.'includes/languages/'.$_USER['language'].'/LC_MESSAGES/messages.mo')) {
			global $gettext_tables;
			$gettext_tables = new gettext_reader(
				new CachedFileReader(PATH.'includes/languages/'.$_USER['language'].'/LC_MESSAGES/messages.mo')
			);
			$gettext_tables->load_tables();
		}
		/*if($db->getFromCookieSessionType($_COOKIE[NAME]) != 'normal') {
			$db->deleteSession($_USER['ID']);
			$_USER = false;
			setcookie(NAME, '', time()-3600);
		}*/
	}
}

/**
 * It parses some 'serialized' strings and converts them into an array
 *
 * @param string $userInfo 
 * @return void
 * @author Marcos García
 */
function processUserInfo($userInfo)
{
	if ($userInfo) {
		if ($userInfo['ignored']) {
			$comp = unserialize(stripslashes($userInfo['ignored']));
			if (!$comp) $userInfo['ignored'] = array();
			else $userInfo['ignored'] = $comp;
		}
		else $userInfo['ignored'] = array();
		if (!is_null($userInfo['twitter'])) $userInfo['twitter'] = unserialize(stripslashes($userInfo['twitter']));
		if (!is_null($userInfo['extras'])) $userInfo['extras'] = unserialize(stripslashes($userInfo['extras']));
		if (!is_null($userInfo['profile'])) $userInfo['profile'] = unserialize(stripslashes($userInfo['profile']));
		if (!is_null($userInfo['shorter_service'])) $userInfo['shorter_service'] = unserialize(stripslashes($userInfo['shorter_service']));
		if (!is_null($userInfo['privacy'])) $userInfo['privacy'] = unserialize(stripslashes($userInfo['privacy']));
		if (!is_null($userInfo['customize'])) $userInfo['customize'] = unserialize(stripslashes($userInfo['customize']));
		else $userInfo['customize'] = array();
	
		if (!$userInfo['customize']['background_style']) $userInfo['customize']['background_style'] = 'normal';
	}
	return $userInfo;
}

function checkUserAPI()
{
	global $db;
	global $_USER;
	$userInfo = $db->getFromCookie($_COOKIE['JSESSIONID']);
	if ($userInfo === false) {
		$_USER = false;
	} else {
		$_USER = $userInfo;
	}
}

/**
 * undocumented function
 *
 * @param string $page 
 * @param string $count 
 * @return void
 * @author Ruben Díaz
 */
function getStart($page, $count = false)
{
	global $jk;
	if ($count == false) $count = (int) $jk->notes_per_page;
	if ($page == 1) $start = 0;
	else if ($page <= 0) return;
		else $start = ($page - 1) * $count;
		return $start;
}

/**
 * undocumented function
 *
 * @param string $ref 
 * @return void
 * @author Ruben Diaz
 */
function uploadAvatar($ref)
{
	global $globals;
	global $_USER;
	global $db;
	import('thumbnail');
	extract($_USER);
	$extension = strtolower(pathinfo($ref['name'], PATHINFO_EXTENSION));
	$size = $ref['size'] / 1024;
	if (!in_array($extension, $globals['allowed_extensions'])) return 'INVALID_EXTENSION';
	if ($size > 250) return 'BIG_FILE';
	$path = PATH.'users_files/'.$username.'/img/avatar/';
	if ($avatar) {
		$avatar_info = pathinfo(PATH.'users_files/'.$username.'/img/avatar/'.$avatar);
		if (!@unlink($path.$avatar) || (!@unlink($path.$avatar_info['filename'].'_side.'.$avatar_info['extension']) || (!@unlink($path.$avatar_info['filename'].'_note.'.$avatar_info['extension']) || (!@unlink($path.$avatar_info['filename'].'_follow.'.$avatar_info['extension']))))) return 'CANT_DELETE';
	}
	if ($ref['type'] == 'image/pjpeg') $ref['type'] = 'image/jpeg';
	$thumbnail  = new Thumbnail($ref['tmp_name'], $ref['type']);
	$thumb_original = $thumbnail->do_thumbnail();
	$thumb_side  = $thumbnail->do_thumbnail(150, 150);
	$thumb_note  = $thumbnail->do_thumbnail(48, 48);
	$thumb_follow = $thumbnail->do_thumbnail(24, 24);
	$avatar_name = substr(md5(rand()), 0, 6);
	if (!$thumbnail->save($avatar_name, $path, $thumb_original) || (!$thumbnail->save($avatar_name.'_side', $path, $thumb_side) || (!$thumbnail->save($avatar_name.'_note', $path, $thumb_note) || (!$thumbnail->save($avatar_name.'_follow', $path, $thumb_follow))))) return 'ERROR_COPY';
	$db->updateUserOptions($_USER['ID'], array('avatar' => "$avatar_name.$extension"));
	return 'OK';
}

/**
 * undocumented function
 *
 * @param string $ref 
 * @return void
 * @author Ruben Díaz
 */
function uploadBackground($ref)
{
	global $globals;
	global $_USER;
	
	$extension = strtolower(pathinfo($ref['name'], PATHINFO_EXTENSION));
	if (!in_array($extension, $globals['allowed_extensions'])) return 'INVALID_EXTENSION';
	else {
		$size = $ref['size'] / 1024;
		if ($size > 1024) return 'TOO_BIG';
		else {
			if ($_USER['customize']['background']) {
				if (!@unlink(PATH.'/users_files/'.$_USER['username'].'/img/background/bg.'.$_USER['customize']['background'])) {
					return 'ERROR_DELETE_PREV';
					die();
				}
			}
			if (!@copy($ref['tmp_name'], PATH.'/users_files/'.$_USER['username']."/img/background/bg.$extension")) return 'ERROR_UPLOAD';
			else {
				return $extension;
			}
		}
	}
}

/**
 * It returns the URL of an uploaded background
 *
 * @param string $username 
 * @param string $background 
 * @return void
 * @author Ruben Díaz
 */
function get_background_url($username, $background)
{
	global $jk;
	return $jk->base.'users_files/'.$username.'/img/background/bg.'.$background;
}

/**
 * undocumented function
 *
 * @param string $row 
 * @param string $reducedAjax 
 * @param string $mobile 
 * @param string $defined 
 * @return void
 * @author Marcos García
 * @author Ruben Díaz
 */

function processNote($row, $reducedAjax = false, $mobile = false, $defined = false)
{
	global $db;
	global $_USER;

	if (is_numeric($row)) {
        $noteInfo = $db->getNoteCombined($row);
    }
	else {
		if (!$defined) {
			if ($row['type'] == 'twitter' || ($row['type'] == 'twitter_reply')) $noteInfo = $db->getTwit($row['id']);
			else {
                $noteInfo = $db->getNoteCombined($row['id']);
            }
		}
		else $noteInfo = $row;
		
		$noteInfo = array_merge($row, $noteInfo);
	}

	if (!$noteInfo) return false;
	else {
		extract($noteInfo);
	}

	if ($type == 'private') {
		if ($_USER['ID'] !== $user_id && ($_USER['ID'] != $reply_user)) return false;
	}

	$note = trim(put_smileys(preg_replace('#https?://[^.\s]+\.[^\s]+#ix', "<a href=\"\\0\" rel=\"external\" target=\"_blank\">\\0</a>", utf8_htmlentities($note))));
    
    // $note = $note." this is bs... ";

	if ($type == 'private') $note = preg_replace('/(\s|\A)(!){1}([a-zA-Z0-9_]+)/', '', $note);

	if ($_USER['shorter_service']['preview'] == true) {
		preg_match_all("#https?://[^.\s]+\.[^\s]+#ix", $note, $matches);
		foreach ($matches as $uri) {
			$uri[0] = str_replace('"', '', $uri[0]);
			$parse_url = parse_url($uri[0]);

			if ($parse_url['host'] == '3.ly') $note = str_replace($uri[0], $uri[0].'-', $note);
			if ($parse_url['host'] == 'tinyurl.com') $note = str_replace($uri[0], 'http://preview.tinyurl.com'.$parse_url['path'], $note);
			if ($parse_url['host'] == 'bit.ly' || ($parse_url['host'] == 'j.mp')) $note = str_replace($uri[0], $uri[0].'+', $note);
			if ($parse_url['host'] == 'is.gd') $note = str_replace($uri[0], $uri[0].'-', $note);
			if ($parse_url['host'] == 'u.nu') $note = str_replace($uri[0], $uri[0].'?', $note);
		}
	}

	if ($type == 'twitter' || ($type == 'twitter_reply')) {
		if ($mobile) {
			if ($mobile == 'touch') $note = preg_replace('/(\s|\A)(@){1}([a-zA-Z0-9_]+)/', '$1<a href="http://mobile.twitter.com/$3" target="_blank">$2$3</a>', $note);
			else $note = preg_replace('/(\s|\A)(@){1}([a-zA-Z0-9_]+)/', '$1<a href="http://m.twitter.com/$3">$2$3</a>', $note);
		}
		else {
			$note = preg_replace('/(\s|\A)(@){1}([a-zA-Z0-9_]+)/', '$1<a href="http://twitter.com/$3">$2$3</a>', $note);
			$note = preg_replace('/(\s|\A)(#){1}([a-zA-Z0-9_]+)/', '$1<a href="http://search.twitter.com/search?q='.urlencode('#$3').'">$2$3</a>', $note);
		}
	}
	else {
		if ($replying) {
			if ($mobile) $note = preg_replace('/(\s|\A)(@){1}([a-zA-Z0-9_\-]+)/', '$1<a href="'.coreLink('mobile', 'status', $replying).'">$2$3</a>', $note);
			else $note = preg_replace('/(\s|\A)(@){1}([a-zA-Z0-9_\-]+)/', '$1<a href="'.coreLink(array('nav=link'), 'ajax', 'note', $replying).'" onmouseover="return tooltip.ajax_delayed(event, \'note\', \''.$replying.'\');" onmouseout="tooltip.hide(event);">$2$3</a>', $note);
		}
		else {
			if (!$mobile) {
				$note = preg_replace('/(\s|\A)(@){1}([a-zA-Z0-9_\-]+)/', '$1<a href="'.coreLink("$3").'">$2$3</a>', $note);
				$note = preg_replace('/(\s|\A)(%){1}([a-zA-Z0-9_\-]+)/', '$1<a href="http://twitter.com/$3">@$3</a>', $note);
			}
			else {
				$note = preg_replace('/(\s|\A)(@){1}([a-zA-Z0-9_\-]+)/', '$1<a href="'.coreLink('mobile', "$3").'">$2$3</a>', $note);
				if ($mobile == 'touch') $note = preg_replace('/(\s|\A)(%){1}([a-zA-Z0-9_\-]+)/', '$1<a href="http://mobile.twitter.com/$3">@$3</a>', $note);
				else $note = preg_replace('/(\s|\A)(%){1}([a-zA-Z0-9_]+)/', '$1<a href="http://m.twitter.com/$3">@$3</a>', $note);
			}
		}
		//$note = preg_replace('/(\s|\A)(&){1}([a-zA-Z0-9_]+)/', '$1<a href="'.BASE.'group/$3">$2$3</a>', $note);
		if ($mobile) $note = preg_replace('/(\s|\A)(#){1}([a-zA-Z0-9_]+)/', '$1<a href="'.coreLink('mobile', 'tag', "$3").'">$2$3</a>', $note);
		else $note = preg_replace('/(\s|\A)(#){1}([a-zA-Z0-9_]+)/', '$1<a href="'.coreLink('tag', "$3").'">$2$3</a>', $note);
		
		$note = preg_replace_callback('/\[(\*|\/|_|\-)(.+)\]/U',
			create_function('$matches', '
				switch ($matches[1]) {
					case "*":
						return "<strong>" . $matches[2] . "</strong>";
					case "/":
						return "<em>" . $matches[2] . "</em>";
					case "_":
						return "<u>" . $matches[2] . "</u>";
					case "-":
						return "<s>" . $matches[2] . "</s>";
				}
		'), $note);
	}

	if ($reducedAjax) {
		$return = array();
		if ($row['type'] != 'twitter' && ($row['type'] != 'twitter_reply')) {
			$return['avatar'] = getAvatar($user_id, 48);
			if ($attached_file) $return['attached_file'] = $attached_file;
			if ($_USER) {
				if (in_array($_USER['ID'], $reply_user) == 1) $return['replying'] = true;
				$is_favorite = $db->checkFavorite($_USER['ID'], $ID);
				if ($is_favorite) $return['favorite'] = true;
			}
			$replies = $db->getNumRepliesNote($ID);
			if ($replies) $return['replies'] = $replies;
			$return['from'] = $from;
			$return['user_id'] = $user_id;
            
		}
		else {
			$serial = unserialize(stripslashes($serial));
			extract($serial);
			$return['avatar'] = $avatar;
			$return['type'] = 'twitter';
		}
		$return['text'] = $note;
		$return['id'] = $ID;
        $return['location'] = $_USER['location'];
		$return['time_ago'] = showTimeAgo($timestamp);
		$return['username'] = $username;
        $return['tip_amount'] = $tip_amount;
        $return['bill_amount'] = $bill_amount;
		return $return;
	}
	//else return $note;
    else return $noteInfo;
}

/**
 * undocumented function
 *
 * @param string $string 
 * @return void
 * @author Ruben Díaz
 */
function put_smileys($string)
{
	global $jk;
	$string = preg_replace('/(\s|^)(:pedobear:)/i', ' <img src="'.$jk->base.'static/img/smileys/pedobear.png" alt="pedobear" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:awesome:)/i', ' <img src="'.$jk->base.'static/img/smileys/awesome.png" alt="awesome" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:roll:)/i', ' <img src="'.$jk->base.'static/img/smileys/roll.gif" alt="rolleyes" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}\\\|;-{0,1}\\\|:-\/|:-{0,1}S|:-{0,1}\?)/', ' <img src="'.$jk->base.'static/img/smileys/confused.png" alt="confused" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}\()/i', ' <img src="'.$jk->base.'static/img/smileys/sad.png" alt="sad" width="16" height="16" title="$1" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}D)/i', ' <img src="'.$jk->base.'static/img/smileys/grin.png" alt="grin" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}O)/i', ' <img src="'.$jk->base.'static/img/smileys/surprised.png" alt="surprised" title="$1"  width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}\))/i', ' <img src="'.$jk->base.'static/img/smileys/smile.png" alt="smile" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(;-{0,1}\))/i', ' <img src="'.$jk->base.'static/img/smileys/wink.png" alt="wink" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(\^\^)/i', ' <img src="'.$jk->base.'static/img/smileys/happy.png" alt="happy" title="$1"  width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(¬¬)/i', ' <img src="'.$jk->base.'static/img/smileys/annoyed.png" alt="¬¬" title="$1"  width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(8-{0,1}\))/i', ' <img src="'.$jk->base.'static/img/smileys/cool.png" alt="cool" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}P)/i', ' <img src="'.$jk->base.'static/img/smileys/tongue.png" alt="tongue" title="$1"  width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:\'-{0,1}\()/i', ' <img src="'.$jk->base.'static/img/smileys/cry.png" alt="cry" title="$1"  width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:\'-{0,1}\))/i', ' <img src="'.$jk->base.'static/img/smileys/yay.png" alt="yay" title="$1"  width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(x-{0,1}D)/i', ' <img src="'.$jk->base.'static/img/smileys/laugh.png" alt="laugh" title="$1"  width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}\|)/i', ' <img src="'.$jk->base.'static/img/smileys/neutral.png" alt="neutral" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}\@)/i', ' <img src="'.$jk->base.'static/img/smileys/furious.png" alt="furious" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(:-{0,1}\*)/i', ' <img src="'.$jk->base.'static/img/smileys/kiss.png" alt="kiss" title="$1" width="16" height="16" />', $string);
	$string = preg_replace('/(\s|^)(\(L\))/i', ' <img src="'.$jk->base.'static/img/smileys/heart.png" alt="love" title="$1" width="16" height="16" />', $string);
	return $string;
}

/**
 * It posts a note
 *
 * @param string $note 
 * @param string $userInfo 
 * @param string $from 
 * @param string $auth 
 * @param string $attached_file 
 * @param string $replying 
 * @param string $get_id 
 * @param string $sendTwitter 
 * @return void
 * @author Ruben Díaz
 * @author Marcos García
 */
function postNote($note = 0, $userInfo = 0, $from = 'web', $auth = 0, $attached_file = 0, $replying = 0, $get_id = 0, $sendTwitter = true, $tip_amount = 0, $bill_amount = 0)
{
	global $_USER;
	global $db;
	global $skipauth;
	global $mailing;
	global $jk;

	//We shouldn't try to post if we don't have any note.
	if (!$db && (!$note)) return;

	//If $userInfo is not set, then we post the note as the logged user.
	if (!$userInfo) $userInfo = $_USER;

	//Security check.
	if (md5($_USER['salt']) != $auth) {
		if (!$skipauth) return 'INVALID';
	}

	if (get_magic_quotes_gpc()) $note = stripslashes($note);

	$length = countChars(utf8_decode(trim($note)));

	if ($length < 2) return 'SHORT_NOTE';
	elseif ($length > 140) return 'LONG_NOTE';

	if ((time() - $_USER['last_note']) < $jk->wait_until_repost) return 'COWBOY';

	$userID = $userInfo['ID'];
	$username = $userInfo['username'];

	if (!empty($attached_file['attach']['tmp_name'])) {
		if ($attached_file['attach']['error'] > 0) return 'ERROR_UPLOAD';
		else {
			$filename = str_replace(' ', '_', $attached_file['attach']['name']);
			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			$file_name = substr(md5(rand()), 0, 8) .'.'.$extension;
			$size = $attached_file['attach']['size'] / 1024;
			if (!in_array($extension, $jk->denied_extensions) && countChars($extension) > 0) {
				if ($size <= 8192) {
					if (is_dir('users_files/'.$username.'/files') === false) @ mkdir('users_files/'.$username.'/files', 0777);
					if (move_uploaded_file($attached_file['attach']['tmp_name'], 'users_files/'.$username.'/files/'.$file_name)) $attached_file = $file_name;
					else return 'ERROR_UPLOAD';
				} 
				else return 'BIG_FILE';
			}
			else return 'FILE_NOT_ALLOWED';
		}
	}
	else $attached_file = false;
	$shorter_service = $userInfo['shorter_service'];
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

	//We check if there's an API name registered
	if ($from != 'web' && ($from != 'mobile')) {
		$query = $db->send("SELECT `api`.`approved` FROM `api` WHERE `api`.`name` = '".mysql_real_escape_string($from)."' LIMIT 1");
		if (mysql_num_rows($query) > 0) {
			if (!mysql_result($query, 0)) $from = 'api';
			else $from = $from;
		}
		else {
			$db->send('INSERT INTO `api` SET `name`=\''.mysql_real_escape_string($from).'\', `timestamp`=\''.time().'\'');
			$from = 'api';
		}
	}

	//If the first character of the note is the '!' symbol, then we know it's a private note.
	if ($note{0} == '!') {
		preg_match('/!([\w\d]+)/', $note, $to_array);
		$to = $to_array[1];
		$to_userID = $db->getIDFromUsername($to);
		if (!$to_userID) return 'INVALID_USER';
		else {
			if ($_USER['ID'] == $to_userID) $following = true;
			else $following = $db->checkFollowing($to_userID, $userID);

			if (!$following) return 'NOT_FOLLOWING';
			else {
				$newPrivateNote = $db->newPrivateNote($userID, $to_userID, $note, $attached_file, $from, $_SERVER['REMOTE_ADDR']);
				if (!$newPrivateNote) return 'INVALID_USER';
				else {
					//We obtain the userInfo of the receiver of the note.
					$info = $db->getUserInfo($to_userID);
					if ($info['notification_level'] == 3 || ($info['notification_level'] >= 4)) {
						$note = preg_replace('/(\s|\A)(!){1}(\w+)(\s|^)/', '', $note);
						$mailing->newPrivateNote($info, stripslashes($note), $userInfo, $attached_file, $newPrivateNote);
					}

					if ($get_id) return $newPrivateNote;
					else return 'OK';
				}
			}
		}
	}
	//Replies, Twitter or Normal notes.
	else {
		$twitter = false;
		$read = true;
		$send_to_twitter = true;

		if (is_array($reply_users)) $reply_users = array_unique($reply_users);

		if ($note{0} == '@') {
			if ($replying) {
				$reply_user = $db->getPosterID($replying);
				if (!$reply_user) $replying = false;
			}
			else $reply_user = $reply_users[0];

			$send_to_twitter = false;
			$read = false;
		}
		elseif ($note{0} == '%') {
			$twitter = true;
			$replying = false;
		}
		else $replying = false;
		
		preg_match_all('/(?:\s|\A)(%|@){1}(\w+)/', $note, $matches);
		foreach ($matches[1] as $key => $value) {
			if ($value == '@') {
				$reply_users[] = (int) $db->getIDFromUsername($matches[2][$key]);
			}
		}

		//Quick fix to resolve http://bugs.launchpad.net/jisko/+bug/326923
		$note = trim(addslashes($note));

		//We create the note. It should return the ID of inserted note
		$newNote = $db->newNote($userID, 'public', $twitter, $note, $attached_file, $from, $replying, $_SERVER['REMOTE_ADDR'], $reply_user, $read, $tip_amount, $bill_amount);

		if (is_array($reply_users)) {
			foreach ($reply_users as $mentionID) {
				if (checkViewableUser($userID, $_USER['ID'], 'show_notes')) {
					$replyInfo = $db->getUserOptions($mentionID, array(
							'notification_level',
							'email',
							'language',
							'username'
						));

					if ($replyInfo) {
						$bulk[] = "('$newNote', '$mentionID', '".time()."')";
						if ($replyInfo['notification_level'] == 3 || ($replyInfo['notification_level'] == 5)) {
							$mailing->newReplyNote($replyInfo, $note, $newNote, $replying, $attached_file);
						}
					}
				}
			}
			if (is_array($bulk)) $db->mentions($bulk);
		}

		//We look for tags in the post
		preg_match_all('/(\s|\A)(#){1}([a-zA-Z0-9_]+)/', $note, $matches);

		$added_tags = array('');
		foreach ($matches[0] as $tag) {
			$tag = trim(str_replace('#', '', $tag));
			if (!in_array($tag, $added_tags)) {
				if (strlen($tag) <= 20) $db->createTag($tag, $userID, $newNote, time());
			}
			$added_tags[] = $tag;
		}

		$db->updateUserOptions($userID, array('last_note'=>time()));

		if ($send_to_twitter && has_twitter()) {
			fork(coreLink(array('note='.$newNote, 'user_id='.$userID, 'auth='.md5($userInfo['salt'])), 'cron', 'twitter'));
		}

		$result = getViewableUsers($userID, 'show_notes');
		$bulk = array();
		if ($result != false) {
			foreach ($result as $follower) $bulk[] = "('$userID', '$follower', '$newNote', '".time()."', '".(int)$reply_user."', 'public')";
		}

		$bulk[] = "('$userID', '$userID', '$newNote', '".time()."', '0', 'public')";
		$result = $db->post2id($bulk);

		if ($get_id) return $newNote;
		else return 'OK';
	}
}

/**
 * undocumented function
 *
 * @param string $timestamp 
 * @return void
 * @author Ruben Díaz
 */
function showTimeAgo($timestamp)
{
	$diff = time() - (int) $timestamp;
	if ($diff <= 1) {
		return __('right now');
	} elseif ($diff < 60) {
		$string = sprintf(__("%ss ago"), $diff);
		return $string;
	} elseif ($diff < 3600) {
		$mdiff = round($diff / 60);
		$string = sprintf(__("%sm ago"), $mdiff);
		return $string;
	} elseif (($diff / 3600) >= 1 && ($diff / 3600)  < 24) {
		$mdiff = ($diff / 3600);
		$floor = floor($mdiff);
		if ($floor == 1) $string = __("1h");
		else $string = sprintf(__("%sh"), $floor);
		return $string;
	} elseif (($diff /  86400) >= 1 && ($diff /  86400) < 25) {
		$mdiff = ($diff /  86400);
		$floor = floor($mdiff);
		if ($floor == 1) $string = __("1d");
		else $string = sprintf(__("%sd"), $floor);
		return $string;
	} else {
		$date = sprintf(__("the %s at %s"), date('d/m/Y', $timestamp), date('H:i', $timestamp));
		return $date;
	}
}

/**
 * undocumented function
 *
 * @return void
 * @author Ruben Díaz
 */
function use_invitations()
{
	global $jk;
	return (bool) $jk->use_invitations;
}

/**
 * undocumented function
 *
 * @param string $token 
 * @return void
 * @author Ruben Díaz
 */
function check_invitation($token = '')
{
	global $db;
	if (!use_invitations()) return true;
	if (empty($token)) return false;
	return (bool) $db->checkToken($token);
}

/**
 * undocumented function
 *
 * @param string $email 
 * @param string $token 
 * @return void
 * @author Ruben Díaz
 */
function newInvitation($email, $token)
{
	global $db;
	global $_USER;
	global $mailing;
	$result = $db->newInvitation($_USER['ID'], $email, $token);
	if ($result) {
		$mailing->newInvitation($email, $token);
		$result = $db->updateInvitations($_USER['ID']);
	}
}

/**
 * It returns an error depending in the format of the request
 *
 * @param string $format 
 * @param string $request 
 * @param string $error 
 * @param string $no401 
 * @param string $display403 
 * @return void
 * @author Marcos García
 */
function apiError($format, $request, $error, $no401 = false, $display403 = false)
{
	if ($no401) {
		if (!$display403) header('HTTP/1.1 401 Unauthorized');
		else header('HTTP/1.1 403 Forbidden');
	}
	if ($format == 'xml' || ($format == 'rss')) {
		header('Content-Type: text/xml charset=UTF-8');
		$XMLWriter = new XMLWriter();
		$XMLWriter->openURI('php://output');
		$XMLWriter->startDocument('1.0', 'UTF-8');

		$XMLWriter->setIndent(true);

		$XMLWriter->startElement('hash');
		$XMLWriter->writeElement('request', $request);
		$XMLWriter->writeElement('error', $error);
		$XMLWriter->endElement();
	} elseif ($format == 'json') {
		header('Content-Type: text/javascript; charset=utf-8');
		$array = array(
			'request' => $request,
			'error' => $error
		);

		echo json_encode($array);
	} elseif ($format == 'basic') {
		echo $error;
	}
}

/**
 * Related to the API, it echoes an XML file with notes, users, ids...
 *
 * @param string $type 
 * @param string $result 
 * @return void
 * @author Marcos García
 */
function createXML($type, $result)
{
	global $db;
	global $_USER;
	global $jk;

	header('Content-Type: application/xml; charset=utf-8');

	$XMLWriter = new XMLWriter();
	$XMLWriter->openURI('php://output');
	$XMLWriter->startDocument('1.0', 'UTF-8');

	$XMLWriter->setIndent(true);

	//We set all the dates to UTC so that clients don't have errors with them
	date_default_timezone_set('UTC');

	if ($type != 'direct_message') {
		if ($type == 'notes' || ($type == 'notes+t' || ($type == 'twitter'))) {
			$XMLWriter->startElement('statuses'); // starting <statuses>
		}
		elseif ($type != 'user' && ($type != 'notess')) $XMLWriter->startElement($type);
	
		if ($type != 'ids' && ($type != 'notess' && ($type != 'acc_verif' && ($type != 'user' && ($type != 'relationship'))))) {
			$XMLWriter->writeAttribute('type', 'array');
		}
	}

	foreach ($result as $note) {
		if ($type == 'ids') {
			$XMLWriter->writeElement('id', $note['ID']);
		}
		else {
			if ($type != 'users' && ($type != 'acc_verif' && ($type != 'user' && ($type != 'relationship')))) {
				if ($type == 'notess') $noteArray = $db->getNoteCombined($note);
				else {
					if ($type == 'twitter' || ($note['type'] == 'twitter' || ($note['type'] == 'twitter_reply'))) {
						$noteArray = $db->getTwit($note['id']);
						$serial = unserialize(stripslashes($noteArray['serial']));
						extract($serial);
					}
					else $noteArray = $db->getNoteCombined($note['id']);
				}
				$favorite = $db->checkFavorite($_USER['ID'], $noteArray['ID']);
				$text = trim(utf8_htmlentities($noteArray['note']));

				if ($type == 'notes' || ($type == 'notess' || ($type == 'notes+t' || ($type == 'twitter')))) {
					$id_to_check = array('user'=>$noteArray['user_id']);
					$XMLWriter->startElement('status'); // starting <status> in <statuses>
				}
				elseif ($type == 'direct_messages' || ($type == 'direct_message')) {
					$id_to_check = array('sender'=>$noteArray['user_id'], 'recipient'=>$noteArray['reply_user']);
					$XMLWriter->startElement('direct_message'); // starting <direct_message> in <statuses>
				}

				if ($note['type'] == 'twitter' || ($note['type'] == 'twitter_reply')) $XMLWriter->writeElement('id', $serial['twitid']);
				else $XMLWriter->writeElement('id', $noteArray['ID']);
				$XMLWriter->writeElement('created_at', date('D M j G:i:s O Y', $noteArray['timestamp']));
				$XMLWriter->writeElement('text', stripslashes($text));
				if ($type == 'direct_messages' || ($type == 'direct_message')) {
					$XMLWriter->writeElement('sender_id', $noteArray['user_id']);
					$XMLWriter->writeElement('recipient_id', $noteArray['reply_user']);
					$XMLWriter->writeElement('sender_screen_name', $noteArray['username']);
					$XMLWriter->writeElement('recipient_screen_name', $db->getUsernameFromID($noteArray['reply_user']));
				} elseif ($type == 'notes' || ($type == 'notes+t' || ($type == 'notess'))) {
					if ($note['type'] != 'twitter' && ($note['type'] != 'twitter_reply')) $XMLWriter->writeElement('source', $noteArray['from']);
					if ($type == 'notes+t') {
						if ($note['type'] != 'twitter' && ($note['type'] != 'twitter_reply')) $XMLWriter->writeElement('type', 'note');
						else $XMLWriter->writeElement('type', $note['type']);
					}
					if ($note['type'] != 'twitter' && ($note['type'] != 'twitter_reply')) {
						$XMLWriter->writeElement('truncated', 'false');
						$XMLWriter->writeElement('in_reply_to_status_id', ($noteArray['replying'] ? $noteArray['replying'] : null));
						$XMLWriter->writeElement('in_reply_to_user_id', ($noteArray['replying'] ? $noteArray['reply_user'][0] : null));
						$XMLWriter->writeElement('favorited', ($db->checkFavorite($_USER['ID'], $noteArray['ID']) ? 'true' : 'false'));
						$XMLWriter->writeElement('in_reply_to_screen_name', ($noteArray['replying'] ? $db->getUsernameFromID($noteArray['reply_user']) : null));
					}
				}
			}
			else {
				if ($type == 'relationship') $id_to_check = array('target'=>$result[0][1], 'source'=>$result[0][0]);
				else $id_to_check = array('user'=>$note);
			}
			foreach ($id_to_check as $key => $userto) {
				if ($note['type'] != 'twitter' && ($note['type'] != 'twitter_reply')) {
					$profile = $db->getUserInfo($userto);
					$avatar = getAvatar($userto, 48);
					
					if (!$profile['customize']['background']) $background = $jk->base.'themes/transparency/img/bg.png';
					else $background = get_background_url($profile['username'], $profile['customize']['background']);
				}
				$XMLWriter->startElement($key); // starting <user> in <status> in <statuses>
				if ($note['type'] != 'twitter' && ($note['type'] != 'twitter_reply' && ($type != 'relationship'))) {
					if ($type == 'direct_message' || ($type == 'direct_messages')) $XMLWriter->writeElement('id', $userto);
					else $XMLWriter->writeElement('id', $profile['ID']);
					$XMLWriter->writeElement('name', ($profile['realname'] ? str_replace('<', '&lt;', str_replace('>', '&gt;', $profile['realname'])) : $profile['username']));
					$XMLWriter->writeElement('screen_name', $profile['username']);
					$XMLWriter->writeElement('location', ($profile['location'] ? str_replace('<', '&lt;', str_replace('>', '&gt;', $profile['location'])) : null));
					$XMLWriter->writeElement('description', ($profile['profile']['bio'] ? str_replace('<', '&lt;', str_replace('>', '&gt;', $profile['profile']['bio'])) : null));
					$XMLWriter->writeElement('profile_image_url', $avatar);
					$XMLWriter->writeElement('url', ($profile['profile']['url'] ? $profile['profile']['url'] : null));
					$XMLWriter->writeElement('protected', (($profile['privacy']['show_notes'] < 3) ? 'true' : 'false'));
					$XMLWriter->writeElement('followers_count', $db->countFollowers($profile['ID']));
					$XMLWriter->writeElement('created_at', date('D M j G:i:s O Y', $profile['since']));
					$XMLWriter->writeElement('favourites_count', $db->countNotes('favorites', $profile['ID']));
					$XMLWriter->writeElement('statuses_count', $db->countNotes('archive', $profile['ID']));
					$XMLWriter->writeElement('friends_count', $db->countFollowing($profile['ID']));
					$XMLWriter->writeElement('following', ($db->checkFollowing($_USER['ID'], $profile['ID']) ? 'true' : 'false'));
					$XMLWriter->writeElement('utc_offset', null); //TODO
					$XMLWriter->writeElement('geo_enabled', 'false'); //TODO
					$XMLWriter->writeElement('time_zone', null); //TODO
					$XMLWriter->writeElement('profile_background_image_url', $background);
					$XMLWriter->writeElement('profile_background_tile', (($profile['customize']['background_style'] == 'repeat') ? 'true' : 'false'));
					$XMLWriter->writeElement('notifications', 'false'); //TODO??
					$XMLWriter->writeElement('verified', 'false');
					$XMLWriter->writeElement('lang', (($profile['language'] == 'def' ? 'en' : $profile['language'])));
					$XMLWriter->writeElement('contributors_enabled', 'false'); //TODO?
					if ($type == 'users' || $type == 'acc_verif' || $type == 'user') {
						$viewable = checkViewableUser($_USER['ID'], $profile['ID'], 'show_notes');
						if ($viewable) {
							$result = $db->getNotes('archive', getStart(1), 1, $profile['ID']);
							$noteArray = $db->getNoteCombined($result[0]['id']);

							if ($noteArray) {
								$XMLWriter->startElement('status');
								$XMLWriter->writeElement('created_at', date('D M j G:i:s O Y', $noteArray['timestamp']));
								$XMLWriter->writeElement('id', $noteArray['ID']);
								$XMLWriter->writeElement('text', stripslashes($noteArray['note']));
								$XMLWriter->writeElement('source', $noteArray['from']);
								$XMLWriter->writeElement('truncated', 'false');
								$XMLWriter->writeElement('in_reply_to_status_id', ($noteArray['replying'] ? $noteArray['replying'] : null));
								$XMLWriter->writeElement('in_reply_to_user_id', ($noteArray['replying'] ? $noteArray['reply_user'][0] : null));
								$XMLWriter->writeElement('favorited', ($noteArray['favorite'] ? 'true' : 'false'));
								$XMLWriter->writeElement('in_reply_to_screen_name', ($noteArray['replying'] ? $db->getUsernameFromID($noteArray['reply_user'][0]) : null));
								$XMLWriter->endElement(); //closing </status>
							}
						}
					}

				}
				else {
					if ($type == 'relationship') {
						$XMLWriter->startElement('followed_by');
						$XMLWriter->writeAttribute('type', 'boolean');
						if ($key == 'target') $XMLWriter->text(($db->checkFollowing($id_to_check['target'], $id_to_check['source']) ? 'true' : 'false'));
						else $XMLWriter->text(($db->checkFollowing($id_to_check['source'], $id_to_check['target']) ? 'true' : 'false'));
						$XMLWriter->endElement();
						$XMLWriter->startElement('following');
						$XMLWriter->writeAttribute('type', 'boolean');
						if ($key == 'target') $XMLWriter->text(($db->checkFollowing($id_to_check['source'], $id_to_check['target']) ? 'true' : 'false'));
						else $XMLWriter->text(($db->checkFollowing($id_to_check['target'], $id_to_check['source']) ? 'true' : 'false'));
						$XMLWriter->endElement();
						$XMLWriter->writeElement('screen_name', $db->getUsernameFromID($userto));
						$XMLWriter->startElement('id');
						$XMLWriter->writeAttribute('type', 'integer');
						$XMLWriter->text($userto);
						$XMLWriter->endElement();
						if ($key == 'source') {
							$XMLWriter->startElement('notifications_enabled');
							$XMLWriter->writeAttribute('type', 'boolean');
							$XMLWriter->text('false');
							$XMLWriter->endElement();
							$XMLWriter->startElement('blocking'); //TODO
							$XMLWriter->writeAttribute('type', 'boolean');
							$XMLWriter->text('false');
							$XMLWriter->endElement();
						}

					}
					else {
						$XMLWriter->writeElement('screen_name', $serial['username']);
						$XMLWriter->writeElement('profile_image_url', $serial['avatar']);
					}
				}
				$XMLWriter->endElement(); //closing </key>
			}
			if ($type != 'users') $XMLWriter->endElement();
		}
	}
	if ($type != 'direct_message') {
		if ($type == 'notes' || ($type == 'notes+t' || ($type == 'twitter'))) $XMLWriter->endElement();
		elseif ($type != 'notes' && ($type != 'notes+t' && ($type != 'twitter' && ($type != 'notess')))) $XMLWriter->endElement();
	}
	$XMLWriter->endDocument();
	$XMLWriter->flush();
}

/**
 * Related to the API, it echoes a JSON file with notes, users, ids...
 *
 * @param string $type 
 * @param string $result 
 * @param string $callback 
 * @return void
 * @author Marcos García
 */
function createJSON($type, $result, $callback = null)
{
	global $db, $jk;
	global $_USER;

	//We set all the dates to UTC so that clients don't have errors with them
	date_default_timezone_set('UTC');

	if ($type != 'acc_verif' && ($type != 'notess' && ($type != 'user' && ($type != 'direct_message' && ($type != 'relationship'))))) $final = '[';

	$count = 0;
	foreach ($result as $note) {
		$count = $count + 1;

		if ($type == 'ids') $final .= $note['ID'];
		else {
			if ($type != 'users' && ($type != 'user' && ($type != 'relationship'))) {

				if ($type == 'notess') $noteArray = $db->getNoteCombined($note);
				else {
					if ($type == 'twitter' || ($note['type'] == 'twitter' || ($note['type'] == 'twitter_reply'))) {
						$noteArray = $db->getTwit($note['id']);
						$serial = unserialize(stripslashes($noteArray['serial']));
						extract($serial);
					}
					else $noteArray = $db->getNoteCombined($note['id']);
				}

				if ($type != 'direct_messages' && ($type != 'direct_message')) $favorite = $db->checkFavorite($_USER['ID'], $noteArray['ID']);
				else $favorite = false;

				$json = array('id' => (int)$noteArray['ID'],
					'created_at' => date('D M j G:i:s O Y', $noteArray['timestamp']),
					'text' => stripslashes(trim($noteArray['note']))
				);

				switch ($type) {
				case 'notes':
				case 'notess':
				case 'notes+t':
				case 'twitter';
					if ($type == 'twitter' || ($note['type'] == 'twitter' || ($note['type'] == 'twitter_reply'))) {
						$id_to_check = array('user' => '');
						if ($type == 'notes+t') $json['type'] = 'twitter';
					}
					else {
						$id_to_check = array('user' => $noteArray['user_id']);
						$json['source'] = $noteArray['from'];
						$json['truncated'] = false;

						$json['in_reply_to_status_id'] = ($noteArray['replying'] ? $noteArray['replying'] : null);
						$json['in_reply_to_user_id'] = ($noteArray['replying'] ? $noteArray['reply_user'][0] : null);
						$json['in_reply_to_screen_name'] = ($noteArray['replying'] ? $db->getUsernameFromID($noteArray['reply_user'][0]) : null);
						$json['geo'] = null;
						$json['favorited'] = (bool)$noteArray['favorite'];
						$json['contributors'] = null;
						$json['coordinates'] = null;
						if ($type == 'notes+t') $json['type'] = 'normal';
					}
					break;
				case 'direct_message':
				case 'direct_messages':
					$id_to_check = array(
						'sender' => $noteArray['user_id'],
						'recipient' => $noteArray['reply_user']
					);
					$json['sender_id'] = $noteArray['user_id'];
					$json['recipient_id'] =  $noteArray['reply_user'];
					$json['sender_screen_name'] = $noteArray['username'];
					$recipient_screen_name = $db->getUsernameFromID($noteArray['reply_user']);
					$json['recipient_screen_name'] = $recipient_screen_name;
					$json['text'] = str_replace('!'.$recipient_screen_name.' ', '', $json['text']);
				}
			}

			if ($type == 'users' || ($type == 'acc_verif' || ($type == 'user'))) $id_to_check = array('user'=>$note);
			elseif ($type == 'relationship') $id_to_check = array('target'=>$result[0][1], 'source'=>$result[0][0]);
			foreach ($id_to_check as $key => $userto) {
				if ($type == 'twitter' || ($note['type'] == 'twitter' || ($note['type'] == 'twitter_reply'))) {
					$array = array(
						'screen_name' => $serial['username'],
						'profile_image_url' => $serial['avatar']
					);
				}
				else {
					if ($type != 'relationship') {
						$profile = $db->getUserInfo($userto);
						$avatar = getAvatar($userto, 48);
						if (!$profile['customize']['background']) $background = $jk->base.'themes/transparency/img/bg.png'; else $background = get_background_url($profile['username'], $profile['customize']['background']);

						$array = array(
							'id' => (int)$profile['ID'],
							'name' => ($profile['realname'] ? $profile['realname'] : $profile['username']),
							'screen_name' => $profile['username'],
							'location' => ($profile['location'] ? $profile['location'] : null),
							'description' => ($profile['profile']['bio'] ? $profile['profile']['bio'] : ''),
							'profile_image_url' => $avatar,
							'url' => ($profile['profile']['url'] ? $profile['profile']['url'] : null),
							'protected' => (($profile['privacy']['show_notes'] < 3) ? true : false),
							'followers_count' => $db->countFollowers($profile['ID']),
							'created_at' => date('D M j G:i:s O Y', $profile['since']),
							'favourites_count' => $db->countNotes('favorites', $profile['ID']),
							'statuses_count' => $db->countNotes('archive', $profile['ID']),
							'friends_count' => $db->countFollowing($profile['ID']),
							'following' => $db->checkFollowing($_USER['ID'], $profile['ID']),
							'utc_offset' => null, //TODO
							'time_zone' => null, //TODO
							'profile_background_image_url' => $background,
							'profile_background_tile' => (($profile['customize']['background_style'] == 'repeat') ? true : false),
							'notifications' => false,
							'verified' => false,
							'lang' => (($profile['language'] == 'def' ? 'en' : $profile['language'])),
							'contributions_enabled' => false, //TODO
							'geo_enabled' => false //TODO
						);

						if ($type == 'users' || $type == 'user') {
							$viewable = checkViewableUser($_USER['ID'], $profile['ID'], 'show_notes');
							if ($viewable) {
								$resulta = $db->getNotes('archive', getStart(1), 1, $profile['ID']);
								$noteArray = $db->getNoteCombined($resulta[0]['id']);

								$array['status'] = array(
									'created_at' => date('D M j G:i:s O Y', $noteArray['timestamp']),
									'id' => $noteArray['ID'],
									'source' => $noteArray['from'],
									'truncated' => false,
									'in_reply_to_status_id' => ($noteArray['replying'] ? $noteArray['replying'] : null),
									'in_reply_to_user_id' => ($noteArray['replying'] ? $noteArray['reply_user'][0] : null),
									'favorited' => (bool)$noteArray['favorite'],
									'in_reply_to_screen_name' => ($noteArray['replying'] ? $db->getUsernameFromID($noteArray['reply_user'][0]) : null),
									'text' => $noteArray['note']
								);
							}
						}
					}
					else {
						$array = array();
						if ($key == 'target') $array['followed_by'] = ($db->checkFollowing($id_to_check['target'], $id_to_check['source']) ? true : false);
						else $array['followed_by'] = ($db->checkFollowing($id_to_check['source'], $id_to_check['target']) ? true : false);

						if ($key == 'target') $array['following'] = ($db->checkFollowing($id_to_check['source'], $id_to_check['target']) ? true : false);
						else $array['following'] = ($db->checkFollowing($id_to_check['target'], $id_to_check['source']) ? true : false);
						$array['screen_name'] = $db->getUsernameFromID($userto);
						$array['id'] = (int)$userto;
						if ($key == 'source') {
							$array['notifications_enabled'] = false;
							$array['blocking'] = false;
						}
					}
				}

				if ($type != 'user' && ($type != 'users')) $json[$key] = $array;
				else $json = $array;
			}
		}
		if ($type == 'relationship') $final = json_encode(array('relationship'=>$json));
		elseif ($type != 'ids') $final .= json_encode($json);
		if ($count < count($result)) $final .= ',';
	}
	header('Content-Type: text/javascript; charset=utf-8');
	if ($callback) echo $callback.'(';
	echo $final;
	if ($type != 'acc_verif' && ($type != 'notess' && ($type != 'user' && ($type != 'direct_message' && ($type != 'relationship'))))) echo ']';
	if ($callback) echo ');';
}

/**
 * It echoes a FOAF file for an user ID
 *
 * @param string $userID 
 * @return void
 * @author Marcos García
 */
function createFOAF($userID)
{
	global $db, $jk;
	$userInfo = $db->getUserInfo($userID);

	$followers_id = $db->getFollowingID($userInfo['ID']);
	$followers = array();
	foreach ($followers_id as $id) {
		$followers[] = $db->getUserInfo($id);
	}

	$XMLWriter = new XMLWriter();
	$XMLWriter->openURI('php://output');
	$XMLWriter->startDocument('1.0', 'UTF-8');
	$XMLWriter->setIndent(true);

	$XMLWriter->startElement('rdf:RDF');
	$XMLWriter->writeAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$XMLWriter->writeAttribute('xmlns:rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
	$XMLWriter->writeAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
	$XMLWriter->writeAttribute('xmlns:bio', 'http://purl.org/vocab/bio/0.1/');
	$XMLWriter->writeAttribute('xmlns:sioc', 'http://rdfs.org/sioc/ns#');
	$XMLWriter->writeAttribute('xmlns', 'http://xmlns.com/foaf/0.1/');

	$XMLWriter->startElement('PersonalProfileDocument');
	$XMLWriter->writeAttribute('rdf:about', '');
	$XMLWriter->startElement('maker');
	$XMLWriter->writeAttribute('rdf:resource', coreLink($userInfo['username']));
	$XMLWriter->endElement();
	$XMLWriter->startElement('primaryTopic');
	$XMLWriter->writeAttribute('rdf:resource', coreLink($userInfo['username']));
	$XMLWriter->endElement();
	$XMLWriter->endElement();
	$XMLWriter->startElement('Agent');
	$XMLWriter->writeAttribute('rdf:about', coreLink($userInfo['username']));
	$XMLWriter->writeElement('mbox_sha1sum', sha1('mailto:'.$userInfo['email']));
	if ($userInfo['realname']) $XMLWriter->writeElement('name', $userInfo['realname']);
	if ($userInfo['profile']['web']) {
		$XMLWriter->startElement('homepage');
		$XMLWriter->writeElement('rdf:resource', $userInfo['homepage']);
		$XMLWriter->endElement();
	}
	$XMLWriter->startElement('weblog');
	$XMLWriter->writeAttribute('rdf:resource', coreLink($userInfo['username']));
	$XMLWriter->endElement();
	if ($userInfo['profile']['bio']) $XMLWriter->writeElement('bio:olb', $userInfo['profile']['bio']);
	$XMLWriter->startElement('holdsAccount');
	$XMLWriter->startElement('OnlineAccount');
	$XMLWriter->writeAttribute('rdf:about', coreLink($userInfo['username']));
	$XMLWriter->startElement('accountServiceHomepage');
	$XMLWriter->writeAttribute('rdf:resource', $jk->base);
	$XMLWriter->endElement();
	$XMLWriter->writeElement('accountName', $userInfo['username']);
	$XMLWriter->startElement('accountProfilePage');
	$XMLWriter->writeAttribute('rdf:resource', coreLink($userInfo['username']));
	$XMLWriter->endElement();
	$XMLWriter->startElement('sioc:account_of');
	$XMLWriter->writeAttribute('rdf:resource', coreLink($userInfo['username']));
	$XMLWriter->endElement();
	foreach ($followers as $id) {
		$XMLWriter->startElement('sioc:follows');
		$XMLWriter->writeAttribute('rdf:resource', coreLink($id['username']));
		$XMLWriter->endElement();
	}
	$XMLWriter->endElement();
	$XMLWriter->endElement();
	$XMLWriter->endElement();
	foreach ($followers as $id) {
		$XMLWriter->startElement('Agent');
		$XMLWriter->writeAttribute('rdf:about', coreLink($id['username']));
		$XMLWriter->startElement('holdsAccount');
		$XMLWriter->startElement('OnlineAccount');
		$XMLWriter->writeAttribute('rdf:about', coreLink($id['username']));
		$XMLWriter->startElement('accountServiceHomepage');
		$XMLWriter->writeAttribute('rdf:resource', $jk->base);
		$XMLWriter->endElement();
		$XMLWriter->writeElement('accountName', $id['username']);
		$XMLWriter->startElement('accountProfilePage');
		$XMLWriter->writeAttribute('rdf:resource', coreLink($id['username']));
		$XMLWriter->endElement();
		$XMLWriter->startElement('sioc:account_of');
		$XMLWriter->writeAttribute('rdf:resource', coreLink($id['username']));
		$XMLWriter->endElement();
		$XMLWriter->startElement('sioc:follows');
		$XMLWriter->writeAttribute('rdf:resource', coreLink($userInfo['username']));
		$XMLWriter->endElement();
		$XMLWriter->endElement();
		$XMLWriter->endElement();
		$XMLWriter->startElement('rdfs:seeAlso');
		$XMLWriter->writeAttribute('rdf:resource', coreLink('foaf', $id['username']));
		$XMLWriter->endElement();
		$XMLWriter->endElement();
		$XMLWriter->startElement('PersonalProfileDocument');
		$XMLWriter->writeAttribute('rdf:about', coreLink('foaf', $id['username']));
		$XMLWriter->startElement('maker');
		$XMLWriter->writeAttribute('rdf:resource', coreLink($id['username']));
		$XMLWriter->endElement();
		$XMLWriter->startElement('primaryTopic');
		$XMLWriter->writeAttribute('rdf:resource', coreLink($id['username']));
		$XMLWriter->endElement();
		$XMLWriter->endElement();
	}
	$XMLWriter->endElement();

	$XMLWriter->flush();
}

/**
 * It echoes a RSS for a specified user
 *
 * @param string $result 
 * @param string $title 
 * @param string $desc 
 * @param string $link 
 * @return void
 * @author Marcos García
 */
function createRSS($result, $title, $desc, $link)
{
	global $db;
	global $jk;

	header('Content-Type: application/rss+xml;');

	$XMLWriter = new XMLWriter();
	$XMLWriter->openURI('php://output');
	$XMLWriter->startDocument('1.0', 'UTF-8');

	$XMLWriter->setIndent(true);

	$XMLWriter->startElement('rss');
	$XMLWriter->writeAttribute('version', '2.0');
	$XMLWriter->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
	$XMLWriter->writeAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
	$XMLWriter->writeAttribute('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
	$XMLWriter->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');

	$XMLWriter->startElement('channel');
	$XMLWriter->startElement('title');
	$XMLWriter->writeCData($jk->name.' '.$jk->separator.' '.$title);
	$XMLWriter->endElement();
	$XMLWriter->startElement('description');
	$XMLWriter->writeCData($desc);
	$XMLWriter->endElement();
	$XMLWriter->writeElement('link', coreLink($link));
	$XMLWriter->startElement('atom:link');
	$XMLWriter->writeAttribute('href', coreLink($link));
	$XMLWriter->writeAttribute('rel', 'self');
	$XMLWriter->writeAttribute('type', 'application/rss+xml');
	$XMLWriter->endElement();
	$XMLWriter->writeElement('generator', 'Jisko');
	$XMLWriter->writeElement('ttl', 10);
	$XMLWriter->startElement('image');
	$XMLWriter->writeElement('link', $jk->base);
	$XMLWriter->writeElement('title', $desc);
	$XMLWriter->writeElement('url', $jk->base.'static/img/logos/'.$jk->logo);
	$XMLWriter->endElement();


	foreach ($result as $note) {
		$array = $db->getNoteCombined($note['id']);

		$XMLWriter->startElement('item');
		$XMLWriter->startElement('title');
		$XMLWriter->writeCData(stripslashes($array['username'].': '.$array['note']));
		$XMLWriter->endElement();
		$XMLWriter->startElement('description');
		$XMLWriter->writeCData(utf8_htmlentities(stripslashes($array['username'].': '.$array['note'])));
		$XMLWriter->endElement();
		$XMLWriter->writeElement('pubDate', date('r', intval($array['timestamp'])));
		$XMLWriter->writeElement('link', stripslashes(coreLink($array['username'], $array['ID'])));
		$XMLWriter->writeElement('guid', stripslashes(coreLink($array['username'], $array['ID'])));
		$XMLWriter->writeElement('dc:creator', stripslashes($array['username']));
		$XMLWriter->startElement('source');
		$XMLWriter->writeAttribute('url', coreLink(array('u='.urlencode($array['username'])), 'rss', 'profile'));
		$XMLWriter->text($array['username'].'\'s notes feed');
		$XMLWriter->endElement();
		if ($array['attached_file']) {
			$XMLWriter->startElement('enclosure');
			$XMLWriter->writeAttribute('url', coreLink('download', $array['ID'], $array['attached_file']));
			$XMLWriter->endElement();
		}
		$XMLWriter->endElement();
	}
	$XMLWriter->endElement();
	$XMLWriter->endElement();
	$XMLWriter->flush();
}

/**
 * undocumented function
 *
 * @param string $input 
 * @return void
 * @author Ruben Díaz
 */
function utf8_htmlentities($input)
{
	return stripslashes(htmlentities($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * undocumented function
 *
 * @param string $script 
 * @return void
 * @author Ruben Díaz
 */
function fork($script)
{
	global $jk;
	$sock = fsockopen(parse_url($jk->base, PHP_URL_HOST), $_SERVER['SERVER_PORT'], $errno, $errstr, 1 );
	$base = parse_url($jk->base, PHP_URL_PATH);
	$line = "GET {$script} HTTP/1.0\r\n" . "Host: {$_SERVER['HTTP_HOST']}\r\n\r\n";
	if ($sock) {
		fputs($sock, $line);
		return true;
	}
	return false;
}

/**
 * undocumented function
 *
 * @param string $lang 
 * @return void
 * @author Ruben Díaz
 */
function deflang($lang)
{
	global $jk;
	if (empty($lang)) return $jk->default_lang;
	return $lang;
}

import('twitter/twitter.class');

/**
 * undocumented function
 *
 * @param string $every 
 * @return void
 * @author Ruben Díaz
 */
function updateTwitterNotes($every = 300)
{
	global $_USER;
	global $db;

	if ($_USER['twitter']['last_update']) {
		$diff = intval(time() - (int) $_USER['twitter']['last_update']);
		if ($diff >= $every) $check = true;
	}
	else $check = true;
	
	if ($check == true) {
		fork(coreLink(array('user='.$_USER['ID']), 'cron', 'update'));
		$db->updateTwitterOptions($_USER['ID'], array('last_update' => time()));
	}
}

function processTweets($userID, $type, $data)
{
	global $db;

	foreach ($data as $id => $update) {
		$hash = md5($userID.'_'.$id);
		$serial = serialize(array(
			'twitid' => $id,
			'username' => $update['username'],
			'avatar' => $update['avatar']
		));
		$db->newTweet($userID, $update['status'], $hash, $serial, $update['timestamp'], $type);
	}
}

/**
 * undocumented function
 *
 * @param string $auth 
 * @return void
 * @author Ruben Díaz
 */
function retrieveTweets($type = 'statuses/friends_timeline')
{
	global $USER;
	global $jk;
	
	$pages = array(1, 2);
	
	import('twitter/toauth.class');
			
	//Calling the tOAuth class with the user keys
	$connection = new tOAuth($jk->tw_consumerkey, $jk->tw_secretkey, $USER['twitter']['oauth_token'], $USER['twitter']['oauth_token_secret']);
	
	//The array that will contain all the notes
	$array = array();
	
	foreach ($pages as $page) {
		
		//We retrieve the notes
		$return = $connection->get($type, array('page' => $page));
		
		//We have to check if there was an error while retrieving the notes
		if (!$return['error']) {			
			foreach ($return as $note) {
			
				//And we store every note in $array, with it's ID as the key
				$array[$note['id']] = array(
					'username' => $note['user']['screen_name'],
					'avatar' => $note['user']['profile_image_url'],
					'status' => $note['text'],
					'timestamp' => strtotime($note['created_at'])
				);
			}
		}
	}
	
	//Then $array is sorted numerically...
	krsort($array, SORT_NUMERIC);
	
	//.. and we return it
	return $array;
}

/**
 * It returns a shorter URL than the specified one
 *
 * @param string $url 
 * @param string $service 
 * @return void
 * @author Marcos García
 */
function shorter_url($url, $service)
{
	$parser = parse_url($url[0]);
	if ($parser['host'] == $service) return $url[0];
	else {
		switch ($service) {
		case 'tinyurl.com':
			$ch = curl_init("http://tinyurl.com/api-create.php?url=".$url[0]);
			break;
		case '3.ly':
			if (strlen($jk->threely_apicode)) $ch = curl_init("http://3.ly/?api=".$jk->threely_apicode."&u=".urlencode($url[0]));
			else return $url[0];
			break;
		case 'ves.cat':
			$ch = curl_init("http://ves.cat/?url=".urlencode($url[0])."&format=json");
			break;
		case 'is.gd':
			$ch = curl_init("http://is.gd/api.php?longurl=".urlencode($url[0]));
			break;
		case 'pic.gd':
			$ch = curl_init("http://pic.gd/?module=ShortURL&file=Add&url=".urlencode($url[0])."&mode=API");
			break;
		case 'j.mp':
		case 'bit.ly':
			if ($jk->bitly_login && $jk->bitly_apicode) {
				if (strlen($jk->bitly_login) && strlen($jk->bitly_apicode)) {
					if ($service == 'bit.ly') $ch = curl_init("http://api.bit.ly/shorten?version=2.0.1&history=1&longUrl=".urlencode($url[0])."&login=".$jk->bitly_login."&apiKey=".$jk->bitly_apicode);
					else $ch = curl_init("http://api.j.mp/shorten?version=2.0.1&history=1&longUrl=".urlencode($url[0])."&login=".$jk->bitly_login."&apiKey=".$jk->bitly_apicode);
				}
				else return $url[0];
			}
			else return $url[0];
			break;
		case 'urlal.com':
			$ch = curl_init("http://urlal.com/?u=".urlencode($url[0])."&o=j");
			break;
		case 'u.nu':
			$ch = curl_init("http://u.nu/unu-api-simple?url=".urlencode($url[0]));
			break;
		case 'tinyarro':
			$ch = curl_init("http://tinyarro.ws/api-create.php?utfpure=1&url=".urlencode($url[0]));
			break;
		case 'url.ba':
			$ch = curl_init("http://url.ba/api.php?url=".urlencode($url[0]));
			break;
		case 'ta.gd':
			$ch = curl_init("http://tinyarro.ws/api-create.php?utfpure=1&host=".$service.'&url='.$url[0]);
			break;
		case 'wipi.es':
			$ch = curl_init("http://wipi.es/create.php?url=".urlencode($url[0]));
			break;
		case 'xrl.us':
			$ch = curl_init("http://metamark.net/api/rest/simple?long_url=".urlencode($url[0]));
			break;
		case 'cort.as':
			$ch = curl_init("http://www.soitu.es/cortas/encode.pl?u=".urlencode($url[0])."&r=json");
			break;
		case 'ir.pe':
			$ch = curl_init("http://ir.pe/?url=".urlencode($url[0])."&api=1");
			break;
		case 'urli.nl':
			$ch = curl_init("http://urli.nl/api.php?format=simple&action=shorturl&url=".urlencode($url[0]));
			break;
		case 'recorta.com':
			$ch = curl_init("http://recorta.com/api.php?url=".urlencode($url[0]));
			break;
		default:
			return $url[0];
			break;
		}
	}

	if ($ch) {
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);

		$short_url = curl_exec($ch);
		curl_close($ch);

		if (empty($short_url)) return $url[0];
		else {
			switch ($service) {
			case 'ves.cat':
				$json = json_decode($short_url);

				if ($json->status != 'Ok') return $url[0];
				else return $json->link;
				break;
			case 'bit.ly':
			case 'j.mp':
				$json = json_decode($short_url, true);

				if ($json['statusCode'] == 'ERROR') return $url[0];
				else return $json['results'][$url[0]]['shortUrl'];
				break;
			case 'urlal.com':
				$json = json_decode($short_url);

				if ($json->Status != 0) return $url[0];
				else return $json->Message;
				break;
			case 'cort.as':
				$json = json_decode($short_url);
				if ($json['status'] != 'ok') return $url[0];
				else return $json['urlCortas'];
				break;
			case 'url.ba':
				$json = json_decode($short_url);
				if ($json->short) return 'http://url.ba/'.$json->short;
				else return $url[0];
				break;
			case 'ta.gd':
			case 'urli.nl':
			case 'tinyarro':
			case 'u.nu':

			case 'is.gd':
			case 'pic.gd':
			case 'wipi.es':
			case 'xrl.us':
			case '3.ly':
			case 'recorta.com':
			case 'tinyurl.com':
			case 'ir.pe':
			default:
				if (filter_var(trim($short_url), FILTER_VALIDATE_URL)) return $short_url;
				else return $url[0];
				break;
			}
		}
	}
}

/**
 * It returns a list of accepted languages
 *
 * @return void
 * @author Marcos García
 */
function list_isocode_languages()
{
	return array(
		'ar' => 'Arabic',
		'an' => 'Aragonese',
		'ast' => 'Asturian',
		'eu' => 'Basque',
		'pt_BR' => 'Brazilian Portuguese',
		'bg' => 'Bulgarian',
		'ca' => 'Catalan',
		'zh_CN' => 'Chinese (Simplified)',
		'hr' => 'Croatian',
		'cs' => 'Czech',
		'nl' => 'Dutch',
		'en_GB' => 'English (United Kingdom)',
		'eo' => 'Esperanto',
		'fr' => 'French',
		'gl' => 'Galician',
		'de' => 'German',
		'el' => 'Greek',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'la' => 'Latin',
		'nds' => 'Low German',
		'mn' => 'Mongolian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'ro' => 'Romanian',
		'ru' => 'Russian',
		'sk' => 'Slovak',
		'es' => 'Spanish',
		'sv' => 'Swedish',
		'th' => 'Thai',
		'tr' => 'Turkish',
		'def' => 'English',
		'vi' => 'Vietnamese'
	);
}

/**
 * It replaces some patterns with dynamic content
 *
 * @param string $text 
 * @return void
 * @author Marcos García
 */
function replacePatterns($text)
{
	global $jk;
	$lang = list_isocode_languages();
	$array1 = array('%url%', '%name%', '%mobile_url%', '%faq_url%', '%language%');
	$array2 = array($jk->base, $jk->name, coreLink('mobile'), coreLink('faq'), $lang[$jk->default_lang]);
	return str_replace($array1, $array2, $text);
}

/**
 * It checks if $userID has permission to view some content of $userID2
 *
 * @param string $userID 
 * @param string $userID2 
 * @param string $type 
 * @return void
 * @author Marcos García
 */
function checkViewableUser($userID, $userID2, $type)
{
	global $_USER;
	global $db;
	
	if ($userID == $userID2) return true;
	else {
		if ($userID2 != $_USER['ID']) $result = $db->getUserOptions($userID2, array('privacy'));
		else $result = array('privacy' => $_USER['privacy']);
	
		if ($result['privacy']) {
			if (!isset($result['privacy'][$type])) $viewable = true;
			else {
				if ($result['privacy'][$type] == '3') $viewable = true;
				elseif ($result['privacy'][$type] == '2') {
					if ($_USER && $db->checkFollowing($userID, $userID2)) $viewable = true;
				}
				elseif ($result['privacy'][$type] == '1') {
					if ($_USER && $db->checkFollowing($userID2, $userID)) $viewable = true;
				}
				elseif ($result['privacy'][$type] == '0') $viewable = false;
			}
	
			if ($viewable) return $viewable;
			else return false;
		}
		else return true;
	}
}

/**
 * It returns a list of users that can see the content of $userID
 *
 * @param string $userID 
 * @param string $type 
 * @return void
 * @author Marcos García
 */
function getViewableUsers($userID, $type)
{
	global $db;

	$userInfo = $db->getUserOptions($userID, array('privacy'));
	if ($userInfo) {
		if (is_array($userInfo['privacy'])) {
			if ($userInfo['privacy'][$type] == 3 || ($userInfo['privacy'][$type] == 1)) $result = $db->getFollowersID($userID);
			elseif ($userInfo['privacy'][$type] == 2) $result = $db->getFriendCreator($userID, true);
			elseif ($userInfo['privacy'][$type] == 0) $result = array($userID);

			return $result;
		}
		else return array_merge(array($userID), $db->getFollowersID($userID));
	}
	else return false;
}

/**
 * It closes the current session for $userID
 *
 * @param string $userID 
 * @return void
 * @author Marcos García
 */
function closeSession($userID)
{
	global $db;
	global $jk;
	$status = $db->deleteSession($userID);
	if ($status) {
		setcookie('jisko_'.md5($jk->base), '', time()-3600);
		return true;
	}
	else return false;
}

// 
// Posted at http://php.net/opendir
/**
 * It removes a directory and its subdirectories
 *
 * Some code was retrieved from the function file_array() by Jamon Holmgren. (jamon at clearsightdesign dot com)
 * Posted at http://php.net/opendir
 *
 * @param string $dir 
 * @return void
 * @author Jamon Holmgren
 * @author Marcos García.
 */
function removeDir($dir)
{
	if (is_dir($dir)) {
		if ($dirfd = opendir($dir)) {
			while (($file = readdir($dirfd)) !== false) {
				if ($file != '..' && ($file != '.')) {
					if (is_dir($dir.'/'.$file)) {
						removeDir($dir.'/'.$file);
					}
					else {
						unlink($dir.'/'.$file);
					}
				}
			}
			closedir($dir);

			if (rmdir($dir)) return true;
			else return false;
		}
		else return false;
	}
	else return false;
}

/**
 * It returns wether the confirmation email config is active or not
 *
 * @return void
 * @author Marcos García
 */
function isEmailConfirmationEnabled()
{
	global $jk;
	return (bool) $jk->no_confirmation_email;
}

/**
 * It returns wether the Facebook integration is enabled of not
 *
 * @return void
 * @author Marcos García
 */
function isFacebookEnabled()
{
	global $jk;
	if (!empty($jk->fb_apikey)) {
		if (!empty($jk->fb_secretkey)) return true;
		else return false;
	}
	else return false;
}

function recaptcha_enabled()
{
	global $jk;
	if (!empty($jk->recaptcha_publickey)) return true;
	else false;
}

/**
 * It returns the number of characters that a string has
 *
 * @param string $string 
 * @return void
 * @author Marcos García
 */
function countChars($string)
{
	global $jk;
	if ($jk->enable_mbstring == true) $count = mb_strlen($string);
	else $count = strlen($string);
	return (int) $count;
}

/**
 * It returns the URL of the avatar of the specified user
 *
 * @param string $userID 
 * @param string $size 
 * @return void
 * @author Marcos García
 */
function getAvatar($userID, $size = 150)
{
	global $_USER;
	global $db, $jk;

	if ($_USER['ID'] == $userID) $userInfo = array('gravatar' => $_USER['gravatar'], 'email' => $_USER['email'], 'avatar' => $_USER['avatar'], 'username' => $_USER['username']);
	else $userInfo = $db->getUserOptions($userID, array('gravatar', 'email', 'avatar', 'username'));
	
	if ($userInfo) {
		if ($userInfo['gravatar']) {
			$return = 'http://gravatar.com/avatar/'.md5($userInfo['email']).'.jpg?s='.$size;
		}
		else {
			if ($size == 150) $which = 'side';
			elseif ($size == 48) $which = 'note';
			elseif ($size == 24) $which = 'follow';

			if ($userInfo['avatar']) {
				$avatar_info = pathinfo(PATH.'users_files/'.$userInfo['username'].'/img/avatar/'.$userInfo['avatar']);
				return $jk->base.'users_files/'.$userInfo['username'].'/img/avatar/'.$avatar_info['filename'].'_'.$which.'.'.$avatar_info['extension'];
				$return = get_avatar_url($userInfo['username'], $userInfo['avatar'], $which);
			}
			else $return = $jk->base."static/img/avatar/default_$which.png";
		}

		return $return;
	}
	else return false;
}

/**
 * It creates a link to the specified page
 *
 * @return void
 * @author Marcos García
 */
function coreLink()
{
	global $jk;
	
	$args = func_get_args();
	
	if (is_array($args[0])) $get = $args[0];
	else {
		$get = array();
		array_unshift($args, array());
	}
	$url = $jk->base;
	
	if ($jk->cleanUrls == true) {
		for ($i=1;$i <= (count($args) - 1);$i++) {
			if ($i == 1) $url .= $args[$i];
			elseif ($i < count($args)) $url .= '/'.$args[$i];
		}
		for ($i=0;$i <= (count($get) - 1);$i++) {
			if ($i == 0) $url .= '?'.$get[$i];
			else $url .= '&'.$get[$i];
		}
	}
	else {
		for ($i=1;$i <= (count($args) - 1);$i++) {
			if ($i == 1) $url .= '?module='.$args[$i];
			elseif ($i == 2) $url .= '&params='.$args[$i];
			elseif ($i == 3) $url .= '&extra='.$args[$i];
			elseif ($i == 4) $url .= '&extra2='.$args[$i];
		}
		for ($i=0;$i <= (count($get) - 1);$i++) {
			$url .= '&'.$get[$i];
		}
	}
	return $url;
}

/**
 * It returns if the user has twitter integration enabled or not
 *
 * @param string $userInfo 
 * @return void
 * @author Marcos García
 */
function has_twitter($userInfo = false)
{
	global $_USER;
	if (!$userInfo) $userInfo = $_USER;
	
	if ($userInfo['twitter']['oauth_token'] && $userInfo['twitter']['oauth_token_secret']) {
		if (!empty($userInfo['twitter']['oauth_token']) && !empty($userInfo['twitter']['oauth_token_secret'])) return true;
		else return false;
	}
	else return false;
}

/**
 * Checks if the $userID has enough $type permissions
 *
 * @param string $userID 
 * @param string $type 
 * @return void
 * @author Marcos García
 */
function checkAdminPermissions($userID, $type)
{
	global $db;
	
	$query = $db->send("SELECT `$type` FROM `permissions` WHERE `userid`='$userID'");
	if (mysql_num_rows($query)) {
		return (bool) mysql_result($query, 0);
	}
}

/**
 * Returns the available shorter services
 *
 * @return void
 * @author Marcos García
 */
function availableShorterServices()
{
	$allowed_shorters = array_merge(array('none'), array('3.ly', 'ves.cat', 'pic.gd', 'is.gd', 'bit.ly', 'j.mp', 'urlal.com', 'u.nu', 'ta.gd', 'tinyurl.com', 'wipi.es', 'xrl.us', 'tinyarro', 'cort.as', 'url.ba', 'ir.pe', 'urli.nl', 'recorta.com'));
	$names = array('none' => __('Disabled'), 'tinyarro' => 'Tinyarro ('.__('random').')');

	$return = array();
	foreach ($allowed_shorters as $shorter) {
		if (array_key_exists($shorter, $names)) $return[$shorter] = $names[$shorter];
		else $return[$shorter] = $shorter;
	}
	return $return;
}

/**
 * Returns if $username is a valid username
 *
 * @param string $username 
 * @param string $str 
 * @return void
 * @author Marcos García
 */
function validUsername($username, $str = false) {
	global $jk;
	global $db;
	
	$username = trim($username);
	
	$forbidden = array('home', 'login', 'register', 'logout', 'notes', 'drop', 'forgot', 'avatar', 'invite', 'preferences', 'follow', 'favorites', 'public', 'profile', 'rss', 'followers', 'following', 'search', 'cron', 'download', 'post', 'ajax', 'mobile', 'report', 'group', 'groups', 'direct_messages', 'account', 'trouble_login', 'resend_mail', 'tos', 'faq', 'admin');
	
	if ($db->checkUsername($username) || in_array($username, $forbidden)) {
		if ($str == true) return 'busy';
		else return false;
	}
	elseif ((countChars($username) > 20) || (!preg_match('/^[a-z_\-0-9]{3,15}$/i', $username))) {
		if ($str == true) return 'invalid';
		else return false;
	}
	else {
		if ($str == true) return 'valid';
		else return true;
	}
}

?>