<?php
require_once __DIR__.'/../../../sweng-projekt.cfg.inc.php';

/**
 * This is a class for easy and general access to the database via PDO.
 *
 * Nicht vergessen: Diese Klasse erweitert nur PDO. PDO ist die grundlegende
 * Datenbankschnittstelle, und genau das soll auch diese Klasse bleiben!
 * Das heißt also: Keine tollen neuen convenience methods hier! Für so etwas
 * lieber eine neue Klasse machen.
 *
 * Für PDO kann der Error mode festgelegt werden:
 * <code>$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_*);</code>
 * Das selbe Verhalten soll auch diese Klasse wiedergeben. Da diese Klasse
 * meistens Methoden von PDO verwendet, ist der Exception mode kein Problem:
 * Exceptions werden hier nicht gefangen sondern gehen einfach weiter zum
 * Aufrufer. Im Silent mode muss explizit nach Fehlern gefragt werden
 * (Rückgabewert FALSE), und das müssen auch diese Methoden hier tun!
 *
 * @author Jakob Schöttl
 */
class DBAccess extends PDO {

    function __construct()
    {
        parent::__construct(PDO_DSN, DB_USERNAME, DB_PASSWORD);
    }

    /**
     * Das SQL-Statement kann Platzhaltern (?) enthalten; die Werte aus dem
     * Array $params werden dort eingesetzt. $params darf auch leer, NULL oder
     * FALSE sein.
     * @param string $statement
     * @param array $params
     * @return PDOStatement
     */
    private function parameterized_query($statement, $params)
    {
        if ($params) {
            $pdostmt = $this->prepare($statement);
            if (!$pdostmt) {
                return false;
            }
            $i = 0;
            foreach ($params as &$v) {
                $pdostmt->bindValue(++$i, $v);
            }
            // PDOStatement::execute: returns boolean
            if (!$pdostmt->execute()) {
                return false;
            }
            return $pdostmt;
        } else {
            return $this->query($statement);
        }
    }

    // e.g. for fields (in order by) or dynamic queryies with AND/OR
    // praktisch waeren noch common white-lists, z.B. ( AND, OR ), ( TRUE, FALSE, NULL ) oder so
    function whitelist($s, $list, $case_sensitive = true)
    {
        $os = $s; // original s
        $olist = $list; // original list
        if (!$case_sensitive) {
            // alles klein schreiben
            $s = strtolower($s);
            $list = array_map('strtolower', $list);
        }
        if (!in_array($s, $list))
            return reset($olist); //first element
        return $os;
    }

    /**
     * Return the number of resulting rows of a query, optional under a
     * condition. The query looks like this: <code>SELECT COUNT(*) FROM
     * $tblspec WHERE $cond</code>.
     * So <code>$tblspec</code> can be a single table name or more than one,
     * joined tables e.g. <code>kunde JOIN rechnung USING (idKunde)</code>.
     *
     * @param string $tblspec The table specification
     * @param string $cond The <code>WHERE</code> condition without the
     * <code>WHERE</code> keyword
     * @return int
     */
    function count($tblspec, $cond = null)
    {
        return $this->pcount($tblspec, $cond);
    }

    /**
     * Wie self::count, aber: Condition kann Platzhalter für Parameter (?)
     * enthalten, weitere Funktionsparameter werden für diese Platzhalter
     * sicher als Strings eingesetzt.
     * p für prepared/parameterized
     *
     * Example:
     * <code>$c = $dbh->pcount('mitarbeiter',
     * "WHERE msmail LIKE ?", $mail);</code>
     *
     * @param type $tblspec
     * @param type $cond
     * @return int
     */
    function pcount($tblspec, $cond = null)
    {
        $statement = "SELECT COUNT(*) FROM $tblspec";
        if ($cond) {
            $statement .= " WHERE $cond";
        }
        $args = func_get_args();
        array_shift($args); // $tablespec weg
        array_shift($args); // $cond weg
        $result = $this->parameterized_query($statement, $args);
        if (!$result) {
            return false;
        }
        return $result->fetchColumn();
    }

    /**
     * Wie PDO::query, aber: Statement kann Platzhalter für Parameter (?)
     * enthalten, weitere Funktionsparameter werden für diese Platzhalter
     * sicher als Strings eingesetzt.
     * p für prepared/parameterized
     *
     * Example:
     * <code>$result = $dbh->pquery("SELECT * FROM mitarbeiter
     * WHERE msmail LIKE ?", $mail);</code>
     *
     * @param string $statement
     * @return int
     */
    function pquery($statement)
    {
        $args = func_get_args();
        array_shift($args);
        return $this->parameterized_query($statement, $args);
    }

    /**
     * Wie PDO::exec, aber: Statement kann Platzhalter für Parameter (?)
     * enthalten, weitere Funktionsparameter werden für diese Platzhalter
     * sicher als Strings eingesetzt.
     * p für prepared/parameterized
     *
     * Example:
     * <code>$result = $dbh->pexec("INSERT mitarbeiter (id, msmail) VALUES
     * (NULL, ?)", $mail);</code>
     *
     * @param string $statement
     * @return int
     */
    function pexec($statement)
    {
        $args = func_get_args();
        array_shift($args); // $statement weg
        $result = $this->parameterized_query($statement, $args);
        // PDO::query: returns a PDOStatement object, or FALSE on failure.
        // PDO::exec: returns FALSE on failure (auch wenn's nicht in
        // Specification steht)!
        if (!$result)
            return false;
        return $result->rowCount(); // Selbes Ergebnis wie PDO::exec
    }
}
