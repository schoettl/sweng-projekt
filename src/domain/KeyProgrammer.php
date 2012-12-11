<?php
class KeyProgrammer
{
    private $key = null;
    function attachKey($key)
    {
        $this->key = $key;
    }
    function detachKey($key)
    {
        if ($this->key == $key)
            $this->key = null;
    }
    function getKey()
    {
        return $this->key;
    }
}
