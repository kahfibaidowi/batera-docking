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

class TenderController extends Controller
{
    //TENDER
    public function add_tender(Request $request)
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
            'id_proyek'         =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $v=ProyekModel::where("id_proyek", $value)->where("status", "published")->first();
                    if(is_null($v)){
                        return $fail("The selected id proyek is invalid or proyek not published.");
                    }
                    return true;
                }
            ],
            'id_user'           =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $v=UserModel::where("id_user", $value)
                        ->where("role", "shipyard");
                    
                    if($login_data['role']=="shipyard"){
                        $v=$v->where("id_user", $login_data['id_user']);
                    }

                    if($v->count()==0){
                        return $fail("The selected id user is invalid or role not shipyard");
                    }
                },
                function($attr, $value, $fail)use($req){
                    $v=TenderModel::where("id_proyek", $req['id_proyek'])
                        ->where("id_user", $value);
                    if($v->count()>0){
                        return $fail("multiple input tender not allowed in one project");
                    }
                    return true;
                }
            ],
            'yard_total_quote'  =>"required|numeric|min:0",
            'general_diskon_persen' =>"required|numeric|between:0,100",
            'additional_diskon' =>"required|numeric|min:0",
            'sum_internal_adjusment'=>"required|numeric|min:0"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $proyek=ProyekModel::where("id_proyek", $req['id_proyek'])->first();

            TenderModel::create([
                'id_proyek'         =>$req['id_proyek'],
                'id_user'           =>$req['id_user'],
                'yard_total_quote'  =>$req['yard_total_quote'],
                'general_diskon_persen' =>$req['general_diskon_persen'],
                'additional_diskon' =>$req['additional_diskon'],
                'sum_internal_adjusment'=>$req['sum_internal_adjusment'],
                'work_area'         =>generate_tender_work_area($proyek['work_area']),
                'status'            =>"draft"
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
        if(!in_array($login_data['role'], ['admin', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $v=TenderModel::where("id_tender", $value)->where("status", "draft");
                    if($login_data['role']=="shipyard"){
                        $v=$v->where("id_user", $login_data['id_user']);
                    }
                    $v=$v->first();
                    
                    if(is_null($v)){
                        return $fail("The selected id tender is invalid or tender is published.");
                    }
                    return true;
                }
            ],
            'yard_total_quote'  =>"required|numeric|min:0",
            'general_diskon_persen' =>"required|numeric|between:0,100",
            'additional_diskon' =>"required|numeric|min:0",
            'sum_internal_adjusment'=>"required|numeric|min:0"
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
                    'sum_internal_adjusment'=>$req['sum_internal_adjusment']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function publish_tender(Request $request, $id)
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
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $v=TenderModel::where("id_tender", $value)->where("status", "draft");
                    if($login_data['role']=="shipyard"){
                        $v=$v->where("id_user", $login_data['id_user']);
                    }
                    $v=$v->first();
                    
                    if(is_null($v)){
                        return $fail("The selected id tender is invalid or tender is published.");
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
            TenderModel::where("id_tender", $req['id_tender'])
                ->update([
                    'status'=>"published"
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
        if(!in_array($login_data['role'], ['admin', 'shipyard', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                Rule::exists("App\Models\TenderModel")->where(function($query)use($login_data){
                    if($login_data['role']=="shipyard"){
                        return $query->where("id_user", $login_data['id_user']);
                    }
                })
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
            TenderModel::where("id_tender", $req['id_tender'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_tender_proyek(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipyard', 'shipmanager', 'shipowner'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek' =>"required|exists:App\Models\ProyekModel,id_proyek"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $tender=TenderModel::with("proyek", "shipyard")
            ->where("id_proyek", $req['id_proyek'])
            ->where("status", "published")
            ->orderBy("id_tender")
            ->get()
            ->toArray();
        
        $data=[];
        foreach($tender as $val){
            $data[]=array_merge_without($val, ['work_area'], [
                'proyek'        =>array_merge_without($val['proyek'], ['work_area'])
            ]);
        }

        return response()->json([
            'data'  =>$data
        ]);
    }

    public function select_tender(Request $request, $id)
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
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $t=TenderModel::where("id_tender", $value)
                        ->where("status", "published");

                    //found
                    if($t->count()==0){
                        return $fail("The selected id tender is invalid or tender not published.");
                    }
                    $t=$t->first();

                    //proyek
                    $p=ProyekModel::where("id_proyek", $t['id_proyek'])
                        ->where("status", "published");
                    if($login_data['role']=="shipowner"){
                        $p=$p->whereHas("kapal", function($query)use($login_data){
                            $query->where("id_user", $login_data['id_user']);
                        });
                    }
                    if($p->count()==0){
                        return $fail("id tender not allowed.");
                    }

                    //proyek report
                    $pr=ProyekReportModel::where("id_proyek", $t['id_proyek']);
                    if($pr->count()>0){
                        return $fail("proyek already selected.");
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
            $tender=TenderModel::where("id_tender", $req['id_tender'])->first();
            $proyek=ProyekModel::where("id_proyek", $tender['id_proyek'])->first();

            $work_area=generate_report_work_area($tender['work_area']);
            ProyekReportModel::create([
                "id_proyek"     =>$proyek['id_proyek'],
                "id_tender"     =>$req['id_tender'],
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
                "work_area"     =>$work_area
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
        if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender'         =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $t=TenderModel::where("id_tender", $value)
                        ->where("status", "published");

                    //found
                    if($t->count()==0){
                        return $fail("The selected id tender is invalid or tender not published.");
                    }
                    $t=$t->first();

                    //proyek
                    $p=ProyekModel::where("id_proyek", $t['id_proyek'])
                        ->where("status", "published");
                    if($login_data['role']=="shipowner"){
                        $p=$p->whereHas("kapal", function($query)use($login_data){
                            $query->where("id_user", $login_data['id_user']);
                        });
                    }
                    if($p->count()==0){
                        return $fail("id tender not allowed.");
                    }
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
        if(!in_array($login_data['role'], ['admin', 'shipyard', 'shipmanager'])){
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
            'status'    =>"in:all,draft,published"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $tender=TenderModel::with("proyek", "shipyard");
        //status
        if($req['status']!="all"){
            $tender=$tender->where("status", $req['status']);
        }
        //shipyard
        if($login_data['role']=="shipyard"){
            $tender=$tender->where("id_user", $login_data['id_user']);
        }
        //order & paginate
        $tender=$tender->orderByDesc("id_tender")
            ->paginate(trim($req['per_page']))
            ->toArray();
        
        $data=[];
        foreach($tender['data'] as $val){
            $data[]=array_merge_without($val, ['work_area'], [
                'proyek'        =>array_merge_without($val['proyek'], ['work_area'])
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$tender['current_page'],
            'last_page'     =>$tender['last_page'],
            'data'          =>$data
        ]);
    }

    public function get_tender(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipyard', 'shipmanager', 'shipowner'])){
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
        $tender=TenderModel::with("proyek", "shipyard")
            ->where("id_tender", $req['id_tender'])
            ->orderBy("id_tender")
            ->first()
            ->toArray();
        
        $offhire_rate=($tender['proyek']['off_hire_period']+$tender['proyek']['off_hire_deviasi'])*$tender['proyek']['off_hire_rate_per_day'];
        $offhire_bunker=($tender['proyek']['off_hire_period']+$tender['proyek']['off_hire_deviasi'])*$tender['proyek']['off_hire_bunker_per_day'];
        $offhire_cost=$offhire_rate+$offhire_bunker;
        $owner_cost=get_owner_cost($tender['proyek']['work_area']);
        $owner_total_cost=$offhire_cost+$owner_cost;
        $general_diskon=($tender['general_diskon_persen']/100)*$tender['yard_total_quote'];
        $after_diskon=$tender['yard_total_quote']-$general_diskon;

        $data=array_merge($tender, [
            'proyek'        =>array_merge_without($tender['proyek'], ['work_area'], []),
            'off_hire_cost' =>$offhire_cost,
            'owner_cost'    =>$owner_cost,
            'owner_total_cost'=>$owner_total_cost,
            'yard_total_quote'=>$tender['yard_total_quote'],
            'general_diskon'=>$general_diskon,
            'after_diskon'  =>$after_diskon,
            'additional_diskon'=>$tender['additional_diskon'],
            'sum_internal_adjusment'=>$tender['sum_internal_adjusment'],
            'work_area'     =>get_summary_work_area($tender['work_area'], ['total_harga_kontrak', 'total_harga_budget'])
        ]);

        return response()->json([
            'data'  =>$data
        ]);
    }

    //TENDER WORK AREA
    public function update_tender_work_area(Request $request, $id)
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
        $req['id_tender']=$id;
        $validation=Validator::make($req, [
            'id_tender' =>[
                "required",
                function($attr, $value, $fail)use($login_data){
                    $v=TenderModel::where("id_tender", $value)->where("status", "draft");
                    if($login_data['role']=="shipyard"){
                        $v=$v->where("id_user", $login_data['id_user']);
                    }
                    $v=$v->first();

                    if(is_null($v)){
                        return $fail("The selected id tender is invalid or tender is published.");
                    }
                    return true;
                }
            ],
            'sfi'       =>[
                "required",
                function($attr, $value, $fail)use($req){
                    $v=TenderModel::where("id_tender", $req['id_tender'])->first();
                    if(is_null($v)){
                        return $fail("id tender not found");
                    }

                    if(!found_sfi_pekerjaan_work_area($value, $v['work_area'], "shipyard")){
                        return $fail("sfi pekerjaan not found or not allowed");
                    }
                    return true;
                }
            ],
            'start'     =>"required|date_format:Y-m-d",
            'end'       =>"required|date_format:Y-m-d|after_or_equal:start",
            'volume'    =>"required|numeric|min:0",
            'harga_satuan_kontrak'  =>"required|numeric|min:0"
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
            $tender=TenderModel::where("id_tender", $req['id_tender'])->lockForUpdate()->first();
            $tender->update([
                'work_area' =>update_tender_work_area($tender['work_area'], $req)
            ]);
        });
        
        return response()->json([
            'status'=>"ok"
        ]);
    }
}
