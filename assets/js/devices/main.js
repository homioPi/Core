var updateValuesTimeout;

homioPiAssign('devices', {
	updateValuesLoop: (interval = 1000) => {
		// if(homioPi.page.current() != 'devices/main') {
		// 	return false;
		// }

		homioPi.devices.updateValues();
		
		updateValuesTimeout = setTimeout(() => {
			homioPi.devices.updateValuesLoop(interval);
		}, interval);
	},

	updateValues: () => {
		homioPi.api.call('devices-get-values', {}).then((response) => {
			$.each(response.data, function(deviceId, properties) {
				const $device = $(`.device[data-id="${deviceId}"]`);

				if($device.length == 0) {
					return true;
				}

				if($device.attr('data-value') == properties['value']) {
					return true;
				}

				if($device.attr('data-value-updating-disabled') == 'true') {
					return true;
				}

				$device.find('.device-control').value(properties['value'], properties['shown_value']);
				$device.attr('data-value', properties['value']);
			})
		})
	}
})

$(document).on('homioPi.ready', () => {
	if(homioPi.page.current() != 'devices/main') {
		console.log(updateValuesTimeout);
		clearTimeout(updateValuesTimeout);
	}

	homioPi.devices.updateValuesLoop()
});

$(document).ready(function() {
	$('.range-thumb').on('touchstart', function() {
		$(this).siblings('.range-tooltip').css('opacity', 1);
	}).on('touchend', function() {
		$(this).siblings('.range-tooltip').css('opacity', 0);
	})
})

$(document).on('input', '.device-control[data-type="range"]', function() {
	let $wrapper = $(this);
	$wrapper.parents('.device').attr('data-value-updating-disabled', 'true');
});

function loadDeviceSearchResults($device) {
	let $input  = $device.find('.input[data-type="search"]');
	let handler = $device.attr('data-search-handler');
	let term    = $input.val();
	let url     = `${homioPi.data.webroot}/assets/device_handlers/public/${handler}/search.php?id=${$device.attr('data-id')}&value=${term}`;

	$input.parent().showLoading();
	$.get(url, function(response) {
		$input.parent().hideLoading();
		try {
			response = JSON.parse(response);
			if(response['success'] == true) {
				$input.clearResults();
				$.each(response['results'], function(i, result) {
					$input.appendResult(result['value'], result['title'], result['title'], result['description'], result['thumbnail']);
				})
			} else {
				throw response['message'];
			}
		} catch (err) {
			sendMessage(l('generic.error'), [], true);
			console.error(err);
		}
	})
}

$(document).on('click', '.categories-list-row', debounce(function() {
	let inactive_categories = [];
	$('.categories-list-row').find('.category-button').each(function() {
		if(!$(this).hasClass('active')) {
			inactive_categories.push($(this).attr('data-category'));
		}
	})

	console.log('debounce!');

	homioPi.users.currentUser.setSetting('devices_inactive_categories', inactive_categories).then(() => {
		homioPi.page.reload();
	}).catch();
}, 500))

$(document).on('homioPi.search_value_change', '.device .input[data-type="search"]', function() {
	let $input  = $(this);
	let $device = $input.parents('.device');

	setDeviceValue($device, $input.getValue(), $input.getShownValue());
})

$(document).on('change', '.device .device-control:not([data-type="search"])', function() {
	let $control = $(this);
	let $device  = $control.parents('.device');
	let value    = $control.value();

	setDeviceValue($device, value, value);
})

function setDeviceValue($device, value, shownValue = undefined) {
	console.log(`Trying to set value of device ${$device.attr('data-id')} to ${value}.`);
	
	$device.attr('data-value-updating-disabled', 'true');

	let controlType = $device.attr('data-control-type');
	let $deviceName = $device.find('.device-name');
	let deviceName  = $deviceName.text();
	let id          = $device.attr('data-id');
	let handler     = $device.attr('data-handler');

	$deviceName.showLoading();

	if(typeof shownValue == 'undefined') {
		shownValue = value;
	}

	let data = {
		'id':          id,
		'value':       value,
		'shown_value': shownValue,
		'force_set':   $device.attr('data-force-set')
	};
	
	homioPi.api.call('device-set-value', data).then(() => {
		$device.attr('data-value-updating-disabled', 'false');
		$deviceName.hideLoading();

		console.log('Device value changed!');

		if(controlType == 'toggle') {
			homioPi.message.send(homioPi.locale.translate(`devices.message.toggled_${value}_success`, [deviceName]));
		} else {
			homioPi.message.send(homioPi.locale.translate(`devices.message.changed_success`, [deviceName, shownValue]));
		}

	}).catch(() => {
		$device.attr('data-value-updating-disabled', 'false');
		$deviceName.hideLoading();

		console.error('Failed to change device value.');

		if(controlType == 'toggle') {
			homioPi.message.error(homioPi.locale.translate(`devices.error.toggled_${value}_error`, [deviceName]));
		} else {
			homioPi.message.error(homioPi.locale.translate(`devices.error.changed_error`, [deviceName, shownValue]));
		}
	})
}

function toggleFavorite($device) {
	if($device.attr('data-favorite') == 'true') {
		$device.attr('data-favorite', 'false');
	} else {
		$device.attr('data-favorite', 'true');
	}

	saveFavoriteDevicesList();
}

function saveFavoriteDevicesList() {
	let favorites = [];
	$('.device[data-favorite="true"]').each(function() {
		favorites.push($(this).attr('data-id'));
	})
	favorites = favorites.join(',');

	saveState('devices_favorite', favorites);
}