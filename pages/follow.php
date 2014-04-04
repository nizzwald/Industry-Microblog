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
// along with this program. If not, see <http://www.gnu.org/licenses/>.

global $db;
global $_USER;

if ($_USER) {
	$userID = (int) $_POST['id'];

	if ($userID) {
		$follows = (bool) $db->checkFollowing($_USER['ID'], $userID);
		if (!$follows) {
			$diff = time() - $_USER['last_follow'];
			if ($diff > $jk->wait_until_refollow) {
				$ignored = (bool) in_array($userID, $_USER['ignored']);
				if (!$ignored) {
					$userInfo = $db->getUserOptions($userID, array('ignored', 'notification_level'));
					if (!in_array($_USER['ID'], $userInfo['ignored'])) {
						if ($userInfo['notification_level'] == 1 || ($userInfo['notification_level'] >= 4)) {
							global $mailing;
							if ($_USER['realname']) $content = $_USER['realname'].' ('.$_USER['username'].')';
							else $content = $_USER['username'];
							$mailing->newFollower($userID, $content);
						}
						$db->dumpRelationship($_USER['ID'], $userID, false);
					}
				}
			}
		}
		else $db->removeRelationship($_USER['ID'], $userID);
	}

	header('Location: '.$_SERVER['HTTP_REFERER']);
}
else header('Location: '.$_SERVER['HTTP_REFERER']);

?>