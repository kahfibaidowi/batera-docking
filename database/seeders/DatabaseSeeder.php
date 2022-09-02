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
            'jabatan'       =>"ship owner",
            'no_hp'         =>"",
            'email'         =>"febimd@gmail.com",
            'password'      =>Hash::make("febii"),
            'avatar_url'    =>"",
            'role'          =>"shipowner",
        ]);

        //USER LOGIN
        $expired=(365*24*3600)-3600;
        $time=1661929262;
        UserLoginModel::create([
            'id_user'       =>1,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjE5Mjg3NjQsIm5iZiI6MTY2MTkyODc2NCwiZXhwIjoxNjkzNDY0NzY0LCJ1aWQiOjF9.FO313haEAhDTsNsyVNCJqvdqpBYr3V2K-AAoZ2XAo4I"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
        UserLoginModel::create([
            'id_user'       =>2,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjE5Mjg5MjgsIm5iZiI6MTY2MTkyODkyOCwiZXhwIjoxNjkzNDY0OTI4LCJ1aWQiOjJ9.gAjLU9xsAM7LdN_GZCFSRy73CfHlWbmBv-HFCQEbWQk"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
        UserLoginModel::create([
            'id_user'       =>3,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjE5Mjg5NzUsIm5iZiI6MTY2MTkyODk3NSwiZXhwIjoxNjkzNDY0OTc1LCJ1aWQiOjN9.BYWb0jVcNlf8TcxU0F7MKcAxal0c_r6Ir135bqIh0hY"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
        UserLoginModel::create([
            'id_user'       =>4,
            'login_token'   =>Crypt::encryptString("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NjE5MjkwMTIsIm5iZiI6MTY2MTkyOTAxMiwiZXhwIjoxNjkzNDY1MDEyLCJ1aWQiOjR9.PNdIJogpgH4nJ6RQWiCXGlr_NBuPviRlwO6CPAigcAg"),
            'expired'       =>date('Y-m-d H:i:s', $time+$expired),
            'device_info'   =>"test"
        ]);
    }
}
