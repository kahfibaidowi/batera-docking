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

class ReportController extends Controller
{

    
    public function update_progress(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_proyek_pekerjaan'   =>[
                "required",
                "exists:App\models\ProyekTenderPekerjaanModel,id_proyek_pekerjaan",
                function($attr, $value, $fail)use($req, $login_data){
                    $pp=ProyekPekerjaanModel::where("id_proyek_pekerjaan", $value);
                    if($pp->count()==0){
                        return $fail($attr." not found");
                    }
                    $pp=$pp->first();
                    $p=ProyekModel::where("id_proyek", $pp['id_proyek'])->first();
                    if(!in_array($p['status'], ['requisition', 'in_progress'])){
                        return $fail($attr." update not allowed");
                    }
                    if($login_data['role']=="shipyard"){
                        $pt=ProyekTenderModel::where("id_proyek", $pp['id_proyek'])->first();
                        if($pt['id_user']!=$login_data['id_user']){
                            return $fail($attr." update not allowed");
                        }
                    }
                    return true;
                }
            ],
            'qty'           =>"required|numeric|min:0",
            'harga_satuan'  =>"required|numeric|min:0",
            'status'        =>"required|in:requisition,in_progress,evaluasi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }


        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $proyek_tender_pekerjaan=ProyekTenderPekerjaanModel::where("id_proyek_pekerjaan", $req['id_proyek_pekerjaan'])->first();
            ProyekTenderPekerjaanRealisasiModel::create([
                'id_proyek_tender_pekerjaan'=>$proyek_tender_pekerjaan['id_proyek_tender_pekerjaan'],
                'id_user'       =>$login_data['id_user'],
                'tgl_realisasi' =>date("Y-m-d H:i:s"),
                'qty'           =>$req['qty'],
                'harga_satuan'  =>$req['harga_satuan'],
                'status_pekerjaan'  =>$req['status'],
                'status'        =>"pending",
                'komentar_rejected' =>""
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function apply_progress(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_proyek_tender_pekerjaan_realisasi'   =>[
                "required",
                "exists:App\models\ProyekTenderPekerjaanRealisasiModel,id_proyek_tender_pekerjaan_realisasi",
                function($attr, $value, $fail)use($req, $login_data){
                    $data=DB::table("tbl_proyek_tender_pekerjaan_realisasi as a")
                        ->join("tbl_proyek_tender_pekerjaan as b", "a.id_proyek_tender_pekerjaan", "=", "b.id_proyek_tender_pekerjaan")
                        ->join("tbl_proyek_tender as c", "b.id_proyek_tender", "=", "c.id_proyek_tender")
                        ->join("tbl_proyek as d", "c.id_proyek", "=", "d.id_proyek")
                        ->select("d.id_user as pembuat", "a.status as status_progress")
                        ->where("a.id_proyek_tender_pekerjaan_realisasi", $value);

                    if($data->count()==0){
                        return $fail($attr." not found");
                    }

                    $data=$data->first();
                    if($data->status_progress!="pending"){
                        return $fail($attr." confirmed");
                    }

                    if($login_data['role']=="shipowner"){
                        if($data->pembuat!=$login_data['id_user']){
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
        DB::transaction(function() use($req, $login_data){
            $p=ProyekTenderPekerjaanRealisasiModel::where("id_proyek_tender_pekerjaan_realisasi", $req['id_proyek_tender_pekerjaan_realisasi'])->first();
            ProyekTenderPekerjaanRealisasiModel::where("id_proyek_tender_pekerjaan_realisasi", $req['id_proyek_tender_pekerjaan_realisasi'])
                ->update([
                    'status'            =>"applied",
                    'id_user_konfirmasi'=>$login_data['id_user'],
                ]);
            ProyekTenderPekerjaanModel::where("id_proyek_tender_pekerjaan", $p['id_proyek_tender_pekerjaan'])
                ->update([
                    'status'            =>$p['status_pekerjaan']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function reject_progress(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_proyek_tender_pekerjaan_realisasi'   =>[
                "required",
                "exists:App\models\ProyekTenderPekerjaanRealisasiModel,id_proyek_tender_pekerjaan_realisasi",
                function($attr, $value, $fail)use($req, $login_data){
                    $data=DB::table("tbl_proyek_tender_pekerjaan_realisasi as a")
                        ->join("tbl_proyek_tender_pekerjaan as b", "a.id_proyek_tender_pekerjaan", "=", "b.id_proyek_tender_pekerjaan")
                        ->join("tbl_proyek_tender as c", "b.id_proyek_tender", "=", "c.id_proyek_tender")
                        ->join("tbl_proyek as d", "c.id_proyek", "=", "d.id_proyek")
                        ->select("d.id_user as pembuat", "a.status as status_progress")
                        ->where("a.id_proyek_tender_pekerjaan_realisasi", $value);

                    if($data->count()==0){
                        return $fail($attr." not found");
                    }

                    $data=$data->first();
                    if($data->status_progress!="pending"){
                        return $fail($attr." confirmed");
                    }

                    if($login_data['role']=="shipowner"){
                        if($data->pembuat!=$login_data['id_user']){
                            return $fail($attr." not found");
                        }
                    }

                    return true;
                }
            ],
            'komentar'  =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            $realisasi=DB::table("tbl_proyek_tender_pekerjaan_realisasi as a")
                ->join("tbl_proyek_tender_pekerjaan as b", "a.id_proyek_tender_pekerjaan", "=", "b.id_proyek_tender_pekerjaan")
                ->join("tbl_proyek_tender as c", "b.id_proyek_tender", "=", "c.id_proyek_tender")
                ->select("a.*", "b.pekerjaan", "c.id_user as id_user_shipyard")
                ->where("a.id_proyek_tender_pekerjaan_realisasi", $req['id_proyek_tender_pekerjaan_realisasi'])
                ->first();
            $shipyard=UserModel::where("id_user", $realisasi->id_user_shipyard)->first();
            $user_rejected=UserModel::where("id_user", $login_data['id_user'])->first();

            //update
            ProyekTenderPekerjaanRealisasiModel::where("id_proyek_tender_pekerjaan_realisasi", $req['id_proyek_tender_pekerjaan_realisasi'])
                ->update([
                    'status'            =>"rejected",
                    'id_user_konfirmasi'=>$login_data['id_user'],
                    'komentar_rejected' =>$req['komentar']
                ]);

            //send email to shipyard
            $details=[
                'type'  =>"emails.reject_progress",
                'subject'=>"Activity Progress Ditolak",
                'to'    =>$shipyard['email'],
                'name'  =>$shipyard['nama_lengkap'],
                'rejected_from_name'=>$user_rejected['nama_lengkap'],
                'pekerjaan' =>$realisasi->pekerjaan,
                'qty'   =>$realisasi->qty,
                'harsat'=>$realisasi->harga_satuan,
                'tgl_realisasi' =>$realisasi->tgl_realisasi,
                'komentar'  =>$req['komentar']
            ];
            $this->dispatch(new SendEmailJob($details));
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_proyek(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"required|numeric|min:1",
            'q'         =>[
                Rule::requiredIf(!isset($req['q'])),
            ],
            'status'    =>"required|in:all,requisition,in_progress,evaluasi"
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
        $proyek=ProyekModel::
            withWhereHas("proyek_tender", function($query)use($login_data){
                //shipyard
                if($login_data['role']=="shipyard"){
                    $query->where("id_user", $login_data['id_user']);
                }
            })
            ->with("proyek_tender.pekerjaan", "proyek_tender.pekerjaan.realisasi");
        $proyek=$proyek->where("tender_status", "complete");
        $proyek=$proyek->whereIn("status", ['requisition', 'in_progress', 'evaluasi']);
        //status
        if($req['status']!="all"){
            $proyek=$proyek->where("status", $req['status']);
        }
        //q
        $proyek=$proyek->where("vessel", "ilike", "%".$req['q']."%");
        //shipowner
        if($login_data['role']=="shipowner"){
            $proyek=$proyek->where("id_user", $login_data['id_user']);
        }

        //select, order & paginate
        $proyek=$proyek
            ->orderByDesc("id_proyek")
            ->paginate($req['per_page'])
            ->toArray();

        $proyek=convert_object_to_array($proyek);
        //end query
        //---------------------------------------------------------------------------------

        $data=[];
        foreach($proyek['data'] as $val){
            $proyek_tender_pekerjaan=$val['proyek_tender']['pekerjaan'];

            $data[]=array_merge_without($val, ['proyek_tender'], [
                'progress'  =>get_progress_realisasi($proyek_tender_pekerjaan)
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$proyek['current_page'],
            'last_page'     =>$proyek['last_page'],
            'data'          =>$data
        ]);
    }

    public function get_proyek(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_proyek'   =>[
                "required",
                "exists:App\models\ProyekModel,id_proyek",
                function($attr, $value, $fail)use($req, $login_data){
                    $data=DB::table("tbl_proyek_tender as a")
                        ->join("tbl_proyek as b", "a.id_proyek", "=", "b.id_proyek")
                        ->select("a.id_user as id_user_shipyard", "b.id_user as id_user_shipowner")
                        ->where("a.id_proyek", $value)
                        ->where("b.tender_status", "complete")
                        ->whereIn("b.status", ['requisition', 'in_progress', 'evaluasi']);

                    //not found
                    if($data->count()==0){
                        return $fail($attr." not found");
                    }
                    
                    $data=$data->first();
                    //shipowner
                    if($login_data['role']=="shipowner"){
                        if($data->id_user_shipowner!=$login_data['id_user']){
                            return $fail($attr." not found");
                        }
                    }
                    //shipyard
                    if($login_data['role']=="shipyard"){
                        if($data->id_user_shipyard!=$login_data['id_user']){
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
        //--------------------------------------------------------------------------------
        //query
        $proyek=ProyekModel::
            with([
                "proyek_tender", 
                "proyek_tender.pekerjaan", 
                "proyek_tender.pekerjaan.realisasi", 
                "proyek_tender.pekerjaan.realisasi.responsible",
                "proyek_tender.pekerjaan.realisasi.confirmed_by"
            ])
            ->where("id_proyek", $req['id_proyek'])
            ->first()->toArray();
        
        $proyek=convert_object_to_array($proyek);
        //end query
        //---------------------------------------------------------------------------------
    
        $proyek_tender_pekerjaan=$proyek['proyek_tender']['pekerjaan'];
        $to_kategori=get_progress_realisasi_per_kategori($proyek_tender_pekerjaan);

        return response()->json([
            'data'  =>array_merge_without($proyek, ['proyek_tender'], [
                'pekerjaan' =>$to_kategori,
                'progress'  =>get_progress_realisasi($proyek_tender_pekerjaan)
            ])
        ]);
    }

    public function update_status(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_proyek'   =>[
                "required",
                "exists:App\models\ProyekModel,id_proyek",
                function($attr, $value, $fail)use($req, $login_data){
                    $data=DB::table("tbl_proyek_tender as a")
                        ->join("tbl_proyek as b", "a.id_proyek", "=", "b.id_proyek")
                        ->select("a.id_user as id_user_shipyard", "b.id_user as id_user_shipowner")
                        ->where("a.id_proyek", $value)
                        ->where("b.tender_status", "complete")
                        ->whereIn("b.status", ['requisition', 'in_progress', 'evaluasi']);

                    //not found
                    if($data->count()==0){
                        return $fail($attr." not found");
                    }
                    
                    $data=$data->first();
                    //shipyard
                    if($login_data['role']=="shipyard"){
                        if($data->id_user_shipyard!=$login_data['id_user']){
                            return $fail($attr." not found");
                        }
                    }

                    return true;
                }
            ],
            "status"    =>"required|in:requisition,in_progress,evaluasi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            ProyekModel::where("id_proyek", $req['id_proyek'])
                ->update([
                    "status"=>$req['status']
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
