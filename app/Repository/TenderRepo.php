<?php

namespace App\Repository;

use App\Models\TenderModel;


class TenderRepo{

    public static function gets_tender_proyek($proyek_id)
    {
        //query
        $tender=TenderModel::with("proyek", "shipyard")
            ->where("id_proyek", $proyek_id)
            ->orderBy("id_tender")
            ->get()
            ->toArray();
        
        $data=[];
        foreach($tender as $val){
            $offhire_rate=($val['proyek']['off_hire_period']+$val['proyek']['off_hire_deviasi'])*$val['proyek']['off_hire_rate_per_day'];
            $offhire_bunker=($val['proyek']['off_hire_period']+$val['proyek']['off_hire_deviasi'])*$val['proyek']['off_hire_bunker_per_day'];
            $offhire_cost=$offhire_rate+$offhire_bunker;
            $general_diskon=($val['general_diskon_persen']/100)*$val['yard_total_quote'];
            $after_diskon=$val['yard_total_quote']-$general_diskon;

            $data[]=array_merge($val, [
                'off_hire_cost' =>$offhire_cost,
                'yard_total_quote'=>$val['yard_total_quote'],
                'general_diskon'=>$general_diskon,
                'after_diskon'  =>$after_diskon,
                'additional_diskon'=>$val['additional_diskon'],
                'sum_internal_adjusment'=>$val['sum_internal_adjusment']
            ]);
        }

        //return
        return $data;
    }

    public static function gets_tender($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $tender=TenderModel::with("shipyard");
        //--status
        if($params['status']!="all"){
            $tender=$tender->where("status", $params['status']);
        }
        //--order
        $tender=$tender->orderByDesc("id_tender");
        
        //return
        return $tender->paginate($params['per_page'])->toArray();
    }

    public static function get_tender($tender_id)
    {
        //query
        $tender=TenderModel::with("proyek", "shipyard")
            ->where("id_tender", $tender_id)
            ->orderBy("id_tender")
            ->first()
            ->toArray();

        //data
        $offhire_rate=($tender['proyek']['off_hire_period']+$tender['proyek']['off_hire_deviasi'])*$tender['proyek']['off_hire_rate_per_day'];
        $offhire_bunker=($tender['proyek']['off_hire_period']+$tender['proyek']['off_hire_deviasi'])*$tender['proyek']['off_hire_bunker_per_day'];
        $offhire_cost=$offhire_rate+$offhire_bunker;
        $general_diskon=($tender['general_diskon_persen']/100)*$tender['yard_total_quote'];
        $after_diskon=$tender['yard_total_quote']-$general_diskon;

        $data=array_merge($tender, [
            'off_hire_cost' =>$offhire_cost,
            'yard_total_quote'=>$tender['yard_total_quote'],
            'general_diskon'=>$general_diskon,
            'after_diskon'  =>$after_diskon,
            'additional_diskon'=>$tender['additional_diskon'],
            'sum_internal_adjusment'=>$tender['sum_internal_adjusment']
        ]);

        //return
        return $data;
    }
}