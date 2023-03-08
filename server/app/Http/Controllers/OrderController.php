<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $client;

    /**
     * Create a new OrderController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->client = new Client();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $order = Order::all();
        return $this->resSuccess($order, [
            200, 'Order List'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data_order = $request->except(['height', 'length', 'width', 'items']);
        $data_orderItems = $request->items;

        $validator_1 = Validator::make($request->all(), [
            'user_id' => 'required|exists:users',
            'service_type_id' => 'required|integer',
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'productFee' => 'required|numeric|min:0',
            'shipFee' => 'required|numeric|min:0',
            'status' => 'required',
            'items' => 'required',
        ]);
        if ($validator_1->fails()) {
            return $this->resValidator([
                400, $validator_1->errors(),
            ]);
        }

        foreach ($data_orderItems as $data_orderItem) {
            $validator_2 = Validator::make($data_orderItem, [
                'book_id' => 'required|exists:books',
                'quantity' => 'required|integer',
                'price' => 'required|integer',
            ]);
            if ($validator_2->fails()) {
                return $this->resValidator([
                    400, $validator_2->errors(),
                ]);
            }
        }

        $order = Order::create(
            array_merge($data_order, [
                'order_id' => $this->client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
            ])
        );

        foreach ($data_orderItems as $data_orderItem) {
            $data_orderItem['order_id'] = $order->order_id;
            $book_countInStock_old = Book::select('countInStock')->where('book_id', '=', $data_orderItem['book_id'])->get();
            foreach ($book_countInStock_old as $val)
                Book::where('book_id', '=', $data_orderItem['book_id'])->update(['countInStock' => $val['countInStock'] - 1]);
            $order->orderItems()->create($data_orderItem);
        }
        $order->orderItems;

        return $this->resSuccess($order, [
            201, 'Order created successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order->orderItems;
        return $this->resSuccess($order, [
            200, 'Order found successfully'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $data = $request->except(['order_id']);
        $data_orderItems = $request->items;

        $validator_1 = Validator::make($request->all(), [
            'order_code' => 'required',
            'user_id' => 'required|exists:users',
            'service_type_id' => 'required|integer',
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'productFee' => 'required|numeric|min:0',
            'shipFee' => 'required|numeric|min:0',
            'status' => 'required',
            'items' => 'required',
        ]);
        if ($validator_1->fails()) {
            return $this->resValidator([
                400, $validator_1->errors(),
            ]);
        }

        foreach ($data_orderItems as $data_orderItem) {
            $validator_2 = Validator::make($data_orderItem, [
                'book_id' => 'required|exists:books',
                'quantity' => 'required|integer',
                'price' => 'required|integer',
            ]);
            if ($validator_2->fails()) {
                return $this->resValidator([
                    400, $validator_2->errors(),
                ]);
            }
        }
        foreach ($data_orderItems as $data_orderItem) {
            $order->orderItems()->where([
                ['order_id', '=', $order->order_id],
                ['book_id', '=', $data_orderItem['book_id']],
            ])->update($data_orderItem);
        }

        if (($order['status'] == "Unprocessed") || ($order['status'] == "Processed" && $data['status_temp'] == "ready_to_pick")) {
            switch ($data['status']) {
                case ('Cancel'):
                    foreach ($data_orderItems as $data_orderItem) {
                        $book_countInStock_old = Book::select('countInStock')->where('book_id', '=', $data_orderItem['book_id'])->get();
                        foreach ($book_countInStock_old as $val) {
                            Book::where('book_id', '=', $data_orderItem['book_id'])->update(['countInStock' => $val['countInStock'] + $data_orderItem['quantity']]);
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        $order->update($data);
        $order->orderItems;
        return $this->resSuccess($order, [
            200, 'Order updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
