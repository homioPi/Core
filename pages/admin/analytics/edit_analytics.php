<?php 
	$analytics_fallback = [
		'axes' => [
			'x' => ['title' =>\HomioPi\Locale\translate('state.unknown'), 'unit' => ''],
			'y' => ['title' =>\HomioPi\Locale\translate('state.unknown'), 'unit' => ''],
		],
		'columns' => []
	];

	if($devices = get_device_list()) {
		while (!isset($device_id) || isset($devices[$device_id])) {
			$device_id = bin2hex(random_bytes(2));
		}
	}
	$new_analytics = true;

	if(isset($_GET['device_id'])) {
		$device_id = $_GET['device_id']; 
		$new_device = false; 
		if(!$device = get_device_info($device_id)) {
			exit();
		}

		if(!$info = get_analytics_info($device_id)) {
			exit();
		}
	}
?>
<form method="POST" action="../_handlers/edit_device.php" id="form-edit-device" class="tile transition-slide-top" data-device-key="<?php echo($_GET['device_id']); ?>">
	<div class="row">
		<div class="col-12 col-md-6 mb-2">
			<span class="tile-title"><?php\HomioPi\Locale\translate('admin.devices.edit_device.name.title', true); ?></span>
			<input name="name" placeholder="<?php\HomioPi\Locale\translate('admin.devices.edit_device.name.title', true); ?>" value="<?php echo($device['name']); ?>" type="text" />
		</div>
		<div class="col-12 col-md-6 mb-2">
			<span class="tile-title"><?php\HomioPi\Locale\translate('admin.devices.edit_device.handler.title', true); ?></span>
			<div class="dropdown-search-wrapper">
				<div class="btn-list bg-tertiary dropdown-search-results" data-type="single" forinput="device_handler">
					<?php 
						$device_manifest = ['name' =>\HomioPi\Locale\translate('state.unknown'), 'namespace' => 'none'];
						if($manifest_files = glob("{$d['assets']}/device_handlers/*/_manifest.json")) {
							foreach ($manifest_files as $manifest_file) {
								if($manifest = \HomioPi\Devices\get_handler_manifest(basename(dirname($manifest_file)))) {
									$manifest['icon'] = '';
									$manifest['namespace'] = basename(dirname($manifest_file));

									if($manifest['analytics_support'] == true) {
										$manifest['icon'] = icon_html('far.chart-area', 'icon-inline dropdown-search-result-icon-end text-debug');
									}

									if($manifest['run_type'] == 'cli') { ?>
										<span class="btn btn-secondary dropdown-search-result" val="<?php echo($manifest['namespace']); ?>"><?php echo($manifest['name']); ?> <?php echo($manifest['icon']); ?></span> <?php
									}

									if(isset($device['handler'])) {
										if($manifest['namespace'] == $device['handler']) {
											$device_manifest = $manifest;
										}
									} else {
										$device['handler'] = '';
										$device_manifest['name'] = \HomioPi\Locale\translate('state.unset');
									}
								}
							}
						}
					?>
					<span class="no-results dropdown-search-result" style="display: none;"><?php\HomioPi\Locale\translate('generic.error.search_no_results', true); ?></span>
				</div>
				<input data-setting="handler" class="dropdown-search" data-name="device_handler" type="text" value="<?php echo($device_manifest['name']); ?>"/>
				<input data-setting="handler" class="dropdown-search-hidden" onchange="updateDeviceOption($(this));" type="hidden" name="device_handler" value="<?php echo($device_manifest['namespace']); ?>" />
			</div>
		</div>
		<div class="col-12 col-md-6 mb-2 device-options">
			<script>reloadHandlerOptions('<?php echo($device['handler']); ?>');</script>
			<span class="tile-title"><?php\HomioPi\Locale\translate('admin.devices.edit_device.options.title', true); ?></span>
			<div class="device-options-inner">
				<input type="hidden" name="device_id" value="<?php echo($_GET['device_id']); ?>">
			</div>
			<div class="row rounded overflow-hidden device-option-template mb-1" style="display: none;">
				<div class="col-12 col-md-6 px-0 border-right border-secondary">
					<div class="option-key bg-tertiary text-muted input rounded-0 text-overflow-ellipsis"></div>
				</div>
				<div class="col-12 col-md-6 px-0">
					<input class="option-value rounded-0" type="text">
				</div>
			</div>
		</div>
	</div>
</form>