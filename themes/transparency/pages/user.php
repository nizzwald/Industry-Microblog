<?php if ($this->module_page == 'permalink'): ?>
	<?php if (!$this->is_viewable): ?>
		<div class="header_title"><?php t('Forbidden access') ?></div>
		<p>
			<?php t('This user has decided to not show his profile to everyone.') ?>
		</p>
	<?php else: ?>
		<?php if ($this->note_id): ?>
			<div class="header_title"><?php t('Link to the post %s', '#'.$this->note_id) ?></div>
			<?php t('To share this note you simply have to share the URL of this page to your friends via social networking, instant messaging, blogs...') ?><br /><br />
			<?php showNote(array('id'=>$this->note_id)) ?>
			<br />
			<br />
			<?php if (is_logged()): ?>
			<div style="float:right;margin-right:15px">
				<a href="<?php anchor('report', 'note', $this->note_id) ?>"><img src="<?php get_permalink('img/icons/warning.png') ?>" alt="<?php t('Report note') ?>"> Report note</a>
			</div>
			<?php endif; ?>

			<div class="addthis_toolbox addthis_default_style">
			<a href="http://addthis.com/bookmark.php?v=250&amp;username=xa-4bc8f1f570731323" class="addthis_button_compact">Share</a>
			<span class="addthis_separator">|</span>
			<a class="addthis_button_facebook"></a>
			<a class="addthis_button_myspace"></a>
			<a class="addthis_button_google"></a>
			<a class="addthis_button_twitter"></a>
			<a class="addthis_button_googlebuzz"></a>
			<a class="addthis_button_email"></a>
			</div>
			<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=xa-4bc8f1f570731323"></script>
		
			<br />
			<?php if ($this->count_replies): ?>
				<br/><div class="header_title"><?php echo txtReplies($this->count_replies) ?> </div>
				<?php foreach ($this->result_replies as $reply) showNote(array('id'=>$reply)) ?>
			<?php endif; ?>
		<?php else: ?>
			<?php echo showStatus(__('No notes were found'), 'warning') ?>
		<?php endif; ?>
	<?php endif; ?>
<?php elseif ($this->module_page == 'followers'): ?>
	<?php if (!$this->is_viewable): ?>
	<div class="header_title"><?php t('Forbidden access') ?></div>
	<p>
		<?php t('This user has decided to not show his followers to everyone.') ?>
	</p>
	<?php else: ?>
	<div class="header_title"><?php t("%s's followers (%d)", $this->user('username'), $this->users_count) ?></div>
	<?php if ($this->users_count == 0): ?>
		<?php echo showStatus(__('No users were found'), 'warning') ?>
	<?php else: ?>
		<div id="note_list">
		<?php foreach ($this->users_result as $followers) showUser($followers['ID']); ?>
		</div>

		<?php getPaginationString(array('followers'), $this->users_count, $this->current_page); ?>
	<?php endif; ?>
	<?php endif; ?>
<?php elseif ($this->module_page == 'following'): ?>
	<?php if (!$this->is_viewable): ?>
	<div class="header_title"><?php t('Forbidden access') ?></div>
	<p>
		<?php t('This user has decided to not show his followings to everyone.') ?>
	</p>
	<?php else: ?>
	<div class="header_title"><?php t("%s's followings (%d)", $this->user('username'), $this->users_count) ?></div>
	<?php if ($this->users_count == 0): ?>
		<?php echo showStatus(__('No users were found'), 'warning') ?>
	<?php else: ?>
		<div id="note_list">
		<?php foreach ($this->users_result as $followings) showUser($followings['ID']); ?>
		</div>

		<?php getPaginationString(array('following'), $this->users_count, $this->current_page); ?>
	<?php endif; ?>
	<?php endif; ?>
<?php else: ?>
	<div id="ajax_status" style="display:none;">
		<div class="ajax_section"><?php show($this->module_page) ?></div>
		<div class="ajax_page"><?php show($this->current_page) ?></div>
	</div>
	<?php if (is_logged()): ?>
	<?php doNoteForm($_GET['note'], $_GET['in_reply_to']) ?>
	<?php endif; ?>
	<?php if (!$this->is_viewable): ?>
		<div class="header_title"><?php t('Forbidden access') ?></div>
		<p>
			<?php t('This user has decided to not show his profile to everyone.') ?>
		</p>
	<?php else: ?>
	<br />
	<?php if ($this->notes_count == 0): ?>
		<?php echo showStatus(__('No notes were found'), 'warning'); ?>
	<?php else: ?>
		<div id="note_list">
			<?php foreach ($this->notes_result as $note) showNote(array('id'=>$note['id'])) ?>
		</div>
		<?php getPaginationString(array($this->user('username'), PARAMS), $this->notes_count, $this->current_page) ?>
	<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>