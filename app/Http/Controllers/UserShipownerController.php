<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\UserShipownerModel;
use App\Models\UserModel;

class UserShipownerController extends Controller
{

    public function gets(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"required|numeric|min:1",
            'q'         =>[
                'regex:/^[\pL\s\-]+$/u',
                Rule::requiredIf(!isset($req['q']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $users_shipowner=UserShipownerModel::with("shipowner");
        //with user
        $users_shipowner=$users_shipowner->whereHas("shipowner", function($q) use($req){
            $q->where("nama_lengkap", "ilike", "%".$req['q']."%");
        });

        //order & paginate
        $users_shipowner=$users_shipowner
            ->orderByDesc("id_user_shipowner")
            ->paginate($req['per_page'])->toArray();

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$users_shipowner['current_page'],
            'last_page'     =>$users_shipowner['last_page'],
            'data'          =>$users_shipowner['data']
        ]);
    }

    public function get(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user_shipowner']=$id;
        $validation=Validator::make($req, [
            'id_user_shipowner' =>"required|exists:App\Models\UserShipownerModel,id_user_shipowner"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $user_shipowner=UserShipownerModel::with("shipowner")
            ->where("id_user_shipowner", $req['id_user_shipowner'])
            ->first()
            ->toArray();

        return response()->json([
            'data'  =>$user_shipowner
        ]);
    }

    public function delete(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user_shipowner']=$id;
        $validation=Validator::make($req, [
            'id_user_shipowner' =>"required|exists:App\Models\UserShipownerModel,id_user_shipowner"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        UserShipownerModel::where("id_user_shipowner", $req['id_user_shipowner'])
            ->delete();

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function add(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_user' =>[
                "required",
                Rule::exists("App\Models\UserModel")->where(function($query){
                    return $query->where("role", "shipowner");
                }),
                "unique:App\Models\UserShipownerModel,id_user"
            ],
            'kapal_tersisa' =>"required|integer|min:0"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            UserShipownerModel::create([
                'id_user'       =>$req['id_user'],
                'kapal_tersisa' =>$req['kapal_tersisa']
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user_shipowner']=$id;
        $validation=Validator::make($req, [
            'id_user_shipowner' =>"required|exists:App\Models\UserShipownerModel,id_user_shipowner",
            'kapal_tersisa' =>"required|integer|min:0"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            UserShipownerModel::where("id_user_shipowner", $req['id_user_shipowner'])
                ->update([
                    'kapal_tersisa' =>$req['kapal_tersisa']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
