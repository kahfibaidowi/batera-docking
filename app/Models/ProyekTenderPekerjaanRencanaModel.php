<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekTenderPekerjaanRencanaModel extends Model{

    protected $table="tbl_proyek_tender_pekerjaan_rencana";
    protected $primaryKey="id_proyek_tender_pekerjaan_rencana";
    protected $fillable=[
        'id_proyek_tender_pekerjaan', 
        'qty', 
        'tgl_rencana', 
        'keterangan'
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
}
