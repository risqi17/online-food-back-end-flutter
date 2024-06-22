<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function userRegister(Request $request){

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'phone' => 'required|string',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($request['password']);
        $data['roles'] = 'user';

        $user = User::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $user
        ]);
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'The provided credentials are incorrect.',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login success',
            'data' => [
                'user' => $user,
                'token' => $token
            ],
        ]);
    }

    // logout
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout success'
        ]);
    }

    // restaurant register
    public function restaurantRegister(Request $request){
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'phone' => 'required|string',
            'restaurant_name' => 'required|string',
            'restaurant_address' => 'required|string',
            'photo' => 'required|image',
            'latlong' => 'required|string'
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'restaurant';

        $user = User::create($data);
        
        // check photo is upload
        if($request->hasFile('photo')){
            $photo = $request->file('photo');
            $photo_name = time().'.'.$photo->getClientOriginalExtension();
            $photo->move(public_path('images'), $photo_name);
            $user->photo = $photo_name;
            $user->save($data);
        }
        

        return response()->json([
            'status' => 'success',
            'message' => 'Restaurant registered successfully',
            'data' => $user
        ]);
    }

    // driver register
    public function driverRegister(Request $request){
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'phone' => 'required|string',
            'license_plate' => 'required|string',
            'photo' => 'required|image',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'driver';

        $user = User::create($data);

        // check photo is upload
        if($request->hasFile('photo')){
            $photo = $request->file('photo');
            $photo_name = time().'.'.$photo->getClientOriginalExtension();
            $photo->move(public_path('images'), $photo_name);
            $user->photo = $photo_name;
            $user->save($data);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Driver registered successfully',
            'data' => $user
        ]);
    }

    // update latlong
    public function updateLatLong(Request $request){
        $request->validate([
            'latlong' => 'required|string',
        ]);

        $user = $request->user();
        $user->latlong = $request->latlong;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Latlong updated successfully',
            'data' => $user
        ]);
    }

    // get all restaurant
    public function getAllRestaurant(){
        $restaurants = User::where('roles', 'restaurant')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Get all restaurant successfully',
            'data' => $restaurants
        ]);
    }
}
