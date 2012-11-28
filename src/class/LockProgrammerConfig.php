<?php
class newPHPClass
{
    public $lockId;
    public $location;
    public $lockConfig;
    public $inSync;
    function __construct($lockId, $location, $lockConfig, $inSync = false)
    {
        $this->lockId   = $lockId;
        $this->location = $location;
        $this->lockConfig = $lockConfig;
        $this->inSync   = $inSync;
    }
}
