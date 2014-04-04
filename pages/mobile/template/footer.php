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
global $_USER;
?>

<div class="f">
	<a href="<?php echo coreLink('mobile', 'public') ?>"><?php echo __('Public notes') ?></a> |
	<?php if ($_USER) echo ' <a href="'.coreLink('mobile', 'notes', 'archive').'">'.__('Archive').'</a> | '; ?>
	<?php if ($jk->tos) echo '<a accesskey="8" href="'.coreLink('tos').'">'.__('Terms of Service').'</a> | '; ?>
	<a accesskey="8" href="<?php echo $jk->base; ?>"><?php echo __('Normal version') ?></a><br /><br />Powered by <a href="http://jisko.org/">Jisko</a> <?php echo JISKO_VERSION ?>
</div>
</body>
</html>