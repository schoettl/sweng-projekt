<?php
require_once 'LockConfig.php';
require_once __DIR__.'/../lib/DBAccess.php';

class Lock
{
    private $lockId;
    private $config;
    function __construct($lockId)
    {
        $this->lockId = $lockId;
        $this->config = new LockConfig();
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
        foreach ($this->config->accessList as $item) {
            if ($item->keyId == $key->getKeyId()
                && $this->isNowInWindow($item->begin, $item->end)
            ) {
                return true;
            }
        }
        
        // Bei Active Key:
        if ($key instanceof ActiveKey) {
            foreach ($key->getConfigList() as $keyConfig) {
                if ($keyConfig->lockId == $this->lockId 
                    && $this->isNowInWindow($keyConfig->begin, $keyConfig->end)
                ) {
                    return true;
                }
            }
        }
        
        return false;
    }
    function getConfig()
    {
        return $this->config;
    }
    function setConfig($config)
    {
        $this->config = $config;
    }
    
    
    private function isNowInWindow($begin, $end = null) {
        // Aktuelle Zeit
//        $dbh = new DBAccess();
//        $now = $dbh->query("SELECT NOW()")->fetchColumn();
        $now = strftime("%Y-%m-%d %H:%M:%S", time());
        
        if (strcmp($now, $begin) < 0)
            return false;
        if (!$end)
            return true;
        if (strcmp($end, $now) >= 0)
            return true;
        
        return false;
    }
}
