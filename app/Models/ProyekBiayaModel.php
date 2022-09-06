<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekBiayaModel extends Model{

    protected $table="tbl_proyek_biaya";
    protected $primaryKey="id_proyek_biaya";
    protected $fillable=[
        'id_proyek', 
        'off_hire_start', 
        'off_hire_end', 
        'off_hire_period', 
        'off_hire_deviasi', 
        'off_hire_rate_per_day', 
        'off_hire_bunker_per_day', 
        'list_pekerjaan'
    ];
    protected $hidden=[];
    

    /*
     *#FUNCTION
     *
     */
    public function proyek(){
        return $this->belongsTo(ProyekModel::class, "id_proyek");
    }
}
