<?php
/**
 * Standard I/O functions und mehr.
 *
 * @author Jakob Schöttl
 */

/**
 * Prüft, ob s in der Whitelist vorhanden ist.
 * Wenn ja wird s zurückgegeben, ansonsten
 * der erste Wert von list.
 *
 * E.g. for fields (in order by) or dynamic queries with AND/OR
 * TODO Praktisch wären noch common white-lists, z.B. ( AND, OR ), ( TRUE, FALSE, NULL ) oder so
 *
 * @param mixed $s
 * @param array $list
 * @param boolean $case_sensitive
 * @return mixed Return s if valid or the first element in list otherwise.
 *     If list is empty the return value is FALSE.
 */
function whitelist($s, $list, $case_sensitive = true)
{
    $os = $s; // original s
    $olist = $list; // original list
    if (!$case_sensitive) {
        // alles klein schreiben
        $s = mb_strtolower($s);
        $list = array_map('mb_strtolower', $list);
    }
    // strict! es wird auf identität geprüft (===), also auch der typ
    if (!in_array($s, $list, true))
        return reset($olist); //first element
    return $os;
}

/**
 * XSS safe conversion function.
 * <code>echo htmlspecialchars($data);</code> is not enough: Single quotes (')
 * are not converted.
 * @param type $data
 * @return type
 */
function xsafe($data, $encoding='UTF-8') {
    return htmlentities($data, ENT_QUOTES | ENT_HTML401, $encoding);
}

function xecho($data, $encoding='UTF-8') {
    echo xsafe($data, $encoding);
}

/**
 * Delete a cookie.
 * From https://www.owasp.org/index.php/PHP_Security_Cheat_Sheet#Proper_Deletion
 * @param string $name
 */
function delcookie($name)
{
    setcookie ($name, "", 1);
    setcookie ($name, false);
    unset($_COOKIE[$name]);
}

function getVarFromGet($key, $encoding='UTF-8') {
    $result = "";
    if (array_key_exists($key, $_GET)) {
        $result = $_GET[$key];
    }
    return $result;
}

function getVarFromPost($key, $encoding='UTF-8') {
    $result = "";
    if (array_key_exists($key, $_POST)) {
        $result = $_POST[$key];
    }
    return $result;
}

function getVarFromPostOrGet($key, $encoding='UTF-8') {
    $result = "";
    if (array_key_exists($key, $_POST)) {
        $result = $_POST[$key];
    } elseif (array_key_exists($key, $_GET)) {
        $result = $_GET[$key];
    }
    return $result;
}
