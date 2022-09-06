<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekTenderPekerjaanRealisasiModel extends Model{

    protected $table="tbl_proyek_tender_pekerjaan_realisasi";
    protected $primaryKey="id_proyek_tender_pekerjaan_realisasi";
    protected $fillable=[
        'id_proyek_tender_pekerjaan', 
        'id_user',
        'id_user_konfirmasi',
        'qty', 
        'harga_satuan',
        'tgl_realisasi',
        'status_pekerjaan',
        'status',
        'komentar_rejected'
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function responsible(){
        return $this->belongsTo(UserModel::class, "id_user");
    }
}
