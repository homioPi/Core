<div class="tile transition-slide-top" id="login-tile">
	<form id="login-form" class="mb-0" method="POST">
		<div class="mb-3">
			<span class="tile-title"><?php echo(\HomioPi\Locale\translate('login.placeholder.username')); ?></span>
			<input name="username" type="text" placeholder="<?php echo(\HomioPi\Locale\translate('login.placeholder.username')); ?>"/>
		</div>
		<div class="mb-3">
			<span class="tile-title"><?php echo(\HomioPi\Locale\translate('login.placeholder.password')); ?></span>
			<div class="position-relative">
				<input name="password" type="password" placeholder="<?php echo(\HomioPi\Locale\translate('login.placeholder.password')); ?>"/>
				<div class="toggle-password" onclick="togglePassword($(this), $(this).siblings('input').first())">
					<?php echo(icon_html('mi.visibility', 'show-password text-info', 'display: none;')); ?>
					<?php echo(icon_html('mi.visibility_off', 'hide-password text-info')); ?>
				</div>
			</div>
		</div>
		<button class="btn btn-primary bg-tertiary" role="submit">
			<?php echo(\HomioPi\Locale\translate('generic.action.sign_in')); ?>
		</button>
	</form>
</div>