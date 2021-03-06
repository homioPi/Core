<?php 
	chdir(__DIR__);
	include_once('../../autoload.php');
?>
<?php 
    $minutes_since_epoch = floor(time()/60);

    $analytics = \HomioPi\Analytics\get_all();

    foreach ($analytics as $properties) {
        $analytic = new \HomioPi\Analytics\Analytic($properties['id']);

        // Record if crontab pattern matches current time
        if(parse_crontab($properties['crontab'])) {
            $analytic->record(true);
        }
    }
?>