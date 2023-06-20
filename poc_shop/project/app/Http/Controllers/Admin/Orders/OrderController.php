<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Addresses\Transformations\AddressTransformable;
use App\Shop\Couriers\Courier;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Customers\Customer;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Orders\Order;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Orders\Requests\UpdateOrderRequest;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\OrderProducts;
use Illuminate\Support\Facades\Http;
use App\Shop\Products\Product;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use AddressTransformable;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @var CourierRepositoryInterface
     */
    private $courierRepo;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var OrderStatusRepositoryInterface
     */
    private $orderStatusRepo;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CourierRepositoryInterface $courierRepository,
        CustomerRepositoryInterface $customerRepository,
        OrderStatusRepositoryInterface $orderStatusRepository
    ) {
        $this->orderRepo = $orderRepository;
        $this->courierRepo = $courierRepository;
        $this->customerRepo = $customerRepository;
        $this->orderStatusRepo = $orderStatusRepository;

        $this->middleware(['permission:update-order, guard:employee'], ['only' => ['edit', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = $this->orderRepo->listOrders('created_at', 'desc');

        if (request()->has('q')) {
            $list = $this->orderRepo->searchOrder(request()->input('q') ?? '');
        }

        $orders = $this->orderRepo->paginateArrayResults($this->transFormOrder($list), 10);

        return view('admin.orders.list', ['orders' => $orders]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $orderId
     * @return \Illuminate\Http\Response
     */
    public function show($orderId)
    {
        $order = $this->orderRepo->findOrderById($orderId);

        $orderRepo = new OrderRepository($order);
        $order->courier = $orderRepo->getCouriers()->first();
        $order->address = $orderRepo->getAddresses()->first();
        $items = $orderRepo->listOrderedProducts();

        return view('admin.orders.show', [
            'order' => $order,
            'items' => $items,
            'customer' => $this->customerRepo->findCustomerById($order->customer_id),
            'currentStatus' => $this->orderStatusRepo->findOrderStatusById($order->order_status_id),
            'payment' => $order->payment,
            'user' => auth()->guard('employee')->user()
        ]);
    }

    /**
     * @param $orderId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($orderId)
    {
        $order = $this->orderRepo->findOrderById($orderId);

        $orderRepo = new OrderRepository($order);
        $order->courier = $orderRepo->getCouriers()->first();
        $order->address = $orderRepo->getAddresses()->first();
        $items = $orderRepo->listOrderedProducts();
        
        foreach ($items as $item) {
            $orderProduct = OrderProducts::where('order_id', $orderId)
                                    ->where('product_sku', $item->sku)
                                    ->orderBy('id')
                                    ->first();
            $item->blockchain_nft_status = $orderProduct->blockchain_nft_status;
            $item->blockchain_UUID = $orderProduct->blockchain_UUID;
        }
        return view('admin.orders.edit', [
            'statuses' => $this->orderStatusRepo->listOrderStatuses(),
            'order' => $order,
            'items' => $items,
            'customer' => $this->customerRepo->findCustomerById($order->customer_id),
            'currentStatus' => $this->orderStatusRepo->findOrderStatusById($order->order_status_id),
            'payment' => $order->payment,
            'user' => auth()->guard('employee')->user()
        ]);
    }

    public function warrenty_check($orderId, $itemSKU)
    {
        $order = $this->orderRepo->findOrderById($orderId);

        $orderProduct = OrderProducts::where('order_id', $orderId)
                                    ->where('product_sku', $itemSKU)
                                    ->orderBy('id')
                                    ->first();
                                    
        
        if($orderProduct->blockchain_nft_status == -1){
            $response = Http::get(env('XRPL_API_ENDPOINT')."/api/nft/status/".$orderProduct->blockchain_UUID);
            if ($response->successful()){
                $respObj = $response->json();
                if($respObj["Account"]){
                    $getCustomerData = $this->customerRepo->findCustomerById($order->customer_id);
                    $orderProduct->blockchain_nft_status = 1;
                    $orderProduct->customer_owner_id = $order->customer_id;
                    $orderProduct->blockchain_nftid = $respObj["meta"]['nftoken_id'];
                    $orderProduct->blockchain_nft_owner_wallet_seed = $getCustomerData->blockchain_wallet_seed;
                    $orderProduct->blockchain_nft_owner_wallet_address = $respObj["Account"];
                    $orderProduct->save();
                    return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup is completed.');
                }
            }

            return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup is underprocess.');
        }

        if($orderProduct->blockchain_nft_status == 1){
            return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup is completed.');
        }

        return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup is underprocess.');
    }

    public function warrenty_activate($orderId, $itemSKU)
    {
        $order = $this->orderRepo->findOrderById($orderId);

        $orderProduct = OrderProducts::where('order_id', $orderId)
                                    ->where('product_sku', $itemSKU)
                                    ->orderBy('id')
                                    ->first();
                                    
        
        if($orderProduct->blockchain_nft_status == 0){
            $setUUID = Str::uuid();
            $getCustomerData = $this->customerRepo->findCustomerById($order->customer_id);

            $getProductData = Product::where('id', $orderProduct->product_id)->first();

            $response = Http::post(env('XRPL_API_ENDPOINT')."/api/mint-nft", [
                "wallet_seed" => $getCustomerData->blockchain_wallet_seed, 
                "fee" => "None", 
                "transfer_fee" => "10", 
                "metadata" => [
                    "product_sku" => $orderProduct->product_sku,
                    "product_name" => $orderProduct->product_name,
                    "quantity" => $orderProduct->quantity,
                    "product_price" => $orderProduct->product_price
                ], 
                "url" => request()->getHost().'/'.$getProductData->slug,
                "uuid" => $setUUID
            ]);

            if ($response->successful()){
                $respObj = $response->json();
                if($respObj["status"] == "ok"){
                    $orderProduct->blockchain_nft_status = -1;
                    $orderProduct->blockchain_UUID = $setUUID;
                    $orderProduct->customer_owner_id = $order->customer_id;
                    $orderProduct->save();
                    return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup is underprocess.');
                }
            }

            return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup unsuccessful');
        }

        if($orderProduct->blockchain_nft_status == -1){
            return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup is underprocess.');
        }

        return redirect()->route('admin.orders.edit', $orderId)->with('message', 'Warrenty setup unsuccessful');
    }

    /**
     * @param UpdateOrderRequest $request
     * @param $orderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateOrderRequest $request, $orderId)
    {
        $order = $this->orderRepo->findOrderById($orderId);
        $orderRepo = new OrderRepository($order);

        if ($request->has('total_paid') && $request->input('total_paid') != null) {
            $orderData = $request->except('_method', '_token');
        } else {
            $orderData = $request->except('_method', '_token', 'total_paid');
        }

        $orderRepo->updateOrder($orderData);

        return redirect()->route('admin.orders.edit', $orderId)
            ->with('message', 'Update successful');
    }

    /**
     * Generate order invoice
     *
     * @param int $id
     * @return mixed
     */
    public function generateInvoice(int $id)
    {
        $order = $this->orderRepo->findOrderById($id);

        foreach ($order->products as $orderedItem) {
            $orderProduct = OrderProducts::where('order_id', $id)
                                    ->where('product_sku', $orderedItem->sku)
                                    ->orderBy('id')
                                    ->first();
            $orderedItem["blockchain_UUID"] = $orderProduct->blockchain_UUID;
        }

        $data = [
            'order' => $order,
            'products' => $order->products,
            'customer' => $order->customer,
            'courier' => $order->courier,
            'address' => $this->transformAddress($order->address),
            'status' => $order->orderStatus,
            'payment' => $order->paymentMethod
        ];

        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('invoices.orders', $data)->stream();
        return $pdf->stream();
    }

    /**
     * @param Collection $list
     * @return array
     */
    private function transFormOrder(Collection $list)
    {
        $courierRepo = new CourierRepository(new Courier());
        $customerRepo = new CustomerRepository(new Customer());
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus());

        return $list->transform(function (Order $order) use ($courierRepo, $customerRepo, $orderStatusRepo) {
            $order->courier = $courierRepo->findCourierById($order->courier_id);
            $order->customer = $customerRepo->findCustomerById($order->customer_id);
            $order->status = $orderStatusRepo->findOrderStatusById($order->order_status_id);
            return $order;
        })->all();
    }
}
