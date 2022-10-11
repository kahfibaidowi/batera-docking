<?php

namespace App\Repository;

use App\Models\ProyekReportModel;
use App\Models\ProyekReportDetailModel;
use App\Models\ProyekReportPicModel;


class SupplierRepo{

    public static function gets_report($params, $login_data)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['status']=trim($params['status']);

        //query
        $proyek_summary=ProyekReportModel::with("proyek", "proyek.kapal");
        //q
        $proyek_summary=$proyek_summary->where(function($q)use($params){
            $q->whereHas("proyek.kapal", function($query)use($params){
                $query->where("nama_kapal", "ilike", "%".$params['q']."%");
            });
        });
        //status
        if($params['status']!=""){
            $proyek_summary=$proyek_summary->where("status", $params['status']);
        }
        //shipyard
        if($login_data['role']=="shipyard"){
            $proyek_summary=$proyek_summary->whereHas("tender", function($query)use($login_data){
                $query->where("id_user", $login_data['id_user']);
            });
        }

        //order & paginate
        $proyek_summary=$proyek_summary->orderByDesc("id_proyek");
        
        //data
        $report=$proyek_summary->paginate($params['per_page'])->toArray();

        $perusahaan=get_info_perusahaan();
        $data=[];
        foreach($report['data'] as $val){
            $data[]=array_merge($val, [
                'perusahaan'=>$perusahaan
            ]);
        }

        //return
        return array_merge($report, [
            'data'  =>$data
        ]);
    }

    public static function get_report($proyek_id)
    {
        //query
        $proyek_summary=ProyekReportModel::with("proyek", "proyek.kapal", "tender", "tender.shipyard")
            ->where("id_proyek", $proyek_id)
            ->first()
            ->toArray();
        
        $general_diskon=($proyek_summary['tender']['general_diskon_persen']/100)*$proyek_summary['tender']['yard_total_quote'];
        $after_diskon=$proyek_summary['tender']['yard_total_quote']-$general_diskon;

        $data=array_merge_without($proyek_summary, ['tender'], [
            'proyek'=>array_merge_without($proyek_summary['proyek'], ['work_area']),
            'estimate_cost' =>$after_diskon,
            'perusahaan'    =>get_info_perusahaan()
        ]);

        //return
        return $data;
    }

    public static function gets_report_detail($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $detail=ProyekReportDetailModel::with("created_by")
            ->whereHas("report", function($query)use($params){
                $query->where("id_proyek", $params['id_proyek']);
            })
            ->where("type", $params['type'])
            ->orderByDesc("id_proyek_report_detail");

        //return
        return $detail->paginate($params['per_page'])->toArray();
    }

    public static function get_report_detail($report_detail_id)
    {
        //query
        $detail=ProyekReportDetailModel::with("created_by")
            ->where("id_proyek_report_detail", $report_detail_id);

        //return
        return $detail->first()->toArray();
    }

    public static function gets_pic($params)
    {
        //query
        $pic=ProyekReportPicModel::with("user")
            ->whereHas("report", function($query)use($params){
                $query->where("id_proyek", $params['id_proyek']);
            })
            ->orderByDesc("updated_at");
        
        //return
        return $pic->get()->toArray();
    }
}