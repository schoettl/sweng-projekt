<?php
/**
 * GET Params koennen sein: accessid und keyid
 * 
 */
require_once '../lib/stdio.php';
require_once '../domain/System.php';

session_start();

$err = array();
$system = System::getInstance();
$kp = $system->getKeyProgrammer1();

$keyid = getVarFromPostOrGet('keyid');

$attach = getVarFromPost('attach');
$detach = getVarFromPost('detach');
if ($attach || $detach) {
    $key = $system->getKey($keyid);
    if (!$key) {
        $err[] = 'Ungültige KeyId.';
    } else if ($attach) {
        $kp->attachKey($key);
    } else if ($detach) {
        $kp->detachKey($key);
    }
}

$key = $kp->getKey();

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" tyep="text/css" href="../web.css" />
        <title>KeyProgrammer</title>
    </head>
    <body>
        <h1>KeyProgrammer</h1>
        <img src="https://www.pcounter-germany.com/UserFiles2/Images/Loesungen/CS_Kartenleser_SXL.jpg" alt="Ein schönes Bild eines Kartenlesegeräts">
        <p>Dies ist das Lese- und Programmiergerät für die Schlüssel. Es ist per USB mit dem Computer verbunden. Hier kann man einen Schlüssel auf das Gerät legen bzw. ihn wieder wegnehmen.</p>
        <form method="POST" >
            <?php
            foreach ($err as $e)
                echo '<div class="err" >' . xsafe($e) . '</div>'; // div id und css fuer error messages
            ?>
            KeyId: <input type="text" name="keyid" value="<?php xecho($keyid); ?>" />
            <input type="submit" name="attach" value="Schlüssel auflegen" />
            <input type="submit" name="detach" value="Schlüssel wegnehmen" />
        </form>
        
        <?php
        echo '<p>Aktuell liegt ' . ($key ? 'ein' : 'kein') . ' Schlüssel auf.</p>';
        if ($key) {
            echo '<p>' . ($key instanceof ActiveKey ? 'Aktiver' : 'Passiver') . ' Schlüssel, KeyId: ' . xsafe($key->getKeyId()) . '</p>';
        }
        ?>
    </body>
</html>
