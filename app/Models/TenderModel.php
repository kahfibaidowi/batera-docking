<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TenderModel extends Model{

    protected $table="tbl_tender";
    protected $primaryKey="id_tender";
    protected $fillable=[
        'id_user',
        'id_attachment',
        'no_kontrak',
        'komentar',
        'nama_galangan',
        'lokasi_galangan',
        'yard_total_quote',
        'general_diskon_persen',
        'additional_diskon',
        'sum_internal_adjusment',
        'work_area'
    ];
    protected $casts=[
        'work_area'     =>'array'
    ];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
    public function shipyard(){
        return $this->belongsTo(UserModel::class, "id_user");
    }

    public function report(){
        return $this->hasOne(ProyekReportModel::class, "id_tender");
    }
    
    public function attachment(){
        return $this->belongsTo(AttachmentModel::class, "id_attachment");
    }
}
