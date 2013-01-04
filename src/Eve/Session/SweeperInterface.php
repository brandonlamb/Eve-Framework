<?php
namespace Eve\Session;

interface SweeperInterface
{
    /**
     * Delete all expired sessions.
     *
     * @param  int  $expiration
     * @return void
     */
    public function garbageCollect($expiration);
}
