<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekReportPicModel extends Model{

    protected $table="tbl_proyek_report_pic";
    protected $primaryKey="id_proyek_report_pic";
    protected $fillable=[
        "id_proyek_report",
        "id_user"
    ];

    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function summary(){
        return $this->belongsTo(ProyekReportModel::class, "id_proyek_report");
    }

    public function user(){
        return $this->belongsTo(UserModel::class, "id_user");
    }
}
