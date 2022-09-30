<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\ProyekReportModel;
use App\Models\KapalModel;
use App\Models\TenderModel;

class TrackingController extends Controller
{

    public function gets_proyek_summary(Request $request)
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
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                'integer',
                'min:1'
            ],
            'q'         =>[
                Rule::requiredIf(!isset($req['q']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $kapal=KapalModel::with("owner", "perusahaan", "proyek", "proyek.report", "proyek.report.tender");
        $kapal=$kapal->where("nama_kapal", "ilike", "%".$req['q']."%");
        //shipowner
        if($login_data['role']=="shipowner"){
            $kapal=$kapal->where("id_user", $login_data['id_user']);
        }
        //get data
        $kapal=$kapal->orderByDesc("id_kapal")
            ->paginate(trim($req['per_page']))->toArray();

        
        $data=[];
        foreach($kapal['data'] as $val){
            $data[]=array_merge($val, [
                'proyek'=>generate_tracking_kapal($val)
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$kapal['current_page'],
            'last_page'     =>$kapal['last_page'],
            'data'          =>$data
        ]);
    }

    public function export_pdf(Request $request)
    {
        // $pdf=Pdf::loadView("pdf.project_export", []);
        // return $pdf->stream();

        // $v=\App\Models\ProyekReportModel::where("id_proyek", 1)->first()->toArray();

        // return response()->json([
        //     'data'  =>get_report_work_area($v['work_area'], [
        //         'progress',
        //         'date_min_max'
        //     ])
        // ]);

        // $v=TenderModel::where("id_tender", 4)->first();

        // return response()->json([
        //     'data'  =>$v['work_area'],
        //     'data_2'=>update_tender_work_area($v['work_area'], [
        //         'sfi'   =>"1.1.1",
        //         'start' =>"2022-10-15",
        //         'end'   =>"2022-10-16",
        //         'harga_satuan_kontrak'=>12300
        //     ])
        // ]);

        // $v=ProyekModel::with("report")->where("id_proyek", 4)->first();

        // return response()->json([
        //     'data'  =>\generate_summary_proyek($v)
        // ]);
    }

    public function export_project_pdf(Request $request, $id)
    {
        // $login_data=$request['fm__login_data'];
        // $req=$request->all();

        // //ROLE AUTHENTICATION
        // if(!in_array($login_data['role'], ['admin', 'shipowner', 'shipmanager'])){
        //     return response()->json([
        //         'error' =>"ACCESS_NOT_ALLOWED"
        //     ], 403);
        // }

        //VALIDATION
        $req['id_proyek']=$id;
        $validation=Validator::make($req, [
            'id_proyek' =>[
                "required",
                function($attr, $value, $fail){
                    //proyek
                    $p=ProyekModel::has("report")->where("id_proyek", $value);

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

        $summary=get_all_summary_work_area($proyek_summary['work_area'], [
            'total_harga_budget', 
            'total_harga_kontrak', 
            'total_harga_aktual', 
            'additional',
            'total_harga_aktual_plus_additional',
            'progress',
            'date_min_max',
            'last_change'
        ]);
        $proyek=ProyekModel::with("kapal", "kapal.owner", "kapal.perusahaan", "report")
            ->where("id_proyek", $req['id_proyek'])
            ->first()
            ->toArray();
        $data=array_merge_without($proyek_summary, [], [
            'proyek'        =>array_merge_without($proyek_summary['proyek'], ['work_area'], []),
            'work_area'     =>$summary['items'],
            'collapse_work_area'=>generate_collapse_report_work_area($summary['items']),
            'summary_work_area' =>array_merge_without($summary, ['items', 'type']),
            'summary_proyek'=>generate_summary_proyek($proyek)
        ]);
        
        //pdf
        $pdf=Pdf::loadView("pdf.project_export", $data);
        return $pdf->stream();
        
        // return response()->json([
        //     'data'  =>$data
        // ]);
    }
}
