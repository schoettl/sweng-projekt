<?php
require_once 'lib/DBAccess.php';
require_once 'domain/System.php';

session_start();

try {

    // In DB: Alle Zugangseinträge löschen
    $dbh = new DBAccess();
    $dbh->exec("DELETE FROM whitelist WHERE LockId IS NOT NULL");
    $dbh->exec("DELETE FROM blacklist WHERE LockId IS NOT NULL");
    $dbh->exec("DELETE FROM access WHERE AccessId IS NOT NULL");
    $dbh->exec("UPDATE `lock` SET last_change = NOW(), last_sync = NULL 
        WHERE LockId IS NOT NULL"); // last_change wird bei Aenderung zwar autom.
        // aktualisiert, aber eben nur bei AENDERUNG. Deswegen explizit.
    
    // -- key 1, 2, 3 und 6, 7, 8 auf whitelist von lock 'R 0. Haupteingang'
    $dbh->exec("INSERT whitelist VALUES (31, 1), (31, 2), (31, 3), (31, 6), (31, 7), (31, 8)");

    System::setInstance(new System());
    
    $_SESSION['accessEntryList'] = array();
    
    $success = true;
} catch (Exception $e) {
    $success = false;
    $err = $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="web.css" />
        <title>sweng-projekt: Initialisierung</title>
    </head>
    <body>
        <h1>Initialisierung</h1>
        <?php
        if ($success) {
            echo 'Initialisierung erfolgreich!';
        } else {
            echo '<div class="err" >Fehler: ' . htmlentities($err) . '</div>';
            echo '<p><a href="init.php">Nochmal versuchen ...</a></p>';
        }
        ?>
        <!-- <p><a href="./">Zurück zur Startseite</a></p> -->
    </body>
</html>
