<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\KapalModel;
use App\Models\UserModel;
use App\Models\ProyekModel;
use App\Repository\KapalRepo;

class HomeController extends Controller
{

    //KAPAL
    public function gets_vessel(Request $request)
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
                'integer',
                'min:1'
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
        $kapal=KapalRepo::gets_kapal($req, $login_data);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$kapal['current_page'],
            'last_page'     =>$kapal['last_page'],
            'data'          =>$kapal['data']
        ]);
    }

    public function get_vessel(Request $request, $id)
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
        $req['id_kapal']=$id;
        $validation=Validator::make($req, [
            'id_kapal'  =>[
                "required",
                Rule::exists("App\Models\KapalModel", "id_kapal"),
                function($attr, $value, $fail)use($login_data){
                    $v=KapalModel::where("id_kapal", $value);
                    //shipyard
                    if($login_data['role']=="shipyard"){
                        $v=$v->whereHas("proyek.report.tender", function($query)use($login_data){
                            $query->where("id_user", $login_data['id_user']);
                        });
                    }
                    //get
                    $v=$v->first();

                    if(is_null($v)){
                        return $fail("kapal not allowed");
                    }
                    return true;
                }
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $kapal=KapalRepo::get_kapal($req['id_kapal'], $login_data);

        return response()->json([
            'data'  =>$kapal
        ]);
    }

    public function add_vessel(Request $request)
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            KapalModel::create([
                'nama_kapal'=>$req['nama_kapal'],
                'foto'      =>$req['foto']
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
        if(!in_array($login_data['role'], ['admin', 'shipmanager'])){
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
            KapalModel::where("id_kapal", $req['id_kapal'])->delete();
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
        if(!in_array($login_data['role'], ['admin', 'shipmanager'])){
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            KapalModel::where("id_kapal", $req['id_kapal'])
                ->update([
                    'nama_kapal'=>$req['nama_kapal'],
                    'foto'      =>$req['foto']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
