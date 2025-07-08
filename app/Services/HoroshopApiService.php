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
                'Content-Type' => 'application/json', // <-- ОБЯЗАТЕЛЬНО
            ])
            ->post("{$this->baseUrl}/auth/", [
                'login' => $this->login,
                'password' => $this->password,
            ])->json();

            if ($response['status'] === 'OK') {
                 $token = $response['response']['token'];
                dd($token); // проверь, что он корректный
                return $token;
            }

            throw new \Exception("Horoshop API auth failed: " . $response['response']['message']);
        });
    }

    public function getOrders($params = [])
    {
        $token = $this->getToken();

        return Http::withHeaders([
                'X-Auth-Token' => $token, 
            ])
            ->get("{$this->baseUrl}/orders/get_available_statuses", $params)
            ->json();
    }
}
