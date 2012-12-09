<?php
class LockProgrammer
{
    private $list; // Liste mit LockProgrammerConfig Objekten
    private $it;
    
    function __construct()
    {
        $list = array();
        $it = $list->getIterator();
    }
    
    function program($lock)
    {
        $i = $list->getIterator();
        while ($i->valid()) {
            $cfg = $i->current();
            if ($cfg->lockId == $lock->getLockId()) {                
                $lock->setConfig($cfg->lockConfig);
                $cfg->inSync = true;
                $it = $i; // iterator fuer location neu setzen
                break;
            }
            $i->next();
        }
        // Wenn keine updates fuer $lock da:
        // auch kein problem!
    }
    
    function nextLocation()
    {
        // weiter in liste, alle mit inSync == true ueberspringen
        while ($it->valid() && $it->current()->inSync) {
            $it->next();
        }
        // location zurueckgeben
        if ($it->valid()) {
            return $it->current()->location();
        }
        return false;
    }
    
    function rewindLocation()
    {
        $it->rewind();
    }
    
    function getConfigList()
    {
        return $this->list;
    }
    
    function setConfigList($list)
    {
        $this->list = $list;
        $it = $list->getIterator();
    }
}
