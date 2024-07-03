<?php 
// Get DB information
include_once('dbconnect.php');

// Create a new table showing the most popular flights ?>
<table>
    <tr>
        <th>Route</th>
        <th>Source Airport</th>
        <th>Destination Airport</th>
        <th>Flight Count</th>
    </tr>

<?php
    

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query
    $stmt = $db->prepare("SELECT route, src, dst, COUNT(*) AS count FROM flights WHERE src_country='AU' AND dst_country='AU' GROUP BY route ORDER BY count DESC");

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td><?php echo "{$result['route']}"; ?></td>
            <td><?php echo "{$result['src']}"; ?></td>
            <td><?php echo "{$result['dst']}"; ?></td> 
            <td><?php echo "{$result['count']}"; ?></td>
        </tr>
        <?php
    }
} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>