<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
    </head>
    <body>
        
       <table>
            <thead>
                <tr>
                    <th>order_id</th>
                    <th>product.title</th>
                    <th>product.article</th>
                    <th>product.quantity</th>
                    <th>product.price</th>
                    <th>product.total_price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    @foreach ($order['products'] as $product)
                        <tr>
                            {{-- 
                                Переменная $loop->first доступна внутри цикла @foreach.
                                Она позволяет отобразить ID заказа с rowspan только для первой строки товара.
                            --}}
                            @if ($loop->first)
                                <td rowspan="{{ count($order['products']) }}">{{ $order['order_id'] }}</td>
                            @endif
                            
                            <td>{{ $product['title'] }}</td>
                            <td>{{ $product['article'] }}</td>
                            <td>{{ $product['quantity'] }}</td>
                            <td>{{ $product['price'] }}</td>
                            <td>{{ $product['total_price'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </body>
</html>

