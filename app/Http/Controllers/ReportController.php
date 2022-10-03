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
        $proyek_summary=ProyekReportModel::with("proyek", "proyek.kapal", "proyek.kapal.perusahaan");
        //q
        $proyek_summary=$proyek_summary->where(function($q)use($req){
            $q->whereHas("proyek.kapal", function($query)use($req){
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
        foreach($proyek_summary['data'] as $val){
            $data[]=array_merge_without($val, ['work_area'], [
                'proyek'=>array_merge_without($val['proyek'], ['work_area'])
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
        $proyek_summary=ProyekReportModel::with("proyek", "proyek.kapal", "proyek.kapal.perusahaan", "tender", "tender.shipyard")
            ->where("id_proyek", $req['id_proyek'])
            ->first()
            ->toArray();

        $general_diskon=($proyek_summary['tender']['general_diskon_persen']/100)*$proyek_summary['tender']['yard_total_quote'];
        $after_diskon=$proyek_summary['tender']['yard_total_quote']-$general_diskon;

        $summary=get_all_summary_work_area($proyek_summary['work_area'], [
            'total_harga_aktual', 
            'progress',
            'date_min_max',
            'last_change'
        ]);
        $data=array_merge_without($proyek_summary, ['tender'], [
            'estimate_cost' =>$after_diskon,
            'proyek'        =>array_merge_without($proyek_summary['proyek'], ['work_area'], []),
            'work_area'     =>$summary['items'],
            'summary_work_area' =>array_merge_without($summary, ['items', 'type'])
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
        $detail=ProyekReportDetailModel::with("created_by")->where("id_proyek_report_detail", $req['id_proyek_report_detail'])->first();

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
        $report=ProyekReportModel::with("tender")->where("id_proyek", $req['id_proyek'])->first();
        $validate_work_area=validation_report_work_area($req['work_area'], $report['tender']['work_area']);
        if($validate_work_area['error']){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validate_work_area['data']
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data, $report){
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
            $proyek_report=ProyekReportModel::with("tender")->where("id_proyek", $req['id_proyek'])->lockForUpdate()->first();
            $data=generate_report_work_area($req['work_area'], $proyek_report['tender']['work_area'], $proyek_report['work_area'], $login_data);
            $proyek_report->update([
                'work_area' =>$data['work_area'],
                'work_area_update_history'  =>array_merge($data['update_history'], $proyek_report['work_area_update_history'])
            ]);
        });
        
        return response()->json([
            'status'=>"ok",
            'data'  =>$a
        ]);
    }

    public function checklist_progress(Request $request, $id)
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
                function($attr, $value, $fail)use($req, $login_data){
                    $v=ProyekReportModel::where("id_proyek", $req['id_proyek'])->first();
                    if(is_null($v)){
                        return $fail("id proyek not found");
                    }

                    $resp=$login_data['role']=="shipyard"?"shipyard":"all";
                    if(!found_sfi_pekerjaan_work_area($value, $v['work_area'], $resp)){
                        return $fail("sfi pekerjaan not found or not allowed");
                    }
                    return true;
                }
            ],
            'type'      =>[
                "required",
                "in:shipowner,shipyard",
                function($attr, $value, $fail)use($login_data){
                    if($login_data['role']=="shipyard" && $value=="shipowner"){
                        return $attr("shipyard not allowed update checklist shipowner");
                    }
                }
            ],
            'checked'   =>"required|boolean"
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
                'work_area' =>update_report_work_area_checklist($proyek_report['work_area'], $login_data, $req)
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
