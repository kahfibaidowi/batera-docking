<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\ProyekReportModel;
use App\Models\ProyekReportDetailModel;
use App\Models\ProyekReportPicModel;
use App\Repository\ProyekReportRepo;

class ReportController extends Controller
{

    //SUMMARY PROJECT
    public function update_report(Request $request, $id)
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
            'summary_detail'=>[
                Rule::requiredIf(!isset($req['summary_detail']))
            ],
            'approved_by'   =>[
                Rule::requiredIf(!isset($req['approved_by']))
            ],
            'approved'      =>[
                Rule::requiredIf(!isset($req['approved'])),
                "date_format:Y-m-d"
            ],
            'proyek_start'  =>"required|date_format:Y-m-d",
            'proyek_end'    =>"required|date_format:Y-m-d|after_or_equal:proyek_start",
            'tipe_proyek'   =>[
                Rule::requiredIf(!isset($req['tipe_proyek']))
            ],
            'master_plan'   =>[
                Rule::requiredIf(!isset($req['master_plan']))
            ],
            'state'         =>"required",
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

            $proyek_report=ProyekReportModel::where("id_proyek", $req['id_proyek'])->first();
            $proyek_report->update([
                "proyek_start"  =>$req['proyek_start'],
                "proyek_end"    =>$req['proyek_end'],
                "proyek_period" =>$proyek_period,
                "master_plan"   =>$req['master_plan'],
                "status"        =>$req['status'],
                "state"         =>$req['state'],
                "tipe_proyek"   =>$req['tipe_proyek'],
                "prioritas"     =>$req['prioritas'],
                "partner"       =>$req['partner'],
                "deskripsi"     =>$req['deskripsi'],
            ]);
            
