<div class="header_title"><?php t('Having trouble while logging in?') ?></div>

<p><?php t('If you cannot login into your account, choose an option from the forms below') ?></p><br /><br />

<div>
	<form name="resend" action="<?php anchor('trouble_login') ?>" method="post">
		<div style="float:right;width:250px">
			<div class="header_title"><?php t('Resend activation mail...') ?></div>
			<p><?php t('If you didnt receive your activation email, you can try resending it from here. Just typing your username') ?></p><br /><br />
			<p><?php t('Username') ?><br /><input class="input_reduced" name="resend" type="text" /></p>
		</div>
		<div style="width:250px">
			<div class="header_title"><?php t('Recover password...') ?></div>
			<p><?php t('You can recover your password just typing your email or your username. An email will be delivered to the linked account') ?></p>
			<br />
			<input type="radio" name="group" value="username" checked> <?php t('Username') ?>
			<br />
			<input type="radio" name="group" value="email"> <?php t('Email') ?><br /><br/>
			<input class="input_reduced" name="forgot" type="text" />			
		</div>
	<br /> <br /><br />
	
	<p>
		<?php if (recaptcha_enabled()) load_recaptcha(); echo '<br /><br />'; ?>
		
		<input name="btforgot" class="submit" type="submit" value="<?php t('Continue') ?>">
	</p>
	
	</form>
</div>