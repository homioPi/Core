<?php 
    namespace HomioPi\Database;

    function connect() {
        return new \MysqliDb(
            \HomioPi\Config\get('hostname', 'database'),
            \HomioPi\Config\get('username', 'database'),
            \HomioPi\Config\get('password', 'database'),
            \HomioPi\Config\get('database', 'database')
        );
    }
?>