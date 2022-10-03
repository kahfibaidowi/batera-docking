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

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$kapal['current_page'],
            'last_page'     =>$kapal['last_page'],
            'data'          =>$kapal['data']
        ]);
    }
}
