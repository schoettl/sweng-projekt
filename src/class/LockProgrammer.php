<?php
class LockProgrammer
{
    private $list; // Liste mit LockProgrammerConfig Objekten
    function program($lock)
    {
        foreach ($list as $cfg)
            if ($cfg->lockId == $lock->getLockId()) {                
                $lock->setConfig($cfg->getLockConfig);
                $cfg->inSync = true;
                break;
            }
    }
    function nextLocation()
    {
        // weiter in liste, alle mit inSync == true ueberspringen
    }
    function getLockProgrammerConfigs()
    {
        return $this->list;
    }
    function setLockProgrammerConfigs($list)
    {
        $this->list = $list;
    }
}
