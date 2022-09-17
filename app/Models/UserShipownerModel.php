<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserShipownerModel extends Model{

    protected $table="tbl_users_shipowner";
    protected $primaryKey="id_user_shipowner";
    protected $fillable=[
        'id_user',
        'kapal_tersisa'
    ];

    /*
     *#FUNCTION
     *
     */
    public function shipowner(){
        return $this->belongsTo(UserModel::class, "id_user");
    }
}
