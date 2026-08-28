<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtLib
{
    protected $key;

    public function __construct()
    {
        $this->key = getenv('JWT_SECRET') ?: 'your-secret-key';
    }

    public function generate($user)
    {
        $payload = [
            'iss'       => 'sto',
            'sub'       => $user->id,
            'username'  => $user->cp_name,
            'payload'   => json_decode($user->payload),
            'roles'     => $user->role_name,
            'nik'       => $user->nik_user,
            'iat'       => time(),
            'exp'       => time() + 3600 * 24
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function validate($token)
    {
        return JWT::decode($token, new Key($this->key, 'HS256'));
    }
}
