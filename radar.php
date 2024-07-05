#!/usr/bin/php
<?php

// Include environment values
include('env.php');

// Include functions
include('functions.php');


// set the rectangle and altitude to store aircraft-data in database - if your lon is negative be aware to use the right values for max and min
//$user_set_array['max_lat'] = 90.000000;    $user_set_array['min_lat'] = -90.000000;    $user_set_array['max_alt'] = 50000;
//$user_set_array['max_lon'] = 180.000000;    $user_set_array['min_lon'] = -180.000000;



// wildcard search function for external filter data
/*function func_wildcard_search($code, $user_code_array, $wildcard_mode) {
        $match = false;
        $code = strtoupper($code);
        if ($wildcard_mode) {
                foreach ($user_code_array as $pattern) {
                        if (preg_match('/^' . trim($pattern) . '$/', $code)) $match = true;
                }
        } else {
                $user_code_array = array_map('trim', $user_code_array);
                if (in_array($code, $user_code_array)) $match = true;
        }
        return $match;
}
*/


$i = 0;
$alert_message = '';
$sent_alert_messages = 0;
$alert_trigger_array = array();
$start_time = time();
$maintenance_clock = time();
date_default_timezone_set($user_set_array['time_zone']);

// fetch receiver.json and read receiver latitude and longitude
$json_receiver_location = json_decode(file_get_contents($user_set_array['url_json'] . 'receiver.json'), true);
isset($json_receiver_location['lat']) ? $rec_lat = $json_receiver_location['lat'] : $rec_lat = 0;
isset($json_receiver_location['lon']) ? $rec_lon = $json_receiver_location['lon'] : $rec_lon = 0;

// create array to prevent less api queries for route information, and less database queries
$flight_array = array();

