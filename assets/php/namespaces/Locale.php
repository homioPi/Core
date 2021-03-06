<?php 
    namespace HomioPi\Locale;

    if(!($locale = \HomioPi\Users\CurrentUser::getSetting('locale') ?? \HomioPi\Config\get('locale'))) {
        $locale = \HomioPi\Config\get('locale');
    }
    
    if(!file_exists(DIR_ASSETS."/locale/{$locale}.json")) {
        $locale = \HomioPi\Config\get('locale');
    }

    if((define('TRANSLATIONS', file_get_json(DIR_ASSETS."/locale/{$locale}.json"))) === false) {
        exit('Failed to load translations!');
    }

	function translate($key, $replacements = [], $fallback = null, $translations = null) {
		if(!isset($fallback)) {
			$fallback = $key;
		}

        if(!isset($translations)) {
            $translations = TRANSLATIONS;
        }

        if(!isset($translations[$key])) {
            return $fallback;
        }

        $translation = $translations[$key];
        
        if(!empty($replacements)) {
            $replacements_count = count($replacements);
            for ($i=$replacements_count-1; $i >= 0; $i--) { 
                $translation = str_replace("%{$i}", $replacements[$i], $translation);
            }
        }
            
        return $translation;
	}

    function date_format_diff($time, $compare_with = null) {
        if(is_string($time)) {
            $time = strtotime($time);
        }

        $compare_with = $compare_with ?? time();
        if(is_string($compare_with)) {
            $compare_with = strtotime($compare_with);
        }

        $date_first = date_create(date('Y-m-d H:i:s', $time));
        $date_last  = date_create(date('Y-m-d H:i:s', $compare_with));

        $diff = (array) date_diff($date_first, $date_last);

        // Invert difference if $time is later than $compare_with
        if($time > $compare_with) {
            array_walk($diff, function(&$v) {
                if(is_numeric($v)) {
                    $v *= -1;
                }
            });
        }
        
        $diff = \HomioPi\Locale\date_diff_round($diff);

        if($diff['y'] != 0) {
            $translation_key = \HomioPi\Locale\date_diff_translation_key($diff['y'], 'year');
            $translation     = \HomioPi\Locale\translate($translation_key, [abs($diff['y'])]);
        } else if($diff['m'] != 0) {
            $translation_key = \HomioPi\Locale\date_diff_translation_key($diff['m'], 'month');
            $translation     = \HomioPi\Locale\translate($translation_key, [abs($diff['m'])]);
        } else if($diff['d'] != 0) {
            $translation_key = \HomioPi\Locale\date_diff_translation_key($diff['d'], 'day');
            $translation     = \HomioPi\Locale\translate($translation_key, [abs($diff['d'])]);
        } else if($diff['h'] != 0) {
            $translation_key = \HomioPi\Locale\date_diff_translation_key($diff['h'], 'hour');
            $translation     = \HomioPi\Locale\translate($translation_key, [abs($diff['h'])]);
        } else if($diff['i'] != 0) {
            $translation_key = \HomioPi\Locale\date_diff_translation_key($diff['i'], 'minute');
            $translation     = \HomioPi\Locale\translate($translation_key, [abs($diff['i'])]);
        } else {
            $translation_key = \HomioPi\Locale\date_diff_translation_key($diff['s'], 'second');
            $translation     = \HomioPi\Locale\translate($translation_key, [abs($diff['s'])]);
        }

        return $translation;
    }

    function date_diff_translation_key($diff, $diff_type) {
        $translation_key = 'now';
        
        if($diff < -1) {
            $translation_key = $diff_type.'s_in';
        } else if($diff == -1) {
            $translation_key = $diff_type.'_in';
        } else if($diff == 1) {
            $translation_key = $diff_type.'_ago';
        } else if($diff > 1) {
            $translation_key = $diff_type.'s_ago';
        }

        return "generic.time_format_diff.{$translation_key}";
    }

    function date_diff_round($diff) {
        $diff_result = $diff;

        $diff = array_reverse(array_splice($diff, 0, 6));
        $timestamp_map = ['s', 'i', 'h', 'd', 'm', 'y'];
        $timestamp_max = ['s' => 60, 'i' => 60, 'h' => 24, 'd' => 30, 'm' => 12];

        foreach ($diff as $timestamp => $difference) {
            if($timestamp == 'y') {
                break;
            }
            $timestamp_next = $timestamp_map[array_search($timestamp, $timestamp_map)+1];

            if($difference >= $timestamp_max[$timestamp]/2) {
                $diff_result[$timestamp] = 0;
                $diff_result[$timestamp_next] = $diff[$timestamp_next] + 1;
            }
        }

        return $diff_result;
    }

    function date_format($format, $time = 0) {
        if(is_string($time)) {
            $time = strtotime($time);
        }

        if(!is_numeric($time)) {
            return false;
        }

        list($date_format, $time_format) = explode(',', $format);
        if($time_format == 'best') {
            $time_format = 'full';
        }

        $today_midnight = strtotime('today');
        $time_midnight = strtotime(\date('Y-m-d', $time));

        $days_ago = floor(($today_midnight - $time_midnight)/86400);

        switch($days_ago) {
            case 0:
                if($date_format == 'best') {
                    return \HomioPi\Locale\date_format_diff($time);
                }

                $day = \HomioPi\Locale\translate('generic.day.today');
                break;

            case 1:
                $day = \HomioPi\Locale\translate('generic.day.yesterday');
                break;

            default:
                $day = \HomioPi\Locale\translate('generic.day.'.date('w', $time));
                break;
        }

        $day_of_week = \HomioPi\Locale\translate('generic.day.'.date('w', $time));

        if($date_format == 'best') {
            $date_format = $days_ago <= 5 ? 'short' : 'full';
        }

        $am_pm_translation = (date('a', $time) == 'am' ? \HomioPi\Locale\Translate('generic.time_signature.am') : \HomioPi\Locale\Translate('generic.time_signature.pm'));

        return \HomioPi\Locale\Translate("generic.time_format.date_{$date_format}_time_{$time_format}", [
            \date('H', $time),  // 0: Time in 24-hour format
            \date('i', $time),  // 1: Minutes of hour
            \date('s', $time),  // 2: Seconds of hour
            $day,               // 3: Name of day, today or yesterday
            \date('d', $time),  // 4: Day of month
            \date('m', $time),  // 5: Month of year
            \date('Y', $time),  // 6: Year
            \date('g', $time),  // 7: Time in 12-hour format
            $am_pm_translation, // 8: AM or PM,
            $day_of_week        // 9: Name of day of week
        ]);
    }
?>