<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\KapalModel;

class TrackingController extends Controller
{
    // public function gets_proyek_summary(Request $request){
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipyard', 'shipmanager'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $validation=Validator::make($req, [
    //         'per_page'  =>[
    //             Rule::requiredIf(!isset($req['per_page'])),
    //             "integer",
    //             "min:1"
    //         ],
    //         'q'         =>[
    //             Rule::requiredIf(!isset($req['q']))
    //         ],
    //         'status'    =>"required|in:all,preparation,in_progress,evaluasi"
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     $proyek_summary=ProyekReportModel::with("proyek", "proyek.kapal");
    //     //q
    //     $proyek_summary=$proyek_summary->where(function($q)use($req){
    //         $q->whereHas("proyek", function($query)use($req){
    //             $query->where("nama_proyek", "ilike", "%".$req['q']."%");
    //         })
    //         ->orWhereHas("proyek.kapal", function($query)use($req){
    //             $query->where("nama_kapal", "ilike", "%".$req['q']."%");
    //         });
    //     });
    //     //status
    //     if($req['status']!="all"){
    //         $proyek_summary=$proyek_summary->where("status", $req['status']);
    //     }
    //     //shipowner
    //     if($login_data['role']=="shipowner"){
    //         $proyek_summary=$proyek_summary->whereHas("proyek.kapal", function($query)use($login_data){
    //             $query->where("id_user", $login_data['id_user']);
    //         });
    //     }
    //     //shipyard
    //     if($login_data['role']=="shipyard"){
    //         $proyek_summary=$proyek_summary->whereHas("tender", function($query)use($login_data){
    //             $query->where("id_user", $login_data['id_user']);
    //         });
    //     }

    //     //order & paginate
    //     $proyek_summary=$proyek_summary->orderByDesc("id_proyek")
    //         ->paginate(trim($req['per_page']))
    //         ->toArray();


    //     $data=[];
    //     foreach($proyek_summary['data'] as $proyek){
    //         $general_diskon=($proyek['tender']['general_diskon_persen']/100)*$proyek['tender']['yard_total_quote'];
    //         $after_diskon=$proyek['tender']['yard_total_quote']-$general_diskon;

            
    //         $work_area=calculate_summary_work_area($proyek['work_area']);
    //         $akumulasi_summary=calculate_akumulasi_summary($proyek['work_area']);
    //         $data[]=array_merge_without($proyek, ['tender'], [
    //             'perusahaan'    =>get_info_perusahaan(),
    //             'estimate_cost' =>$after_diskon,
    //             'proyek'        =>array_merge_without($proyek['proyek'], ['work_area'], []),
    //             'work_area'     =>$work_area,
    //             'progress'      =>$akumulasi_summary['progress'],
    //             'count_pekerjaan_pending' =>$akumulasi_summary['count_pending'],
    //             'count_pekerjaan_applied' =>$akumulasi_summary['count_applied'],
    //             'count_pekerjaan_rejected'=>$akumulasi_summary['count_rejected'],
    //             'count_pekerjaan'         =>$akumulasi_summary['count_pending']+$akumulasi_summary['count_applied']+$akumulasi_summary['count_rejected']
    //         ]);
    //     }

    //     return response()->json([
    //         'first_page'    =>1,
    //         'current_page'  =>$proyek_summary['current_page'],
    //         'last_page'     =>$proyek_summary['last_page'],
    //         'data'          =>$data
    //     ]);
    // }
}
