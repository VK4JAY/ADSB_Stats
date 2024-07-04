<?php

// set loop-interval default is 30 this is the frequency the script runs and writes to database
$user_set_array['sleep'] = getenv('RUN_INTERVAL') ?? '30'; 

// set the maintenance period to clean the database, resolve missing flight details, and update aircraft details
$user_set_array['maintenance'] = getenv('MAINTENANCE_INTERVAL') ?? '600'; 

// set the database details
$user_set_array['db_name'] = getenv('DB_NAME') ?? 'adsb';
$user_set_array['db_host'] = getenv('DB_HOST') ?? '127.0.0.1';
$user_set_array['db_user'] = getenv('DB_USER') ?? 'root';
$user_set_array['db_pass'] = getenv('DB_PASSWORD') ?? '123456';

// set path to aircraft.json file. This is usually in the format http://IP_ADDRESS_OF_ADSB_NODE/data/
// There is no need to include the aircraft.json at the end of the url, this is added automatically
$user_set_array['url_json'] = getenv('AIRCRAFT_JSON_URL'); 

// set your timezone see http://php.net/manual/en/timezones.php
$user_set_array['time_zone'] = getenv('TIME_ZONE') ?? 'Australia/Brisbane'; 

// set your local airport using the 3 letter iata code. Default is my local airport of Brisbane, Australia
$localAirport = getenv('LOCAL_AIRPORT') ?? 'BNE'; 

// set your local country code. Default is AU for Australia
$userCountryCode = getenv('COUNTRY_CODE') ?? 'AU'; 

?>