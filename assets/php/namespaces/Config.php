<?php 
    namespace HomioPi\Config;

    function load() {
        return parse_ini_file(ROOT.'HomioPi.ini');
    }

    function get($key, $config = 'main') {
        if(!isset(CONFIG[$config][$key])) {
            return null;
        }

        return CONFIG[$config][$key];
    }
?>