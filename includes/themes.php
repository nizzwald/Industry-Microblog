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

function get_menubar_extra_links($spacer)
{
	global $globals;

	$tex = '';
	foreach ($globals['menubar_links'] as $key=>$value) {
		$tex .= '<a href="'.anchor($value).'">'.$key.'</a> '.$spacer.' ';
	}
	echo $tex;
}

function unread_privates()
{
	global $unread_privates;
	if ($unread_privates > 0) return $unread_privates;
	else false;
}

function unread_replies()
{
	global $unread_replies;
	if ($unread_replies > 0) return $unread_replies;
	else false;
}

function get_permalink($location)
{
	global $_USER;
	global $jk;
	if ($_USER['theme']) echo $jk->base.'themes/'.$_USER['theme'].'/'.$location;
	else echo $jk->base.'themes/'.$jk->default_theme.'/'.$location;
}

function get_type_sidebar()
{
	global $sidebar;
	return $sidebar;
}

function get_queries()
{
	global $db;
	echo $db->queries;
}

function get_ignored()
{
	global $_USER;
	return $_USER['ignored'];
}

function is_archive()
{
	if (MODULE == 'notes' && (PARAMS == 'archive')) return true;
	else false;
}

function is_privates()
{
	if (MODULE == 'notes') {
		if (PARAMS == 'private' || (PARAMS == 'private_sent')) return true;
		else false;
	}
}

function is_replies()
{
	if (MODULE == 'notes' && (PARAMS == 'replies')) return true;
	elseif (PARAMS == 'replies') return true;
	else return false;
}

function is_home()
{
	if (MODULE == 'notes') {
		if (!PARAMS || (PARAMS == 'friends')) return true;
		else false;
	}
}

function is_twitter()
{
	if (MODULE == 'notes') {
		if (PARAMS == 'twitter' || (PARAMS == 'twitter_replies')) return true;
		else return false;
	}
}

function is_admin()
{
	global $_USER;
	if (checkAdminPermissions($_USER['ID'], 'can_panel')) return true;
	else return false;
}

function count_ignored()
{
	global $_USER;
	return count($_USER['ignored']);
}

function count_following($ID = null, $return = false)
{
	global $db;
	if (!$return) echo $db->countFollowing($ID);
	else return $db->countFollowing($ID);
}

function count_followers($ID = null)
{
	global $db;
	echo $db->countFollowers($ID);
}

function count_unread_followers()
{
	global $db;
	global $_USER;
	return $db->countUnreadFollowers($_USER['ID']);
}

function count_favorites($ID)
{
	global $db;
	echo $db->countNotes('favorites', $ID);
}

function count_privates($userID)
{
	global $db;
	echo $db->countNotes('private', $userID);
}

function count_notes($ID)
{
	global $db;
	echo $db->countNotes('archive', $ID);
}

function show($abc)
{
	echo utf8_htmlentities($abc);
}

function get_avatar($userID = null, $size = 150, $return = false)
{
	if ($return) return getAvatar($userID, $size);
	else echo getAvatar($userID, $size);
}

function get_logo()
{
	global $jk;
	echo $jk->base.'static/img/logos/'.$jk->logo;
}

function count_users($type = null)
{
	global $db;

	switch ($type) {
	case 'active':
		$query = $db->send("SELECT COUNT(*) FROM `users` WHERE `status`='ok'");
		break;
	case 'banned':
		$query = $db->send("SELECT COUNT(*) FROM `users` WHERE `status`='banned'");
		break;
	case 'nc':
		$query = $db->send("SELECT COUNT(*) FROM `users` WHERE `status`='nc'");
		break;
	default:
		$query = $db->send("SELECT COUNT(*) FROM `users`");
		break;
	}
	return mysql_result($query, 0);
}

function get_following($ID, $number)
{
	global $db;
	return $db->getFollowing($ID, 0, $number);
}

function anchor()
{
	$args = func_get_args();
	if (!is_array($args[0])) echo call_user_func_array('coreLink', array_merge(array(array()), $args));
	else echo call_user_func_array('coreLink', $args);
}

function logged_username()
{
	global $_USER;
	echo $_USER['username'];
}

function logged_email()
{
	global $_USER;
	echo $_USER['email'];
}

