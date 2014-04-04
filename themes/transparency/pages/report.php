<?php if ($this->page_module == 'before_user'): ?>
	<?php if ($this->error): ?>
	<?php echo showStatus(__($this->error), 'error');?>
	<?php else: ?>
	<div class="header_title"><?php t('Report the user %s', $this->reporting_username) ?></div>
    <?php t('Some reasons to report an account can be:'); ?>
	<br>
	<ul style="color:red">
		<li><?php t('SPAM') ?></li>
		<li><?php t('Offensive content') ?></li>
		<li><?php t('Duplicated account') ?></li>
		<li><?php t('Unlawful attached files') ?></li>
		<li><?php t('Illicit purposes') ?></li>
	</ul>
	<?php t('You must be completely SURE that the user you are reporting is not following our Terms of Service.') ?>
	<?php t('And remember:') ?>
	<br>
	<ul style="color:red">
		<li><?php t('You will remain in anonymity') ?></li>
		<li><?php t('When finishing this, you will not get any notification, but report has been sent') ?></li>
		<li><?php t('Fake or duplicated reports means the blocking of your account!') ?></li>
	</ul>
	<br />
	<?php t('If after reading this, you still want to report this account, go forward, we are grateful for your help!') ?>
	<br>
	<form action="<?php anchor('report', 'user', EXTRA) ?>" method="post">
	<?php if (recaptcha_enabled()) echo '<br />'; load_recaptcha(); ?>
	<br>
    <div id="bot">
    		<input type="hidden" name="auth" value="<?php show(md5($this->user('salt'))) ?>">
			<input type="submit" value="<?php t('Continue') ?>" class="submit">
		</form>
	</div>
	<?php endif; ?>
<?php elseif ($this->page_module == 'after_user'): ?>
<?php if ($this->error): ?>
<?php echo showStatus(__($this->error), 'error'); ?>
<?php else: ?>
<?php echo showStatus(__('The user was reported successfully'), 'ok') ?>
<?php endif; ?>
<?php elseif ($this->page_module == 'before_note'): ?>
<?php if ($this->error): ?>
	<?php echo showStatus(__($this->error), 'error');?>
	<?php else: ?>
	<div class="header_title"><?php t('Report the note #%s', $this->reporting_noteid) ?></div>
    <?php t('Some reasons to report a note can be:'); ?>
	<br>
	<ul style="color:red">
		<li><?php t('SPAM') ?></li>
		<li><?php t('Offensive content') ?></li>
		<li><?php t('Duplicated account') ?></li>
		<li><?php t('Unlawful attached files') ?></li>
		<li><?php t('Illicit purposes') ?></li>
	</ul>
	<?php t('You must be completely SURE that the note you are reporting is not following our Terms of Service.') ?>
	<?php t('And remember:') ?>
	<br>
	<ul style="color:red">
		<li><?php t('You will remain in anonymity') ?></li>
		<li><?php t('When finishing this, you will not get any notification, but report has been sent') ?></li>
		<li><?php t('Fake or duplicated reports means the blocking of your account!') ?></li>
	</ul>
	<br />
	<?php t('If after reading this, you still want to report this account, go forward, we are grateful for your help!') ?>
	<br>
	<form action="<?php anchor('report', 'note', EXTRA) ?>" method="post">
	<?php if (recaptcha_enabled()) load_recaptcha(); ?>
	<br>
    <div id="bot">
    		<input type="hidden" name="auth" value="<?php show(md5($this->user('salt'))) ?>">
			<input type="submit" value="<?php t('Continue') ?>" class="submit">
		</form>
	</div>
	<?php endif; ?>
<?php elseif ($this->page_module == 'after_note'): ?>
<?php if ($this->error): ?>
<?php echo showStatus(__($this->error), 'error'); ?>
<?php else: ?>
<?php echo showStatus(__('The note was reported successfully'), 'ok') ?>
<?php endif; ?>
<?php endif; ?>