<?php 
	chdir(__DIR__);
	include_once("../../../../autoload.php");
?>
<?php 
	if(count($argv) <= 3) {
		\HomioPi\Response\error('Insufficient amount of arguments given.');
	}

	$options = shell_arg_decode($argv[3]);
	
	$state = $argv[2] == 'on' ? 1 : 0;

	if(!isset($options['unit'])) {
		\HomioPi\Response\error('option_unit_missing');
	}

	if(!isset($options['address'])) {
		\HomioPi\Response\error('option_address_missing');
	}

	if(!isset($options['port'])) {
		\HomioPi\Response\error('option_port_missing');
	}

	if(!isset($options['baud'])) {
		\HomioPi\Response\error('option_baud_missing');
	}

	if(!isset($options['pulse_length'])) {
		\HomioPi\Response\error('option_pulse_length_missing');
	}

	$cmd = "/usr/bin/python3 send.py {$options['address']} {$options['unit']} {$state} {$options['pulse_length']} {$options['port']} {$options['baud']}";
	if(!$output = @json_decode(shell_exec_timeout($cmd, 10), true)) {
		\HomioPi\Response\error('invalid_output');
	}

	if($output['success'] === true) {
		\HomioPi\Response\success();
	} else if(isset($output['data'])) {
		\HomioPi\Response\error($output['data']);
	} else {
		\HomioPi\Response\error();
	}

	\HomioPi\Log\write('KlikAanKlikUit', "Sent address={$address},unit={$unit},state={$state},pulse_length={$pulse_length}.", 'debug');
?>