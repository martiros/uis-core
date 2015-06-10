<?php

namespace UIS\Core\Auth;

interface AuthTokenProviderContract
{
    /**
     * Retrieve a token by their unique identifier.
     *
     * @param  int $id
     * @return \UIS\Core\Auth\AuthTokenContract
     */
    public function retrieveById($id);

    /**
     * Create new auth token.
     * @param array $data
     * @return \UIS\Core\Auth\AuthTokenContract
     */
    public function create(array $data);

    /**
     * Delete auth token.
     * @param int $id
     */
    public function delete($id);
}
