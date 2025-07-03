<?php

namespace App\Http\Controllers;

use App\Exceptions\FrontApiException;
use App\Models\OcModel;
use App\Models\OcOrder;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function index () {

        $orders = OcModel::limit(10)->get();

        $orders = OcOrder::whereIn('order_id', function ($subQuery) {
            $subQuery->select('op.order_id')
                ->from('order_product as op')
                ->join('product as p', 'op.product_id', '=', 'p.product_id')
                ->whereNotNull('p.ean')
                ->where('p.ean', '!=', '');
        })->get();

        dump($orders);
        return view('test');
    }

    



}