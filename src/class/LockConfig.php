<?php
class LockConfig
{
    public $accessList;
    public $whiteList;
    public $blackList;
    function __construct($accessList = null, $whiteList = null, $blackList = null)
    {
        $this->accessList = $accessList;
        $this->whiteList = $whiteList;
        $this->blackList = $blackList;
    }
}
