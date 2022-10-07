<?php

namespace App\Repository;

use App\Models\KapalModel;


class KapalRepo{

    public static function gets_kapal($params, $login_data)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $kapal=KapalModel::query();
        //--shipyard
        if($login_data['role']=="shipyard"){
            $kapal=$kapal->whereHas("proyek.report.tender", function($query)use($login_data){
                $query->where("id_user", $login_data['id_user']);
            });
        }
        //--q
        $kapal->where("nama_kapal", "ilike", "%".$params['q']."%");
        //--order
        $kapal=$kapal->orderByDesc("id_kapal");
        
        //return
        return $kapal->paginate($params['per_page'])->toArray();
    }

    public static function get_kapal($kapal_id, $login_data)
    {
        //query
        $kapal=KapalModel::with("proyek", "proyek.report", "proyek.report.tender")
            ->where("id_kapal", $kapal_id)
            ->orderByDesc("id_kapal");
        
        //data
        $kapal=$kapal->first()->toArray();

        //process data
        $proyek=[];
        foreach($kapal['proyek'] as $val){
            //shipyard
            if($login_data['role']=="shipyard"){
                if(!is_null($val['report'])){
                    if($val['report']['tender']['id_user']==$login_data['id_user']){
                        $proyek[]=array_merge($val, [
                            'report'=>array_merge_without($val['report'], ['tender']),
                            'tender'=>$val['report']['tender']
                        ]);
                    }
                }
            }
            else{
                if(!is_null($val['report'])){
                    $proyek[]=array_merge($val, [
                        'report'=>array_merge_without($val['report'], ['tender']),
                        'tender'=>$val['report']['tender']
                    ]);
                }
                else{
                    $proyek[]=array_merge($val, [
                        'report'=>null,
                        'tender'=>null
                    ]);
                }
            }
        }

        //return
        return array_merge($kapal, [
            'proyek'    =>$proyek,
            'perusahaan'=>get_info_perusahaan()
        ]);
    }

    public static function gets_tracking_kapal($params, $login_data)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $kapal=KapalModel::with("proyek", "proyek.report", "proyek.report.tender");
        $kapal=$kapal->where("nama_kapal", "ilike", "%".$params['q']."%");
        //--order
        $kapal=$kapal->orderByDesc("id_kapal");
            
        //data
        $kapal=$kapal->paginate($params['per_page'])->toArray();

        $perusahaan=get_info_perusahaan();
        $data=[];
        foreach($kapal['data'] as $ship){
            $proyek=[];
            foreach($ship['proyek'] as $val){
                //shipyard
                if($login_data['role']=="shipyard"){
                    if(!is_null($val['report'])){
                        if($val['report']['tender']['id_user']==$login_data['id_user']){
                            $proyek[]=array_merge($val, [
                                'report'=>array_merge_without($val['report'], ['tender']),
                                'tender'=>$val['report']['tender']
                            ]);
                        }
                    }
                }
                else{
                    if(!is_null($val['report'])){
                        $proyek[]=array_merge($val, [
                            'report'=>array_merge_without($val['report'], ['tender']),
                            'tender'=>$val['report']['tender']
                        ]);
                    }
                    else{
                        $proyek[]=array_merge($val, [
                            'report'=>null,
                            'tender'=>null
                        ]);
                    }
                }
            }

            $data[]=array_merge($ship, [
                'proyek'    =>$proyek,
                'perusahaan'=>$perusahaan
            ]);
        }

        //return
        return array_merge($kapal, [
            'data'  =>$data
        ]);
    }
}