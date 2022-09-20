<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\ProyekReportModel;

class ReportController extends Controller
{

    //SUMMARY PROJECT
    public function update_summary(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek' =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $value);
                    //--shipowner
                    if($login_data['role']=="shipowner"){
                        $p=$p->whereHas("kapal", function($query)use($login_data){
                            $query->where("id_user", $login_data['id_user']);
                        });
                    }
                    //--shipyard
                    if($login_data['role']=="shipyard"){
                        $p=$p->whereHas("report.tender", function($query)use($login_data){
                            $query->where("id_user", $login_data['id_user']);
                        });
                    }
                    if($p->count()==0){
                        return $fail("The selected id proyek is invalid.");
                    }
                    return true;
                }
            ],
            'proyek_start'  =>"required|date_format:Y-m-d",
            'proyek_end'    =>"required|date_format:Y-m-d|after_or_equal:proyek_start",
            'tipe_proyek'   =>[
                Rule::requiredIf(!isset($req['tipe_proyek']))
            ],
            'master_plan'   =>[
                Rule::requiredIf(!isset($req['master_plan']))
            ],
            'negara'        =>[
                Rule::requiredIf(!isset($req['negara']))
            ],
            'prioritas'     =>[
                Rule::requiredIf(!isset($req['prioritas']))
            ],
            'partner'       =>[
                Rule::requiredIf(!isset($req['partner']))
            ],
            'deskripsi'     =>[
                Rule::requiredIf(!isset($req['deskripsi']))
            ],
            'status'        =>"required|in:preparation,in_progress,evaluasi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $proyek_period=count_day($req['proyek_start'], $req['proyek_end']);

            ProyekReportModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    "proyek_start"  =>$req['proyek_start'],
                    "proyek_end"    =>$req['proyek_end'],
                    "proyek_period" =>$proyek_period,
                    "master_plan"   =>$req['master_plan'],
                    "status"        =>$req['status'],
                    "negara"        =>$req['negara'],
                    "tipe_proyek"   =>$req['tipe_proyek'],
                    "prioritas"     =>$req['prioritas'],
                    "partner"       =>$req['partner'],
                    "deskripsi"     =>$req['deskripsi'],
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_summary(Request $request)
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
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                "integer",
                "min:1"
            ],
            'q'         =>[
                Rule::requiredIf(!isset($req['q']))
            ],
            'status'    =>"required|in:preparation,in_progress,evaluasi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $proyek_summary=ProyekReportModel::with("proyek", "tender");
        //q
        $proyek_summary=$proyek_summary->where(function($q){
            $q->whereHas("proyek", function($query)use($req){
                $query->where("nama_proyek", "ilike", "%".$req['q']."%");
            })
            ->orWhereHas("proyek.kapal", function($query)use($req){
                $query->where("nama_kapal", "ilike", "%".$req['q']."%");
            });
        });
        //shipowner
        if($login_data['role']=="shipowner"){
            $proyek_summary=$proyek_summary->whereHas("proyek.kapal", function($query)use($login_data){
                $query->where("id_user", $login_data['id_user']);
            });
        }
        //shipyard
        if($login_data['role']=="shipyard"){
            $proyek_summary=$proyek_summary->whereHas("tender", function($query)use($login_data){
                $query->where("id_user", $login_data['id_user']);
            });
        }

        //order & paginate
        $proyek_summary=$proyek_summary->orderByDesc("id_proyek")
            ->paginate(trim($req['per_page']))
            ->toArray();

        return response()->json([
            'status'=>"ok"
        ]);
    }

    // public function add_proyek(Request $request)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $validation=Validator::make($req, [
    //         'id_kapal'  =>[
    //             "required",
    //             Rule::exists("App\Models\KapalModel")->where(function($query)use($login_data){
    //                 if($login_data['role']=="shipowner"){
    //                     return $query->where("id_user", $login_data['id_user']);
    //                 }
    //             })
    //         ],
    //         'tahun'     =>"required|integer|digits:4",
    //         'nama_proyek'   =>"required",
    //         'mata_uang'     =>"required",
    //         'off_hire_start'=>"required|date_format:Y-m-d",
    //         'off_hire_end'  =>"required|date_format:Y-m-d|after_or_equal:off_hire_start",
    //         'off_hire_deviasi'      =>"required|integer|min:0",
    //         'off_hire_rate_per_day' =>"required|numeric|min:0",
    //         'off_hire_bunker_per_day'=>"required|numeric|min:0",
    //         'repair_start'  =>"required|date_format:Y-m-d",
    //         'repair_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_start",
    //         'repair_in_dock_start'  =>"required|date_format:Y-m-d|after_or_equal:after_or_equal:repair_start",
    //         'repair_in_dock_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_in_dock_start|before_or_equal:repair_end",
    //         'repair_additional_day' =>"required|integer|min:0",
    //         'owner_supplies'    =>"required|numeric|min:0",
    //         'owner_services'    =>"required|numeric|min:0",
    //         'owner_class'       =>"required|numeric|min:0",
    //         'owner_other'       =>"required|numeric|min:0",
    //         'owner_cancel_job'  =>"required|numeric|min:0",
    //         'yard_cost'         =>"required|numeric|min:0",
    //         'yard_cancel_job'   =>"required|numeric|min:0",
    //         'work_area'         =>[
    //             Rule::requiredIf(function()use($req){
    //                 if(!isset($req['work_area'])) return true;
    //                 if(!is_array($req['work_area'])) return true;
    //             }),
    //             'array',
    //             'min:0'
    //         ],
    //         'status'            =>"required|in:draft,published"
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }

    //     //VALIDATION DETAIL FOR WORK AREA
    //     $validate_work_area=validation_proyek_work_area($req['work_area']);
    //     if($validate_work_area['error']){
    //         return response()->json([
    //             'data'  =>$validate_work_area['data']
    //         ], 500);
    //     }

    //     //ADD CREATE_AT AND LAST_UPDATE IN WORK AREA
    //     $req['work_area']=add_timestamps_proyek_work_area($req['work_area']);

    //     //SUCCESS
    //     DB::transaction(function() use($req, $login_data){
    //         $off_hire_period=count_day($req['off_hire_start'], $req['off_hire_end']);
    //         $repair_period=count_day($req['repair_start'], $req['repair_end']);
    //         $repair_in_dock_period=count_day($req['repair_in_dock_start'], $req['repair_in_dock_end']);

    //         ProyekModel::create([
    //             'id_kapal'  =>$req['id_kapal'],
    //             'tahun'     =>$req['tahun'],
    //             'nama_proyek'   =>$req['nama_proyek'],
    //             'mata_uang'     =>$req['mata_uang'],
    //             'off_hire_start'=>$req['off_hire_start'],
    //             'off_hire_end'  =>$req['off_hire_end'],
    //             'off_hire_period'   =>$off_hire_period,
    //             'off_hire_deviasi'      =>$req['off_hire_deviasi'],
    //             'off_hire_rate_per_day' =>$req['off_hire_rate_per_day'],
    //             'off_hire_bunker_per_day'=>$req['off_hire_bunker_per_day'],
    //             'repair_start'  =>$req['repair_start'],
    //             'repair_end'    =>$req['repair_end'],
    //             'repair_period' =>$repair_period,
    //             'repair_in_dock_start'  =>$req['repair_in_dock_start'],
    //             'repair_in_dock_end'    =>$req['repair_in_dock_end'],
    //             'repair_in_dock_period' =>$repair_in_dock_period,
    //             'repair_additional_day' =>$req['repair_additional_day'],
    //             'owner_supplies'    =>$req['owner_supplies'],
    //             'owner_services'    =>$req['owner_services'],
    //             'owner_class'       =>$req['owner_class'],
    //             'owner_other'       =>$req['owner_other'],
    //             'owner_cancel_job'  =>$req['owner_cancel_job'],
    //             'yard_cost'         =>$req['yard_cost'],
    //             'yard_cancel_job'   =>$req['yard_cancel_job'],
    //             'work_area'         =>$req['work_area'],
    //             'status'            =>$req['status']
    //         ]);
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }

    // public function update_proyek(Request $request, $id)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $req['id_proyek']=$id;
    //     $validation=Validator::make($req, [
    //         'id_proyek'  =>[
    //             "required",
    //             function($attr, $value, $fail)use($login_data){
    //                 $v=ProyekModel::where("id_proyek", $value)->where("status", "draft");
    //                 if($login_data['role']=="shipowner"){
    //                     $v=$v->whereHas("kapal", function($q)use($login_data){
    //                         $q->where("id_user", $login_data['id_user']);
    //                     });
    //                 }
    //                 if($v->count()==0){
    //                     return $fail("The selected id proyek is invalid.");
    //                 }
    //                 return true;
    //             }
    //         ],
    //         'tahun'     =>"required|integer|digits:4",
    //         'nama_proyek'   =>"required",
    //         'mata_uang'     =>"required",
    //         'off_hire_start'=>"required|date_format:Y-m-d",
    //         'off_hire_end'  =>"required|date_format:Y-m-d|after_or_equal:off_hire_start",
    //         'off_hire_deviasi'      =>"required|integer|min:0",
    //         'off_hire_rate_per_day' =>"required|numeric|min:0",
    //         'off_hire_bunker_per_day'=>"required|numeric|min:0",
    //         'repair_start'  =>"required|date_format:Y-m-d",
    //         'repair_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_start",
    //         'repair_in_dock_start'  =>"required|date_format:Y-m-d|after_or_equal:after_or_equal:repair_start",
    //         'repair_in_dock_end'    =>"required|date_format:Y-m-d|after_or_equal:repair_in_dock_start|before_or_equal:repair_end",
    //         'repair_additional_day' =>"required|integer|min:0",
    //         'owner_supplies'    =>"required|numeric|min:0",
    //         'owner_services'    =>"required|numeric|min:0",
    //         'owner_class'       =>"required|numeric|min:0",
    //         'owner_other'       =>"required|numeric|min:0",
    //         'owner_cancel_job'  =>"required|numeric|min:0",
    //         'yard_cost'         =>"required|numeric|min:0",
    //         'yard_cancel_job'   =>"required|numeric|min:0",
    //         'work_area'         =>[
    //             Rule::requiredIf(function()use($req){
    //                 if(!isset($req['work_area'])) return true;
    //                 if(!is_array($req['work_area'])) return true;
    //             }),
    //             'array',
    //             'min:0'
    //         ],
    //         'status'            =>"required|in:draft,published"
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }
        
    //     //VALIDATION DETAIL FOR WORK AREA
    //     $validate_work_area=validation_proyek_work_area($req['work_area']);
    //     if($validate_work_area['error']){
    //         return response()->json([
    //             'data'  =>$validate_work_area['data']
    //         ], 500);
    //     }

    //     //ADD CREATE_AT AND LAST_UPDATE IN WORK AREA
    //     $req['work_area']=add_timestamps_proyek_work_area($req['work_area']);

    //     //SUCCESS
    //     DB::transaction(function() use($req, $login_data){
    //         $off_hire_period=count_day($req['off_hire_start'], $req['off_hire_end']);
    //         $repair_period=count_day($req['repair_start'], $req['repair_end']);
    //         $repair_in_dock_period=count_day($req['repair_in_dock_start'], $req['repair_in_dock_end']);

    //         ProyekModel::where("id_proyek", $req['id_proyek'])
    //             ->update([
    //                 'tahun'     =>$req['tahun'],
    //                 'nama_proyek'   =>$req['nama_proyek'],
    //                 'mata_uang'     =>$req['mata_uang'],
    //                 'off_hire_start'=>$req['off_hire_start'],
    //                 'off_hire_end'  =>$req['off_hire_end'],
    //                 'off_hire_period'   =>$off_hire_period,
    //                 'off_hire_deviasi'      =>$req['off_hire_deviasi'],
    //                 'off_hire_rate_per_day' =>$req['off_hire_rate_per_day'],
    //                 'off_hire_bunker_per_day'=>$req['off_hire_bunker_per_day'],
    //                 'repair_start'  =>$req['repair_start'],
    //                 'repair_end'    =>$req['repair_end'],
    //                 'repair_period' =>$repair_period,
    //                 'repair_in_dock_start'  =>$req['repair_in_dock_start'],
    //                 'repair_in_dock_end'    =>$req['repair_in_dock_end'],
    //                 'repair_in_dock_period' =>$repair_in_dock_period,
    //                 'repair_additional_day' =>$req['repair_additional_day'],
    //                 'owner_supplies'    =>$req['owner_supplies'],
    //                 'owner_services'    =>$req['owner_services'],
    //                 'owner_class'       =>$req['owner_class'],
    //                 'owner_other'       =>$req['owner_other'],
    //                 'owner_cancel_job'  =>$req['owner_cancel_job'],
    //                 'yard_cost'         =>$req['yard_cost'],
    //                 'yard_cancel_job'   =>$req['yard_cancel_job'],
    //                 'work_area'         =>json_encode($req['work_area']),
    //                 'status'            =>$req['status']
    //             ]);
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }

    // public function publish_proyek(Request $request, $id)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $req['id_proyek']=$id;
    //     $validation=Validator::make($req, [
    //         'id_proyek'  =>[
    //             "required",
    //             function($attr, $value, $fail)use($login_data){
    //                 $v=ProyekModel::where("id_proyek", $value)->where("status", "draft");
    //                 if($login_data['role']=="shipowner"){
    //                     $v=$v->whereHas("kapal", function($q)use($login_data){
    //                         $q->where("id_user", $login_data['id_user']);
    //                     });
    //                 }
    //                 if($v->count()==0){
    //                     return $fail("The selected id proyek is invalid.");
    //                 }
    //                 return true;
    //             }
    //         ]
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     DB::transaction(function() use($req, $login_data){
    //         ProyekModel::where("id_proyek", $req['id_proyek'])
    //             ->update([
    //                 'status'=>"published"
    //             ]);
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }

    // public function delete_proyek(Request $request, $id)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $req['id_proyek']=$id;
    //     $validation=Validator::make($req, [
    //         'id_proyek'  =>[
    //             "required",
    //             function($attr, $value, $fail)use($login_data){
    //                 $v=ProyekModel::where("id_proyek", $value);
    //                 if($login_data['role']=="shipowner"){
    //                     $v=$v->whereHas("kapal", function($q)use($login_data){
    //                         $q->where("id_user", $login_data['id_user']);
    //                     });
    //                 }
    //                 if($v->count()==0){
    //                     return $fail("The selected id proyek is invalid.");
    //                 }
    //                 return true;
    //             }
    //         ]
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     DB::transaction(function() use($req, $login_data){
    //         ProyekModel::where("id_proyek", $req['id_proyek'])->delete();
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }

    // public function gets_proyek(Request $request)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $validation=Validator::make($req, [
    //         'per_page'  =>[
    //             Rule::requiredIf(!isset($req['per_page'])),
    //             'integer',
    //             'min:1'
    //         ],
    //         'q' =>[
    //             Rule::requiredIf(!isset($req['q']))
    //         ]
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     $proyek=ProyekModel::with("kapal", "kapal.owner");
    //     //q
    //     $proyek=$proyek->where("nama_proyek", "ilike", "%".$req['q']."%");
    //     //shipowner
    //     if($login_data['role']=="shipowner"){
    //         $proyek=$proyek->withWhereHas("kapal", function($q)use($login_data){
    //             $q->where("id_user", $login_data['id_user']);
    //         });
    //     }
    //     //get & paginate
    //     $proyek=$proyek->orderByDesc("id_proyek")
    //         ->paginate(trim($req['per_page']))
    //         ->toArray();

    //     return response()->json([
    //         'first_page'    =>1,
    //         'current_page'  =>$proyek['current_page'],
    //         'last_page'     =>$proyek['last_page'],
    //         'data'          =>$proyek['data']
    //     ]);
    // }

    // public function get_proyek(Request $request, $id)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $req['id_proyek']=$id;
    //     $validation=Validator::make($req, [
    //         'id_proyek'  =>[
    //             "required",
    //             function($attr, $value, $fail)use($login_data){
    //                 $v=ProyekModel::where("id_proyek", $value);
    //                 if($login_data['role']=="shipowner"){
    //                     $v=$v->whereHas("kapal", function($q)use($login_data){
    //                         $q->where("id_user", $login_data['id_user']);
    //                     });
    //                 }
    //                 if($v->count()==0){
    //                     return $fail("The selected id proyek is invalid.");
    //                 }
    //                 return true;
    //             }
    //         ]
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     $proyek=ProyekModel::where("id_proyek", $req['id_proyek'])
    //         ->with("kapal", "kapal.owner")
    //         ->first()
    //         ->toArray();

    //     return response()->json([
    //         'data'  =>$proyek
    //     ]);
    // }
}
