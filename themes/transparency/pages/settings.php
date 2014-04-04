<script>
$(document).ready(
	function() {
		$('.content_left_top').css('float', 'none');
		$('.content_left_top').css('margin', 'auto');
		$('.content_left_top').css('width', '594px');
		$('.content_top').css('background-image', 'url(<?php echo $this->base ?>themes/transparency/img/web_bg3.png)');
	}
);
</script>
<div style="margin:auto">
<?php if ($this->page_module == 'profile'): ?>
	<h1 class="user" style="font-family:Arial;letter-spacing:-1px;text-align:center"><?php t('Profile') ?></h1>
	<br />
	<ul class="tags" style="margin:auto;width:100%">
		<li class="left_item current_item"><a href="<?php anchor('settings', 'profile') ?>"><?php t('Profile') ?></a></li>
		<li><a href="<?php anchor('settings', 'config') ?>"><?php t('Configuration') ?></a></li>
		<li><a href="<?php anchor('settings', 'privacy') ?>"><?php t('Privacy') ?></a></li>
		<li><a href="<?php anchor('settings', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<li class="right_item"><a href="<?php anchor('settings', 'ignores') ?>"><?php t('Ignored users') ?></a></li>
	</ul>
	<br />
	<br /><br />
	<form method="post" action="<?php anchor('settings', 'profile') ?>" enctype="multipart/form-data" style="width:554px;margin:auto">
		<div style="float:right">
			<p>
				<?php t('Bio (140 chars max.)') ?>
				<br />
				<textarea name="bio" type="text" class="input_textarea" rows="8"><?php show($this->user('profile_bio')) ?></textarea>
			</p>
		</div>
		<p><?php t('Name') ?><br /><input name="realname" type="text" class="input" value="<?php show($this->user('realname')) ?>" /></p>
		<p style="height:6px"></p>
		<p><?php t('Webpage') ?><br /><input name="url" type="text" class="input" value="<?php show($this->user('profile_url')) ?>" /> </p>
		<p style="height:6px"></p>
		<p><?php t('Location') ?><br /><input name="location" type="text" class="input" value="<?php show($this->user('location')) ?>" /> </p>
		<br />
		<p>
			<?php t('Language') ?><br />
			<select name="language" id="lang" class="listbox">
				
				<?php foreach (return_languages() as $short => $name): ?>
					<?php if ($this->user('language') == $short): ?>
					<option value="<?php show($short) ?>" selected><?php show($name) ?></option>
					<?php else: ?>
					<option value="<?php show($short) ?>"><?php show($name) ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</p>
		<br />
		<br />
		<div style="width:310px">
			<?php t('Change my profile picture') ?><br /><p style="height:6px"></p>
			<div style="float:right;">
				<p>
					<input name="avatar" class="input" type="file" style="width:200px"/>
					<br /><small><?php t('250 KB max. size, jpg/png/gif format') ?></small>
					<br /><br />
					<?php if ($this->user('avatar')): ?>
					<span style="font-weight:600">
						<a href="<?php anchor(array('action=delete_avatar', 'auth='.md5($this->user('salt'))), 'settings', 'profile') ?>"><?php t('Delete it') ?></a>
					</span>
					<?php else: ?>
						<?php if ($this->user('gravatar') == 1): ?>
						<input type="checkbox" name="use_gravatar" checked/>
						<?php else: ?>
						<input type="checkbox" name="use_gravatar" />
						<?php endif; ?>
						<?php t('Enable Gravatar service') ?>
					<?php endif; ?>
				</p>
			</div>
			<img src="<?php get_avatar($this->user('ID'), 150) ?>" width="80" height="80"/>
		</div>
		<p><input type="hidden" name="auth" value="<?php show(md5($this->user('salt'))) ?>" id="hiddenkey" /></p>
		<br /><br /><p><input type="submit" value="<?php t('Save') ?>" class="submit" /></p><br />
		
	</form>
