<?php
// Get DB information
include_once('dbconnect.php');


?>
<div id="table" style="display: inline-block; width: 500px; position: relative; left: 50%; transform: translateX(-50%); " >
<table>
    <tr>
        <th></th>
        <th>Total</th>
    </tr>

<?php

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Prepare the SQL query
    $sql = "
    SELECT 
        (SELECT COUNT(*) FROM flights) AS total_flights,
        (SELECT COUNT(DISTINCT route) FROM flights) AS total_unique_routes,
        (SELECT COUNT(*) FROM aircraft) AS total_aircraft,
        (SELECT COUNT(*) FROM flights WHERE flags = 1) AS military_aircraft,
        (SELECT COUNT(DISTINCT airport) 
        FROM (
            SELECT src AS airport FROM flights
            UNION
            SELECT dst AS airport FROM flights
        ) AS unique_airports) AS total_unique_airports,
        (SELECT COUNT(DISTINCT country) 
        FROM (
            SELECT src_country AS country FROM flights
            UNION
            SELECT dst_country AS country FROM flights
        ) AS unique_countries) AS total_unique_countries;
    ";

    // Execute the query
    $stmt = $db->query($sql);

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td>Flights</td>
            <td><?php echo number_format($result['total_flights']); ?></td>
        </tr>
        <tr>
            <td>Routes</td>
            <td><?php echo number_format($result['total_unique_routes']); ?></td>
        </tr>
        <tr>
            <td>Aircraft</td>
            <td><?php echo number_format($result['total_aircraft']); ?></td>
        </tr>
        <tr>
            <td>Military Aircraft</td>
            <td><?php echo number_format($result['military_aircraft']); ?></td>
        </tr>
        <tr>
            <td>Airports</td>
            <td><?php echo number_format($result['total_unique_airports']); ?></td> 
        </tr>
        <tr>
            <td>Countries</td>        
            <td><?php echo number_format($result['total_unique_countries']); ?></td>
        </tr>

        <?php
    }
} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table></div><br>


<?php

include('statsLast7days.php');
echo "<br>";
echo "placeholder starts here";
include('mapsDomestic.php');
echo "<br> placholder ends here";

?>


<iframe id="included-iframe" src="mapsDomestic.php" style="width: 100%; height: 80vh; border: none;"></iframe>
<iframe id="included-iframe" src="mapsInternational.php" style="width: 100%; height: 80vh; border: none;"></iframe>