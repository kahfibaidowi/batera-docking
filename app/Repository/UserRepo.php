<?php

namespace App\Repository;

use App\Models\UserModel;
use App\Models\UserLoginModel;


class UserRepo{

    public static function gets_user($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $users=UserModel::query();
        //--q
        $users=$users->where(function($query) use($params){
            $query->where("nama_lengkap", "ilike", "%".$params['q']."%")
                ->orWhere("jabatan", "ilike", "%".$params['q']."%")
                ->orWhere("no_hp", "ilike", "%".$params['q']."%")
                ->orWhere("email", "ilike", "%".$params['q']."%");
        });
        //--role
        if($params['role']!=""){
            $users=$users->where("role", $params['role']);
        }
        //--status
        if($params['status']!=""){
            $users=$users->where("status", $params['status']);
        }
        //--order
        $users=$users->orderByDesc("id_user");

        //return
        return $users->paginate($params['per_page'])->toArray();
    }

    public static function get_user($user_id)
    {
        //query
        $users=UserModel::where("id_user", $user_id);

        //return
        return optional($users->first())->toArray();
    }
}