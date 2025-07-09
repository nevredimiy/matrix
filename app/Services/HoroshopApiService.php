<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HoroshopApiService
{
    protected string $baseUrl;
    protected string $login;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.horoshop.base_url');
        $this->login = config('services.horoshop.login');
        $this->password = config('services.horoshop.password');
    }

    public function getOrders(array $params = [])
    {
        try {
            return $this->requestOrders($params);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'UNAUTHORIZED') || str_contains($e->getMessage(), 'AUTHORIZATION_ERROR')) {
                // сбрасываем кэшированный токен
                Cache::forget('horoshop_api_token');

                // повторяем запрос заново с новым токеном
                return $this->requestOrders($params);
            }

            throw $e; // если другая ошибка — пробрасываем
        }
    }

    private function requestOrders(array $params = [])
    {
        $token = $this->getToken();

        $body = array_merge(['token' => $token], $params);

        $response = Http::asJson()
            ->post("{$this->baseUrl}/orders/get/", $body)
            ->json();

        if (isset($response['status']) && in_array($response['status'], ['UNAUTHORIZED', 'AUTHORIZATION_ERROR'])) {
            throw new \Exception($response['status']);
        }

        return $response;
    }

    public function getToken(): string
    {
        return Cache::remember('horoshop_api_token', now()->addMinutes(60), function () {
            $response = Http::asJson()->post("{$this->baseUrl}/auth/", [
                'login' => $this->login,
                'password' => $this->password,
            ])->json();

            if ($response['status'] === 'OK') {
                return $response['response']['token'];
            }

            throw new \Exception("Horoshop API auth failed: " . $response['response']['message']);
        });
    }

    public function call(string $method, array $params = [])
    {
        try {
            return $this->makeRequest($method, $params);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'UNAUTHORIZED') || str_contains($e->getMessage(), 'AUTHORIZATION_ERROR')) {
                Cache::forget('horoshop_api_token');
                return $this->makeRequest($method, $params); // повтор
            }

            throw $e;
        }
    }

    private function makeRequest(string $method, array $params = [])
    {
        $token = $this->getToken();

        $body = array_merge(['token' => $token], $params);

        $response = Http::asJson()
            ->post("{$this->baseUrl}/{$method}/", $body)
            ->json();

        if (isset($response['status']) && in_array($response['status'], ['UNAUTHORIZED', 'AUTHORIZATION_ERROR'])) {
            throw new \Exception($response['status']);
        }

        return $response;
    }
}
