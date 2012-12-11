<?php
require_once 'AccessSystem.php';

class PseudoAccessSystem implements AccessSystem
{    
    function getAccessEntry($id)
    {
        $accessEntryList = $_SESSION['accessEntryList'];
        if (array_key_exists($id, $accessEntryList))
            return $accessEntryList[$id];
        
        return false;
    }
}
