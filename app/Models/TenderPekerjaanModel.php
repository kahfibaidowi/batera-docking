<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TenderPekerjaanModel extends Model{

    protected $table="tbl_tender_pekerjaan";
    protected $primaryKey="id_tender_pekerjaan";
    protected $fillable=['id_tender', 'id_proyek_pekerjaan', 'pekerjaan', 'satuan', 'qty', 'harga_satuan', 'kategori_1', 'kategori_2', 'kategori_3', 'kategori_4', 'deadline'];
    protected $hidden=[];


}
