<?php
$connection = new mysqli(
    'localhost',
    'root',
    '',
    'mydb'
);

if ($connection->connect_error) {
    die('connection to db failed');
}

$baseQuery = 'SELECT kunde.vorname, kunde.nachname, kunde.idKunde, fahrt.leange, fahrt.startZeit, fahrt.endZeit, hs.name as startName, he.name as endeName, hs.idHaltestelle as idStart, he.idHaltestelle as idEnde FROM fahrt
        JOIN haltestelle hs ON hs.idHaltestelle = fahrt.Haltestelle_idHaltestelleStart
        JOIN Haltestelle he ON he.idHaltestelle = fahrt.Haltestelle_idHaltestelleEnde
        JOIN kunde ON kunde.idKunde = fahrt.Kunde_idKunde';


$selectedCustomer;
$selectedStation;
$rides = array();
$stations = array();
$customers = array();

//get all stations for filter
$query = 'select * from haltestelle';
$query = $connection->prepare($query);
$query->execute();
$stations = $query->get_result();

//get all customers for filter
$query = 'select * from kunde';
$query = $connection->prepare($query);
$query->execute();
$customers = $query->get_result();

//if filter active
if (isset($_GET["filter"])) {
    //FILTER FOR BOTH 
    if (isset($_GET["station"]) && isset($_GET["kunde"]) && $_GET["station"] != '' && $_GET["kunde"] != "") {
        $selectedCustomer = $_GET["kunde"];
        $selectedStation = $_GET["station"];

        $query = $baseQuery . ' where ? between hs.idHaltestelle and he.idHaltestelle and kunde.idKunde = ?';

        $query = $connection->prepare($query);
        $query->bind_param("ii", $selectedStation, $selectedCustomer);
        $query->execute();
        $rides = $query->get_result();
        //FILTER FOR ONLY STATION
    } else if (isset($_GET["station"]) && $_GET["station"] != "") {

        $selectedStation = $_GET["station"];

        $query = $baseQuery . ' where ? between hs.idHaltestelle and he.idHaltestelle';
        $query = $connection->prepare($query);
        $query->bind_param("i", $selectedStation);
        $query->execute();
        $rides = $query->get_result();
        //FILTER FOR ONLY KUNDE
    } else if (isset($_GET["kunde"]) && $_GET["kunde"] != "") {
        $selectedCustomer = $_GET["kunde"];
        $query = $baseQuery .  ' where kunde.idKunde = ?';
        $query = $connection->prepare($query);
        $query->bind_param("i", $selectedCustomer);
        $query->execute();
        $rides = $query->get_result();
    } else {
        $query = $baseQuery;
        $query = $connection->prepare($query);
        $query->execute();
        $rides = $query->get_result();
    }
    //NO FILTER
} else {
    $query = $baseQuery;
    $query = $connection->prepare($query);
    $query->execute();
    $rides = $query->get_result();
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Martin Zwicklhuber">
    <title>Fahrten</title>
    <style>
        table,
        th,
        td {
            border: solid 1px black
        }
    </style>
</head>

<body>
    <form method="GET" id="filterForm">
        <label for="kunde">Kunde: </label>
        <select name="kunde" id="kunde">
            <option>
                <?php
                foreach ($customers as $customer) {
                    //if one customer is selected make it selected in the dropdown
                    if (isset($selectedCustomer) && (int) $selectedCustomer == $customer["idKunde"]) {
                        echo '<option value="' . $customer['idKunde'] . '" selected>' . $customer['vorname'] . ' ' . $customer['nachname'] . '</option>';
                    } else {
                        echo '<option value="' . $customer['idKunde'] . '">' . $customer['vorname'] . ' ' . $customer['nachname'] . '</option>';
                    }
                }
                ?>
            </option>
        </select>
        <label for="station">Station: </label>
        <select name="station" id="station">
            <option>
                <?php
                foreach ($stations as $station) {
                    //if one station is selected make it selected in the dropdown
                    if (isset($selectedStation) && (int) $station["idHaltestelle"] == $selectedStation) {
                        echo '<option value="' . $station['idHaltestelle'] . '" selected>' .  $station['name'] . '</option>';
                    } else {
                        echo '<option value="' . $station['idHaltestelle'] . '">'  . $station['name'] . '</option>';
                    }
                }
                ?>
            </option>
        </select>
        <button type="submit" name="filter">Anzeigen</button>
    </form>

    <table>
        <tr>
            <th>Vorname</th>
            <th>Nachname</th>
            <th>Start Haltestelle</th>
            <th>Ende Halstestelle</th>
            <th>Start Zeit</th>
            <th>Ende Zeit</th>
            <th>Leange</th>
        </tr>
        <?php
        foreach ($rides as $ride) {
            echo '<tr>';
            echo '<td>' . $ride['vorname'] . '</td>';
            echo '<td>' . $ride['nachname'] . '</td>';
            echo '<td>' . $ride['startName'] . '</td>';
            echo '<td>' . $ride['endeName'] . '</td>';
            echo '<td>' . $ride['startZeit'] . '</td>';
            echo '<td>' . $ride['endZeit'] . '</td>';
            echo '<td>' . $ride['leange'] . '</td>';
            echo '</tr>';
        }
        ?>
    </table>
    <?php
    if (isset($rides)) {
        echo '<p>Fahrten insgesamt: ' . $rides->num_rows . '</p>';
    }
    ?>
    <form id="showAddRide" name="showAddRide" method="get">
        <button type="submit" name="addRide">Hinzufügen</button>
    </form>

    <?php
    //show form to add new ride. Maybe it would be better to add it to addRide.php i m not sure
    if (isset($_GET["addRide"])) {
        echo '<form id="addRideForm" name="addRideForm" method="post" action="addRide.php">';
        echo '<label for="customer">Kunde</label><br>';
        echo '<select id="customer" name="customer">';
        foreach ($customers as $customer) {
            echo '<option value="' . $customer['idKunde'] . '">' . $customer['vorname'] . ' ' . $customer['nachname'] . '</option>';
        }
        echo '</select><br>';
        echo '<label for="stationStart">Station Start</label><br>';
        echo '<select id="stationStart" name="stationStart">';
        foreach ($stations as $station) {
            echo '<option value="' . $station['idHaltestelle'] . '">'  . $station['name'] . '</option>';
        }
        echo '</select><br>';
        echo '<label for="stationEnde">Station Ende</label><br>';
        echo '<select id="stationEnde" name="stationEnde">';
        foreach ($stations as $station) {
            echo '<option value="' . $station['idHaltestelle'] . '">'  . $station['name'] . '</option>';
        }
        echo '</select><br>';
        echo '<label for="startTime">Start Zeit</label><br>';
        echo '<input id="startTime" name="startTime" type="datetime-local"><br>';
        echo '<label for="endTime">End Zeit</label><br>';
        echo '<input id="endTime" name="endTime" type="datetime-local"><br>';
        echo '<button type="submit" name="addRide">Hinzufügen</button>';
        echo '</form>';
    }

    ?>
</body>

</html>