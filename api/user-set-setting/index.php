<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php
    // Check if uuid is given
	if(!isset($_POST['uuid'])) {
		$_POST['uuid'] = \HomioPi\Users\CurrentUser::getProperty('uuid');
	}

    // Check if key is given
	if(!isset($_POST['key'])) {
		\HomioPi\Response\error('request_field_missing', 'Field key is missing.');
	}
    
    // Check if value is given
	if(!isset($_POST['value'])) {
		$_POST['value'] = [];
	}

    // Convert value to boolean if it is one
    if($_POST['value'] == 'true') {
        $_POST['value'] = true; 
    } else if($_POST['value'] == 'false') {
        $_POST['value'] = false; 
    }

    $uuid = \HomioPi\Users\CurrentUser::getProperty('uuid');

    // If uuid is not users uuid, check if user has permission to change settings
    if(\HomioPi\Users\CurrentUser::getFlag('is_admin') !== true) {
        \HomioPi\Response\error('no_permission', 'You don\'t have permission to change settings for this user.');
    }

    $user = @new \HomioPi\Users\User($_POST['uuid']);
    if(!$user->setSetting($_POST['key'], $_POST['value'])) {
        \HomioPi\Response\error();
    }

    \HomioPi\Response\success();
?>