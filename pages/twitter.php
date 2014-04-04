<?php
// Jisko: An open-source microblogging application
// Copyright (C) 2008-10 RubŽn D’az <outime@gmail.com>
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
global $jk;

//Only logged users can access this section
if ($_USER) {

	//The user wants to link their account with Twitter
	if (isset($_GET['connect'])) {
	
		//We have to check &auth param to know that the user is following the correct link
		if (isset($_GET['auth']) && ($_GET['auth'] == md5($_USER['salt']))) {
			import('twitter/toauth.class');
			
			//Calling the tOAuth class
			$connection = new tOAuth($jk->tw_consumerkey, $jk->tw_secretkey);
			
			//We tell Twitter that we are going to authenticate an user
			$auth = $connection->authenticate(true);
			
			//Check that the request was OK
			if ($auth['oauth_token'] && $auth['oauth_token_secret'] && $auth['request_link']) {
			
				//We have to store those keys in a $_SESSION variable so...
				session_start();
				$_SESSION['oauth_token'] = $auth['oauth_token'];
				$_SESSION['oauth_token_secret'] = $auth['oauth_token_secret'];
				
				//Now we redirect the user to the twitter oauth page
				header('Location: '.$auth['request_link']);
			}
			else {
				//Something is not working... we throw an error
				header('Location: '.coreLink(array('error=token'), 'settings', 'twitter'));
			}
		}
		else {
			//The user wasn't following the link from the settings page, so we redirect him back to it
			header('Location: '.coreLink(array('error=auth'), 'settings', 'twitter'));
		}
	}
	
	//The user comes from Twitter authorization page
	elseif (isset($_GET['callback'])) {
		import('twitter/toauth.class');
		
		//We have to check that we have the previous keys
		session_start();
		
		if ($_SESSION['oauth_token'] && $_SESSION['oauth_token_secret']) {
			
			//Calling the tOAuth class but with the previous keys
			$connection = new tOAuth($jk->tw_consumerkey, $jk->tw_secretkey, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
			
			//This time we don't need to authenticate the user
			$auth = $connection->authenticate(false);
			
			//Check that the request was OK
			if ($auth['oauth_token'] && $_SESSION['oauth_token_secret']) {
				
				//So we store these keys into the database for later use and
				//we enable the default settings for Twitter integration
				$db->updateTwitterOptions($_USER['ID'], array(
					'oauth_token' => $auth['oauth_token'],
					'oauth_token_secret' => $auth['oauth_token_secret'],
					'post_tweets' => (bool) $_POST['post_tweets'],
					'combined_view' => (bool) $_POST['combined_view']
				));
				
				//We don't need those keys anymore
				session_unset();
				
				//We retrieve the users' twitter notes.
				updateTwitterNotes();
				
				//We redirect back to the settings page
				header('Location: '.coreLink(array('ok'), 'settings', 'twitter'));

			}
			else {
				//Something is not working... we throw an error
				header('Location: '.coreLink(array('error=token'), 'settings', 'twitter'));
			}
		}
		else {
			//Something is not working... we throw an error
			header('Location: '.coreLink(array('error=token'), 'settings', 'twitter'));
		}		
	}
	else {
		//Where are you trying to go? Caught!
		header('Location: '.$jk->base);
	}
}
else {
	//The user has to be logged in
	header('Location: '.$jk->base);
}

?>