function load_feeds($before = '', $inside = '', $after = '')
{
	global $_USER;
	global $module;
	global $jk;

	$feeds['application/rss+xml'] = array(__('Public notes'), coreLink('api', 'statuses', 'public_timeline.rss'));
	if ($_USER) {
		$feeds['application/rss+xml'] = array(__('My notes'), coreLink(array('?u='.$_USER['username']), 'rss', 'profile'));
		$feeds['application/rss+xml'] = array(__('Friends timeline'), coreLink(array('id='.$_USER['ID']), 'rss', 'friends'));
	}
	if ($module == 'permalink') {
		$feeds['application/json+oembed'] = array(__($jk->title.' '.$jk->separator.' '.$jk->name), coreLink(array('url='.urlencode(coreLink($jk->base, MODULE, PARAMS)), 'format=json'), 'oembed'));
		$feeds['text/xml+oembed'] = array(__($jk->title.' '.$jk->separator.' '.$jk->name), coreLink(array('url='.urlencode(coreLink($jk->base, MODULE, PARAMS)), 'format=xml'), 'oembed'));
	}
	echo $before;
	foreach ($feeds as $title => $feed) {
		echo '<link rel="alternate" type="'.$title.'" title="'.$feed[0].' (RSS 2.0)" href="'.$feed[1].'"';
		echo $inside;
		echo ' />';
	}
	echo $after;
}

function parse_faq()
{
	global $db;
	$faq = $db->getJiskoSettings(array('faq_content'));

	echo replacePatterns(stripslashes($faq['faq_content']));
}

function parse_homepage()
{
	global $db;
	$content = $db->getJiskoSettings(array('homepage_content'));

	echo replacePatterns(stripslashes($content['homepage_content']));
}

function parse_tos()
{
	global $db;
	global $jk;
	$content = $db->getJiskoSettings(array('tos_content'));

	echo replacePatterns(stripslashes($content['tos_content']));
}

function load_url_shorters()
{
	global $jk;

	if ($jk->allowed_shorter_service) $allowed_shorters = array_merge(array('none'), $jk->allowed_shorter_service);
	else $allowed_shorters = array('none');
	
	$names = array('none' => __('Disabled'), 'tinyarro' => 'Tinyarro ('.__('random').')');

	$return = array();
	foreach ($allowed_shorters as $shorter) {
		if (array_key_exists($shorter, $names)) $return[$shorter] = $names[$shorter];
		else $return[$shorter] = $shorter;
	}
	return $return;
}

function load_themes()
{
	global $jk;
	$return = array();
	foreach ($jk->allowed_themes as $thm) {
		$return[$thm] = ucfirst(str_replace('_', '', $thm));
	}
	return $return;
}

function load_background_styles()
{
	return array('normal'=>__('Normal'), 'repeat'=>__('Repeat'), 'centered'=>__('Centered'), 'fixed'=>__('Fixed'));
}

function load_privacy_options()
{
	return array(false => __('Everyone'), '3' => __('Everyone'), '2' => __('Following'), '1' => __('Followers'), '0' => __('Nobody'));
}

function t($string)
{
	$args = func_get_args();

	if (count($args) >= 2) {
		$args = array_splice($args, 1);
		echo vsprintf(__($string), $args);
	}
	else echo __($string);
}

function nt($string1, $string2, $number)
{
	echo sprintf(ngettext($string1, $string2, $number), $number);
}

function display_title()
{
	global $jk;
	if (isset($jk->title)) echo $jk->title.' '.$jk->separator.' '.$jk->name;
	else echo $jk->name;
}

function loadCustomCSS()
{
	global $_USER;
	global $jk;
	global $sidebar;

	echo '<style type="text/css">';
	if ($jk->user('ID') == $_USER['ID']) {
		if ($_USER['customize']['background']) {
			echo 'body {background: url('.$jk->base.'users_files/'.$_USER['username'].'/img/background/bg.'.$_USER['customize']['background'].')';
			switch ($_USER['customize']['background_style']) {
			case 'normal':
				echo ' no-repeat fixed';
				break;
			case 'centered':
				echo ' top center no-repeat';
				break;
			case 'fixed':
				echo ' fixed';
				break;
			}
			echo ';}';
		}

		if ($sidebar != 'no_sidebar') {
			if ($_USER['customize']['profile_sidebar_color']) echo '.content_right_top { background-color: '.$_USER['customize']['profile_sidebar_color'].' !important; }';
			if ($_USER['customize']['profile_links_color']) echo 'A { color: '.$_USER['customize']['profile_links_color'].' !important; } .content_left_top A { color: '.$_USER['customize']['profile_links_color'].' !important; }';
			if ($_USER['customize']['profile_background_color']) echo '.content_left_top { background-color: '.$_USER['customize']['profile_background_color'].' !important; }';
			if ($_USER['customize']['profile_sidebar_text_color']) echo '.content_right_top * { color: '.$_USER['customize']['profile_sidebar_text_color'].' !important; }';
			if ($_USER['customize']['profile_text_color']) echo '.content_left_top * { color: '.$_USER['customize']['profile_text_color'].' !important; }';
		}
	}
	else {
		if ($jk->user('customize_background')) {
			echo 'body {background: url('.$jk->base.'users_files/'.$jk->user('username').'/img/background/bg.'.$jk->user('customize_background').')';
			switch ($jk->user('customize_background_style')) {
			case 'normal':
				echo ' no-repeat fixed';
				break;
			case 'centered':
				echo ' top center no-repeat';
				break;
			case 'fixed':
				echo ' fixed';
				break;
			}
			echo ';}';
		}

		if ($sidebar != 'no_sidebar') {
			if ($jk->user('customize_profile_text_color')) echo '.content_left_top * { color: '.$jk->user('customize_profile_text_color').' !important; }';
			if ($jk->user('customize_profile_sidebar_color')) echo '.content_right_top { background-color: '.$jk->user('customize_profile_sidebar_color').' !important; }';
			if ($jk->user('customize_profile_links_color')) echo 'A { color: '.$jk->user('customize_profile_links_color').' !important; }';
			if ($jk->user('customize_profile_background_color')) echo '.content_left_top { background-color: '.$jk->user('customize_profile_background_color').' !important; }';
			if ($jk->user('customize_profile_sidebar_text_color')) echo '.content_right_top * { color: '.$jk->user('customize_profile_sidebar_text_color').' !important; }';
		}
	}
	echo '</style>';
}

