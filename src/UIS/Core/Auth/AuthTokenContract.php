<?php

namespace UIS\Core\Auth;

interface AuthTokenContract
{
    public function getTokenIdentifier();

    public function getToken();

    /**
     * @return bool
     */
    public function isActiveToken();
}
