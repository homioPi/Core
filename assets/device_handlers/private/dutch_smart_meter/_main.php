<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
    set_time_limit(70);

	if(count($argv) <= 1) {
		\HomioPi\Response\error('Insufficient amount of arguments given.');
	}

	$id = $argv[1];

	$device  = new \HomioPi\Devices\Device($id);
	$options = $device->getProperty('options');

	// Check if dsmr_version was specified
	if(!isset($options['dsmr_version'])) {
		\HomioPi\Response\error('option_dsmr_version_missing', 'Option dsmr_version was not specified in device options.');
	}
	$dsmr_version = $options['dsmr_version'];

	// Check if port was specified
	if(!isset($options['port'])) {
		\HomioPi\Response\error('option_port_missing', 'Option port was not specified in device options.');
	}
	$port = $options['port'];

    $cmd = "/usr/bin/python3 inner.py {$port} {$dsmr_version}";

    if(!$execute(escapeshellcmd($cmd, $meter_json, 65)) {
		\HomioPi\Response\error('error_running_script', 'Failed to run handler script.');
	}

    if(($meter_data = @json_decode($meter_json, true)) === false) {
		\HomioPi\Response\error('error_decoding_data', 'Failed to decode data returned from handler script.');
	}
	
	if(!isset($meter_data['success']) || $meter_data['success'] != true || !isset($meter_data['electricity']) || !isset($meter_data['gas'])) {
		\HomioPi\Response\error('error_parsing_data', 'Failed to parse data returned from handler script.');
	}

 	// Return DSM data
	\HomioPi\Response\success(null, $meter_data);
?>