<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    // Check if user has permission to manage extensions
    if(\HomioPi\Users\CurrentUser::getFlag('is_admin') !== true) {
        \HomioPi\Response\error('no_permission', 'You don\'t have permission to manage extensions settings');
    }

    $_POST['query'] = $_POST['query'] ?? null;
    $_POST['category'] = $_POST['category'] ?? null;

    \HomioPi\Response\success(null, \HomioPi\Extensions\get_all_from_server($_POST['category'], $_POST['query']));
?>