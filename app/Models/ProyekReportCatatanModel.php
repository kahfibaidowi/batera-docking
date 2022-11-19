<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekReportCatatanModel extends Model{

    protected $table="tbl_proyek_report_catatan";
    protected $primaryKey="id_proyek_report_catatan";
    protected $fillable=[
        "id_proyek_report",
        "catatan"
    ];

    protected $hidden=[];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
    public function report(){
        return $this->belongsTo(ProyekReportModel::class, "id_proyek_report");
    }
}
