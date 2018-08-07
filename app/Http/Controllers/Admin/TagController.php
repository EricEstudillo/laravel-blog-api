<?php

namespace App\Http\Controllers\Admin;

use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

class TagController extends Controller
{

    public function index()
    {
        $response = $this->requestToApi(url('/api/tags'));

        return response($response->getBody(), Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    private function requestToApi(string $url): ResponseInterface
    {
        $guzzle = new Client;
        $response = $guzzle->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . env('API_CLIENT_CREDENTIALS_TOKEN')
            ],
        ]);

        return $response;
    }
}
