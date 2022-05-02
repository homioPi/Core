$(document).on('homiopi.ready', function() {
	if(homiopi.page.current() != 'dashboard/main') {
		return;
	}

	if(typeof HomioPi?.data?.dashboard?.widgets == 'undefined') {
		return;
	}

	$.each(homiopi.data.dashboard.widgets, function(widgetId, widget) {
		// Find widget element
		const elem = document.querySelector(`[data-widget-id="${widgetId}"] .dashboard-widget-content`);

		// Load the widget
		try {
			new widget(elem);
		} catch(err) {
			console.error(`An error occured while loading widget ${widgetId}:`, err);
		}
	})
})