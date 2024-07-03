<?php 
$user_set_array['db_name'] = 'adsb'; $user_set_array['db_host'] = '192.168.0.2'; $user_set_array['db_user'] = 'root'; $user_set_array['db_pass'] = '73Cr3XqZ7yQZ';

// Create a new table showing the most popular flights ?>
<table>
    <tr>
        <th>Flag</th>
        <th>Source Country</th>
        <th>Flights Seen</th>
    </tr>

<?php
    

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the SQL query
    $stmt = $db->prepare("SELECT src_country, COUNT(*) AS count FROM flights WHERE src_country<>'' GROUP BY src_country ORDER BY count DESC");

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td><?php echo "<img src=\"https://flagcdn.com/48x36/" . strtolower($result['src_country']) . ".png\">"; ?></td>
            <td><?php echo "{$result['src_country']}"; ?></td>
            <td><?php echo "{$result['count']}"; ?></td> 
            
        </tr>
        <?php
    }

} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>

<?php
// Create a new table showing the most popular flights ?>
<table>
    <tr>
        <th>Flag</th>
        <th>Destination Country</th>
        <th>Flights Seen</th>
    </tr>

<?php
    

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the SQL query
    $stmt = $db->prepare("SELECT dst_country, COUNT(*) AS count FROM flights WHERE dst_country<>'' GROUP BY dst_country ORDER BY count DESC");

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td><?php echo "<img src=\"https://flagcdn.com/48x36/" . strtolower($result['dst_country']) . ".png\">"; ?></td>
            <td><?php echo "{$result['dst_country']}"; ?></td>
            <td><?php echo "{$result['count']}"; ?></td> 
            
        </tr>
        <?php
    }

} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>