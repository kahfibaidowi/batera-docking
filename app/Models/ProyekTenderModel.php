<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekTenderModel extends Model{

    protected $table="tbl_proyek_tender";
    protected $primaryKey="id_proyek_tender";
    protected $fillable=[
        'id_proyek',
        'id_user',
        'rencana_off_hire_start',
        'rencana_off_hire_end', 
        'rencana_off_hire_period', 
        'rencana_off_hire_deviasi', 
        'rencana_off_hire_rate_per_day', 
        'rencana_off_hire_bunker_per_day', 
        'rencana_repair_start', 
        'rencana_repair_end', 
        'rencana_repair_period', 
        'rencana_repair_in_dock_start', 
        'rencana_repair_in_dock_end', 
        'rencana_repair_in_dock_period', 
        'rencana_repair_additional_day', 
        'rencana_diskon_umum_persen', 
        'rencana_diskon_tambahan'
        // 'realisasi_off_hire_start',
        // 'realisasi_off_hire_end'
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function pekerjaan(){
        return $this->hasMany(ProyekTenderPekerjaanModel::class, "id_proyek_tender")->orderBy("id_proyek_pekerjaan");
    }

    public function shipyard(){
        return $this->belongsTo(UserModel::class, "id_user");
    }

    public function proyek(){
        return $this->belongsTo(ProyekModel::class, "id_proyek");
    }
}
