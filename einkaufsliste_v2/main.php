<?php
$conn = new mysqli("localhost", "root", "", "lap_einkaufsliste");

if ($conn->connect_error) {
    die("Connection fehlgeschlagen: " . $conn->connect_error);
}

$result = array();

$baseQuery = 'select * from liste
join liste_produkt on liste_produkt.LISTE_idLISTE = liste.idLISTE
join produkt on produkt.idPRODUKT = liste_produkt.PRODUKT_idPRODUKT
join kategorie on kategorie.idKATEGORIE = produkt.KATEGORIE_idKATEGORIE
join shop on liste_produkt.SHOP_idSHOP = shop.idSHOP
join benutzer on benutzer.idBENUTZER = liste.BENUTZER_idBENUTZER
where benutzer.BENUTZERMail = "max@einkaufslist.com"';
// if filter active
if (!empty($_GET["list"])) {
    $query = $baseQuery . ' and liste.idLISTE = ?';
    $query = $conn->prepare($query);
    $query->bind_param("i", $_GET["list"]);
    $query->execute();
    $result = $query->get_result();
}

//get all categories for table headers
$query = 'select * from kategorie';
$query = $conn->prepare($query);
$query->execute();
$categories = $query->get_result();

//get all lists for filter dropdown
$query = 'select * from liste group by idLISTE';
$query = $conn->prepare($query);
$query->execute();
$lists = $query->get_result();

$conn->close();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Einkaufsliste</title>
</head>

<body>
    <h1>Einkaufsliste</h1>

    <form id="filter" name="filter" method="get">
        <?php
        echo '<select id="list" name="list">';
        foreach ($lists as $list) {
            //if filter is current list then select it in dropdown
            if ((int) $list["idLISTE"] == $_GET["list"]) {
                echo '<option value="' . $list["idLISTE"] . '" selected>' . $list["LISTEBezeichnung"] . '</option>';
            } else {
                echo '<option value="' . $list["idLISTE"] . '">' . $list["LISTEBezeichnung"] . '</option>';
            }
        }

        echo '</select>';
        ?>
        <button type="submit">Anzeigen</button>
    </form>

    <table border="1px">
        <tr>
            <th>GEKAUFT</th>
            <th>PRODUKT</th>
            <th>MENGE</th>
            <th>SHOP</th>
        </tr>
        <?php
        foreach ($categories as $category) {

            $found = false;
            foreach ($result as $row) {
                //find all categories that need to be shown
                if ($category["idKATEGORIE"] == $row["idKATEGORIE"]) {
                    $found = true;
                }
            }
            
            //display category heading when found in result
            if ($found) {
                echo '<tr>';
                echo '<td colspan="4">' . $category["KATEGORIEBezeichnung"] . '</td>';
                echo '</tr>';
            }
            foreach ($result as $product) {
                if ($product["KATEGORIE_idKATEGORIE"] == $category["idKATEGORIE"]) {
                    //check checkbox if already bought
                    if ((int) $product["LISTE_PRODUKTGekauft"] == 1) {
                        echo '<td><input type="checkbox" checked></td>';
                    } else {
                        echo '<td><input type="checkbox"></td>';
                    }
                    echo '<td>' . $product["PRODUKTBezeichnung"] . '</td>';
                    echo '<td>' . $product["LISTE_PRODUKTMenge"] . '</td>';
                    echo '<td>' . $product["SHOPBezeichnung"] . '</td>';
                    echo '</tr>';
                }
            }
        }
        ?>
    </table>
    <form id="addEntry" name="addEntry" action="addEntry.php" method="post">
        <button type="submit">Hinzufügen</button>
    </form>
</body>

</html>