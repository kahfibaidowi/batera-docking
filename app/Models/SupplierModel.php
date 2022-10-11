<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SupplierModel extends Model{

    protected $table="tbl_supplier";
    protected $primaryKey="id_supplier";
    protected $fillable=[
        "nama_supplier",
        "alamat",
        "email",
        "no_hp"
    ];


    /*
     *#FUNCTION
     *
     */
    public function proyek(){
        return $this->belongsTo(ProyekModel::class, "id_proyek");
    }

}
