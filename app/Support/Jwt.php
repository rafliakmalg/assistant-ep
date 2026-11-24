<?php

namespace App\Support;

use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key;

class Jwt
{
    private const ALGORITHM = 'RS256';

    /**
     * @return array<string, mixed>
     */
    public static function decode(string $token): array
    {
        FirebaseJwt::$leeway = 30;

        $publicKey = file_get_contents(config('jwt.public_key_path'));

        return (array) FirebaseJwt::decode($token, new Key($publicKey, self::ALGORITHM));
    }
}
