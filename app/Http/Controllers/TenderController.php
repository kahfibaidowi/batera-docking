<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\TenderModel;
use App\Models\ProyekReportModel;
use App\Models\UserModel;
use App\Repository\TenderRepo;

class TenderController extends Controller
{
    //TENDER
    public function add_tender(Request $request)
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
            'id_user'           =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $v=UserModel::where("id_user", $value)
                        ->where("role", "shipyard");

                    if($v->count()==0){
                        return $fail("The selected id_user(responsible) is invalid or role not shipyard");
                    }
                }
            ],
            'yard_total_quote'  =>"required|numeric|min:0",
            'general_diskon_persen' =>"required|numeric|between:0,100",
            'additional_diskon' =>"required|numeric|min:0",
            'sum_internal_adjusment'=>"required|numeric|min:0",
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
            ],
            'no_kontrak'    =>"required",
            'komentar'      =>[
                Rule::requiredIf(!isset($req['komentar']))
            ],
            'nama_galangan' =>"required",
            'lokasi_galangan'=>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $tender=TenderModel::create([
                'id_user'           =>$req['id_user'],
                'yard_total_quote'  =>$req['yard_total_quote'],
                'general_diskon_persen' =>$req['general_diskon_persen'],
                'additional_diskon' =>$req['additional_diskon'],
                'sum_internal_adjusment'=>$req['sum_internal_adjusment'],
                'dokumen_kontrak'   =>$req['dokumen'],
                'no_kontrak'        =>$req['no_kontrak'],
                'komentar'          =>$req['komentar'],
                'nama_galangan'     =>$req['nama_galangan'],
                'lokasi_galangan'   =>$req['lokasi_galangan'],
                'work_area'         =>null
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update_tender(Request $request, $id)
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
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                Rule::exists("App\Models\TenderModel")
            ],
            'yard_total_quote'  =>"required|numeric|min:0",
            'general_diskon_persen' =>"required|numeric|between:0,100",
            'additional_diskon' =>"required|numeric|min:0",
            'sum_internal_adjusment'=>"required|numeric|min:0",
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
            ],
            'no_kontrak'    =>"required",
            'komentar'      =>[
                Rule::requiredIf(!isset($req['komentar']))
            ],
            'nama_galangan' =>"required",
            'lokasi_galangan'=>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            TenderModel::where("id_tender", $req['id_tender'])
                ->update([
                    'yard_total_quote'  =>$req['yard_total_quote'],
                    'general_diskon_persen' =>$req['general_diskon_persen'],
                    'additional_diskon' =>$req['additional_diskon'],
                    'sum_internal_adjusment'=>$req['sum_internal_adjusment'],
                    'dokumen_kontrak'   =>$req['dokumen'],
                    'no_kontrak'        =>$req['no_kontrak'],
                    'komentar'          =>$req['komentar'],
                    'nama_galangan'     =>$req['nama_galangan'],
                    'lokasi_galangan'   =>$req['lokasi_galangan'],
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function delete_tender(Request $request, $id)
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
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                Rule::exists("App\Models\TenderModel")
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
            TenderModel::where("id_tender", $req['id_tender'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function select_tender(Request $request, $id)
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
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                Rule::unique("App\Models\ProyekReportModel", "id_tender"),
                Rule::exists("App\Models\TenderModel", "id_tender")
            ],
            'id_proyek'         =>[
                "required",
                Rule::unique("App\Models\ProyekReportModel", "id_proyek"),
                Rule::exists("App\Models\ProyekModel", "id_proyek")
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
            $proyek=ProyekModel::where("id_proyek", $req['id_proyek'])->first();

            ProyekReportModel::create([
                "id_proyek"     =>$req['id_proyek'],
                "id_tender"     =>$req['id_tender'],
                "summary_detail"=>"",
                "approved_by"   =>"",
                "approved"      =>"",
                "proyek_start"  =>$proyek['off_hire_start'],
                "proyek_end"    =>$proyek['off_hire_end'],
                "proyek_period" =>$proyek['off_hire_period'],
                "master_plan"   =>"",
                "status"        =>"preparation",
                "state"         =>"pending",
                "tipe_proyek"   =>"",
                "prioritas"     =>"",
                "partner"       =>"",
                "deskripsi"     =>"",
                "work_area"     =>null
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function unselect_tender(Request $request, $id)
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
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                Rule::exists("App\Models\ProyekReportModel", "id_tender")
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
            ProyekReportModel::where("id_tender", $req['id_tender'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_tender(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager', 'director', 'shipyard'])){
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
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $tender=TenderRepo::gets_tender($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$tender['current_page'],
            'last_page'     =>$tender['last_page'],
            'data'          =>$tender['data']
        ]);
    }

    public function get_tender(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipyard', 'shipmanager', 'director'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender' =>"required|exists:App\Models\TenderModel,id_tender"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $tender=TenderRepo::get_tender($req['id_tender']);

        return response()->json([
            'data'  =>$tender
        ]);
    }

    //WORK AREA
    public function update_tender_work_area(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager', 'shipyard'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender' =>[
                "required",
                Rule::exists("App\Models\TenderModel"),
                function($attr, $value, $fail)use($login_data){
                    //tender
                    $p=TenderModel::where("id_tender", $value);
                    
                    //--shipyard
                    if($login_data['role']=="shipyard"){
                        $p=$p->where("id_user", $login_data['id_user']);
                    }
                    if($p->count()==0){
                        return $fail("The selected id tender is invalid.");
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
        DB::transaction(function() use($req){
            TenderModel::where("id_tender", $req['id_tender'])
                ->update([
                    'work_area' =>$req['work_area']
                ]);
        });
        
        return response()->json([
            'status'=>"ok"
        ]);
    }
}
