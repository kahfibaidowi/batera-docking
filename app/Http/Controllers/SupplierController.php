<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\SupplierModel;
use App\Repository\SupplierRepo;

class SupplierController extends Controller
{

    public function add_supplier(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'nama_supplier' =>"required",
            'alamat'        =>[
                Rule::requiredIf(!isset($req['alamat']))
            ],
            'email'         =>"required|email",
            'no_hp'         =>[
                Rule::requiredIf(!isset($req['no_hp']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $supplier=(object)[];
        DB::transaction(function() use($req, $login_data, &$supplier){
            $create=SupplierModel::create([
                'nama_supplier' =>$req['nama_supplier'],
                'alamat'        =>$req['alamat'],
                'email'         =>$req['email'],
                'no_hp'         =>$req['no_hp']
            ]);
            $supplier=$create;
        });

        return response()->json([
            'status'=>"ok",
            'data'  =>$supplier
        ]);
    }

    public function update_supplier(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_supplier']=$id;
        $validation=Validator::make($req, [
            'id_supplier'   =>"required|exists:App\Models\SupplierModel,id_supplier",
            'nama_supplier' =>"required",
            'alamat'        =>[
                Rule::requiredIf(!isset($req['alamat']))
            ],
            'email'         =>"required|email",
            'no_hp'         =>[
                Rule::requiredIf(!isset($req['no_hp']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $supplier=(object)[];
        DB::transaction(function() use($req, $login_data, &$supplier){
            $update=SupplierModel::where("id_supplier", $req['id_supplier'])
                ->updateOrCreate([
                    'nama_supplier' =>$req['nama_supplier'],
                    'alamat'        =>$req['alamat'],
                    'email'         =>$req['email'],
                    'no_hp'         =>$req['no_hp']
                ]);
            $supplier=$update;
        });

        return response()->json([
            'status'=>"ok",
            'data'  =>$supplier
        ]);
    }

    public function delete_supplier(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_supplier']=$id;
        $validation=Validator::make($req, [
            'id_supplier'   =>"required|exists:App\Models\SupplierModel,id_supplier"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            SupplierModel::where("id_supplier", $req['id_supplier'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_supplier(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //condition
        $req['type']=isset($req['type'])?$req['type']:"";
        if($req['type']=="multiple"){
            return $this->gets_supplier_by_id($request);
        }
        else{
            return $this->gets_all_supplier($request);
        }
    }

    private function gets_all_supplier(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'director', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                "integer",
                "min:1"
            ],
            'q'         =>[
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
        $supplier=SupplierRepo::gets_supplier($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$supplier['current_page'],
            'last_page'     =>$supplier['last_page'],
            'data'          =>$supplier['data']
        ]);
    }

    private function gets_supplier_by_id(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'director', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'id_supplier'  =>"required|array"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $supplier=SupplierRepo::gets_supplier_by_id($req);

        return response()->json([
            'data'  =>$supplier
        ]);
    }

    public function get_supplier(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'director', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_supplier']=$id;
        $validation=Validator::make($req, [
            'id_supplier'   =>"required|exists:App\Models\SupplierModel,id_supplier"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $supplier=SupplierRepo::get_supplier($req['id_supplier']);

        return response()->json([
            'data'  =>$supplier
        ]);
    }
}
