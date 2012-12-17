<?php
class LockConfig
{
    public $accessList;
    public $whiteList;
    public $blackList;
    function __construct($accessList = null, $whiteList = null, $blackList = null)
    {
        $this->accessList = $accessList ? $accessList : array();
        $this->whiteList = $whiteList ? $whiteList : array();
        $this->blackList = $blackList ? $blackList : array();
    }
}
