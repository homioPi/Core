$(document).on('submit', 'form#login-form', function(e) {
	e.preventDefault();

	let $form = $(this);
	$form.find('button').showLoading();

	let username = $form.find('[name="username"]').val();
	let password = $form.find('[name="password"]').val();

	HomioPi.api.call('login-verify', {'username': username, 'password': password}).then((response) => {
		$form.find('button').hideLoading();
		HomioPi.page.load(urlParam('redirect') || 'home/main', true);
	}).catch(() => {
		$form.find('button').hideLoading();
		HomioPi.message.error(HomioPi.locale.translate('login.invalid_credentials'));
	}) 
})