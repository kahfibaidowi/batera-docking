<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\KapalModel;
use App\Models\UserModel;
use App\Models\UserShipownerModel;
use App\Models\ProyekModel;
use App\Models\PerusahaanModel;

class PerusahaanController extends Controller
{
    public function gets_perusahaan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                'integer',
                'min:1'
            ],
            'q' =>[
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
        $perusahaan=PerusahaanModel::where("nama_perusahaan", "ilike", "%".$req['q']."%")
            ->orderBy("nama_perusahaan")
            ->paginate(trim($req['per_page']))
            ->toArray();

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$perusahaan['current_page'],
            'last_page'     =>$perusahaan['last_page'],
            'data'          =>$perusahaan['data']
        ]);
    }

    public function get_perusahaan(Request $request, $id)
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
        $req['id_perusahaan']=$id;
        $validation=Validator::make($req, [
            'id_perusahaan'     =>"required|exists:App\Models\PerusahaanModel,id_perusahaan"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $perusahaan=PerusahaanModel::where("id_perusahaan", $req['id_perusahaan'])->first();

        return response()->json([
            'data'  =>$perusahaan
        ]);
    }

    public function add_perusahaan(Request $request)
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
        $validation=Validator::make($req, [
            'nama_perusahaan'   =>"required",
            'merk_perusahaan'   =>"required",
            'alamat_perusahaan_1'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_1']))],
            'alamat_perusahaan_2'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_2']))],
            'telepon'   =>"required",
            'fax'       =>"required",
            'npwp'      =>"required",
            'email'     =>"required|email"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            //perusahaan
            PerusahaanModel::create([
                'nama_perusahaan'   =>$req['nama_perusahaan'],
                'merk_perusahaan'   =>$req['merk_perusahaan'],
                'alamat_perusahaan_1'   =>$req['alamat_perusahaan_1'],
                'alamat_perusahaan_2'   =>$req['alamat_perusahaan_2'],
                'telepon'   =>$req['telepon'],
                'fax'       =>$req['fax'],
                'npwp'      =>$req['npwp'],
                'email'     =>$req['email']
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function delete_perusahaan(Request $request, $id)
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
        $req['id_perusahaan']=$id;
        $validation=Validator::make($req, [
            'id_perusahaan'     =>"required|exists:App\Models\PerusahaanModel,id_perusahaan"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            //perusahaan
            PerusahaanModel::where("id_perusahaan", $req['id_perusahaan'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update_perusahaan(Request $request, $id)
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
        $req['id_perusahaan']=$id;
        $validation=Validator::make($req, [
            'id_perusahaan'     =>"required|exists:App\Models\PerusahaanModel,id_perusahaan",
            'nama_perusahaan'   =>"required",
            'merk_perusahaan'   =>"required",
            'alamat_perusahaan_1'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_1']))],
            'alamat_perusahaan_2'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_2']))],
            'telepon'   =>"required",
            'fax'       =>"required",
            'npwp'      =>"required",
            'email'     =>"required|email"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            //perusahaan
            PerusahaanModel::where("id_perusahaan", $req['id_perusahaan'])
                ->update([
                    'nama_perusahaan'   =>$req['nama_perusahaan'],
                    'merk_perusahaan'   =>$req['merk_perusahaan'],
                    'alamat_perusahaan_1'   =>$req['alamat_perusahaan_1'],
                    'alamat_perusahaan_2'   =>$req['alamat_perusahaan_2'],
                    'telepon'   =>$req['telepon'],
                    'fax'       =>$req['fax'],
                    'npwp'      =>$req['npwp'],
                    'email'     =>$req['email']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
