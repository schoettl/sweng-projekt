<?php
/**
 * GET Params koennen sein: lockid
 * 
 */
require_once '../lib/stdio.php';
require_once '../lib/DBAccess.php';
require_once '../domain/System.php';

session_start();

$err = array();
$dbh = new DBAccess();
$system = System::getInstance();
$lp = $system->getLockProgrammer1();

$lockid = getVarFromPostOrGet('lockid');

$lock = $system->getLock($lockid);
if (!$lock) {
    $err[] = 'Ungültige LockId.';
} else if (getVarFromPost('program')) {
    $lp->program($lock);
    $lp->nextLocation();
}

if (getVarFromPost('next')) {
    $lp->nextLocation();
} else if (getVarFromPost('rewind')) {
    $lp->rewindLocation();
}

$location = $lp->currentLocation();

if ($location === false) $err[] = 'Keine weiteren Schlösser zu synchronisieren. Probieren Sie "Von vorne", falls Sie Schlösser übersprungen haben.';

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>Lock Programmer</title>
    </head>
    <body>
        <h1>Schloss-Programmiergerät</h1>
        <?php
        foreach ($err as $e)
            echo '<div>' . xsafe($e) . '</div>'; // div id und css fuer error messages
        ?>
        <form method="POST" >
            <input type="hidden" name="lockid" value="<?php xecho($lockid); /* damit die lock id nicht verloren geht */ ?>" />
            <table>
                <tr>
                    <td><input type="submit" name="next" value="Nächstes" /></td>                    
                    <td><?php echo '<a href="lock.php?lockid=' . $lockid . '" >' . xsafe($location) . '</a>'; ?></td>
                </tr>
                <tr>
                    <td><input type="submit" name="program" value="Programmieren" /></td>                    
                </tr>
                <tr>
                    <td><input type="submit" name="rewind" value="Von vorne" /> (nur nicht-synchronisierte)</td>
                </tr>
            </table>
            <table>
                <tr>            
                    <td>LockId:</td>
                    <td><input type="text" name="lockid" value="<?php xecho($lockid); ?>"/></td>
                    <td><input type="submit" name="apply" value="Schloss auswählen" /></td>
                </tr>
                <tr>
                    <td>
                        <?php xecho($location); echo '<a href="lock.php?lockid=' . $lockid . '" >
                            <img src="../img/lock' . ($lockid % 5) .
                            'alt="Denk\' dir schönes Bild einer Türe mit einem Schloss." /></a>';
                        ?>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
