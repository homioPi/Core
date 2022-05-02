<?php 
    chdir(__DIR__);
    include_once("../../autoload.php");
?>
<?php 
    set_time_limit(300);

    \HomioPi\Streams\trigger_all('cron', []);
?>