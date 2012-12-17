<?php
/**
 * GET Params koennen sein: lockid und keyid
 * 
 */
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';

session_start();

$err = array();
$dbh = new DBAccess();
$system = System::getInstance();

$lockid = getVarFromPostOrGet('lockid');
$keyid  = getVarFromPostOrGet('keyid');

$locked = true;

if (!$lockid) $err[] = 'Kein Schloss ausgewählt.';
if (!$keyid)  $err[] = 'Kein Schlüssel angegeben.';

if (!$err) {
    $lock = $system->getLock($lockid);
    $key  = $system->getKey ($keyid );
    if (!$lock) $err[] = 'Ungültige LockId.';
    if (!$key)  $err[] = 'Ungültige KeyId.';
    if (getVarFromPost('unlock') && !$err) {
        // aufsperren versuchen
        if ($lock->unlock($key)) {
            $locked = false;
        } else {
            $err[] = 'Schlüssel ist ungültig für dieses Schloss.';
        }
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Schloss</title>
    </head>
    <body>
        <h1>Schloss</h1>
        <form method="POST" >
            <?php
            foreach ($err as $e)
                echo '<div>' . xsafe($e) . '</div>'; // div id und css fuer error messages
            ?>
            <table>
                <tr>            
                    <td>LockId:</td>
                    <td><input type="text" name="lockid" value="<?php xecho($lockid); ?>"/></td>
                    <td><input type="submit" name="apply" value="Schloss auswählen" /></td>
                </tr>
                <tr>
                    <td>KeyId:</td>
                    <td><input type="text" name="keyid" value="<?php xecho($keyid); ?>" /></td>
                    <td><input type="submit" name="unlock" value="Aufsperren (versuchen)" /></td>
                    <td><input type="submit" name="lock" value="Zumachen" /></td>
                </tr>
            </table>
            <?php
            if (!$locked)
                echo '<div>Die Türe wurde aufgesperrt! Das war der richtige Schlüssel.</div>';
            ?>
        </form>
           
        
        <?php
        if ($lockid) {            
            $result = $dbh->pquery("SELECT location FROM `lock` WHERE LockId = ?", $lockid);            
            $location = $result->fetchColumn();
            $lock = $system->getLock($lockid);
            // TODO hier koennte man die config von lock $anzeigen
        ?>
        <h2><?php echo 'LockId: ' . xsafe($lockid) . '<br />' . xsafe($location) ?></h2>
        <table>
            <tr>
                <td><img src="a<?php /* TODO einfache zahl-url-zuordnung */ ?>" alt="Denk' dir schönes Bild einer Türe mit einem Schloss." /></td>
                <td>
                    <h3><?php echo "Status: " . ($locked ? "Locked" : "Unlocked"); ?></h3>
                    <h3>LockConfig</h3>
                    Hier könnte die Schlosskonfiguration ausgegeben werden.
                </td>
            </tr>
        </table>
        <?php
        }
        ?>
        
        
    </body>
</html>
