<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php 
    $response = [];

	$devices = \HomioPi\Devices\get_all();

	foreach ($devices as $properties) {

		if(is_null($properties['value'])) {
			continue;
		}

		$response[$properties['id']] = [
			'name'        => $properties['name'],
			'value'       => $properties['value'],
			'shown_value' => $properties['shown_value'] ?? $properties['value']
		];
	}

	if(empty($response)) {
		\HomioPi\response\error();
	} else {
		\HomioPi\response\success(null, $response);
	}
?>