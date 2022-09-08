<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\UserModel;
use App\Models\UserLoginModel;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $req=$request->all();

        $validation=Validator::make($req, [
            'user_email'=>"required",
            'password'  =>"required",
            'remember'  =>"required|boolean"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        $user=UserModel::select([
                "id_user", 
                "username", 
                "nama_lengkap", 
                "jabatan", 
                "no_hp", 
                "email", 
                "password", 
                "avatar_url", 
                "role"
            ])
            ->where("email", $req['user_email'])
            ->orWhere("username", $req['user_email']);
        if($user->count()==0){
            return response()->json([
                'error' =>"USER_OR_PASSWORD_WRONG"
            ], 500);
        }

        $user_data=$user->first();
        if(!Hash::check($req['password'], $user_data['password'])){
            return response()->json([
                'error' =>"USER_OR_PASSWORD_WRONG"
            ], 500);
        }

        //SUCCESS
        //no_remember=12 jam, remember=7 hari
        $expired=!$req['remember']?12*3600:7*24*3600;
        $time=time();
        $token=[
            'iat'   =>$time,
            'nbf'   =>$time,
            'exp'   =>$time+$expired,
            'uid'   =>$user_data['id_user']
        ];
        $generated_token=JWT::encode($token, env("JWT_SECRET"), env("JWT_ALGORITM"));

        //insert
        UserLoginModel::create([
            'id_user'       =>$user_data['id_user'],
            'login_token'   =>Crypt::encryptString($generated_token),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>$_SERVER['HTTP_USER_AGENT']
        ]);
        
        return response()->json([
            'data'  =>[
                'id_user'       =>$user_data['id_user'],
                'user_name'     =>$user_data['username'],
                'nama_lengkap'  =>$user_data['nama_lengkap'],
                'jabatan'       =>$user_data['jabatan'],
                'no_hp'         =>$user_data['no_hp'],
                'email'         =>$user_data['email'],
                'avatar_url'    =>$user_data['avatar_url'],
                'role'          =>$user_data['role'],
                'access_token'  =>$generated_token
            ]
        ]);
    }

    public function verify_login(Request $request)
    {
        $login_data=$request['fm__login_data'];

        return response()->json([
            'status'    =>"ok"
        ]);
    }

    public function get_profile(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        $user=UserModel::select([
                "id_user", 
                "username", 
                "nama_lengkap", 
                "jabatan", 
                "no_hp", 
                "email", 
                "avatar_url", 
                "role"
            ])
            ->where("id_user", $login_data['id_user'])
            ->first();

        return response()->json([
            'data'  =>$user
        ]);
    }

    public function update_profile(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'email'     =>"required|email",
            'username'  =>"required",
            'nama_lengkap'  =>"required|regex:/^[\pL\s\-]+$/u",
            'no_hp'     =>[
                Rule::requiredIf(!isset($req['no_hp'])),
                'numeric'
            ],
            'avatar_url'=>[
                Rule::requiredIf(!isset($req['avatar_url']))
            ],
            'password'  =>[
                Rule::requiredIf(!isset($req['password'])),
                'min:5'
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //EMAIL & USERNAME
        $user=UserModel::where(function($query)use($req){
                $query->where("email", $req['email'])
                    ->orWhere("username", $req['username']);
            })
            ->where("id_user", "!=", $login_data['id_user']);
        if($user->count()>0){
            return response()->json([
                'error' =>"EMAIL_USERNAME_USED"
            ], 500);
        }

        //SUCCESS
        $data_update=[
            'email'         =>$req['email'],
            'username'      =>$req['username'],
            'nama_lengkap'  =>$req['nama_lengkap'],
            'no_hp'         =>$req['no_hp'],
            'avatar_url'    =>$req['avatar_url']
        ];
        if($req['password']!=""){
            $data_update=array_merge($data_update, [
                'password'  =>Hash::make($req['password'])
            ]);
        }
        
        UserModel::where("id_user", $login_data['id_user'])
            ->update($data_update);

        return response()->json([
            'status'    =>"ok"
        ]);
    }

    public function logout(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //SUCCESS
        $user_login=UserLoginModel::where("id_user", $login_data['id_user'])->get();
        $id_user_login=0;
        foreach($user_login as $val){
            if(Crypt::decryptString($val['login_token'])==$request->bearerToken()){
                $id_user_login=$val['id_user_login'];
            }
        }

        UserLoginModel::where("id_user_login", $id_user_login)
            ->delete();

        return response()->json([
            'status'=>"ok"
        ], 401);
    }

    public function gets_token(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page' =>"required|numeric|min:1"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $user_tokens=UserLoginModel::where("id_user", $login_data['id_user'])
            ->orderByDesc("id_user_login")
            ->paginate($req['per_page'])->toArray();

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$user_tokens['current_page'],
            'last_page'     =>$user_tokens['last_page'],
            'data'          =>$user_tokens['data']
        ]);
    }

    //DELETE
    public function delete_token_by_id(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user_login']=$id;
        $validation=Validator::make($req, [
            'id_user_login' =>[
                "required",
                Rule::exists("App\Models\UserLoginModel")->where(function($query)use($login_data){
                    return $query->where("id_user", $login_data['id_user']);
                })
            ]
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

    public function delete_token(Request $request)
    {
        switch($request->get("type")){
            //hapus token expired
            case "expired":
                return $this->delete_token_expired($request);
            break;
            
            
        }
    }

    private function delete_token_expired(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //SUCCESS
        UserLoginModel::where("expired", "<", date("Y-m-d H:i:s"))
            ->where("id_user", $login_data['id_user'])
            ->delete();

        return response()->json([
            'status'=>"ok"
        ]);
    }
}