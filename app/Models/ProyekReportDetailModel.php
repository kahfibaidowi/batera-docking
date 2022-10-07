<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekReportDetailModel extends Model{

    protected $table="tbl_proyek_report_detail";
    protected $primaryKey="id_proyek_report_detail";
    protected $fillable=[
        "id_proyek_report",
        "id_user",
        "type",
        "tgl",
        "perihal",
        "nama_pengirim",
        "keterangan",
        "dokumen"
    ];

    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function report(){
        return $this->belongsTo(ProyekReportModel::class, "id_proyek_report");
    }

    public function created_by(){
        return $this->belongsTo(UserModel::class, "id_user");
    }
}
