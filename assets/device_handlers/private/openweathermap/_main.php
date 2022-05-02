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

	if(!$api_key = @$options['api_key']) {
		\HomioPi\Response\error('option_api_key_missing', 'Option api_key was not specified in device options.');
	}

	if(!$lat = @floatval($options['latitude'])) {
		\HomioPi\Response\error('option_latitude_missing', 'Option latitude was not specified in device options.');
	}

	if(!$long = @floatval($options['longitude'])) {
		\HomioPi\Response\error('option_longitude_missing', 'Option longitude was not specified in device options.');
	}

	if(!$units = @$options['units']) {
		$units = 'standard';
	}
	
	$api_url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$long}&units={$units}&appid={$api_key}";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$weather_data = curl_exec($ch);
	curl_close($ch);

	if($weather_data = @json_decode($weather_data, true)) {
		if($weather_data['cod'] == 200) {
			# Return weather data
			\HomioPi\Response\success(null, $weather_data);
		} else {
			\HomioPi\Response\error($weather_data['message']);
		}
	}

	\HomioPi\Response\error();
?>