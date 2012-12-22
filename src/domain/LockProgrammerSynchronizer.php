<?php
require_once __DIR__.'/../lib/DBAccess.php';
require_once __DIR__.'/LockProgrammerConfig.php';
require_once __DIR__.'/AccessListItem.php';

class LockProgrammerSynchronizer
{
    function synchronize($lockProgrammer)
    {
        $dbh = new DBAccess();
        
        // Von Programmiergeraet in DB
        $stmt = $dbh->prepare("UPDATE `lock` SET last_sync = NOW() WHERE LockId = ?");
        foreach ($lockProgrammer->getConfigList() as $cfg) {
            if ($cfg->inSync) {
                $stmt->bindParam(1, $cfg->lockId, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
        
        // Von DB auf Programmiergeraet
        $list = array();
        $locks = $dbh->query("SELECT LockId, Location FROM `lock` WHERE last_change > last_sync OR last_sync IS NULL");
        while ($lock = $locks->fetchObject()) {            
            $wlres = $dbh->pquery("SELECT KeyId FROM whitelist WHERE LockId = ?", $lock->LockId);
            $blres = $dbh->pquery("SELECT KeyId FROM blacklist WHERE LockId = ?", $lock->LockId);
            $alres = $dbh->pquery("SELECT KeyId, Begin, End FROM access NATURAL LEFT JOIN `key` WHERE Aktiv = FALSE AND LockId = ?", $lock->LockId);
            // LockConfig anlegen
            $wl = array();
            $bl = array();
            $al = array();
            while ($id = $wlres->fetchColumn()) $wl[] = $id;
            while ($id = $blres->fetchColumn()) $bl[] = $id;
            while ($row = $alres->fetchObject()) {
                $al[] = new AccessListItem($row->KeyId, $row->Begin, $row->End);
            }
            $lockConfig = new LockConfig($al, $wl, $bl);
            // LockProgrammerConfig anlegen und zur Liste hinzufuegen
            $lockProgrammerConfig = new LockProgrammerConfig($lock->LockId, $lock->Location, $lockConfig);
            $list[] = $lockProgrammerConfig;
        }
        
        // Auf Programmiergeraet uebertragen
        $lockProgrammer->setConfigList($list);
    }
}
