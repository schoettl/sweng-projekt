<?php
class LockProgrammer
{
    // TODO eigentlich ein array! schlechter name
    private $list; // Liste mit LockProgrammerConfig Objekten
    private $currentIndex;
    
    function __construct()
    {
        $this->list = array();
        $this->currentIndex = 0;
    }
    
    function program($lock)
    {
        $i = 0;
        foreach ($this->list as $cfg) {            
            if ($cfg->lockId == $lock->getLockId()) {                
                $lock->setConfig($cfg->lockConfig);
                $cfg->inSync = true;
                $this->currentIndex = $i;
                break;
            }
            $i++;
        }
        // Wenn keine updates fuer $lock da:
        // auch kein problem!
    }
    
    function nextLocation()
    {
        $this->currentIndex++;
        while ($this->currentIndex < count($this->list) 
            && $this->list[$this->currentIndex]->inSync) {
            $this->currentIndex++;
        }
                
        // location zurueckgeben
        return $this->currentLocation();
    }
    
    function currentLocation()
    {
        if ($this->currentIndex < count($this->list))
            return $this->list[$this->currentIndex]->location;
        return false;
    }
    
    function rewindLocation()
    {
        $this->currentIndex = 0;
        if ($this->currentIndex < count($this->list) 
            && $this->list[$this->currentIndex]->inSync) {
            // gueltig, aber schon inSync:
            $this->nextLocation();
        }        
    }
    
    function getConfigList()
    {
        return $this->list;
    }
    
    function setConfigList($list)
    {
        $this->list = $list;
        $this->currentIndex = 0;
    }
}
