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
        $i = new ArrayIterator($this->list);
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
        $it = $this->it;
        
        if (!$it->valid())
            return false;
        
        if ($it->current()->inSync) {
            // weiter in liste, alle mit inSync == true ueberspringen
            while ($it->valid() && $it->current()->inSync) {
                $it->next();
            }
        } else {
            // not inSync: trotzdem eins weiter
            $it->next();
        }
        
        // location zurueckgeben
        return $this->currentLocation();
    }
    
    function currentLocation()
    {
        if ($this->it->valid())
            return $this->it->current()->location();
        return false;
    }
    
    function rewindLocation()
    {
        $it = $this->it;
        $it->rewind();
        if ($it->valid() && $it->current()->inSync)
            $this->nextLocation();
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
