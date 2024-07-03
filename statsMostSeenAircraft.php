<?php 
// Get DB information
include_once('dbconnect.php');

// Create a new table showing the most popular aircraft ?>
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

    // Prepare the SQL query
    $stmt = $db->prepare('SELECT type, manufacturer, mode_s, registration, registered_owner_country_name, registered_owner, url_photo, url_photo_thumbnail, seen, first_seen FROM aircraft ORDER BY seen DESC');

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td><?php echo "<img src=\"{$result['url_photo_thumbnail']}\">"; ?></td>
            <td><?php echo "{$result['type']}"; ?></td>
            <td><?php echo "{$result['manufacturer']}"; ?></td>
            <td><?php echo "{$result['mode_s']}"; ?></td> 
            <td><?php echo "{$result['registration']}"; ?></td>
            <td><?php echo "{$result['registered_owner_country_name']}"; ?></td>
            <td><?php echo "{$result['registered_owner']}"; ?></td>
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