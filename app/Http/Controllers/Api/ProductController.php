<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request){
        $products = Product::with('user')->where('user_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Product data loaded successfully',
            'data' => $products
        ]);
    }

    public function getProductByUserId(Request $request){
        $products = Product::with('user')->where('user_id', $request->user_id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Product data loaded successfully',
            'data' => $products
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'is_available' => 'required|boolean',
            'is_favorite' => 'required|boolean',
            'image' => 'required|image',
        ]);

        $user = $request->user();
        $request->merge(['user_id' => $user->id]);

        $data = $request->all();

        // check photo is upload
        if($request->hasFile('image')){
            $photo = $request->file('image');
            $photo_name = time().'.'.$photo->getClientOriginalExtension();
            $photo->move(public_path('product'), $photo_name);
            $data['image'] = $photo_name;
        }

        $product = Product::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product
        ]);
    }

    public function update(Request $request, $id){

        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'is_available' => 'required|boolean',
            'is_favorite' => 'required|boolean',
            'image' => 'image'
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        $data = $request->all();

        $product->update($data);

        // check photo is upload
        if($request->hasFile('image')){
            // delete photo
            if($product->image){
                $path = public_path('product/'.$product->image);
                unlink($path);
            }

            $photo = $request->file('image');
            $photo_name = time().'.'.$photo->getClientOriginalExtension();
            $photo->move(public_path('product'), $photo_name);
            $product->image = $photo_name;
            $product->save($data);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product
        ]);

    }

    public function destroy($id){
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        // delete photo
        if($product->image){
            $path = public_path('product/'.$product->image);
            unlink($path);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }


}
