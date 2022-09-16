<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TenderModel extends Model{

    protected $table="tbl_tender";
    protected $primaryKey="id_tender";
    protected $fillable=[
        'id_proyek',
        'id_user',
        'yard_total_quote',
        'general_diskon_persen',
        'additional_diskon',
        'sum_internal_adjusment'
    ];

    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function shipyard(){
        return $this->belongsTo(UserModel::class, "id_user");
    }

    public function proyek(){
        return $this->belongsTo(ProyekModel::class, "id_proyek");
    }
}
