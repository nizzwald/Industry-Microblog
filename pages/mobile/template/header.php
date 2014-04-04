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

global $_USER, $jk;
echo '<?xml version="1.0" encoding="UTF-8"?>'
?>

<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $jk->name; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="cache-control" content="max-age=0"/>
<link href="<?php echo $jk->base ?>static/css/mobile.css" rel="stylesheet" type="text/css"/>
<link rel="shortcut icon" href="<?php echo $jk->base ?>static/img/m/favicon.ico"/>
<?php if ($_USER) echo '<link rel="alternate" type="application/rss+xml" title="'.__('Friends timeline (RSS 2.0)').'" href="'.coreLink(array('id='.$_USER['ID']), 'rss', 'friends').'" />'; ?>
<link rel="alternate" type="application/rss+xml" title="<?php echo __('Friends timeline (RSS 2.0)') ?>" href="<?php echo coreLink('api', 'statuses', 'public_timeline.rss') ?>" />
</head>
<body>
<div class="h">
<div style="float:right;margin-top:5px;margin-right:2px;">

<?php
if ($_USER) echo '<a accesskey="3" href="'.coreLink(array('mobile'), 'logout').'">'.__('Logout').'</a>';
else echo '<a accesskey="2" href="'.coreLink('mobile', 'login').'">'.__('Login').'</a>';
?>
</div>
<a accesskey="2" href="<?php echo coreLink('mobile', 'notes') ?>"><img src="<?php echo $jk->base ?>static/img/m/logo.gif" alt="<?php echo $jk->name; ?>" /></a>

</div>