<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de la recherche</title>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }

         /* Colors */
        tr[type="0"] {
            background-color: #ffffff; /* white type 0 */
        }

        tr[type="1"] {
            background-color: #87CEFA; /* blue type 1 */
        }

        tr[type="2"] {
            background-color: #ffb6c1; /* pink type 2 */
        }
    </style>
</head>
<body>

<form action="recherche.php" method="post">
    <input type="text" placeholder="Prénom" name="prenom" id="prenom">
    <button type="submit">Rechercher</button>
</form>

<table>
    <tr>
        <th>Prénom</th>
        <th>Type</th>
        <th>Genre</th>
        <th>Origine</th>
    </tr>
  
    <?php
    if(isset($_POST['prenom'])) {
        include "connect.php";
        $recherche = $_POST["prenom"];

        $sqlRefPrenoms = "SELECT * FROM ref_prenoms WHERE label LIKE CONCAT(:recherche, '%')";
        $stmt = $conn->prepare($sqlRefPrenoms);
        $stmt->bindParam(':recherche', $recherche);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr type='" . $row['type'] . "'>
                    <td>" . $row['label'] . "</td>
                    <td>" . $row['type'] . "</td>
                    <td>" . $row['genre'] . "</td>
                    <td>" . $row['origin'] . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Aucun résultat trouvé.</td></tr>";
        }
    }
    ?>
</table>

</body>
</html>
