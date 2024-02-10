<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\{ConnectException, TransferException};

class HttpService
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 15.0]);
    }

    public function checkUrl(string $url): ?array
    {
        try {
            $response = $this->http->get($url);
        } catch (ConnectException | TransferException) {
            return null;
        }

        return [
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $response->getBody()->getContents(),
        ];
    }
}
