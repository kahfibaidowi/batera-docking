<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekModel extends Model{

    protected $table="tbl_proyek";
    protected $primaryKey="id_proyek";
    protected $fillable=[
        'id_kapal',
        'tahun',
        "nama_proyek",
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
        "owner_supplies",
        "owner_services",
        "owner_class",
        "owner_other",
        "owner_cancel_job",
        "yard_cost",
        "yard_cancel_job",
        "work_area",
        "status"
    ];

    protected $casts = [
        'work_area' =>'array'
    ];

    protected $hidden=['status'];

    /*
     *#CUSTOM ATTR
     *
     */
    protected $appends=['published'];

    public function getPublishedAttribute(){
        return $this->status=="published"?true:false;
    }


    /*
     *#FUNCTION
     *
     */
    public function kapal(){
        return $this->belongsTo(KapalModel::class, "id_kapal");
    }

    public function report(){
        return $this->hasOne(ProyekReportModel::class, "id_proyek");
    }
}
