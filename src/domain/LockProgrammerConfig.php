<?php
class LockProgrammerConfig
{
    public $lockId;
    public $location;
    public $lockConfig;
    public $lastChange;
    public $inSync;    
    function __construct($lockId, $location, $lockConfig, $lastChange, $inSync = false)
    {
        $this->lockId   = $lockId;
        $this->location = $location;
        $this->lockConfig = $lockConfig;        
        $this->lastChange = $lastChange;
        $this->inSync = $inSync;
    }
}
