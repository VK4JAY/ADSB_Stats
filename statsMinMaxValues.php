<?php
// Get DB information
include_once('dbconnect.php');

function func_table_create($orderBy,$sort,$db) {
    $sql = "SELECT message_date, hex, flight, reg, route, src, dst, shortest_distance, largest_distance, lowest, highest, slowest, fastest FROM flights WHERE shortest_distance != '' ORDER BY $orderBy $sort LIMIT 1";
    $stmt = $db->query($sql);
    $flight = $stmt->fetch();
    
    $html = "
    <tr>   
        <td>$flight[0]</td>
        <td>$flight[1]</td>
        <td>$flight[2]</td>
        <td>$flight[3]</td>
        <td>$flight[4]</td>
        <td>$flight[5]</td>
        <td>$flight[6]</td>";
        if($orderBy == 'shortest_distance'){
            $html .= "<td bgcolor=\"green\">$flight[7]</td>";
        }else{
            $html .= "<td>$flight[7]</td>";
        }
        if($orderBy == 'largest_distance'){
            $html .= "<td bgcolor=\"green\">$flight[8]</td>";
        }else{
            $html .= "<td>$flight[8]</td>";
        }  
        if($orderBy == 'lowest'){
            $html .= "<td bgcolor=\"green\">$flight[9]</td>";
        }else{
            $html .= "<td>$flight[9]</td>";
        }
        if($orderBy == 'highest'){
            $html .= "<td bgcolor=\"green\">$flight[10]</td>";
        }else{
            $html .= "<td>$flight[10]</td>";
        }
        if($orderBy == 'slowest'){
            $html .= "<td bgcolor=\"green\">$flight[11]</td>";
        }else{
            $html .= "<td>$flight[11]</td>";
        }
        if($orderBy == 'fastest'){
            $html .= "<td bgcolor=\"green\">$flight[12]</td>";
        }else{
            $html .= "<td>$flight[12]</td>";
        }
    
    $html .= "        
    </tr>";
        
    return $html;
}
?>
<table>
    <tr>
        <th>Date</th>
        <th>Hex</th>
        <th>Flight</th>
        <th>Registration</th>
        <th>Route</th>
        <th>Source Airport</th>
        <th>Destination Airport</th>
        <th>Closest Distance</th>
        <th>Furthest Distance</th>
        <th>Lowest Altitude</th>
        <th>Highest Altitude</th>
        <th>Slowest</th>
        <th>Fastest</th>
    </tr>

<?php


try {
    $db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    
    // Closest Flight
    $orderBy = 'shortest_distance';
    $sort = "ASC";
    $table = func_table_create($orderBy,$sort,$db);
    echo $table;
      
    // Furthest Flight
    $orderBy = 'largest_distance';
    $sort = "DESC";
    $table = func_table_create($orderBy,$sort,$db);
    echo $table;

    // Fastest Lowest
    $orderBy = 'lowest';
    $sort = "ASC";
    $table = func_table_create($orderBy,$sort,$db);
    echo $table;
 
    // Fastest Highest
    $orderBy = 'highest';
    $sort = "DESC";
    $table = func_table_create($orderBy,$sort,$db);
    echo $table;

    // Fastest Slowest
    $orderBy = 'slowest';
    $sort = "ASC";
    $table = func_table_create($orderBy,$sort,$db);
    echo $table;
 
    // Fastest Fastest
    $orderBy = 'fastest';
    $sort = "DESC";
    $table = func_table_create($orderBy,$sort,$db);
    echo $table;
 
    // Highest Wind (maybe)
    
    
} catch (PDOException $db_error) {
    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
}

?>
</table>