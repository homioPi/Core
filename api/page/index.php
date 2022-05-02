<?php 
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php
	if(!isset($_POST['url'])) {
		\HomioPi\Response\error('request_field_missing', 'Field url is missing.');
	}

	if($_POST['url'] == 'home/main') {
		$_POST['url'] = \HomioPi\Users\CurrentUser::getSetting('home') ?? 'dashboard/main';
	}

	$url = array_replace(
		['path' => '', 'query' => ''], 
		parse_url($_POST['url'])
	);

	// Obtain url query and modify $_GET variable
	parse_str($url['query'], $query);
	$_GET = $query;

	// Get page path
	$page_path     = trim($url['path'], '/');
	$content_path  = DIR_PAGES."/{$page_path}.php";
	$manifest_path = DIR_PAGES."/{$page_path}.json";
	
	// Load manifest
	if(!$manifest = file_get_json($manifest_path)) {
		\HomioPi\response\error('error_loading_manifest');
	}

	// Authorize user unless specified otherwise in page manifest
	if(!isset($manifest['require_auth']) || $manifest['require_auth'] !== false) {
		\HomioPi\Authorization\authorize();

		// Close the session for writing as to not block other requests
        session_write_close();
	}

	// Load page content
	if(!$content = get_script_output($content_path)) {
		\HomioPi\response\error('error_loading_content');
	}

	$response = [
		'manifest' => $manifest,
		'html'     => $content
	];

	\HomioPi\response\success('success_loaded', $response);
?>