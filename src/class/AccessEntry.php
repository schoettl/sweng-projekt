<?php
class AccessEntry
{
    public $id;
    public $firstName;
    public $lastName;
    public $location;
    public $begin;
    public $end;    
    function __construct($id, $fistName, $lastName, $location, $begin, $end = null)
    {
        $this->id = $id;
        $this->fistName = $fistName;
        $this->lastName = $lastName;
        $this->location = $location;
        $this->begin = $begin;
        $this->end   = $end;
    }
}
