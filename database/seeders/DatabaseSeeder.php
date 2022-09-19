<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserModel;
use App\Models\UserLoginModel;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //USER
        UserModel::create([
            'username'      =>"SA_MA",
            'nama_lengkap'  =>"Super Admin",
            'nama_kapal'    =>"-",
            'title'         =>"",
            'departemen'    =>"",
            'user_id'       =>"",
            'jabatan'       =>"admin",
            'no_hp'         =>"",
            'email'         =>"admin@gmail.com",
            'password'      =>Hash::make("admin"),
            'avatar_url'    =>"",
            'role'          =>"admin"
        ]);
        UserModel::create([
            'username'      =>"F_SM",
            'nama_lengkap'  =>"Febi Fleet Manager",
            'nama_kapal'    =>"-",
            'title'         =>"",
            'departemen'    =>"",
            'user_id'       =>"",
            'jabatan'       =>"fleet manager",
            'no_hp'         =>"",
            'email'         =>"febifm@gmail.com",
            'password'      =>Hash::make("febii"),
            'avatar_url'    =>"",
            'role'          =>"shipmanager",
        ]);
        UserModel::create([
            'username'      =>"F_CM",
            'nama_lengkap'  =>"Febi Ship Yard",
            'nama_kapal'    =>"-",
            'title'         =>"",
            'departemen'    =>"",
            'user_id'       =>"",
            'jabatan'       =>"ship yard",
            'no_hp'         =>"",
            'email'         =>"febicm@gmail.com",
            'password'      =>Hash::make("febii"),
            'avatar_url'    =>"",
            'role'          =>"shipyard",
        ]);
        UserModel::create([
            'username'      =>"F_MD",
            'nama_lengkap'  =>"Febi Ship Owner",
            'nama_kapal'    =>"-",
            'title'         =>"",
            'departemen'    =>"",
            'user_id'       =>"",
            'jabatan'       =>"ship owner",
            'no_hp'         =>"",
            'email'         =>"febimd@gmail.com",
            'password'      =>Hash::make("febii"),
            'avatar_url'    =>"",
            'role'          =>"shipowner",
        ]);
        UserModel::create([
            'username'      =>"F_PD",
            'nama_lengkap'  =>"Febi Provider",
            'nama_kapal'    =>"-",
            'title'         =>"",
            'departemen'    =>"",
            'user_id'       =>"",
            'jabatan'       =>"provider",
            'no_hp'         =>"",
            'email'         =>"febipd@gmail.com",
            'password'      =>Hash::make("febii"),
            'avatar_url'    =>"",
            'role'          =>"provider",
        ]);

        //USER LOGIN
        $expired=(100*24*3600);
        $time=1661929262;
        UserLoginModel::create([
            'id_user'       =>1,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjM1NTkzMDYsIm5iZiI6MTY2MzU1OTMwNiwiZXhwIjoxNjcyMTk5MzA2LCJ1aWQiOjF9.7OR7bf24aWUbF14j6DZAyUqKMDJWYEy1fDuiXm2zSe4"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
        UserLoginModel::create([
            'id_user'       =>2,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjM1NTkzMDYsIm5iZiI6MTY2MzU1OTMwNiwiZXhwIjoxNjcyMTk5MzA2LCJ1aWQiOjJ9.2WHpXls1Mcg46K7VcWm86DBv8TFTiE3MNhASYDLTWec"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
        UserLoginModel::create([
            'id_user'       =>3,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjM1NTkzMDYsIm5iZiI6MTY2MzU1OTMwNiwiZXhwIjoxNjcyMTk5MzA2LCJ1aWQiOjN9.YaZX_cnucsie51TxnuWY1G84PxPSvIZ1G_KCfuOmGtI"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
        UserLoginModel::create([
            'id_user'       =>4,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjM1NTkzMDYsIm5iZiI6MTY2MzU1OTMwNiwiZXhwIjoxNjcyMTk5MzA2LCJ1aWQiOjR9.cWL_00NT9f_fezkOJX0oPM0mKueTDZcFjuA4OUwmchE"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
        UserLoginModel::create([
            'id_user'       =>5,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjM1NTkzMDYsIm5iZiI6MTY2MzU1OTMwNiwiZXhwIjoxNjcyMTk5MzA2LCJ1aWQiOjV9.mxj1qNejK7bctyJVn8LbuvGvnPfQDAkaPceel7Es_gQ"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
    }
}
