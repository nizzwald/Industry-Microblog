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

showMobileMenu();
doNoteFormMobile();

?>

<ul class="n">
<li><strong><?php echo __('Public notes') ?></strong></li>
<?php
if (isset($_GET['page'])) $page = $_GET['page']; else $page = 1;
$start = getStart($page);
if ($_USER['ignored']) $ignored = $_USER['ignored']; else $ignored = false;
$result = $db->getNotes('public', $start, $jk->notes_per_page, false, $ignored);
$count = $db->countNotes('public', '', $ignored);

if (count($result)) {
	foreach ($result as $note) showNoteMobile($note);
} else {
	echo '<li>'.__('No notes were found').'</li>';
}
echo '</ul>';

if ($count > $jk->notes_per_page) echo getPaginationStringMobile(array('mobile', 'public'), $count, $page);
?>