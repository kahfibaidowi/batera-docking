<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekPekerjaanModel extends Model{

    protected $table="tbl_proyek_pekerjaan";
    protected $primaryKey="id_proyek_pekerjaan";
    protected $fillable=['id_proyek', 'satuan', 'qty', 'pekerjaan', 'kategori_1', 'kategori_2', 'kategori_3', 'kategori_4'];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
}
