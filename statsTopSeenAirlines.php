<?php 
// Get DB information
include_once('dbconnect.php');

$sortBy = $_GET["sortBy"];
$sortDirection = $_GET["sortDirection"];
$arrow = $_GET["arrow"];

// Create a new table showing the most popular flights ?>
<table>
    <tr>
        <th></th>
        
        <th>
            Airline
            <span class="arrow-container">
                <span id="arrow-down" class="arrow">
                    <a href="#airlines" <?php if($arrow == 'airline_down'){echo 'class="arrow-link active"';}else{echo "class=arrow-link";} ?> onclick="sortArray('/statsTopSeenAirlines.php?sortBy=owner_name&sortDirection=DESC&arrow=airline_down'); return false;">&#9660;</a>
                </span>
                <span id="arrow-up" class="arrow">
                    <a href="#airlines" <?php if($arrow == 'airline_up'){echo 'class="arrow-link active"';}else{echo "class=arrow-link";} ?> onclick="sortArray('/statsTopSeenAirlines.php?sortBy=owner_name&sortDirection=ASC&arrow=airline_up'); return false;">&#9650;</a>
                </span>
            </span>
        </th>
        
        <th>Country</th>
        
        <th>
            Flights Seen
            <span class="arrow-container">
                <span id="arrow-down" class="arrow">
                    <a href="#airlines" <?php if($arrow == 'total_down'){echo 'class="arrow-link active"';}else{echo "class=arrow-link";} ?> onclick="sortArray('/statsTopSeenAirlines.php?sortBy=total_seen&sortDirection=DESC&arrow=total_down'); return false;">&#9660;</a>
                </span>
                <span id="arrow-up" class="arrow">
                    <a href="#airlines" <?php if($arrow == 'total_up'){echo 'class="arrow-link active"';}else{echo "class=arrow-link";} ?> onclick="sortArray('/statsTopSeenAirlines.php?sortBy=total_seen&sortDirection=ASC&arrow=total_up'); return false;">&#9650;</a>
                </span>
            </span>
        </th>

        <th>
            Aircraft Seen
            <span class="arrow-container">
                <span id="arrow-down" class="arrow">
                    <a href="#airlines" <?php if($arrow == 'fleet_down'){echo 'class="arrow-link active"';}else{echo "class=arrow-link";} ?> onclick="sortArray('/statsTopSeenAirlines.php?sortBy=total_aircraft&sortDirection=DESC&arrow=fleet_down'); return false;">&#9660;</a>
                </span>
                <span id="arrow-up" class="arrow">
                    <a href="#airlines" <?php if($arrow == 'fleet_up'){echo 'class="arrow-link active"';}else{echo "class=arrow-link";} ?> onclick="sortArray('/statsTopSeenAirlines.php?sortBy=total_aircraft&sortDirection=ASC&arrow=fleet_up'); return false;">&#9650;</a>
                </span>
            </span>
        </th>
    </tr>

<?php

$airline_array = array();

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query
    $stmt = $db->prepare("SELECT registered_owner, registered_owner_country_iso_name, registered_owner_country_name FROM aircraft WHERE registered_owner <> '' GROUP BY registered_owner");

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    foreach ($results as $result) {
        $owner_name = addslashes($result['registered_owner']);
        
        $stmt2 = $db->prepare("SELECT seen FROM aircraft WHERE registered_owner = '$owner_name' ");
        $stmt2->execute();
        $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        $total_seen = 0;
        $total_aircraft =0;

        // Loop through each aircraft to total up the seen counts
        foreach ($results2 as $result2) {
            $seen = $result2['seen'];
            $total_seen = $total_seen + $seen;
            $total_aircraft++;
        }

        $country_iso = $result['registered_owner_country_iso_name'];
        $country_name = $result['registered_owner_country_name'];
    
        // Add values to the array
        $new_airline = array("country_iso" => $country_iso, "owner_name" => $owner_name, "country_name" => $country_name, "total_seen" => $total_seen, "total_aircraft" => $total_aircraft);
        $airline_array[] = $new_airline;

    }

    // Sort the Array based on the colum defined in $sortArray    
    $key_values = array_column($airline_array, $sortBy);
    if($sortDirection == "DESC"){
        array_multisort($key_values, SORT_DESC, $airline_array);
    }else{
        array_multisort($key_values, SORT_ASC, $airline_array);
    }

    // Loop through the Array to show the results
    foreach ($airline_array as $airline_info) {
        ?><tr>
            <td><?php echo "<img src=\"https://flagcdn.com/48x36/" . strtolower($airline_info['country_iso']) . ".png\">"; ?></td>
            <td><?php echo $airline_info['owner_name']; ?></td>
            <td><?php echo $airline_info['country_name']; ?></td>
            <td><?php echo $airline_info['total_seen']; ?></td>
            <td><a href="#airlines" onclick="sortArray('/statsAirlineFleet.php?airline=<?php echo $airline_info['owner_name']; ?>&country=<?php echo $airline_info['country_name']; ?>&flights=<?php echo $airline_info['total_seen']; ?>&page=Airlines'); return false;"><?php echo $airline_info['total_aircraft']; ?></a></td>
        </tr>
        <?php

    }


} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>