<?php elseif ($this->page_module == 'config'): ?>
	<?php if (isFacebookEnabled()): ?>
	<link rel="stylesheet" href="http://www.facebook.com/css/connect/connect_button.css" type="text/css" />
	<?php endif; ?>
	<h1 class="user" style="font-family:Arial;letter-spacing:-1px;text-align:center"><?php t('Configuration') ?></h1>
	<br />
	<ul class="tags" style="margin:auto;width:100%">
		<li class="left_item"><a href="<?php anchor('settings', 'profile') ?>"><?php t('Profile') ?></a></li>
		<li class="current_item"><a href="<?php anchor('settings', 'config') ?>"><?php t('Configuration') ?></a></li>
		<li><a href="<?php anchor('settings', 'privacy') ?>"><?php t('Privacy') ?></a></li>
		<li><a href="<?php anchor('settings', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<li class="right_item"><a href="<?php anchor('settings', 'ignores') ?>"><?php t('Ignored users') ?></a></li>
	</ul>
	<br />
	<br><br>
	<form method="post" action="<?php anchor('settings', 'config') ?>" style="width:554px;margin:auto">
		<p><?php t('change my username') ?><br /><input name="new_username" type="text" class="input" tabindex="1" value="<?php show($this->user('username')) ?>"/> </p>
		<p style="height:6px"></p>
		<div>
			<div style="float:right">
				<p class="second_col"><?php t('repeat new password') ?><br /><input name="new_password2" type="password" class="input" tabindex="3"/> </p>
			</div>
			<p><?php t('new password (if you want to change it)') ?><br /><input name="new_password" type="password" class="input" tabindex="2"/> </p>
			<p style="height:6px"></p>
		</div>
		<div>
			<p><?php t('e-mail') ?><br /><input name="email" type="text" class="input" value="<?php show($this->user('email')) ?>" tabindex="4"/> </p>
		</div>
		<p style="height:6px"></p>
		<div>
			<?php if (isFacebookEnabled()): ?>
			<div style="float:right;width:266px">
				<p class="second_col" style="float:left;padding-top:16px">
				<?php if ($this->user('facebook')): ?>
				<a href="<?php anchor(array('unlink', 'auth='.md5($this->user('salt'))), 'facebook') ?>" class="fbconnect_login_button FBConnectButton FBConnectButton_Medium"> <span id="RES_ID_fb_login_text" class="FBConnectButton_Text"><?php t('Disable connection with Facebook') ?></span> </a>
				<?php else: ?>
				<a href="<?php anchor(array('link', 'auth='.md5($this->user('salt'))), 'facebook') ?>" class="fb_button fb_button_medium "><span class="fb_button_text"><?php t('Connect my account with Facebook') ?></span></a>
				<?php endif; ?>
				</p>
			</div>
			<?php endif; ?>
			<p><?php t('OpenID URL') ?><br /><input type="text" class="input" value="<?php show($this->user('openid')) ?>" name="openid" tabindex="6"/></p>
		</div>
		
		<p style="height:6px"></p>
		
		<p style="height:6px"></p>
		<div style="float:right">
			<p class="second_col" style="width:256px;margin-top:20px;">
				<input type="checkbox" name="new_api" tabindex="7"/><?php t('Request new API key') ?>
			</p>
		</div>
		<p>
			<?php t('API key') ?><br />
			<input type="text" class="input" value="<?php show($this->user('api')) ?>" readonly tabindex="6"/>
		</p>
		<p style="height:6px"></p>
		<p>
			<?php t('Notification level') ?><br />
			<select name="notification_level" id="level" class="listbox" tabindex="8">
				<?php if ($this->user('notification_level') == 5): ?>
				<option value="5" selected><?php t('Everything') ?></option>
				<?php else: ?>
				<option value="5"><?php t('Everything') ?></option>
				<?php endif; ?>
				
				<?php if ($this->user('notification_level') == 4): ?>
				<option value="4" selected><?php t('Privates and Following') ?></option>
				<?php else: ?>
				<option value="4"><?php t('Privates and Following') ?></option>
				<?php endif; ?>
				
				<?php if ($this->user('notification_level') == 3): ?>
				<option value="3" selected><?php t('Replies') ?></option>
				<?php else: ?>
				<option value="3"><?php t('Replies') ?></option>
				<?php endif; ?>
				
				<?php if ($this->user('notification_level') == 2): ?>
				<option value="2" selected><?php t('Privates') ?></option>
				<?php else: ?>
				<option value="2"><?php t('Privates') ?></option>
				<?php endif; ?>
				
				<?php if ($this->user('notification_level') == 1): ?>
				<option value="1" selected><?php t('Following') ?></option>
				<?php else: ?>
				<option value="1"><?php t('Following') ?></option>
				<?php endif; ?>
				
				<?php if ($this->user('notification_level') == 0): ?>
				<option value="0" selected><?php t('None') ?></option>
				<?php else: ?>
				<option value="0"><?php t('None') ?></option>
				<?php endif; ?>
			</select>
		</p>
		<br />
		<div style="float:right">
			<p class="second_col" style="width:256px;margin-top:10px;">
				<?php if ($this->user('shorter_preview') == true): ?>
				<input type="checkbox" name="shorted_preview" tabindex="9" checked />
				<?php else: ?>
				<input type="checkbox" name="shorted_preview" tabindex="9" />
				<?php endif; ?>
				<?php t('Try to always show the preview page of a shorted link') ?>
			</p>
		</div>
		<p>
			<?php t('URL Shortening service') ?><br />
			<select name="shorter_service" id="shrter_service" class="listbox" tabindex="8">
				<?php foreach(load_url_shorters() as $shorter=>$name): ?>
				<?php if ($shorter == $this->user('shorter_service')): ?>
					<option value="<?php echo $shorter ?>" selected><?php echo $name ?></option>
				<?php else: ?>
					<option value="<?php echo $shorter ?>"><?php echo $name ?></option>
				<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</p>
		<br />
		<br />
		<p><?php t('current password') ?> <strong><?php t("(if you don't have a password just leave it blank)") ?></strong><br /><input name="current_password" type="password" class="input" tabindex="10"/> </p>
		<br />
		<p><input type="submit" value="<?php t('Save') ?>" class="submit" tabindex="11"/></p>
		<br />
		<a href="<?php anchor('drop_account') ?>" tabindex="12"><strong><?php t('Delete my account') ?></strong></a>
	</form>
	
