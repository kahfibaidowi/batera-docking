<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KapalModel extends Model{

    protected $table="tbl_kapal";
    protected $primaryKey="id_kapal";
    protected $fillable=[
        'nama_kapal',
        'foto',
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function proyek(){
        return $this->hasMany(ProyekModel::class, "id_kapal")->orderByDesc("id_proyek");
    }
}
