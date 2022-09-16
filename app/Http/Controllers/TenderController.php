<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\TenderModel;

class TenderController extends Controller
{

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
            'id_proyek'         =>"required|exists:App\Models\ProyekModel,id_proyek",
            'id_user'           =>[
                "required",
                Rule::exists("App\Models\UserModel")->where(function($query){
                    $query->where("role", "shipyard");
                })
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
            TenderModel::create([
                'id_proyek'         =>$req['id_proyek'],
                'id_user'           =>$req['id_user'],
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
            'id_tender'         =>"required|exists:App\Models\TenderModel,id_tender",
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
            'id_tender'         =>"required|exists:App\Models\TenderModel,id_tender"
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
        $tender=TenderModel::with("proyek")
            ->where("id_proyek", $req['id_proyek'])
            ->orderBy("id_tender")
            ->get()
            ->toArray();
        
        $data=[];
        foreach($tender as $val){
            $offhire_rate=($val['proyek']['off_hire_period']+$val['proyek']['off_hire_deviasi'])*$val['proyek']['off_hire_rate_per_day'];
            $offhire_bunker=($val['proyek']['off_hire_period']+$val['proyek']['off_hire_deviasi'])*$val['proyek']['off_hire_bunker_per_day'];
            $offhire_cost=$offhire_rate+$offhire_bunker;
            $owner_cost=get_owner_cost($val['proyek']['work_area']);
            $owner_total_cost=$offhire_cost+$owner_cost;
            $general_diskon=($val['general_diskon_persen']/100)*$val['yard_total_quote'];

            $data[]=array_merge($val, [
                'off_hire_cost' =>$offhire_cost,
                'owner_cost'    =>$owner_cost,
                'owner_total_cost'=>$owner_total_cost,
                'yard_total_quote'=>$val['yard_total_quote'],
                'general_diskon'=>$general_diskon,
                'additional_diskon'=>$val['additional_diskon'],
                'sum_internal_adjusment'=>$val['sum_internal_adjusment']
            ]);
        }

        return response()->json([
            'data'  =>$data
        ]);
    }

    public function get_tender(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION

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
        $tender=TenderModel::with("proyek")
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

        $data=array_merge($tender, [
            'off_hire_cost' =>$offhire_cost,
            'owner_cost'    =>$owner_cost,
            'owner_total_cost'=>$owner_total_cost,
            'yard_total_quote'=>$tender['yard_total_quote'],
            'general_diskon'=>$general_diskon,
            'additional_diskon'=>$tender['additional_diskon'],
            'sum_internal_adjusment'=>$tender['sum_internal_adjusment']
        ]);

        return response()->json([
            'data'  =>$data
        ]);
    }
}
