<?php

namespace App\Services;

use App\Http\Resources\MeliListProductResource;
use App\Http\Resources\ProductResource;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MercadoLibreService
{
    protected $client;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken;
    protected $refreshToken;

    protected $INVALID_ACCESS_TOKEN = 'invalid access token';

    public function __construct()
    {
        $this->client = new Client();
        $this->clientId = config('mercadolibre.client_id');
        $this->clientSecret = config('mercadolibre.client_secret');
        $this->accessToken = config('mercadolibre.access_token');
        $this->refreshToken = config('mercadolibre.refresh_token');
    }

    public function getUserDetails()
    {
        $response = $this->client->get('https://api.mercadolibre.com/users/me', [
            'query' => [
                'access_token' => $this->accessToken
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function searchProducts($query, $retry = true) {
        $accessToken = Cache::get('mercadolibre_access_token', $this->accessToken);

        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])
            ->withQueryParameters([
                'site_id' => 'MLA',
                'status' => 'active',
                'q' => urlencode($query),
            ])
            ->get('https://api.mercadolibre.com/products/search');

        $responseBody = json_decode($response->getBody(), true);

        if ($this->hasInvalidToken($responseBody)) {
            if ($retry) {
                $this->refreshAccessToken();
                return $this->searchProducts($query, false);
            }
            throw new \Exception('Could not refresh Mercado Libre token after retry.');
        }

        return MeliListProductResource::collection(collect($responseBody['results']));
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function getProductInformation($id, $retry = true): ProductResource {
        $accessToken = Cache::get('mercadolibre_access_token', $this->accessToken);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])
        ->get('https://api.mercadolibre.com/products/'. $id);

        $responseBody = json_decode($response->getBody(), true);
        if ($this->hasInvalidToken($responseBody)) {
            if ($retry) {
                $this->refreshAccessToken();
                return $this->getProductInformation($id, false);
            }
            throw new \Exception('Could not refresh Mercado Libre token after retry.');
        }

        return new ProductResource($responseBody);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    private function refreshAccessToken(): void {
        list($formParams, $headers) = $this->getParamsAndHeadersForRefreshRequest();

        $response = $this->executeRefreshRequest($headers, $formParams);

        if ($response->failed()) {
            sleep(5);
            $response = $this->executeRefreshRequest($headers, $formParams);
        }

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['access_token'])) {
                Cache::put('mercadolibre_access_token', $data['access_token'], 360);
            }
        } else {
            throw new Exception('Could not refresh mercado libre token');
        }
    }

    /**
     * @param array $headers
     * @param array $formParams
     * @return PromiseInterface|Response
     * @throws ConnectionException
     */
    private function executeRefreshRequest(array $headers, array $formParams): Response|PromiseInterface {
        return Http::withOptions(['verify' => false])
            ->asForm()
            ->withHeaders($headers)
            ->post('https://api.mercadolibre.com/oauth/token', $formParams);
    }

    /**
     * @return array
     */
    private function getParamsAndHeadersForRefreshRequest(): array {
        $formParams = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
        ];

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/x-www-form-urlencoded',
        ];
        return array($formParams, $headers);
    }

    /**
     * @param $responseBody
     * @return bool
     */
    public function hasInvalidToken($responseBody): bool {
        return isset($responseBody['message']) && $responseBody['message'] === $this->INVALID_ACCESS_TOKEN;
    }
}
