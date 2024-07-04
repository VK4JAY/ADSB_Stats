<?php 
// Get DB information
include_once('dbconnect.php');

// Create a new table showing the most popular military aircraft ?>
<table>
    <tr>
        <th></th>
        <th>Type</th>
        <th>Manufacturer</th>
        <th>Hex</th>
        <th>Registration</th>
        <th>Registered Country</th>
        <th>Registered Owner</th>
        <th>Seen</th>
        <th>First Seen</th>
    </tr>
<?php

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 1: Query the flights table
    $query = "
        SELECT hex, message_date
        FROM flights
        WHERE flags = 1
        GROUP BY hex
    ";
    $stmt = $db->query($query);
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare an array to store results
    $planes = [];

    // Step 2: Query the aircraft table for each plane
    foreach ($flights as $flight) {
        $hex = $flight['hex'];
        
        $aircraft_query = "
            SELECT type, manufacturer, mode_s, registration, registered_owner_country_name, registered_owner, url_photo_thumbnail, seen, first_seen
            FROM aircraft
            WHERE mode_s = :hex
        ";
        $stmt = $db->prepare($aircraft_query);
        $stmt->execute(['hex' => $hex]);
        $aircraft = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($aircraft) {
            $planes[] = $aircraft;
        }
    }

    // Sort the Array based on the column defined in $sortArray
    $sortBy = 'seen';
    $sortDirection = 'DESC';    
    $key_values = array_column($planes, $sortBy);
    if($sortDirection == "DESC"){
        array_multisort($key_values, SORT_DESC, $planes);
    }else{
        array_multisort($key_values, SORT_ASC, $planes);
    }

    foreach ($planes as $plane) {
        echo "<tr>
                <td><img src=\"{$plane['url_photo_thumbnail']}\"></td>
                <td>{$plane['type']}</td>
                <td>{$plane['manufacturer']}</td>
                <td>{$plane['mode_s']}</td>
                <td>{$plane['registration']}</td>
                <td>{$plane['registered_owner_country_name']}</td>
                <td>{$plane['registered_owner']}</td>
                <td>{$plane['seen']}</td>
                <td>{$plane['first_seen']}</td>
              </tr>";
    }


} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>