<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
	
    if(!isset($_POST['username'])) {
		$_POST['username'] = '';
	}

	if(!isset($_POST['password'])) {
		$_POST['password'] = '';
	}

	if(!\HomioPi\Authorization\login_verify($_POST['username'], $_POST['password'])) {
		\HomioPi\response\error('error_credentials');
	}
	
	\HomioPi\response\success('success_login');
?>