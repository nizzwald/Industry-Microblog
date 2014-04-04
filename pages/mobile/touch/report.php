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

if (!EXTRA) die();

$extra = EXTRA;
if (!$userInfo) {
	echo '<strong>'.__('Not found').'</strong>';
}
else {
	echo '<div class="sn"><strong>'.__('Report the user '.$extra).'</strong><span style="color:black;"><br>';
	echo __('Some reasons to report an account can be:').'<br><ul style="color:red"><li>'.__('SPAM').'</li><li>'.__('Offensive content').'</li><li>'.__('Duplicated account').'</li><li>'.__('Unlawful attached files').'</li><li>'.__('Illicit purposes').'</li></ul>'.__('You must be completely SURE that the user you are reporting is not following our Terms of Service. And remember this:').'<br><ul style="color:red"><li>'.__('You will remain in anonymity').'</li><li>'.__('When finishing this, you will not get any notification, but report has been sent').'</li><li>'.__('Fake or duplicated reports means the blocking of your account!').'</li></ul>'.__('If after reading this, you still want to report this account, go forward, we are grateful for your help!').'<br><br>';
	echo '<div id="bot"><a href="'.coreLink('mobile', 'notes').'">'.__('Cancel').'</a> <a href="'.coreLink('mobile', 'reportok', $extra).'" style="color:white;background:url('.$jk->base.'static/img/m/warn_bg.jpg);">'.__('Continue').'</a></div>';
	echo '</span></div>';
}
?>