<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\AttachmentModel;
use App\Repository\AttachmentRepo;

class FileController extends Controller
{

    //FILE
    public function upload(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'dokumen'   =>"required|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $file=$login_data['id_user']."__".date("YmdHis")."__".preg_replace("/\s+/", "-", $request->file("dokumen")->getClientOriginalName());
        $file_name=$request->file("dokumen")->getClientOriginalName();
        $file_size=$request->file("dokumen")->getSize();

        //upload
        $request->file("dokumen")->move(storage_path(env("UPLOAD_PATH")), $file);

        return response()->json([
            'data'  =>[
                'file'      =>$file,
                'file_name' =>$file_name,
                'size'      =>$file_size/1000
            ]
        ]);
    }

    public function show($file)
    {
        $upload_path=storage_path(env("UPLOAD_PATH"));

        if(file_exists($upload_path."/".$file)){
            $file_info=new \finfo(FILEINFO_MIME_TYPE);
            $file_show=file_get_contents($upload_path."/".$file);

            return response($file_show, 200)
                ->header('Content-Type', $file_info->buffer($file_show));
        }

        return response()->json([
            'status'=>"NOT_FOUND"
        ], 404);
    }

    //ATTACHMENT
    public function add_attachment(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'dokumen'   =>"required|file|max:2048|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $dokumen=(object)[];
        DB::transaction(function()use($request, &$dokumen){
            //file
            $file=$request->file("dokumen");
            $a=\file_get_contents($file);
            $nama_attachment=$file->getClientOriginalName();

            //save
            $create=AttachmentModel::create([
                'nama_attachment'   =>$nama_attachment,
                'attachment'        =>base64_encode($a)
            ]);
            $dokumen=[
                'id_attachment' =>$create['id_attachment'],
                'nama_attachment'=>$create['nama_attachment'],
                'created_at'    =>$create['created_at'],
                'updated_at'    =>$create['updated_at']
            ];
        });

        return response()->json([
            'status'=>"ok",
            'data'  =>$dokumen
        ]);
    }

    public function get_attachment(Request $request, $id)
    {   
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_attachment']=$id;
        $validation=Validator::make($req, [
            'id_attachment'   =>"required|exists:App\Models\AttachmentModel,id_attachment"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $attachment=AttachmentRepo::get_attachment($req['id_attachment']);

        $file_info=new \finfo(FILEINFO_MIME_TYPE);
        $file=base64_decode($attachment['attachment']);

        return response($file, 200)->header('Content-Type', $file_info->buffer($file));
    }

    public function gets_attachment(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //condition
        $req['type']=isset($req['type'])?$req['type']:"";
        if($req['type']=="multiple"){
            return $this->gets_attachment_by_id($request);
        }
        else{
            return $this->gets_all_attachment($request);
        }
    }

    private function gets_all_attachment(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

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
        $attachment=AttachmentRepo::gets_attachment($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$attachment['current_page'],
            'last_page'     =>$attachment['last_page'],
            'data'          =>$attachment['data']
        ]);
    }

    private function gets_attachment_by_id(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'id_attachment' =>"required|array"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $attachment=AttachmentRepo::gets_attachment_by_id($req);

        return response()->json([
            'data'          =>$attachment
        ]);
    }

    public function delete_attachment(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_attachment']=$id;
        $validation=Validator::make($req, [
            'id_attachment'   =>"required|exists:App\Models\AttachmentModel,id_attachment"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function()use($req){
            AttachmentModel::where("id_attachment", $req['id_attachment'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
