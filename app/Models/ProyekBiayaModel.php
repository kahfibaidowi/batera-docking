<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProyekBiayaModel extends Model{

    protected $table="tbl_proyek_biaya";
    protected $primaryKey="id_proyek_biaya";
    protected $fillable=['id_proyek', 'off_hire_start', 'off_hire_end', 'off_hire_period', 'off_hire_deviasi', 'off_hire_rate_per_day', 'off_hire_bunker_per_day', 'list_pekerjaan'];
    protected $hidden=[];


    protected $appends=['off_hire_rate', 'off_hire_bunker', 'off_hire_rate_per_day', 'off_hire_bunker_per_day', 'list_pekerjaan'];

    /*
     *#CUSTOM RETURN ATTRIBUTE
     *
     */
    public function getRateAttribute(){
        $value=$this->attributes['rate'];
        return (double)$value;
    }
    public function getRatePerDayAttribute(){
        $value=$this->attributes['rate_per_day'];
        return (double)$value;
    }
    public function getBunkerAttribute(){
        $value=$this->attributes['bunker'];
        return (double)$value;
    }
    public function getBunkerPerDayAttribute(){
        $value=$this->attributes['bunker_per_day'];
        return (double)$value;
    }
    public function getListPekerjaanAttribute(){
        $value=$this->attributes['list_pekerjaan'];
        return (double)$value;
    }


    /*
     *#FUNCTION
     *
     */
}
