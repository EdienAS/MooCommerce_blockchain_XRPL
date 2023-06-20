@extends('layouts.admin.app')

@section('content')
    <!-- Main content -->
    <section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-6">
                        <h2>
                            <a href="{{ route('admin.customers.show', $customer->id) }}">{{$customer->name}}</a> <br />
                            <small>{{$customer->email}}</small> <br />
                            <small>reference: <strong>{{$orderProduct->blockchain_UUID}}</strong></small>
                        </h2>
                    </div>
                    <div class="col-md-3 col-md-offset-3">
                        <h2><a title="Show Transaction" target="blank" href="/warrenty/card/{{$orderProduct->blockchain_UUID}}" class="btn btn-primary btn-block">Download Card</a></h2>
                    </div>
                </div>
            </div>
        </div>
        
        @if($orderProduct)
            <div class="box">
                    <div class="box-body">
                        <h4> <i class="fa fa-gift"></i> Product</h4>
                        <table class="table">
                            <thead>
                            <th class="col-md-2">SKU</th>
                            <th class="col-md-2">Name</th>
                            <th class="col-md-2">Customer</th>
                            <th class="col-md-2">Quantity</th>
                            <th class="col-md-2">Price</th>
                            <th class="col-md-2">BC Link</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $orderProduct->product_sku }}</td>
                                    <td>{{ $orderProduct->product_name }}</td>
                                    <td>
                                        {{ $customer->name }} | {{ $customer->email }}
                                    </td>
                                    <td>{{ $orderProduct->quantity }}</td>
                                    <td>{{ $orderProduct->product_price }}</td>
                                    <td>
                                        @if($orderProduct->blockchain_nftid)
                                        <a title="Show Transaction" target="blank" href="https://testnet.xrpl.org/nft/{{$orderProduct->blockchain_nftid}}">Show XRPL Block</a>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
                
            <!-- /.box -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.warrenty.index') }}" class="btn btn-default">Back</a>
                    @if($user->hasPermission('update-order'))<a href="{{ route('admin.warrenty.edit', $orderProduct->id) }}" class="btn btn-primary">Edit</a>@endif
                </div>
            </div>

    </section>
    <!-- /.content -->
@endsection