<h1 class="header_title"><?php t('Results for %s', "<i>".$this->search_string."</i>") ?></h1>

<?php if ($this->count_search == 0): ?>
<?php echo showStatus(__('Nothing found'), 'warning'); ?>
<?php else: ?>
	<?php foreach($this->search_result as $user) showUser($user); ?>
<?php endif; ?>

<?php if ($this->count_search > $this->notes_per_page): ?>
<?php getPaginationString(array("search"), $this->count_search, $this->current_page, array('query='.urlencode($this->search_string))); ?>
<?php endif; ?>