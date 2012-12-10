<?php
class LockConfig
{
    public $accessList;
    public $whiteList;
    public $blackList;
    function __construct($accessList = array(), $whiteList = array(), $blackList = array())
    {
        $this->accessList = $accessList;
        $this->whiteList = $whiteList;
        $this->blackList = $blackList;
    }
}
