<?php 
// Get DB information
include_once('dbconnect.php');

$hex = $_GET["hex"];
$reg = $_GET["reg"];
$page = $_GET["page"];
$page = $_GET["page"];

if ($hex){
    $where = "hex";
    $value = $hex;
    
}else if ($reg){
    $where = "reg";
    $value = $reg;
}
    
$page = $page ." > " .$value;

?>
<h2><?php echo $page; ?></h2>
<?php

try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query for TOP 5 Flights
    $stmt = $db->prepare("SELECT message_date, flight, route, src, dst, COUNT(*) AS count FROM flights WHERE $where = '$value' GROUP BY route ORDER BY count DESC LIMIT 5");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create a new table showing all flights from this Aircraft 
    ?>
    <h2>TOP 5 flights</h2>
    <table>
    <tr>
        <th>Flight</th>
        <th>Route</th>
        <th>Source Airport</th>
        <th>Destination Airport</th>
        <th>Total</th>
    </tr>
    <?php

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td><?php echo "{$result['flight']}"; ?></td>
            <td><?php echo "{$result['route']}"; ?></td>
            <td><?php echo "{$result['src']}"; ?></td>
            <td><?php echo "{$result['dst']}"; ?></td> 
            <td><?php echo "{$result['count']}"; ?></td>
        </tr>
        <?php
    }
    ?></table><?php




    // Prepare the SQL query for ALL flights
    $stmt = $db->prepare("SELECT message_date, flight, route, src, dst FROM flights WHERE $where = '$value' ORDER BY message_date DESC");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    // Create a new table showing all flights from this Aircraft ?>
    <h2>All flights</h2>
    <table>
    <tr>
        <th>Date</th>
        <th>Flight</th>
        <th>Route</th>
        <th>Source Airport</th>
        <th>Destination Airport</th>
    </tr>
    <?php

    // Output the results
    foreach ($results as $result) {
        ?><tr>
            <td><?php echo "{$result['message_date']}"; ?></td>
            <td><?php echo "{$result['flight']}"; ?></td>
            <td><?php echo "{$result['route']}"; ?></td>
            <td><?php echo "{$result['src']}"; ?></td>
            <td><?php echo "{$result['dst']}"; ?></td> 
        </tr>
        <?php
    }
    ?></table><?php

} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}
?>
</table><br>