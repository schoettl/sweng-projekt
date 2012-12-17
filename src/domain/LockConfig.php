<?php
class LockConfig
{
    public $accessList;
    public $whiteList;
    public $blackList;
    function __construct($accessList = null, $whiteList = null, $blackList = null)
    {
        $this->accessList = isset($accessList) ? $accessList : array();
        $this->whiteList = isset($whiteList) ? $whiteList : array();
        $this->blackList = isset($blackList) ? $blackList : array();
    }
}
