@extends('layouts.admin.app')

@section('content')
    <!-- Main content -->
    <section class="content">

    @include('layouts.errors-and-messages')
    <!-- Default box -->
        @if($orders)
            <div class="box">
                <div class="box-body">
                    <h2>Warrenty</h2>
                    @include('layouts.search', ['route' => route('admin.warrenty.index')])
                    <table class="table">
                        <thead>
                            <tr>
                                <td class="col-md-2">ID</td>
                                <td class="col-md-3">Date</td>
                                <td class="col-md-3">Customer</td>
                                <td class="col-md-2">Product SKU</td>
                                <td class="col-md-2">BC Link</td>
                                <td class="col-md-2">Card</td>
                                <td class="col-md-2">Status</td>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->blockchain_UUID }}</td>
                                <td><a title="Show Warrenty" href="{{ route('admin.warrenty.show', $order->id) }}">{{ date('M d, Y h:i a', strtotime($order->updated_at)) }}</a></td>
                                <td>{{$order->customer->name}} ({{$order->customer->email}})</td>
                                <td>{{ $order->product_sku }}</td>
                                <td>
                                    @if($order->blockchain_nftid && ($order->blockchain_nft_status == "1"))
                                    <a title="Show Transaction" target="blank" href="https://testnet.xrpl.org/nft/{{$order->blockchain_nftid}}">Show XRPL Block</a>
                                    @else
                                    <a href="/admin/warrenty/{{$order->id}}/status" id="activate_warrenty_status">Check Status</a>
                                    @endif
                                </td>
                                <td>
                                    @if($order->blockchain_nftid && ($order->blockchain_nft_status == "1"))
                                    <a title="Show Transaction" target="blank" href="/warrenty/card/{{$order->blockchain_UUID}}">Show Card</a>
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td>
                                    @if ($order->blockchain_nft_status == 1)
                                    <p class="text-center" style="color: #ffffff; background-color: green">Active</p>
                                    @endif
                                    @if ($order->blockchain_nft_status == -1)
                                    <p class="text-center" style="color: #000; background-color: yellow">Processing</p>
                                    @endif
                                    @if ($order->blockchain_nft_status == 0)
                                    <p class="text-center" style="color: #000; background-color: gray">Pending</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    {{ $orders->links() }}
                </div>
            </div>
            <!-- /.box -->
        @endif

    </section>
    <!-- /.content -->
@endsection