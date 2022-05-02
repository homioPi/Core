<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
	if(count($argv) <= 1) {
		\HomioPi\Response\error('Insufficient amount of arguments given.');
	}

	$id = $argv[1];

	$device  = new \HomioPi\Devices\Device($id);
	$options = $device->getProperty('options');

	if(!$units = @$options['units']) {
		$units = 'standard';
	}

	if($temperature_str = @trim(@shell_exec_timeout('sudo vcgencmd measure_temp', 2))) {
		$temperature = floatval(preg_replace('/[^0-9.]/', '', $temperature_str));
	}

	$unit = substr($temperature_str, -1);

	// Convert to Kelvin
	switch($unit) {
		case 'F':
			$temperature = ($temperature - 32) * (5/9) + 273.15;
			break;

		case 'C':
			$temperature += 273.15;
			break;
	}

	// Convert to desired unit (metric, imperial, standard)
	switch($units) {
		case 'metric':
			$temperature -= 273.15;
			break;

		case 'imperial':
			$temperature = ($temperature - 273.15) * (9/5) + 32;
			break;
	}

	$temperature = round($temperature, 3);

	# Return temperature
	\HomioPi\Response\success(null, ['temperature' => $temperature]);
?>