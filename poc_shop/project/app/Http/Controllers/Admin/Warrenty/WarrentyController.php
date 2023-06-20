<?php

namespace App\Http\Controllers\Admin\Warrenty;

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

class WarrentyController extends Controller
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
        //$list = $this->orderRepo->listOrders('created_at', 'desc');

        $list = OrderProducts::orderBy('id', 'desc')->where("customer_owner_id", "!=", "")->paginate(15);
        if (request()->has('q') && (request()->input('q') != "")) {
            $list = OrderProducts::where("blockchain_UUID", request()->input('q') ?? '')->orderBy('id', 'desc')->paginate(15);
        }

        foreach ($list as $warrenty) {
            $warrenty["customer"] = $this->customerRepo->findCustomerById(intval($warrenty->customer_owner_id));
            //dd($warrenty);
        }

        return view('admin.warrenty.list', ['orders' => $list]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $orderId
     * @return \Illuminate\Http\Response
     */
    public function show($orderId)
    {
        $orderProduct = OrderProducts::where('id', $orderId)
                                    ->orderBy('id')
                                    ->first();

        if($orderProduct){
            $getCustomer = Customer::where("id",$orderProduct->customer_owner_id)->first();
            $getProduct = Product::where("id",$orderProduct->product_id)->first();
            $getOrder = Order::where("id",$orderProduct->order_id)->first();

            return view('admin.warrenty.show', [
                'orderProduct' => $orderProduct,
                'product' => $getProduct,
                'customer' => $getCustomer,
                'user' => auth()->guard('employee')->user()
            ]);
        }else{
            return view('front.404');
        }

        
    }

    /**
     * @param $orderId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($orderId)
    {
        $orderProduct = OrderProducts::where('id', $orderId)
                                    ->orderBy('id')
                                    ->first();
        $getallCustomers = Customer::where("blockchain_nftid" , "!=" , "")->get();

        if($orderProduct){
            $getCustomer = Customer::where("id",$orderProduct->customer_owner_id)->first();
            $getProduct = Product::where("id",$orderProduct->product_id)->first();
            $getOrder = Order::where("id",$orderProduct->order_id)->first();

            return view('admin.warrenty.edit', [
                'orderProduct' => $orderProduct,
                'product' => $getProduct,
                'customer' => $getCustomer,
                'allCustomers' => $getallCustomers,
                'user' => auth()->guard('employee')->user()
            ]);
        }else{
            return view('front.404');
        }
    }

    public function status($orderId)
    {
        $orderProduct = OrderProducts::where('id', $orderId)
                                    ->orderBy('id')
                                    ->first();

        if($orderProduct->blockchain_nft_status == -1){
            $response = Http::get(env('XRPL_API_ENDPOINT')."/api/nft/status/".$orderProduct->blockchain_UUID);
            if ($response->successful()){
                $respObj = $response->json();
                if($respObj["Account"]){
                    $getCustomerData = Customer::where("id",$orderProduct->customer_owner_id)->first();
                    $orderProduct->blockchain_nft_status = 1;
                    $orderProduct->blockchain_nftid = $respObj["meta"]['nftoken_id'];
                    $orderProduct->blockchain_nft_owner_wallet_seed = $getCustomerData->blockchain_wallet_seed;
                    $orderProduct->blockchain_nft_owner_wallet_address = $respObj["Account"];
                    $orderProduct->save();
                    return redirect('/admin/warrenty')->with('message', 'Warrenty setup is completed.');
                }
            }

            return redirect('/admin/warrenty')->with('message', 'Warrenty setup is underprocess.');
        }

        if($orderProduct->blockchain_nft_status == 1){
            return redirect('/admin/warrenty')->with('message', 'Warrenty setup is completed.');
        }

        return redirect('/admin/warrenty')->with('message', 'Warrenty setup is underprocess.');
    }

    /**
     * @param UpdateOrderRequest $request
     * @param $orderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $orderId)
    {

        $orderProduct = OrderProducts::where('id', $orderId)
                                    ->orderBy('id')
                                    ->first();

        if($orderProduct){

            $getCustomerData = Customer::where("id",$orderProduct->customer_owner_id)->first();
            $getProductData = Product::where('id', $orderProduct->product_id)->first();

            if ($request->has('customer_id') && $request->input('customer_id') != null) {
                $getCustomerToTransfer = Customer::where("id",$request->input('customer_id'))->first();
            }

            if($getCustomerToTransfer){
                $response = Http::post(env('XRPL_API_ENDPOINT')."/api/transfer-nft", [
                    "wallet_seed" => $getCustomerData->blockchain_wallet_seed, 
                    "nft_id" => $orderProduct->blockchain_nftid, 
                    "nft_offer_price" => "10", 
                    "transfer_to_wallet_seed" => $getCustomerToTransfer->blockchain_wallet_seed,
                ]);
    
                if ($response->successful()){
                    $respObj = $response->json();
                    if($respObj["status"] == "ok"){
                        $orderProduct->blockchain_nft_status = -1;
                        $orderProduct->customer_owner_id = $getCustomerToTransfer->id;
                        $orderProduct->save();
                        return redirect()->route('admin.warrenty.edit', $orderId)->with('message', 'Warrenty setup is underprocess.');
                    }
                }
            }
            return redirect()->route('admin.warrenty.edit', $orderId)->with('message', 'Warrenty transfer could not process.');
        }else{
            return view('front.404');
        }


        // $order = $this->orderRepo->findOrderById($orderId);
        // $orderRepo = new OrderRepository($order);

        // if ($request->has('total_paid') && $request->input('total_paid') != null) {
        //     $orderData = $request->except('_method', '_token');
        // } else {
        //     $orderData = $request->except('_method', '_token', 'total_paid');
        // }

        // $orderRepo->updateOrder($orderData);

        // return redirect()->route('admin.warrenty.edit', $orderId)
        //     ->with('message', 'Update successful');
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
