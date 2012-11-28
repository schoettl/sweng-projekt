<?php
class Lock
{
    private $lockId;
    private $config;
    function __construct($lockId)
    {
        $this->lockId = $lockId;
    }
    function getLockId()
    {
        return $this->lockId;
    }
    function unlock($key)
    {
        // auf blacklist: return false
        if (in_array($key->getKeyId(), $this->config->blackList))
            return false;
        
        // auf whitelist: return true
        if (in_array($key->getKeyId(), $this->config->whiteList))
            return true;
        
        // accesslist pruefen!
        foreach ($this->config->accessList as $item)
            if ($item->keyId == $key->getKeyId()) {
                
                // zeit zur not via DB pruefen ala SELECT ($begin <= NOW() AND (NOW() < $end OR $end IS NULL))
                
                break;
            }
    }
    function getConfig()
    {
        return $this->config;
    }
    function setConfig($config)
    {
        $this->config = $config;
    }
}
