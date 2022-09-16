<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;

class ProyekController extends Controller
{

    public function add_proyek(Request $request)
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
            'id_kapal'  =>"required|exists:App\Models\KapalModel,id_kapal",
            'tahun'     =>"required|integer|digits:4",
            'proyek_start'  =>"required|date_format:Y-m-d",
            'proyek_end'    =>"required|date_format:Y-m-d|after_or_equal:proyek_start",
            'tipe_proyek'   =>"required",
            'perusahaan_penanggung_jawab'   =>"required",
            'estimasi_biaya'=>"required|numeric|min:0",
            'master_plan'   =>"required",
            'negara'        =>"required",
            'prioritas'     =>"required",
            'nama_proyek'   =>"required",
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
            'repair_additional_day' =>"required|integer|min:0",
            'owner_supplies'    =>"required|numeric|min:0",
            'owner_services'    =>"required|numeric|min:0",
            'owner_class'       =>"required|numeric|min:0",
            'owner_other'       =>"required|numeric|min:0",
            'owner_cancel_job'  =>"required|numeric|min:0",
            'yard_cost'         =>"required|numeric|min:0",
            'yard_cancel_job'   =>"required|numeric|min:0",
            'deskripsi'         =>[Rule::requiredIf(!isset($req['deskripsi']))],
            'work_area'         =>[
                Rule::requiredIf(function()use($req){
                    if(!isset($req['work_area'])) return true;
                    if(!is_array($req['work_area'])) return true;
                }),
                'array',
                'min:0'
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //VALIDATION DETAIL FOR WORK AREA
        $validate_work_area=validation_proyek_work_area($req['work_area']);
        if($validate_work_area['error']){
            return response()->json([
                'data'  =>$validate_work_area['data']
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $proyek_period=count_day($req['proyek_start'], $req['proyek_end']);
            $off_hire_period=count_day($req['off_hire_start'], $req['off_hire_end']);
            $repair_period=count_day($req['repair_start'], $req['repair_end']);
            $repair_in_dock_period=count_day($req['repair_in_dock_start'], $req['repair_in_dock_end']);

            ProyekModel::create([
                'id_kapal'  =>$req['id_kapal'],
                'tahun'     =>$req['tahun'],
                'proyek_start'  =>$req['proyek_start'],
                'proyek_end'    =>$req['proyek_end'],
                'proyek_period' =>$proyek_period,
                'status'        =>"preparation",
                'tipe_proyek'   =>$req['tipe_proyek'],
                'perusahaan_penanggung_jawab'   =>$req['perusahaan_penanggung_jawab'],
                'estimasi_biaya'=>$req['estimasi_biaya'],
                'master_plan'   =>$req['master_plan'],
                'negara'        =>$req['negara'],
                'prioritas'     =>$req['prioritas'],
                'nama_proyek'   =>$req['nama_proyek'],
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
                'repair_additional_day' =>$req['repair_additional_day'],
                'owner_supplies'    =>$req['owner_supplies'],
                'owner_services'    =>$req['owner_services'],
                'owner_class'       =>$req['owner_class'],
                'owner_other'       =>$req['owner_other'],
                'owner_cancel_job'  =>$req['owner_cancel_job'],
                'yard_cost'         =>$req['yard_cost'],
                'yard_cancel_job'   =>$req['yard_cancel_job'],
                'deskripsi'         =>$req['deskripsi'],
                'work_area'         =>json_encode($req['work_area'])
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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek'  =>"required|exists:App\Models\ProyekModel,id_proyek",
            'tahun'     =>"required|integer|digits:4",
            'proyek_start'  =>"required|date_format:Y-m-d",
            'proyek_end'    =>"required|date_format:Y-m-d|after_or_equal:proyek_start",
            'tipe_proyek'   =>"required",
            'perusahaan_penanggung_jawab'   =>"required",
            'estimasi_biaya'=>"required|numeric|min:0",
            'master_plan'   =>"required",
            'negara'        =>"required",
            'prioritas'     =>"required",
            'nama_proyek'   =>"required",
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
            'repair_additional_day' =>"required|integer|min:0",
            'owner_supplies'    =>"required|numeric|min:0",
            'owner_services'    =>"required|numeric|min:0",
            'owner_class'       =>"required|numeric|min:0",
            'owner_other'       =>"required|numeric|min:0",
            'owner_cancel_job'  =>"required|numeric|min:0",
            'yard_cost'         =>"required|numeric|min:0",
            'yard_cancel_job'   =>"required|numeric|min:0",
            'deskripsi'         =>[Rule::requiredIf(!isset($req['deskripsi']))],
            'work_area'         =>[
                Rule::requiredIf(function()use($req){
                    if(!isset($req['work_area'])) return true;
                    if(!is_array($req['work_area'])) return true;
                }),
                'array',
                'min:0'
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }
        
        //VALIDATION DETAIL FOR WORK AREA
        $validate_work_area=validation_proyek_work_area($req['work_area']);
        if($validate_work_area['error']){
            return response()->json([
                'data'  =>$validate_work_area['data']
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $proyek_period=count_day($req['proyek_start'], $req['proyek_end']);
            $off_hire_period=count_day($req['off_hire_start'], $req['off_hire_end']);
            $repair_period=count_day($req['repair_start'], $req['repair_end']);
            $repair_in_dock_period=count_day($req['repair_in_dock_start'], $req['repair_in_dock_end']);

            ProyekModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    'tahun'     =>$req['tahun'],
                    'proyek_start'  =>$req['proyek_start'],
                    'proyek_end'    =>$req['proyek_end'],
                    'proyek_period' =>$proyek_period,
                    'tipe_proyek'   =>$req['tipe_proyek'],
                    'perusahaan_penanggung_jawab'   =>$req['perusahaan_penanggung_jawab'],
                    'estimasi_biaya'=>$req['estimasi_biaya'],
                    'master_plan'   =>$req['master_plan'],
                    'negara'        =>$req['negara'],
                    'prioritas'     =>$req['prioritas'],
                    'nama_proyek'   =>$req['nama_proyek'],
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
                    'repair_additional_day' =>$req['repair_additional_day'],
                    'owner_supplies'    =>$req['owner_supplies'],
                    'owner_services'    =>$req['owner_services'],
                    'owner_class'       =>$req['owner_class'],
                    'owner_other'       =>$req['owner_other'],
                    'owner_cancel_job'  =>$req['owner_cancel_job'],
                    'yard_cost'         =>$req['yard_cost'],
                    'yard_cancel_job'   =>$req['yard_cancel_job'],
                    'deskripsi'         =>$req['deskripsi'],
                    'work_area'         =>json_encode($req['work_area'])
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update_proyek_status(Request $request, $id)
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
            'id_proyek'  =>"required|exists:App\Models\ProyekModel,id_proyek",
            'status'    =>"required|in:preparation,in_progress,evaluasi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            ProyekModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    'status'=>$req['status']
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
            'id_proyek'  =>"required|exists:App\Models\ProyekModel,id_proyek"
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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
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
        //-- param
        $per_page=trim($req['per_page'])!=""?$req['per_page']:ProyekModel::count();

        //-- query
        $proyek=ProyekModel::with("kapal", "kapal.owner");
        //q
        $proyek=$proyek->where("nama_proyek", "ilike", "%".$req['q']."%");
        //shipowner
        if($login_data['role']=="shipowner"){
            $proyek=$proyek->withWhereHas("kapal", function($q)use($login_data){
                $q->where("id_user", $login_data['id_user']);
            });
        }
        //get & paginate
        $proyek=$proyek->orderByDesc("id_proyek")
            ->paginate($per_page)
            ->toArray();

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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek'  =>"required|exists:App\Models\ProyekModel,id_proyek"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $proyek=ProyekModel::where("id_proyek", $req['id_proyek'])
            ->with("kapal", "kapal.owner")
            ->first()
            ->toArray();

        return response()->json([
            'data'  =>$proyek
        ]);
    }
}
