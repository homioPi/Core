<?php
	chdir(__DIR__);
	include_once("../../autoload.php");
?>
<?php
    if(!isset($_GET['id'])) {
        \HomioPi\Response\error('Missing query parameter id.');
    }
    $id = $_GET['id'];

    if(!isset($_GET['name'])) {
        \HomioPi\Response\error('Missing query parameter name.');
    }
    $name = $_GET['name'];

    if(!isset($_GET['parameters'])) {
        \HomioPi\Response\error('Missing query parameter parameters.');
    }
    $parameters = $_GET['parameters'];


    $stream = new \HomioPi\Streams\Stream($id);
?>