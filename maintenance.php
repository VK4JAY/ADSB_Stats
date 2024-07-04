<?php
// Get DB information
include_once('dbconnect.php');

// Find flights that don't have a route, 
// Search for other entires that have the same flight number
// Update the entries route,src,dst
include('maintFindEmptyFlights.php');


// Find all distinct registrations and count occurances
// Check if we have this aircraft in the database
// Resolve the details via API if we don't
include('maintAircraft.php');


// Merge duplicate entries that are within 20 minutes of each other
include('maintCleandb.php');


?>