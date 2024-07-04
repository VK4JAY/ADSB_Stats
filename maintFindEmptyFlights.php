<?php
// Get DB information
include_once('dbconnect.php');


try {
    // Connect to the database
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $x=1;

    while ($x == 1){
        
        // Find flights that don't have a route
        $stmt = $db->prepare("SELECT id, flight FROM flights WHERE route='' OR route IS NULL");
        $stmt->execute();
        $ids = $stmt->fetchAll();

        if (!empty($ids)) {
            foreach ($ids as $flight_details) {
                

                $flight = $flight_details['flight'];
                $id = $flight_details['id'];

                // Search for another flight that is the same
                $stmt_search = $db->prepare("SELECT route, src, dst, src_country, dst_country FROM flights WHERE flight = '$flight' AND id<>'$id' LIMIT 1");
                $stmt_search->execute();
                $match = $stmt_search->fetch();

                if (!empty($match)){
                    // Update the entry that is missing the route details
                    $route = $match[0];
                    $src = $match[1];
                    $dst = $match[2];
                    $src_country = $match[3];
                    $dst_country = $match[4];
                                                
                    $update = $db->prepare("UPDATE flights SET route='$route', src='$src', dst='$dst', src_country='$src_country', dst_country='$dst_country' WHERE id='$id'");
                    $update->execute();
                }
                $x = 0;
            }
            
        } else {
            echo "No empty entries found. \n";
            $x = 0;
        }
    }
}catch (PDOException $e) {
    // If an error occurs, display it
    echo 'Connection failed: ' . $e->getMessage();
}
echo "\n Missing Route details updated";
?>