<?php

namespace App\Http\Controllers;

use App\Exceptions\FrontApiException;
use App\Models\OcModel;
use App\Models\OcOrder;
use Illuminate\Support\Facades\DB;
use App\Services\HoroshopApiService;

class TestController extends Controller
{
    public function index(HoroshopApiService $api)
    {

        $orders = $api->getOrders([
            'status' => [1, 2], // например, только новые и в обработке
            'limit' => 10,
        ])['response']['orders'];

        // dd($orders);

        return view('test', compact('orders'));
    }
}

