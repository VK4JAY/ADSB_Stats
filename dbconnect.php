<?php
// set parameters for database connection
$user_set_array['db_name'] = 'adsb'; $user_set_array['db_host'] = 'IP_NAME_DATABASE'; $user_set_array['db_user'] = 'DB_USERNAME'; $user_set_array['db_pass'] = 'DB_PASSWORD';

//Connect to DB
$db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>