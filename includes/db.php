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

/**
 * This class 
 *
 * @package default
 * @author Ruben Diaz
 * @author Marcos García
 */
class DB
{
	var $socket;
	var $connected = false;
	var $queries = 0;
	var $queryis = array();

	/**
	 * Connect to the database
	 *
	 * @param string $dbHost 
	 * @param string $dbPort 
	 * @param string $dbUser 
	 * @param string $dbPassword 
	 * @author Marcos García
	 * @author Ruben Díaz
	 */
	function __construct($dbHost, $dbPort = 3306, $dbUser, $dbPassword)
	{
		$this->socket = @mysql_connect($dbHost.':'.$dbPort, $dbUser, $dbPassword);

		if ($this->socket) {
			$this->connected = true;
			$this->send("SET NAMES 'utf8'");
		}
	}
	
	/**
	 * It selects the database we are going to use
	 *
	 * @param string $dbName 
	 * @return void
	 * @author Ruben Díaz
	 */
	function select($dbName)
	{
		$result = @mysql_select_db($dbName);

		if ($result === false) $this->connected = false;
		return $result;
	}
	
	/**
	 * It returns if Jisko is connected to the database or not
	 *
	 * @return void
	 * @author Ruben Díaz
	 */
	function isConnected()
	{
		return $this->connected;
	}
	
	/**
	 * It sends a mySQL query to the database server
	 *
	 * @param string $query 
	 * @return void
	 * @author Ruben Díaz
	 */
	function send($query)
	{
		global $jk;
		if ($jk->is_debug == true) $sql = mysql_query($query) or write_debug(mysql_error(), 'mysql');
		else $sql = mysql_query($query);

		$this->queries = $this->queries + 1;
		$this->queryis[] = $query;
		return $sql;
	}

	/**
	 * Used as an equivalent of mysql_real_escape_string()
	 * Taken from Partystic libraries
	 *
	 * @param string $query 
	 * @param string $vars 
	 * @return void
	 */
	function clean($query, $vars)
	{
		$query_length = strlen($query);
		if (count($vars) > 0) {
			$result = '';
			for ($i = 0, $varsParsed = 0; $i < $query_length; ++$i) {
				if ($query[$i] == "\\" and $query[$i + 1] == '%') {
					$result .= '%';
					++$i;
				} else if ($query[$i] != '%') {
						$result .= $query[$i];
					} else {
					if ($query[$i + 1] == 'i') {
						$result .= (int)$vars[$varsParsed];
					} else if ($query[$i + 1] == 's') {
							if ($query[$i - 1] == '`') {
								$toInclude = str_replace('`', '``', $vars[$varsParsed]);
							} else {
								$toInclude = str_replace("\\", "\\\\", $vars[$varsParsed]);
								$toInclude = str_replace($query[$i - 1], "\\" . $query[$i - 1], $toInclude);
							}
							$result .= $toInclude;
						}
					++$varsParsed;
					++$i;
				}
			}
			return $result;
		}
		return $query;
	}

	/**
	 * It returns notes for the specified type
	 *
	 * @param string $where 
	 * @param string $start 
	 * @param string $end 
	 * @param string $userID 
	 * @param string $ignored 
	 * @param string $since_id 
	 * @param string $since_timestamp 
	 * @param string $max_id 
	 * @return void
	 * @author Marcos García
	 * @author Ruben Díaz
	 */
	function getNotes($where, $start = 0, $end, $userID = false, $ignored = false, $since_id = false, $since_timestamp = false, $max_id = false)
	{
		global $_USER;

		if (is_array($ignored) && count($ignored) > 0) {
			$ignored = implode(',', $ignored);
			if (!empty($ignored)) {
				$s_post2id = ' AND `post2id`.`from` NOT IN('.$ignored.')';
				$s_notes = ' AND `notes`.`user_id` NOT IN('.$ignored.')';
			} else {
				$s_post2id = '';
				$s_notes = '';
			}
		}
		
		$sec2nd = in_array($where, array('public', 'archive', 'private', 'private_sent'));
		
		if (is_numeric($since_id)) {
			if ($sec2nd == true) $port = ' AND `notes`.`ID` > '.(int)$since_id;
			else $port = ' AND `note_id` > '.(int)$since_id;
		}
		else $port = '';
		if (is_numeric($max_id)) {
			if ($sec2nd == true) $port .= ' AND `notes`.`ID` < '.(int)$max_id;
			else $port .= ' AND `note_id` < '.(int)$max_id;
		}
		else $port .= '';
		if (is_numeric($since_timestamp)) $port .= ' AND `timestamp` > '.(int)$since_timestamp;
		else $port .= '';

		switch ($where) {
		case 'tag':
			$query = "SELECT `tags_n`.`timestamp` AS `orderby`, `tags_n`.`note_id` AS id FROM `tags_n` WHERE `tags_n`.`tag` = '$userID'";
			break;
		case 'public':
			$query = "SELECT `notes`.`timestamp` AS `orderby`, `notes`.`ID` AS id, `notes`.`type`, `users`.`privacy` FROM `notes`, `users` WHERE `notes`.`type` = 'public' AND `notes`.`user_id`=`users`.`ID`$port$s_notes";
			break;
		case 'archive':
			$query = "SELECT `notes`.`timestamp` AS `orderby`, `notes`.`ID` AS id, `notes`.`type` FROM `notes` WHERE `type`='public' AND user_id = ".(int)$userID."$port$s_notes";
			break;
		case 'private':
		case 'private_sent':
			$query = "SELECT `notes`.`timestamp` AS `orderby`, `notes`.`ID` AS id, `notes`.`type` FROM `notes` WHERE `notes`.`type` = 'private' AND ";
			if ($where == 'private') $query .= "`notes`.`reply_user` = '".(int)$userID."'";
			elseif ($where == 'private_sent') $query .= "`notes`.`user_id` = ".(int)$userID;
			$query .= $port;
			break;
		case 'mentions':
		case 'replies':
			$query = "SELECT `timestamp` AS `orderby`, `note_id` AS id, 'public' AS `type` FROM `mentions` WHERE `user_id` = ".(int)$userID." $port$s_notes";
			break;
		case 'followers':
			$followers = $this->getFollowersID($userID);
			$query = "SELECT `post2id`.`timestamp` AS `orderby`, `post2id`.`note_id` AS id, `post2id`.`type` FROM `post2id` WHERE `post2id`.`type`='public' AND `post2id`.`from` IN (".implode(',', $followers).") AND `post2id`.`to`= ".(int)$userID.$port.$s_post2id;
			break;
		case 'friends':
		case 'friendsof':
			$query = "SELECT `post2id`.`timestamp` AS `orderby`, `post2id`.`note_id` AS id, `post2id`.`type` FROM `post2id` WHERE `post2id`.`type` = 'public' AND `post2id`.`to`= ".(int)$userID.$port.$s_post2id;
			break;
		case 'favorites':
			$query = "SELECT `favorites`.`note_id` AS id, `favorites`.`id` AS `orderby`, 'public' AS `type` FROM `favorites` WHERE `favorites`.`user_id` = ".(int)$userID;
			$query .= $port;
			break;
		case 'all':
		case 'twitter':
		case 'twitter_replies':
			$query = "SELECT `timestamp` AS `orderby`, `note_id` AS id, `type` AS type FROM `post2id` WHERE `post2id`.`to` = ".(int)$userID . " AND type ";
			if ($where == 'all') $query .= "IN('public', 'twitter','twitter_reply')".$s_post2id;
			elseif ($where == 'twitter') $query .= "IN('twitter','twitter_reply')";
			elseif ($where == 'twitter_replies') $query .= "= 'twitter_reply'";
			$query .= $port;
			break;
		}
		$notesArray = array();

		$query = $query." ORDER BY `orderby` DESC LIMIT ".(int)$start.", ".(int)$end;
		$result = $this->send($query);

		while ($note = @mysql_fetch_array($result, MYSQL_ASSOC)) array_push($notesArray, $note);

		return $notesArray;
	}
	
