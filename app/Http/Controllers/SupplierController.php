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

class SupplierController extends Controller
{
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
}
