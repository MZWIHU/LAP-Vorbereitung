<?php
//connect to database
$server = 'localhost';
$username = 'root';
$password = '';
$database = 'lap_einkaufsliste';
$connection = new mysqli(
    $server,
    $username,
    $password,
    $database
);

if ($connection->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$mail = 'max@einkaufslist.com';
//get einkaufslisten for given email
$query = $connection->prepare('SELECT liste.LISTEBezeichnung, liste.idListe from liste join benutzer on benutzer.idBenutzer = liste.BENUTZER_idBenutzer where benutzer.BENUTZERMail = ?');
$query->bind_param("s", $mail);
$query->execute();
$lists = $query->get_result();
$categories = array();
$products = array();
$allCategories = array();
//get all categories
$query = $connection->prepare('SELECT * FROM KATEGORIE');
$query->execute();
$allCategories = $query->get_result();
//if list is explicitly set get all items on the list
if (isset($_GET['list'])) {
    $query = $connection->prepare('SELECT produkt.PRODUKTBezeichnung, liste_produkt.LISTE_PRODUKTMenge, liste.LISTEBezeichnung, shop.SHOPBezeichnung, kategorie.KATEGORIEBezeichnung, kategorie.idKATEGORIE, liste_produkt.LISTE_PRODUKTGekauft from liste join benutzer on benutzer.idBENUTZER = liste.BENUTZER_idBENUTZER join liste_produkt on liste_produkt.LISTE_idLISTE = liste.idLISTE join shop on shop.idSHOP = liste_produkt.SHOP_idSHOP join produkt on produkt.idPRODUKT = liste_produkt.PRODUKT_idPRODUKT join kategorie on kategorie.idKATEGORIE = produkt.KATEGORIE_idKATEGORIE where liste.idLISTE = ?');
    $query->bind_param("i", $_GET['list']);
    $query->execute();
    $products = $query->get_result();
    //get all categories contained in the list
    foreach ($products as $product) {
        $found = false;
        foreach($categories as $category) {
            if($category["idKATEGORIE"] == $product["idKATEGORIE"]) {
                $found = true;
            }
        }
        if (!$found) {
            //link product to category
            array_push($categories, $product);
        }
    }
}
//if new product is added
if (isset($_POST['addEntry'])) {
    $query = $connection->prepare('insert into produkt (PRODUKTBezeichnung, PRODUKTBeschreibung, KATEGORIE_idKATEGORIE, BENUTZER_idBENUTZER) values (?, ?, ?, 1)');
    $query->bind_param("sss", $_POST['productName'], $_POST['description'], $_POST["categories"]);
    $query->execute();
    $result = $connection->insert_id;
    $query = 'insert into liste_produkt(LISTE_ProduktMenge, LISTE_idLISTE, PRODUKT_idPRODUKT, SHOP_idSHOP) values (?, ?, ?, 1)';
    $query = $connection->prepare($query);
    $query->bind_param("sss", $_POST['amount'], $_POST['listToAdd'], $result);
    $query->execute();
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Martin Zwicklhuber">
    <title>Einkaufslist</title>
    <style>
        table,
        th,
        td {
            border: solid 1px black
        }
    </style>
</head>

<body>
    <form action="main.php" , method="get" id="listForm">
        <select name="list" id="list" form="listForm">
            <?php
            // echo the lists from the user if one is explicitly set it will be selected in the dropdown
            foreach ($lists as $list) {
                if (isset($_GET['list']) &&  $list['idListe'] == (int)$_GET['list']) {
                    echo '<option value="' . $list['idListe'] . '" selected>' . $list["LISTEBezeichnung"] . '</option>';
                } else {
                    echo '<option value="' . $list['idListe'] . '">' . $list["LISTEBezeichnung"] . '</option>';
                }
            }
            ?>
        </select>
        <button type="submit">ANZEIGEN</button>
    </form>
    <table>
        <th>GEKAUFT</th>
        <th>PRODUKT</th>
        <th>MENGE</th>
        <th>SHOP</th>
        <?php
        //loop over all categories and insert the products matching the category
        foreach ($categories as $category) {
            echo '<tr style=background-color:yellow>';
            echo '<td colspan="4">' . $category['KATEGORIEBezeichnung'] . '</td>';
            echo '</tr>';
            foreach ($products as $row_products) {
                if ($row_products['idKATEGORIE'] == $category["idKATEGORIE"]) {
                    echo '<tr>';
                    //set checkbox accordingly to state from db
                    if ($row_products['LISTE_PRODUKTGekauft'] ==  1) {
                        echo '<td><input type="checkbox" name="bought" value="2" checked></td>';
                    } else {
                        echo '<td><input type="checkbox" name="bought" value="2"></td>';
                    }
                    echo '<td>' . $row_products['PRODUKTBezeichnung'] . '</td>';
                    echo '<td>' . $row_products['LISTE_PRODUKTMenge'] . '</td>';
                    echo '<td>' . $row_products['SHOPBezeichnung'] . '</td>';
                    echo '</tr>';
                }
            }
        }
        ?>
    </table>
    <form method="post">
        <button type="submit" value="add" name="add">Produkt hinzufügen</button>
    </form>
    <?php
    // if add button is called show form to enter new product
    if (isset($_POST['add'])) {
        echo '<br><form method="post">';
        echo '
        <label for="productName">Produktname</label>
        <input type="text" name="productName" id="productName" required> <br>
        ';
        echo '
        <label for="listToAdd">Liste</label>
        <select name="listToAdd" id="listToAdd">
        ';
        foreach ($lists as $list) {
            echo '<option value="' . $list['idListe'] . '">' . $list["LISTEBezeichnung"] . '</option>';
        }
        echo '</select><br>';
        echo '
        <label for="amount">Menge</label>
        <input type="number" name="amount" id="amount"><br>
        ';
        echo '
        <label for="description">Beschreibung</label>
        <input type="text" name="description" id="description"><br>
        ';
        echo '
        <label for="categories">Kategorien</label>
        <select name="categories" id="categories">
        ';
        foreach ($allCategories as $category) {
            echo '<option value="' . $category['idKATEGORIE'] . '">' . $category["KATEGORIEBezeichnung"] . '</option>';
        }
        echo '</select><br>';
        echo '<button type="submit" value="addEntry" name="addEntry">Hinzufügen</button>';
        echo '</form>';
    }
    ?>
</body>

</html>