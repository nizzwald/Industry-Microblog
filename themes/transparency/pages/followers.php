<div class="header_title"><?php t("My followers") ?></div>

<div id="note_list">
	<?php if ($this->users_count == 0): ?>
	<div class="warning"><?php t('No users were found') ?></div>
	<?php else: ?>
		<?php foreach ($this->users_result as $user) showUser($user['ID']); ?>
	<?php endif; ?>
</div>

<?php echo getPaginationString(array('my_followers'), $this->users_count, $this->current_page) ?>