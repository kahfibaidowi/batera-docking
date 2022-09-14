<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
// use App\Repository\UserLoginRepository;
use App\Models\UserLoginModel;
use App\Models\UserModel;

class UserLoginController extends Controller
{

    // protected $user_login;

    // public function __construct(UserLoginRepository $user_login)
    // {
    //     $this->user_login=$user_login;
    // }

    public function gets(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"required|numeric|min:1",
            'q'         =>[
                'regex:/^[\pL\s\-]+$/u',
                Rule::requiredIf(!isset($req['q']))
            ],
            'token_status'=>[
                'required',
                Rule::in(["all", "expired", "not_expired"])
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $users_login=UserLoginModel::query();
        //with user
        $users_login=$users_login->whereHas("user", function($q) use($req){
            $q->where("nama_lengkap", "ilike", "%".$req['q']."%");
        });
        $users_login=$users_login->with("user:id_user,nama_lengkap,jabatan,avatar_url,role");
        //token status
        switch($req['token_status']){
            case "expired":
                $users_login=$users_login->where("expired", "<", date("Y-m-d H:i:s"));
            break;
            case "not_expired":
                $users_login=$users_login->where("expired", ">=", date("Y-m-d H:i:s"));
            break;
        }

        //order & paginate
        $users_login=$users_login
            ->orderByDesc("id_user_login")
            ->paginate($req['per_page'])->toArray();

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$users_login['current_page'],
            'last_page'     =>$users_login['last_page'],
            'data'          =>$users_login['data']
        ]);
    }
    
    //DELETE
    public function delete_by_id(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user_login']=$id;
        $validation=Validator::make($req, [
            'id_user_login' =>"required|exists:App\Models\UserLoginModel,id_user_login"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        UserLoginModel::where("id_user_login", $req['id_user_login'])
            ->delete();

        return response()->json([
            'status'=>"ok"
        ]);
    }
    
    public function delete(Request $request)
    {
        switch($request->get("type")){
            //hapus expired
            case "expired":
                return $this->delete_expired($request);
            break;

        }
    }
    private function delete_expired(Request $request)
    {
        //SUCCESS
        UserLoginModel::where("expired", "<", date("Y-m-d H:i:s"))
            ->delete();

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
