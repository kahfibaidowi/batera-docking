<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KapalModel extends Model{

    protected $table="tbl_kapal";
    protected $primaryKey="id_kapal";
    protected $fillable=[
        'id_user',
        'nama_kapal',
        'foto',
        'nama_perusahaan',
        'merk_perusahaan',
        'alamat_perusahaan_1',
        'alamat_perusahaan_2',
        'telepon',
        'faximile',
        'npwp',
        'email'
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function owner(){
        return $this->belongsTo(UserModel::class, "id_user");
    }
}
