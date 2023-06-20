<?php

namespace App\Http\Controllers\Front;

use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Shop\Orders\Order;
use App\Shop\Orders\Transformers\OrderTransformable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Shop\Customers\Customer;
use App\OrderProducts;
use App\Shop\Products\Product;

class AccountsController extends Controller
{
    use OrderTransformable;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var CourierRepositoryInterface
     */
    private $courierRepo;

    /**
     * AccountsController constructor.
     *
     * @param CourierRepositoryInterface $courierRepository
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CourierRepositoryInterface $courierRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepo = $customerRepository;
        $this->courierRepo = $courierRepository;
    }

    public function index()
    {
        $customer = $this->customerRepo->findCustomerById(auth()->user()->id);

        $customerRepo = new CustomerRepository($customer);

        if($customer->blockchain_nft_status == -1){
            $response = Http::get(env('XRPL_API_ENDPOINT')."/api/nft/status/".$customer->blockchain_UUID);
            if ($response->successful()){
                $respObj = $response->json();
                if($respObj && $respObj["Account"]){
                    $getCustomerData = Customer::where("id",$customer->id)->first();
                    $getCustomerData->blockchain_nft_status = 1;
                    $getCustomerData->blockchain_signed = 1;
                    $getCustomerData->blockchain_nftid = $respObj["meta"]['nftoken_id'];
                    $getCustomerData->save();
                }
            }
        }

        $orders = $customerRepo->findOrders(['*'], 'created_at');

        $orders->transform(function (Order $order) {
            return $this->transformOrder($order);
        });

        $orders->load('products');

        $addresses = $customerRepo->findAddresses();

        return view('front.accounts', [
            'customer' => $customer,
            'orders' => $this->customerRepo->paginateArrayResults($orders->toArray(), 15),
            'addresses' => $addresses
        ]);
    }

    public function generateWarrentyCard($id)
    {
        $getOrderProduct = OrderProducts::where('blockchain_UUID', $id)->first();

        if($getOrderProduct){
            $getCustomer = Customer::where("id",$getOrderProduct->customer_owner_id)->first();
            $getProduct = Product::where("id",$getOrderProduct->product_id)->first();
            $getOrder = Order::where("id",$getOrderProduct->order_id)->first();
            return view('front.cert', [
                'warranty' => $getOrderProduct,
                'customer' => $getCustomer,
            ]);
        }else{
            return view('front.404');
        }
    }
}
