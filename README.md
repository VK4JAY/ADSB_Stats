This web application was designed to create statistics that other ADSB applications didn't offer, including:
  1) Total Flights Seen
  2) Resolving each commercial route seen
  3) Identifying Domestic flights (Your country Start and End)
  4) Identifying International flights (Your Country to or from another country)
  5) Displaying maps of the domestic and international routes
  6) Identifying all Airports of routes seen
  7) Listing all countries flown to or from
  8) Identifying Military Aircraft
  9) Identifying each unique aircraft and each flight it as done as well as the frequency seen
  10) Top Stats (Closest, Furthest, Lowest, Highest, Slowest, Fastest)
  11) Most seen aircraft
  12) Aircraft Types
  13) Aircraft Manufacturers

Most stats are clickable, allowing you to drill down into further details.

Under the Hood:
  The 3 main files that run the application are:
  1) radar.php
     - This file checked the aircraft.json file created by the ADSB application.
     - The interval is configured in the env.php file but defaults to every 30 seconds.
     - The file grabs all the details of current flights, adds new flights to the database, and checks if any existing flights have new Top Stats since the last iteration. If any of the stats are better, the database is updated.
     - Only one entry per flight is kept in the database, so adjusting the frequency of the radar.php file will generally only improve the Top Stats but will not impact any other data generated. Once each flight is seen, it will be added to the database.
     - This file should be run as part of a cron job to ensure it loops forever.
     - There is a sample cron file named adsb-cron, which prevents the application from running multiple times
  3) maintenance.php
     - This file runs sanity checks on the database and updates entries where possible if the information was unavailable at the time of database insertion.
     - This should be run as a cron job, but not nearly as often as radar.php
     - The sample cron file includes the maintenance.php file
  4) index.html
     - This is the file that hosts the web application and needs to be accessible to the users.
     - It was designed and tested using Apache and PHP 7.3. Unless you are using our docker image, these will need to be installed on your host.

Installation:
  This web application requires a database to be configured separately. The Database file is in the DB folder with the structure required.
  The application was designed and tested using MariaDB but could be easily adapted to any other database.

  Step 1: Edit the env.php file for your database settings and location of aircraft.json file
           $user_set_array['sleep'] = 30; // How often to run the radar.php file
           $user_set_array['maintenance'] = 600; // How often to run the maintenance file
           $user_set_arry['db_name'] = 'database_name';
           $user_set_arry['db_host'] = 'IP of database server';
           $user_set_arry['db_user'] = 'Database username';
           $user_set_arry['db_pass'] = 'Database password';
           $user_set_array['url_json'] = 'http://the_path_to_your_adsb_node/data/';
           $user_set_array['time_zone'] = 'Australia/Brisbane';
           $localAirport = 'BNE'; // Your local airport, used to create maps
           $userCountry = 'AU'; // Your country code, used to determine domestic and international flights
  Step 2: Configure the cron to run as often as you prefer
  Step 3: Access the index.html file
