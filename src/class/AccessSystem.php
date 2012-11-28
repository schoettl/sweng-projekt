<?php
interface AccessSystem
{
    /**
     * 
     * @param int $id
     * @return AccessEntry
     */
    function getAccessEntry($id);
}
