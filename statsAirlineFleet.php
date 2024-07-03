<?php 
// Get DB information
include_once('dbconnect.php');

$airline = $_GET["airline"];
$country = $_GET["country"];
$flights = $_GET["flights"];
$page = $_GET["page"] ." > " .$airline;

?>
<h2><?php echo $page; ?></h2>
<?php
try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    /* Query to count unique icao_type */
    $countStmt = $db->prepare("
        SELECT icao_type, COUNT(*) as count
        FROM aircraft
        WHERE registered_owner = :airline
        GROUP BY icao_type
        ORDER BY count DESC
    ");

    // Bind the airline parameter
    $countStmt->bindParam(':airline', $airline, PDO::PARAM_STR);
    $countStmt->execute();


    /* Second Query to retrieve all other details */
    $stmt = $db->prepare("
        SELECT type, icao_type, manufacturer, mode_s, registration, registered_owner_country_name, registered_owner, url_photo, url_photo_thumbnail, seen, first_seen 
        FROM aircraft 
        WHERE registered_owner = '$airline' 
        ORDER BY seen DESC
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    ?>
    <table>
        <th></th>
        <th></th>
        <tr>
            <td>Airline</td>
            <td><?php echo $airline; ?></td>
        </tr>
        <tr>
            <td>Country</td>
            <td><?php echo $country; ?></td>
        </tr>
        <tr>
            <td>Flights</td>
            <td><?php echo $flights; ?></td>
        </tr>
        <tr>
            <td>Aircraft Types:</td>
            <td></td>
        </tr>
    <?php
        
        
        // Fetch and display the counts of unique icao_type
        $total_count = 0;
        while ($row = $countStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "
                <tr>
                    <td style=\"padding-left: 4em;\">" . $row['icao_type'] . "</td>
                    <td>" . $row['count'] . "</td>
                </tr>";
            $total_count = $total_count + $row['count'];    
        }

    ?>
        <tr>
            <td>Total Aircraft</td>
            <td><?php echo $total_count; ?></td>
        </tr>
    </table>
    <?php


    ?>
    <table>
    <tr>
        <th></th>
        <th>Type</th>
        <th>ICAO Tye</th>
        <th>Manufacturer</th>
        <th>Hex</th>
        <th>Registration</th>
        <th>Seen</th>
        <th>First Seen</th>
    </tr>
    <?php



    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td><?php echo "<img src=\"{$result['url_photo_thumbnail']}\">"; ?></td>
            <td><?php echo "{$result['type']}"; ?></td>
            <td><?php echo "{$result['icao_type']}"; ?></td>
            <td><?php echo "{$result['manufacturer']}"; ?></td>
            <td><a href="#airlines" onclick='sortArray("/statsHexReg.php?hex=<?php echo $result['mode_s']; ?>&page=<?php echo $page; ?>"); return false;'><?php echo "{$result['mode_s']}"; ?></a></td>
             <td><a href="#airlines" onclick='sortArray("/statsHexReg.php?reg=<?php echo $result['registration']; ?>&page=<?php echo $page; ?>"); return false;'><?php echo "{$result['registration']}"; ?></td>
            <td><?php echo "{$result['seen']}"; ?></td>
            <td><?php echo "{$result['first_seen']}"; ?></td>
        </tr>
        <?php

}

} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>