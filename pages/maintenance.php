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

global $jk;
global $maintenance;

if (!$jk->maintenance && (empty($maintenance))) header('Location: '.$jk->base);
else {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title><?php echo __('Maintenance mode') ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<!--  <meta http-equiv="refresh" content="15;url=<?php echo $_SERVER['HTTP_HOST'].$_SERVER['QUERY_STRING'] ?>"/>-->
		<meta name="keywords" content="<?php echo replacePatterns($jk->meta_keywords) ?>" />
		<meta name="robots" content="<?php echo replacePatterns($jk->meta_robots) ?>" />
		<meta name="description" content="<?php echo replacePatterns($jk->meta_description) ?>" />
		<style type="text/css">
			BODY{width:500px;height:300px;margin:150px auto auto}
			#contenedor .content{background-color:#005DC4;color:#FFF;font-family:serif;text-align:center;font-size:35px;padding:10px}
			#contenedor #title{text-align:center;padding-bottom:20px}
			.footer{padding-top:5px;font-size:11px;color:#B9B7C0;font-family:"Lucida Grande", Arial, serif}
			.footer a{color:#B9B7D0}
		</style>
		<link rel="shortcut icon" href="favicon.ico" type="image/png" />
	</head>
	<body>
		<div id="contenedor">
			<div id="title">
				<img src="static/img/logos/jisko.png"><br>
			</div>
			<div class="content"><?php echo __($maintenance); ?></div>
		</div>
		<div class="footer">
			<div style="float:right">
			Powered by <a href="http://www.jisko.org">Jisko</a>
			</div>
			<?php printf(__('If you are having problems with Jisko contact us at %s'), '<a href="http://answers.launchpad.net/jisko">http://answers.launchpad.net/jisko</a>') ?>
		</div>
	</body>
</html>
<?php
}
?>