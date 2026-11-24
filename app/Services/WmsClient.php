<?php

namespace App\Services;

use App\Contracts\SiteClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class WmsClient implements SiteClient
{
    public function __construct(
        private string $url,
        private string $token,
    ) {}

    public function get(string $path, array $query = []): string
    {
        return $this->send('GET', $path, $query);
    }

    public function post(string $path, array $data = []): string
    {
        return $this->send('POST', $path, $data);
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->baseUrl($this->url)
            ->timeout(15)
            ->withToken($this->token);
    }

    private function send(string $method, string $path, array $data): string
    {
        $options = $method === 'GET' ? ['query' => $data] : ['json' => $data];

        try {
            $response = $this->request()->send($method, $path, $options);
        } catch (ConnectionException) {
            return json_encode([
                'message' => __('index.wms.unreachable'),
                'status' => 503,
            ], JSON_UNESCAPED_UNICODE);
        }

        $result = [
            'body' => $response->json(),
            'status' => $response->status(),
        ];

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
