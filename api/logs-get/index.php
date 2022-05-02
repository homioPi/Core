<?php
	chdir(__DIR__);
	include_once("{$_SERVER['DOCUMENT_ROOT']}/autoload.php");
?>
<?php
    $log_items = [];
   
    if(\HomioPi\Users\CurrentUser::getFlag('is_admin') !== true) {
        \HomioPi\Response\error('no_permission', \HomioPi\Locale\translate('generic.error.no_page_access'));
    }

    if(isset($_POST['latest']) && is_numeric($_POST['latest'])) {
        $log_items = \HomioPi\Log\readLatest($_POST['latest']);
        $method = 'latest';
    } else if(@is_numeric($_POST['min']) && @is_numeric($_POST['max'])) {
        $log_items = \HomioPi\Log\readIndexesBetween(@$_POST['min'], @$_POST['max']);
        $method = 'between';
    } else {
		\HomioPi\Response\error('request_field_missing', 'Field latest or min and max is missing.');
    }

    foreach ($log_items as $log_item_index => $log_item) {
        if(isset($log_item['at'])) {
            if(!isset($newest_item)) {
                $newest_item = $log_item;
            }

            $day = HomioPi\Locale\date_format($log_item['at']);

            $log_items[$log_item_index]['at_readable'] = \HomioPi\Locale\translate('generic.time_format.day_hour_minute_second', null, [$day, date('H', $log_item['at']), date('i', $log_item['at']), date('s', $log_item['at'])]);
        } else {
            unset($log_items[$log_item_index]);
        }
    }

    if(isset($_GET['no_indexes']) && $_GET['no_indexes'] == 'true') {
        exit(json_encode(['success' => true, 'message' => array_values($log_items), 'method' => $method, 'newest_item' => $newest_item]));
    } else {
        exit(json_encode(['success' => true, 'message' => $log_items, 'method' => $method, 'newest_item' => $newest_item], JSON_FORCE_OBJECT));
    }
?>