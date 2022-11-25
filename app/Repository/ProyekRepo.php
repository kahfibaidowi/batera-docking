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
        //--shipyard
        if($login_data['role']=="shipyard"){
            $proyek=$proyek->whereHas("report.tender", function($query)use($login_data){
                $query->where("id_user", $login_data['id_user']);
            });
        }
        //--order
        $proyek=$proyek->orderByDesc("id_proyek");
        
        //return
        return $proyek->paginate($params['per_page'])->toArray();
    }

    public static function get_proyek($proyek_id, $login_data)
    {
        //query
        $proyek=ProyekModel::with("kapal", "report", "report.tender", "report.tender.shipyard")
            ->where("id_proyek", $proyek_id);
        
        //data
        $proyek=$proyek->first()->toArray();
        //--offhire
        $offhire_rate=($proyek['off_hire_period']+$proyek['off_hire_deviasi'])*$proyek['off_hire_rate_per_day'];
        $offhire_bunker=($proyek['off_hire_period']+$proyek['off_hire_deviasi'])*$proyek['off_hire_bunker_per_day'];
        $proyek['off_hire_rate']=$offhire_rate;
        $proyek['off_hire_bunker']=$offhire_bunker;
        $proyek['off_hire_cost']=$offhire_rate+$offhire_bunker;
        //--custom
        $proyek['tender']=!is_null($proyek['report'])?$proyek['report']['tender']:null;
        $proyek['report']=!is_null($proyek['report'])?array_merge_without($proyek['report'], ['tender']):null;

        //return
        return $proyek;
    }
}