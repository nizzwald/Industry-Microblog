<div class="header_title"><?php t('Contact') ?></div>

<p>
	<?php t('If you want to contact with the administrator of %s, please fill the form below
			and the administrator will receive a copy of your message', $this->name) ?>
</p>
<br />
<br />
<form action="<?php anchor('contact') ?>" method="post">
	<?php if (is_logged()): ?>
	<p><?php t('Username') ?><br /> <input name="username" type="text" class="input" value="<?php logged_username() ?>" readonly/> </p>
	<br />
	<p><?php t('Email') ?><br /> <input name="email" type="text" class="input" value="<?php logged_email() ?>" readonly/> </p>
	<br />
	<?php else: ?>
	<p><?php t('Email') ?><br /> <input name="email" type="text" class="input" value=""/> </p>
	<?php endif; ?>
	<p><?php t('Message') ?><br />
	<textarea name="message" type="text" class="input_textarea" rows="8"></textarea>
	<br /><br />
	<input type="checkbox" name="copy" /> <?php t('I want to receive a copy in my email account') ?>
	<br />
	<?php if (recaptcha_enabled()) echo '<br />'; load_recaptcha(); ?>
	<br />
	<p><input name="submit" type="submit" value="<?php t('Send') ?>" class="submit" /></p>
</form>

