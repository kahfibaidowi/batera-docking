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

            $proyek_report=ProyekReportModel::where("id_proyek", $req['id_proyek'])->first();
            $proyek_report->update([
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
            'status'    =>"required|in:all,preparation,in_progress,evaluasi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $proyek_summary=ProyekReportModel::with("proyek", "proyek.kapal", "tender", "tender.shipyard");
        //q
        $proyek_summary=$proyek_summary->where(function($q)use($req){
            $q->whereHas("proyek", function($query)use($req){
                $query->where("nama_proyek", "ilike", "%".$req['q']."%");
            })
            ->orWhereHas("proyek.kapal", function($query)use($req){
                $query->where("nama_kapal", "ilike", "%".$req['q']."%");
            });
        });
        //status
        if($req['status']!="all"){
            $proyek_summary=$proyek_summary->where("status", $req['status']);
        }
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

        $data=[];
        foreach($proyek_summary['data'] as $proyek){
            $general_diskon=($proyek['tender']['general_diskon_persen']/100)*$proyek['tender']['yard_total_quote'];
            $after_diskon=$proyek['tender']['yard_total_quote']-$general_diskon;

            
            $work_area=calculate_summary_work_area($proyek['work_area']);
            $akumulasi_summary=calculate_akumulasi_summary($proyek['work_area']);
            $data[]=array_merge_without($proyek, ['tender'], [
                'perusahaan'    =>get_info_perusahaan(),
                'estimate_cost' =>$after_diskon,
                'proyek'        =>array_merge_without($proyek['proyek'], ['work_area'], []),
                'work_area'     =>$work_area,
                'progress'      =>$akumulasi_summary['progress'],
                'count_pekerjaan_pending' =>$akumulasi_summary['count_pending'],
                'count_pekerjaan_applied' =>$akumulasi_summary['count_applied'],
                'count_pekerjaan_rejected'=>$akumulasi_summary['count_rejected'],
                'count_pekerjaan'         =>$akumulasi_summary['count_pending']+$akumulasi_summary['count_applied']+$akumulasi_summary['count_rejected']
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$proyek_summary['current_page'],
            'last_page'     =>$proyek_summary['last_page'],
            'data'          =>$data
        ]);
    }

    public function get_summary(Request $request, $id)
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $proyek_summary=ProyekReportModel::with("proyek", "proyek.kapal", "tender", "tender.shipyard")
            ->where("id_proyek", $req['id_proyek'])
            ->first()
            ->toArray();

        $general_diskon=($proyek_summary['tender']['general_diskon_persen']/100)*$proyek_summary['tender']['yard_total_quote'];
        $after_diskon=$proyek_summary['tender']['yard_total_quote']-$general_diskon;

        $work_area=calculate_summary_work_area($proyek_summary['work_area']);
        $akumulasi_summary=calculate_akumulasi_summary($proyek_summary['work_area']);
        $data=array_merge_without($proyek_summary, ['tender'], [
            'perusahaan'    =>get_info_perusahaan(),
            'estimate_cost' =>$after_diskon,
            'proyek'        =>array_merge_without($proyek_summary['proyek'], ['work_area'], []),
            'work_area'     =>$work_area,
            'progress'      =>$akumulasi_summary['progress'],
            'count_pekerjaan_pending' =>$akumulasi_summary['count_pending'],
            'count_pekerjaan_applied' =>$akumulasi_summary['count_applied'],
            'count_pekerjaan_rejected'=>$akumulasi_summary['count_rejected'],
            'count_pekerjaan'         =>$akumulasi_summary['count_pending']+$akumulasi_summary['count_applied']+$akumulasi_summary['count_rejected']
        ]);

        return response()->json([
            'data'  =>$data
        ]);
    }

    //SUMMARY DETAIL
    public function add_detail(Request $request)
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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipyard', 'shipmanager'])){
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
                    if(in_array($login_data['role'], ["shipowner", "shipyard"])){
                        $query->where("id_user", $login_data['id_user']);
                    }
                }),
                function($attr, $value, $fail)use($login_data){
                    //detail
                    $b=ProyekReportDetailModel::with("summary")->where("id_proyek_report_detail", $value);
                    if($b->count()==0){
                        return $fail("The selected id proyek report detail is invalid.");
                    }

                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $b->first()['summary']['id_proyek']);
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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipyard', 'shipmanager'])){
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
                    if(in_array($login_data['role'], ["shipowner", "shipyard"])){
                        $query->where("id_user", $login_data['id_user']);
                    }
                }),
                function($attr, $value, $fail)use($login_data){
                    //detail
                    $b=ProyekReportDetailModel::with("summary")->where("id_proyek_report_detail", $value);
                    if($b->count()==0){
                        return $fail("the selected id proyek report detail is invalid.");
                    }

                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $b->first()['summary']['id_proyek']);
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

    public function gets_summary_detail(Request $request, $id)
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $detail=ProyekReportDetailModel::with("created_by")->whereHas("summary", function($query)use($req){
                $query->where("id_proyek", $req['id_proyek']);
            })
            ->where("type", $req['type'])
            ->orderByDesc("id_proyek_report_detail")
            ->paginate(trim($req['per_page']))
            ->toArray();

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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipyard', 'shipmanager'])){
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
                    $b=ProyekReportDetailModel::with("summary")->where("id_proyek_report_detail", $value);
                    if($b->count()==0){
                        return $fail("the selected id proyek report detail is invalid.");
                    }

                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $b->first()['summary']['id_proyek']);
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $detail=ProyekReportDetailModel::with("summary")->where("id_proyek_report_detail", $req['id_proyek_report_detail'])->first();

        return response()->json([
            'data'          =>$detail
        ]);
    }

    //WORK AREA
    public function update_progress(Request $request, $id)
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
            'sfi'       =>[
                "required",
                function($attr, $value, $fail)use($req){
                    $v=ProyekReportModel::where("id_proyek", $req['id_proyek'])->first();
                    if(is_null($v)){
                        return $fail("id proyek not found");
                    }

                    if(!found_sfi_pekerjaan_work_area($value, $v['work_area'])){
                        return $fail("sfi pekerjaan not found");
                    }
                    return true;
                }
            ],
            'status'    =>"required|in:in_progress,complete",
            'progress'  =>"required|numeric|between:0,100",
            'start'     =>"required|date_format:Y-m-d",
            'end'       =>"required|date_format:Y-m-d|after_or_equal:start",
            'responsible'       =>"required|in:shipowner,shipyard",
            'persetujuan'       =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $in=['pending', 'rejected', 'applied'];
                    if($login_data['role']=="shipyard"){
                        $in=['pending'];
                    }

                    if(!in_array($value, $in)){
                        return $fail("persetujuan must ".implode(", ", $in));
                    }
                    return true;
                }
            ],
            'comment'   =>[Rule::requiredIf(!isset($req['comment']))]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            //pic
            $summary=ProyekReportModel::where("id_proyek", $req['id_proyek'])->first();
            $pic=ProyekReportPicModel::where("id_proyek_report", $summary['id_proyek_report'])
                ->where("id_user", $login_data['id_user']);
            if($pic->count()>0){
                $pic->touch();
            }
            else{
                ProyekReportPicModel::create([
                    'id_proyek_report'  =>$summary['id_proyek_report'],
                    'id_user'   =>$login_data['id_user']
                ]);
            }

            //proyek
            $proyek_report=ProyekReportModel::where("id_proyek", $req['id_proyek'])->lockForUpdate()->first();
            $proyek_report->update([
                'work_area' =>update_report_work_area($proyek_report['work_area'], $login_data, $req)
            ]);
        });
        
        return response()->json([
            'status'=>"ok"
        ]);
    }

    //PIC
    public function gets_summary_pic(Request $request, $id)
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $proyek_pic=ProyekReportPicModel::with("user")
            ->whereHas("summary", function($query)use($req){
                $query->where("id_proyek", $req['id_proyek']);
            })
            ->orderByDesc("updated_at")
            ->get();

        return response()->json([
            'data'  =>$proyek_pic
        ]);
    }
}
