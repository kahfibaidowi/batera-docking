<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekModel extends Model{

    protected $table="tbl_proyek";
    protected $primaryKey="id_proyek";
    protected $fillable=[
        'id_kapal',
        'id_user',
        'phase',
        'tahun',
        "mata_uang",
        "off_hire_start",
        "off_hire_end",
        "off_hire_period",
        "off_hire_deviasi",
        "off_hire_rate_per_day",
        "off_hire_bunker_per_day",
        "repair_start",
        "repair_end",
        "repair_period",
        "repair_in_dock_start",
        "repair_in_dock_end",
        "repair_in_dock_period",
        "repair_additional_day",
        "work_area"
    ];
    protected $casts=[
        'work_area'     =>'array'
    ];


    /*
     *#FUNCTION
     *
     */
    public function kapal(){
        return $this->belongsTo(KapalModel::class, "id_kapal");
    }

    public function responsible(){
        return $this->belongsTo(UserModel::class, "id_user");
    }

    public function report(){
        return $this->hasOne(ProyekReportModel::class, "id_proyek");
    }
}
