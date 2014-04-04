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

function showAdminHeader($title = 'Administration') {
	global $jk;
	
	echo '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.LANG.'" lang="'.LANG.'"><head>
<title>'.__('Administration').' // '.$jk->name.'</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<script src="'.$jk->base.'static/js/jquery-1.4.2.js" type="text/javascript"></script> 
<style type="text/css">
.ok {background-color:green;color:white;padding:5px;margin-left:30px;margin-right:20px;text-align:center}
.error {background-color:red;color:white;padding:5px;text-align:center;margin:10px;}
BODY{margin:auto;margin-top:50px;width:700px;font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 17px;}#contenedor .content{background:url(../themes/transparency/img/web_bg.png);border:1px solid #ddd;border-radius:4px;-moz-border-radius:4px;color:black;}#contenedor .content{padding:5px;padding-right:30px;}.footer{padding-top:5px;font-size:11px;color:#B9B7C0;font-family:"Lucida Grande",Arial,serif}.footer a{color:#B9B7D0}H3{font-style:italic;font-size:20px}.input{width:300px;}input[type=text]{height:31px;font-size:17px;padding-left:7px}li{margin-bottom:1.3em}.inputs{min-height:100px;width:550px;list-style-type:none}.inputs small{padding-right:6px;font-size:.7em;color:#aaa}#menu ul{list-style-type:none;padding-right:50px;padding-top:10px;padding-bottom:10px}#menu ul li{display:inline;background-color:#08004D;padding:10px 12px}#menu ul li a{color:#ddd;text-decoration:none;color:white;font-size:16px}#menu ul{padding-top:2px;padding-bottom:5px;border-bottom: 5px solid #08004D;}.title{font-family:Georgia,Tahoma;color:#08004D;font-style:italic;font-weight:bold;padding-top:20px;padding-left:30px;font-size:20px}input[type=submit]{margin-left:40px}#shorters ul{list-style-type:none;}#shorters input{margin-top:1px}
.filter{list-style-type:none;padding-right:50px;padding-top:10px;padding-bottom:10px}.filter li{display:inline;background-color:#08004D;padding:7px 8px}.filter li a{color:#ddd;text-decoration:none;color:white;font-size:16px}.filter{padding-top:2px;padding-bottom:5px;}
                                                                                                                                                                                                                                #update{ min-height: 20px; background-color: #FF3F3F; border: 1px solid #DDDDDD; text-align:center; padding-top: 11px;font-family:serif;font-style:italic;color:white;padding-bottom:11px;}
</style><link rel="shortcut icon" href="'.$jk->base.'favicon.ico" type="image/png" />
</head><body>
<div id="contenedor">
<a href="'.$jk->base.'"><img src="'.$jk->base.'static/img/logos/'.$jk->logo.'" style="border:0px" alt="'.$jk->name.'" /></a><br /><br />
<div id="update" style="display:none"></div>
<div id="menu">
	<ul>
		<li><a href="'.coreLink('admin', 'general').'">'.__('General').'</a></li>
		<li><a href="'.coreLink('admin', 'environment').'">'.__('Environment').'</a></li>
		<li><a href="'.coreLink('admin', 'shorter_urls').'">'.__('URL Shortening').'</a></li>
		<li><a href="'.coreLink('admin', 'themes').'">'.__('Themes').'</a></li>
		<li><a href="'.coreLink('admin', 'users').'">'.__('Users').'</a></li>
		<li><a href="'.coreLink('admin', 'misc').'">'.__('Misc').'</a></li>
	</ul>
</div>
	<div class="content"><div class="title">'.$title.'</div><br />
	';
}

function showAdminFooter() {
	echo '
	</div>
<div class="footer">
<div style="float:right">
Powered by <a href="http://www.jisko.org">Jisko</a>
</div>'.sprintf(__('If you are having problems with Jisko contact us at %s'), '<a href="http://answers.launchpad.net/jisko">http://answers.launchpad.net/jisko</a>').'</div><br /><br /></body></html>
	';
}

?>