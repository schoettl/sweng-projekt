<?php
require_once __DIR__.'/../class/DBAccess.php';
require_once 'Key.php';
require_once 'Lock.php';
require_once 'KeyProgrammer.php';
require_once 'LockProgrammer.php';
require_once 'LockProgrammerSynchronizer.php';
require_once 'PseudoAccessSystem.php';
require_once 'ActiveKeyConfig.php';

class System {
    
    private $keys;
    private $locks;
    private $lp1; // LockProgrammer
    private $kp1; // KeyProgrammer
    private $lps; // LockProgrammerSynchronizer
    private $as;  // AccessSystem    
    
    /**
     * Set the instance of this class by simply setting the
     * session variable. The session must be started!
     * 
     * @param System $system
     */
    static function setInstance($system)
    {
        $_SESSION['system'] = $system;
    }
    
    /**
     * Return the instance of this class by simply getting it
     * from the session. The session must be started and the
     * session variable must be set!
     * 
     * @return System
     */
    static function getInstance()
    {
        return $_SESSION['system'];
    }
    
    function __construct()
    {
        $this->keys  = array();
        $this->locks = array();
        $this->lp1 = new LockProgrammer();
        $this->kp1 = new KeyProgrammer();
        $this->lps = new LockProgrammerSynchronizer();
        $this->as  = new PseudoAccessSystem();
    }
    
    function getKey($id)
    {
        if (array_key_exists($id, $this->keys)) {
            return $this->keys[$id];
        }
        
        $dbh = new DBAccess();
        $result = $dbh->pquery("SELECT KeyId, Aktiv FROM `key` WHERE KeyId = ?", $id);
        $row = $result->fetchObject();
        if ($row) {
            if ($row->Aktiv) {
                $key = new ActiveKey($row->KeyId);
                $result = $dbh->pquery("SELECT LockId, Begin, End FROM access WHERE KeyId = ?", $id);
                $list = array();
                while ($row = $result->fetchObject()) {
                    $list[] = new ActiveKeyConfig($row->LockId, $row->Begin, $row->End);
                }
                $key->setConfigList($list);
            } else {
                $key = new PassiveKey($row->KeyId);
            }
            
            $this->keys[$id] = $key;
            return $key;
        }
        
        return false; // oder exception, bei ungueltiger key id
    }

    function getLock($id)
    {
        if (array_key_exists($id, $this->locks)) {
            return $this->locks[$id];
        }
        
        $dbh = new DBAccess();
        $result = $dbh->pcount("SELECT LockId FROM `lock` WHERE LockId = ?", $id);
        $row = $result->fetchObject();
        if ($row) {
            $lock = new Lock($row->LockId);
            
            $this->locks[$id] = $lock;
            return $lock;
        }
        
        return false; // oder exception, bei ungueltiger key id
    }

    function getAccessSystem()
    {
        return $this->as;
    }

    function getKeyProgrammer1()
    {
        return $this->kp1;
    }

    function getLockProgrammerSynchronizer()
    {
        return $this->lps;
    }

    function getLockProgrammer1()
    {
        return $this->lp1;
    }
}
