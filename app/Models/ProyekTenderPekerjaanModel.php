<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekTenderPekerjaanModel extends Model{

    protected $table="tbl_proyek_tender_pekerjaan";
    protected $primaryKey="id_proyek_tender_pekerjaan";
    protected $fillable=[
        'id_proyek_tender',
        'id_proyek_pekerjaan',
        'pekerjaan', 
        'satuan', 
        'rencana_qty', 
        'rencana_harga_satuan', 
        'kategori_1', 
        'kategori_2', 
        'kategori_3', 
        'kategori_4', 
        'rencana_deadline',
        'status'
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function realisasi(){
        return $this->hasMany(ProyekTenderPekerjaanRealisasiModel::class, "id_proyek_tender_pekerjaan")->orderBy("tgl_realisasi");
    }

    public function rencana(){
        return $this->hasMany(ProyekTenderPekerjaanRencanaModel::class, "id_proyek_tender_pekerjaan")->orderBy("tgl_rencana");
    }

    public function proyek_tender(){
        return $this->belongsTo(ProyekTenderModel::class, "id_proyek_tender");
    }
}
