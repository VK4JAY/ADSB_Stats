<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.10.0/proj4.js" integrity="sha512-e3rsOu6v8lmVnZylXpOq3DO/UxrCgoEMqosQxGygrgHlves9HTwQzVQ/dLO+nwSbOSAecjRD7Y/c4onmiBVo6w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
    <title>ADSB Statistics</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>ADSB Statistics</h1>
    </header>

    <div class="container">
        <div class="menu">
            <ul>
                <li><a href="" class="active" onclick="handleMenuClick(this,'/statsMain.php')">Home</a></li>
                <li><a href="#top-flights" onclick="handleMenuClick(this,'/statsMinMaxValues.php')">Top Flights</a></li>
                <li><a href="#all-flights" onclick="handleMenuClick(this,'/statsAllFlights.php')">All Routes</a></li>
                <li><a href="#most-seen-airplanes" onclick="handleMenuClick(this,'/statsMostSeenAircraft.php')">Most Seen Airplanes</a></li>
                <li><a href="#domestic-flights" onclick="handleMenuClick(this,'/statsTopDomesticFlights.php')">Domestic Flights</a></li>
                <li><a href="#international-flights" onclick="handleMenuClick(this,'/statsTopInternationalFlights.php')">International Flights</a></li>
                <li><a href="#airplane-types" onclick="handleMenuClick(this,'/statsTopAircraftTypes.php')">Airplanes Types</a></li>
                <li><a href="#aircraft-manufacturer" onclick="handleMenuClick(this,'/statsTopAircraftManufacturer.php')">Aircraft Manufacturer</a></li>
                <li><a href="#airlines" onclick="handleMenuClick(this,'/statsTopSeenAirlines.php?sortBy=total_seen&sortDirection=DESC&arrow=total_down')">Airlines</a></li>
                <li><a href="#military" onclick="handleMenuClick(this,'/statsMostSeenMilitary.php')">Military Aircraft</a></li>
                <li><a href="#countries" onclick="handleMenuClick(this,'/statsTopCountries.php')">Countries</a></li>
            </ul>
        </div>
        

        <div class="content" id="content">
        
            <?php include('statsMain.php'); ?>    
        
        </div>
    </div>

    <footer>
        <p>Copyright &copy; 2024 Your Website Name</p>
    </footer>

    <script>
        function handleMenuClick(clickedElement,url) {
            // Remove 'active' class from all menu items
            const menuItems = document.querySelectorAll('.menu a');
            menuItems.forEach(item => {
                item.classList.remove('active');
            });

            // Add 'active' class to the clicked menu item
            clickedElement.classList.add('active');

            // Send AJAX request to load content from the specified PHP file
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Update the content div with the response from the server
                    $('#content').html(response);
                },
                error: function() {
                    alert('Error loading content');
                }
            });            
        }

        function sortArray(url) {
            // Send AJAX request to load content from the specified PHP file
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Update the content div with the response from the server
                    $('#content').html(response);
                },
                error: function() {
                    alert('Error loading content');
                }
            });
        }

    </script>
</body>
</html>