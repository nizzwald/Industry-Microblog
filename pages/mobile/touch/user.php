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
global $db, $jk;

if (!$userInfo) {
	echo '<strong>'.__('Not found').'</strong>';
}
else {
	$viewable = checkViewableUser($_USER['ID'], $userInfo['ID'], 'show_notes');
	if (!$viewable) {
		echo '<strong>';
		echo __('Not allowed');
		echo '</strong>';
		echo '<br />';
		echo __('You aren\'t allowed to see this user\'s profile.');
	}
	else {
		if ($_USER) {
			echo '<script type="text/javascript">function fl(st){var dc=document.getElementById(\'fll\');dc.innerText=\''.__('Wait...').'\';var ajax=new XMLHttpRequest();ajax.open("POST","'.coreLink('ajax', 'follow').'",true);ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");ajax.onreadystatechange=function(){if(ajax.readyState==4){if(ajax.status==200){var resp=eval(\'(\'+ajax.responseText+\')\');if(resp.error){alert(resp.error);if(st==0){dc.innerText=\''.__('Follow').'\';}else{dc.innerText=\''.__('UnFollow').'\';}}else{if(resp.following==true){dc.innerText=\''.__('UnFollow').'\';dc.attributes[0].nodeValue=\'javascript:fl(1)\';}else{dc.innerText=\''.__('Follow').'\';dc.attributes[0].nodeValue=\'javascript:fl(0)\';}}}else{alert(\''.__('There was a problem while trying to follow the user').'\');if(st==0){dc.innerText=\''.__('Follow').'\';}else{dc.innerText=\''.__('UnFollow').'\';}}}};ajax.send("&who='.$userInfo['ID'].'");} function ig(st){var dc=document.getElementById(\'igg\');dc.innerText=\''.__('Wait...').'\';var ajax=new XMLHttpRequest();ajax.open("POST","'.coreLink('ajax', 'ignore').'",true);ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");ajax.onreadystatechange=function(){if(ajax.readyState==4){if(ajax.status==200){var resp=eval(\'(\'+ajax.responseText+\')\');if (resp.error){alert(resp.error);if (st==0){dc.innerText=\''.__('Ignore').'\';}else{dc.innerText=\''.__('UnIgnore').'\';}}else{if (resp.ignored==true){dc.innerText=\''.__('UnIgnore').'\';dc.attributes[0].nodeValue=\'javascript:ig(1)\';}else{dc.innerText=\''.__('Ignore').'\';dc.attributes[0].nodeValue=\'javascript:ig(0)\';}}}else{alert(\''.__('There was a problem while trying to ignore the user').'\');if (st==0){dc.innerText=\''.__('Ignore').'\';}else{dc.innerText=\''.__('UnIgnore').'\';}}}};ajax.send("&who='.$userInfo['ID'].'");}</script>';
		}
		$result = $db->getNotes('archive', getStart(1), $jk->notes_per_page, $userInfo['ID']);
		echo '
		<div style="margin-left:20px;padding-top:10px;margin-bottom:50px">
			<img src="'.getAvatar($userInfo['ID'], 48).'" style="-webkit-border-radius: 5px;float:left;">
			<div style="padding-bottom:5px;font-size:25px;float:left;padding-left:10px;padding-top:5px">'.($userInfo['realname'] ? $userInfo['realname'] : $userInfo['username']).'</div>
		</div>
		';
		if ($_USER) {
			echo '<br />';
			echo '<div id="bot">';
			if (!$db->checkFollowing($_USER['ID'], $userInfo['ID'])) echo '<a href="javascript:fl(0)" id="fll">'.__('Follow').'</a>';
			else echo '<a href="javascript:fl(1)" id="fll">'.__('Unfollow').'</a>';
			if (in_array($userInfo['ID'], $_USER['ignored'])) echo ' <a href="javascript:ig(1)" id="igg">'.__('UnIgnore').'</a>';
			else echo ' <a href="javascript:ig(0)" id="igg">'.__('Ignore').'</a>';
			echo ' <a href="'.coreLink('mobile', 'report', $userInfo['username']).'" style="color:white;background:url('.$jk->base.'static/img/m/warn_bg.jpg);">'.__('Report').'</a>';
			echo '</div>';
		}
		echo '<table class="n" id="n">';
		if (count($result)) foreach ($result as $row) showNoteMobileTouch($row);
		else echo '<tr><td>' . __('No notes were found') . '</td></tr>';
		echo '</table>';

		if ($db->countNotes('archive', $userInfo['ID']) > $jk->notes_per_page) echo getPaginationStringMobileTouch($userInfo['username']);
	}
}

?>