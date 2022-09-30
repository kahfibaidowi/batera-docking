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

class HomeController extends Controller
{

    //KAPAL
    public function gets_vessel(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //SUCCESS
        $kapal=KapalModel::with("owner", "perusahaan");
        //shipowner
        if($login_data['role']=="shipowner"){
            $kapal=$kapal->where("id_user", $login_data['id_user']);
        }
        //shipyard
        if($login_data['role']=="shipyard"){
            $kapal=$kapal->whereHas("proyek.report.tender", function($query)use($login_data){
                $query->where("id_user", $login_data['id_user']);
            });
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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_kapal']=$id;
        $validation=Validator::make($req, [
            'id_kapal'  =>[
                "required",
                Rule::exists("App\Models\KapalModel")->where(function($query)use($login_data){
                    if($login_data['role']=="shipowner"){
                        return $query->where("id_user", $login_data['id_user']);
                    }
                }),
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
        $kapal=KapalModel::with("owner", "perusahaan", "proyek", "proyek.report", "proyek.report.tender")
            ->where("id_kapal", $req['id_kapal'])
            ->orderByDesc("id_kapal")
            ->first()->toArray();

        return response()->json([
            'data'  =>array_merge($kapal, [
                'proyek'=>generate_summary_kapal($kapal, $login_data)
            ])
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
                function($attr, $value, $fail)use($login_data){
                    $v=UserModel::where("id_user", $value)
                        ->where("role", "shipowner");

                    if($login_data['role']=="shipowner"){
                        $v=$v->where("id_user", $login_data['id_user']);
                    }

                    if(is_null($v->first())){
                        return $fail("The selected id user is invalid or role not shipowner");
                    }
                },
                function($attr, $value, $fail){
                    $v=UserShipownerModel::where("id_user", $value);
                    if($v->count()>0){
                        $vr=$v->first();
                        if($vr['kapal_tersisa']<=0){
                            return $fail("limit kapal runs out");
                        }
                    }
                    return true;
                }
            ],
            'id_perusahaan' =>"required|exists:App\Models\PerusahaanModel,id_perusahaan",
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
            //kapal
            KapalModel::create([
                'id_user'   =>$req['id_user'],
                'nama_kapal'=>$req['nama_kapal'],
                'foto'      =>$req['foto'],
                'id_perusahaan' =>$req['id_perusahaan']
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
            'id_kapal'  =>[
                "required",
                Rule::exists("App\Models\KapalModel")->where(function($query)use($login_data){
                    if($login_data['role']=="shipowner"){
                        return $query->where("id_user", $login_data['id_user']);
                    }
                })
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
            //kapal
            $kapal=KapalModel::where("id_kapal", $req['id_kapal'])->first();
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
            'id_kapal'  =>[
                "required",
                Rule::exists("App\Models\KapalModel")->where(function($query)use($login_data){
                    if($login_data['role']=="shipowner"){
                        return $query->where("id_user", "!=", $login_data['id_user']);
                    }
                })
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
            'id_perusahaan' =>"required|exists:App\Models\PerusahaanModel,id_perusahaan"
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
                    'id_perusahaan' =>$req['id_perusahaan']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
