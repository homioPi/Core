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

	if(!$filter = @$options['filter']) {
		$filter = 'temperature';
	}

	if(!$units = @$options['units']) {
		$units = 'standard';
	}
	
	if(!$gpio_pin = @$options['gpio_pin']) {
		\HomioPi\Response\error('option_gpio_pin_missing', 'Option gpio_pin was not specified in device options.');
	}

	$cmd = "/usr/bin/python3 inner.py {$gpio_pin}";

	if(!$dht22_json = @shell_exec_timeout(escapeshellcmd($cmd), 10)) {
		exit('Failed to run script.');
	}

	if(($dht22_output = @json_decode($dht22_json, true)) === false) {
		exit(json_output('Failed to decode data returned from script.'));
	}
			
	if($dht22_output['success'] != true || !isset($dht22_output['humidity']) || !isset($dht22_output['temperature'])) {
		exit(json_output('Failed to decode data returned from script.'));
	}

	switch($units) {
		case 'metric':
			$dht22_output['temperature'] -= 273.15;
			break;

		case 'imperial':
			$dht22_output['temperature'] = ($dht22_output['temperature'] - 273.15) * 9/5 + 32;
			break;
	}

	$dht22_output['temperature'] = round($dht22_output['temperature'], 3);
	$dht22_output['humidity'] = round($dht22_output['humidity'], 3);

	# Return DHT22 output
	exit(json_output($dht22_output, 'success'));
?>