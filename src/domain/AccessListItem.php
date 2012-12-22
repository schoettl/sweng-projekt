<?php
class AccessListItem
{
    public $keyId;
    public $begin;
    public $end;
    function __construct($keyId, $begin, $end = null)
    {
        $this->keyId = $keyId;
        $this->begin  = $begin;
        $this->end    = $end;
    }
    function __toString()
    {
        return $this->keyId . ' (' . $this->begin . ' – ' . $this->end . ')';
    }
}
