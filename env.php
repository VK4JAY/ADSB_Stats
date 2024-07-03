<?php


// set lookup-interval default is 1 (must be integer between 1 - 900) this is the frequency the script runs and writes to database or looks for alerts
$user_set_array['sleep'] = 30;

// set the maintenance period to clean the database, resolve missing flight details, and update aircraft details
$user_set_array['maintenance'] = 600;

// set parameters for database connection
//$user_set_array['db_name'] = 'adsb'; 
//$user_set_array['db_host'] = '192.168.0.2'; 
//$user_set_array['db_user'] = 'root'; 
//$user_set_array['db_pass'] = '73Cr3XqZ7yQZ';
include('dbconnect.php');

// set path to aircraft.json file
$user_set_array['url_json'] = 'http://192.168.0.252:8081/data/';

// set your timezone see http://php.net/manual/en/timezones.php
$user_set_array['time_zone'] = 'Australia/Brisbane';

// set you local airport using the 3 letter iata code
$localAirport = 'BNE';
$userCountry = 'AU';




?>