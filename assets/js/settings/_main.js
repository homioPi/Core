$(document).on('homioPi.change', '.setting input', function() {
	let $input      = $(this);
	let $wrapper    = $input.closest('.input-wrapper');
	let $setting    = $input.closest('[data-setting]');
	let key         = $setting.attr('data-setting');
	let key_shown   = $input.closest('.tile').find('.tile-title').text().trim();
	let value       = $input.value();
	let value_shown = $input.val();

	$wrapper.showLoading();

	homioPi.users.currentUser.setSetting(key, value).then(() => {
		$wrapper.hideLoading();
		
		if($setting.attr('data-needs-reload') == 'true') {
			window.location.reload();
		} else {
			homioPi.message.send(homioPi.locale.translate('settings.message.setting_changed', [key_shown, value_shown]));
		}
	})
})