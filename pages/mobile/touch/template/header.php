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

global $jk, $_USER;
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $jk->name; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="cache-control" content="max-age=0"/>
<meta content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" name="viewport"/>
<link href="<?php echo $jk->base ?>static/css/mobile_touch.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $jk->base ?>static/img/m/apple_icon.png" rel="apple-touch-icon"/>
<script>
function count(){var length=140-document.getElementById('ni').value.length;if(length<0){document.getElementById("c").innerHTML='<span style="color:red">'+length+' <?php echo __('remaining characters'); ?></span>';}else{document.getElementById("c").innerHTML=length+' <?php echo __('remaining characters'); ?></span>';}}function ev(){document.getElementById('c').innerText='<?php echo __('Wait...'); ?>';var ajax=new XMLHttpRequest();ajax.open("POST",'<?php echo coreLink('ajax', 'post') ?>',true);ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");ajax.onreadystatechange=function(){if(ajax.readyState==4){if(ajax.status==200){var resp=eval('('+ajax.responseText+')');if(resp.ok){location.reload();}else{alert(resp.error);}}}};ajax.send('in_reply_to='+document.getElementById('in_reply_to').value+'&auth='+document.getElementById('au').value+'&note='+escape(document.getElementById('ni').value)+'&usemobile=true');}function sbmit(){var length=document.getElementById('ni').value.length;if(length<3){alert('<?php echo __('The length of the note needs to be between 3 and 140 characters'); ?>');}else{ev();}}
function mn(url, extra){document.getElementById('pgi').innerHTML='<?php echo __('Wait...'); ?>';var ajax=new XMLHttpRequest();var aurl="<?php echo coreLink(array('touch=true', 'page=%page', '%d'), 'ajax', '%s') ?>".replace('%s', url).replace('%page', document.getElementById('page').innerHTML);if(extra){aurl=aurl.replace('%d', 'user='+extra);}ajax.open("GET",aurl);ajax.setRequestHeader("Content-Type","charset=UTF-8");ajax.onreadystatechange=function(){if(ajax.readyState==4){if(ajax.status==200){var resp=eval('('+ajax.responseText+')');if(resp.length<<?php echo $jk->notes_per_page;?> || (resp.error)){if(resp.error){alert(resp.error);}document.getElementById('pgi').style.display='none';}for(i=0;i<resp.length;i++){var nD=document.createElement('td');if(resp[i].type=='twitter'||(resp[i].type=='twitter_reply')){nD.style.backgroundColor='#EFFFFF';ndL='twit-'+resp[i].id;}else{ndL=resp[i].id;}nD.setAttribute('onclick',"location.href='<?php echo coreLink('mobile','status', '%s'); ?>'.replace('%s', ndL)+'");nDin='<a class="user" href="';if(resp[i].type=='twitter'||(resp[i].type=='twitter_reply')){nDin=nDin+'http://m.twitter.com/'+resp[i].username+'">'+resp[i].username+' (twitter)';}else{nDin=nDin+'<?php echo coreLink('mobile', '%s'); ?>'.replace('%s', resp[i].username)+'">'+resp[i].username;}nDin=nDin+'</a> '+resp[i].text+' <em class="t">'+resp[i].time_ago+'</em>';nD.innerHTML=nDin;var nT=document.createElement('tr');nT.appendChild(nD);document.getElementById('n').appendChild(nT);}document.getElementById('page').innerHTML=++document.getElementById('page').innerHTML;}}document.getElementById('pgi').innerHTML='<?php echo __('more notes'); ?>';};ajax.send(null);}
</script>
</head>
<body>
<div id="page" style="display:none">2</div>
<div class="h<?php if ($_USER) echo 'l'; else echo 'u'; ?>" id="h">
	<div id="t_m">
		<?php
echo '<a href="'.coreLink('mobile', 'notes', 'public').'">'.__('Public notes').'</a>';
if ($_USER) echo ' | <a href="'.coreLink(array('mobile'), 'logout').'">'.__('Logout').' »</a></div>';
else echo ' | <a href="'.coreLink('mobile', 'login').'">'.__('Login').' »</a></div>';
?>
	<a href="<?php echo coreLink('mobile', 'notes'); ?>"><img src="<?php echo $jk->base ?>static/img/m/logo.gif" alt="Jisko" style="padding-top: 5px;"/></a>
	<div id="po" style="padding-top:2px">
	<?php if ($_USER): ?>
	<form method="POST" onsubmit="return false" name="subnote">
		<textarea class="i" type="text" name="note" id="ni" onkeyup="count()"></textarea>
		<input type="hidden" name="auth" value="<?php echo md5($_USER['salt']) ?>" id="au"/>
		<input type="hidden" name="in_reply_to" value="" id="in_reply_to" />
		<br />
		<input class="b" type="submit" value="<?php echo __('Send') ?>" onclick="sbmit()"/> <span id="c"><?php echo __('140 remaining characters') ?></span>
	</form>
	<?php endif; ?>
	</div>
</div>
<?php
if ($_USER) {
	echo '<div class="p">
		<ul>
			<li><a href="'.coreLink('mobile', 'notes').'">'.__('Home').'</a></li>
			<li><a href="'.coreLink('mobile', 'notes', 'replies').'">@'.$_USER['username'].'</a></li>
			<li><a href="'.coreLink('mobile', 'notes', 'private').'">'.__('Private messages').'</a></li>';
			if (has_twitter()) echo '<li><a href="'.coreLink('mobile', 'notes', 'twitter').'">'.__('Twitter').'</a></li>';
	echo '</ul>';
	echo '</div>';
}
?>
<div id="contenedor">