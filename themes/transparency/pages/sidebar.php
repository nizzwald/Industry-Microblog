</div>
<div class="content_right_top">
	<?php if (get_type_sidebar() != 'no_sidebar'): ?>
	<?php if (is_logged()): ?>
		<div class="user_side">
		<?php if (get_type_sidebar() == 'profile'): ?>
				<img height="150" width="150" src="<?php get_avatar($this->user('ID')) ?>" alt="<?php t('Avatar') ?>" /><br />
				<a href="<?php anchor($this->user('username')) ?>" class="user"><?php show($this->user('username')) ?></a><br /><br />
			<form method="post" action="<?php anchor('follow') ?>" onsubmit="return false;">
			<input type="hidden" name="id" value="<?php show($this->user('ID')) ?>" />
			<?php if (following($this->user('ID'))): ?>
			<input type="submit" value="<?php t('Stop Following') ?>" class="submit_follow" onclick="follow_user(<?php show($this->user('ID')) ?>, '');" id="follow" />
			<?php else: ?>
			<input type="submit" value="<?php t('Follow') ?>" class="submit_follow" onclick="follow_user(<?php show($this->user('ID')) ?>, '');" id="follow" />
			<?php endif; ?>
			</form>
			<form method="post" action="<?php anchor('ignore') ?>" onsubmit="return false;">
			<input type="hidden" name="id" value="<?php show($this->user('ID')) ?>" />
			<?php if (ignoring($this->user('ID'))): ?>
			<input type="submit" value="<?php t('Stop Ignoring') ?>" class="submit" onclick="ignore_user(<?php show($this->user('ID')) ?>);" id="ignore" /><br />
			<?php else: ?>
			<input type="submit" value="<?php t('Ignore') ?>" class="submit" onclick="ignore_user(<?php show($this->user('ID')) ?>);" id="ignore" /><br />
			<?php endif; ?>
			</form>
			<br />
			<span class="report_button"><a href="<?php anchor('report', 'user', $this->user('ID')) ?>"><?php t('Report') ?></a></span>
		</div>
		<?php if ($this->viewable_profile == true): ?>
			<div class="info_title"><img src="<?php get_permalink('img/icons/information.png') ?>" height="14" width="14" alt="<?php t('About') ?>" /> <?php t('About') ?></div>
			<div class="info_user">
				<?php if ($this->user('realname')): ?>
				<span class="bold"><?php t('Name') ?></span> <?php show($this->user('realname')) ?><br />
				<?php endif; ?>
				<?php if ($this->user('location')): ?>
				<span class="bold"><?php t('Location') ?></span> <?php show($this->user('location')) ?><br />
				<?php endif; ?>
				<?php if ($this->user('profile_url')): ?>
				<span class="bold"><?php t('Web') ?></span> <a href="<?php show($this->user('profile_url')) ?>" rel="external" class="external"><?php show($this->user('profile_url')) ?></a><br />
				<?php endif; ?>
				<?php if ($this->user('profile_bio')): ?>
				<span class="bold"><?php t('Bio') ?></span> <?php show($this->user('profile_bio')) ?><br />
				<?php endif; ?>
				<span class="bold"><?php t('Since') ?></span> <?php _date_format($this->user('since')) ?><br />
				<span class="bold"><?php t('Last login') ?></span>
				<?php if (get_last_seen($this->user('last_seen')) == 'online'): ?>
				<span style="color:green">online</span>
				<?php else: ?>
				<?php echo get_last_seen($this->user('last_seen')); ?>
				<?php endif; ?>
			</div>
			<div class="info_title"><img src="<?php get_permalink('img/icons/chart_bar.png') ?>" height="14" width="14" alt="<?php t('Stats') ?>" /> <?php t('Stats') ?></div>
			<div class="info_user">
				<span class="bold"><a href="<?php anchor($this->user('username'), 'following') ?>"><?php t('Following') ?></a></span> <?php count_following($this->user('ID')) ?><br />
				<span class="bold"><a href="<?php anchor($this->user('username'), 'followers') ?>"><?php t('Followers') ?></a></span> <span id="sfollowers"><?php count_followers($this->user('ID')) ?></span><br />
				<span class="bold"><a href="<?php anchor($this->user('username'), 'favorites') ?>"><?php t('Favorites') ?></a></span> <span id="sfavorites"><?php count_favorites($this->user('ID')) ?></span><br />
				<span class="bold"><?php t('Notes') ?></span> <span id="ajax_notes"><?php count_notes($this->user('ID')) ?></span>
			</div>
			<?php if (count_user_tags($this->user('ID')) > 0): ?>
				<div class="info_title"><img height="16" width="16" src="<?php get_permalink('img/icons/tag_blue.png') ?>" alt="<?php t('Most used tags by the user') ?>" /> <?php t('Most used tags by the user') ?></div>
				<div class="info_user">
					<?php get_user_tags($this->user('ID'), '', '', '<br />', ''); ?>
					<span class="bold"><a href="<?php anchor('tag') ?>"><?php t('more') ?> &raquo;</a></span>
				</div>
			<?php endif; ?>
			<?php if (count_following($this->user('ID'), true) > 0): ?>
			<div class="info_title"><img src="<?php get_permalink('img/icons/refresh.png') ?>" alt="<?php t('Following') ?>" /> <?php t('Following') ?></div>
			<div class="info_user">
				<?php foreach(get_following($this->user('ID'), 25) as $following): ?>
					<a href="<?php anchor($following['username']) ?>"><img width="24" height="24" src="<?php get_avatar($following['ID'], 24) ?>" alt="<?php t('Avatar') ?>" /></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			
		<?php endif; ?>
		<?php elseif (get_type_sidebar() == 'my_profile'): ?>
				<div style="float:left; width:100%;text-align:left;">
                    <div class="profilePhoto" style="float:left; margin:-15px 10px 0 0;"><img height="48" width="48" src="<?php get_avatar($this->user('ID'),48) ?>" alt="<?php t('Avatar') ?>" /></div>
				<div class="profileName" style="font-size:14px;margin-top:-6px;color:black;"><a href="<?php anchor($this->user('username')) ?>" style="color:black;font-weight:bold;" class="user"><?php show($this->user('username')) ?></a></div>
                    <div class="profileName" style="float:left;font-size:12px;margin:2px 0 0 0;"><a href="<?php anchor('settings') ?>"><?php t('Settings') ?></a></div>
    </div>
			</div>
