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
        <div class="box">
            <div class="box-body">
                <h4> <i class="fa fa-shopping-bag"></i> Warrenty Information</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <td class="col-md-3">Date</td>
                            <td class="col-md-3">Customer</td>
                            <td class="col-md-3">Current Owner</td>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ date('M d, Y h:i a', strtotime($orderProduct['updated_at'])) }}</td>
                        <td><a href="{{ route('admin.customers.show', $customer->id) }}">{{ $customer->name }}</a></td>
                        
                        <td>
                            <form action="{{ route('admin.warrenty.update', $orderProduct->id) }}" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="post">
                                <label for="customer_id" class="hidden">Update status</label>
                                <div class="input-group">
                                    <select name="customer_id" id="customer_id" class="form-control select2">
                                        @foreach ($allCustomers as $customer)
                                        <option @if($customer->id == $orderProduct->customer_owner_id) selected="selected" @endif value="{{ $customer->id }}">{{ $customer->email }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-btn"><button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger">Transfer</button></span>
                                </div>
                            </form>
                        </td>
                    </tr>
                    </tbody>
                    
                </table>
            </div>
            <!-- /.box-body -->
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
                    <a href="{{ route('admin.warrenty.show', $orderProduct->id) }}" class="btn btn-default">Back</a>
                </div>
            </div>

    </section>
    <!-- /.content -->
@endsection
@section('js')
    <script type="text/javascript">
        $(document).ready(function () {
            let osElement = $('#order_status_id');
            osElement.change(function () {
                if (+$(this).val() === 1) {
                    $('input[name="total_paid"]').fadeIn();
                } else {
                    $('input[name="total_paid"]').fadeOut();
                }
            });
        })
    </script>
@endsection