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
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>Lock</title>
    </head>
    <body>
        <h1>Schloss</h1>
        <form method="POST" >
            <?php
            foreach ($err as $e)
                echo '<div class="err" >' . xsafe($e) . '</div>'; // div id und css fuer error messages
            ?>
            <table>
                <tr>            
                    <td>Lock Location:</td>
                    <td>
                        <select name="lockid">
                            <?php
                            $result = $dbh->query("SELECT LockId, Location FROM `lock` ORDER BY Location");
                            while ($row = $result->fetchObject()) {
                                echo '<option value="' . $row->LockId . '"' .
                                    (($row->LockId == $lockid) ? ' selected' : '') . '>' . 
                                    $row->Location . '</option>';
                            }
                            ?>
                        </select>
                    </td>
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
                echo '<div class="succ" >Die Türe wurde aufgesperrt! Das war der richtige Schlüssel.</div>';
            ?>
        </form>
           
        
        <?php
        if ($lockid) {            
            $result = $dbh->pquery("SELECT location FROM `lock` WHERE LockId = ?", $lockid);            
            $location = $result->fetchColumn();
            $lock = $system->getLock($lockid);
            echo '<table><tr><th>Schlossdaten</th></tr>';
            
            echo '<tr><td>LockId:</td>  <td>' . xsafe($lockid)   . '</td></tr>';
            echo '<tr><td>Location:</td><td>' . xsafe($location) . '</td></tr>';
            echo '<tr><td>Status:</td>  <td>' . ($locked ? "Locked" : "Unlocked") . '</td></tr>';
            
            echo '<tr><td>Keys auf white list:</td> <td>' . implode(', ', $lock->getConfig()->whiteList)  . '</td></tr>';
            echo '<tr><td>Keys auf black list:</td> <td>' . implode(', ', $lock->getConfig()->blackList)  . '</td></tr>';            
            echo '<tr><td>Keys auf access list:</td><td>' . implode(', ', $lock->getConfig()->accessList) . '</td></tr>';
            
            echo '</table>';
        }
        ?>
        
        
    </body>
</html>
