<?php

namespace App\Repository;

use App\Models\TenderModel;


class TenderRepo{

    public static function gets_tender($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $tender=TenderModel::with("shipyard", "attachment:id_attachment,nama_attachment");
        //--order
        $tender=$tender->orderByDesc("id_tender");
        
        //return
        return $tender->paginate($params['per_page'])->toArray();
    }

    public static function get_tender($tender_id)
    {
        //query
        $tender=TenderModel::with("report.proyek", "shipyard", "attachment:id_attachment,nama_attachment")
            ->where("id_tender", $tender_id)
            ->orderBy("id_tender")
            ->first()
            ->toArray();

        //data
        $general_diskon=($tender['general_diskon_persen']/100)*$tender['yard_total_quote'];
        $after_diskon=$tender['yard_total_quote']-$general_diskon;
        $yard_price=[
            'general_diskon'=>$general_diskon,
            'after_diskon'  =>$after_diskon  
        ];
        if(!is_null($tender['report'])){
            $tender['proyek']=$tender['report']['proyek'];
            $offhire_rate=($tender['proyek']['off_hire_period']+$tender['proyek']['off_hire_deviasi'])*$tender['proyek']['off_hire_rate_per_day'];
            $offhire_bunker=($tender['proyek']['off_hire_period']+$tender['proyek']['off_hire_deviasi'])*$tender['proyek']['off_hire_bunker_per_day'];
            $offhire_cost=$offhire_rate+$offhire_bunker;

            $tender['proyek']=array_merge($tender['proyek'], [
                'off_hire_rate' =>$offhire_rate,
                'off_hire_bunker'   =>$offhire_bunker,
                'off_hire_cost' =>$offhire_cost
            ]);
        }
        else{
            $tender['proyek']=null;
        }

        $data=array_merge_without($tender, ['report'], $yard_price);

        //return
        return $data;
    }
}