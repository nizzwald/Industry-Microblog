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

function showNoteMobile($row, $no_moreinfo_link = false, $no_reply_link = false)
{
	global $db;
	global $_USER;

	if ($row['type'] == 'twitter' || $row['type'] == 'twitter_reply') return showTwitMobile($row);
	else {
		$viewable = checkViewableUser($_USER['ID'], $result['from'], 'show_notes');
		if ($viewable) {
			$noteInfo = $db->getNoteCombined($row['id']);
			if (!$noteInfo) return false;
			extract($noteInfo);

			$note = processNote($noteInfo, false, 'mobile', true);

			echo '<li';
			if ($type != 'private' && in_array($_USER['ID'], $reply_user)) echo ' style="background-color:#FFF8AF"';
			echo '><em class="us">';
			echo '<a href="'.coreLink('mobile', $username).'">'.$username.'</a></em>';
			echo '<em class="nl"> '.$note.'</em>';
			echo '<em class="t"> '.showTimeAgo($timestamp).'</em>';
			if ($_USER) {
				if (!$no_reply_link && ($_USER['ID'] != $noteInfo['user_id'])) {
					if ($noteInfo['type'] == 'private' && ($noteInfo['user_id'] != $_USER['ID'])) $r_link = true;
					elseif ($noteInfo['type'] == 'public') $r_link = true;

					if ($r_link) echo ' <a href="'.coreLink(array('id='.$row['id']), 'mobile', 'reply').'" class="act">'.__('Reply').'</a>';
				}
				if (!$no_moreinfo_link && ($noteInfo['type'] != 'private')) echo ' <a href="'.coreLink('mobile', 'status', $row['id']).'" class="act">'.__('More info').'</a>';
				echo '</li>';
			}
		}
	}
}

function showNoteMobileTouch($row, $reducedAjax = false)
{
	global $db;
	global $_USER;

	if ($row['type'] == 'twitter' || $row['type'] == 'twitter_reply') return showTwitMobileTouch($row, $reducedAjax);
	else {
		$viewable = checkViewableUser($_USER['ID'], $result['from'], 'show_notes');
		if ($viewable) {
			$noteInfo = $db->getNoteCombined($row['id']);
			if (!$noteInfo) return false;
			extract($noteInfo);

			$note = processNote($noteInfo, false, 'touch', true);

			if ($reducedAjax) {
				$return = array();
				$return['text'] = $note;
				$return['id'] = $ID;
				$return['time_ago'] = showTimeAgo($timestamp);
				$return['username'] = $username;
				if (preg_match("/@\b".$_USER['username']."\b/i", $note) == 1) $return['replying'] = true;
				if ($attached_file) $return['attached_file'] = $attached_file;
				return $return;
			}
			else {
				echo '<tr><td onclick="location.href=\''.coreLink('mobile', 'status', $row['id']).'\'"';

				if ($_USER && is_array($reply_user)) {
					if (in_array($_USER['ID'], $reply_user)) echo ' style="background-color: #FFF8AF"';
				}
				echo '><p id="np"><a href="'.coreLink('mobile', $username).'" class="user">'.$username.'</a> '.stripslashes($note).'<em class="t"> '.showTimeAgo($timestamp).'</em></p></td></tr>'."\n";
			}
		}
	}
}

function showTwitMobile($row)
{
	global $db;
	global $_USER;

	$noteInfo = $db->getTwit($row['id']);
	extract($noteInfo);

	$note = processNote($row, false, 'mobile');

	$serial = unserialize(stripslashes($serial));
	extract($serial);

	echo '<li style="background:#EFFFFF"><em class="us">';
	echo '<a href="http://m.twitter.com/'.$username.'">'.$username.' (twitter)</a></em>';
	echo '<em class="nl"> '.$note.'</em>';
	echo '<em class="t"> '.showTimeAgo($timestamp).'</em></li>';
	echo "\n";

}

function showTwitMobileTouch($row, $reducedAjax = false)
{
	global $db;
	global $_USER;
	global $jk;

	$noteInfo = $db->getTwit($row['id']);
	extract($noteInfo);

	$note = processNote($row, false, 'touch');

	$serial = unserialize(stripslashes($serial));
	extract($serial);

	if ($reducedAjax) {
		$return = array();
		$return['text'] = $note.' ';
		$return['id'] = $ID;
		$return['time_ago'] = showTimeAgo($timestamp);
		$return['username'] = $username;
		if (preg_match("/@\b".$_USER['username']."\b/i", $note) == 1) $return['replying'] = true;
		if ($attached_file) $return['attached_file'] = $attached_file;
		return $return;
	}
	else {
		echo '<tr><td onclick="location.href=\''.coreLink('mobile', 'status', 'twit-'.$row['id']).'\'" style="background:#EFFFFF url('.$jk->base.'static/img/m/m_list_arrow.png) no-repeat scroll right center">';
		echo '<a href="http://m.twitter.com/'.$username.'" class="user">'.$username.' (twitter)</a>';
		echo ' '.$note;
		echo '<em class="t"> '.showTimeAgo($timestamp).'</em></td></tr>'."\n";
	}
}

