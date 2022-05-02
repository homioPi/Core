<?php 
    chdir(__DIR__);
	include_once("../../autoload.php");
?>
<?php 
    $card = @shell_arg_decode($argv[1]);

    if(@count($card) < 3) {
        \HomioPi\Response\error('Insufficient amount of arguments given.');
    }

    $card_handler_path = DIR_ASSETS."/streams/card_handlers/{$card['collection']}/{$card['namespace']}.php";
    if(!file_exists($card_handler_path)) {
        \HomioPi\Response\error('card_handler_not_found');
    }

    // Get card handler output
    ob_start();
    if(is_readable($card_handler_path)) {
        include($card_handler_path);
    } else {
        \HomioPi\Response\error('card_handler_not_readable');
    }
    $card_output = ob_get_clean();

    if(str_to_bool($card_output) === true) {
        \HomioPi\Response\success('card_succeeded', $card_output);
    } else {
        \HomioPi\Response\error('card_not_succeeded', $card_output);
    }

    exit();
?>