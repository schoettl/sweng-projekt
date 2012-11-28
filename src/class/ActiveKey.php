<?php
class ActiveKey extends Key
{
    private $configs; // private, weil keyId auch private ist
    function getConfigs()
    {
        return $this->configs;
    }
    function setConfigs($configs)
    {
        $this->configs = $configs;
    }
}