            //pic
            $pic=ProyekReportPicModel::where("id_proyek_report", $proyek_report['id_proyek_report'])
                ->where("id_user", $login_data['id_user']);
            if($pic->count()>0){
                $pic->touch();
            }
            else{
                ProyekReportPicModel::create([
                    'id_proyek_report'  =>$proyek_report['id_proyek_report'],
                    'id_user'   =>$login_data['id_user']
                ]);
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_report(Request $request)
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
            ],
            'status'    =>[
                Rule::requiredIf(!isset($req['status'])),
                "in:preparation,in_progress,evaluasi"
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $report=ProyekReportRepo::gets_report($req, $login_data);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$report['current_page'],
            'last_page'     =>$report['last_page'],
            'data'          =>$report['data']
        ]);
    }

    public function get_report(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek' =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $value);
                    //--shipyard
                    if($login_data['role']=="shipyard"){
                        $p=$p->whereHas("report.tender", function($query)use($login_data){
                            $query->where("id_user", $login_data['id_user']);
                        });
                    }
                    if($p->count()==0){
                        return $fail("The selected id proyek is invalid or proyek no report.");
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
        $report=ProyekReportRepo::get_report($req['id_proyek']);

        return response()->json([
            'data'  =>$report
        ]);
    }

    //SUMMARY DETAIL
    public function add_detail(Request $request)
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
            'id_proyek' =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $value);
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
            'type'      =>"required|in:bast,close_out_report,surat_teguran",
            'tgl'       =>"required|date_format:Y-m-d",
            'perihal'   =>"required",
            'nama_pengirim' =>"required",
            'keterangan'=>[
                Rule::requiredIf(!isset($req['keterangan']))
            ],
            'dokumen'   =>[
                Rule::requiredIf(!isset($req['dokumen'])),
                "ends_with:.pdf,.doc,.docx,.xls,.xlsx",
                function($attr, $value, $fail){
                    if(trim($value)==""){
                        return true;
                    }
                    if(is_document_file($value)){
                        return true;
                    }
                    return $fail("document not found.");
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
            $proyek_summary=ProyekReportModel::where("id_proyek", $req['id_proyek'])->first();

            ProyekReportDetailModel::create([
                'id_proyek_report'  =>$proyek_summary['id_proyek_report'],
                'id_user'   =>$login_data['id_user'],
                'type'      =>$req['type'],
                'tgl'       =>$req['tgl'],
                'perihal'   =>$req['perihal'],
                'nama_pengirim' =>$req['nama_pengirim'],
                'keterangan'    =>$req['keterangan'],
                'dokumen'       =>trim($req['dokumen'])
            ]);

            //pic
            $pic=ProyekReportPicModel::where("id_proyek_report", $proyek_summary['id_proyek_report'])
                ->where("id_user", $login_data['id_user']);
            if($pic->count()>0){
                $pic->touch();
            }
            else{
                ProyekReportPicModel::create([
                    'id_proyek_report'  =>$proyek_summary['id_proyek_report'],
                    'id_user'   =>$login_data['id_user']
                ]);
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update_detail(Request $request, $id)
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
        $req['id_proyek_report_detail']=$id;
        $validation=Validator::make($req, [
            'id_proyek_report_detail' =>[
                "required",
                Rule::exists("App\Models\ProyekReportDetailModel")->where(function($query)use($login_data){
                    if(!in_array($login_data['role'], ["admin", "shipmanager"])){
                        $query->where("id_user", $login_data['id_user']);
                    }
                }),
                function($attr, $value, $fail)use($login_data){
                    //detail
                    $b=ProyekReportDetailModel::with("report")->where("id_proyek_report_detail", $value);
                    if($b->count()==0){
                        return $fail("The selected id proyek report detail is invalid.");
                    }

                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $b->first()['report']['id_proyek']);
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
            'tgl'       =>"required|date_format:Y-m-d",
            'perihal'   =>"required",
            'nama_pengirim' =>"required",
            'keterangan'=>[
                Rule::requiredIf(!isset($req['keterangan']))
            ],
            'dokumen'   =>[
                Rule::requiredIf(!isset($req['dokumen'])),
                "ends_with:.pdf,.doc,.docx,.xls,.xlsx",
                function($attr, $value, $fail){
                    if(trim($value)==""){
                        return true;
                    }
                    if(is_document_file($value)){
                        return true;
                    }
                    return $fail("document not found.");
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
            $proyek_report=ProyekReportDetailModel::where("id_proyek_report_detail", $req['id_proyek_report_detail'])->first();
            $proyek_report->update([
                'tgl'       =>$req['tgl'],
                'perihal'   =>$req['perihal'],
                'nama_pengirim' =>$req['nama_pengirim'],
                'keterangan'    =>$req['keterangan'],
                'dokumen'       =>trim($req['dokumen'])
            ]);
            
            //pic
            $pic=ProyekReportPicModel::where("id_proyek_report", $proyek_report['id_proyek_report'])
                ->where("id_user", $login_data['id_user']);
            if($pic->count()>0){
                $pic->touch();
            }
            else{
                ProyekReportPicModel::create([
                    'id_proyek_report'  =>$proyek_report['id_proyek_report'],
                    'id_user'   =>$login_data['id_user']
                ]);
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function delete_detail(Request $request, $id)
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
        $req['id_proyek_report_detail']=$id;
        $validation=Validator::make($req, [
            'id_proyek_report_detail' =>[
                "required",
                Rule::exists("App\Models\ProyekReportDetailModel")->where(function($query)use($login_data){
                    if(!in_array($login_data['role'], ["admin", "shipmanager"])){
                        $query->where("id_user", $login_data['id_user']);
                    }
                }),
                function($attr, $value, $fail)use($login_data){
                    //detail
                    $b=ProyekReportDetailModel::with("report")->where("id_proyek_report_detail", $value);
                    if($b->count()==0){
                        return $fail("The selected id proyek report detail is invalid.");
                    }

                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $b->first()['report']['id_proyek']);
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
            $proyek_report=ProyekReportDetailModel::where("id_proyek_report_detail", $req['id_proyek_report_detail'])->first();
            $proyek_report->delete();

            //pic
            $pic=ProyekReportPicModel::where("id_proyek_report", $proyek_report['id_proyek_report'])
                ->where("id_user", $login_data['id_user']);
            if($pic->count()>0){
                $pic->touch();
            }
            else{
                ProyekReportPicModel::create([
                    'id_proyek_report'  =>$proyek_report['id_proyek_report'],
                    'id_user'   =>$login_data['id_user']
                ]);
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_report_detail(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                "integer",
                "min:1"
            ],
            'type'      =>"required|in:bast,close_out_report,surat_teguran",
            'id_proyek' =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $value);
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $detail=ProyekReportRepo::gets_report_detail($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$detail['current_page'],
            'last_page'     =>$detail['last_page'],
            'data'          =>$detail['data']
        ]);
    }

    public function get_detail(Request $request, $id)
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
        $req['id_proyek_report_detail']=$id;
        $validation=Validator::make($req, [
            'id_proyek_report_detail' =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    //detail
                    $b=ProyekReportDetailModel::with("report")->where("id_proyek_report_detail", $value);
                    if($b->count()==0){
                        return $fail("the selected id proyek report detail is invalid.");
                    }

                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $b->first()['report']['id_proyek']);
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $detail=ProyekReportRepo::get_report_detail($req['id_proyek_report_detail']);

        return response()->json([
            'data'  =>$detail
        ]);
    }

    //WORK AREA
    public function update_report_work_area(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek'  =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $value);
                    
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
            'work_area' =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $proyek_report=ProyekReportModel::where("id_proyek", $req['id_proyek'])->first();

            //report
            ProyekReportModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    'work_area' =>$req['work_area']
                ]);
            
            //pic
            $pic=ProyekReportPicModel::where("id_proyek_report", $proyek_report['id_proyek_report'])
                ->where("id_user", $login_data['id_user']);
            if($pic->count()>0){
                $pic->touch();
            }
            else{
                ProyekReportPicModel::create([
                    'id_proyek_report'  =>$proyek_report['id_proyek_report'],
                    'id_user'   =>$login_data['id_user']
                ]);
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    //PIC
    public function gets_report_pic(Request $request, $id)
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
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek' =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $value);
                    
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $proyek_pic=ProyekReportRepo::gets_pic($req);

        return response()->json([
            'data'  =>$proyek_pic
        ]);
    }
}