<?php elseif ($this->page_module == 'customize'): ?>
	<script type="text/javascript" src="<?php get_permalink('js/jscolor/jscolor.js') ?>"></script>
	<h1 class="user" style="font-family:Arial;letter-spacing:-1px;text-align:center"><?php t('Customize') ?></h1>
	<br />
	<ul class="tags" style="margin:auto;width:100%">
		<li class="left_item"><a href="<?php anchor('settings', 'profile') ?>"><?php t('Profile') ?></a></li>
		<li><a href="<?php anchor('settings', 'config') ?>"><?php t('Configuration') ?></a></li>
		<li><a href="<?php anchor('settings', 'privacy') ?>"><?php t('Privacy') ?></a></li>
		<li><a href="<?php anchor('settings', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<li class="right_item"><a href="<?php anchor('settings', 'ignores') ?>"><?php t('Ignored users') ?></a></li>
	</ul>
	<br /><br /><br />
	<form method="POST" action="<?php anchor('settings', 'customize') ?>" enctype="multipart/form-data" style="width:554px;margin:auto">
		<p>
			<?php t('Theme') ?><br />
			<SELECT name="theme" class="listbox">
				<?php foreach (load_themes() as $thm=>$thmname): ?>
					<?php if ($thm == $this->user('theme')): ?>
					<option name="them" value="<?php echo $thm ?>" selected><?php echo $thmname ?></option>
					<?php else: ?>
					<option name="them" value="<?php echo $thm ?>"><?php echo $thmname ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</p>
		<br />
		<p style="height:6px"></p>
		<div style="float:right">
			<p class="second_col">
				<?php t('Background style') ?><br />
				<SELECT name="style" class="listbox">
					<?php foreach (load_background_styles() as $sty=>$styname): ?>
						<?php if ($sty == $this->user('customize_background_style')): ?>
						<option value="<?php echo $sty ?>" selected><?php echo $styname ?></option>
						<?php else: ?>
						<option value="<?php echo $sty ?>"><?php echo $styname ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<?php t('Background') ?><br />
		<p><input name="background" class="input" type="file" /></p>
		<small><?php t('1 MB max. size, jpg/png/gif format') ?></small>
		<br /><br />
		<div style="float:right">
			<p class="second_col"><?php t('Background color') ?><br /><input name="background_color" type="text" class="input color{hash:true,required:false}" value="<?php show($this->user('customize_profile_background_color')) ?>"/> </p>
		</div>
		<p class="second_col"><?php t('Text color') ?><br /><input name="text_color" type="text" class="input color{hash:true,required:false}" value="<?php show($this->user('customize_profile_text_color')) ?>"/> </p>
		
		<p style="height:6px"></p>
		<div style="float:right">
			<p class="second_col"><?php t('Sidebar color') ?><br /><input name="sidebar_color" type="text" class="input color{hash:true,required:false}" value="<?php show($this->user('customize_profile_sidebar_color')) ?>"/> </p>
		</div>
		<p class="second_col"><?php t('Links color') ?><br /><input name="links_color" type="text" class="input color{hash:true,required:false}" value="<?php show($this->user('customize_profile_links_color')) ?>"/> </p>
		<p style="height:6px"></p>
		<p class="second_col"><?php t('Sidebar text color') ?><br /><input name="sidebar_text_color" type="text" class="input color{hash:true,required:false}" value="<?php show($this->user('customize_profile_sidebar_text_color')) ?>"/> </p>
		<p style="height:6px"></p>
		<br /><br />
		<p><input type="submit" value="<?php t('Save') ?>" class="submit" />
		<?php if ($this->user('customize_background')): ?>
		&nbsp;<span style="padding-left:10px;font-weight:600"><a href="<?php anchor(array('action=delete', 'auth='.md5($this->user('salt'))), 'settings', 'customize') ?>"><?php t('Delete background') ?></a></span></p>
		<?php endif; ?>
	</form>
