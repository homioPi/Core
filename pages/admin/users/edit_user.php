<?php 
	$uid = sha1(openssl_random_pseudo_bytes(16));
	$new_user = true;
	$username = '';

	if(isset($_GET['user_uid'])) { $uid = $_GET['user_uid']; $new_user = false; }
	if(isset($userdata_all[$uid]['username'])) { $username = $name = $userdata_all[$uid]['username']; }
	if(isset($userdata_all[$uid]['name'])) { $name = $userdata_all[$uid]['name']; }
?>
<form method="POST" action="handlers/edit_user.php" class="tile transition-slide-top">
	<div class="row">
		<div class="col-12 col-md-6 mb-2">
			<span class="tile-title" data-label="title tile_title"><?php\HomioPi\Locale\translate('admin.users.edit_user.name.title', true); ?></span>
			<input name="name" placeholder="<?php\HomioPi\Locale\translate('admin.users.edit_user.name.title', true); ?>" value="<?php echo(get_user_info('name', $_GET['user_uid'])); ?>" type="text" />
		</div>
		<div class="col-12 col-md-6 mb-2">
			<span class="tile-title text-muted" data-label="title tile_title"><?php\HomioPi\Locale\translate('admin.users.edit_user.flags.title', true); ?></span>
			<a class="btn btn-sm bg-tertiary" data-label="button" onclick="loadPage('admin', 'users>edit_flags', {'user_uid': '<?php echo($_GET['user_uid']); ?>'});"><?php\HomioPi\Locale\translate('action.edit', true); ?></a>
		</div>
		<div class="col-12 col-md-6 mb-2">
			<span class="tile-title" data-label="title tile_title"><?php\HomioPi\Locale\translate('admin.users.edit_user.username.title', true); ?></span>
			<input name="username" placeholder="<?php\HomioPi\Locale\translate('admin.users.edit_user.username.title', true); ?>" value="<?php echo(get_user_info('username', $_GET['user_uid'])); ?>" type="text" />
		</div>
		<div class="col-12 col-md-6 mb-2">
			<span class="tile-title" data-label="title tile_title"><?php\HomioPi\Locale\translate('admin.users.edit_user.password.title', true); ?></span>
			<div class="btn btn-sm bg-tertiary" data-label="button"><?php\HomioPi\Locale\translate('action.reset', true); ?></div>
		</div>
		<div class="col-12 col-md-6 mb-2" data-label="title tile_title">
			<span class="tile-title"><?php\HomioPi\Locale\translate('admin.users.edit_user.sign_out.title', true); ?></span>
			<div class="btn btn-sm bg-tertiary" data-label="button"><?php\HomioPi\Locale\translate('action.sign_out', true); ?></div>
		</div>
		<div class="col-12 col-md-6 mb-2" data-label="title tile_title">
			<span class="tile-title"><?php\HomioPi\Locale\translate('admin.users.edit_user.delete.title', true); ?></span>
			<div 
				class="btn btn-sm bg-tertiary btn-primary" 
				onclick="openDialog(undefined);">
					<?php\HomioPi\Locale\translate('action.delete', true); ?>
				</div>
		</div>
	</div>
</form>