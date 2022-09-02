<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\ProyekPekerjaanModel;
use App\Models\ProyekBiayaModel;

class ProyekController extends Controller
{

    public function add(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            //project
            'id_user'       =>[
                Rule::requiredIf(function()use($login_data){
                    if($login_data['role']=="shipowner"){
                        return false;
                    }
                    return true;
                }),
                Rule::exists("App\Models\UserModel")->where(function($query)use($req){
                    return $query->where("id_user", $req['id_user'])
                        ->where("role", "shipowner");
                })
            ],
            'vessel'        =>"required|regex:/^[\pL\s\-]+$/u",
            'tahun'         =>"required|integer|digits:4",
            'foto'          =>[
                'required',
                'ends_with:.jpg,.png,.jpeg',
                function($attr, $value, $fail)use($req){
                    if(is_image_file($req['foto'])){
                        return true;
                    }
                    return $fail($attr." image not found");
                }
            ],
            'deskripsi'     =>"required",

            //project detail
            'detail.off_hire_start'     =>"required|date_format:Y-m-d",
            'detail.off_hire_end'       =>"required|date_format:Y-m-d|after_or_equal:detail.off_hire_start",
            'detail.off_hire_deviasi'   =>"required|numeric|min:0",
            'detail.off_hire_rate_per_day'  =>"required|numeric|min:0", 
            'detail.off_hire_bunker_per_day'=>"required|numeric|min:0",
            'detail.list_pekerjaan'     =>"required|numeric|min:0",

            //job list
            'pekerjaan'     =>"required|array|min:1",
            'pekerjaan.*.pekerjaan' =>"required",
            'pekerjaan.*'   =>"required_array_keys:kategori_1,kategori_2,kategori_3,kategori_4",
            'pekerjaan.*.qty'   =>"required|numeric|min:0",
            'pekerjaan.*.satuan'=>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            //proyek
            $id_user=$login_data['role']!="shipowner"?$req['id_user']:$login_data['id_user'];
            $proyek=ProyekModel::create([
                'id_user'       =>$id_user,
                'vessel'        =>$req['vessel'],
                'tahun'         =>$req['tahun'],
                'foto'          =>$req['foto'],
                'currency'      =>"IDR",
                'prioritas'     =>"high",
                'negara'        =>"indonesia",
                'deskripsi'     =>$req['deskripsi'],
                'status'        =>"published",
                'tender_status' =>"opened"
            ]);
            $proyek_id=$proyek->id_proyek;

            //proyek biaya
            $off_hire_period=count_day($req['detail']['off_hire_start'], $req['detail']['off_hire_end']);
            ProyekBiayaModel::create([
                'id_proyek'     =>$proyek_id,
                'off_hire_start'=>$req['detail']['off_hire_start'],
                'off_hire_end'  =>$req['detail']['off_hire_end'],
                'off_hire_period'       =>$off_hire_period,
                'off_hire_deviasi'      =>$req['detail']['off_hire_deviasi'],
                'off_hire_rate_per_day' =>$req['detail']['off_hire_rate_per_day'],
                'off_hire_bunker_per_day'=>$req['detail']['off_hire_bunker_per_day'],
                'list_pekerjaan'=>$req['detail']['list_pekerjaan']
            ]);

            //proyek pekerjaan
            foreach($req['pekerjaan'] as $val){
                ProyekPekerjaanModel::create(array_merge($val, [
                    'id_proyek' =>$proyek_id
                ]));
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    // public function gets(Request $request)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //VALIDATION
    //     $validation=Validator::make($req, [
    //         'per_page'  =>"required|numeric|min:1",
    //         'q'         =>[
    //             Rule::requiredIf(!isset($req['q'])),
    //             'regex:/^[\pL\s\-]+$/u'
    //         ],
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     $proyek=ProyekModel::select()
    //         ->selectRaw("(rate_per_day*(off_hire_period+deviasi)) as rate")
    //         ->selectRaw("(bunker_per_day*(off_hire_period+deviasi)) as bunker")
    //         ->with("owner:id_user,nama_lengkap,jabatan,avatar_url,role");
    //     //role
    //     if($login_data['role']=="shipowner"){
    //         $proyek=$proyek->where("id_user", $login_data['id_user']);
    //     }
    //     //search
    //     $proyek=$proyek->where("vessel", "ilike", "%".$req['q']."%");

    //     //paginate & order
    //     $proyek=$proyek->orderByDesc("id_proyek")
    //         ->paginate($req['per_page']);

    //     return response()->json($proyek);
    // }
}
