<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Jobs\SendEmailJob;
use App\Models\UserModel;
use App\Models\ProyekModel;
use App\Models\ProyekPekerjaanModel;
use App\Models\ProyekBiayaModel;
use App\Models\TenderModel;
use App\Models\TenderPekerjaanModel;
use App\Models\TenderPekerjaanRencanaModel;
use App\Models\ProyekTenderModel;
use App\Models\ProyekTenderPekerjaanModel;
use App\Models\ProyekTenderPekerjaanRencanaModel;
use App\Models\ProyekTenderPekerjaanRealisasiModel;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"required|numeric|min:1",
            'q'         =>[
                Rule::requiredIf(!isset($req['q'])),
            ],
            'status'    =>"in:all,requisition,in_progress,evaluasi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        //--------------------------------------------------------------------------------
        //query
        $proyek=ProyekModel::query();
        $proyek=$proyek->withWhereHas("proyek_tender", function($query)use($login_data, $req){
            //shipyard
            if($login_data['role']=="shipyard"){
                $query->where("id_user", $login_data['id_user']);
            }
        });
        $proyek=$proyek->with([
            'proyek_tender.pekerjaan',
            'proyek_tender.pekerjaan.realisasi',
            'proyek_tender.pekerjaan.rencana'
        ]);
        //q
        $proyek=$proyek->where("vessel", "ilike", "%".$req['q']."%");
        //status
        if($req['status']!="all"){
            $proyek=$proyek->where("status", $req['status']);
        }
        if($login_data['role']=="shipowner"){
            $proyek=$proyek->where("id_user", $login_data['id_user']);
        }

        //select, order & paginate
        $proyek=$proyek->orderByDesc("id_proyek")
            ->paginate($req['per_page'])
            ->toArray();
        
        $proyek=convert_object_to_array($proyek);
        //end query
        //---------------------------------------------------------------------------------

        $data=[];
        foreach($proyek['data'] as $val){
            $owner=UserModel::where("id_user", $val['id_user'])
                ->select(['nama_lengkap', 'username', 'avatar_url', 'id_user'])
                ->first();
            $yard=UserModel::where("id_user", $val['proyek_tender']['id_user'])
                ->select(['nama_lengkap', 'username', 'avatar_url', 'id_user'])
                ->first();
            $graph=get_graph_tracking_proyek($val['proyek_tender']);

            $data[]=array_merge($val, [
                'shipowner' =>$owner,
                'shipyard'  =>$yard,
                'graph'     =>$graph
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$proyek['current_page'],
            'last_page'     =>$proyek['last_page'],
            'data'          =>$data
        ]);
    }
}