<div class="clearIt" style="clear:both;"></div>

			<div class="info_user" style="margin-top:20px;">
				<div class="user_menu">
                <span class="bold"><a href="<?php anchor('following') ?>"><?php t('Following') ?></a></span> <?php count_following($this->user('ID')) ?></div>
				<?php if (count_unread_followers() > 0): ?>
                <div class="user_menu">
				<span class="bold" style="background-color: red"><a style="color:white" href="<?php anchor('my_followers') ?>"><?php t('Followers') ?></a></span> <span id="sfollowers" style="color:red"><?php count_followers($this->user('ID')) ?></span></div>
				<?php else: ?>
                <div class="user_menu">
				<span class="bold"><a href="<?php anchor('my_followers') ?>"><?php t('Followers') ?></a></span> <span id="sfollowers"><?php count_followers($this->user('ID')) ?></span></div>
				<?php endif; ?>
                <div class="user_menu">
				<span class="bold"><a href="<?php anchor('notes', 'favorites') ?>"><?php t('Favorites') ?></a></span> <span id="sfavorites"><?php count_favorites($this->user('ID')) ?></span></div>
                <div class="user_menu">
				<span class="bold"><a href="<?php anchor('notes', 'private') ?>"><?php t('Private messages') ?></a></span> <span id="ajax_privates"><?php count_privates($this->user('ID')) ?></span></div>
                <div class="user_menu">    
				<span class="bold"><a href="<?php anchor('notes', 'archive') ?>"><?php t('Notes') ?></a></span> <span id="ajax_notes"><?php count_notes($this->user('ID')) ?></span></div>
                
                
                 <div class="user_menu">    
				<span class="bold"><a href="<?php anchor('public') ?>"><?php t('Public notes') ?></a></span></div>
                
                <div class="user_menu">    
				<span class="bold"><a href="<?php anchor('settings') ?>"><?php t('Settings') ?></a></span></div>
                
                
                
			</div>
			<?php if (count_following($this->user('ID'), true) > 0): ?>
