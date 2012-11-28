<?php
class LockProgrammerSynchronizer
{
    function synchronize($lockProgrammer)
    {
        $dbh = new DBAccess();
        
        // Von Programmiergeraet in DB
        foreach ($lockProgrammer->getLockProgrammerConfigs() as $cfg) {
            if ($cfg->inSync) {
                $dbh->pexec("UPDATE lock SET last_sync = NOW() WHERE LockId = ?", $cfg->lockId);
            }
        }
        
        // Von DB auf Programmiergeraet
        $list = array();
        $locks = $dbh->query("SELECT LockId, Location FROM `lock` WHERE last_change > last_sync");
        while ($lock = $locks->fetchObject()) {            
            $wlres = $dbh->pquery("SELECT KeyId FROM whitelist WHERE LockId = ?", $lock->LockId);
            $blres = $dbh->pquery("SELECT KeyId FROM blacklist WHERE LockId = ?", $lock->LockId);
            $alres = $dbh->pquery("SELECT KeyId, Begin, End FROM accesslist NATURAL LEFT JOIN access WHERE LockId = ?", $lock->LockId);
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
        $lockProgrammer->setLockProgrammerConfigs($list);
    }
}
