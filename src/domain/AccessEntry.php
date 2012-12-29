<?php
class AccessEntry
{
    public $id;
    public $firstName;
    public $lastName;
    public $location;
    public $begin;
    public $end;    
    function __construct($id = 0, $firstName = '', $lastName = '', $location = '', $begin = null, $end = null)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->location = $location;
        $this->begin = $begin;
        $this->end   = $end;
    }
}
