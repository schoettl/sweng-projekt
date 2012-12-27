<?php
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';

session_start();

$err = array();
$dbh = new DBAccess();
$system = System::getInstance();
$lp = $system->getLockProgrammer1();

$lockid = getVarFromPost('lockid');
$lock = $system->getLock($lockid);

if (getVarFromPost('program')) {
    if ($lock) {
        $lp->program($lock);
        $lp->nextLocation();
        $success = true; // programmieren erfolgreich
    } else {
        $err[] = 'Ungültige LockId.';
    }    
} else if (getVarFromPost('next')) {
    $lp->nextLocation();
} else if (getVarFromPost('rewind')) {
    $lp->rewindLocation();
}

$location = $lp->currentLocation();

if (!getVarFromPost('apply') && $location) {
    // Es wurde am Handheld was gedrückt (nicht rechts) -> Wähle autom. 'next location' als das Schloss, vor dem wir gerade stehen.
    $lockid = $dbh->pquery("SELECT LockId FROM `lock` WHERE Location = ?", $location)->fetchColumn();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>Lock Programmer</title>
    </head>
    <body>
        <h1>Schloss-Programmiergerät</h1>
        Nicht vergessen, das Schloss-Programmiergerät vorher <a href="../ui/sync.php">mit dem System zu synchronisieren</a>.
        <form method="POST" >
            <?php
            if (isset($success))
                echo '<div class="succ">Programmierung erfolgreich!</div>';
            foreach ($err as $e)
                echo '<div class="err" >' . xsafe($e) . '</div>'; // div id und css fuer error messages            
            ?>
            
            <table>
                <tr>
                    <td>
                        <div style="position: relative">            
                            <div style="position: absolute; top: 250px; left: 300px">
                                <p>
                                    <input type="submit" name="next" value="Nächstes" />
                                    <?php                                    
                                    if ($location !== false) {
                                        xecho($location . ' (nur ein Vorschlag)');
                                    }
                                    ?>
                                </p>
                                <p><input type="submit" name="program" value="Programmieren" /></p>
                                <p><input type="submit" name="rewind" value="Von vorne" /> (nur nicht-synchronisierte)</p> 
                                <?php
                                if ($location === false) {
                                    echo '<br>Keine weiteren Schlösser zu synchronisieren.<br>Probieren Sie "Von vorne", falls Sie Schlösser<br>übersprungen haben.';
                                }
                                ?>
                            </div>
                            <img src="../img/lockprogrammer.png">
                        </div>
                    </td>
                    <td>
                        Vor welchem Schloss stehen wir mit dem Programmiergerät gerade?
                        <p>
                            Lock Location:
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
                            <input type="submit" name="apply" value="Schloss auswählen" />
                            <a href="lock.php?lockid=<?php xecho($lockid); ?>">Link zum Schloss</a>
                        </p>
                    </td>
                </tr>
            </table>  
        </form>
    </body>
</html>
