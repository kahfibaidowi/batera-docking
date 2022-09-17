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

class HomeController extends Controller
{

    public function gets_vessel(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //SUCCESS
        $kapal=KapalModel::with("owner");
        //shipowner
        if($login_data['role']=="shipowner"){
            $kapal=$kapal->where("id_user", $login_data['id_user']);
        }
        //get data
        $kapal=$kapal->orderByDesc("id_kapal")
            ->get()->toArray();

        return response()->json([
            'data'  =>$kapal
        ]);
    }

    public function get_vessel(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_kapal']=$id;
        $validation=Validator::make($req, [
            'id_kapal'  =>"required|exists:App\Models\KapalModel,id_kapal"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $kapal=KapalModel::with("owner")
            ->where("id_kapal", $req['id_kapal'])
            ->orderByDesc("id_kapal")
            ->first()->toArray();

        return response()->json([
            'data'  =>$kapal
        ]);
    }

    public function add_vessel(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'id_user'   =>[
                'required',
                Rule::exists("App\Models\UserModel")->where(function($query){
                    return $query->where("role", "shipowner");
                }),
                function($attr, $value, $fail){
                    $v=UserShipownerModel::where("id_user", $value);
                    if($v->count()>0){
                        $vr=$v->first();
                        if($vr['kapal_tersisa']==0){
                            return $fail("limit kapal runs out");
                        }
                    }
                    return true;
                }
            ],
            'nama_kapal'=>"required",
            'foto'      =>[
                'required',
                'ends_with:.jpg,.png,.jpeg',
                function($attr, $value, $fail)use($req){
                    if(is_image_file($req['foto'])){
                        return true;
                    }
                    return $fail($attr." image not found");
                }
            ],
            'nama_perusahaan'   =>"required",
            'merk_perusahaan'   =>"required",
            'alamat_perusahaan_1'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_1']))],
            'alamat_perusahaan_2'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_2']))],
            'telepon'   =>"required",
            'faximile'  =>"required",
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
            //kapal
            KapalModel::create([
                'id_user'   =>$req['id_user'],
                'nama_kapal'=>$req['nama_kapal'],
                'foto'      =>$req['foto'],
                'nama_perusahaan'   =>$req['nama_perusahaan'],
                'merk_perusahaan'   =>$req['merk_perusahaan'],
                'alamat_perusahaan_1'   =>$req['alamat_perusahaan_1'],
                'alamat_perusahaan_2'   =>$req['alamat_perusahaan_2'],
                'telepon'   =>$req['telepon'],
                'faximile'  =>$req['faximile'],
                'npwp'      =>$req['npwp'],
                'email'     =>$req['email']
            ]);

            //user shipowner
            UserShipownerModel::where("id_user", $req['id_user'])
                ->update([
                    'kapal_tersisa' =>DB::raw("kapal_tersisa-1")
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function delete_vessel(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_kapal']=$id;
        $validation=Validator::make($req, [
            'id_kapal'  =>"required|exists:App\Models\KapalModel,id_kapal"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            //kapal
            KapalModel::where("id_kapal", $req['id_kapal'])->delete();

            //user shipowner
            UserShipownerModel::where("id_user", $kapal['id_user'])
                ->update([
                    'kapal_tersisa' =>DB::raw("kapal_tersisa+1")
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update_vessel(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_kapal']=$id;
        $validation=Validator::make($req, [
            'id_kapal'  =>"required|exists:App\Models\KapalModel,id_kapal",
            'nama_kapal'=>"required",
            'foto'      =>[
                'required',
                'ends_with:.jpg,.png,.jpeg',
                function($attr, $value, $fail)use($req){
                    if(is_image_file($req['foto'])){
                        return true;
                    }
                    return $fail($attr." image not found");
                }
            ],
            'nama_perusahaan'   =>"required",
            'merk_perusahaan'   =>"required",
            'alamat_perusahaan_1'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_1']))],
            'alamat_perusahaan_2'   =>[Rule::requiredIf(!isset($req['alamat_perusahaan_2']))],
            'telepon'   =>"required",
            'faximile'  =>"required",
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
            KapalModel::where("id_kapal", $req['id_kapal'])
                ->update([
                    'nama_kapal'=>$req['nama_kapal'],
                    'foto'      =>$req['foto'],
                    'nama_perusahaan'   =>$req['nama_perusahaan'],
                    'merk_perusahaan'   =>$req['merk_perusahaan'],
                    'alamat_perusahaan_1'   =>$req['alamat_perusahaan_1'],
                    'alamat_perusahaan_2'   =>$req['alamat_perusahaan_2'],
                    'telepon'   =>$req['telepon'],
                    'faximile'  =>$req['faximile'],
                    'npwp'      =>$req['npwp'],
                    'email'     =>$req['email']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
