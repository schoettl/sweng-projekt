<?php
class ActiveKey extends Key
{
    private $list; // private, weil keyId auch private ist
    function getConfigList()
    {
        return $this->list;
    }
    function setConfigList($list)
    {
        $this->list = $list;
    }
}
