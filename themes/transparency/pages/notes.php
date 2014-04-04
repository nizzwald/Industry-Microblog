<?php if (is_logged()):?>
	<?php doNoteForm($this->textNote, $this->in_reply_to); ?>
	
	<?php if (has_twitter()): ?>
	<ul class="tags" style="width:448px">
		<?php if (is_archive()): ?>
		<li id="archive_tab" class="left_item current_item"><a href="<?php anchor('notes', 'archive') ?>"><?php t('Archive') ?></a></li>
		<?php else: ?>
		<li id="archive_tab" class="left_item"><a href="<?php anchor('notes', 'archive') ?>"><?php t('Archive') ?></a></li>
		<?php endif; ?>
		<?php if (is_replies()): ?>
		<li id="replies_tab" class="current_item"><a href="<?php anchor('notes', 'replies') ?>">@<?php show($this->user('username')) ?></a></li>
		<?php else: ?>
			<?php if (unread_replies()): ?>
			<li id="replies_tab" class="unread"><a href="<?php anchor('notes', 'replies') ?>">@<?php show($this->user('username')) ?></a></li>
			<?php else: ?>
			<li id="replies_tab" class=""><a href="<?php anchor('notes', 'replies') ?>">@<?php show($this->user('username')) ?></a></li>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (is_home()): ?>
		<li id="friends_tab" class="current_item"><a href="<?php anchor('notes', 'friends') ?>"><?php t('Friends') ?></a></li>
		<?php else: ?>
		<li id="friends_tab" class=""><a href="<?php anchor('notes', 'friends') ?>"><?php t('Friends') ?></a></li>
		<?php endif; ?>
		<?php if (is_privates()): ?>
		<li id="private_tab" class="current_item"><a href="<?php anchor('notes', 'private') ?>"><?php t('Private messages') ?></a></li>
		<?php else: ?>
			<?php if (unread_privates()): ?>
			<li id="private_tab" class="unread"><a href="<?php anchor('notes', 'private') ?>"><?php t('Private messages') ?></a></li>
			<?php else: ?>
			<li id="private_tab" class=""><a href="<?php anchor('notes', 'private') ?>"><?php t('Private messages') ?></a></li>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (is_twitter()): ?>
		<li id="twitter_tab" class="right_item current_item"><a href="<?php anchor('notes', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<?php else: ?>
		<li id="twitter_tab" class="right_item"><a href="<?php anchor('notes', 'twitter') ?>"><?php t('Twitter') ?></a></li>
		<?php endif; ?>
	</ul>
	<?php else: ?>
	<ul class="tags" style="width:376px">
		<?php if (is_archive()): ?>
		<li id="archive_tab" class="left_item current_item"><a href="<?php anchor('notes', 'archive') ?>"><?php t('Archive') ?></a></li>
		<?php else: ?>
		<li id="archive_tab" class="left_item"><a href="<?php anchor('notes', 'archive') ?>"><?php t('Archive') ?></a></li>
		<?php endif; ?>
		<?php if (is_replies()): ?>
		<li id="replies_tab" class="current_item"><a href="<?php anchor('notes', 'replies') ?>">@<?php show($this->user('username')) ?></a></li>
		<?php else: ?>
			<?php if (unread_replies()): ?>
			<li id="replies_tab" class="unread"><a href="<?php anchor('notes', 'replies') ?>">@<?php show($this->user('username')) ?></a></li>
			<?php else: ?>
			<li id="replies_tab" class=""><a href="<?php anchor('notes', 'replies') ?>">@<?php show($this->user('username')) ?></a></li>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (is_home()): ?>
		<li id="friends_tab" class="current_item"><a href="<?php anchor('notes', 'friends') ?>"><?php t('Friends') ?></a></li>
		<?php else: ?>
		<li id="friends_tab" class=""><a href="<?php anchor('notes', 'friends') ?>"><?php t('Friends') ?></a></li>
		<?php endif; ?>
		<?php if (is_privates()): ?>
		<li id="private_tab" class="right_item current_item"><a href="<?php anchor('notes', 'private') ?>"><?php t('Private messages') ?></a></li>
		<?php else: ?>
			<?php if (unread_privates()): ?>
			<li id="private_tab" class="unread right_item"><a href="<?php anchor('notes', 'private') ?>"><?php t('Private messages') ?></a></li>
			<?php else: ?>
			<li id="private_tab" class="right_item"><a href="<?php anchor('notes', 'private') ?>"><?php t('Private messages') ?></a></li>
			<?php endif; ?>
		<?php endif; ?>
	</ul>
	<?php endif; ?>
<?php else: ?>
	<div class="header_title"><?php t('Public notes') ?></div>
<?php endif; ?>
<div id="js_options" style="display:none">
	<div class="ajax_section"><?php show($this->params_page) ?></div>
	<div class="ajax_page"><?php show($this->current_page) ?></div>
</div>
<div class="separator"></div>


<?php if ($this->params_page == 'twitter' or $this->params_page == 'twitter_replies'): ?>
<br />
<div class="tabs_t"><a href="<?php anchor('notes', 'twitter') ?>"><?php t('Following') ?></a> | <a href="<?php anchor('notes', 'twitter_replies') ?>"><?php t('Replies') ?></a></div>
<br />
<?php endif; ?>

<?php if ($this->params_page == 'private' or $this->params_page == 'private_sent'): ?>
<br />
<div class="tabs_t"><a href="<?php anchor('notes', 'private') ?>"><?php t('Received') ?></a> | <a href="<?php anchor('notes', 'private_sent') ?>"><?php t('Sent') ?></a></div>
<br />
<?php endif; ?>

<div id="note_list">
	<?php if ($this->notes_count == 0): ?>
	<div class="warning"><?php t('No notes were found') ?></div>
	<?php else: ?>
		<?php foreach ($this->notes_result as $note) showNote($note); ?>
	<?php endif; ?>
</div>

<?php if ($this->notes_count > $this->notes_per_page): ?>
<?php getPaginationString(array('notes', $this->params_page), $this->notes_count, $this->current_page); ?>
<?php endif; ?>