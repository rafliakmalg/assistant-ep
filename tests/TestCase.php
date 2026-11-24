<?php

namespace Tests;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static ?string $keyDirectory = null;

    private static function generateKeys(): string
    {
        $directory = sys_get_temp_dir().'/jwt-test-keys-'.getmypid();

        if (! is_dir($directory)) {
            mkdir($directory);
        }

        $options = ['config' => __DIR__.'/openssl.cnf'];

        foreach (['foreign', 'main'] as $name) {
            $key = openssl_pkey_new([
                ...$options,
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);

            openssl_pkey_export($key, $private, null, $options);

            file_put_contents("{$directory}/{$name}-private.pem", $private);
            file_put_contents("{$directory}/{$name}-public.pem", openssl_pkey_get_details($key)['key']);
        }

        return $directory;
    }

    protected function keyPath(string $name): string
    {
        self::$keyDirectory ??= self::generateKeys();

        return self::$keyDirectory."/{$name}.pem";
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['jwt.public_key_path' => $this->keyPath('main-public')]);
    }

    /**
     * @param  array<string, mixed>  $claims
     * @return array<string, string>
     */
    protected function headers(array $claims = [], string $site = 'wms', string $signingKey = 'main-private'): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token($claims, $signingKey),
            'X-Site' => $site,
        ];
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    protected function token(array $claims = [], string $signingKey = 'main-private'): string
    {
        $now = time();

        $payload = [
            'sub' => 5,
            'name' => 'Budi',
            'roles' => ['operator'],
            'permissions' => [],
            'iat' => $now,
            'exp' => $now + 3600,
            ...$claims,
        ];

        return JWT::encode($payload, file_get_contents($this->keyPath($signingKey)), 'RS256');
    }
}