function _date_format($timestamp)
{
	date_default_timezone_set('America/New_York');
    echo date('d/m/Y', $timestamp);
}

function get_current_tab($snd = false)
{
	if (!$snd) return PARAMS;
	else return EXTRA;
}

function calc_tagWritingRate($notes, $since)
{
	return round(($notes / (time() - $since) * 60), 2);
}

function loadNote($note)
{
	global $db;
	global $_USER;

	if ($note['type'] == 'twitter' || ($note['type'] == 'twitter_reply')) {
		$result = $db->getTwit($note['id']);
		$serial = unserialize(stripslashes($result['serial']));
		return array_merge($result, $serial, array('ntype'=>$note['type'], 'viewable'=>true));
	}
	else {
		$result = $db->getNoteCombined($note['id']);
		if ($result) {
			if ($result['user_id'] == $_USER['ID']) $viewable = true;
			else {
				$viewable = checkViewableUser($_USER['ID'], $result['user_id'], 'show_notes');
			}
	
			if ($viewable) {
				if ($result['type'] == 'private') {
					$result['reply_user'] = array($result['reply_user']);
					if ($result['user_id'] == $_USER['ID']) {
						unset($result['username'], $result['profile'], $result['avatar']);
						$infoa = $db->getUserOptions($result['reply_user'][0], array('username', 'profile', 'avatar'));
						$result['user_id'] = $result['reply_user'][0];
						$result = array_merge($result, $infoa);
	
					}
				}
				$is_favorite = array('is_favorite' => $db->checkFavorite($_USER['ID'], $note['id']));
				return array_merge($result, $is_favorite, array('ntype'=>'normal', 'viewable'=>true));
			}
			else return array('viewable', false);
		}
		else return array();
	}
}

function loadUser($note)
{
	global $db;
	return $db->getUserInfoNote($note);
}

function countReplies($note)
{
	global $db;
	return $db->getNumRepliesNote($note);
}

function get_replies($note)
{
	global $db;
	$odgovori = $db->getRepliesNote($note);
	if ($odgovori > 0) {
		foreach ($odgovori as $post) showNote(array('id'=>$post));
	}
}

function tos_enabled()
{
	global $jk;
	return (bool) $jk->tos;
}

function get_trending_tags($before, $before_tag, $after_tag, $after)
{
	global $db;
	$query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n WHERE (UNIX_TIMESTAMP() - `timestamp`) < 86400 GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT 5");
	if (mysql_num_rows($query) > 0) {
		echo $before;
		while ($row = mysql_fetch_row($query)) {
			echo $before_tag.'<a href="'.coreLink('tag', $row[0]).'">#'.$row[0].'</a>'.$after_tag;
		}
		echo $after;
	}
	else return '';
}

function get_user_tags($user, $before, $before_tag, $after_tag, $after)
{
	global $db;
	$query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n WHERE `poster`='".(int)$user."' GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT 5");
	if (mysql_num_rows($query) > 0) {
		echo $before;
		while ($row = mysql_fetch_row($query)) {
			echo $before_tag.'<a href="'.coreLink('tag',$row[0]).'">#'.$row[0].'</a>'.$after_tag;
		}
		echo $after;
	}
	else return '';
}

