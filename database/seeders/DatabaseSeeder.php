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
    }
}
