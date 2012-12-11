<?php
class ActiveKeyConfig
{
    public $lockId;
    public $begin;
    public $end;
    function __construct($lockId, $begin, $end = null)
    {
        $this->lockId = $lockId;
        $this->begin  = $begin;
        $this->end    = $end;
    }
}
