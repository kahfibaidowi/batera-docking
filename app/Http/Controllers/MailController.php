<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
}