/* PAGINATION (DIGG-STYLE PAGINATION BY STRANGER STUDIOS) */
function getPaginationStringMobile($targetStart, $totalitems, $page, $extraget = array())
{

	if (!$page) $page = 1;
	global $jk;

	$prev = $page - 1;         //previous page is page - 1
	$next = $page + 1;         //next page is page + 1
	$lastpage = ceil($totalitems / $jk->notes_per_page);    //lastpage is = total items / items per page, rounded up.
	$lpm1 = $lastpage - 1;        //last page minus 1
	$adjacents = 1;

	$pagination = "";
	if ($lastpage > 1) {
		$pagination .= '<div class="pa">';

		//previous button
		if ($page > 1)
			$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$prev"))), $targetStart)).'">'.__('previous').'</a>';
		else
			$pagination .= "<span class=\"dis\">".__('previous')."</span>";

		//pages
		if ($lastpage < 3 + ($adjacents * 2)) {
			for ($counter = 1; $counter <= $lastpage; $counter++) {
				if ($counter == $page)
					$pagination .= "<span class=\"cu\">$counter</span>";
				else
					$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";
			}
		}
		elseif ($lastpage >= 3 + ($adjacents * 2)) {
			if ($page < 1 + ($adjacents * 2)) {
				for ($counter = 1; $counter < 4 + ($adjacents * 1); $counter++) {
					if ($counter == $page)
						$pagination .= "<span class=\"cu\">$counter</span>";
					else
						$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";
				}
			}
			elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
				for ($counter = $page - 2; $counter <= $page + 2; $counter++) {
					if ($counter == $page)
						$pagination .= "<span class=\"cu\">$counter</span>";
					else
						$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";
				}
			}
			else {
				for ($counter = $lastpage - (1 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
					if ($counter == $page)
						$pagination .= "<span class=\"cu\">$counter</span>";
					else
						$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$counter"))), $targetStart))."\">$counter</a>";
				}
			}
		}

		if ($page < $counter - 1)
			$pagination .= '<a href="'.call_user_func_array('coreLink', array_merge(array(array_merge($extraget, array("page=$next"))), $targetStart)).'">'.__('next').'</a>';
		else
			$pagination .= "<span class=\"dis\">".__('next')."</span>";
		$pagination .= "</div>\n";
	}

	echo $pagination;

}

function getPaginationStringMobileTouch($type)
{
	$types = array('private', 'private_sent', 'twitter', 'replies', 'mentions', 'friends', 'archive', 'all', 'public');
	$r = '<div class="pa">';
	if (in_array($type, $types)) $r .= '<a href="javascript:mn(\''.$type.'\')"';
	else $r .= '<a href="javascript:mn(\'user\', \''.$type.'\')"';
	$r .= ' id="pgi">'.__('more notes').'</a></div>';
	return $r;
}

function doNoteFormMobile($in_reply_to = false, $text = '')
{
	global $_USER;
	if (!$_USER) return;
	echo '<div class="sn" style="line-height:21px;">
	<form action="'.coreLink('post').'" method="post">
	<p><strong>'. __('Send note').'</strong></p>
	<p><input class="i" type="text" maxlength="140" name="note" style="width:99%;height:30px;margin-left:1px" value="'.$text.'"/><br /></p>
	<p><input type="hidden" name="auth" value="'.md5($_USER['salt']).'" /></p>';
	if ($in_reply_to) echo '<p><input type="hidden" name="in_reply_to" value="'.(int)$in_reply_to.'" /></p>';
	echo '<p><input type="hidden" name="usemobile" value="true" /></p>
	<p><input class="b" type="submit" value="'.__('Send').'" /></p>
	</form>
	</div>';
}

function showMobileMenu()
{
	global $db;
	global $_USER;

	if ($_USER) {
		$unread_privates = $db->countNotes('unread_private', $_USER['ID']);
		$unread_replies = $db->countNotes('unread_reply', $_USER['ID']);

		$sections = array(__('Archive') => 'notes/archive', __('Private messages') => 'notes/private', __('Replies') => 'notes/replies', __('Friends') => '');

		echo '<div class="p">';

		echo '<a href="'.coreLink('mobile', 'notes').'">'.__('Home').'</a> | <a href="'.coreLink('mobile', 'notes', 'replies').'">@'.$_USER['username'];
		if ($unread_replies) echo ' ('.$unread_replies.')';
		echo '</a> | <a href="'.coreLink('mobile', 'notes', 'private').'">'.__('Private messages');
		if ($unread_privates) echo ' ('.$unread_privates.')';
		echo '</a>';

		if (has_twitter()) echo ' | <a href="'.coreLink('mobile', 'notes', 'twitter').'">'.__('Twitter').'</a>';
		if (EXTRA == 'private' || (EXTRA == 'private_sent')) echo '<br /><br /><a href="'.coreLink('mobile', 'notes', 'private').'">'.__('Received').'</a> | <a href="'.coreLink('mobile', 'notes', 'private_sent').'">'.__('Sent').'</a>';

		echo '</div>';
	}
}

?>