<?php

namespace App\Repository;

use App\Models\ProyekModel;


class ProyekRepo{

    public static function gets_proyek($params, $login_data)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $proyek=ProyekModel::with("kapal");
        //--q
        $proyek=$proyek->whereHas("kapal", function($query)use($params){
            $query->where("nama_kapal", "ilike", "%".$params['q']."%");
        });
        //--order
        $proyek=$proyek->orderByDesc("id_proyek");
        
        //return
        return $proyek->paginate($params['per_page'])->toArray();
    }

    public static function get_proyek($proyek_id, $login_data)
    {
        //query
        $proyek=ProyekModel::with("kapal", "report", "report.tender")
            ->where("id_proyek", $proyek_id);
        
        //data
        $proyek=$proyek->first()->toArray();
        $proyek['tender']=!is_null($proyek['report'])?$proyek['report']['tender']:null;
        $proyek['report']=!is_null($proyek['report'])?array_merge_without($proyek['report'], ['tender']):null;

        //return
        return $proyek;
    }
}