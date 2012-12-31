<?php
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';

session_start();

$system = System::getInstance();
$err = array();

$success = false;
if (getVarFromPost('sync')) {
    $kp  = $system->getKeyProgrammer1();
    $aks = $system->getActiveKeySynchronizer();
    $key = $kp->getKey();
    if (!($key instanceof ActiveKey)) {
        $err[] = 'Es liegt kein aktiver Schlüssel auf dem Lese-/Programmiergerät.';
    } else {
        $keyid = $key->getKeyId();
        $aks->synchronize($key);
        $success = true; // wenn synchronize keine Exception wirft
    }    
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>Sync Active Key</title>
    </head>
    <body>
        <h1>Aktive Schlüssel synchronisieren</h1>
        <p>Bitte legen Sie einen aktiven Schlüssel auf das Lese-/Programmiergerät.</p>
        <form method="POST" >
            <?php
            foreach ($err as $e)
                echo '<div class="err" >' . xsafe($e) . '</div>'; // div id und css fuer error messages
            ?>
            <input type="submit" name="sync" value="Synchronisieren" />
        </form>
        <?php
        if ($success) {
            echo '<div class="succ" >Der aktive Schlüssel (KeyId: '.$keyid.') wurde synchronisiert.</div>';
        }        
        ?>
    </body>
</html>
