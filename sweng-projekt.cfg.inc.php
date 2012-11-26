<?php
// Am Besten ausserhalb des Document Root!
// Sonst mit .htaccess sichern!
// und wenn das fehlschlaegt, ist die Endung immerhin noch PHP

// Die Konstantennamen richten sich nach PDO
define('DB_HOST', 'localhost'); // host
define('DB_NAME',     'sweng_projekt'); // dbname
define('DB_USERNAME', 'sweng_projekt'); // username
define('DB_PASSWORD', 'sweng_projekt'); // password

// Data Source Name for PHP Data Objects -- a kind of connection string
define('PDO_DSN', 'mysql:host='.DB_HOST.';dbname='.DB_NAME);
// define PDO_DRIVER_OPTIONS
