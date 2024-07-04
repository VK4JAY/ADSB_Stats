<?php
// Get DB information
include_once('dbconnect.php');


try {
    // Connect to the database
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $start = time();
    $current_time = time() - 3600; // 1 hour.
       
    // Find flights where the flight is null, but there is another flight with same HEX within 20 minutes that does have information
        // Find ALL flights where the flight column is null
        $stmt = $db->prepare("SELECT id, now, hex, lowest, highest, shortest_distance, largest_distance, slowest, fastest, ws_low, ws_high, roll_left, roll_right FROM flights WHERE flight = '' OR flight IS NULL AND now > $current_time ");
        $stmt ->execute();
        
        // Loop through all empty flights
        while ($row = $stmt->fetch()) {
            $id = $row[0];
            $now = $row[1];
            $hex = $row[2];
            $lowest = $row[3];
            $highest = $row[4];
            $shortest = $row[5];
            $longest = $row[6];
            $slowest = $row[7];
            $fastest = $row[8];
            $ws_low = $row[9];
            $ws_high = $row[10];
            $roll_left = $row[11];
            $roll_right = $row[12];


            // Find a flight within the last 20 minutes that has the same hex, but DOES have flight details
            $stmt2 = $db->prepare("SELECT id, now, hex, lowest, highest, shortest_distance, largest_distance, slowest, fastest, ws_low, ws_high, roll_left, roll_right FROM flights WHERE hex='$hex' AND flight<>'' AND now BETWEEN $now - 1200 AND $now + 1200 LIMIT 1 ");
            $stmt2 ->execute();
            $matching_flights = $stmt2->fetch();

            if (!empty($matching_flights)) {
                $id2 = $matching_flights[0];
                $now2 = $matching_flights[1];
                $hex2 = $matching_flights[2];
                $lowest2 = $matching_flights[3];
                $highest2 = $matching_flights[4];
                $shortest2 = $matching_flights[5];
                $longest2 = $matching_flights[6];
                $slowest2 = $matching_flights[7];
                $fastest2 = $matching_flights[8];
                $ws_low2 = $matching_flights[9];
                $ws_high2 = $matching_flights[10];
                $roll_left2 = $matching_flights[11];
                $roll_right2 = $matching_flights[12];

                if ($lowest < $lowest2){ $lowest2 = $lowest; }
                if ($highest > $highest2){ $highest2 = $highest; }
                if ($shortest < $shortest2){ $shortest2 = $shortest; }
                if ($longest > $longest2){ $longest2 = $longest; }
                if ($slowest < $slowest2){ $slowest2 = $slowest; }
                if ($fastest > $fastest2){ $fastest2 = $fastest; }
                if ($ws_low < $ws_low2){ $ws_low2 = $ws_low; }
                if ($ws_high > $ws_high2){ $ws_high2 = $ws_high; }
                if ($roll_left < $roll_left2){ $roll_left2 = $roll_left; }
                if ($roll_right > $roll_right2){ $roll_right2 = $roll_right; }


                // Delete empty value
                $stmt_delete = $db->prepare("DELETE FROM flights WHERE id='$id' ");
                $stmt_delete->execute();

                // Update stats on full entry
                $stmt_update = $db->prepare("UPDATE flights SET shortest_distance='$shortest2', largest_distance='$longest2', lowest='$lowest2', highest='$highest2', slowest='$slowest2', fastest='$fastest2', ws_low='$ws_low2', ws_high='$ws_high', roll_left='$roll_left', roll_right='$roll_right' WHERE id='$id2' ");
                $stmt_update->execute();
            }
        }    
    

// Find flights where the flight is not NULL , but there is duplicate flight within 20 minutes
        // Find ALL flights
        $stmt = $db->prepare("SELECT id, now, hex, lowest, highest, shortest_distance, largest_distance, slowest, fastest, ws_low, ws_high, roll_left, roll_right FROM flights WHERE flight <> '' AND now > $current_time ");
        $stmt ->execute();
        
        // Loop through all flights
        while ($row = $stmt->fetch()) {
            $id = $row[0];
            $now = $row[1];
            $hex = $row[2];
            $lowest = $row[3];
            $highest = $row[4];
            $shortest = $row[5];
            $longest = $row[6];
            $slowest = $row[7];
            $fastest = $row[8];
            $ws_low = $row[9];
            $ws_high = $row[10];
            $roll_left = $row[11];
            $roll_right = $row[12];


            // Find a flight within the last 20 minutes that has the same hex
            $stmt2 = $db->prepare("SELECT id, now, hex, lowest, highest, shortest_distance, largest_distance, slowest, fastest, ws_low, ws_high, roll_left, roll_right FROM flights WHERE hex='$hex' AND flight<>'' AND id<>'$id' AND now BETWEEN $now - 1200 AND $now + 1200 LIMIT 1 ");
            $stmt2 ->execute();
            $matching_flights = $stmt2->fetch();

            if (!empty($matching_flights)) {
                $id2 = $matching_flights[0];
                $now2 = $matching_flights[1];
                $hex2 = $matching_flights[2];
                $lowest2 = $matching_flights[3];
                $highest2 = $matching_flights[4];
                $shortest2 = $matching_flights[5];
                $longest2 = $matching_flights[6];
                $slowest2 = $matching_flights[7];
                $fastest2 = $matching_flights[8];
                $ws_low2 = $matching_flights[9];
                $ws_high2 = $matching_flights[10];
                $roll_left2 = $matching_flights[11];
                $roll_right2 = $matching_flights[12];

                if ($lowest < $lowest2){ $lowest2 = $lowest; }
                if ($highest > $highest2){ $highest2 = $highest; }
                if ($shortest < $shortest2){ $shortest2 = $shortest; }
                if ($longest > $longest2){ $longest2 = $longest; }
                if ($slowest < $slowest2){ $slowest2 = $slowest; }
                if ($fastest > $fastest2){ $fastest2 = $fastest; }
                if ($ws_low < $ws_low2){ $ws_low2 = $ws_low; }
                if ($ws_high > $ws_high2){ $ws_high2 = $ws_high; }
                if ($roll_left < $roll_left2){ $roll_left2 = $roll_left; }
                if ($roll_right > $roll_right2){ $roll_right2 = $roll_right; }

                // Delete empty value
                $stmt_delete = $db->prepare("DELETE FROM flights WHERE id='$id' ");
                $stmt_delete->execute();

                // Update stats on full entry
                $stmt_update = $db->prepare("UPDATE flights SET shortest_distance='$shortest2', largest_distance='$longest2', lowest='$lowest2', highest='$highest2', slowest='$slowest2', fastest='$fastest2', ws_low='$ws_low2', ws_high='$ws_high', roll_left='$roll_left', roll_right='$roll_right' WHERE id='$id2' ");
                $stmt_update->execute();

                //echo $id . "-->" . $id2 . "<br>";
            }
        }


        
    // Fix DateTime entries (This looks for any entries that don't have the DateTime set and sets it based on the NOW value)
    // This is to fix a bug that has now been resolved, but any older versions might still need the updated db entries
    // Prepare the SQL query
    $stmt = $db->prepare('SELECT * FROM flights WHERE message_date = 0');

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    if (!empty($results)) {
        echo "Entries found:<br>";
        foreach ($results as $result) {
            
            // Set date from the now column (unix timestamp)
            $date = date("Y-m-d H:i:s", $result['now']);
            $id = $result['id'];
            ////print_r($result);

            // Update the Database
            $sql = "UPDATE flights SET message_date='$date' WHERE id='$id';";
            $db->exec($sql);
            
            
        }
    }


} catch (PDOException $e) {
    // If an error occurs, display it
    echo 'Connection failed: ' . $e->getMessage();
}

$end = time();
$duration = $end - $start;
echo "\n DB Cleaned - Time to clean --> ". $duration;
?>