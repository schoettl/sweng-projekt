<?php
class Key
{
    private $keyId;
    function __construct($keyId)
    {
        $this->keyId = $keyId;
    }
    function getKeyId()
    {
        return $this->keyId;
    }
}
