<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PengaturanModel extends Model{

    protected $table="tbl_pengaturan";
    protected $primaryKey="id_pengaturan";
    protected $fillable=["tipe_pengaturan", "value_pengaturan"];
    protected $hidden=[];
}