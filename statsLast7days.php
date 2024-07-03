<?php
// Get DB information
include_once('dbconnect.php');

// Create connection
$db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$sql = "
SELECT
    DATE(message_date) AS flight_day,
    COUNT(*) AS total_flights,
    SUM(CASE WHEN src_country = 'AU' AND dst_country = 'AU' THEN 1 ELSE 0 END) AS domestic_flights,
    SUM(CASE WHEN src_country != 'AU' OR dst_country != 'AU' THEN 1 ELSE 0 END) AS international_flights
FROM
    flights
WHERE
    message_date >= CURDATE() - INTERVAL 13 DAY
GROUP BY
    flight_day
ORDER BY
    flight_day DESC";

$stmt = $db->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dates = '';
$domestic = '';
$international = '';
$domestic_previous = '';
$international_previous = '';

    
    // Output the results
    $x=1;
    foreach ($results as $row) {
  
        //echo "Date: " . $row["flight_day"]. " - Total Flights: " . $row["total_flights"]. " - Domestic Flights: " . $row["domestic_flights"]. " - International Flights: " . $row["international_flights"]. "<br>";

        if($x == 1){ // First one doesn't need a comma space in front (102, 98, 65)
            
            $dates .= "'" . $row["flight_day"] . "'";
            $domestic .= $row["domestic_flights"];
            $international .= $row["international_flights"];
        
        }else if($x <8){ // next entries do need the comma space (102, 98, 65)
        
            $dates .= ", '" . $row["flight_day"]. "'";
            $domestic .= ', ' . $row["domestic_flights"];
            $international .= ', ' . $row["international_flights"];
        
        }else if ($x == 8){
            
            $domestic_previous .= $row["domestic_flights"];
            $international_previous .= $row["international_flights"];
        
        }else if ($x > 8){

            $domestic_previous .= ', ' . $row["domestic_flights"];
            $international_previous .= ', ' . $row["international_flights"];
        
        }
        
        $x++;
    }



// Highcharts Code

?>

<figure class="highcharts-figure">
    <div id="container"></div>
    <p class="highcharts-description">
        
    </p>
</figure>


<script> Highcharts.chart('container', {

    chart: {
        type: 'column'
    },

    title: {
        text: 'Flights over the last 7-days',
        align: 'left'
    },

    xAxis: {
        categories: [<?php echo $dates; ?>]
    },

    yAxis: {
        allowDecimals: false,
        min: 0,
        title: {
            text: 'Flight Count'
        }
    },

    tooltip: {
        format: '<b>{key}</b><br/>{series.name}: {y}<br/>' +
            'Total: {point.stackTotal}'
    },

    plotOptions: {
        column: {
            stacking: 'normal'
        }
    },

    series: [{
        name: 'Domestic',
        data: [<?php echo $domestic; ?>],
        stack: 'Europe'
    }, {
        name: 'International',
        data: [<?php echo $international; ?>],
        stack: 'Europe'
    }, {
        name: 'Previous 7-days Domestic',
        data: [<?php echo $domestic_previous; ?>],
        stack: 'North America'
    }, {
        name: 'Previous 7-days International',
        data: [<?php echo $international_previous; ?>],
        stack: 'North America'
    }]
});

</script>