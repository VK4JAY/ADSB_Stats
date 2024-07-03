<?php
// Get DB information
include_once('dbconnect.php');
include_once('env.php');

$characters_to_escape = "'\"()\,";

try {
    $pdo = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get flights where src_country and dst_country are 'AU'
    $stmt = $pdo->prepare("SELECT DISTINCT route FROM flights WHERE src_country != '$userCountryCode' OR dst_country != '$userCountryCode' AND route != '' ");
    $stmt->execute();
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Array to store all the airport IATA codes from the routes
    $iata_codes = [];
    $source_airports = [];
    $routes = [];

    // Extracting IATA codes from routes and tracking source and destination airports
    foreach ($flights as $flight) {
        $route_parts = explode('-', $flight['route']);        
        if (count($route_parts) == 2) {
            $source_iata = $route_parts[0];
            $destination_iata = $route_parts[1];

            $iata_codes[] = $source_iata; // Source airport
            $iata_codes[] = $destination_iata; // Destination airport

            // Add to source airports if destination is not BNE
            if ($destination_iata !== $localAirport) {
                $source_airports[] = $source_iata;
            }

            // Store the route
            $routes[] = [
                'source' => $source_iata,
                'destination' => $destination_iata
            ];
        }
    }

    // Prepare the query to get airport details
    $placeholders = rtrim(str_repeat('?,', count($iata_codes)), ',');
    $stmt = $pdo->prepare("SELECT iata_code, latitude, longitude, municipality,name FROM airports WHERE iata_code IN ($placeholders)");
    $stmt->execute($iata_codes);
    $airports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatting the data into JSON
    $result = [];
    $source_airports_js_parts = [];
    $airport_details = [];

    foreach ($airports as $airport) {
        $iata_code = $airport['iata_code'];
        $airport_details[$iata_code] = addslashes($airport['name']);

        $result[] = [
            'id' => addcslashes($airport['name'], $characters_to_escape),
            'lat' => floatval($airport['latitude']),
            'lon' => floatval($airport['longitude'])
        ];

        // Check if this airport is a source airport and generate the JS constant part if true
        if (in_array($iata_code, $source_airports)) {
            $municipality = str_replace([' ', '-'], '', $airport['name']); // Remove spaces from municipality name
            $municipality = addcslashes($municipality, $characters_to_escape); // add slashes for countries with brackets and quotes
            $source_airports_js_parts[] = "{$municipality}Point = chart.get('{$airport['name']}')";
        }
    }

    // Combine the JS parts into a single const declaration
    $source_airports = 'const ' . implode(', ', $source_airports_js_parts) . ';';

    // Create the routes array grouped by source airport without duplicates
    $routes_grouped = [];
    $unique_routes = [];

    foreach ($routes as $route) {
        if (!empty($route['destination']) && $route['destination'] !== $localAirport ) {
            $source_municipality = $airport_details[$route['source']];
            $destination_municipality = $airport_details[$route['destination']];
            $source_js_name = preg_replace('/\s+/', '', $source_municipality);

            if($source_municipality != '' && $destination_municipality != ''){
                $route_id = "{$source_municipality} - {$destination_municipality}";
                if (!isset($unique_routes[$route_id])) {
                    $unique_routes[$route_id] = true;
                    $routes_grouped[$route['source']][] = [
                        'id' => $route_id,
                        'path' => "pointsToPath({$source_js_name}Point, chart.get('{$destination_municipality}'))"
                    ];
                }
            }
        }
    }

    // Prepare the data for final output
    $airport_list = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $routes_output = json_encode($routes_grouped, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  
    // Create a new JSON array that only contains the information from the sub-array for 'BNE'
    $routes_array = json_decode($routes_output, true);
    
    $arrayNames = array_keys($routes_array);
    $arrayCount = count($arrayNames);
        
    if (isset($routes_array[$localAirport])) {
        $local_routes = json_encode($routes_array[$localAirport], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $local_routes = str_replace('"pointsToPath(', 'pointsToPath(', $local_routes);
        $local_routes = str_replace(')"', ')', $local_routes);
           
    } 

    // Prepare the output string
    $output = "data: {$airport_list},\nsource_airports_js: {$source_airports},\nroutes: {$routes_output}";

    // Remove quotes around pointsToPath
    $output = str_replace('"pointsToPath(', 'pointsToPath(', $output);
    $output = str_replace(')"', ')', $output);
    
    $airport_list = str_replace('"pointsToPath(', 'pointsToPath(', $airport_list);
    $airport_list = str_replace(')"', ')', $airport_list);

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>