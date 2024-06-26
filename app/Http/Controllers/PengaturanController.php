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
use App\Models\PengaturanModel;

class PengaturanController extends Controller
{
    public function get_profile_perusahaan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //CHECK PROFILE IN DATABASE
        $key_name=[
            "profile_nama_perusahaan",
            "profile_merk_perusahaan",
            "profile_alamat_perusahaan",
            "profile_telepon",
            "profile_fax",
            "profile_npwp",
            "profile_email"
        ];
        $profile_perusahaan=PengaturanModel::whereIn("tipe_pengaturan", $key_name);
        if($profile_perusahaan->count()==0){
            DB::transaction(function() use($req, $login_data, $key_name){
                foreach($key_name as $val){
                    PengaturanModel::create([
                        "tipe_pengaturan"   =>$val,
                        "value_pengaturan"  =>""
                    ]);
                }
            });
        }
        
        //SUCCESS
        $profile_perusahaan=PengaturanModel::whereIn("tipe_pengaturan", $key_name)->get();

        $data=[];
        foreach($profile_perusahaan as $val){
            $data[$val['tipe_pengaturan']]=$val['value_pengaturan'];
        }

        return response()->json([
            'data'  =>(object)$data
        ]);
    }

    public function update_profile_perusahaan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            "profile_nama_perusahaan"   =>[Rule::requiredIf(!isset($req['profile_nama_perusahaan']))],
            "profile_merk_perusahaan"   =>[Rule::requiredIf(!isset($req['profile_merk_perusahaan']))],
            "profile_alamat_perusahaan" =>[Rule::requiredIf(!isset($req['profile_alamat_perusahaan']))],
            "profile_telepon"           =>[Rule::requiredIf(!isset($req['profile_telepon']))],
            "profile_fax"               =>[Rule::requiredIf(!isset($req['profile_fax']))],
            "profile_npwp"              =>[Rule::requiredIf(!isset($req['profile_npwp']))],
            "profile_email"             =>[Rule::requiredIf(!isset($req['profile_email']))]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //CHECK PROFILE IN DATABASE
        $key_name=[
            "profile_nama_perusahaan",
            "profile_merk_perusahaan",
            "profile_alamat_perusahaan",
            "profile_telepon",
            "profile_fax",
            "profile_npwp",
            "profile_email"
        ];
        $profile_perusahaan=PengaturanModel::whereIn("tipe_pengaturan", $key_name);
        if($profile_perusahaan->count()==0){
            DB::transaction(function() use($req, $login_data, $key_name){
                foreach($key_name as $val){
                    PengaturanModel::create([
                        "tipe_pengaturan"   =>$val,
                        "value_pengaturan"  =>""
                    ]);
                }
            });
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data, $key_name){
            foreach($key_name as $val){
                PengaturanModel::where("tipe_pengaturan", $val)
                    ->update([
                        'value_pengaturan'  =>$req[$val]
                    ]);
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
