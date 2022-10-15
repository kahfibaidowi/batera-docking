<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\UserModel;
use App\Repository\ProyekRepo;

class ProyekController extends Controller
{

    //PROYEK
    public function add_proyek(Request $request)
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
            'id_kapal'  =>[
                "required",
                Rule::exists("App\Models\KapalModel")
            ],
            'id_user'   =>[
                "required",
                function($attr, $value, $fail){
                    $v=UserModel::where("id_user", $value)
                        ->whereIn("role", ['shipyard', 'shipmanager']);

                    if($v->count()==0){
                        return $fail("The selected id_user(responsible) is invalid or role not shipyard, shipmanager");
                    }
                }
            ],
            'phase'         =>"required|in:requisition,in_progress,evaluasi,finish",
            'tahun'     =>"required|integer|digits:4",
            'mata_uang'     =>"required",
            'off_hire_start'=>"required|date_format:Y-m-d",
            'off_hire_end'  =>"required|date_format:Y-m-d|after_or_equal:off_hire_start",
            'off_hire_deviasi'      =>"required|integer|min:0",
            'off_hire_rate_per_day' =>"required|numeric|min:0",
            'off_hire_bunker_per_day'=>"required|numeric|min:0",
            'repair_start'  =>"required|date_format:Y-m-d",
            'repair_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_start",
            'repair_in_dock_start'  =>"required|date_format:Y-m-d|after_or_equal:after_or_equal:repair_start",
            'repair_in_dock_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_in_dock_start|before_or_equal:repair_end",
            'repair_additional_day' =>"required|integer|min:0"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $off_hire_period=count_day($req['off_hire_start'], $req['off_hire_end']);
            $repair_period=count_day($req['repair_start'], $req['repair_end']);
            $repair_in_dock_period=count_day($req['repair_in_dock_start'], $req['repair_in_dock_end']);

            ProyekModel::create([
                'id_kapal'  =>$req['id_kapal'],
                'id_user'   =>$req['id_user'],
                'phase'     =>$req['phase'],
                'tahun'     =>$req['tahun'],
                'mata_uang'     =>$req['mata_uang'],
                'off_hire_start'=>$req['off_hire_start'],
                'off_hire_end'  =>$req['off_hire_end'],
                'off_hire_period'   =>$off_hire_period,
                'off_hire_deviasi'      =>$req['off_hire_deviasi'],
                'off_hire_rate_per_day' =>$req['off_hire_rate_per_day'],
                'off_hire_bunker_per_day'=>$req['off_hire_bunker_per_day'],
                'repair_start'  =>$req['repair_start'],
                'repair_end'    =>$req['repair_end'],
                'repair_period' =>$repair_period,
                'repair_in_dock_start'  =>$req['repair_in_dock_start'],
                'repair_in_dock_end'    =>$req['repair_in_dock_end'],
                'repair_in_dock_period' =>$repair_in_dock_period,
                'repair_additional_day' =>$req['repair_additional_day']
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update_proyek(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek'  =>[
                "required",
                Rule::exists("App\Models\ProyekModel")
            ],
            'id_user'   =>[
                "required",
                function($attr, $value, $fail){
                    $v=UserModel::where("id_user", $value)
                        ->whereIn("role", ['shipyard', 'shipmanager']);

                    if($v->count()==0){
                        return $fail("The selected id_user(responsible) is invalid or role not shipyard, shipmanager");
                    }
                }
            ],
            'phase'         =>"required|in:requisition,in_progress,evaluasi,finish",
            'tahun'     =>"required|integer|digits:4",
            'mata_uang'     =>"required",
            'off_hire_start'=>"required|date_format:Y-m-d",
            'off_hire_end'  =>"required|date_format:Y-m-d|after_or_equal:off_hire_start",
            'off_hire_deviasi'      =>"required|integer|min:0",
            'off_hire_rate_per_day' =>"required|numeric|min:0",
            'off_hire_bunker_per_day'=>"required|numeric|min:0",
            'repair_start'  =>"required|date_format:Y-m-d",
            'repair_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_start",
            'repair_in_dock_start'  =>"required|date_format:Y-m-d|after_or_equal:after_or_equal:repair_start",
            'repair_in_dock_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_in_dock_start|before_or_equal:repair_end",
            'repair_additional_day' =>"required|integer|min:0"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $off_hire_period=count_day($req['off_hire_start'], $req['off_hire_end']);
            $repair_period=count_day($req['repair_start'], $req['repair_end']);
            $repair_in_dock_period=count_day($req['repair_in_dock_start'], $req['repair_in_dock_end']);

            ProyekModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    'id_user'   =>$req['id_user'],
                    'tahun'     =>$req['tahun'],
                    'phase'     =>$req['phase'],
                    'mata_uang'     =>$req['mata_uang'],
                    'off_hire_start'=>$req['off_hire_start'],
                    'off_hire_end'  =>$req['off_hire_end'],
                    'off_hire_period'   =>$off_hire_period,
                    'off_hire_deviasi'      =>$req['off_hire_deviasi'],
                    'off_hire_rate_per_day' =>$req['off_hire_rate_per_day'],
                    'off_hire_bunker_per_day'=>$req['off_hire_bunker_per_day'],
                    'repair_start'  =>$req['repair_start'],
                    'repair_end'    =>$req['repair_end'],
                    'repair_period' =>$repair_period,
                    'repair_in_dock_start'  =>$req['repair_in_dock_start'],
                    'repair_in_dock_end'    =>$req['repair_in_dock_end'],
                    'repair_in_dock_period' =>$repair_in_dock_period,
                    'repair_additional_day' =>$req['repair_additional_day']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function delete_proyek(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek'  =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $v=ProyekModel::where("id_proyek", $value);
                    if($login_data['role']=="shipowner"){
                        $v=$v->whereHas("kapal", function($q)use($login_data){
                            $q->where("id_user", $login_data['id_user']);
                        });
                    }
                    if($v->count()==0){
                        return $fail("The selected id proyek is invalid.");
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
        DB::transaction(function() use($req, $login_data){
            ProyekModel::where("id_proyek", $req['id_proyek'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_proyek(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'director', 'shipmanager', 'shipyard'])){
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
        $proyek=ProyekRepo::gets_proyek($req, $login_data);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$proyek['current_page'],
            'last_page'     =>$proyek['last_page'],
            'data'          =>$proyek['data']
        ]);
    }

    public function get_proyek(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'director', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek'  =>[
                "required",
                Rule::exists("App\Models\ProyekModel")
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $proyek=ProyekRepo::get_proyek($req['id_proyek'], $login_data);

        return response()->json([
            'data'  =>$proyek
        ]);
    }

    //WORK AREA
    public function update_proyek_work_area(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek'  =>[
                "required",
                Rule::exists("App\Models\ProyekModel")
            ],
            'work_area' =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            ProyekModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    'work_area' =>$req['work_area']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
