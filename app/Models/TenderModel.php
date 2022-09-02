<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\ProyekModel;

class TenderModel extends Model{

    protected $table="tbl_tender";
    protected $primaryKey="id_tender";
    protected $fillable=['id_proyek', 'id_user', 'off_hire_start', 'off_hire_end', 'off_hire_period', 'off_hire_deviasi', 'off_hire_rate_per_day', 'off_hire_bunker_per_day', 'repair_start', 'repair_end', 'repair_period', 'repair_in_dock_start', 'repair_in_dock_end', 'repair_in_dock_period', 'repair_additional_day', 'diskon_umum_persen', 'diskon_tambahan', 'status'];
    protected $hidden=[];


    /*
     *#FUNCTION
     *
     */
    public function proyek(){
        return $this->belongsTo(ProyekModel::class, "id_proyek");
    }
}
