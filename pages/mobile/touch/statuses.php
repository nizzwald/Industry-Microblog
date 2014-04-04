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
global $db;

$extra = EXTRA;
$explode_extra = explode('-', $extra);

if (!$extra) {
	echo '<script>location.href=\''.coreLink('mobile').'\'</script>';
	die();
}
else {
	echo '<div class="sn"><strong>';
	if ($explode_extra[0] == 'twit') {
		echo __('Twitter note').' #'.$extra;
	}
	else __('Note').' #'.$extra;
	echo '</strong><table class="n">';
	
	if ($explode_extra[0] == 'twit') {
		if (!$explode_extra[1]) {
			echo '<tr><td style="background:transparent;border:0">'.__('No notes were found').'</td></tr></table>';
		}
		else {
			$result = $db->getTwit($explode_extra[1]);
			if ($result) $result = array_merge($result, array('type'=>'twitter'));
			$seriala = unserialize(stripslashes($result['serial']));
		}
	}
	else {
		$result = $db->getNoteCombined($extra);
		if ($result) {
			$viewable = checkViewableUser($_USER['ID'], $result['user_id'], 'show_notes');
			if (!$viewable) {
				echo '<tr><td style="background:transparent;border:0">'.__("This note is from a private profile").'</td></tr></table>';
				die();
			}
		}
	}

	if (!$result) {
		echo '<tr><td style="background:transparent;border:0">'.__('No notes were found').'</td></tr></table>';
	}
	else {
		extract($result);
		$note = processNote($result, false, 'touch', true);
		
		echo '<tr><td id="not" onclick="history.go(-1);" style="padding-left:40px;background:#FFFFFF url('.$jk->base.'static/img/m/m_list_arrowi.jpg) no-repeat scroll left center;border-bottom:0px">';
		if ($explode_extra[0] == 'twit') {
			$username = $seriala['username'];
			echo '<span class="user">'.$username.'</span> ';
		}
		else echo '<a href="'.coreLink('mobile', $username).'" class="user">'.$username.'</a> ';
		echo '<span id="np">'.$note.'</span> <em class="t">'.showTimeAgo($timestamp).'</em></td></tr></table>';
		
		if ($_USER) {
			echo '<script>';
			if ($type == 'public') {
				echo 'function fav(){var dc=document.getElementById(\'fav\');dc.innerHTML=\''.__('Wait...').'\';var ajax=new XMLHttpRequest();ajax.open("GET","'.coreLink(array('id='.$ID), 'ajax', 'favorite').'",true);ajax.setRequestHeader("Content-Type","charset=UTF-8");ajax.send(null);ajax.onreadystatechange=function(){if(ajax.readyState==4){if(ajax.status==200){var resp=eval(\'(\'+ajax.responseText+\')\');if(resp.favorited == true){dc.innerHTML=\'<img src="'.$jk->base.'static/img/m/heart.png">\';dc.attributes[\'href\'].nodeValue=\'javascript:fav()\';}else{dc.innerHTML=\''.__('Favorite').'\';dc.attributes[\'href\'].nodeValue=\'javascript:fav()\';}}else{alert(\''.__('There was an error while trying to favoritize the note').'\');if(st==0){dc.innerText=\''.__('Favorite').'\';}else{dc.innerHTML=\'<img src="'.$jk->base.'static/img/m/heart.png">\';}}}};}';
				if ($user_id == $_USER['ID']) echo 'function del(){var dc=document.getElementById(\'del\');dc.innerHTML=\''.__('Wait...').'\';var ajax=new XMLHttpRequest();ajax.open("GET","'.coreLink(array('id='.$ID), 'ajax', 'delete').'",true);ajax.setRequestHeader("Content-Type","charset=UTF-8");ajax.send(null);ajax.onreadystatechange=function(){if(ajax.readyState==4){if(ajax.status==200){var resp=eval(\'(\'+ajax.responseText+\')\');if(resp.ok){alert(resp.ok);location.href=\''.coreLink('mobile').'\';}else{alert(resp.error);}}else{alert(\''.__('There was an error while trying to remove the note').'\');}}}}';
			}
			elseif ($type == 'private' && ($user_id == $_USER['ID'])) echo 'function del(){var dc=document.getElementById(\'del\');dc.innerHTML=\''.__('Wait...').'\';var ajax=new XMLHttpRequest();ajax.open("GET","'.coreLink(array('id='.$ID), 'ajax', 'delete').'",true);ajax.setRequestHeader("Content-Type","charset=UTF-8");ajax.send(null);ajax.onreadystatechange=function(){if(ajax.readyState==4){if(ajax.status==200){var resp=eval(\'(\'+ajax.responseText+\')\');if(resp.ok){alert(resp.ok);location.href=\''.coreLink('mobile').'\';}else{alert(resp.error);}}else{alert(\''.__('There was an error while trying to remove the note').'\');}}}}';
			echo 'function RT(char){document.getElementById(\'ni\').innerText=\'"\'+document.getElementById(\'np\').innerText+\'" /via \'+char+\''.$username.'\';count();}function RP() {document.getElementById(\'ni\').innerText=\'';
			if ($type == 'private' || ($type == 'private_sent')) {
				echo '!';
				if ($type == 'private') echo $username;
				else echo $db->getUsernameFromID($reply_user);
				echo '\';count();}';
			}
			elseif ($type == 'twitter') echo '%'.$username.'\';count();}';
			elseif ($type == 'public') echo '@'.$username.'\';count();document.getElementById(\'in_reply_to\').value=\''.$ID.'\';}';
			echo '</script>';
			echo '<br>';
		}
		echo '<div id="bot">';
		if ($_USER) {
			if ($type == 'public' || ($type == 'twitter')) {
				if ($type == 'public') $char = '@'; else $char = '%';
				echo '<a href="javascript:RT(\''.$char.'\')">'.__('Quote').'</a>';
			}
			if ($user_id == $_USER['ID'] && ($type == 'public')) {
				echo ' <a id="del" style="background: url('.$jk->base.'static/img/m/warn_bg.jpg);color:white;" href="javascript:del()">'.__('Delete').'</a> ';
			}
			else echo ' <a style="background: url('.$jk->base.'static/img/m/confirm_bg.jpg);color:white;" href="javascript:RP()">'.__('Reply').'</a> ';
			
			if ($type == 'public') {
				echo '<a id="fav" href="javascript:fav()">';
				if (!$db->checkFavorite($_USER['user_id'], $extra)) echo __('Favorite');
				else echo '<img src="'.$jk->base.'static/img/m/heart.png">';
				echo '</a>';
			}
		}
		if ($attached_file) {
			if ($_USER) echo '<br /><br /><br />';
			echo '<p><a style="background-image: url('.$jk->base.'static/img/m/confirm_bg.jpg);color:white" href="'.coreLink('download', $ID, $attached_file).'">'.__('View attachment').'</a></p>';
		}
		echo '</div>';
	}
}
echo '</div>';
?>