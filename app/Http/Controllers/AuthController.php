<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //

    public function _construct(){
        $this->middleware('auth:api',['except'=>['login','register']]);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|string|unique:users',
            'password'=>'required|string|confirmed|min:6'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }else{
            $user = User::create(array_merge(
                $validator->validated(),
                ['password'=>bcrypt($request->password)]
            ));

            return response()->json([
                'message'=> 'User succussfully registerd',
                'user'=> $user
            ],201);
        }

    }

    public function login(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|string',
            'password'=>'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        if(!$token=auth()->attempt($validator->validated())){
            return response()->json(['error'=>'unauthorized'],401);
        }

        return $this->createNewToken($token);
    }

    public function createNewToken($token){
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()->getTTL()*60,
            'user'=>auth()->user()
        ]);
    }

    public function profile(){
        return response()->json(auth()->user());
    }

    public function logout(){
        auth()->logout();

        return response()->json([
            'message'=> 'User succussfully logged out'
        ]);
    }
}
