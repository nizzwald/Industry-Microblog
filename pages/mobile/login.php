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

switch ($_GET['err']) {
case 'passwd':
	echo '&nbsp;&nbsp;<strong>'.__('Incorrect password').'</strong>';
	break;
case 'noname':
	echo '&nbsp;&nbsp;<strong>'.__('There is no user with that name').'</strong>';
	break;
case 'empty':
	echo '&nbsp;&nbsp;<strong>'.__('There are empty fields, fill them and try again').'</strong>';
	break;
case 'noactive':
	echo '&nbsp;&nbsp;<strong>'.__("This account hasn't been confirmed yet").'</strong>';
	break;
}
?>
<div class="l">
<form action="<?php echo coreLink('login') ?>" method="post">
<div class="lt"><?php echo __('Login') ?></div>
<p><?php echo __('Username') ?><br /><input class="i" type="text" maxlength="15" name="username" /></p>
<p><?php echo __('Password') ?><br /><input class="i" type="password" maxlength="32" name="password" /></p>
<input type="hidden" name="usemobile" value="true" />
<br />
<p><input class="b" type="submit" value="<?php echo __('Log In') ?>	" /></p>
</form>
</div>