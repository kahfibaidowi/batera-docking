<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\ProyekModel;
use App\Models\ProyekPekerjaanModel;
use App\Models\ProyekTenderModel;
use App\Models\ProyekTenderPekerjaanModel;
use App\Models\ProyekBiayaModel;
use App\Models\UserModel;

class ProyekController extends Controller
{

    public function add(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            //project
            'id_user'       =>[
                Rule::requiredIf(function()use($login_data){
                    if($login_data['role']=="shipowner"){
                        return false;
                    }
                    return true;
                }),
                Rule::exists("App\Models\UserModel")->where(function($query)use($req){
                    return $query->where("id_user", $req['id_user'])
                        ->where("role", "shipowner");
                })
            ],
            'vessel'        =>"required|regex:/^[\pL\s\-]+$/u",
            'tahun'         =>"required|integer|digits:4",
            'foto'          =>[
                'required',
                'ends_with:.jpg,.png,.jpeg',
                function($attr, $value, $fail)use($req){
                    if(is_image_file($req['foto'])){
                        return true;
                    }
                    return $fail($attr." image not found");
                }
            ],
            'deskripsi'     =>"required",

            //project detail
            'detail.off_hire_start'     =>"required|date_format:Y-m-d",
            'detail.off_hire_end'       =>"required|date_format:Y-m-d|after_or_equal:detail.off_hire_start",
            'detail.off_hire_deviasi'   =>"required|numeric|min:0",
            'detail.off_hire_rate_per_day'  =>"required|numeric|min:0", 
            'detail.off_hire_bunker_per_day'=>"required|numeric|min:0",
            'detail.list_pekerjaan'     =>"required|numeric|min:0",

            //job list
            'pekerjaan'     =>"required|array|min:1",
            'pekerjaan.*.pekerjaan' =>"required",
            'pekerjaan.*'   =>"required_array_keys:kategori_1,kategori_2,kategori_3,kategori_4",
            'pekerjaan.*.kategori_1'=>"required",
            'pekerjaan.*.qty'   =>"required|numeric|min:0",
            'pekerjaan.*.satuan'=>"required"
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
            $id_user=$login_data['role']!="shipowner"?$req['id_user']:$login_data['id_user'];
            $proyek=ProyekModel::create([
                'id_user'       =>$id_user,
                'vessel'        =>$req['vessel'],
                'tahun'         =>$req['tahun'],
                'foto'          =>$req['foto'],
                'currency'      =>"IDR",
                'prioritas'     =>"high",
                'negara'        =>"indonesia",
                'deskripsi'     =>$req['deskripsi'],
                'status'        =>"published",
                'tender_status' =>"opened"
            ]);
            $proyek_id=$proyek->id_proyek;

            //proyek biaya
            $off_hire_period=count_day($req['detail']['off_hire_start'], $req['detail']['off_hire_end']);
            ProyekBiayaModel::create([
                'id_proyek'     =>$proyek_id,
                'off_hire_start'=>$req['detail']['off_hire_start'],
                'off_hire_end'  =>$req['detail']['off_hire_end'],
                'off_hire_period'       =>$off_hire_period,
                'off_hire_deviasi'      =>$req['detail']['off_hire_deviasi'],
                'off_hire_rate_per_day' =>$req['detail']['off_hire_rate_per_day'],
                'off_hire_bunker_per_day'=>$req['detail']['off_hire_bunker_per_day'],
                'list_pekerjaan'=>$req['detail']['list_pekerjaan']
            ]);

            //proyek pekerjaan
            foreach($req['pekerjaan'] as $val){
                ProyekPekerjaanModel::create(array_merge($val, [
                    'id_proyek' =>$proyek_id
                ]));
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_proyek_persiapan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"required|numeric|min:1",
            'q'         =>[
                Rule::requiredIf(!isset($req['q'])),
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
        //--------------------------------------------------------------------------------
        //query
        $proyek=DB::table("tbl_proyek");
        $proyek->where("tender_status", "complete");
        //q
        $proyek->where("vessel", "ilike", "%".$req['q']."%");
        //status
        if($req['status']!="all"){
            $proyek->where("status", $req['status']);
        }
        //shipowner
        if($login_data['role']=="shipowner"){
            $proyek->where("id_user", $login_data['id_user']);
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

            $data[]=array_merge($val, [
                'shipowner' =>$owner,
                'created_at'=>with_timezone($val['created_at']),
                'updated_at'=>with_timezone($val['updated_at'])
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$proyek['current_page'],
            'last_page'     =>$proyek['last_page'],
            'data'          =>$data
        ]);

    }

    public function gets_pekerjaan_mendekati_deadline(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"required|numeric|min:1",
            'q'         =>[
                Rule::requiredIf(!isset($req['q']))
            ],
            'status'    =>"in:all,requisition,in_progress",
            'yard'      =>[
                Rule::requiredIf(!isset($req['yard']))
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
        $pekerjaan=DB::table("tbl_proyek_tender_pekerjaan as a");
        $pekerjaan->join("tbl_proyek_tender as b", "a.id_proyek_tender", "=", "b.id_proyek_tender");
        $pekerjaan->join("tbl_proyek as c", "b.id_proyek", "=", "c.id_proyek");
        $pekerjaan->where("a.rencana_deadline", "<", add_date(date("Y-m-d H:i:s"), -14));
        //q
        $pekerjaan->where(function($query)use($req){
            $query->where("a.pekerjaan", "ilike", "%".$req['q']."%");
            $query->orWhere("a.kategori_1", "ilike", "%".$req['q']."%");
            $query->orWhere("a.kategori_2", "ilike", "%".$req['q']."%");
            $query->orWhere("a.kategori_3", "ilike", "%".$req['q']."%");
            $query->orWhere("a.kategori_4", "ilike", "%".$req['q']."%");
        });
        //status
        if($req['status']!="all"){
            $pekerjaan->where("a.status", $req['status']);
        }
        else{
            $pekerjaan->whereIn("a.status", ["requisition", "in_progress"]);
        }
        //yard
        if($req['yard']!=""){
            $pekerjaan->where("b.id_user", $req['yard']);
        }
        //shipyard
        if($login_data['role']=="shipyard"){
            $pekerjaan->where("b.id_user", $login_data['id_user']);
        }
        //shipowner
        if($login_data['role']=="shipowner"){
            $pekerjaan->where("c.id_user", $login_data['id_user']);
        }

        //select, order & paginate
        $pekerjaan=$pekerjaan->select([
                "a.*",
                "b.id_user as id_user_shipyard",
                "c.id_user as id_user_owner",
                "c.id_proyek"
            ])
            ->orderBy("a.rencana_deadline")
            ->paginate($req['per_page'])
            ->toArray();

        $pekerjaan=convert_object_to_array($pekerjaan);
        //end query
        //---------------------------------------------------------------------------------

        $data=[];
        foreach($pekerjaan['data'] as $val){
            $owner=UserModel::where("id_user", $val['id_user_owner'])
                ->select(['nama_lengkap', 'username', 'avatar_url', 'id_user'])
                ->first();
            $yard=UserModel::where("id_user", $val['id_user_shipyard'])
                ->select(['nama_lengkap', 'username', 'avatar_url', 'id_user'])
                ->first();
            $proyek=ProyekModel::where("id_proyek", $val['id_proyek'])
                ->select(["vessel", "tahun", 'id_proyek'])
                ->first();

            $data[]=array_merge($val, [
                'shipowner' =>$owner,
                'shipyard'  =>$yard,
                'proyek'    =>$proyek,
                'created_at'=>with_timezone($val['created_at']),
                'updated_at'=>with_timezone($val['updated_at'])
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$pekerjaan['current_page'],
            'last_page'     =>$pekerjaan['last_page'],
            'data'          =>$data
        ]);
    }

    public function gets_shipyard(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //SUCCESS
        $yard=UserModel::select([
                "nama_lengkap",
                "username",
                "avatar_url",
                "id_user"
            ])
            ->where("role", "shipyard")
            ->get();

        return response()->json([
            'data'  =>$yard
        ]);
    }
    
    public function gets_proyek_berjalan(Request $request)
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
        $proyek=DB::table("tbl_proyek as a");
        $proyek->join("tbl_proyek_tender as b", "b.id_proyek", "=", "a.id_proyek");
        $proyek->where("a.tender_status", "complete");
        //q
        $proyek->where("a.vessel", "ilike", "%".$req['q']."%");
        //shipyard
        if($login_data['role']=="shipyard"){
            $proyek->where("b.id_user", "=", $login_data['id_user']);
        }
        //status
        if($req['status']!="all"){
            $proyek->where("a.status", $req['status']);
        }
        //shipowner
        if($login_data['role']=="shipowner"){
            $proyek->where("a.id_user", $login_data['id_user']);
        }

        //select, order & paginate
        $proyek=$proyek->select([
                "a.*",
                "b.id_user as id_user_shipyard"
            ])
            ->orderByDesc("a.id_proyek")
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
            $yard=UserModel::where("id_user", $val['id_user_shipyard'])
                ->select(['nama_lengkap', 'username', 'avatar_url', 'id_user'])
                ->first();

            $data[]=array_merge($val, [
                'shipowner' =>$owner,
                'shipyard'  =>$yard,
                'created_at'=>with_timezone($val['created_at']),
                'updated_at'=>with_timezone($val['updated_at'])
            ]);
        }

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$proyek['current_page'],
            'last_page'     =>$proyek['last_page'],
            'data'          =>$data
        ]);
    }

    public function get_proyek_berjalan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_proyek' =>[
                'required',
                function($attr, $value, $fail)use($login_data){
                    $v=DB::table("tbl_proyek as a")
                        ->join("tbl_proyek_tender as b", "b.id_proyek", "=", "a.id_proyek")
                        ->where("a.tender_status", "complete")
                        ->where("a.id_proyek", $value)
                        ->select("a.id_user as id_user_shipowner", "b.id_user as id_user_shipyard");

                    //not found
                    if($v->count()==0){
                        return $fail($attr." not found");
                    }

                    //shipowner
                    $data=$v->first();
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
        //--1 proyek
        $proyek=ProyekModel::where("id_proyek", $req['id_proyek'])
            ->first()->toArray();
        
        $proyek=convert_object_to_array($proyek);

        //--2 proyek tender
        $tender=ProyekTenderModel::
            with([
                "shipyard:id_user,nama_lengkap,avatar_url,username",
                "pekerjaan",
                "pekerjaan.realisasi",
                "pekerjaan.rencana"
            ])
            ->where("id_proyek", $req['id_proyek'])
            ->first()->toArray();

        $tender=convert_object_to_array($tender);
        
        //--3 proyek biaya
        $proyek_biaya=ProyekBiayaModel::where("id_proyek", $req['id_proyek'])
            ->select(['off_hire_start', 'off_hire_end', 'off_hire_period', 'off_hire_deviasi', 'off_hire_rate_per_day', 'off_hire_bunker_per_day', 'list_pekerjaan'])
            ->first()->toArray();
        
        $proyek_biaya=convert_object_to_array($proyek_biaya);
        //end query
        //---------------------------------------------------------------------------------
        
        //yard
        $owner_off_hire_rate=($proyek_biaya['off_hire_period']+$proyek_biaya['off_hire_deviasi'])*$proyek_biaya['off_hire_rate_per_day'];
        $owner_off_hire_bunker=($proyek_biaya['off_hire_period']+$proyek_biaya['off_hire_deviasi'])*$proyek_biaya['off_hire_bunker_per_day'];
        $yard_off_hire_rate=($tender['rencana_off_hire_period']+$tender['rencana_off_hire_deviasi'])*$tender['rencana_off_hire_rate_per_day'];
        $yard_off_hire_bunker=($tender['rencana_off_hire_period']+$tender['rencana_off_hire_deviasi'])*$tender['rencana_off_hire_bunker_per_day'];
        $list_pekerjaan_rencana_kategori=get_tender_rencana_selected_kategori($tender['pekerjaan']);
        $list_pekerjaan_rencana=get_total_cost_tender_rencana_selected($tender['pekerjaan']);
        $total_quote=$yard_off_hire_rate+$yard_off_hire_bunker+$list_pekerjaan_rencana;
        $diskon_umum=($tender['rencana_diskon_umum_persen']/100)*$total_quote;
        $yard_pekerjaan=get_tender_rencana_selected_per_kategori($tender['pekerjaan']);
        //yard from owner
        $yard_off_hire_rate_owner=($tender['rencana_off_hire_period']+$tender['rencana_off_hire_deviasi'])*$proyek_biaya['off_hire_rate_per_day'];
        $yard_off_hire_bunker_owner=($tender['rencana_off_hire_period']+$tender['rencana_off_hire_deviasi'])*$proyek_biaya['off_hire_bunker_per_day'];
        $total_quote_owner=$yard_off_hire_rate_owner+$yard_off_hire_bunker_owner+$list_pekerjaan_rencana;
        $diskon_umum_owner=($tender['rencana_diskon_umum_persen']/100)*$total_quote_owner;

        //realisasi
        $list_pekerjaan_realisasi_kategori=get_realisasi_kategori($tender['pekerjaan']);
        $list_pekerjaan_realisasi=get_total_cost_realisasi($tender['pekerjaan']);
        $total_quote_realisasi=$yard_off_hire_rate+$yard_off_hire_bunker+$list_pekerjaan_realisasi;
        $total_quote_realisasi_owner=$yard_off_hire_rate_owner+$yard_off_hire_bunker_owner+$list_pekerjaan_realisasi;
        $diskon_umum_realisasi=($tender['rencana_diskon_umum_persen']/100)*$total_quote_realisasi;
        $diskon_umum_realisasi_owner=($tender['rencana_diskon_umum_persen']/100)*$total_quote_realisasi_owner;
        $realisasi_pekerjaan=get_progress_realisasi_per_kategori($tender['pekerjaan']);
        // $realisasi_off_hire_start=!is_null($tender['realisasi_off_hire_start'])?$tender['realisasi_off_hire_start']:"-";
        // $realisasi_off_hire_end=!is_null($tender['realisasi_off_hire_end'])?$tender['realisasi_off_hire_end']:"-";
        
        $data=array_merge_without($tender, ["rencana_off_hire_start", "rencana_off_hire_end", "rencana_off_hire_period", "rencana_off_hire_deviasi", "rencana_off_hire_rate_per_day", "rencana_off_hire_bunker_per_day", "rencana_diskon_umum_persen", "rencana_diskon_umum_tambahan", "yard_list_pekerjaan", "pekerjaan"], [
            'owner_budget'  =>array_merge($proyek_biaya, [
                'off_hire_rate'     =>$owner_off_hire_rate,
                'off_hire_bunker'   =>$owner_off_hire_bunker,
                'off_hire'          =>$owner_off_hire_rate+$owner_off_hire_bunker,
                'total_cost'        =>$owner_off_hire_rate+$owner_off_hire_bunker+$proyek_biaya['list_pekerjaan']
            ]),
            'yard_budget'   =>[
                'off_hire_start'        =>$tender['rencana_off_hire_start'],
                'off_hire_end'          =>$tender['rencana_off_hire_end'],
                'off_hire_period'       =>$tender['rencana_off_hire_period'],
                'off_hire_deviasi'      =>$tender['rencana_off_hire_deviasi'],
                'off_hire_rate_per_day' =>$tender['rencana_off_hire_rate_per_day'],
                'off_hire_bunker_per_day'=>$tender['rencana_off_hire_bunker_per_day'],
                'off_hire_rate'         =>$yard_off_hire_rate,
                'off_hire_bunker'       =>$yard_off_hire_bunker,
                'off_hire'              =>$yard_off_hire_rate+$yard_off_hire_bunker,
                'off_hire_rate_owner'   =>$yard_off_hire_rate_owner,
                'off_hire_bunker_owner' =>$yard_off_hire_bunker_owner,
                'off_hire_owner'        =>$yard_off_hire_rate_owner+$yard_off_hire_bunker_owner,
                'list_pekerjaan'        =>$list_pekerjaan_rencana,
                'list_pekerjaan_kategori'=>$list_pekerjaan_rencana_kategori,
                'pekerjaan'             =>$yard_pekerjaan,
                "diskon_umum"           =>$diskon_umum,
                "diskon_umum_owner"     =>$diskon_umum_owner,
                "diskon_tambahan"       =>$tender['rencana_diskon_tambahan'],
                'total_quote'           =>$total_quote,
                'total_quote_owner'     =>$yard_off_hire_rate_owner+$yard_off_hire_bunker_owner+$list_pekerjaan_rencana
            ],
            'realisasi'     =>[
                'list_pekerjaan'        =>$list_pekerjaan_realisasi,
                'list_pekerjaan_kategori'=>$list_pekerjaan_realisasi_kategori,
                'pekerjaan'             =>$realisasi_pekerjaan,
                'off_hire_rate'         =>$yard_off_hire_rate,
                'off_hire_bunker'       =>$yard_off_hire_bunker,
                'off_hire'              =>$yard_off_hire_rate+$yard_off_hire_bunker,
                'off_hire_rate_owner'   =>$yard_off_hire_rate_owner,
                'off_hire_bunker_owner' =>$yard_off_hire_bunker_owner,
                'off_hire_owner'        =>$yard_off_hire_rate_owner+$yard_off_hire_bunker_owner,
                'total_quote'           =>$total_quote_realisasi,
                'total_quote_owner'     =>$total_quote_realisasi_owner,
                'diskon_umum'           =>$diskon_umum_realisasi,
                'diskon_umum_owner'     =>$diskon_umum_realisasi_owner
            ],
            'proyek'        =>$proyek
        ]);

        return response()->json([
            'data'  =>$data
        ]);
    }
}
