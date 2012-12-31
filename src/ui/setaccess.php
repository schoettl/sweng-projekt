<?php
/**
 * GET Params koennen sein: accessid und keyid
 * 
 */
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';
require_once '../domain/PassiveKey.php';
require_once '../domain/ActiveKey.php';
require_once '../domain/AccessEntry.php';

function deleteAccess($dbh, $accessId)
{
    // Not tested, but would be a good option 
    // if getAccessEntry(id) === false or a delete button
    $dbh->pexec("DELETE FROM access WHERE AccessId = ?", $accessId);
}

function replaceAccess($dbh, $accessEntry, $keyId)
{
    global $err;
    
    // Datenbankeintragung in 2 Schritten, anders geht's wohl nicht:
    // sonst wird `lock` kurz gesperrt, wodurch der trigger nicht ausgeloest werden kann!

    $result = $dbh->pquery("SELECT LockId FROM `lock` WHERE Location = ?", $accessEntry->location);
    $lockId = $result->fetchColumn();
    if ($lockId === false) $err[] = 'Ungültige Location.';
    //$rowCount = $result->rowCount(); echo $rowCount . '<br>';
    //echo $accessid . ', ' . $lockid . ', ' . $keyid . ', '; var_dump($accessEntry);
    $count = $dbh->pexec("REPLACE access VALUES (?, ?, ?, ?, ?)",
        $accessEntry->id, $lockId, $keyId, $accessEntry->begin, $accessEntry->end);
    // REPLACE macht 1 oder 2 Dinge: [ DELETE FROM ... ; ] INSERT INTO ...

    if ($count != 1 && $count != 2) $err[] = 'Fehler beim Eintragen in die Datenbank.';
    return !$err; // sollte implizit bool werden, oder?
}

session_start();

$err = array();
$dbh = new DBAccess();
$system = System::getInstance();
$accessEntry = null;

$key = $system->getKeyProgrammer1()->getKey();
$isKeyAttached = (bool) $key;

$accessid = getVarFromPostOrGet('accessid');
$keyid = getVarFromPostOrGet('keyid');

// Wenn key id nicht explizit angegeben, von KeyProgrammer holen
if (!$keyid && $isKeyAttached) {
    $keyid = $key->getKeyId();
}

$success = false;
$get = getVarFromPost('get');
$set = getVarFromPost('set');
if ($get || $set) {
    // Überprüfung von access id
    $accessEntry = $system->getAccessSystem()->getAccessEntry($accessid);
    if (!$accessEntry) $err[] = 'Ungültige AccessId.';
    
    if ($set) {
        if (!$keyid) {
            // Schluessel nicht gegeben, d.h. Zugang "in Abwesenheit" des Keys ändern
            // Geht nur wenn's schon einen Zugang mit AccessId gibt
            $result = $dbh->pquery('SELECT KeyId FROM access WHERE AccessId = ?', $accessid);
            $keyid = $result->fetchColumn();
            if (!$keyid) $err[] = 'Ungültige KeyId.';
        } else {
            $key = $system->getKey($keyid);
            if (!$key) $err[] = 'Ungültige KeyId.';
        }

        if (!$err) {
            // TODO testen: akt. gültige andere Zugänge für key? unsynchronisierte locks zu key?
            $count = $dbh->pcount('access', 'KeyId = ? AND 
                Begin <= NOW() AND (NOW() <= End OR End IS NULL) AND 
                AccessId != ?', $keyid, $accessid);
            if ($count > 0) $err[] = 'Für diesen Schlüssel sind bereits andere aktuell gültige Zugänge eingetragen.';
            $count1 = $dbh->pcount('whitelist NATURAL JOIN `lock`', 'KeyId = ? AND last_change > last_sync OR last_sync IS NULL', $keyid);
            $count2 = $dbh->pcount('blacklist NATURAL JOIN `lock`', 'KeyId = ? AND last_change > last_sync OR last_sync IS NULL', $keyid);
            if ($count1 > 0 || $count2 > 0) $err[] = 'Es sind noch nicht alle Schlösser bezüglich dieses Schlüssels synchronisiert.'; // dabei wird black-/whitelist betrachtet

            if (!$err) {
                $success = replaceAccess($dbh, $accessEntry, $keyid);
                
                // Update Active Key if it is on KeyProgrammer
                if ($isKeyAttached && $key instanceof ActiveKey) {
                    $result = $dbh->pquery("SELECT LockId, Begin, End FROM access WHERE KeyId = ?", $key->getKeyId());
                    $list = array();
                    while ($row = $result->fetchObject()) {
                        $list[] = new ActiveKeyConfig($row->LockId, $row->Begin, $row->End);
                    }
                    $key->setConfigList($list);
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>Set Access</title>
    </head>
    <body>
        <h1>Zugang für Schlüssel erstellen/ändern</h1>
        Hier können Sie einen Zugang für einen Schlüssel erstellen bzw. ändern.
        Geben Sie dazu die AccessId (aus Zugangs-/Buchungssystem) ein.
        Das Feld mit der KeyId kann freigelassen werden, wenn
        <ul>
            <li>ein Schlüssel auf dem Schlüssel-Programmiergerät liegt.</li>
            <li>der Zugang schon erstellt wurde. In diesem Fall wird der bereits festgelegte Schlüssel angenommen.</li>
        </ul>
        <form method="POST" >
            <?php
            foreach ($err as $e)
                echo '<div class="err" >' . xsafe($e) . '</div>'; // div id und css fuer error messages
            ?>
            <table>
                <tr>
                    <td>AccessId:</td>                    
                    <td><input type="text" name="accessid" value="<?php xecho($accessid); ?>" /></td>
                    <td><input type="submit" name="get" value="Zugang anzeigen" /></td>
                </tr>
                <tr>
                    <td>KeyId:</td>                    
                    <td><input type="text" name="keyid" value="<?php xecho($keyid); ?>" /></td>
                </tr>
                
                
                <?php
                if ($accessEntry) {
                ?>
                <tr>
                    <td>Vorname:</td>
                    <td><?php xecho($accessEntry->firstName); ?></td>
                </tr>
                <tr>
                    <td>Nachname:</td>
                    <td><?php xecho($accessEntry->lastName); ?></td>
                </tr>
                <tr>
                    <td>Ort:</td>
                    <td><?php xecho($accessEntry->location); ?></td>
                </tr>
                <tr>
                    <td>Beginn:</td>
                    <td><?php xecho($accessEntry->begin); ?></td>
                </tr>
                <tr>
                    <td>Ende:</td>
                    <td><?php xecho($accessEntry->end); ?></td>
                </tr>
                <?php
                }
                ?>
                
                
            </table>
            <input type="submit" name="set" value="Zugang erstellen/ändern" />
            <?php
            if ($success) {
                echo '<div class="succ" >Der Zugang für den Schlüssel wurde erstellt bzw. geändert.</div>';
                if ($key instanceof PassiveKey) {
                    echo '<div>Das Schloss muss noch neu programmiert werden (wegen passivem Schlüssel).</div>';
                }
            }
            ?>
        </form>
    </body>
</html>
