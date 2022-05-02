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

    $extension = new \HomioPi\Extensions\Extension($extension_id);

    // Store properties in a variable because they won't be accessible
    // anymore when the extension is uninstalled
    $properties = $extension->getProperties();

    // Return an error if extension is not installed
    if(!isset($properties)) {
        \HomioPi\Response\error('extension_not_installed', $properties);
    }

    if($extension->uninstall()) {
        \HomioPi\Response\success('uninstalled', $properties);
    }

    \HomioPi\Response\error('error_uninstalling', $properties);
?>