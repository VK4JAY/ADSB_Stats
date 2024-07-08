## Introduction
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

Some stats are clickable, allowing you to drill down into further details. New updates will have more drill downs.

## Docker images
Comming soon!
In my lab at the moment, I'm running three containers
- Web Server
   - Apache with PHP 7.3
- MariaDB
   - Database and tables defined in the adsb.sql file in this repository
- Application Server
   - Same image as the Web Server, but it runs the radar.php and maintenance.php scripts


## Under the Hood
The two main files that make it all happen:
  1) radar.php
     - Checks the aircraft.json file created by the ADSB application.
     - The interval is configured in the env.php file but defaults to every 30 seconds.
     - The file grabs all the details of current flights, adds new flights to the database, and checks if any existing flights have new Top Stats since the last iteration. 
     - If any of the stats are better, the database is updated.
     - Only one entry per flight is kept in the database, so adjusting the frequency of the radar.php file will generally only improve the Top Stats but will not impact any other data generated. 
     - This file should be run as part of a cron job to ensure it loops forever.
     - There is a sample cron file named adsb-cron, which prevents the application from running multiple times
     - It runs the maintenance.php file at the scheduled interval (default 600 seconds). Maintenance is a sanity check and cleanup for the database.
  
  2) index.html
     - This is the file that hosts the web application and needs to be accessible to the users.
     - It was designed and tested using Apache and PHP 7.3. Unless you are using our docker image, these will need to be installed on your host.

## Installation
This web application requires a database to be configured separately. The Database file is in the DB folder with the structure required.
The application was designed and tested using MariaDB but could be easily adapted to any other database.

  Step 1: Edit the env.php (or use a .env file if using Docker) file for your database settings and location of aircraft.json file

| Environment Variable                       | Details                                 | Default      |
| ------------------------------------------ | --------------------------------------- | ------------ |
| `$user_set_array['sleep'] = 30;`           | How often to loop the radar.php file    | 30 Seconds   |         
| `$user_set_array['maintenance'] = 600;`    | How often to run the maintenance file   | 600 Seconds  |
| `$user_set_arry['db_name'] = 'database_name';`   | Database Name                                  | 'adsb'         |
| `$user_set_arry['db_host'] = 'IP of database server';`                      | IP Address of Database        | '127.0.0.1'    |
| `$user_set_arry['db_user'] = 'Database username';`                          | Database Username       | 'root'         |
| `$user_set_arry['db_pass'] = 'Database password';`                          | Database Password       | '123456'       |
| `$user_set_array['url_json'] = ' ';`  | URL to the aircraft.json file in the format http://{IP_ADDRESS}/data/       |              |
| `$user_set_array['time_zone'] = ' ';`                      | Timezone       | 'Australia/Brisbane' |
| `$localAirport = ' ';`                                                    | Your local airport, used to create maps | 'BNE' |
| `$userCountry = ' ';`                                                      | Your country code, used to determine domestic and international flights  | 'AU'  |
 
Step 2: Configure the cron to run as often as you prefer

Step 3: Access the index.html file

## Files needed per container
If you run each component in seperate containers, below are the needed files for each.
You can of course run all of this on a single device.

- Database
   - db\adsb.sql

- Application
   - adsb-cron
   - dbconnect.php
   - env.php
   - functions.php
   - maintAircraft.php
   - maintCleandb.php
   - maintenance.php
   - maintFindEmptyFlights.php
   - radar.php
   
- Web Application
   - dbconnect.php
   - env.php
   - functions.php
   - index.php
   - maps*.php
   - stats*.php
   - style.css

## Acknowledgements
- docker-adsb-ultrafeeder --> Thanks for the awesome application that runs my ADSB node
   - `https://github.com/sdr-enthusiasts/docker-adsb-ultrafeeder`
- TomMuc1 --> The start of my applications began with code from here 
   - `https://github.com/TomMuc1/Dump1090-MySQL-Alert-Filter`