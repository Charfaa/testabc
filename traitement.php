<?php

include("connect.php");
if (isset($_POST["valider"])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
        $fileName = $_FILES["file"]["tmp_name"];
        if ($_FILES["file"]["type"] !== "text/csv") {
            echo "Veuillez télécharger un fichier CSV.";
        } else {
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=abcsalle", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $firstLine = true;
                $donnees_csv = array_map(function($ligne) use (&$firstLine) {
                    if ($firstLine) {
                        $firstLine = false;
                        return null;
                    }
                    return str_getcsv($ligne, ';');
                }, file($fileName));

                foreach ($donnees_csv as $ligne) {
                    if ($ligne !== null) {
                        $firstname = mb_convert_encoding($ligne[1],'UTF-8');
                        $lastname = mb_convert_encoding($ligne[2],'UTF-8');
                        $found = false;
                        $civility = '';

                        $stmt = $pdo->prepare("SELECT * FROM ref_prenoms");
                        $stmt->execute();
                        $rowsRefPrenoms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($rowsRefPrenoms as $rowRefPrenom) {
                            if ($rowRefPrenom['genre'] == 1) {
                                $civility = 'M.';
                            } else if ($rowRefPrenom['genre'] == 2) {
                                $civility = 'Mme';
                            }
                            if ($rowRefPrenom['label'] == $firstname) {
                                $name = $civility . ' ' . $firstname . ' ' . $lastname;
                                $sqlInsert = "INSERT INTO results (id, firstName, lastName, new_firstname, new_lastname, name) VALUES (?, ?, ?, ?, ?, ?)";
                                $stmtInsert = $pdo->prepare($sqlInsert);
                                $stmtInsert->execute([$ligne[0], $firstname, $lastname, $firstname, $lastname, $name]);
                                $found = true;
                                break;
                            } elseif ($rowRefPrenom['label'] == $lastname) {
                                $name = $civility . ' ' . $lastname . ' ' . $firstname;
                                $sqlInsert = "INSERT INTO results (id, firstName, lastName, new_firstname, new_lastname, name) VALUES (?, ?, ?, ?, ?, ?)";
                                $stmtInsert = $pdo->prepare($sqlInsert);
                                $stmtInsert->execute([$ligne[0], $firstname, $lastname, $lastname, $firstname, $name]);
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $name = $firstname . ' ' . $lastname;
                            $sqlInsert = "INSERT INTO results (id, firstName, lastName, new_firstname, new_lastname, name) VALUES (?, ?, ?, ?, ?, ?)";
                            $stmtInsert = $pdo->prepare($sqlInsert);
                            $stmtInsert->execute([$ligne[0], $firstname, $lastname, $firstname, $lastname, $name]);
                        }
                    }
                }

                // Export to CSV file

            } catch (PDOException $e) {
                echo 'Erreur : ' . $e->getMessage();
            }
        }
    }
        $table_name = 'results';
        $export = 'export.csv';
        $sql = "SELECT * FROM $table_name";

        // prep of traitment
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Creation of export
        if ($stmt->rowCount() > 0) {
        $file = fopen($export, 'w');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        fputcsv($file, array_keys($row));

        // cleaning
        fputcsv($file, array_map('strip_tags', $row));

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($file, array_map('strip_tags', $row)); 
        }

        fclose($file);

        // download csv file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $export . '"');
        readfile($export);
        unlink($export);
        exit();
        }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importation CSV</title>
</head>
<body>

    <form action="traitement.php" method="post" enctype="multipart/form-data">
        <label for="file">Choisissez un fichier CSV :</label>
        <input type="file" name="file" id="file" accept=".csv">
        <button type="submit" name="valider">Valider</button>
    </form>

</body>
</html>
