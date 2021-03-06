$(document).on('submit', 'form#login-form', function(e) {
	e.preventDefault();

	let $form = $(this);
	$form.find('button').showLoading();

	let username = $form.find('[name="username"]').val();
	let password = $form.find('[name="password"]').val();

	homioPi.api.call('login-verify', {'username': username, 'password': password}).then((response) => {
		$form.find('button').hideLoading();
		homioPi.page.load(urlParam('redirect') || 'home/main', true);
	}).catch(() => {
		$form.find('button').hideLoading();
		homioPi.message.error(homioPi.locale.translate('login.invalid_credentials'));
	}) 
})