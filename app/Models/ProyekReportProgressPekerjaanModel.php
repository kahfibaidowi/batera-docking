<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekReportProgressPekerjaanModel extends Model{

    protected $table="tbl_proyek_report_progress_pekerjaan";
    protected $primaryKey="id_proyek_report_progress_pekerjaan";
    protected $fillable=[
        "id_proyek_report",
        "progress"
    ];

    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function report(){
        return $this->belongsTo(ProyekReportModel::class, "id_proyek_report");
    }
}
