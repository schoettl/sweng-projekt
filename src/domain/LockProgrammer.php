<?php
class LockProgrammer
{
    // TODO eigentlich ein array! schlechter name
    private $list; // Liste mit LockProgrammerConfig Objekten
    private $it;
    
    function __construct()
    {
        $this->list = array();
        $this->it = new ArrayIterator();
    }
    
    function program($lock)
    {
        $i = new ArrayIterator($list);
        while ($i->valid()) {
            $cfg = $i->current();
            if ($cfg->lockId == $lock->getLockId()) {                
                $lock->setConfig($cfg->lockConfig);
                $cfg->inSync = true;
                $this->it = $i; // iterator fuer location neu setzen
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
        while ($this->it->valid() && $this->it->current()->inSync) {
            $this->it->next();
        }
        // location zurueckgeben
        if ($this->it->valid()) {
            return $this->it->current()->location();
        }
        return false;
    }
    
    function rewindLocation()
    {
        $this->it->rewind();
    }
    
    function getConfigList()
    {
        return $this->list;
    }
    
    function setConfigList($list)
    {
        $this->list = $list;
        $this->it = new ArrayIterator($list); //$list->getIterator();
    }
}
