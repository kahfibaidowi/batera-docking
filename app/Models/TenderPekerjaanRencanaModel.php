<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TenderPekerjaanRencanaModel extends Model{

    protected $table="tbl_tender_pekerjaan_rencana";
    protected $primaryKey="id_tender_pekerjaan_rencana";
    protected $fillable=['id_tender_pekerjaan', 'qty', 'tgl_rencana', 'keterangan'];
    protected $hidden=[];


}