function get_last_seen($last_seen)
{
	$calc = time() - $last_seen;
	if ($calc > 2678400) return __('more than 1 month');
	else {
		if ($calc < 300) return 'online';
		else return showTimeAgo($last_seen);
	}
}

function count_trending_tags()
{
	global $db;
	$query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n WHERE (UNIX_TIMESTAMP() - `timestamp`) < 86400 GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT 5");
	return mysql_num_rows($query);
}

function count_user_tags($id)
{
	global $db;
	$query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n WHERE `poster`='".(int)$id."' GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT 5");
	return mysql_num_rows($query);
}

function is_ignored($user1)
{
	global $_USER;
	global $db;

	if (!in_array($user1, $_USER['ignored'])) return false;
	else return true;
}

function is_following($user1, $user2)
{
	global $db;

	if ($db->checkFollowing($user1, $user2)) return true;
	return false;
}

function load_recaptcha()
{
	global $jk;
	if (recaptcha_enabled()) {
		import('recaptchalib');
		echo recaptcha_get_html($jk->recaptcha_publickey);
	}
}

function get_tagsStatsByTime($type, $count = 5)
{
	global $db;

	if ($type == 'week') $query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n WHERE (UNIX_TIMESTAMP() - `timestamp`) < 604800 GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT $count");
	elseif ($type == 'today') $query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n WHERE (UNIX_TIMESTAMP() - `timestamp`) < 86400 GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT $count");
	elseif ($type == 'everytime') $query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT $count");
	elseif ($type == 'month') $query = $db->send("SELECT `tag`, COUNT(`id`) FROM tags_n WHERE (UNIX_TIMESTAMP() - `timestamp`) < 18144000 GROUP BY `tag` ORDER BY COUNT(`id`) DESC LIMIT $count");

	$return = array();
	while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$return[] = $row;
	}

	return $return;
}

function get_tagsStatsWrRate($count = 5)
{
	global $db;

	$query = $db->send("SELECT t2.tag, t1.name, (UNIX_TIMESTAMP() - t1.timestamp) as `timestamp`, COUNT(t2.id) as `count`, ROUND((COUNT(t2.id)/(UNIX_TIMESTAMP() - t1.timestamp))*60, 2) as `calc` FROM `tags_c` AS t1, `tags_n` AS t2 WHERE t1.name = t2.tag GROUP BY t2.tag ORDER BY `calc` DESC LIMIT $count");

	$return = array();
	while ($row = mysql_fetch_array($query)) {
		$return[] = $row;
	}

	return $return;
}

function get_tagsStatsFounder($count = 5)
{
	global $db;

	$query = $db->send("SELECT founder, name, COUNT(name) FROM tags_c GROUP BY founder ORDER BY COUNT(name) DESC LIMIT $count");

	$return = array();
	while ($row = mysql_fetch_array($query)) {
		$return[] = $row;
	}

	return $return;
}

function get_usernameFromID($id)
{
	global $db;
	return $db->getUsernameFromID($id);
}

function return_languages()
{
	$languages = list_isocode_languages();
	$array = array();

	if ($dirfd = opendir(PATH.'includes/languages')) {
		while ($dir = readdir($dirfd)) {
			if (!is_file($dir)) {
				if ($languages[$dir]) {
					$array[$dir] = __($languages[$dir]);
				}
			}
			else continue;
		}
		$array['def'] = __('English');
		asort($array);
		return $array;
	}
	else return array();
}

function check_favorite($userID, $noteID)
{
	global $db;
	return $db->checkFavorite($userID, $noteID);
}

function is_logged()
{
	global $_USER;
	if ($_USER) return true;
	else return false;
}

function following($userID)
{
	global $_USER;
	global $db;
	if ($db->checkFollowing($_USER['ID'], $userID)) return true;
	else return false;
}

function ignoring($userID)
{
	global $_USER;
	if (in_array($userID, $_USER['ignored'])) return true;
	else return false;
}

function count_everytime_notes()
{
	global $db;
	return $db->countNotes('everytime');
}

function get_last_registered_user()
{
	global $db;
	$query = $db->send("SELECT `username` FROM `users` WHERE `status`='ok' ORDER BY `ID` DESC LIMIT 1");
	if (mysql_num_rows($query)) return mysql_result($query, 0);
	else return false;
}

function getLastNote()
{
	global $db;
	global $_USER;
	
	if ($_USER) {
		$userID = $_USER['ID'];
		$ignored = $_USER['ignored'];
	}
	else {
		$userID = false;
		$ignored = false;
	}
	
	$return = $db->getNotes('friends', getStart(1), 1, $userID, $ignored, false, false, false);

	if ($return[0]['id']) return $return[0]['id']; else return 'false';
}

?>