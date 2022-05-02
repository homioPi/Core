<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if user has permission to manage extensions
    if(\HomioPi\Users\CurrentUser::getFlag('is_admin') !== true) {
        \HomioPi\Response\error('no_permission', 'You don\'t have permission to manage extensions settings');
    }
    
    // Check if extension id is given
	if(!isset($_POST['id'])) {
		\HomioPi\Response\error('request_field_missing', 'Field id is missing.');
	}
    $extension_id = basename($_POST['id']);

    // Check if extension action is given
	if(!isset($_POST['action'])) {
		\HomioPi\Response\error('request_field_missing', 'Field action is missing.');
	}
    $enable = $_POST['action'] == 'enable' ? true : false;

    $extension = new \HomioPi\Extensions\Extension($extension_id);

    // Return an error if extension is not installed
    if(is_null($extension->getProperties())) {
        \HomioPi\Response\error('extension_not_installed');
    }

    if($extension->getProperty('enabled') === $enable) {
        \HomioPi\Response\success($enable ? 'already_enabled' : 'already_disabled', $extension->getProperties());
    }

    if($extension->setProperty('enabled', $enable)) {
        \HomioPi\Response\success(null, $extension->getProperties());
    }
    
    \HomioPi\Response\error('failed_to_disable_extension', $extension->getProperties());
?>