<?php

namespace App\Helper;

use Tymon\JWTAuth\Providers\Auth\AuthInterface;

class CustomAuthentication implements AuthInterface
{
    public function byCredentials(array $credentials = [])
    {
        return $credentials['username'] == 'admin' && $credentials['password'] == 'admin';
    }

    public function byId($id)
    {
        // maybe throw an expection?
    }

    public function user()
    {
        // you will have to implement this maybe.
    }
}
