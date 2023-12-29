<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importation CSV vers MySQL</title>
</head>
<body>

<form action="import.php" method="post" enctype="multipart/form-data">
    <label for="file">Choisissez un fichier CSV :</label>
    <input type="file" name="file" id="file" accept=".csv">
    <button type="submit">Importer</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
    $fileName = $_FILES["file"]["tmp_name"];
    if ($_FILES["file"]["type"] !== "text/csv") {
        echo "Veuillez télécharger un fichier CSV.";
    } else {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=abcsalle", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // read csv file and upload it in var $donnees_csv
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

                $label = $ligne[0] ?? null; // Col 1
                $genre = $ligne[1] ?? null; // Col 2
                $origin = $ligne[2] ?? null; // Col 3

                $type = 0; //  ambigu default
                $genreValue = 0; // default
            
                // type and genre configuration 
                if ($genre === 'M') {
                    $type = 1; 
                    $genreValue = 1; 
                } elseif ($genre === 'F') {
                    $type = 2; 
                    $genreValue = 2; 
                } elseif ($genre === 'M,F') {
                    $type = 0; // Ambigu
                    $genreValue = 1; // prio M
                } elseif ($genre === 'F,M') {
                    $type = 0; // Ambigu
                    $genreValue = 2; // prio F
                }
                
                    // Insert into ref_prenoms
                    $stmt = $pdo->prepare("INSERT INTO ref_prenoms (label, origin, type, genre) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$label, $origin, $type, $genreValue]);}
                    // send dump sql

            
        } 
        
    }catch (PDOException $e) {
        echo "Erreur lors de l'importation : " . $e->getMessage();
    }
    
}}
?>

</body>
</html>
