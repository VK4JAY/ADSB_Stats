<?php 
// Get DB information
include_once('dbconnect.php');

// Create a new table showing the most popular flights ?>
<table>
    <tr>
        <th></th>
        <th>Type</th>
        <th>Manufacturer</th>
        <th>Seen</th>
    </tr>

<?php
    

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query
    $stmt = $db->prepare("SELECT icao_type, manufacturer, COUNT(*) AS count FROM aircraft WHERE icao_type <> '' GROUP BY icao_type ORDER BY count DESC");

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td></td>
            <td><?php echo "{$result['icao_type']}"; ?></td>
            <td><?php echo "{$result['manufacturer']}"; ?></td>
            <td><?php echo "{$result['count']}"; ?></td> 
            
        </tr>
        <?php
    }
} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>