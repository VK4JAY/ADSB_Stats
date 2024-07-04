<?php

// function to compute distance between receiver and aircraft
function func_haversine($lat_from, $lon_from, $lat_to, $lon_to, $earth_radius = 3440) {
    $delta_lat = deg2rad($lat_to - $lat_from);
    $delta_lon = deg2rad($lon_to - $lon_from);
    $a = sin($delta_lat / 2) * sin($delta_lat / 2) + cos(deg2rad($lat_from)) * cos(deg2rad($lat_to)) * sin($delta_lon / 2) * sin($delta_lon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c;
}

// function to get details from adsb.lol for route details
function getApiResponse($callsign) {
    $url = "https://api.adsb.lol/api/0/routeset";
    
    $data = [
        "planes" => [
            [
                "callsign" => $callsign,
                "lat" => 0, // Assuming 0 if lat is not known
                "lng" => 0  // Assuming 0 if lng is not known
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return null;
    }

    return $response;
}


// Get Route information
function getRouteInfo($callsign) {
    $response = getApiResponse($callsign);

    if ($response === null) {
        return null;
    }

    $data = json_decode($response, true);

    if (isset($data[0]['_airport_codes_iata']) && isset($data[0]['_airports']) && count($data[0]['_airports']) >= 2) {
        return [
            '_airport_codes_iata' => $data[0]['_airport_codes_iata'],
            'airport_1_name' => $data[0]['_airports'][0]['name'],
            'airport_2_name' => $data[0]['_airports'][1]['name'],
            'country_1_name' => $data[0]['_airports'][0]['countryiso2'],
            'country_2_name' => $data[0]['_airports'][1]['countryiso2']
        ];
    }

    return null;
}

// This function is to another website. The format for the route is a little different.
function getRouteInfoADSBDB($callsign) {
    $url = "https://api.adsbdb.com/v0/callsign/".$callsign;
    $response = @file_get_contents($url);
    $data = json_decode($response, true);

    // Check if decoding was successful
    if ($data === null) {
        // JSON decoding failed
        return null;
    }else {
        // JSON decoding successful
        // $data now contains the JSON response as an associative array

        $src_iata = $data['response']['flightroute']['origin']['iata_code'];
        $dst_iata = $data['response']['flightroute']['destination']['iata_code'];
        $route = $src_iata . "-" . $dst_iata;
        
        return [
            'route' => $route,
            
            'src_country_iso_name' => $data['response']['flightroute']['origin']['country_iso_name'],
            'src_country_name' => $data['response']['flightroute']['origin']['country_name'],
            'src_elevation' => $data['response']['flightroute']['origin']['elevation'],
            'src_iata_code' => $data['response']['flightroute']['origin']['iata_code'],
            'src_icao_code' => $data['response']['flightroute']['origin']['icao_code'],
            'src_latitude' => $data['response']['flightroute']['origin']['latitude'],
            'src_longitude' => $data['response']['flightroute']['origin']['longitude'],
            'src_municipality' => $data['response']['flightroute']['origin']['municipality'],
            'src_name' => $data['response']['flightroute']['origin']['name'],

            'dst_country_iso_name' => $data['response']['flightroute']['destination']['country_iso_name'],
            'dst_country_name' => $data['response']['flightroute']['destination']['country_name'],
            'dst_elevation' => $data['response']['flightroute']['destination']['elevation'],
            'dst_iata_code' => $data['response']['flightroute']['destination']['iata_code'],
            'dst_icao_code' => $data['response']['flightroute']['destination']['icao_code'],
            'dst_latitude' => $data['response']['flightroute']['destination']['latitude'],
            'dst_longitude' => $data['response']['flightroute']['destination']['longitude'],
            'dst_municipality' => $data['response']['flightroute']['destination']['municipality'],
            'dst_name' => $data['response']['flightroute']['destination']['name'] 
        ];
    }

    return null;
}


// Function to get details of aircraft
function getPlaneInfo($callsign) {
    $url = "https://api.adsbdb.com/v0/aircraft/".$callsign;
    $response = @file_get_contents($url);
    $data = json_decode($response, true);

    // Check if decoding was successful
    if ($data === null) {
        // JSON decoding failed
        return null;
    }else {
        // JSON decoding successful
        // $data now contains the JSON response as an associative array
        return [
            'type' => $data['response']['aircraft']['type'],
            'icao_type' => $data['response']['aircraft']['icao_type'],
            'manufacturer' => $data['response']['aircraft']['manufacturer'],
            'mode_s' => $data['response']['aircraft']['mode_s'],
            'registration' => $data['response']['aircraft']['registration'],
            'registered_owner_country_iso_name' => $data['response']['aircraft']['registered_owner_country_iso_name'],
            'registered_owner_country_name' => $data['response']['aircraft']['registered_owner_country_name'],
            'registered_owner_operator_flag_code' => $data['response']['aircraft']['registered_owner_operator_flag_code'],
            'registered_owner' => $data['response']['aircraft']['registered_owner'],
            'url_photo' => $data['response']['aircraft']['url_photo'],
            'url_photo_thumbnail' => $data['response']['aircraft']['url_photo_thumbnail']
         ];
    }

    return null;
}

// Function to check if an airport exists and add if it doesn't
function checkAirportExists($pdo, $airport) {
    // Prepare and execute the SELECT statement
    $stmt = $pdo->prepare("SELECT iata_code FROM airports WHERE iata_code = :airport");
    $stmt->execute(['airport' => $airport]);
    
    // Return true if the airport exists, false otherwise
    return $stmt->rowCount() > 0;
    
}
?>
 