<div class="user_menu">
			<div class="info_title"><img src="<?php get_permalink('img/icons/refresh.png') ?>" alt="<?php t('Following') ?>" /> <?php t('Following') ?></div></div>
			<div class="info_user">
				<?php foreach(get_following($this->user('ID'), 25) as $following): ?>
					<a href="<?php anchor($following['username']) ?>"><img height="24" width="24" src="<?php get_avatar($following['ID'], 24) ?>" alt="<?php t('Avatar') ?>" /></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if (use_invitations() && $this->user('invitations')): ?>
				<div class="info_title"><img src="<?php get_permalink('img/icons/invite.png') ?>"> <?php t('Invitations') ?></div>
				<div class="info_user">
					<form method="post" action="<?php anchor('invite') ?>">
						<p><input name="email" class="input_invite" value="<?php t('E-mail...') ?>" onblur="if(this.value=='') this.value='<?php t('E-mail...') ?>';" onfocus="if(this.value=='<?php t('E-mail...') ?>') this.value='';" type="text" /></p>
						<p><input type="submit" class="submit" value="<?php t('Send!') ?>"> <?php show($this->user('invitations')) ?> <?php t('invitations left') ?></p>
					</form>
				</div>
			<?php endif; ?>
			<?php if (count_trending_tags() > 0): ?>
				<div class="info_title"><img height="16" width="16" src="<?php get_permalink('img/icons/tag_blue.png') ?>" alt="<?php t('Most used tags') ?>" /> <?php t('Today most used tags') ?></div>
				<div class="info_user">
					<?php get_trending_tags('', '', '<br />', ''); ?>
					<span class="bold"><a href="<?php anchor('tag') ?>"><?php t('more') ?> &raquo;</a></span>
				</div>
			<?php endif; ?>
			
		<?php elseif (get_type_sidebar() == 'tags'): ?>
			<img height="16" width="16" src="<?php get_permalink('img/icons/tag_blue.png') ?>" class="tag" /> <a href="<?php anchor('tag/', $this->tag('name')) ?>" class="user">#<?php show($this->tag('name')) ?></a><br />
		</div>
		<div class="info_title"><img src="<?php get_permalink('img/icons/information.png') ?>" height="14" width="14" alt="<?php t('About') ?>" /> <?php t('About') ?></div>
		<div class="info_user">
			<span class="bold"><?php t('First note') ?></span> <?php _date_format($this->tag('since')) ?><br />
			<span class="bold"><?php t('First poster') ?></span> <a href="<?php anchor($this->tag('founder')) ?>"><?php show($this->tag('founder')) ?></a><br />
		</div>
		<div class="info_title"><img height="14" width="14" src="<?php get_permalink('img/icons/chart_bar.png') ?>" alt="<?php t('Stats') ?>" /> <?php t('Stats') ?></div>
		<div class="info_user">
			<span class="bold"><?php t('Notes') ?></span> <span id="ajax_notes"><?php show($this->tag('notes_count')) ?></span><br />
			<span class="bold"><?php t('Writing rate') ?></span> <strong><?php echo calc_tagWritingRate($this->tag('notes_count'), $this->tag('since')) ?></strong>/minute<br />
			<span class="bold"><?php t('Max. poster') ?></span> <a href="<?php anchor($this->tag('max_poster_username')) ?>"><?php show($this->tag('max_poster_username')) ?></a> <?php t('(%d notes)', $this->tag('max_poster_quantity')) ?>
		</div>
		<div class="info_title"><?php t('Export') ?></div>
		<div class="info_feeds">
			<ul class="feed">
				<li><a href="<?php anchor('api', 'statuses', 'public_timeline.rss') ?>"><img src="<?php get_permalink('img/icons/feed.png') ?>" height="16" width="16" alt="<?php t('Feed') ?>" /> <?php t('Public notes') ?></a></li>
				<li><a href="<?php anchor('rss', 'tag', $this->tag('name')) ?>"><img src="<?php get_permalink('img/icons/feed.png') ?>" height="16" width="16" alt="<?php t('Feed') ?>" /> <?php t("Tag #%s", $this->tag('name')) ?></a></li>
			</ul>
		</div>
	<?php endif; ?>
	<?php else: ?>
		<?php if (get_type_sidebar() == 'profile'): ?>
		<div class="user_side">
			<img height="150" width="150" src="<?php get_avatar($this->user('ID')) ?>" alt="<?php t('Avatar') ?>" /><br />
				<a href="<?php anchor($this->user('username')) ?>" class="user"><?php show($this->user('username')) ?></a><br />
			</div>
			<?php if ($this->viewable_profile == true): ?>
			<div class="info_title"><img src="<?php get_permalink('img/icons/information.png') ?>" height="14" width="14" alt="<?php t('About') ?>" /> <?php t('About') ?></div>
			<div class="info_user">
				<?php if ($this->user('realname')): ?>
				<span class="bold"><?php t('Name') ?></span> <?php show($this->user('realname')) ?><br />
				<?php endif; ?>
				<?php if ($location): ?>
				<span class="bold"><?php t('Location') ?></span> <?php show($this->user('location')) ?><br />
				<?php endif; ?>
				<?php if ($this->user('profile_url')): ?>
				<span class="bold"><?php t('Web') ?></span> <a href="<?php show($this->user('profile_url')) ?>" rel="external" class="external"><?php show($this->user('profile_url')) ?></a><br />
				<?php endif; ?>
				<?php if ($this->user('profile_bio')): ?>
				<span class="bold"><?php t('Bio') ?></span> <?php show($this->user('profile_bio')) ?><br />
				<?php endif; ?>
				<span class="bold"><?php t('Since') ?></span> <?php _date_format($this->user('since')) ?><br />
				<span class="bold"><?php t('Last login') ?></span>
				<?php if (get_last_seen($this->user('last_seen')) == 'online'): ?>
				<span style="color:green">online</span>
				<?php else: ?>
				<?php echo get_last_seen($this->user('last_seen')); ?>
				<?php endif; ?>
			</div>
			<div class="info_title"><img height="14" width="14" src="<?php get_permalink('img/icons/chart_bar.png') ?>" alt="<?php t('Stats') ?>" /> <?php t('Stats') ?></div>
			<div class="info_user">
				<span class="bold"><a href="<?php anchor($this->user('username'), 'following') ?>"><?php t('Following') ?></a></span> <?php count_following($this->user('ID')) ?><br />
				<span class="bold"><a href="<?php anchor($this->user('username'), 'followers') ?>"><?php t('Followers') ?></a></span> <span id="sfollowers"><?php count_followers($this->user('ID')) ?></span><br />
				<span class="bold"><a href="<?php anchor($this->user('username'), 'favorites') ?>"><?php t('Favorites') ?></a></span> <span id="sfavorites"><?php count_favorites($this->user('ID')) ?></span><br />
				<span class="bold"><?php t('Notes') ?></span> <span id="ajax_notes"><?php count_notes($this->user('ID')) ?></span>
			</div>
			<?php if (count_following($this->user('ID'), true) > 0): ?>
			<div class="info_title"><img src="<?php get_permalink('img/icons/refresh.png') ?>" alt="<?php t('Following') ?>" /> <?php t('Following') ?></div>
			<div class="info_user">
				<?php foreach(get_following($this->user('ID'), 25) as $following): ?>
					<a href="<?php anchor($following['username']) ?>"><img height="24" width="24" src="<?php get_avatar($following['ID'], 24) ?>" alt="<?php t('Avatar') ?>" /></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			
		<?php endif; ?>
		<?php elseif (get_type_sidebar() == 'tags'): ?>
			<img height="16" width="16" src="<?php get_permalink('img/icons/tag_blue.png') ?>" class="tag" /> <a href="<?php anchor('tag', $this->tag('name')) ?>" class="user">#<?php show($this->tag('name')) ?></a><br />
		</div>
		<div class="info_title"><img src="<?php get_permalink('img/icons/information.png') ?>" height="14" width="14" alt="<?php t('About') ?>" /> <?php t('About') ?></div>
		<div class="info_user">
			<span class="bold"><?php t('First note') ?></span> <?php _date_format($since) ?><br />
			<span class="bold"><?php t('First poster') ?></span> <a href="<?php anchor($founder) ?>"><?php show($founder) ?></a><br />
		</div>
		<div class="info_title"><img height="14" width="14" src="<?php get_permalink('img/icons/chart_bar.png') ?>" alt="<?php t('Stats') ?>" /> <?php t('Stats') ?></div>
		<div class="info_user">
			<span class="bold"><?php t('Notes') ?></span> <span id="ajax_notes"><?php count_tagsByName($name) ?></span><br />
			<span class="bold"><?php t('Writing rate') ?></span> <strong><?php echo calc_tagWritingRate($count, $since) ?></strong>/minute<br />
			<span class="bold"><?php t('Max. poster') ?></span> <a href="<?php anchor($max_poster_username) ?>"><?php show($max_poster_username) ?></a> <?php t('(%d notes)', $max_poster_quantity) ?>
		</div>
		
		<?php else: ?>
		<div class="login"> 
			<h1><img src="<?php get_permalink('img/login.png') ?>" /></h1>
			<div>
				<form method="post" action="<?php anchor('login') ?>">
					<p><input id="ttusername" name="username" type="text" class="input_login" value="<?php t('user or email') ?>" onblur="if(this.value=='') this.value='<?php t('user or email', true) ?>';" onfocus="if(this.value=='<?php t('user or email', true) ?>') this.value='';" /></p> 
					<p><input id="ttpassword" name="password" type="password" class="input_login" value="<?php t('Password') ?>" onblur="if(this.value=='') this.value='<?php t('Password', true) ?>';" onfocus="if(this.value=='<?php t('Password', true) ?>') this.value='';" /></p>
					<br />
					<p><input id="ttopenid" name="openid" type="text" class="input_login" value="<?php t('OpenID') ?>" onblur="if(this.value=='' || this.value=='http://') this.value='<?php t('OpenID') ?>';" onfocus="if(this.value=='<?php t('OpenID', true) ?>') this.value='http://';" /></p>
					<br/>
					<?php if (isFacebookEnabled()): ?>
					<div style="float:right;padding-top:5px;"><a href="<?php anchor('facebook') ?>" class="fbconnect_login_button fb_button fb_button_small"> <span id="RES_ID_fb_login_text" class="fb_button_text">Login</span></a></div>
					<?php endif; ?>
					<p><input type="submit" name="button" value="<?php t('Login') ?>" class="submit" id="btlogin" onclick="javascript:startLogin();" /></p>
				</form>
			</div>
			<div class="register_side"> 
				<div class="register_button"><a href="<?php anchor('register') ?>"><?php t('Register') ?></a></div> 
			</div> 
		<div> 
		<br /> 
		<p style="text-align:center"><strong><a href="<?php anchor('trouble_login') ?>"><?php t('Having trouble while logging in?') ?></a></strong></p> 
		</div> 
	</div>
	<div class="info_title"><?php t('RSS') ?></div>
	<div class="info_feeds">
		<ul class="feed">
			<li><a href="<?php anchor('api', 'statuses', 'public_timeline.rss') ?>"><img src="<?php get_permalink('img/icons/feed.png') ?>" alt="<?php t('Feed') ?>" height="16" width="16"/> <?php t('Public notes') ?></a></li>
		</ul>
	</div>
	<?php endif; ?>
	<?php endif; ?>
	
	<?php else: ?>
	<div class="user_side">
	</div>
	<?php endif; ?>
	
	</div>