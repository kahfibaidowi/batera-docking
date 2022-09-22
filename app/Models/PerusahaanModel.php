<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PerusahaanModel extends Model{

    protected $table="tbl_perusahaan";
    protected $primaryKey="id_perusahaan";
    protected $fillable=[
        'nama_perusahaan',
        'merk_perusahaan',
        'alamat_perusahaan_1',
        'alamat_perusahaan_2',
        'telepon',
        'fax',
        'npwp',
        'email'
    ];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
}
