<?php
require_once __DIR__.'/../lib/DBAccess.php';

class ActiveKeySynchronizer
{
    function synchronize($activeKey)
    {
        $dbh = new DBAccess();
        
        // Update Active Key
        $result = $dbh->pquery("SELECT LockId, Begin, End FROM access NATURAL JOIN `key` WHERE Aktiv = TRUE AND KeyId = ?", $activeKey->getKeyId());
        $list = array();
        while ($row = $result->fetchObject()) {
            $list[] = new ActiveKeyConfig($row->LockId, $row->Begin, $row->End);
        }
        $activeKey->setConfigList($list);
    }
}
