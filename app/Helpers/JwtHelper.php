<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generate_jwt($user)
{
    $key = getenv('JWT_SECRET') ?: 'your-secret-key';
    $payload = [
        'iss' => 'sto',
        'sub' => $user->id,
        'username' => $user->username,
        'iat' => time(),
        'exp' => time() + (3600 * 24),
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function validate_jwt($token)
{
    $key = getenv('JWT_SECRET') ?: 'your-secret-key';
    return JWT::decode($token, new Key($key, 'HS256'));
}