	/**
	 * Returns all the info about a specific ote
	 *
	 * @param string $noteID
	 * @return void
	 * @author Ruben Díaz
	 */
	function getNoteInfo($noteID)
	{
		$query = "SELECT * FROM `notes` WHERE `notes`.`ID` = ".(int)$noteID;
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		$row = mysql_fetch_array($result);
		return $row;
	}
	
	/**
	 * It returns the text of a note from a note ID
	 *
	 * @param string $noteID 
	 * @return void
	 * @author Marcos García
	 */
	function getTextFromNoteID($noteID)
	{
		$result = $this->send("SELECT `note` FROM `notes` WHERE `ID` = ".(int)$noteID);
		if (!mysql_num_rows($result)) return false;
		else return mysql_result($result, 0);
	}
	
	/**
	 * It returns the mentions of a specific note
	 *
	 * @param string $noteID 
	 * @return void
	 * @author Marcos García
	 */
	function getNoteMentions($noteID)
	{
		$result = $this->send('SELECT `user_id` FROM `mentions` WHERE `note_id`=\''.$noteID.'\'');
		if (mysql_num_rows($result)) {
			$return = array();
			while ($row = mysql_fetch_row($result)) $return[] = $row[0];
			return $return;
		}
		else return array();
	}
	
	/**
	 * It returns some info for a specific note
	 *
	 * @param string $noteID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getNoteCombined($noteID)
	{
		$query = "SELECT `notes`.`ID`, `notes`.`user_id`, `notes`.`type`, `notes`.`note`, `notes`.`attached_file`, `notes`.`from`, `notes`.`tip_amount`, `notes`.`bill_amount`,`notes`.`replying`, `notes`.`reply_user`, `notes`.`timestamp`, `users`.`username`, `users`.`profile`, `users`.`avatar` FROM `notes`, `users` WHERE `users`.`ID` = `notes`.`user_id` AND `notes`.`ID` = ".(int)$noteID;
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		else {
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			if ($row['type'] != 'private') $row['reply_user'] = $this->getNoteMentions($noteID);
			return $row;
		}
	}
	
	/**
	 * It returns some info of a specific tweet
	 *
	 * @param string $twitID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getTwit($twitID)
	{
		$query = "SELECT * FROM `twitter` WHERE `ID` = " . (int) $twitID;
		$result = mysql_query($query);
		if (!mysql_num_rows($result)) return false;
		return mysql_fetch_assoc($result);
	}
	
	/**
	 * It returns some info for a specific user searching by userID, username, email, openID or Facebook ID
	 *
	 * @param string $userID 
	 * @param string $username 
	 * @param string $email 
	 * @param string $openid 
	 * @param string $facebook 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getUserInfo($userID = false, $username = false, $email = false, $openid = false, $facebook = false)
	{
		if ($username)
			$query = $this->clean("SELECT `users`.* FROM users WHERE `users`.`username` = '%s' LIMIT 1", array($username));
		elseif ($userID)
			$query = "SELECT `users`.* FROM `users` WHERE `users`.`ID` = ".(int)$userID." LIMIT 1";
		elseif ($email)
			$query = $this->clean("SELECT `users`.* FROM users WHERE `users`.`email` = '%s' LIMIT 1", array($email));
		elseif ($openid)
			$query = $this->clean("SELECT `users`.* FROM users WHERE `users`.`openid` = '%s' LIMIT 1", array($openid));
		elseif ($facebook)
			$query = $this->clean("SELECT `users`.* FROM users WHERE `users`.`facebook` = '%s' LIMIT 1", array($facebook));
		else return false;
		$result = $this->send($query);

		if (!mysql_num_rows($result)) return false;
		else {
			$return = mysql_fetch_assoc($result);
			$return = processUserInfo($return);
			return $return;
		}
	}
	
	/**
	 * It returns information about a session from a cookie ID
	 *
	 * @param string $cookie 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getFromCookie($cookie)
	{
		$query = $this->clean("SELECT `sessions`.`user_id`, `sessions`.`hash`, `users`.* FROM `sessions`, `users` WHERE `sessions`.`hash` = '%s' AND `users`.`id` = `sessions`.`user_id` LIMIT 1", array($cookie));
		$result = $this->send($query);
		if ($userInfo = @mysql_fetch_array($result, MYSQL_ASSOC)) {
			return processUserInfo($userInfo);
		}
		else return false;
	}
	
	/**
	 * It searches for users
	 *
	 * @param string $info 
	 * @param string $start 
	 * @param string $end 
	 * @param string $email 
	 * @return void
	 * @author Ruben Díaz
	 */
	function searchUser($info, $start, $end, $email = false)
	{
		if ($email == true) $query = "SELECT `ID` FROM `users` WHERE (`email` = '".mysql_real_escape_string($info)."') LIMIT ".(int)$start.", ".(int)$end;
		else $query = "SELECT `ID` FROM `users` WHERE (`username` = '".mysql_real_escape_string($info)."') OR (`username` LIKE '%".mysql_real_escape_string($info)."%') OR (`realname` LIKE '%".mysql_real_escape_string($info)."%') LIMIT ".(int)$start.", ".(int)$end;
		$result = $this->send($query);

		$rowArray = array();
		if (mysql_num_rows($result)) {
			while ($row = mysql_fetch_row($result)) $rowArray[] = $row[0];
		}
		return $rowArray;
	}
	
	/**
	 * It returns the user ID from a username
	 *
	 * @param string $username 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getIDFromUsername($username)
	{
		$query = $this->clean("SELECT `users`.`ID` FROM users WHERE `users`.`username` = '%s'", array($username));
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		else return mysql_result($result, 0);
	}
	
	/**
	 * It returns the username from a user ID
	 *
	 * @param string $ID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getUsernameFromID($ID)
	{
		$query = $this->clean("SELECT `users`.`username` FROM users WHERE `users`.`ID` = '%s'", array($ID));
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		return mysql_result($result, 0);
	}

	/*function getIdByGroup($group_name) { // correct name would be getIdFromGroup
		$query = $this->clean("SELECT `groups`.`ID` FROM `groups` WHERE `groups`.`name` = '%s'", array($group_name));
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		return mysql_result($result, 0);
	} */

	function getEmailFromKey($token, $userID)
	{
		$query = $this->clean("SELECT `keys`.`email` FROM `keys` WHERE `keys`.`token` = '%s' AND `keys`.`user_id` = ".(int)$userID." LIMIT 1", array($token));
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		if ($row = mysql_fetch_array($result)) return $row['email']; else return false;
	}

	function getUserInfoNote($userID)
	{
		$query = "SELECT `users`.`username`, `users`.`avatar` FROM `users` WHERE `users`.`ID` = ".(int)$userID." LIMIT 1";
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		if ($userInfo = mysql_fetch_array($result)) return $userInfo; else return false;
	}

	/**
	 * Returns info about the user that has $api as it's API Key
	 *
	 * @param string $username 
	 * @param string $email 
	 * @param string $api 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getUserInfoAPI($username = false, $email = false, $api)
	{
		if ($username) $query = $this->clean("SELECT `users`.* FROM `users` WHERE `users`.`username` = '%s' AND `users`.`api` = '%s' LIMIT 1", array($username, $api));
		if ($email) $query = $this->clean("SELECT `users`.* FROM `users` WHERE `users`.`email` = '%s' AND `users`.`api` = '%s' LIMIT 1", array($email, $api));
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		if ($userInfo = mysql_fetch_array($result)) return processUserInfo($userInfo); else return false;
	}

	/*function getFromCookieSessionType($cookie) {
		$query = $this->clean("SELECT `sessions`.`type` FROM `sessions` WHERE `sessions`.`hash` = '%s' LIMIT 1", array($cookie));
		$result = $this->send($query);
		if ($row = mysql_fetch_array($result)) return $row['type'];
	}*/

