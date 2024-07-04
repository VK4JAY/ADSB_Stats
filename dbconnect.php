<?php

// get Database values
$user_set_array['db_name'] = getenv('DB_NAME') ?? 'adsb';
$user_set_array['db_host'] = getenv('DB_HOST') ?? '127.0.0.1';
$user_set_array['db_user'] = getenv('DB_USER') ?? 'root';
$user_set_array['db_pass'] = getenv('DB_PASSWORD') ?? '123456';

?>