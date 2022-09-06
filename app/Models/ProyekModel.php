<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekModel extends Model{

    protected $table="tbl_proyek";
    protected $primaryKey="id_proyek";
    protected $fillable=['id_user', 'vessel', 'tahun', 'foto', 'currency', 'prioritas', 'negara', 'deskripsi', 'status', 'tender_status'];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function owner(){
        return $this->belongsTo(UserModel::class, "id_user");
    }

    public function tender(){
        return $this->hasMany(TenderModel::class, "id_proyek");
    }

    public function biaya(){
        return $this->hasOne(ProyekBiayaModel::class, "id_proyek");
    }

    public function proyek_tender(){
        return $this->hasOne(ProyekTenderModel::class, "id_proyek");
    }

    public function pekerjaan(){
        return $this->hasMany(ProyekPekerjaanModel::class, "id_proyek");
    }
}
