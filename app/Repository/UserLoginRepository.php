<?php
namespace App\Repository;

use App\Models\UserLoginModel;

class UserLoginRepository
{
    public function gets_user_login($req)
    {
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

        //return
        return $users_login;
    }
}