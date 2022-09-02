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
}
