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

//get the length of the ride in stations. abs is used to prevent negativ numbers if someone is travelling in the other direction
$length = abs((int) $_POST["stationStart"] - (int) $_POST["stationEnde"]);
$query = "insert into fahrt (leange, Kunde_idKunde, Haltestelle_idHaltestelleStart, Haltestelle_idHaltestelleEnde, startZeit, endZeit) values (?, ?, ?, ?, ?, ?)";
$query = $connection->prepare($query);
$query->bind_param("iiiiss", $length, $_POST["customer"], $_POST["stationStart"], $_POST["stationEnde"], $_POST["startTime"], $_POST["endTime"]);
$query->execute();
echo $query->affected_rows;
//navigate back to main page
header("Location: fahrten.php");
die();
?>