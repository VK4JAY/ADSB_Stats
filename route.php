<?php
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
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Check if decoding was successful
    if ($data === null) {
        // JSON decoding failed
        echo "Failed to decode JSON.";
    }else {
        // JSON decoding successful
        // $data now contains the JSON response as an associative array

        $src = $data['response']['flightroute']['origin']['iata_code'];
        $dst = $data['response']['flightroute']['destination']['iata_code'];
        $route = $src . "-" . $dst;
        
        return [
            '_airport_codes_iata' => $route,
            'airport_1_name' => $data['response']['flightroute']['origin']['name'],
            'airport_2_name' => $data['response']['flightroute']['destination']['name'],
            'country_1_name' => $data['response']['flightroute']['origin']['country_iso_name'],
            'country_2_name' => $data['response']['flightroute']['destination']['country_iso_name']
        ];
    }

    return null;
}

function getPlaneInfo($callsign) {
    $url = "https://api.adsbdb.com/v0/aircraft/".$callsign;
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Check if decoding was successful
    if ($data === null) {
        // JSON decoding failed
        echo "Failed to decode JSON.";
    }else {
        // JSON decoding successful
        // $data now contains the JSON response as an associative array
        return [
            'type' => $data['response']['aircraft']['type'],
            'icao_type' => $data['response']['aircraft']['icao_type'][0],
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
?>
 