	/**
	 * Returns info about the users who are following $userID
	 *
	 * @param string $userID 
	 * @param string $start 
	 * @param string $items 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getFollowing($userID, $start = 0, $items = false)
	{
		global $jk;
		if ($items === false) $items = (int) $jk->notes_per_page;
		$query = "SELECT `users`.`username`, `users`.`ID` FROM `relationships` INNER JOIN `users` ON `users`.`ID` = `relationships`.`who` WHERE `relationships`.`creator` = ".(int)$userID." ORDER BY `users`.`username` ASC LIMIT $start, $items";
		$result = $this->send($query);
		$rowArray = array();
		while ($row = mysql_fetch_array($result)) array_push($rowArray, $row);
		return $rowArray;
	}

	/**
	 * Returns info about the followers of $userID
	 *
	 * @param string $userID 
	 * @param string $start 
	 * @param string $items 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getFollowers($userID, $start = 0, $items = false)
	{
		global $jk;
		if ($items === false) $items = (int) $jk->notes_per_page;
		$query = "SELECT `users`.`username`, `users`.`ID` FROM `relationships` INNER JOIN `users` ON `users`.`ID` = `relationships`.`creator` WHERE `relationships`.`who` = ".(int)$userID." ORDER BY `users`.`username` ASC LIMIT $start, $items";
		$result = $this->send($query);
		$rowArray = array();
		while ($row = mysql_fetch_array($result)) array_push($rowArray, $row);
		return $rowArray;
	}

	/**
	 * Returns the ID of the username that has $fbid as their Facebook ID
	 *
	 * @param string $fbid 
	 * @return void
	 * @author Marcos García
	 */
	function checkFacebook($fbid)
	{
		$query = $this->send("SELECT `ID` FROM `users` WHERE `facebook`='$fbid'");
		if (mysql_num_rows($query)) {
			return mysql_result($query, 0);
		}
		else return false;
	}

	/**
	 * It returns the ID of the username that has $openid as its OpenID login url
	 *
	 * @param string $openid 
	 * @return void
	 * @author Marcos García
	 */
	function checkOpenID($openid)
	{
		$query = $this->send("SELECT `ID` FROM `users` WHERE `openid`='$openid'");
		if (mysql_num_rows($query)) {
			return mysql_result($query, 0);
		}
		else return false;
	}

	/**
	 * Return the URL of the gravatar avatar in case that it is enabled
	 *
	 * @param string $username 
	 * @param string $size 
	 * @return void
	 * @author Marcos García
	 */
	function checkGravatar($username, $size)
	{
		$query = "SELECT `gravatar`, `email` FROM `users` WHERE `users`.`username`='$username'";
		$result = $this->send($query);
		$result = mysql_fetch_array($result);
		if ($result[0]) {
			return 'http://gravatar.com/avatar/'.md5($result[1]).'.jpg?s='.$size;
		}
		else return false;
	}