while (true) {

    $x = 0;
    $sql = '';
    $start_loop_microtime = microtime(true);
    $current_flights = array();


        // fetch aircraft.json and read timestamp and overall message number
        $json_data_array = json_decode(file_get_contents($user_set_array['url_json'] . 'aircraft.json'), true);
        isset($json_data_array['now']) ? $ac_now = $json_data_array['now'] : $ac_now = time();
        isset($json_data_array['messages']) ? $ac_messages_total = $json_data_array['messages'] : $ac_messages_total = '';

        // fetch and read external filter data files
        if ($user_set_array['filter_mode_alert'] || $user_set_array['filter_mode_database']) {
                $hex_code_array = explode(',', str_replace('%', '.', strtoupper(file_get_contents($user_set_array['hex_file_path']))));
                $flight_code_array = explode(',', str_replace('%', '.', strtoupper(file_get_contents($user_set_array['flight_file_path']))));
        }

        // compute receiver message rate averaged over 30 seconds
        $message_rate_array[] = array('messages' => $ac_messages_total, 'time' => $ac_now);
        if (time() - $message_rate_array[0]['time'] > 30) array_shift($message_rate_array);
        $delta_message_number = $message_rate_array[count($message_rate_array) - 1]['messages'] - $message_rate_array[0]['messages'];
        $delta_message_time = $message_rate_array[count($message_rate_array) - 1]['time'] - $message_rate_array[0]['time'];
        $delta_message_time > 0 ? $message_rate = round($delta_message_number / $delta_message_time, '1') : $message_rate = 0;
        
        $ac_route = "";
        $ac_src = "";
        $ac_dst = "";

        // loop through aircraft section of aircraft.json file
        foreach ($json_data_array['aircraft'] as $row) {
                isset($row['hex']) ? $ac_hex = $row['hex'] : $ac_hex = '';
                isset($row['flight']) ? $ac_flight = trim($row['flight']) : $ac_flight = '';
                isset($row['r']) ? $ac_reg = trim($row['r']) : $ac_reg = '';
                isset($row['alt_baro']) ? $ac_altitude = $row['alt_baro'] : $ac_altitude = '';
                isset($row['lat']) ? $ac_lat = $row['lat'] : $ac_lat = '';
                isset($row['lon']) ? $ac_lon = $row['lon'] : $ac_lon = '';
                isset($row['track']) ? $ac_track = $row['track'] : $ac_track = '';
                isset($row['gs']) ? $ac_speed = $row['gs'] : $ac_speed = ''; // Ground Speed
                isset($row['baro_rate']) ? $ac_vert_rate = $row['baro_rate'] : $ac_vert_rate = '';
                isset($row['dbFlags']) ? $ac_dbFlags = trim($row['dbFlags']) : $ac_dbFlags = '';
                isset($row['ws']) ? $ac_ws = trim($row['ws']) : $ac_ws = ''; // Wind Speed
                isset($row['oat']) ? $ac_oat = trim($row['oat']) : $ac_oat = ''; // Outside Air Temp
                isset($row['tat']) ? $ac_tat = trim($row['tat']) : $ac_tat = ''; // Total Air Temp
                isset($row['roll']) ? $ac_roll = trim($row['roll']) : $ac_roll = ''; // Negative is left, positive is right
                isset($row['messages']) ? $ac_messages = $row['messages'] : $ac_messages = '';
                isset($row['category']) ? $ac_category = $row['category'] : $ac_category = '';
                isset($row['squawk']) ? $ac_squawk = $row['squawk'] : $ac_squawk = '';
                            
                $ac_lat && $ac_lon ? $ac_dist = round(func_haversine($rec_lat, $rec_lon, $ac_lat, $ac_lon), 1) : $ac_dist = '';

                 // Check if $callsign is present in the array
                $flight_exists = false;
                $hex = $ac_hex; // I needed to change this from $ac_flight to $ac_hex as sometimes the flight was not resolved, ending in multiple entries in DB
                $callsign = $ac_flight; // Needed for the functions to resolve route 
                $update_db = false;
                $insert_db = false;

                foreach ($flight_array as $flight_info) {
                    if ($flight_info['callsign'] == $callsign) {
                        echo "Existing --> ";
                        $flight_exists = true;
                        $ac_route = $flight_info['route'];
                        $ac_src = $flight_info['src'];
                        $ac_dst = $flight_info['dst'];
                        $flight_info['time'] = $ac_now;

                        // Check if this is the furthest they have been away
                        if ($ac_dist > $flight_info['longest']){
                            $update_db = true;
                            $ac_longest = $ac_dist;
                        } else {
                            $ac_longest = $flight_info['longest'];
                         }

                        // Check if this is the closest they have been
                        if ($ac_dist < $flight_info['shortest']){
                            $update_db = true;
                            $ac_shortest = $ac_dist;
                        } else {
                            $ac_shortest = $flight_info['shortest'];
                        }

                        // Check if this is the HIGHEST they have been
                        if ($ac_altitude > $flight_info['highest']){
                            $update_db = true;
                            $ac_highest = $ac_altitude;
                        } else {
                            $ac_highest = $flight_info['highest'];
                        }

                        // Check if this is the LOWEST they have been
                        if ($ac_altitude < $flight_info['lowest']){
                            $update_db = true;
                            $ac_lowest = $ac_altitude;
                        } else {
                            $ac_lowest = $flight_info['lowest'];
                        }

                        // Check if this is the FASTEST they have been
                        if ($ac_speed > $flight_info['fastest']){
                            $update_db = true;
                            $ac_fastest = $ac_speed;
                        } else {
                            $ac_fastest = $flight_info['fastest'];
                        }

                        // Check if this is the SLOWEST they have been
                        if ($ac_speed < $flight_info['slowest']){
                            $update_db = true;
                            $ac_slowest = $ac_speed;
                        } else {
                            $ac_slowest = $flight_info['slowest'];
                        }

                        // Check if this is the FASTEST WIND SPEED
                        if ($ac_ws > $flight_info['ws_high']){
                            $update_db = true;
                            $ac_ws_high = $ac_ws;
                        } else {
                            $ac_ws_high = $flight_info['ws_high'];
                        }

                        // Check if this is the SLOWEST WIND SPEED
                        if ($ac_ws < $flight_info['ws_low']){
                            $update_db = true;
                            $ac_ws_low = $ac_ws;
                        } else {
                            $ac_ws_low = $flight_info['ws_low'];
                        }

                        // Check Roll left
                        if ($ac_roll < $flight_info['roll_left']){
                            $update_db = true;
                            $ac_roll_left = $ac_roll;
                        } else {
                            $ac_roll_left = $flight_info['roll_left'];
                        }

                        // Check Roll right
                        if ($ac_roll > $flight_info['roll_right']){
                            $update_db = true;
                            $ac_roll_right = $ac_roll;
                        } else {
                            $ac_roll_right = $flight_info['roll_right'];
                        }

                        // Check if messages increased
                        if ($ac_messages > $flight_info['messages']){
                            $update_db = true;
                        }

                        echo $ac_hex." --  ".$callsign." -- ".$ac_route." -- ".$ac_src." --> ".$ac_dst. "\n";
                                         
                        //break;
                    }
                }

                // If $flight is not present, add it to the array, then check the airports database and update as needed
                if (!$flight_exists) {
                    echo "New Flight --> Resolving details --> ";
                    $insert_db = true;
                    
                    // Get Route information
                    $failed = 0;
                    $routeInfo = getRouteInfoADSBDB($callsign);
                    if( $routeInfo === null){
                        echo "Failed to resolve Route, using backup API --> ";
                        
                        $routeInfo = getRouteInfo($callsign);
                        
                        if( $routeInfo === null){
                            echo "Failed to resolve Route --> ";
                            $failed = 1;
                        }
                    }

                                
                    // These variables are needed for the flights table
                    isset($routeInfo['route']) ? $ac_route = $routeInfo['route'] : $ac_route = '';
                    isset($routeInfo['src_country_iso_name']) ? $ac_src_country = $routeInfo['src_country_iso_name'] : $ac_src_country = '';
                    isset($routeInfo['src_name']) ? $ac_src = $routeInfo['src_name'] : $ac_src = '';
                    isset($routeInfo['dst_name']) ? $ac_dst = $routeInfo['dst_name'] : $ac_dst = '';
                    isset($routeInfo['dst_country_iso_name']) ? $ac_dst_country = $routeInfo['dst_country_iso_name'] : $ac_dst_country = '';
                    
                    // These variables are needed for the airports table
                    isset($routeInfo['src_country_name']) ? $src_country_name = $routeInfo['src_country_name'] : $src_country_name = '';
                    isset($routeInfo['src_elevation']) ? $src_elevation = $routeInfo['src_elevation'] : $src_elevation = '';
                    isset($routeInfo['src_iata_code']) ? $src_iata_code = $routeInfo['src_iata_code'] : $src_iata_code = '';
                    isset($routeInfo['src_icao_code']) ? $src_icao_code = $routeInfo['src_icao_code'] : $src_icao_code = '';
                    isset($routeInfo['src_latitude']) ? $src_latitude = $routeInfo['src_latitude'] : $src_latitude = '';
                    isset($routeInfo['src_longitude']) ? $src_longitude = $routeInfo['src_longitude'] : $src_longitude = '';
                    isset($routeInfo['src_municipality']) ? $src_municipality = $routeInfo['src_municipality'] : $src_municipality = '';
                    isset($routeInfo['dst_country_name']) ? $dst_country_name = $routeInfo['dst_country_name'] : $dst_country_name = '';
                    isset($routeInfo['dst_elevation']) ? $dst_elevation = $routeInfo['dst_elevation'] : $dst_elevation = '';
                    isset($routeInfo['dst_iata_code']) ? $dst_iata_code = $routeInfo['dst_iata_code'] : $dst_iata_code = '';
                    isset($routeInfo['dst_icao_code']) ? $dst_icao_code = $routeInfo['dst_icao_code'] : $dst_icao_code = '';
                    isset($routeInfo['dst_latitude']) ? $dst_latitude = $routeInfo['dst_latitude'] : $dst_latitude = '';
                    isset($routeInfo['dst_longitude']) ? $dst_longitude = $routeInfo['dst_longitude'] : $dst_longitude = '';
                    isset($routeInfo['dst_municipality']) ? $dst_municipality = $routeInfo['dst_municipality'] : $dst_municipality = '';

                    // Set default values for these as it's a new entry, the low's and hi's will be the original values
                    $ac_longest = $ac_dist;
                    $ac_shortest = $ac_dist;
                    $ac_lowest = $ac_altitude;
                    $ac_highest = $ac_altitude;
                    $ac_slowest = $ac_speed;
                    $ac_fastest = $ac_speed;
                    $ac_ws_low = $ac_ws;
                    $ac_ws_high = $ac_ws;
                    $ac_roll_left = $ac_roll;
                    $ac_roll_right = $ac_roll;
                    
                    echo $ac_hex." -- ".$callsign." -- ".$ac_route." -- ".$ac_src." --> ".$ac_dst."\n";

                    // Add values to the array. The array is used to see if the flight is new on the next loop
                    $new_flight = array("hex" => $ac_hex, 
                        "callsign" => $callsign, 
                        "route" => $ac_route, 
                        "src" => $ac_src, 
                        "dst" => $ac_dst, 
                        "longest" => $ac_longest, 
                        "shortest" => $ac_shortest, 
                        "lowest" => $ac_lowest, 
                        "highest" => $ac_highest, 
                        "slowest" => $ac_slowest, 
                        "fastest" => $ac_fastest, 
                        "time" => $ac_now, 
                        "dbFlags" => $ac_dbFlags, 
                        "ws" => $ac_ws, 
                        "ws_low" => $ac_ws_low,
                        "ws_high" => $ac_ws_high,
                        "oat" => $ac_oat, 
                        "tat" => $ac_tat, 
                        "roll" => $ac_roll, 
                        "roll_left" => $ac_roll_left,
                        "roll_right" => $ac_roll_right,
                        "messages" => $ac_messages
                    );
                    $flight_array[] = $new_flight;
                    
                    // break;
                }

                // Add to Fresh Array to remove stale entries, only fresh entries are kept in the array.
                $new_flight = array("hex" => $ac_hex, 
                    "callsign" => $callsign, 
                    "route" => $ac_route, 
                    "src" => $ac_src, 
                    "dst" => $ac_dst, 
                    "longest" => $ac_longest, 
                    "shortest" => $ac_shortest, 
                    "lowest" => $ac_lowest, 
                    "highest" => $ac_highest, 
                    "slowest" => $ac_slowest, 
                    "fastest" => $ac_fastest, 
                    "time" => $ac_now, 
                    "dbFlags" => $ac_dbFlags, 
                    "ws" => $ac_ws,
                    "ws_low" => $ac_ws_low,
                    "ws_high" => $ac_ws_high, 
                    "oat" => $ac_oat, 
                    "tat" => $ac_tat, 
                    "roll" => $ac_roll,
                    "roll_left" => $ac_roll_left,
                    "roll_right" => $ac_roll_right, 
                    "messages" => $ac_messages
                );
                $current_flights[] = $new_flight;


                
                if ($insert_db === true ) {
                    $sql .= "INSERT INTO flights (id, message_date, now, hex, flight, reg, route, src, dst, src_country, dst_country, distance, shortest_distance, largest_distance, altitude, 
                        lowest, highest, lat, lon, track, speed, slowest, fastest, vert_rate, category, squawk, messages, flags, ws, ws_low, ws_high, oat, tat, roll, roll_left, roll_right) ";
                    $sql .= "VALUES (NULL, '" . date("Y-m-d H:i:s", $ac_now) . "', '$ac_now', '$ac_hex', '$ac_flight', '$ac_reg', '$ac_route', '$ac_src', '$ac_dst', '$ac_src_country', '$ac_dst_country', '$ac_dist', '$ac_shortest', '$ac_longest', ";
                    $sql .= "'$ac_altitude', '$ac_lowest', '$ac_highest', '$ac_lat', '$ac_lon', '$ac_track', '$ac_speed', '$ac_slowest', '$ac_fastest', '$ac_vert_rate', ";
                    $sql .= "'$ac_category', '$ac_squawk', '$ac_messages', '$ac_dbFlags', '$ac_ws', '$ac_ws_low','$ac_ws_high', '$ac_oat', '$ac_tat', '$ac_roll', '$ac_roll_left', '$ac_roll_right');";
                    $sql .= PHP_EOL;
                    $x++;
                } else if ($update_db === true ) {
                    $sql .= "UPDATE flights SET shortest_distance='$ac_shortest', largest_distance='$ac_longest', lowest='$ac_lowest', highest='$ac_highest', 
                                slowest='$ac_slowest', fastest='$ac_fastest', messages='$ac_messages', ws_low='$ac_ws_low', ws_high='$ac_ws_high', roll_left='$ac_roll_left', roll_right='$ac_roll_right' 
                            WHERE flight='$ac_flight' AND hex='$ac_hex' AND now>($ac_now - 1200 );";
                    $sql .= PHP_EOL;
                }


                // Now check if the airport information exists for this new flight
                try {
                    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']); $db_insert = '';
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    if (!checkAirportExists($db, $src_iata_code)) {
                        // Prepare and execute the INSERT statement for source airport
                        $stmt = $db->prepare("INSERT INTO airports (country_iso_name, country_name, elevation, iata_code, icao_code, latitude, longitude, municipality, name) 
                                VALUES ('$ac_src_country', '$src_country_name', '$src_elevation', '$src_iata_code', '$src_icao_code', '$src_latitude', '$src_longitude', '$src_municipality', '$ac_src' )");
                        $stmt->execute();
                    }
                    
                    if (!checkAirportExists($db, $dst_iata_code)) {
                        // Prepare and execute the INSERT statement for destination airport
                        $stmt = $db->prepare("INSERT INTO airports (country_iso_name, country_name, elevation, iata_code, icao_code, latitude, longitude, municipality, name) 
                                VALUES ('$ac_dst_country', '$dst_country_name', '$dst_elevation', '$dst_iata_code', '$dst_icao_code', '$dst_latitude', '$dst_longitude', '$dst_municipality', '$ac_dst' )");
                        $stmt->execute();
                    }
                    
                } catch (PDOException $db_error) {
                    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
                }
            

            }

        // if db connection is ok write selected aircraft data to database
        try {
                $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']); $db_insert = '';
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                if ($sql) { $db->exec($sql); $db_insert = 'inserted'; }
                $db = null;
        } catch (PDOException $db_error) {
                $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
        }

        // generate terminal output and set sleep timer to get minimum a full second until next aircraft.json is ready to get fetched
        $runtime = (time() - $start_time);
        $runtime_formatted = sprintf('%d days %02d:%02d:%02d', $runtime/60/60/24,($runtime/60/60)%24,($runtime/60)%60,$runtime%60);
        ($runtime > 0) ? $loop_clock = number_format(round(($i / $runtime),6),6) : $loop_clock = number_format(1, 6);
        $process_microtime = (round(1000000 * (microtime(true) - $start_loop_microtime)));
        print('upt(us): ' . sprintf('%07d', $process_microtime) . ' - ' . $loop_clock . ' loops/s avg - since ' . $runtime_formatted . ' - run ' . $i . ' @ ' . number_format($message_rate, '1', ',', '.') . ' msg/s -> ' . sprintf('%03d', $x) . ' dataset(s) => ' . $db_insert . PHP_EOL);
        echo "\n \n";
        $flight_array = $current_flights;
        $i++;
                
        // Run maintenance script when needed
        if (time() - $maintenance_clock > $user_set_array['maintenance'] OR $i == 1 ){
            echo "\n\n\n Maintenance Starting \n";
            $maintenance_start = time();
            include('maintenance.php');
            $maintenance_stop = time();
            $maintenance_time = $maintenance_stop - $maintenance_start;
            echo "\n Maintenance Complete in ". $maintenance_time . " seconds \n\n\n";

            $maintenance_clock = time(); // Reset the clock to the current time
        }else{
            sleep($user_set_array['sleep']);
        }

    }

?>