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

    public function getToken(): string
    {
        return Cache::remember('horoshop_api_token', now()->addMinutes(60), function () {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json', 
            ])
            ->post("{$this->baseUrl}/auth/", [
                'login' => $this->login,
                'password' => $this->password,
            ])->json();

            if ($response['status'] === 'OK') {
                 $token = $response['response']['token'];
                return $token;
            }

            throw new \Exception("Horoshop API auth failed: " . $response['response']['message']);
        });
    }

    public function getOrders(array $params = [])
    {
        $token = $this->getToken();

        // добавляем токен в тело запроса
        $body = array_merge(['token' => $token], $params);

        // dd($body);
        return Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/orders/get/", $body)
            ->json();
    }

}
