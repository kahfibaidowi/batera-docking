<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttachmentModel extends Model{

    protected $table="tbl_attachment";
    protected $primaryKey="id_attachment";
    protected $fillable=[
        "nama_attachment",
        "attachment"
    ];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
}
