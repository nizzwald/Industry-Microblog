<?php extract($_GET); global $allowed ?>

<?php if (!$allowed): ?>
<?php echo showStatus(__('You need a valid token to register an account'), 'error') ?>
<?php else: ?>

<div class="header_title"><?php t('Register') ?></div>

<?php if (!empty($openid)): ?>
<?php t("We couldn't find your OpenID url on our database, so maybe you want to register. To do so we need some details which you can fill in the form below") ?>
<br /><br /><br />
<?php elseif (!empty($fbid)): ?>
<?php t("We couldn't find your Facebook profile on our database, so maybe you want to register. To do so we need some details which you can fill in the form below") ?>
<br /><br /><br />
<?php endif; ?>
<form name="register" action="<?php anchor('register') ?>" method="post">
	<p><input name="token" type="hidden" value="<?php echo $token ?>" /></p>
	<p><?php t('Username') ?><br /><input name="username" type="text" class="input" value="<?php show($nickname) ?>" tabindex="1"/> </p>
	<p style="height:6px"></p>
	<p><?php t('E-mail') ?><br /><input name="email" type="text" class="input" value="<?php show($email) ?>"  tabindex="2"/> </p>
	<p style="height:6px"></p>
	<?php if (empty($openid) && (empty($fbid))): ?>
	<div>
		<div style="float:right"><p style="margin-right:4px"><?php t('password (again)') ?><br /><input name="password2" type="password" class="input" value=""  tabindex="4"/> </p></div>
		<p><?php t('Password') ?><br /><input name="password" type="password" class="input" value=""  tabindex="3"/></p>
	</div>
	<?php endif; ?>
	<?php if (!empty($openid)): ?>
	<p style="height:6px"></p>
	<p><?php t('OpenID <strong>(optional)</strong>') ?>
	<br />
	<input name="openid" type="text" class="input" value="<?php show($openid) ?>" tabindex="5"/> </p>
	<?php endif; ?>
	<?php if (!empty($fbid)): ?>
	<input type="hidden" name="fbid" value="<?php show($fbid) ?>">
	<?php endif; ?>
	<p style="height:6px"></p>
	<p><?php t('Language') ?><br />
	
	<select name="language" id="lang" class="listbox" tabindex="5">
	<?php foreach (return_languages() as $short => $name): ?>
		<?php if (LANG == $short): ?>
		<option value="<?php echo $short ?>" selected ><?php echo $name ?></option>
		<?php else: ?>
		<option value="<?php echo $short ?>"><?php echo $name ?></option>
		<?php endif; ?>
	<?php endforeach; ?>
	</select> </p>
	
	<?php if (recaptcha_enabled()) echo '<br>'; load_recaptcha(); ?>
	<br />
	<?php if (tos_enabled()): ?>
	<p><input type="checkbox" name="legal" onclick="document.register.btregister.disabled=!document.register.btregister.disabled" /><?php t('I accept the') ?> <a href="<?php anchor('tos') ?>"><?php t('privacy and service terms') ?></a></p>
	<br />
	<p><input type="submit" name="btregister" value="<?php t('Register!') ?>" class="submit" disabled /></p>
	<?php else: ?>
	<p><input type="submit" name="btregister" value="<?php t('Register!') ?>" class="submit" /></p>
	<?php endif; ?>
</form>
<div class="separator"></div>

<?php endif; ?>