<?php
/**
 * GET Params koennen sein: accessid und keyid
 * 
 */
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';
require_once '../domain/PassiveKey.php';

session_start();

$err = array();
$dbh = new DBAccess();
$system = System::getInstance();

$key = $system->getKeyProgrammer1()->getKey();

$accessid = getVarFromPostOrGet('accessid');
$keyid = getVarFromPostOrGet('keyid');

if (!$keyid && $key) {
    $keyid = $key->getKeyId();
}

$success = false;
if (getVarFromPost('set')) {
    // Überprüfung von access id
    $accessEntry = $system->getAccessSystem()->getAccessEntry($accessid);    
    if (!$accessEntry) $err[] = 'Ungültige AccessId.';    
    
    // Überprüfung von key id
    if (!$keyid) {
        // Schluessel nicht gegeben, d.h. Zugang "in Abwesenheit" des Keys ändern
        // Geht nur wenn's schon einen Zugang mit AccessId gibt
        $result = $dbh->pquery('SELECT KeyId FROM access WHERE AccessId = ?', $accessid);
        $keyid = $result->fetchColumn();
        if (!$keyid) $err[] = 'Ungültige KeyId. Das Feld KeyId kann nur dann freigelassen werden, wenn bereits ein Zugang zu AccessId existiert.';
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
        $count1 = $dbh->pcount('whitelist NATURAL JOIN `lock`', 'KeyId = ? AND last_change > last_sync', $keyid);
        $count2 = $dbh->pcount('blacklist NATURAL JOIN `lock`', 'KeyId = ? AND last_change > last_sync', $keyid);
        if ($count1 > 0 || $count2 > 0) $err[] = 'Es sind noch nicht alle Schlösser bezüglich dieses Schlüssels synchronisiert.'; // dabei wird black-/whitelist betrachtet
        
        if (!$err) {
            // $dbh->pexec("REPLACE access SELECT ?, LockId, ?, ?, ? FROM `lock` WHERE Location = ?", 
            //    $accessid, $keyid, $accessEntry->Begin, $accessEntry->End, $accessEntry->Location);            
            // $dbh->pexec("REPLACE access VALUES (?, (SELECT LockId FROM `lock` WHERE Location = ?), ?, ?, ?)", 
            //    $accessid, $accessEntry->Location, $keyid, $accessEntry->Begin, $accessEntry->End);
            // ^ geht leider nicht, weil dadurch `lock` kurz gesperrt wird, wodurch der trigger nicht ausgeloest werden kann!
            
            $result = $dbh->pquery("SELECT LockId FROM `lock` WHERE Location = ?", $accessEntry->Location);
            $lockid = $result->fetchColumn();
            $dbh->pexec("REPLACE access VALUES (?, ?, ?, ?, ?)", 
                $accessid, $lockid, $keyid, $accessEntry->Begin, $accessEntry->End);
            $success = true; // wenn's keine keine Exception gibt
        }
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Set Access</title>
    </head>
    <body>
        <h1>Zugang für Schlüssel erstellen/ändern</h1>
        <form method="POST" > 
            <h1>Schloss-Programmiergerät</h1>
            <?php
            foreach ($err as $e)
                echo '<div>' . xsafe($e) . '</div>'; // div id und css fuer error messages
            ?>
            <table>
                <tr>
                    <td>AccessId:</td>                    
                    <td><input type="text" name="accessid" value="<?php xecho($accessid); ?>" /></td>
                </tr>
                <tr>
                    <td>KeyId:</td>                    
                    <td><input type="text" name="keyid" value="<?php xecho($keyid); ?>" /></td>
                </tr>
            </table>
            <input type="submit" name="set" value="Zugang erstellen/ändern" />
            <?php
            if ($success) {
                echo '<div>Der Zugang für den Schlüssel wurde erstellt bzw. geändert.</div>';
                if ($key instanceof PassiveKey) {
                    echo '<div>Das Schloss noch neu programmiert werden (wegen passivem Schlüssel).</div>';
                }
            }
            ?>
        </form>
    </body>
</html>
