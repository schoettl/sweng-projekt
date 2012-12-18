<?php
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';

session_start();

$system = System::getInstance();

$success = false;
if (getVarFromPost('sync')) {
    $lps = $system->getLockProgrammerSynchronizer();
    $lp  = $system->getLockProgrammer1();
    $lps->synchronize($lp);
    $success = true; // wenn synchronize keine Exception wirft
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Sync LockProgrammer</title>
    </head>
    <body>
        <h1>Schloss-Programmiergerät synchronisieren</h1>
        Bitte überprüfen Sie, ob das Programmiergerät mit dem Computer verbunden ist (via USB).
        &ndash; Wird schon passen.
        <form method="POST" >
            <input type="submit" name="sync" value="Synchronisieren" />
        </form>
        <?php
        if ($success) {
            echo '<div>Das Schloss-Programmiergerät wurde synchronisiert.</div>';
        }
        ?>
    </body>
</html>
