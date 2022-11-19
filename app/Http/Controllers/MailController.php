<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Jobs\SendEmailJob;
use App\Models\UserLoginModel;
use App\Models\UserModel;

class MailController extends Controller
{

    
    public function send(Request $request)
    {
        // $details=[
        //     'type'  =>"emails.test",
        //     'subject'=>"test",
        //     'to'    =>"test@gmail.com",
        //     'name'  =>"test",
        //     'rejected_from_name'=>"",
        //     'body'  =>"this is lorem ipsum"
        // ];

        // $this->dispatch(new \App\Jobs\SendEmailJob($details));

        // return response()->json([
        //     'da'    =>"wk"
        // ]);
        // $data=DB::table("tbl_users")->get()->toArray();
        // $data=array_object_to_array($data);
        
        // return response()->json([
        //     'x' =>$data[0]['nama_lengkap']
        // ]);

        // return response()->json([
        //     'a' =>add_date(date("Y-m-d"), -2)
        // ]);
    }

    public function send_work_progress(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager', 'director'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'shipyard'  =>"required",
            'shipyard.email'=>"required|email",
            'shipyard.nama_user'        =>"required",
            'shipyard.nama_perusahaan'  =>"required",
            'no_docking'    =>"required",
            'work_progress' =>"required|array|min:1",
            'work_progress.*.job_no'    =>"required",
            'work_progress.*.job_name'  =>"required",
            'work_progress.*.progress'  =>"required",
            'work_progress.*.start'     =>"required",
            'work_progress.*.end'       =>"required",
            'work_progress.*.volume'    =>"required",
            'work_progress.*.unit'      =>"required",
            'work_progress.*.unit_price'=>"required",
            'work_progress.*.total_price'=>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        Mail::send("emails.work_progress", $req, function($message)use($req){
            $message->to($req['shipyard']['email'], $req['shipyard']['nama_user'])->subject("Pemberitahuan Work Progress");
            $message->from(env("MAIL_USERNAME"), env("MAIL_FROM_NAME"));
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function send_work_variant(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager', 'director'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'shipyard'  =>"required",
            'shipyard.email'=>"required|email",
            'shipyard.nama_user'        =>"required",
            'shipyard.nama_perusahaan'  =>"required",
            'no_docking'    =>"required",
            'work_variant' =>"required|array|min:1",
            'work_variant.*.job_no'    =>"required",
            'work_variant.*.job_name'  =>"required",
            'work_variant.*.progress'  =>"required",
            'work_variant.*.start'     =>"required",
            'work_variant.*.end'       =>"required",
            'work_variant.*.volume'    =>"required",
            'work_variant.*.unit'      =>"required",
            'work_variant.*.unit_price'=>"required",
            'work_variant.*.total_price'=>"required",
            'work_variant.*.category'  =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        Mail::send("emails.work_variant", $req, function($message)use($req){
            $message->to($req['shipyard']['email'], $req['shipyard']['nama_user'])->subject("Pemberitahuan Work Variant");
            $message->from(env("MAIL_USERNAME"), env("MAIL_FROM_NAME"));
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function send_bast(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager', 'director'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'shipyard'  =>"required",
            'shipyard.email'=>"required|email",
            'shipyard.nama_user'        =>"required",
            'shipyard.nama_perusahaan'  =>"required",
            'no_docking'    =>"required",
            'bast'  =>"required|array|min:1",
            'bast.*.title'  =>"required",
            'bast.*.sender' =>"required",
            'bast.*.date'   =>"required",
            'bast.*.remarks'=>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        Mail::send("emails.bast", $req, function($message)use($req){
            $message->to($req['shipyard']['email'], $req['shipyard']['nama_user'])->subject("Pemberitahuan BAST");
            $message->from(env("MAIL_USERNAME"), env("MAIL_FROM_NAME"));
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function send_surat_teguran(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'shipmanager', 'director'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'shipyard'  =>"required",
            'shipyard.email'=>"required|email",
            'shipyard.nama_user'        =>"required",
            'shipyard.nama_perusahaan'  =>"required",
            'no_docking'    =>"required",
            'surat_teguran'  =>"required|array|min:1",
            'surat_teguran.*.title'  =>"required",
            'surat_teguran.*.sender' =>"required",
            'surat_teguran.*.date'   =>"required",
            'surat_teguran.*.remarks'=>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        Mail::send("emails.surat_teguran", $req, function($message)use($req){
            $message->to($req['shipyard']['email'], $req['shipyard']['nama_user'])->subject("Pemberitahuan Surat Teguran");
            $message->from(env("MAIL_USERNAME"), env("MAIL_FROM_NAME"));
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
