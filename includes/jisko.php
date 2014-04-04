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

class Jisko
{
	private $var_glob = array();
	private $var_user = array();
	private $var_note = array();
	private $var_tags = array();

	function __get($key)
	{
		if (array_key_exists($key, $this->var_glob)) return $this->var_glob[$key];
		else return false;
	}

	function __set($key, $value)
	{
		$this->var_glob[$key] = $value;
		return true;
	}

	function __isset($key)
	{
		return (bool) isset($this->var_glob[$key]);
	}
	
	function loadConfig() {
		global $db;
		
		if ($db->connected == true) {
			$settings = $db->getJiskoSettings(array(
				'base_url',
				'name',
				'admin_mail',
				'abuse_mail',
				'cron_pw',
				'meta_keywords',
				'meta_robots',
				'meta_description',
				'separator',
				'wait_until_repost',
				'wait_until_refollow',
				'ajax_refresh',
				'language',
				'notes_per_page',
				'clean_urls',
				'use_invitations',
				'enable_mbstring',
				'alert_on_newuser',
				'alert_on_deluser',
				'no_confirmation_email',
				'is_debug',
				'default_url_shorter',
				'allowed_url_shorters',
				'threely_apicode',
				'bitly_login',
				'bitly_apicode',
				'recaptcha_publickey',
				'recaptcha_secretkey',
				'denied_extensions',
				'home_page',
				'fb_apikey',
				'fb_secretkey',
				'default_theme',
				'allowed_themes',
				'tos',
				'maintenance',
				'logo',
				'tw_secretkey',
				'tw_consumerkey'
			));
			
			$this->name = $settings['name'];
			$this->base = $settings['base_url'];
			$this->notes_per_page = (int) $settings['notes_per_page'];
			$this->admin_mail = $settings['admin_mail'];
			$this->abuse_mail = $settings['abuse_mail'];
			$this->cron_password = $settings['cron_pw'];
			$this->default_lang = $settings['language'];
			$this->meta_keywords = $settings['meta_keywords'];
			$this->meta_description = $settings['meta_description'];
			$this->meta_robots = $settings['meta_robots'];
			$this->separator = $settings['separator'];
			$this->ajax_refresh = $settings['ajax_refresh'];
			$this->wait_until_repost = (int) $settings['wait_until_repost'];
			$this->wait_until_refollow = (int) $settings['wait_until_refollow'];
			$this->cleanUrls = (bool) $settings['clean_urls'];
			$this->use_invitations = (bool) $settings['use_invitations'];
			$this->enable_mbstring = (bool) $settings['enable_mbstring'];
			$this->alert_on_newuser = (bool) $settings['alert_on_newuser'];
			$this->alert_on_deluser = (bool) $settings['alert_on_deluser'];
			$this->no_confirmation_email = (bool) $settings['no_confirmation_email'];
			$this->is_debug = (bool) $settings['is_debug'];
			$this->default_shorter_service = $settings['default_url_shorter'];
			if ($settings['allowed_url_shorters']) $this->allowed_shorter_service = unserialize(stripslashes($settings['allowed_url_shorters']));
			else $this->allowed_shorter_service = array();
			$this->threely_apicode = $settings['threely_apicode'];
			$this->bitly_login = $settings['bitly_login'];
			$this->bitly_apicode = $settings['bitly_apicode'];
			$this->recaptcha_publickey = $settings['recaptcha_publickey'];
			$this->recaptcha_privatekey = $settings['recaptcha_secretkey'];
			if ($settings['denied_extensions']) $this->denied_extensions = unserialize(stripslashes($settings['denied_extensions']));
			else $this->denied_extensions = array();
			$this->home_page = $settings['home_page'];
			$this->fb_apikey = $settings['fb_apikey'];
			$this->fb_secretkey = $settings['fb_secretkey'];
			$this->default_theme = $settings['default_theme'];
			if ($settings['allowed_themes']) $this->allowed_themes = unserialize(stripslashes($settings['allowed_themes']));
			else $this->allowed_themes = array();
			$this->tos = (bool) $settings['tos'];
			$this->maintenance = $settings['maintenance'];
			$this->logo = $settings['logo'];
			if (defined('SHARED_HOST')) $this->shared_host = (bool)SHARED_HOST;
			else $this->shared_host = false;
			$this->tw_secretkey = $settings['tw_secretkey'];
			$this->tw_consumerkey = $settings['tw_consumerkey'];
		}
	}

	function load($file)
	{
		global $_USER;
		if ($_USER['theme']) include PATH.'themes/'.$_USER['theme'].'/pages/'.$file.'.php';
		else include PATH.'themes/'.$this->default_theme.'/pages/'.$file.'.php';
	}

	function selectUser($userID, $customize = false)
	{
		global $db;
		
		if (!$customize) $check = $db->getUserOptions($userID, array('username', 'language', 'avatar', 'realname', 'location', 'status', 'since', 'last_seen', 'last_note', 'profile'));
		else $check = $db->getUserOptions($userID, array('username', 'language', 'avatar', 'realname', 'location', 'status', 'since', 'last_seen', 'last_note', 'profile', 'customize'));
		
		if ($check) {
			$this->var_user = array();
			$check['ID'] = $userID;
			if ($check['profile']) {
				foreach ($check['profile'] as $key=>$content) {
					$check['profile_'.$key] = $content;
				}
			}
			if ($check['customize']) {
				foreach ($check['customize'] as $key=>$content) {
					$check['customize_'.$key] = $content;
				}
			}

			$this->var_user = $check;
		}
		else $this->var_user = array();
		return $this->var_user;
	}

	function selectTag($tag)
	{
		global $db;
		$check = $db->getInfoTag($tag, true);
		if ($check) {
			$this->var_tags = array_merge(array('name' => $tag), $check);
		}
		else return false;
	}

	function selectSelfUser()
	{
		global $_USER;
		if ($_USER) {
			$check = $_USER;
			$this->var_user = array();
			if ($_USER['profile']) {
				foreach ($_USER['profile'] as $key=>$content) {
					$check['profile_'.$key] = $content;
				}
			}
			if ($_USER['shorter_service']) {
				foreach ($check['shorter_service'] as $key=>$content) {
					$check['shorter_'.$key] = $content;
				}
			}
			if ($_USER['customize']) {
				foreach ($check['customize'] as $key=>$content) {
					$check['customize_'.$key] = $content;
				}
			}
			if ($_USER['privacy']) {
				foreach ($check['privacy'] as $key=>$content) {
					$check['privacy_'.$key] = $content;
				}
			}
			else {
				foreach (array('show_followings', 'show_followers', 'show_notes', 'show_favorite', 'show_profile_info', 'allow_read_rss') as $content) {
					$check['privacy_'.$content] = 3;
				}
			}
			if ($_USER['twitter']) {
				foreach ($check['twitter'] as $key=>$content) {
					$check['twitter_'.$key] = $content;
				}
			}
			$this->var_user = $check;
		}
	}
	
	function updateUser($key, $value)
	{
		if (array_key_exists($key, $this->var_user)) {
			$this->var_user[$key] = $value;
		}
		else return false;
	}

	function user($key)
	{
		if (array_key_exists($key, $this->var_user)) {
			return $this->var_user[$key];
		}
		else return false;
	}

	function note($key)
	{
		if (array_key_exists($key, $this->var_note)) {
			return $this->var_note[$key];
		}
		else return false;
	}

	function tag($key)
	{
		if (array_key_exists($key, $this->var_tags)) {
			return $this->var_tags[$key];
		}
		else return false;
	}
}

?>