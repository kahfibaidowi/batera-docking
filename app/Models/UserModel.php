<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserLoginModel;

class UserModel extends Model{

    protected $table="tbl_users";
    protected $primaryKey="id_user";
    protected $fillable=['username', 'nama_lengkap', 'jabatan', 'no_hp', 'email', 'password', 'avatar_url', 'role', 'status'];
    protected $hidden=['password'];


    /*
     *#FUNCTION
     *
     */
    public function user_login(){
        return $this->hasMany(UserLoginModel::class, "id_user");
    }
}
