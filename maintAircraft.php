<?php

// Connect to DB
include_once('dbconnect.php');
// Load Functions
include_once('functions.php');

$old=0;
$new=0;

// Get all unique registrations from the database
$db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "SELECT DISTINCT reg, COUNT(*) as count, hex FROM flights GROUP BY reg ORDER BY count DESC";
$stmt = $db->query($sql);
while ($row = $stmt->fetch()) {
    $callsign = $row[0];
    $seen = $row[1];
    
    // Check if we already have this aircraft
    $sql2 = "SELECT COUNT(*) FROM aircraft WHERE registration='$row[0]'";
    $stmt2 = $db->query($sql2);
    $count = $stmt2->fetchColumn();
    
    if($count == 0){

        // Maintain Rate Limits from adsbdb.com
        if (is_int($new / 500) && $new != 0){
            sleep(60);
        }
        
        // Get plane information
        $planeInfo = getPlaneInfo($callsign);
        if ($planeInfo !== null) {
            $type = addslashes($planeInfo['type']);
            $icao_type = $planeInfo['icao_type'];
            $manufacturer = $planeInfo['manufacturer'];
            $mode_s = $planeInfo['mode_s'];
            $registration = $planeInfo['registration'];
            $registered_owner_country_iso_name  = $planeInfo['registered_owner_country_iso_name'];
            $registered_owner_country_name = $planeInfo['registered_owner_country_name'];
            $registered_owner_operator_flag_code = $planeInfo['registered_owner_operator_flag_code'];
            $registered_owner = addslashes($planeInfo['registered_owner']);
            $url_photo = $planeInfo['url_photo'];
            $url_photo_thumbnail = $planeInfo['url_photo_thumbnail' ];

            // Get first seen date
            $sql3 = "SELECT message_date FROM flights WHERE reg='$callsign' GROUP BY message_date ORDER BY message_date ASC LIMIT 1";
            $stmt3 = $db->query($sql3);
            $first_seen = $stmt3->fetchColumn();

            // Create SQL INSERT command
            $sql2 = "INSERT INTO aircraft (type, icao_type, manufacturer, mode_s, registration, registered_owner_country_iso_name, registered_owner_country_name, registered_owner_operator_flag_code, registered_owner, url_photo, url_photo_thumbnail, seen, first_seen ) ";
            $sql2 .= "VALUES ('$type', '$icao_type', '$manufacturer', '$mode_s', '$registration', '$registered_owner_country_iso_name', '$registered_owner_country_name', ";
            $sql2 .= "'$registered_owner_operator_flag_code', '$registered_owner', '$url_photo', '$url_photo_thumbnail', '$seen', '$first_seen'); ";
            
            try {
                $db->prepare($sql2);
                $db->exec($sql2);
            } catch (PDOException $db_error) {
                $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
            }
            $new++;

        }else{ // Add the blank entry to the DB, but mark it as unresolved
            // Get first seen date
            $sql3 = "SELECT message_date FROM flights WHERE reg='$callsign' GROUP BY message_date ORDER BY message_date ASC LIMIT 1";
            $stmt3 = $db->query($sql3);
            $first_seen = $stmt3->fetchColumn();

            // Create SQL INSERT command
            $hex = $row[2];
            $sql2 = "INSERT INTO aircraft (mode_s, registration, seen, first_seen, resolved ) ";
            $sql2 .= "VALUES ('$hex', '$callsign', '$seen', '$first_seen', 'NO' ); ";
                        
            try {
                $db->exec($sql2);
            } catch (PDOException $db_error) {
                $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
            }
            $new++;
        }
    }else{

        // Create SQL UPDATE to update seen count
        $sql2 = "UPDATE aircraft SET seen='$seen' WHERE registration='$callsign'";
        
        try {
            $db->exec($sql2);
        } catch (PDOException $db_error) {
            $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
        }

        $old++;
    }
     
}

echo "\n New aircraft --> " . $new;
echo "\n Old aircraft --> " . $old;

?>