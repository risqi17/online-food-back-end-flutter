<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // create new order
    public function store(Request $request){
        $request->validate([
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|integer|exists:products,id',
            'order_items.*.quantity' => 'required|integer',
            'restaurant_id' => 'required|integer|exists:users,id',
            'shipping_cost' => 'required|integer'
        ]);


        $total_price = 0;
        foreach ($request->order_items as $key => $item) {
            $product = Product::find($item['product_id']);
            $total_price += $product->price * $item['quantity'];
        }

        $user = $request->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $shipping_address = $user->address;
        $data['shipping_address'] = $shipping_address;
        $shipping_latlong = $user->latlong;
        $data['shipping_latlong'] = $shipping_latlong;
        $data['status'] = 'pending';
        $data['total_price'] = $total_price;
        $data['total_bill'] = $total_price + $request->shipping_cost;

        $order = Order::create($data);

        foreach ($request->order_items as $key => $item) {
            $product = Product::find($item['product_id']);
            $order_item = new OrderItem([
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'product_id' => $product->id,
                'order_id' => $order->id
            ]);

            $order->orderItems()->save($order_item);
        }

        return response()->json([
            'success' => 'success',
            'message' => 'Order created successfully',
            'data' => $order
        ]);
    }

    //update purchase status
    public function updatePurchaseStatus(Request $request, $id){

        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $order = Order::find($id);
        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    //order history
    public function orderHistory(Request $request){
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->get();

        return response()->json([
            'success' => 'success',
            'message' => 'Order history fetched successfully',
            'data' => $orders
        ]);
    }

    //cancel order
    public function cancelOrder(Request $request, $id){
        $order = Order::find($id);
        $order->update(['status' => 'cancelled']);

        return response()->json([
            'success' => 'success',
            'message' => 'Order cancelled successfully',
            'data' => $order
        ]);
    }
    
    //get order by status for restaurant
    public function getOrderByStatus(Request $request){
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $user = $request->user();
        $orders = Order::where('restaurant_id', $user->id)->where('status', $request->status)->get();

        return response()->json([
            'success' => 'success',
            'message' => 'Order history fetched successfully',
            'data' => $orders
        ]);
    }

    //update order status for restaurant
    public function updateOrderStatus(Request $request, $id){
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,ready_for_delivery,preparing',
        ]);

        $order = Order::find($id);
        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    //get orders by status for driver
    public function getOrdersByStatusDriver(Request $request){
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,ready_for_delivery,preparing',
        ]);

        $user = $request->user();
        $orders = Order::where('driver_id', $user->id)->where('status', $request->status)->get();

        return response()->json([
            'success' => 'success',
            'message' => 'Order history fetched successfully',
            'data' => $orders
        ]);
    }

    //get order status ready for delivery
    public function getOrderStatusReadyForDelivery(Request $request){
        $orders = Order::with('restaurant')->where('status', 'ready_for_delivery')->get();

        return response()->json([
            'success' => 'success',
            'message' => 'Order history fetched successfully',
            'data' => $orders
        ]);
    }

    //update status for driver
    public function updateStatusForDriver(Request $request, $id){
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,on_the_way,delivered',
        ]);

        $order = Order::find($id);
        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

}
