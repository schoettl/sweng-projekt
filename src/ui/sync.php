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
    $count = count($lp->getConfigList());
    $success = true; // wenn synchronize keine Exception wirft
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>Sync LockProgrammer</title>
    </head>
    <body>
        <h1>Schloss-Programmiergerät synchronisieren</h1>
        <p>Bitte überprüfen Sie, ob das Programmiergerät mit dem Computer verbunden ist.</p>
        <form method="POST" >
            <input type="submit" name="sync" value="Synchronisieren" />
        </form>
        <?php
        if ($success) {
            echo '<div class="succ" >Das Schloss-Programmiergerät wurde synchronisiert. Es wurden Aktualisierungen für ' . $count . ' Schlösser übertragen.</div>';
        }        
        ?>
    </body>
</html>
