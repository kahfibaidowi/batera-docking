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

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //SUCCESS
        //--------------------------------------------------------------------------------
        //query
        $proyek=ProyekModel::
            withWhereHas("proyek_tender", function($query)use($login_data){
                //shipyard
                if($login_data['role']=="shipyard"){
                    $query->where("id_user", $login_data['id_user']);
                }
            })
            ->with("proyek_tender.pekerjaan", "proyek_tender.pekerjaan.realisasi")
            ->orderByDesc("id_proyek")
            ->get()->toArray();
        
        $proyek=convert_object_to_array($proyek);
        //end query
        //---------------------------------------------------------------------------------
        
        $data=[];
        foreach($proyek as $val){
            $data[]=array_merge_without($val, ['proyek_tender'], [
                'progress'  =>get_progress_realisasi($val['proyek_tender']['pekerjaan'])
            ]);
        }

        return response()->json([
            'data'  =>$data
        ]);
    }
}
