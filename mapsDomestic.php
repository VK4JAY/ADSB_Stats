<?php
// Get DB information
include_once('dbconnect.php');
include_once('env.php');
include('mapsDomesticGenerate.php');


// Create connection
$db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>

<html>
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.10.0/proj4.js" integrity="sha512-e3rsOu6v8lmVnZylXpOq3DO/UxrCgoEMqosQxGygrgHlves9HTwQzVQ/dLO+nwSbOSAecjRD7Y/c4onmiBVo6w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://code.highcharts.com/maps/highmaps.js"></script>
    <script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
</head>

<style>
#container {
    height: 680px;
    min-width: 310px;
    max-width: 800px;
    margin: 0 auto;
}

.loading {
    margin-top: 10em;
    text-align: center;
    color: gray;
}
</style>

<body>

    <div id="container"></div>


<script>
(async () => {

    const mapData = await fetch(
        'https://code.highcharts.com/mapdata/countries/au/au-all.topo.json'
    ).then(response => response.json());

    // Initialize the chart
    const chart = Highcharts.mapChart('container', {

        title: {
            text: 'Domestic flight routes',
            align: 'left'
        },

        legend: {
            align: 'left',
            layout: 'vertical',
            floating: true
        },

        accessibility: {
            point: {
                valueDescriptionFormat: '{xDescription}.'
            }
        },

        mapNavigation: {
            enabled: true
        },

        tooltip: {
            format: '{point.id}{#if point.lat}<br>Lat: {point.lat} Lon ' +
                '{point.lon}{/if}'
        },

        plotOptions: {
            series: {
                marker: {
                    fillColor: '#FFFFFF',
                    lineWidth: 2,
                    lineColor: Highcharts.getOptions().colors[1]
                }
            }
        },

        series: [{
            // Use the au map with no data as a basemap
            mapData,
            name: 'Australia',
            borderColor: '#707070',
            nullColor: 'rgba(200, 200, 200, 0.3)',
            showInLegend: false
        }, {
            // Specify cities using lat/lon
            type: 'mappoint',
            name: 'Cities',
            dataLabels: {
                format: '{point.id}'
            },
            // Use id instead of name to allow for referencing points later
            // using
            // chart.get
            data: <?php echo $airport_list .","; ?>  
        }]
    });

    // Function to return an SVG path between two points, with an arc
    function pointsToPath(fromPoint, toPoint, invertArc) {
        const
            from = chart.mapView.lonLatToProjectedUnits(fromPoint),
            to = chart.mapView.lonLatToProjectedUnits(toPoint),
            curve = 0.05,
            arcPointX = (from.x + to.x) / (invertArc ? 2 + curve : 2 - curve),
            arcPointY = (from.y + to.y) / (invertArc ? 2 + curve : 2 - curve);
        return [
            ['M', from.x, from.y],
            ['Q', arcPointX, arcPointY, to.x, to.y]
        ];
    }

    //const brisbanePoint = chart.get('Brisbane'),
    //    lerwickPoint = chart.get('Lerwick');

    //const BrisbanePoint = chart.get("Brisbane"), SydneyPoint = chart.get("Sydney"), DarwinPoint = chart.get("Darwin"), MelbournePoint = chart.get("Melbourne"), MackayPoint = chart.get("Mackay"), AdelaidePoint = chart.get("Adelaide"), GoldCoastPoint = chart.get("Gold Coast"), WilliamtownPoint = chart.get("Williamtown"), PerthPoint = chart.get("Perth"), CanberraPoint = chart.get("Canberra"), MaroochydorePoint = chart.get("Maroochydore"), HamiltonIslandPoint = chart.get("Hamilton Island"), CairnsPoint = chart.get("Cairns"), TownsvillePoint = chart.get("Townsville"), KarrathaPoint = chart.get("Karratha"), HobartPoint = chart.get("Hobart"), MilduraPoint = chart.get("Mildura"), LismorePoint = chart.get("Lismore"), DubboPoint = chart.get("Dubbo"), EmeraldPoint = chart.get("Emerald"); 
    
    <?php echo $source_airports; ?>

    // Add a series of lines for London
    chart.addSeries({
        name: 'Brisbane flight routes',
        type: 'mapline',
        lineWidth: 2,
        color: Highcharts.getOptions().colors[3],
        data: <?php echo $local_routes; ?>

        }, true, false);

  
})();
</script>

</body>
</html>