<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekReportModel extends Model{

    protected $table="tbl_proyek_report";
    protected $primaryKey="id_proyek_report";
    protected $fillable=[
        "id_proyek",
        "id_tender",
        "summary_detail",
        "approved_by",
        "approved",
        "proyek_start",
        "proyek_end",
        "proyek_period",
        "master_plan",
        "status",
        "state",
        "tipe_proyek",
        "prioritas",
        "partner",
        "deskripsi",
        "work_area",
        "variant_work"
    ];
    protected $casts = [
        'work_area' =>'array',
        'variant_work'  =>'array'
    ];
    protected $hidden=[];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
    public function proyek(){
        return $this->belongsTo(ProyekModel::class, "id_proyek");
    }

    public function tender(){
        return $this->belongsTo(TenderModel::class, "id_tender");
    }
}