<?php elseif ($this->page_module == 'twitter'): ?>
	<h1 class="user" style="font-family:Arial;letter-spacing:-1px;text-align:center"><?php t('Twitter') ?></h1>
	<br />
	<ul class="tags" style="margin:auto;width:100%">
		<li class="left_item"><a href="<?php anchor('settings', 'profile') ?>"><?php t('Profile') ?></a></li>
		<li><a href="<?php anchor('settings', 'config') ?>"><?php t('Configuration') ?></a></li>
		<li><a href="<?php anchor('settings', 'privacy') ?>"><?php t('Privacy') ?></a></li>
		<li class="current_item"><a href="<?php anchor('settings', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<li class="right_item"><a href="<?php anchor('settings', 'ignores') ?>"><?php t('Ignored users') ?></a></li>
	</ul>
	<br /><br /><br /><br /><br />
	<?php if (has_twitter()): ?>
	<div style="float:right;border-left: 1px solid #808080;padding-left:20px;width:220px">
		<?php if ($this->user('twitter_last_update')): ?>
		<h2><?php t("Twitter integration it's working properly") ?></h2>
		<?php else: ?>
		<h2><?php t("Twitter notes not updated yet...") ?></h2>
		<?php endif; ?>
		<br>
		<?php if ($this->user('twitter_last_update')): ?>
		<br /><?php showStatus(t('Twitter updated %s', showTimeAgo($this->user('twitter_last_update'))), 'ok'); ?>
		<?php else: ?>
		<br /><?php showStatus(t('Twitter not updated yet, wait...'), 'error'); ?>
		<?php endif; ?>
		<br><br>
	</div>
	<?php endif; ?>
	<form action="<?php anchor('settings', 'twitter') ?>" method="post" autocomplete="off" style="width:454px;margin:auto">
		<p><img width="125" height="29" src="<?php get_permalink('img/logos/logo_twitter.png') ?>" alt="Twitter" /></p>
		<br />
		<p><input type="hidden" name="auth" value="<?php echo md5($this->user('salt')) ?>" id="hiddenkey" />
		<?php if (!has_twitter()): ?>
		<a href="<?php anchor(array('connect', 'auth='.md5($this->user('salt'))), 'twitter') ?>"><img src="<?php get_permalink('img/Sign-in-with-Twitter-darker.png') ?>" /></a>
		<?php else: ?>
		<strong><a href="<?php anchor(array('action=delete', 'auth='.md5($this->user('salt'))), 'settings', 'twitter') ?>"><?php t('Disable twitter integration') ?></a></strong>
		<?php endif; ?>
		</p>
		
		<p style="height:6px"></p><p style="height:6px"></p>
		<?php if ($this->user('twitter_combined_view') == true): ?>
		<p><input type="checkbox" name="combined_view" checked="checked" /> <?php t('Show tweets in the friends tab') ?></p>
		<?php else: ?>
		<p><input type="checkbox" name="combined_view" /> <?php t('Show tweets in the friends tab') ?></p>
		<?php endif; ?>
		<?php if ($this->user('twitter_post_tweets') == true): ?>
		<p><input type="checkbox" name="post_tweets" checked="checked" /> <?php t('Post %s notes to Twitter', $this->name) ?></p>
		<?php else: ?>
		<p><input type="checkbox" name="post_tweets" /> <?php t('Post %s notes to Twitter', $this->name) ?></p>
		<?php endif; ?>
		<p style="height:6px"></p>
		<br />
		<p>
			<input type="submit" value="<?php t('Save') ?>" class="submit"/>
		</p>
	</form>
