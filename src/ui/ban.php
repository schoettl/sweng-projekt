<?php
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';

session_start();

$system = System::getInstance();
$dbh = new DBAccess();
$err = array();
$success = false;

$keyid    = getVarFromPostOrGet('keyid');
$accessid = getVarFromPostOrGet('accessid');

$perKey    = getVarFromPost('lockPerKeyId');
$perAccess = getVarFromPost('lockPerAccessId');

if ($perKey) {
    if ($keyid) { // id explizit angegeben
        $key = $system->getKey($keyid);
    } else { // id nicht angegeben -> wenn's geht vom KeyProgrammer holen
        $key = $system->getKeyProgrammer1()->getKey();
        if ($key) $keyid = $key->getKeyId();
    }
    
    if (!$key) $err[] = 'Ungültige KeyId.';
    // jetzt haben wir key und keyid
    
} else if ($perAccess) {
    $result = $dbh->pquery("SELECT KeyId FROM access WHERE AccessId = ?", $accessid);
    $keyid  = $result->fetchColumn();
    $key = ($keyid !== false) ? $system->getKey($keyid) : false;
    if (!$key) $err[] = 'Ungültige AccessId.';
    // jetzt haben wir key und keyid
}

if (($perKey || $perAccess) && !$err) {
    $dbh->pexec("DELETE FROM whitelist WHERE KeyId = ?", $keyid);
    $dbh->pexec("DELETE FROM blacklist WHERE KeyId = ?", $keyid);
    if ($key instanceof ActiveKey) {
        // Bei aktiven Keys: Auf Blacklists setzen, wo 'aktive' Zugaenge waren
        $dbh->pexec("INSERT INTO blacklist (LockId, KeyId) SELECT LockId, KeyId FROM access WHERE KeyId = ?", $keyid);
    }    
    $dbh->pexec("DELETE FROM access WHERE KeyId = ?", $keyid);
    // Eigentlich sollte man den Key noch als 'Banned' markieren (neue Spalte in `key`)
    
    $success = true;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>Ban</title>
    </head>
    <body>
        <h1>Schlüssel sperren</h1>
        <?php
        if ($success) echo '<div class=succ>Der Schlüssel (KeyId: ' . xsafe($keyid) . ') wurde gesperrt.</div>';
        foreach ($err as $e)
            echo '<div class=err>' . xsafe($e) . '</div>';
        ?>
        <form method="POST" >
            <h2>Schlüssel per KeyId sperren</h2>
            <p>
                Wenn bekannt ist um welchen Schlüssel es sich handelt, kann dessen Id eingegeben werden.
                Wenn der Schlüssel auf dem Key-Programmiergerät liegt, kann das Feld auch leer gelassen werden.
            </p>
            KeyId:
            <input type="text" name="keyid" value="<?php xecho($keyid) ?>">
            <input type="submit" name="lockPerKeyId" value="Sperren">

            <h2>Schlüssel per AccessId sperren</h2>
            <p>Dabei wird der Schlüssel gesperrt, der dem <span title="Beim Hotel der Buchungseintrag, damit also einer Person zugeordnet">Zugangseintrag</span> aktuell zugeordnet ist.</p>
            AccessId:
            <input type="text" name="accessid" value="<?php xecho($accessid) ?>">
            <input type="submit" name="lockPerAccessId" value="Sperren">
       </form>        
    </body>
</html>
