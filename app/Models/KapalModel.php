<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KapalModel extends Model{

    protected $table="tbl_kapal";
    protected $primaryKey="id_kapal";
    protected $fillable=[
        'id_user',
        'id_perusahaan',
        'nama_kapal',
        'foto',
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function owner(){
        return $this->belongsTo(UserModel::class, "id_user");
    }

    public function proyek(){
        return $this->hasMany(ProyekModel::class, "id_kapal")->orderByDesc("id_proyek");
    }

    public function perusahaan(){
        return $this->belongsTo(PerusahaanModel::class, "id_perusahaan");
    }
}
