<?php
$conn = new mysqli('localhost', 'root', '', 'lap_einkaufsliste');

if ($conn->connect_error) {
    die("Connection fehlgeschlagen: " . $conn->connect_error);
}

//if post is called from this page
if (!empty($_POST["product"])) {
    $query = 'insert into produkt (PRODUKTBezeichnung, PRODUKTBeschreibung, KATEGORIE_idKATEGORIE, BENUTZER_idBENUTZER) values (?, ?, ?, 1)';
    $query = $conn->prepare($query);
    $query->bind_param("ssi", $_POST["product"], $_POST["produktSpecification"], $_POST["category"]);
    $query->execute();
    $insertedId = $conn->insert_id;

    $query = 'insert into liste_produkt(LISTE_PRODUKTAnmerkung, LISTE_PRODUKTMenge, LISTE_PRODUKTGekauft, LISTE_idLISTE, SHOP_idSHOP, PRODUKT_idPRODUKT) values ("", ?, 0, ?, ?, ?)';
    $query = $conn->prepare($query);
    $query->bind_param("iiii", $_POST["amount"], $_POST["list"], $_POST["shop"], $insertedId);
    $query->execute();

    if ($query->get_result() >= 0) {
        header("Location: main.php");
        die();
    }
    echo 'Fehler beim einfügen';
}

//fetch all data for the dropdowns
$query = 'select * from liste';
$query = $conn->prepare($query);
$query->execute();
$lists = $query->get_result();

$query = 'select * from kategorie';
$query = $conn->prepare($query);
$query->execute();
$categories = $query->get_result();

$query = 'select * from shop';
$query = $conn->prepare($query);
$query->execute();
$shops = $query->get_result();
?>

<html>

<head>
    <title>Einkaufsliste</title>
</head>

<body>
    <form id="addEntryToDB" name="addEntryToDB" method="post">
        <label for="product">Produkt</label>
        <input type="text" name="product" id="product" required><br>
        <label for="list">Liste</label>
        <select name="list" id="list">
            <?php
            foreach ($lists as $list) {
                echo '<option value="' . $list["idLISTE"] . '">' . $list["LISTEBezeichnung"] . '</option>';
            }
            ?>
        </select><br>
        <label for="amount">Anzahl</label>
        <input type="number" name="amount" id="amount"><br>
        <label for="produktSpecification">Produktspezifikation</label>
        <input type="text" name="produktSpecification" id="produktSpecification"><br>
        <label for="category">Kategorie</label>
        <select name="category" id="category">
            <?php
            foreach ($categories as $category) {
                echo '<option value="' . $category["idKATEGORIE"] . '">' . $category["KATEGORIEBezeichnung"] . '</option>';
            }
            ?>
        </select><br>
        <label for="shop">Shop</label>
        <select name="shop" id="shop">
            <?php
            foreach ($shops as $shop) {
                echo '<option value="' . $shop["idSHOP"] . '">' . $shop["SHOPBezeichnung"] . '</option>';
            }
            ?>
        </select><br>
        <button type="submit">Hinzufügen</button>
    </form>
</body>

</html>