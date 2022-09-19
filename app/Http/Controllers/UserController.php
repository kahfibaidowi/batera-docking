<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\UserModel;
use App\Models\UserLoginModel;

class UserController extends Controller
{

    //INSERT
    public function add(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'username'      =>"required|unique:App\Models\UserModel,username",
            'nama_lengkap'  =>"required|regex:/^[\pL\s\-]+$/u",
            'nama_kapal'    =>[
                Rule::requiredIf(!isset($req['nama_kapal']))
            ],
            'title'         =>[
                Rule::requiredIf(!isset($req['title']))
            ],
            'departemen'    =>[
                Rule::requiredIf(!isset($req['departemen']))
            ],
            'user_id'       =>[
                Rule::requiredIf(!isset($req['user_id']))
            ],
            'jabatan'       =>[
                Rule::requiredIf(!isset($req['jabatan']))
            ],
            'no_hp'         =>[
                Rule::requiredIf(!isset($req['no_hp'])),
                "numeric"
            ],
            'email'         =>"required|email|unique:App\Models\UserModel,email",
            'password'      =>"required|min:5",
            'role'          =>[
                'required',
                Rule::in(["admin", "shipmanager", "shipyard", "shipowner", "provider"])
            ],
            "avatar_url"    =>[
                Rule::requiredIf(!isset($req['avatar_url']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        UserModel::create([
            'username'      =>$req['username'],
            'nama_lengkap'  =>$req['nama_lengkap'],
            'nama_kapal'    =>$req['nama_kapal'],
            'title'         =>$req['title'],
            'departemen'    =>$req['departemen'],
            'user_id'       =>$req['user_id'],
            'jabatan'       =>$req['jabatan'],
            'no_hp'         =>$req['no_hp'],
            'email'         =>$req['email'],
            'password'      =>Hash::make($req['password']),
            'avatar_url'    =>$req['avatar_url'],
            'role'          =>$req['role'],
            'status'        =>"active"
        ]);

        return response()->json([
            'status'=>"ok"
        ]);
    }

    //GET
    public function gets(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        $validation=Validator::make($req, [
            'per_page'  =>"required|numeric|min:1",
            'q'         =>[
                'regex:/^[\pL\s\-]+$/u',
                Rule::requiredIf(!isset($req['q']))
            ],
            'role'      =>[
                'required',
                Rule::in(['all', 'admin', 'shipyard', 'shipmanager', 'shipowner', 'provider']),
            ],
            'status'    =>[
                'required',
                Rule::in(['all', 'active', 'suspend'])
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $users=UserModel::query();
        //q
        $users=$users->where(function($query) use($req){
            $query->where("nama_lengkap", "ilike", "%".$req['q']."%")
                ->orWhere("jabatan", "ilike", "%".$req['q']."%")
                ->orWhere("no_hp", "ilike", "%".$req['q']."%")
                ->orWhere("email", "ilike", "%".$req['q']."%");
        });
        //role
        if($req['role']!="all"){
            $users=$users->where("role", $req['role']);
        }
        //status
        if($req['status']!="all"){
            $users=$users->where("status", $req['status']);
        }

        //order & paginate
        $users=$users->orderByDesc("id_user")
            ->paginate($req['per_page'])->toArray();

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$users['current_page'],
            'last_page'     =>$users['last_page'],
            'data'          =>$users['data']
        ]);
    }

    public function get_by_id(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user']=$id;
        $validation=Validator::make($req, [
            'id_user'   =>"required|exists:App\Models\UserModel,id_user"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $user=UserModel::where("id_user", $req['id_user']);
        
        return response()->json([
            'data'  =>$user->first()
        ]);
    }

    //DELETE
    public function delete_by_id(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user']=$id;
        $validation=Validator::make($req, [
            'id_user'   =>"required|exists:App\Models\UserModel,id_user"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //ME
        if($req['id_user']==$login_data['id_user']){
            return response()->json([
                'error' =>"SELF_DELETE_NOT_ALLOWED"
            ], 500);
        }

        //SUCCESS
        UserModel::where("id_user", $req['id_user'])
            ->delete();

        return response()->json([
            'status'=>"ok"
        ]);
    }

    //UPDATE
    public function update_by_id(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //VALIDATION
        $req['id_user']=$id;
        $validation=Validator::make($req, [
            'id_user'       =>"required|exists:App\Models\UserModel,id_user",
            'username'      =>[
                'required',
                Rule::unique("App\Models\UserModel")->where(function($query)use($req){
                    return $query->where("id_user", "!=", $req['id_user']);
                })
            ],
            'nama_lengkap'  =>"required|regex:/^[\pL\s\-]+$/u",
            'nama_kapal'    =>[
                Rule::requiredIf(!isset($req['nama_kapal']))
            ],
            'title'         =>[
                Rule::requiredIf(!isset($req['title']))
            ],
            'departemen'    =>[
                Rule::requiredIf(!isset($req['departemen']))
            ],
            'user_id'       =>[
                Rule::requiredIf(!isset($req['user_id']))
            ],
            'jabatan'       =>[
                Rule::requiredIf(!isset($req['jabatan']))
            ],
            'no_hp'         =>[
                Rule::requiredIf(!isset($req['no_hp'])),
                "numeric"
            ],
            'email'         =>[
                'required',
                Rule::unique("App\Models\UserModel")->where(function($query)use($req){
                    return $query->where("id_user", "!=", $req['id_user']);
                })
            ],
            'password'      =>[
                Rule::requiredIf(!isset($req['password'])),
                'min:5'
            ],
            'status'        =>[
                'required',
                Rule::in(["active", "suspend"])
            ],
            "avatar_url"    =>[
                Rule::requiredIf(!isset($req['avatar_url']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()
            ], 500);
        }

        //SUCCESS
        $data_update=[
            'username'  =>$req['username'],
            'nama_lengkap'  =>$req['nama_lengkap'],
            'nama_kapal'    =>$req['nama_kapal'],
            'title'         =>$req['title'],
            'departemen'    =>$req['departemen'],
            'user_id'       =>$req['user_id'],
            'jabatan'       =>$req['jabatan'],
            'no_hp'         =>$req['no_hp'],
            'email'         =>$req['email'],
            'status'        =>$req['status'],
            'avatar_url'    =>$req['avatar_url']
        ];
        if($req['password']!=""){
            $data_update=array_merge($data_update, [
                'password'  =>Hash::make($req['password'])
            ]);
        }

        UserModel::where("id_user", $req['id_user'])
            ->update($data_update);
        return response()->json([
            'status'=>"ok"
        ]);
    }
}