	/**
	 * Returns the ID of the users that $userID is following
	 *
	 * @param string $userID 
	 * @param string $start 
	 * @param string $items 
	 * @param string $nolimit 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getFollowingID($userID, $start = 0, $items = false, $nolimit = false)
	{
		global $jk;

		if ($items === false) $items = (int) $jk->notes_per_page;
		if (!$nolimit) $limit = "LIMIT $start, $items"; else $limit = '';

		$result = $this->send("SELECT `users`.`ID` FROM `relationships` INNER JOIN `users` ON `users`.`ID` = `relationships`.`who` WHERE `relationships`.`creator` = ".(int)$userID." ORDER BY `users`.`username` ASC LIMIT $start, $items");
		$rowArray = array();
		while ($row = mysql_fetch_array($result)) array_push($rowArray, $row['ID']);
		return $rowArray;
	}

	/**
	 * Returns the ID of the followers of $userID
	 *
	 * @param string $userID 
	 * @param string $start 
	 * @param string $items 
	 * @param string $nolimit 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getFollowersID($userID, $start = 0, $items = false, $nolimit = false)
	{
		global $jk;

		if ($items === false) $items = (int) $jk->notes_per_page;
		if (!$nolimit) $limit = "LIMIT $start, $items"; else $limit = '';

		$result = $this->send("SELECT `users`.`ID` FROM `relationships` INNER JOIN `users` ON `users`.`ID` = `relationships`.`creator` WHERE `relationships`.`who` = ".(int)$userID." ORDER BY `users`.`username` ASC $limit");
		$rowArray = array();
		if (mysql_num_rows($result)) {
			while ($row = mysql_fetch_array($result)) array_push($rowArray, $row['ID']);
		}
		return $rowArray;
	}

	function getFriendCreator($userID, $both = false, $notboth = false)
	{
		$query = "SELECT `relationships`.`creator` FROM `relationships` WHERE `relationships`.`who` = ".(int)$userID;
		if ($both) $query .= " AND `relationships`.`both` = 1";
		elseif ($notboth) $query .= " AND `relationships`.`both` = 0";

		$result = $this->send($query);

		if (mysql_num_rows($result) == 0) return false;
		else {
			$creatorArray = array();
			while ($creator = mysql_fetch_row($result)) $creatorArray[] = $creator[0];

			return $creatorArray;
		}
	}

	/**
	 * Returns the number of invitations that $userID has
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getInvitations($userID)
	{
		$query = "SELECT `users`.`invitations` FROM `users` WHERE `users`.`ID` = ".(int)$userID." LIMIT 1";
		$result = $this->send($query);
		$row = mysql_fetch_array($result);
		return $row['invitations'];
	}

	/**
	 * Returns the userID of the author of the note $noteID
	 *
	 * @param string $noteID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getPosterID($noteID)
	{
		$query = "SELECT `notes`.`user_id` FROM `notes` WHERE `notes`.`ID` = ".(int)$noteID." LIMIT 1";
		$result = $this->send($query);
		$row = mysql_fetch_array($result);
		return $row['user_id'];
	}

	function getLastNotePermalink($username = false, $own = false)
	{
		if (!$own) $query = $this->clean("SELECT `users`.`ID`, `notes`.`ID` AS `permalink` FROM `users`, `notes` WHERE `users`.`username` = '%s' AND `notes`.`user_id` = `users`.`ID` AND `notes`.`type` = 'public' ORDER BY `notes`.`ID` DESC LIMIT 1", array($username));
		else $query = $this->clean("SELECT `users`.`ID`, `notes`.`ID` AS `permalink` FROM `users`, `notes` WHERE `users`.`username` = '%s' AND `notes`.`user_id` = `users`.`ID` ORDER BY `notes`.`ID` DESC LIMIT 1", array($username));
		$result = $this->send($query);
		if ($permalink = mysql_fetch_array($result)) return $permalink; else return false;
	}

	/**
	 * Return last $limit notes of $userID
	 *
	 * @param string $userID 
	 * @param string $limit 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getLastNotes($userID, $limit = 25)
	{
		$limit = min( (int) $limit, 25);
		$query = "SELECT * FROM `notes` WHERE `notes`.`user_id` = ".(int) $userID." AND type IN('public') ORDER BY `notes`.`ID` DESC LIMIT 0,".(int)$limit;
		$result = $this->send($query);
		$inf = array();
		while ($row = mysql_fetch_assoc($result)) $inf[] = $row;
		return $inf;
	}

	/**
	 * Returns the ID of the note that $noteID is replying to
	 *
	 * @param string $noteID 
	 * @return void
	 * @author Emir Beganovic
	 */
	function getReplying($noteID)
	{
		$query = "SELECT `notes`.`replying` FROM `notes` WHERE `notes`.`ID` = ".(int)$noteID." LIMIT 1";
		$result = $this->send($query);
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			$query = "SELECT `notes`.`note` FROM `notes` WHERE `notes`.`ID` = ".(int)$row[0]." LIMIT 1";
			$result = $this->send($query);
			$row = mysql_fetch_array($result);
		}
		return $row[0];
	}

	/**
	 * Returns a number of notes that reply to $noteID
	 *
	 * @param string $noteID 
	 * @return void
	 * @author Emir Beganovic
	 */
	function getNumRepliesNote($noteID)
	{
		$query="SELECT SQL_CACHE count(`notes`.`ID`) from notes where `notes`.`replying` = ". (int) $noteID;
		$result = $this->send($query);
		$row = mysql_fetch_row($result);
		if ($row) return $row[0]; else return "0";
	}

	/**
	 * Returns an array of ID with the ID of notes that reply to the note $id
	 *
	 * @param string $id 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getRepliesNote($id)
	{
		$query= "SELECT SQL_CACHE `notes`.`ID` FROM `notes`, `users` WHERE `users`.`ID` = `notes`.`user_id` AND `notes`.`replying` = ".(int)$id;
		$result = $this->send($query);
		if ($result) {
			$notesArray = array();
			while ($note = @mysql_fetch_array($result)) $notesArray[] = $note[0];
			return $notesArray;
		}
		else return false;
	}

	/**
	 * Returns $db->getNoteCombined() for the last note of the user $user.
	 *
	 * @param string $user 
	 * @param string $timestamp 
	 * @param string $me 
	 * @return void
	 * @author Ruben Díaz
	 */
	function getLastNoteOf($user, $timestamp, $me = 0)
	{
		$userID = (int) $this->getIDFromUsername($user);
		$timestamp = (int) $timestamp;
		if (!$userID) return false;
		$query = "SELECT `notes`.`ID` FROM `notes` WHERE `notes`.`user_id` = ". (int) $userID . " AND `notes`.`timestamp` < " . $timestamp. " AND `notes`.`type` = 'public' ORDER BY `timestamp` DESC LIMIT 1";
		$result = $this->send($query);
		if (!mysql_num_rows($result)) return false;
		$noteID = mysql_result($result, 0);
		$note = $this->getNoteCombined($noteID);
		return $note;
	}

	/**
	 * Checks if the note $note exists for the user $userID
	 *
	 * @param string $note 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkNote($note = 0, $userID = 0)
	{
		$query = "SELECT COUNT(*) FROM `notes` WHERE `user_id` = ".(int)$userID." AND `type` != 'private' AND `ID` = " . (int) $note;
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * Checks if the note $noteID is favorite for the user $userID
	 *
	 * @param string $userID 
	 * @param string $noteID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkFavorite($userID, $noteID)
	{
		$query = "SELECT COUNT(*) FROM `favorites` WHERE `favorites`.`user_id` = ".(int)$userID." AND `favorites`.`note_id` = ".(int)$noteID." LIMIT 1";
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * Checks if $currentUserID and $userID are following each other
	 *
	 * @param string $currentUserID 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkFollowing($currentUserID, $userID)
	{
		$query = "SELECT COUNT(*) FROM `relationships` WHERE `relationships`.`creator` = ".(int)$currentUserID." AND `relationships`.`who` = ".(int)$userID." LIMIT 1";
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * Checks if an invitation token exists
	 *
	 * @param string $token 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkToken($token)
	{
		$query = $this->clean("SELECT COUNT(*) FROM `invitations` WHERE `invitations`.`token` = '%s' LIMIT 1", array($token));
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * It checks if an user exists
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkInvitations($userID)
	{
		$query = "SELECT COUNT(*) FROM `users` WHERE `users`.`ID` = ".(int)$userID." LIMIT 1";
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * It checks if an userID is being used by an user
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkUserID($userID)
	{
		$query = $this->send("SELECT COUNT(*) FROM `users` WHERE `users`.`ID` = '".(int)$userID."' LIMIT 1");
		$count = mysql_result($query, 0);
		return (bool) $count;
	}

	/**
	 * It checks if an Username is being used
	 *
	 * @param string $username 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkUsername($username)
	{
		$query = $this->clean("SELECT COUNT(*) FROM `users` WHERE `users`.`username` = '%s' LIMIT 1", array($username));
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * It checks if an Email account is being used
	 *
	 * @param string $email 
	 * @param string $userid 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkEmail($email, $userid = 0)
	{
		$query = $this->clean("SELECT COUNT(*) FROM `users` WHERE `users`.`email` = '%s' AND `users`.`ID` <> ".(int)$userid." LIMIT 1", array($email));
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * It checks if a Jabber account is being used
	 *
	 * @param string $jabber 
	 * @param string $userid 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkJabber($jabber, $userid = 0)
	{
		$query = $this->clean("SELECT COUNT(*) FROM `users` WHERE `users`.`jabber` = '%s' AND `users`.`ID` <> ".(int)$userid." LIMIT 1", array($jabber));
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * It checks if an activation key is valid or not
	 *
	 * @param string $key 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkRegKey($key, $userID)
	{
		$query = $this->clean("SELECT COUNT(*) FROM `keys` WHERE `keys`.`user_id` = ".(int)$userID." AND `keys`.`token` = '%s' AND `keys`.`type` = 'activation' LIMIT 1", array($key));
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return (bool) $count;
	}

	/**
	 * It checks if a password key is valid or not
	 *
	 * @param string $key 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkForgotKey($key, $userID)
	{
		$query = $this->clean("SELECT `timestamp` FROM `keys` WHERE `keys`.`user_id` = ".(int)$userID." AND `keys`.`token` = '%s' AND `keys`.`type` = 'password' LIMIT 1", array($key));
		$result = $this->send($query);
		if (mysql_num_rows($result)) {
			$timestamp = mysql_result($result, 0);
			$tiempo = time() - $timestamp;
			if ($tiempo > 86400) return false;
			else return true;
		}
		else return false;
	}

	/**
	 * It checks if a drop key $key is valid or not
	 *
	 * @param string $key 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkDropKey($key, $userID)
	{
		$query = $this->clean("SELECT `timestamp` FROM `keys` WHERE `keys`.`user_id` = ".(int)$userID." AND `keys`.`token` = '%s' AND `keys`.`type` = 'drop' LIMIT 1", array($key));
		$result = $this->send($query);
		if (mysql_num_rows($result)) {
			$timestamp = mysql_result($result, 0);
			$tiempo = time() - $timestamp;
			if ($tiempo > 86400) return false;
			else return true;
		}
		else return false;
	}

	/**
	 * It checks if an email key $key is valid or not
	 *
	 * @param string $key 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function checkEmailKey($key, $userID)
	{
		$query = $this->clean("SELECT `timestamp` FROM `keys` WHERE `keys`.`user_id` = ".(int)$userID." AND `keys`.`token` = '%s' AND `keys`.`type` = 'email' LIMIT 1", array($key));
		$result = $this->send($query);
		if (mysql_num_rows($result)) {
			$timestamp = mysql_result($result, 0);
			$tiempo = time() - $timestamp;
			if ($tiempo > 86400) return false;
			else return true;
		}
		else return false;
	}

	/**
	 * Deletes a key for a $userID
	 *
	 * @param string $userID 
	 * @param string $type 
	 * @return void
	 * @author Ruben Díaz
	 */
	function deleteKeys($userID, $type)
	{
		if ($this->send('DELETE FROM `keys` WHERE `keys`.`user_id`='.(int)$userID.' AND `keys`.`type`=\''.mysql_real_escape_string($type).'\'')) return true;
		else return false;
	}

	/**
	 * Creates a new user
	 *
	 * @param string $username 
	 * @param string $password 
	 * @param string $api 
	 * @param string $salt 
	 * @param string $default_lang 
	 * @param string $default_theme 
	 * @param string $email 
	 * @param string $ip 
	 * @param string $token 
	 * @param string $noc 
	 * @param string $openid 
	 * @param string $facebook 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newUser($username, $password, $api, $salt, $default_lang, $default_theme, $email, $ip, $token, $noc, $openid = false, $facebook = false)
	{
		if ($noc == true) $noc = 'ok'; else $noc = 'nc';
		if (!$openid) $txt = 'null';
		else $txt = "'$openid'";
		if (!$facebook) $txt2 = 'null';
		else $txt2 = "'$facebook'";
		$query = $this->clean("INSERT INTO `users` (`username`, `password`, `api`, `salt`, `language`, `theme`, `email`, `status`, `since`, `last_seen`, `ip`, `notification_level`, `openid`, `facebook`) values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '%s', 4, %s, %s)", array($username, $password, $api, $salt, $default_lang, $default_theme, $email, $noc, $ip, $txt, $txt2));
		$result = $this->send($query);
		if (mysql_affected_rows()) {
			if ($noc == 'nc') {
				$insert = mysql_insert_id();
				$query = $this->clean("INSERT INTO `keys` (`user_id`, `token`, `type`) VALUES ($insert, '%s', 'activation')", array($token));
				$result = $this->send($query);
				return $insert;
			}
		} else {
			return false;
		}
	}

	/**
	 * Creates a new session
	 *
	 * @param string $userID 
	 * @param string $SID 
	 * @param string $type 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newSession($userID, $SID, $type = 'normal')
	{
		$query = $this->clean("INSERT INTO `sessions` (`user_id`, `time`, `hash`, `type`) VALUES (".(int)$userID.", UNIX_TIMESTAMP(), '%s', '%s')", array($SID, $type));
		$result = $this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * Creates a new invitation
	 *
	 * @param string $userID 
	 * @param string $email 
	 * @param string $token 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newInvitation($userID, $email, $token)
	{
		$query = $this->clean("INSERT INTO `invitations` (`token`, `email`, `user_id`) VALUES ('%s', '%s', ".(int)$userID.")", array($token, $email));
		$result = $this->send($query);
		if (mysql_affected_rows()) return $token; else return false;
	}
	
	/**
	 * Creates a new private note
	 *
	 * @param string $userID 
	 * @param string $to 
	 * @param string $note 
	 * @param string $attached_file 
	 * @param string $from 
	 * @param string $ip 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newPrivateNote($userID, $to = 0, $note, $attached_file = 0, $from, $ip)
	{
		$result = $this->checkUserID($to);
		if ($result) {
			$query = $this->clean("INSERT INTO `notes` (`user_id`, `type`, `reply_user`, `note`, `attached_file`, `from`, `timestamp`, `ip`, `read`) VALUES (".(int)$userID.", 'private', '%s', '%s', '%s', '%s', UNIX_TIMESTAMP(), '%s', 'false')", array($to, $note, $attached_file, $from, $ip));
			$result = $this->send($query);
			return mysql_insert_id();
		} else {
			return false;
		}
	}

	/**
	 * Checks if the tag $name exists
	 *
	 * @param string $name 
	 * @return void
	 * @author Marcos García
	 */
	function checkTag($name)
	{
		$name = mysql_real_escape_string($name);
		$query = $this->send("SELECT COUNT(*) FROM `tags_c` WHERE `name`='$name'");

		if (!mysql_result($query, 0)) return false;
		else return true;
	}

	/**
	 * Creates a new tag
	 *
	 * @param string $name 
	 * @param string $founder 
	 * @param string $id 
	 * @param string $timestamp 
	 * @return void
	 * @author Marcos García
	 */
	function createTag($name, $founder, $id, $timestamp)
	{
		if (!$this->checkTag($name)) $this->send("INSERT INTO `tags_c` SET `name`='$name', `timestamp`='$timestamp', `founder`='$founder'");
		$this->send("INSERT INTO `tags_n` SET `note_id`='$id', `tag`='$name', `timestamp`='$timestamp', `poster`='$founder'");
	}
	
	/**
	 * Returns information about a specific tag
	 *
	 * @param string $name 
	 * @param string $full 
	 * @return void
	 * @author Marcos García
	 */
	function getInfoTag($name, $full = false)
	{
		$result = array();
		$query = $this->send("SELECT `timestamp`, `founder` FROM `tags_c` WHERE `name`='".mysql_real_escape_string($name)."'");
		$row = mysql_fetch_row($query);

		if ($full) {
			$q = $this->send("SELECT `poster`, COUNT(*) FROM `tags_n` WHERE `tag`='$name' GROUP BY `poster` ORDER BY COUNT(*) DESC LIMIT 1");
			$r = mysql_fetch_row($q);

			$abc = $this->getUserInfoNote($r[0]);

			$result['max_poster_username'] = $abc[0];
			$result['max_poster_userid'] = $r[0];
			$result['max_poster_quantity'] = $r[1];

			$count = $this->countNotes('tag', $name);
			$result['notes_count'] = (int) $count;
		}

		$abc = $this->getUserInfoNote($row[1]);

		$result['since'] = $row[0];
		$result['founder'] = $abc[0];
		return $result;
	}

	/**
	 * Inserts a new note into the database
	 *
	 * @param string $userID 
	 * @param string $type 
	 * @param string $twitter 
	 * @param string $note 
	 * @param string $attached_file 
	 * @param string $from 
	 * @param string $replying 
	 * @param string $ip 
	 * @param string $replyuser 
	 * @param string $read 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newNote($userID, $type = 'public', $twitter = 0, $note, $attached_file = 0, $from, $replying = 0, $ip, $replyuser = 0, $read = 1, $tip_amount, $bill_amount)
	{
		$query = $this->clean("INSERT INTO `notes` (`user_id`, `type`, `twitter`, `note`, `attached_file`, `from`, `replying`, `timestamp`, `ip`, `reply_user`, `read`, `tip_amount`, `bill_amount` ) VALUES (".(int)$userID.", '%s', ".(int)$twitter.", '%s', '%s', '%s', ".(int)$replying.", UNIX_TIMESTAMP(), '%s', ".(int)$replyuser.", ".(int)$read.", ".(int)$tip_amount.", ".(int)$bill_amount.")", array($type, $note, $attached_file, $from, $ip));

		$result = $this->send($query);
		if (mysql_affected_rows()) {
			$insert = mysql_insert_id();
			return $insert;
		} else {
			return false;
		}
	}

	/**
	 * Inserts a new tweet to the database
	 *
	 * @param string $userID 
	 * @param string $note 
	 * @param string $hash 
	 * @param string $serial 
	 * @param string $timestamp 
	 * @param string $type 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newTweet($userID, $note, $hash, $serial, $timestamp, $type = 'twitter')
	{
		$query = $this->clean("INSERT INTO `twitter` (`ID`, `hash`, `timestamp`,`note` ,`serial`) VALUES (NULL , '%s', '%s', '%s', '%s')", array($hash, $timestamp, $note, mysql_real_escape_string($serial)));
		$result = $this->send($query);
		if ($result) {
			$insert_id = mysql_insert_id();
			$query = $this->clean("INSERT INTO `post2id` (`ID`,`from`,`to`,`note_id`,`timestamp`,`reply_user`,`type`) VALUES (NULL , '0', '%i', '%i', '%i', '0', '".$type."')", array($userID, $insert_id, $timestamp));
			$this->send($query);
		}
		return $result;
	}

	/**
	 * Creates tokens
	 *
	 * @param string $uid 
	 * @param string $type 
	 * @param string $token 
	 * @param string $email 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newKey($uid, $type, $token, $email = false)
	{
		$query = $this->clean("DELETE FROM `keys` WHERE `keys`.`user_id` = $uid AND `keys`.`type` = '%s'", array($type));
		$result = $this->send($query);
		if ($email) {
			$query = $this->clean("INSERT INTO `keys` (`user_id`, `type`, `token`, `email`, `timestamp`) VALUES ($uid, '%s', '%s', '%s', UNIX_TIMESTAMP())", array($type, $token, $email));
		} else {
			$query = $this->clean("INSERT INTO `keys` (`user_id`, `type`, `token`, `timestamp`) VALUES ($uid, '%s', '%s', UNIX_TIMESTAMP())", array($type, $token));
		}
		$result = $this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It starts following a user
	 *
	 * @param string $creator 
	 * @param string $who 
	 * @param string $read 
	 * @return void
	 * @author Ruben Díaz
	 * @author Marcos García
	 */
	function newFollowing($creator, $who, $read = true)
	{
		$me = $this->getUserInfo($creator);
		$recipro = (bool) $this->checkFollowing($who, $creator);
		if ($recipro) {
			$both = '1';
			$query = "UPDATE `relationships` SET `relationships`.`both` = 1 WHERE `relationships`.`creator` = " . (int) $who . " AND `relationships`.`who` = ". (int) $creator;
			$this->send($query);
		} else {
			$both = '0';
		}
		$query = "INSERT INTO `relationships` (`creator`, `who`, `both`, `read`) VALUES (".(int)$creator.", ".(int)$who.", ".(int)$both.", ".(int)$read.")";
		$this->send($query);
		return $recipro;
	}

	/**
	 * It turns a note to favorite for the user $userID
	 *
	 * @param string $userID 
	 * @param string $noteID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function newFavorite($userID, $noteID)
	{
		$query = "INSERT INTO `favorites` (`user_id`, `note_id`, `timestamp`) VALUES (".(int)$userID.", ".(int)$noteID.", UNIX_TIMESTAMP())";
		$result = $this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It inserts into the `post2id` table the specified content ($bulk)
	 *
	 * @param string $bulk 
	 * @return void
	 * @author Ruben Díaz
	 */
	function post2id($bulk)
	{
		$sql = "INSERT INTO `post2id` (`from`, `to`, `note_id`, `timestamp`, `reply_user`, `type`) VALUES ".implode(', ', $bulk);
		$result = $this->send($sql);
		if (mysql_affected_rows() != 0) return true; else return false;
	}
	
	/**
	 * It inserts into the `mentions` table the specified content ($bulk)
	 *
	 * @param string $bulk 
	 * @return void
	 * @author Ruben Díaz
	 * @author Marcos García
	 */
	function mentions($bulk)
	{
		$sql = "INSERT INTO `mentions` (`note_id`, `user_id`, `timestamp`) VALUES ".implode(', ', $bulk);
		$result = $this->send($sql);
		if (mysql_affected_rows() != 0) return true; else return false;
	}

	/**
	 * It removes an invitation with the specified $token
	 *
	 * @param string $token 
	 * @return void
	 * @author Ruben Díaz
	 */
	function deleteToken($token)
	{
		$query = $this->clean("DELETE FROM `invitations` WHERE `invitations`.`token` = '%s' LIMIT 1", array($token));
		$result = $this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It removes the specified $token of an $userID
	 *
	 * @param string $token 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function deleteKey($token, $userID)
	{
		$query = $this->clean("DELETE FROM `keys` WHERE `keys`.`token` = '%s' LIMIT 1", array($token));
		$result = $this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It removes sessions for a specific userID from the database
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function deleteSession($userID)
	{
		$query = "DELETE FROM `sessions` WHERE `sessions`.`user_id` = ".(int)$userID." LIMIT 1";
		$result = $this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It removes a relationship from the database
	 *
	 * @param string $creator 
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function deleteFollowing($creator, $userID)
	{
		$query = $this->clean("DELETE FROM `relationships` WHERE `relationships`.`who` = %i AND `relationships`.`creator` = %i LIMIT 1", array($userID, $creator));
		$result = $this->send($query);
		$query = "UPDATE `relationships` SET `relationships`.`both` = 0 WHERE `relationships`.`creator` = " . (int) $userID . " AND `relationships`.`who` = ".(int)$creator." LIMIT 1";
		$this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It removes a favorite from the database
	 *
	 * @param string $userID 
	 * @param string $noteID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function deleteFavorite($userID, $noteID)
	{
		$query = "DELETE FROM `favorites` WHERE `favorites`.`user_id` = ".$userID." AND `favorites`.`note_id` = ".(int)$noteID." LIMIT 1";
		$this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It removes a note from the database
	 *
	 * @param string $noteID 
	 * @param string $userID 
	 * @param string $username 
	 * @return void
	 * @author Ruben Díaz
	 */
	function deleteNote($noteID, $userID, $username = false)
	{
		$row = $this->getNoteCombined($noteID);
		if ($row['user_id'] == $userID) {
			if ($row['attached_file']) {
				if (!$username) $file = './users_files/'.$row['username'].'/files/'.$row['attached_file'];
				else $file = './users_files/'.$username.'/files/'.$row['attached_file'];
				if (file_exists($file)) unlink($file);
			}

			$this->send("DELETE FROM `notes` WHERE `notes`.`ID` = ".(int)$noteID);
			if ($row['type'] != 'private') {
				$this->send("DELETE FROM `favorites` WHERE `favorites`.`note_id` = ".(int)$noteID);
				$this->send("DELETE FROM `post2id` WHERE `post2id`.`note_id` = ".(int)$noteID);
				$this->send("DELETE FROM `tags_n` WHERE `note_id`='".(int)$noteID."'");
				$this->send("DELETE FROM `mentions` WHERE `note_id`='".(int)$noteID."'");

				//We look for tags in the post
				preg_match_all('/(\s|\A)(#){1}([a-zA-Z0-9_]+)/', $row['note'], $matches);
				$added_tags = array();
				foreach ($matches[0] as $tag) {
					$tag = trim(str_replace('#', '', $tag));
					if (!in_array($tag, $added_tags)) {
						if (strlen($tag) <= 20) {
							if ($this->countNotes('tag', $tag) == 0) $this->send("DELETE FROM `tags_c` WHERE `name`='$tag'");
						}
					}
					$added_tags[] = $tag;
				}
			}
			return true;
		}
		else return false;
	}
	
	/**
	 * It removes an user from the database
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 * @author Marcos García
	 */
	function deleteUser($userID)
	{
		$username = $this->getUsernameFromID($userID);

		$this->send("DELETE FROM `users` WHERE `users`.`ID` = ".(int)$userID." LIMIT 1");
		$this->send("DELETE FROM `notes` WHERE `notes`.`user_id` = ".(int)$userID);
		$this->send("DELETE FROM `notes` WHERE `notes`.`type`='private' AND `notes`.`reply_user` = ".(int)$userID);
		$this->send("DELETE FROM `keys` WHERE `keys`.`user_id` = ".(int)$userID);
		$this->send("DELETE FROM `post2id` WHERE `post2id`.`from` = ".(int)$userID);
		$this->send("DELETE FROM `post2id` WHERE `post2id`.`to` = ".(int)$userID);
		$this->send("DELETE FROM `relationships` WHERE `relationships`.`creator` = ".(int)$userID);
		$this->send("DELETE FROM `relationships` WHERE `relationships`.`who` = ".(int)$userID);
		$this->send("DELETE FROM `sessions` WHERE `sessions`.`user_id` = ".(int)$userID);
		$this->send("DELETE FROM `favorites` WHERE `favorites`.`user_id` = ".(int)$userID);
		$this->send("DELETE FROM `tags_n` WHERE `tags_n`.`poster` = ".(int)$userID);
	}

	/**
	 * It optimizes the `twitter` table and removes all the twitter notes from the database
	 *
	 * @return void
	 * @author Ruben Díaz
	 */
	function cleanTwitter()
	{
		$this->send("DELETE FROM `post2id` WHERE `post2id`.`type`='twitter'");
		$this->send("TRUNCATE TABLE `twitter`");
		$this->send("OPTIMIZE TABLE `notes`");
		$this->send("OPTIMIZE TABLE `post2id`");
		$this->send("OPTIMIZE TABLE `users`");
	}

	/**
	 * It returns the content unserialized of the `twitter` field in the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @return void
	 * @author Marcos García
	 */
	function getTwitterOptions($userID)
	{
		$query = $this->send("SELECT `twitter` FROM `users` WHERE `id`='$userID'");
		if (mysql_num_rows($query)) {
			return unserialize(stripslashes(mysql_result($query, 0)));
		}
		else return false;
	}

	/**
	 * It returns the content unserialized of the `customize` field in the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @return void
	 * @author Marcos García
	 */
	function getCustomizeOptions($userID)
	{
		$query = $this->send("SELECT `customize` FROM `users` WHERE `id`='$userID'");
		if (mysql_num_rows($query)) {
			return unserialize(stripslashes(mysql_result($query, 0)));
		}
		else return false;
	}

	/**
	 * It updates some options of the `twitter` field in the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function updateTwitterOptions($userID, $options)
	{
		$oldOptions = $this->getTwitterOptions($userID);

		if (!$oldOptions) $newOptions = serialize($options);
		else $newOptions = serialize(array_merge($oldOptions, $options));

		$this->send("UPDATE `users` SET `twitter`='$newOptions' WHERE `id`='$userID'");
	}

	/**
	 * It updates some options of the `customize` field in the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function updateCustomizeOptions($userID, $options)
	{
		$oldOptions = $this->getCustomizeOptions($userID);

		if (!$oldOptions) $newOptions = serialize($options);
		else $newOptions = serialize(array_merge($oldOptions, $options));

		$this->send("UPDATE `users` SET `customize`='$newOptions' WHERE `id`='$userID'");
	}

	/**
	 * It updates some options of the `profile` field in the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function updateProfile($userID, $options)
	{
		$oldOptions = $this->getUserOptions($userID, array('profile'));

		if (!$oldOptions['profile']) $newOptions = serialize($options);
		else $newOptions = serialize(array_merge($oldOptions['profile'], $options));

		$this->send("UPDATE `users` SET `profile`='$newOptions' WHERE `id`='$userID'");
	}

	/**
	 * It returns the content unserialized of the `extra` field in the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @return void
	 * @author Marcos García
	 */
	function getUserExtraOptions($userID)
	{
		$query = $this->send("SELECT `extras` FROM `users` WHERE `id`='$userID'");
		if (mysql_num_rows($query)) {
			return unserialize(stripslashes(mysql_result($query, 0)));
		}
		else return false;
	}

	/**
	 * It updates some options of the `extra` field in the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function updateUserExtraOptions($userID, $options)
	{
		$oldOptions = $this->getUserExtraOptions($userID);

		if (!$oldOptions) $newOptions = serialize($options);
		else $newOptions = serialize(array_merge($oldOptions, $options));

		$this->send("UPDATE `users` SET `extras`='$newOptions' WHERE `id`='$userID'");
	}

	/**
	 * It returns a specific value of the 'users' table for the specified $userID
	 *
	 * @param string $userID 
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function getUserOptions($userID, $options)
	{
		$return = '';
		$i = 0;
		foreach ($options as $key) {
			if (gettype($value) == 'boolean') $value = (int) $value;
			if ($i == (count($options) - 1)) $return .= "`$key`";
			else $return .= "`$key`, ";
			++$i;
		}
		$query = $this->send("SELECT $return FROM `users` WHERE `ID`='$userID'");
		if ($query) {
			$return = processUserInfo(mysql_fetch_assoc($query));
			return $return;
		}
		else return false;
	}
	
	/**
	 * It updates some values of the 'users' table for the user with the specified $userID
	 *
	 * @param string $userID 
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function updateUserOptions($userID, $options)
	{
		$return = '';
		$i = 0;
		foreach ($options as $key=>$value) {
			if (gettype($value) == 'boolean') $value = (int) $value;
			if ($i == (count($options) - 1)) {
				if (!is_null($value)) $return .= "`$key`='$value'";
				else $return .= "`$key`=NULL";
			}
			else {
				if (!is_null($value)) $return .= "`$key`='$value', ";
				else $return .= "`$key`=NULL, ";
			}
			++$i;
		}
		if ($this->send("UPDATE `users` SET $return WHERE `ID`='$userID'")) return true;
		else return false;
	}
	/**
	 * It updates a value of a category in Jisko settings table
	 *
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function updateJiskoSettings($options)
	{
		foreach ($options as $key=>$value) {
			if (gettype($value) == 'boolean') $value = (int) $value;
			else $value = mysql_real_escape_string($value);
			$this->send("UPDATE `settings` SET `value`='$value' WHERE `category`='$key'");
		}	
	}
	
	/**
	 * It returns a value of a category in Jisko settings table
	 *
	 * @param string $options 
	 * @return void
	 * @author Marcos García
	 */
	function getJiskoSettings($options)
	{
		for ($i = 0; $i <= (count($options) - 1);$i++) {
			if ($i == 0) $where = 'WHERE `category`=\''.$options[$i].'\' ';
			else $where .= 'OR `category`=\''.$options[$i].'\' ';
		}
		$query = $this->send("SELECT `category`, `value` FROM `settings` $where");
		
		while ($row = mysql_fetch_row($query)) {
			$array[$row[0]] = $row[1];
		}
		
		return $array;
	}
	
	/**
	 * It updates the number of invitations
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function updateInvitations($userID)
	{
		$result = $this->getInvitations($userID);
		$invitations = ($result - 1);
		$query = "UPDATE `users` SET `users`.`invitations` = ".(int)$invitations." WHERE `users`.`ID` = ".(int)$userID." LIMIT 1";
		$result = $this->send($query);
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It marks all the unread private messages as 'read'
	 *
	 * @param string $ID 
	 * @return void
	 * @author Ruben Díaz
	 * @author Marcos García
	 */
	function updateUnreadPrivates($ID)
	{
		$result = $this->send("UPDATE `notes` SET `read` = 1 WHERE `reply_user` = '".(int)$ID."' AND `type` = 'private' AND `read` = 0");
		if (mysql_affected_rows()) return true; else return false;
	}
	
	/**
	 * It marks all the unread mentions as 'read'
	 *
	 * @param string $ID 
	 * @return void
	 * @author Marcos García
	 */
	function updateUnreadReplies($ID)
	{
		$result = $this->send("UPDATE `mentions` SET `read` = 1 WHERE `user_id` = '".(int)$ID."' AND `read` = 0");
		if (mysql_affected_rows()) return true; else return false;
	}
	
	/**
	 * It returns the number of 'unread' followers
	 *
	 * @param string $ID 
	 * @return void
	 * @author Marcos García
	 */
	function countUnreadFollowers($ID)
	{
		$query = $this->send("SELECT COUNT(*) FROM `relationships` WHERE `who`='".(int)$ID."' AND `read`=0");
		$result = mysql_result($query, 0);
		return $result;
	}
	
	/**
	 * It marks all the unread followers as 'read'
	 *
	 * @param string $ID 
	 * @return void
	 * @author Marcos García
	 */
	function updateUnreadFollowers($ID)
	{
		$result = $this->send("UPDATE `relationships` SET `read` = 1 WHERE `who` = '".(int)$ID."' AND `read` = 0");
		if (mysql_affected_rows()) return true; else return false;
	}

	/**
	 * It returns the number of followers that $userID has
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function countFollowers($userID)
	{
		$query = "SELECT COUNT(*) FROM `relationships` WHERE `relationships`.`who` = ".(int)$userID;
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return $count;
	}
	
	/**
	 * It returns the number of the users that $userID is following
	 *
	 * @param string $userID 
	 * @return void
	 * @author Ruben Díaz
	 */
	function countFollowing($userID)
	{
		$query = "SELECT COUNT(*) FROM `relationships` WHERE `relationships`.`creator` = ".(int)$userID;
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return $count;
	}
	
	/**
	 * It returns the number of notes of each type
	 * $type can be: private, archive, twitter, twitter_reply, favorites, public, reply, friends, friendsof, all, tag
	 * The user or the userID is stored in $data.
	 *
	 * @param string $type 
	 * @param string $data 
	 * @param string $ignored 
	 * @return void
	 * @author Marcos García
	 * @author Ruben Díaz
	 */
	function countNotes($type, $data = false, $ignored = false)
	{
		if (is_array($ignored) && count($ignored) > 0) {
			$ignored = implode(',', $ignored);
			if (!empty($ignored)) {
				$s_post2id = ' AND `post2id`.`from` NOT IN('.$ignored.')';
				$s_notes = ' AND `user_id` NOT IN('.$ignored.')';
			} else {
				$s_post2id = '';
				$s_notes = '';
			}
		}
		switch ($type) {
		case 'private':
			$query = "SELECT COUNT(*) FROM `notes` WHERE (`notes`.`type` = 'private') AND (`notes`.`reply_user` = '".(int)$data."')";
			break;
		case 'private_sent':
			$query = "SELECT COUNT(*) FROM `notes` WHERE (`notes`.`type` = 'private') AND (`notes`.`user_id` = '".(int)$data."')";
			break;
		case 'unread_private':
			$query = "SELECT COUNT(*) FROM `notes` WHERE `notes`.`type` = 'private' and `notes`.`read` = 0 and `notes`.`reply_user` = ".(int)$data;
			break;
		case 'archive':
			$query = "SELECT COUNT(*) FROM `notes` WHERE `notes`.`type` = 'public' AND `notes`.`user_id` = ".(int)$data;
			break;
		case 'twitter':
		case 'twitter_reply':
			$query = "SELECT COUNT(*) FROM `post2id` WHERE `post2id`.`type` = '$type' AND `post2id`.`to` = ".(int)$data;
			break;
		case 'favorites':
			$query = "SELECT COUNT(*) FROM `favorites` WHERE `favorites`.`user_id` = ".(int)$data;
			break;
		case 'tag':
			$query = "SELECT COUNT(*) FROM `tags_n` WHERE `tag`='$data'";
			break;
		case 'everytime':
			$query = "SELECT COUNT(*) FROM `notes` WHERE `notes`.`type` = 'public'";
			break;
		case 'public':
			$query = "SELECT COUNT(*) FROM `notes` WHERE `notes`.`type` = 'public'".$s_notes;
			break;
		case 'unread_reply':
			$query = "SELECT COUNT(*) FROM `mentions` WHERE `read`=0 AND `user_id` = ".(int)$data;
			break;
		case 'replies':
		case 'mentions':
			$query = "SELECT COUNT(*) FROM `mentions` WHERE `user_id` = '".(int)$data."'" . $s_notes;
			break;
		case 'friends':
		case 'friendsof':
			$query = "SELECT COUNT(*) FROM `post2id` WHERE `post2id`.`type` = 'public' AND `post2id`.`to` = ".(int)$data.$s_post2id;
			break;
		case 'all':
			$query = "SELECT COUNT(*) FROM `post2id` WHERE `post2id`.`to` = ".(int)$data . " AND `post2id`.`type` IN('public', 'twitter')".$s_post2id;
			break;
		}

		$result = $this->send($query);
		$count = @mysql_result($result, 0);
		if ($count) return $count;
		else return 0;
	}
	
	/**
	 * It returns the number of users that have a pattern like $info
	 *
	 * @param string $info 
	 * @return void
	 * @author Marcos García
	 */
	function countUsers($info)
	{
		$query = $this->clean("SELECT COUNT(*) FROM `users` WHERE `users`.`username` = '%s' OR `users`.`realname` LIKE '\%%s\%'", array($info, $info));
		$result = $this->send($query);
		$count = mysql_result($result, 0);
		return $count;
	}

	/**
	 * When a user starts following an user, this function adds their notes to post2id in order
	 * to see their notes in Friends tab
	 *
	 * @param string $userID 
	 * @param string $toFollow 
	 * @param string $read 
	 * @return void
	 * @author Ruben Díaz
	 * @author Marcos García
	 */
	function dumpRelationship($userID, $toFollow, $read = true)
	{
		$recipro = $this->newFollowing($userID, $toFollow, $read);

		$viewable = checkViewableUser($userID, $toFollow, 'show_notes');
		$viewable2 = checkViewableUser($toFollow, $userID, 'show_notes');

		if ($viewable) {
			$notes = $this->getLastNotes($toFollow);

			$bulk = array();
			$this->updateUserOptions($userID, array('last_follow' => time()));

			foreach ($notes as $note) {
				$bulk[] = "(".(int)$toFollow.", ".(int)$userID.", ".(int)$note['ID'].", ".(int)$note['timestamp'].", ".(int)$note['reply_user'].", '".$note['type']."')";
			}
			$this->post2id($bulk);
		}
		elseif ($viewable2) {
			$mynotes = $this->getLastNotes($userID);
			$hisnotes = $this->getLastNotes($toFollow);

			$bulk = array();
			$this->updateUserOptions($userID, array('last_follow' => time()));

			foreach ($mynotes as $note) $bulk[] = "(".(int)$userID.", ".(int)$toFollow.", ".(int)$note['ID'].", ".(int)$note['timestamp'].", ".(int)$note['reply_user'].", '".$note['type']."')";
			foreach ($hisnotes as $note) $bulk[] = "(".(int)$toFollow.", ".(int)$userID.", ".(int)$note['ID'].", ".(int)$note['timestamp'].", ".(int)$note['reply_user'].", '".$note['type']."')";
			$this->post2id($bulk);
		}
	}
	
	/**
	 * Remove relationship between $userID and $toFollow
	 *
	 * @param string $userID 
	 * @param string $toFollow 
	 * @return void
	 * @author Marcos García
	 */
	function removeRelationship($userID, $toFollow)
	{
		$me = $this->getUserOptions($userID, array('privacy'));

		if ($me['show_notes'] < 3 && ($me['show_notes'] > 1)) {
			$this->send("DELETE FROM `post2id` WHERE `post2id`.`from` = ".(int) $userID." AND `post2id`.`to` = ".(int) $toFollow);
		}
		$this->deleteFollowing($userID, $toFollow);
		$this->send("DELETE FROM `post2id` WHERE `post2id`.`from` = ".(int) $toFollow." AND `post2id`.`to` = ".(int) $userID);
	}
	
	/**
	 * It returns the number of notes between $time and $limit
	 *
	 * @param string $time 
	 * @param string $limit 
	 * @return void
	 * @author Marcos García
	 */
	function retrieveTimeNotes($time, $limit = 86400)
	{
		$query = $this->send("SELECT COUNT(*) FROM `notes` WHERE `timestamp`>(UNIX_TIMESTAMP()-$time) AND `timestamp`<(UNIX_TIMESTAMP()-($time - $limit))");
		return (int) mysql_result($query, 0);
	}
}

?>