<?php elseif ($this->page_module == 'ignores'): ?>
	<h1 class="user" style="font-family:Arial;letter-spacing:-1px;text-align:center"><?php t('Ignored users') ?></h1>
	<br />
	<ul class="tags" style="margin:auto;width:100%">
		<li class="left_item"><a href="<?php anchor('settings', 'profile') ?>"><?php t('Profile') ?></a></li>
		<li><a href="<?php anchor('settings', 'config') ?>"><?php t('Configuration') ?></a></li>
		<li><a href="<?php anchor('settings', 'privacy') ?>"><?php t('Privacy') ?></a></li>
		<li><a href="<?php anchor('settings', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<li class="right_item current_item"><a href="<?php anchor('settings', 'ignores') ?>"><?php t('Ignored users') ?></a></li>
	</ul>
	<br /><br /><br />
	<?php if (count_ignored() == 0): ?>
	<?php echo showStatus(__('You are not ignoring anyone'), 'warning'); ?>
	<?php else: ?>
		<div id="note_list">
			<?php foreach(get_ignored() as $follower) showUser($follower); ?>
		</div>
	<?php endif; ?>
<?php elseif ($this->page_module == 'privacy'): ?>
	<h1 class="user" style="font-family:Arial;letter-spacing:-1px;text-align:center"><?php t('Privacy') ?></h1>
	<br />
	<ul class="tags" style="margin:auto;width:100%">
		<li class="left_item"><a href="<?php anchor('settings', 'profile') ?>"><?php t('Profile') ?></a></li>
		<li><a href="<?php anchor('settings', 'config') ?>"><?php t('Configuration') ?></a></li>
		<li class="current_item"><a href="<?php anchor('settings', 'privacy') ?>"><?php t('Privacy') ?></a></li>
		<li><a href="<?php anchor('settings', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<li class="right_item"><a href="<?php anchor('settings', 'ignores') ?>"><?php t('Ignored users') ?></a></li>
	</ul>
	<br /><br /><br />
	<form action="<?php anchor('settings', 'privacy') ?>" method="post" style="width:554px;margin:auto">
	<div style="float:right">
		<p class="second_col"><?php t('Show my followings') ?><br />
		<SELECT name="show_followings" class="listbox">
			<?php foreach (load_privacy_options() as $n=>$m): ?>
				<?php if ($n == $this->user('privacy_show_followings')): ?>
				<option value="<?php echo $n ?>" selected><?php echo $m ?></option>
				<?php else: ?>
				<option value="<?php echo $n ?>"><?php echo $m ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select></p>
	</div>
	<p class="second_col"><?php t('Show my followers') ?><br />
	<SELECT name="show_followers" class="listbox">
		<?php foreach (load_privacy_options() as $n=>$m): ?>
			<?php if ($n == $this->user('privacy_show_followers')): ?>
			<option value="<?php echo $n ?>" selected><?php echo $m ?></option>
			<?php else: ?>
			<option value="<?php echo $n ?>"><?php echo $m ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	</select></p>
	<br /><br />
	<div style="float:right">
	<p class="second_col"><?php t('Show my notes') ?><br />
	<SELECT name="show_notes" class="listbox">
		<?php foreach (load_privacy_options() as $n=>$m): ?>
			<?php if ($n == $this->user('privacy_show_notes')): ?>
			<option value="<?php echo $n ?>" selected><?php echo $m ?></option>
			<?php else: ?>
			<option value="<?php echo $n ?>"><?php echo $m ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	</select></p>
	</div>
	<p class="second_col"><?php t('Show my favorite notes') ?><br />
	<SELECT name="show_favorite" class="listbox">
		<?php foreach (load_privacy_options() as $n=>$m): ?>
			<?php if ($n == $this->user('privacy_show_favorite')): ?>
			<option value="<?php echo $n ?>" selected><?php echo $m ?></option>
			<?php else: ?>
			<option value="<?php echo $n ?>"><?php echo $m ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	</select></p>
	<br /><br />
	<div style="float:right">
	<p class="second_col"><?php t('Show my profile info') ?><br />
	<SELECT name="show_profile_info" class="listbox">
		<?php foreach (load_privacy_options() as $n=>$m): ?>
			<?php if ($n == $this->user('privacy_show_profile_info')): ?>
			<option value="<?php echo $n ?>" selected><?php echo $m ?></option>
			<?php else: ?>
			<option value="<?php echo $n ?>"><?php echo $m ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	</select></p>
	</div>
	<p class="second_col"><?php t('Allow to read my RSS') ?><br />
	<SELECT name="allow_read_rss" class="listbox">
		<?php foreach (array('3' => __('Everyone'), '0' => __('Nobody')) as $n=>$m): ?>
			<?php if ($n == $this->user('privacy_allow_read_rss')): ?>
			<option value="<?php echo $n ?>" selected><?php echo $m ?></option>
			<?php else: ?>
			<option value="<?php echo $n ?>"><?php echo $m ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	</select></p><br />
	<br />
	<p>
		<input type="submit" value="<?php t('Save') ?>" class="submit"/>
	</p>
<?php endif; ?>
</div>