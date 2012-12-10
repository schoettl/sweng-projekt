<?php
interface AccessSystem
{
    /**
     * Returns an AccessEntry (aka "Buchungseintrag")
     * or false if the id is not valid.
     * @param int $id
     * @return AccessEntry|boolean
     */
    function getAccessEntry($id);
}
