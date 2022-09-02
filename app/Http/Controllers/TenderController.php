<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\ProyekPekerjaanModel;
use App\Models\ProyekBiayaModel;
use App\Models\TenderModel;
use App\Models\TenderPekerjaanModel;
use App\Models\TenderPekerjaanRencanaModel;
use App\Models\ProyekTenderModel;
use App\Models\ProyekTenderPekerjaanModel;
use App\Models\ProyekTenderPekerjaanRencanaModel;

class TenderController extends Controller
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
                    if($login_data['role']=="shipyard"){
                        return false;
                    }
                    return true;
                }),
                Rule::exists("App\Models\UserModel")->where(function($query)use($req){
                    return $query->where("id_user", $req['id_user'])
                        ->where("role", "shipyard");
                })
            ],
            'id_proyek'     =>"required|exists:App\Models\ProyekModel,id_proyek",
            
            //project/tender detail
            'detail.off_hire_start' =>"required|date_format:Y-m-d",
            'detail.off_hire_end'   =>"required|date_format:Y-m-d|after_or_equal:detail.off_hire_start",
            'detail.off_hire_deviasi'   =>"required|numeric|min:0",
            'detail.off_hire_rate_per_day'  =>"required|numeric|min:0", 
            'detail.off_hire_bunker_per_day'=>"required|numeric|min:0",
            'detail.repair_start'   =>"required|date_format:Y-m-d",
            'detail.repair_end'     =>"required|date_format:Y-m-d|after_or_equal:detail.repair_start",
            'detail.repair_in_dock_start'   =>"required|date_format:Y-m-d",
            'detail.repair_in_dock_end'     =>"required|date_format:Y-m-d|after_or_equal:detail.repair_in_dock_start",
            'detail.repair_additional_day'  =>"required|numeric|min:0",
            'detail.diskon_umum_persen'     =>"required|numeric|between:0,100",
            'detail.diskon_tambahan'        =>"required|numeric|min:0",

            //pekerjaan
            'pekerjaan'           =>[
                'required',
                'array',
                'min:1',
                function($attr, $value, $fail)use($req){
                    $v=ProyekPekerjaanModel::where("id_proyek", $req['id_proyek']);

                    if($v->count()==count($req['pekerjaan'])){
                        return true;
                    }
                    return $fail($attr." not same in data");
                }
            ],
            'pekerjaan.*.id_proyek_pekerjaan'   =>[
                'required',
                'distinct',
                function($attr, $value, $fail)use($req){
                    $v=ProyekPekerjaanModel::where("id_proyek", $req['id_proyek'])
                        ->where("id_proyek_pekerjaan", $value);

                    if($v->count()>0){
                        return true;
                    }
                    return $fail($attr." is invalid");
                }
            ],
            'pekerjaan.*.qty'       =>"required|numeric|min:0",
            'pekerjaan.*.harga_satuan'  =>"required|numeric|min:0",
            'pekerjaan.*.deadline'  =>"required|date_format:Y-m-d|after_or_equal:detail.repair_start|before_or_equal:detail.repair_end",

            //pekerjaan rencana
            'pekerjaan.*.rencana'     =>[
                "required",
                "array",
                "min:1",
                function($attr, $value, $fail)use($req){
                    $exp_attr=explode_request($attr);
                    $pekerjaan=$req['pekerjaan'][$exp_attr[1]];
                    $rencana=$req['pekerjaan'][$exp_attr[1]]['rencana'];

                    if(sum_data_in_array($rencana, "qty")==$pekerjaan['qty']){
                        return true;
                    }
                    return $fail($attr." total value must 100%");
                }
            ],
            'pekerjaan.*.rencana.*'     =>"required_array_keys:keterangan",
            'pekerjaan.*.rencana.*.qty' =>"required|numeric|min:0",
            'pekerjaan.*.rencana.*.tgl_rencana' =>[
                'required',
                'date_format:Y-m-d',
                'after_or_equal:detail.repair_start',
                'before_or_equal:pekerjaan.*.deadline',
                function($attr, $value, $fail)use($req){
                    $exp_attr=explode_request($attr);
                    $rencana=$req['pekerjaan'][$exp_attr[1]]['rencana'];
                    
                    if(is_array_distinct($rencana, "tgl_rencana", $value)){
                        return true;
                    }
                    return $fail($attr." has duplicate value");
                }
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //TENDER STATUS
        $v=ProyekModel::where("id_proyek", $req['id_proyek'])->first();
        if($v['tender_status']=="complete"){
            return response()->json([
                'error' =>"TENDER_CLOSED"
            ], 500);
        }
        if($v['tender_status']=="pending"){
            return response()->json([
                'error' =>"TENDER_UNOPENED"
            ], 500);
        }
        if($v['status']=="draft"){
            return response()->json([
                'error' =>"TENDER_UNPUBLISHED"
            ], 500);
        }

        //DOUBLE DATA
        $id_user=$login_data['role']!="shipyard"?$req['id_user']:$login_data['id_user'];
        $v=TenderModel::where("id_proyek", $req['id_proyek'])->where("id_user", $id_user);
        if($v->count()>0){
            return response()->json([
                'error' =>"MULTIPLE_INPUT_TENDER_NOT_ALLOWED"
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data, $id_user){
            //tender
            $off_hire_period=count_day($req['detail']['off_hire_start'], $req['detail']['off_hire_end']);
            $repair_period=count_day($req['detail']['repair_start'], $req['detail']['repair_end']);
            $repair_in_dock_period=count_day($req['detail']['repair_in_dock_start'], $req['detail']['repair_in_dock_end']);
            $tender=TenderModel::create([
                'id_user'   =>$id_user,
                'id_proyek' =>$req['id_proyek'],
                'off_hire_start'    =>$req['detail']['off_hire_start'],
                'off_hire_end'      =>$req['detail']['off_hire_end'],
                'off_hire_period'   =>$off_hire_period,
                'off_hire_deviasi'  =>$req['detail']['off_hire_deviasi'],
                'off_hire_rate_per_day' =>$req['detail']['off_hire_rate_per_day'],
                'off_hire_bunker_per_day'=>$req['detail']['off_hire_bunker_per_day'],
                'repair_start'      =>$req['detail']['repair_start'],
                'repair_end'        =>$req['detail']['repair_end'],
                'repair_period'     =>$repair_period,
                'repair_in_dock_start'  =>$req['detail']['repair_in_dock_start'],
                'repair_in_dock_end'    =>$req['detail']['repair_in_dock_end'],
                'repair_in_dock_period' =>$repair_in_dock_period,
                'repair_additional_day' =>$req['detail']['repair_additional_day'],
                'diskon_umum_persen'    =>$req['detail']['diskon_umum_persen'],
                'diskon_tambahan'       =>$req['detail']['diskon_tambahan'],
                'status'            =>"published"
            ]);
            $tender_id=$tender->id_tender;

            //tender pekerjaan
            $proyek_pekerjaan=ProyekPekerjaanModel::where("id_proyek", $req['id_proyek'])->get();
            foreach($req['pekerjaan'] as $val){
                $pekerjaan=[];
                foreach($proyek_pekerjaan as $find){
                    if($find['id_proyek_pekerjaan']==$val['id_proyek_pekerjaan']){
                        $pekerjaan=$find;
                        break;
                    }
                }

                $tender_pekerjaan=TenderPekerjaanModel::create([
                    'id_tender' =>$tender_id,
                    'pekerjaan' =>$pekerjaan['pekerjaan'],
                    'satuan'    =>$pekerjaan['satuan'],
                    'kategori_1'=>$pekerjaan['kategori_1'],
                    'kategori_2'=>$pekerjaan['kategori_2'],
                    'kategori_3'=>$pekerjaan['kategori_3'],
                    'kategori_4'=>$pekerjaan['kategori_4'],
                    'id_proyek_pekerjaan'   =>$val['id_proyek_pekerjaan'],
                    'qty'           =>$val['qty'],
                    'harga_satuan'  =>$val['harga_satuan'],
                    'deadline'      =>$val['deadline']  
                ]);
                $tender_pekerjaan_id=$tender_pekerjaan->id_tender_pekerjaan;

                //tender pekerjaan rencana
                foreach($val['rencana'] as $renc){
                    TenderPekerjaanRencanaModel::create(array_merge($renc, [
                        'id_tender_pekerjaan'   =>$tender_pekerjaan_id
                    ]));
                }
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function select_yard(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_tender' =>[
                "required",
                "exists:App\Models\TenderModel,id_tender",
                function($attr, $value, $fail)use($req, $login_data){
                    if($login_data['role']=="shipowner"){
                        $tender=TenderModel::where("id_tender", $req['id_tender'])->with("proyek");
                        if($tender->count()==0){
                            return $fail($attr." not found");
                        }
                        if($tender->first()['proyek']['id_user']!=$login_data['id_user']){
                            return $fail($attr." not found");
                        }
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

        //PROJECT TENDER SUDAH DIPILIH
        $tender=TenderModel::where("id_tender", $req['id_tender'])->with("proyek")->first();
        if($tender['proyek']['tender_status']=="complete"){
            return response()->json([
                'error' =>"TENDER_CLOSED"
            ], 500);
        }

        //PROJECT TENDER DRAFT
        if($tender['status']=="draft"){
            return response()->json([
                'error' =>"TENDER_UNPUBLISHED"
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            //update status tender proyek
            $tender=TenderModel::where("id_tender", $req['id_tender'])->first();
            ProyekModel::where("id_proyek", $tender['id_proyek'])
                ->update([
                    'status'        =>"requisition",
                    'tender_status' =>"complete"
                ]);

            //proyek tender
            $proyek_tender=ProyekTenderModel::create([
                'id_proyek'     =>$tender['id_proyek'],
                'id_user'       =>$tender['id_user'],
                'rencana_off_hire_start'    =>$tender['off_hire_start'],
                'rencana_off_hire_end'      =>$tender['off_hire_end'], 
                'rencana_off_hire_period'   =>$tender['off_hire_period'], 
                'rencana_off_hire_deviasi'  =>$tender['off_hire_deviasi'], 
                'rencana_off_hire_rate_per_day'     =>$tender['off_hire_rate_per_day'], 
                'rencana_off_hire_bunker_per_day'   =>$tender['off_hire_bunker_per_day'], 
                'rencana_repair_start'      =>$tender['repair_start'], 
                'rencana_repair_end'        =>$tender['repair_end'], 
                'rencana_repair_period'     =>$tender['repair_period'], 
                'rencana_repair_in_dock_start'  =>$tender['repair_in_dock_start'], 
                'rencana_repair_in_dock_end'    =>$tender['repair_in_dock_end'], 
                'rencana_repair_in_dock_period' =>$tender['repair_in_dock_period'], 
                'rencana_repair_additional_day' =>$tender['repair_additional_day'], 
                'rencana_diskon_umum_persen'    =>$tender['diskon_umum_persen'], 
                'rencana_diskon_tambahan'       =>$tender['diskon_tambahan']
            ]);
            $proyek_tender_id=$proyek_tender->id_proyek_tender;

            //proyek tender pekerjaan
            $tender_pekerjaan=TenderPekerjaanModel::where("id_tender", $req['id_tender'])->get();
            foreach($tender_pekerjaan as $val){
                $proyek_tender_pekerjaan=ProyekTenderPekerjaanModel::create([
                    'id_proyek_tender'  =>$proyek_tender_id,
                    'pekerjaan'         =>$val['pekerjaan'],
                    'satuan'            =>$val['satuan'],
                    'kategori_1'        =>$val['kategori_1'],
                    'kategori_2'        =>$val['kategori_2'],
                    'kategori_3'        =>$val['kategori_3'],
                    'kategori_4'        =>$val['kategori_4'],
                    'id_proyek_pekerjaan'   =>$val['id_proyek_pekerjaan'],
                    'rencana_qty'           =>$val['qty'],
                    'rencana_harga_satuan'  =>$val['harga_satuan'],
                    'rencana_deadline'      =>$val['deadline']  
                ]);
                $proyek_tender_pekerjaan_id=$proyek_tender_pekerjaan->id_proyek_tender_pekerjaan;

                //proyek tender pekerjaan rencana
                $tender_pekerjaan_rencana=TenderPekerjaanRencanaModel::where("id_tender_pekerjaan", $val['id_tender_pekerjaan'])->get();
                foreach($tender_pekerjaan_rencana as $renc){
                    ProyekTenderPekerjaanRencanaModel::create([
                        'id_proyek_tender_pekerjaan'=>$proyek_tender_pekerjaan_id,
                        'qty'           =>$renc['qty'],
                        'tgl_rencana'   =>$renc['tgl_rencana'],
                        'keterangan'    =>$renc['keterangan']
                    ]);
                }
            }
        });
        
        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function cancel_select_yard(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_proyek' =>[
                "required",
                "exists:App\Models\ProyekModel,id_proyek",
                function($attr, $value, $fail)use($req, $login_data){
                    if($login_data['role']=="shipowner"){
                        $proyek=ProyekModel::where("id_proyek", $value);
                        if($proyek->count()==0){
                            return $fail($attr." not found");
                        }
                        if($proyek->first()['id_user']!=$login_data['id_user']){
                            return $fail($attr." not found");
                        }
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
        DB::transaction(function() use($req){
            //update proyek
            ProyekModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    'status'        =>"published",
                    'tender_status' =>"pending"
                ]);
            
            //delete proyek tender
            ProyekTenderModel::where("id_proyek", $req['id_proyek'])->delete();
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
