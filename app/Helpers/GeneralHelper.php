<?php

use Illuminate\Support\Facades\DB;
use App\Models\UserModel;
use App\Models\ProyekModel;
use App\Models\ProyekPekerjaanModel;
use App\Models\ProyekBiayaModel;
use App\Models\TenderModel;
use App\Models\TenderPekerjaanModel;
use App\Models\TenderPekerjaanRencanaModel;
use App\Models\ProyekTenderModel;
use App\Models\ProyekTenderPekerjaanModel;
use App\Models\ProyekTenderPekerjaanRencanaModel;
use App\Models\ProyekTenderPekerjaanRealisasiModel;



function null_to_empty($var)
{
    if(is_null($var)){
        return "";
    }
    return $var;
}
function addKeyInArrayColumn($array, $key, $value)
{
    foreach ($array as $i => $element) {
        $array[$i][$key]=$value;
    }

    return $array;
}
function is_image_file($string)
{
    $upload_path=storage_path(env("UPLOAD_PATH"));
    
    if(trim($string)==""){
        return false;
    }
    if(file_exists($upload_path."/".$string)){
        $file_info=new \finfo(FILEINFO_MIME_TYPE);
        $file_show=file_get_contents($upload_path."/".$string);

        $extensions=['image/jpeg', 'image/jpg', 'image/png'];
        if(in_array($file_info->buffer($file_show), $extensions)){
            return true;
        }
        return false;
    }
    return false;
}
function count_day($start, $end){
    $time_start=strtotime($start);
    $time_end=strtotime($end);
    
    return (($time_end-$time_start)/(24*3600))+1;
}
function explode_request($req_attr){
    return explode(".", $req_attr);
}
function is_array_distinct($array, $key, $value){
    $found=0;
    foreach($array as $val){
        if($val[$key]==$value){
            $found++;
        }
        if($found==2){
            break;
        }
    }

    return $found>1?false:true;
}
function is_found_array($array, $key, $value){
    $found=0;

    foreach($array as $val){
        if($val[$key]==$value){
            $found++;
            break;
        }
    }

    return $found>0?true:false;
}
function sum_data_in_array($array, $key){
    $sum=0;
    foreach($array as $val){
        $sum+=isset($val[$key])?$val[$key]:0;
    }

    return $sum;
}
function object_to_array($object){
    return (array)$object;
}
function array_object_to_array($array_object){
    return array_map(function($value){
        return (array)$value;
    }, $array_object);
}
function with_timezone($datetime){
    return \Carbon\Carbon::parse($datetime)
            ->timezone(env("APP_TIMEZONE"));
}
function add_date($date, $day_count){
    return date("Y-m-d", strtotime($date." ".$day_count." day"));
}
function found_in_array($array, $key, $value){
    $i=0;
    foreach($array as $val) {
        if ($val[$key]===$value) {
            return $i;
        }
        $i++;
    }
    return -1;
}
function multi_array_search($array, $search)
{
    $result=[];
    foreach($array as $key=>$value)
    {
        foreach ($search as $k=>$v)
        {
            if(!isset($value[$k]) || $value[$k]!=$v)
            {
                continue 2;
            }
        }
        $result[]=$array[$key];

    }

    // Return the result array
    return $result;
}
function convert_object_to_array($data){
    return json_decode(json_encode($data), true);
}
function array_merge_without($array, $without, $merge){
    $new_array=$array;
    foreach($without as $w){
        if(isset($new_array[$w])) unset($new_array[$w]);
    }

    return array_merge($new_array, $merge);
}
function array_without($array, $without){
    $new_array=$array;
    foreach($without as $w){
        if(isset($new_array[$w])) unset($new_array[$w]);
    }

    return $new_array;
}



//-------------------------------------------------------------------------
// Query
//-------------------------------------------------------------------------
function filter_space_kategori($pekerjaan){
    $data=[];
    foreach($pekerjaan as $val){
        $data[]=array_merge($val, [
            'kategori_1'=>trim($val['kategori_1']),
            'kategori_2'=>trim($val['kategori_2']),
            'kategori_3'=>trim($val['kategori_3']),
            'kategori_4'=>trim($val['kategori_4'])
        ]);
    }

    return $data;
}
function remove_empty_kategori($params){
    $new_params=$params;
    if(trim($new_params['kategori_1'])==""){
        unset($new_params['kategori_1']);
    }
    if(trim($new_params['kategori_2'])==""){
        unset($new_params['kategori_2']);
    }
    if(trim($new_params['kategori_3'])==""){
        unset($new_params['kategori_3']);
    }
    if(trim($new_params['kategori_4'])==""){
        unset($new_params['kategori_4']);
    }

    return $new_params;
}
function get_pekerjaan_kategori($pekerjaan){
    $pekerjaan=filter_space_kategori($pekerjaan);

    //generate kategori 1-4
    $kategori=[];
    foreach($pekerjaan as $val){
        for($i=1;$i<=4;$i++){
            if(trim($val['kategori_1'])!=""){
                $kategori_1=found_in_array($kategori, "kategori", $val['kategori_1']);
                if($kategori_1>=0){
                    if(trim($val['kategori_2'])!=""){
                        $kategori_2=found_in_array($kategori[$kategori_1]['dropdown'], "kategori", $val['kategori_2']);
                        if($kategori_2>=0){
                            if(trim($val['kategori_3'])!=""){
                                $kategori_3=found_in_array($kategori[$kategori_1]['dropdown'][$kategori_2]['dropdown'], "kategori", $val['kategori_3']);
                                if($kategori_3>=0){
                                    if(trim($val['kategori_4'])!=""){
                                        $kategori_4=found_in_array($kategori[$kategori_1]['dropdown'][$kategori_2]['dropdown'][$kategori_3]['dropdown'], "kategori", $val['kategori_4']);
                                        if($kategori_4>=0){
    
                                        }
                                        else{
                                            $kategori[$kategori_1]['dropdown'][$kategori_2]['dropdown'][$kategori_3]['dropdown'][]=[
                                                'kategori'  =>$val['kategori_4'],
                                                'params'    =>[
                                                    'kategori_1'=>trim($val['kategori_1']),
                                                    'kategori_2'=>trim($val['kategori_2']),
                                                    'kategori_3'=>trim($val['kategori_3']), 
                                                    'kategori_4'=>trim($val['kategori_4'])
                                                ],
                                                'items'     =>[],
                                                'dropdown'  =>[]
                                            ];
                                        }
                                    }
                                }
                                else{
                                    $kategori[$kategori_1]['dropdown'][$kategori_2]['dropdown'][]=[
                                        'kategori'  =>$val['kategori_3'],
                                        'params'    =>[
                                            'kategori_1'=>trim($val['kategori_1']),
                                            'kategori_2'=>trim($val['kategori_2']),
                                            'kategori_3'=>trim($val['kategori_3']), 
                                            'kategori_4'=>""
                                        ],
                                        'items'     =>[],
                                        'dropdown'  =>[]
                                    ];
                                }
                            }
                        }
                        else{
                            $kategori[$kategori_1]['dropdown'][]=[
                                'kategori'  =>$val['kategori_2'],
                                'params'    =>[
                                    'kategori_1'=>trim($val['kategori_1']),
                                    'kategori_2'=>trim($val['kategori_2']),
                                    'kategori_3'=>"", 'kategori_4'=>""
                                ],
                                'items'     =>[],
                                'dropdown'  =>[]
                            ];
                        }
                    }
                }
                else{
                    $kategori[]=[
                        'kategori'  =>$val['kategori_1'],
                        'params'    =>[
                            'kategori_1'=>trim($val['kategori_1']),
                            'kategori_2'=>"", 'kategori_3'=>"", 'kategori_4'=>""
                        ],
                        'items'     =>[],
                        'dropdown'  =>[]
                    ];
                }
            }
        }
    }

    //generate kategori & pekerjaan
    $rendered_kategori=recursive_kategori($kategori, $pekerjaan);

    //return
    return $rendered_kategori;
}
function recursive_kategori($kategori, $pekerjaan){
    $v=[];
    foreach($kategori as $kd){
        $v[]=[
            'kategori'=>$kd['kategori'],
            'params'  =>$kd['params'],
            'dropdown'=>recursive_kategori($kd['dropdown'], $pekerjaan),
            'items' =>multi_array_search($pekerjaan, $kd['params']),
        ];
    }

    return $v;
}
function get_pekerjaan_kategori_parent($pekerjaan){
    $pekerjaan=filter_space_kategori($pekerjaan);

    //generate kategori
    $kategori=[];
    foreach($pekerjaan as $val){
        if(trim($val['kategori_1'])!=""){
            $kategori_1=found_in_array($kategori, "kategori", $val['kategori_1']);
            if($kategori_1==-1){
                $kategori[]=[
                    'kategori'  =>$val['kategori_1'],
                    'params'    =>[
                        'kategori_1'=>trim($val['kategori_1']),
                        'kategori_2'=>"", 'kategori_3'=>"", 'kategori_4'=>""
                    ],
                    'items'     =>[],
                    'dropdown'  =>[]
                ];   
            }
        }
    }
    
    //generate kategori & pekerjaan
    $rendered_kategori=kategori_parent($kategori, $pekerjaan);

    //return
    return $rendered_kategori;
}
function kategori_parent($kategori, $pekerjaan){
    $v=[];
    foreach($kategori as $kd){
        $params=remove_empty_kategori(object_to_array($kd['params']));

        $v[]=[
            'kategori'=>$kd['kategori'],
            'params'  =>$kd['params'],
            'items' =>multi_array_search($pekerjaan, $params),
        ];
    }

    return $v;
}
function sum_total_cost($kategori){
    $sum=0;
    foreach($kategori as $val){
        $sum+=$val['total_cost'];
    }

    return $sum;
}
function find_max_tgl($array_data, $params, $date_field="date"){
    $data="";
    $params=remove_empty_kategori(object_to_array($params));

    $mfilter=multi_array_search($array_data, $params);
    foreach($mfilter as $val){
        if($data!=""){
            if(strtotime($data)<strtotime($val[$date_field])){
                $data=$val[$date_field];
            }
        }
        else{
            $data=$val[$date_field];
        }
    }

    return $data;
}

//realisasi
function get_progress_realisasi_per_kategori($pekerjaan){
    $rendered_kategori=get_pekerjaan_kategori($pekerjaan);

    $data=[];
    foreach($rendered_kategori as $val){
        $data[]=sub_progress_realisasi_per_kategori($val, $pekerjaan);
    }

    return $data;
}
function sub_progress_realisasi_per_kategori($kategori, $pekerjaan){
    $data=[];
    foreach($kategori['dropdown'] as $val){
        $data[]=sub_progress_realisasi_per_kategori($val, $pekerjaan);
    }

    return array_merge($kategori, [
        'dropdown'  =>$data,
        'deadline'  =>find_max_tgl($pekerjaan, $kategori['params'], "rencana_deadline"),
        'last_change'=>last_change_realisasi($pekerjaan, $kategori['params']),
        'progress'  =>sum_progress_realisasi($pekerjaan, $kategori['params']),
        'total_cost'=>sum_total_cost_realisasi($pekerjaan, $kategori['params'])
    ]);
}
function get_progress_realisasi($proyek_tender_pekerjaan){
    return sum_progress_realisasi($proyek_tender_pekerjaan, [
        'kategori_1'=>"",
        'kategori_2'=>"",
        'kategori_3'=>"",
        'kategori_4'=>""
    ]);
}
function sum_progress_realisasi($array_data, $params){
    $params=remove_empty_kategori($params);

    $mfilter=multi_array_search($array_data, $params);
    $count_rencana=0;
    $sum_data=0;
    foreach($mfilter as $val){
        $sum_realisasi=0;
        foreach($val['realisasi'] as $v2){
            if($v2['status']=="applied"){
                $sum_realisasi+=$v2['qty'];
            }
        }
        //jika qty realisasi lebih besar dari rencana set ke 1
        $sum_data+=$sum_realisasi>$val['rencana_qty']?1:$sum_realisasi/($val['rencana_qty']?:1);
        $count_rencana++;
    }

    return floor(($sum_data/($count_rencana?:1))*100);
}
function last_change_realisasi($array_data, $params){
    $data="";
    $params=remove_empty_kategori(object_to_array($params));

    $mfilter=multi_array_search($array_data, $params);
    foreach($mfilter as $val){
        foreach($val['realisasi'] as $v2){
            if($data!=""){
                if($v2['status']=="applied"){
                    if(strtotime($data)<strtotime($v2["tgl_realisasi"])){
                        $data=with_timezone($v2["tgl_realisasi"]);
                    }
                }
            }
            else{
                if($v2['status']=="applied"){
                    $data=with_timezone($v2["tgl_realisasi"]);
                }
            }
        }
    }

    return $data;
}
function get_realisasi_kategori($pekerjaan){
    $rendered_kategori=get_pekerjaan_kategori_parent($pekerjaan);

    $data=[];
    foreach($rendered_kategori as $val){
        $data[]=array_merge($val, [
            'total_cost'=>sum_total_cost_realisasi($pekerjaan, $val['params'])
        ]);
    }

    return $data;
}
function sum_total_cost_realisasi($array_data, $params){
    $params=remove_empty_kategori($params);

    $mfilter=multi_array_search($array_data, $params);
    $sum_data=0;
    foreach($mfilter as $val){
        foreach($val['realisasi'] as $v2){
            if($v2['status']=="applied"){
                $sum_data+=$v2['qty']*$v2['harga_satuan'];
            }
        }
    }

    return $sum_data;
}
function get_total_cost_realisasi($pekerjaan){
    return sum_total_cost_realisasi($pekerjaan, [
        'kategori_1'=>"",
        'kategori_2'=>"",
        'kategori_3'=>"",
        'kategori_4'=>""
    ]);
}

//rencana/tender
function get_tender_rencana_per_kategori($pekerjaan){
    $rendered_kategori=get_pekerjaan_kategori($pekerjaan);

    $data=[];
    foreach($rendered_kategori as $val){
        $data[]=sub_tender_rencana_per_kategori($val, $pekerjaan);
    }

    return $data;
}
function sub_tender_rencana_per_kategori($kategori, $pekerjaan){
    $data=[];
    foreach($kategori['dropdown'] as $val){
        $data[]=sub_tender_rencana_per_kategori($val, $pekerjaan);
    }

    return array_merge($kategori, [
        'total_cost'=>sum_total_cost_tender_rencana($pekerjaan, $kategori['params']),
        'dropdown'  =>$data
    ]);
}
function get_total_cost_tender_rencana($pekerjaan){
    return sum_total_cost_tender_rencana($pekerjaan, [
        'kategori_1'=>"",
        'kategori_2'=>"",
        'kategori_3'=>"",
        'kategori_4'=>""
    ]);
}
function sum_total_cost_tender_rencana($array_data, $params){
    $params=remove_empty_kategori($params);

    $mfilter=multi_array_search($array_data, $params);
    $sum_data=0;
    foreach($mfilter as $val){
        $sum_data+=$val['qty']*$val['harga_satuan'];
    }

    return $sum_data;
}

//rencana/tender selected
function get_tender_rencana_selected_kategori($pekerjaan){
    $rendered_kategori=get_pekerjaan_kategori_parent($pekerjaan);

    $data=[];
    foreach($rendered_kategori as $val){
        $data[]=array_merge($val, [
            'total_cost'=>sum_total_cost_tender_rencana_selected($pekerjaan, $val['params'])
        ]);
    }

    return $data;
}
function sum_total_cost_tender_rencana_selected($array_data, $params){
    $params=remove_empty_kategori($params);

    $mfilter=multi_array_search($array_data, $params);
    $sum_data=0;
    foreach($mfilter as $val){
        $sum_data+=$val['rencana_qty']*$val['rencana_harga_satuan'];
    }

    return $sum_data;
}
function get_total_cost_tender_rencana_selected($pekerjaan){
    return sum_total_cost_tender_rencana_selected($pekerjaan, [
        'kategori_1'=>"",
        'kategori_2'=>"",
        'kategori_3'=>"",
        'kategori_4'=>""
    ]);
}
function get_tender_rencana_selected_per_kategori($pekerjaan){
    $rendered_kategori=get_pekerjaan_kategori($pekerjaan);

    $data=[];
    foreach($rendered_kategori as $val){
        $data[]=sub_tender_rencana_selected_per_kategori($val, $pekerjaan);
    }

    return $data;
}
function sub_tender_rencana_selected_per_kategori($kategori, $pekerjaan){
    $data=[];
    foreach($kategori['dropdown'] as $val){
        $data[]=sub_tender_rencana_selected_per_kategori($val, $pekerjaan);
    }

    return array_merge($kategori, [
        'total_cost'=>sum_total_cost_tender_rencana_selected($pekerjaan, $kategori['params']),
        'dropdown'  =>$data
    ]);
}


/*----------------------------------------------------------------------------------
 * Tracking
 *----------------------------------------------------------------------------------
 */
function get_graph_tracking_proyek($proyek_tender){
    $pekerjaan=$proyek_tender['pekerjaan'];

    //proyek
    $data=[
        'rencana'   =>get_graph_detail_proyek_rencana($proyek_tender),
        'realisasi' =>get_graph_detail_proyek_realisasi($proyek_tender)
    ];

    //data kategori
    $rendered_kategori=get_pekerjaan_kategori_parent($proyek_tender['pekerjaan']);
    $data_kategori=[];
    foreach($rendered_kategori as $val){
        $data_kategori[]=sub_graph_tracking_proyek($val, $proyek_tender);
    }

    //return
    return [
        'proyek'    =>$data,
        'kategori'  =>$data_kategori
    ];
}
function sub_graph_tracking_proyek($kategori, $proyek_tender){
    return array_merge($kategori, [
        'rencana'   =>get_graph_detail_proyek_rencana_kategori($kategori['params'], $proyek_tender),
        'realisasi' =>get_graph_detail_proyek_realisasi_kategori($kategori['params'], $proyek_tender)
    ]);
}
function get_graph_detail_proyek_rencana($proyek_tender){
    $rencana_start_date=$proyek_tender['rencana_repair_start'];
    $rencana_end_date=$proyek_tender['rencana_repair_end'];
    $rencana_period=$proyek_tender['rencana_repair_period'];

    $data=[];
    for($i=0;$i<$rencana_period;$i++){
        $tgl=add_date($rencana_start_date, "+".$i);
        $progress=get_graph_detail_proyek_rencana_progress($tgl, $proyek_tender['pekerjaan']);

        $data[]=[
            'tgl_rencana'=>$tgl,
            'progress'   =>$progress
        ];
    }

    return $data;
}
function get_graph_detail_proyek_rencana_progress($tgl, $pekerjaan){
    $count_rencana=0;
    $sum_data=0;
    foreach($pekerjaan as $val){
        $sum_date=0;
        foreach($val['rencana'] as $v2){
            $v2_tgl_rencana=date("Y-m-d", strtotime($v2['tgl_rencana']));

            if(strtotime($v2_tgl_rencana)<=strtotime($tgl)){
                $sum_date+=$v2['qty'];
            }
        }

        $sum_data+=$sum_date/($val['rencana_qty']?:1);
        $count_rencana++;
    }

    return floor(($sum_data/($count_rencana?:1))*100);
}
function get_graph_detail_proyek_realisasi($proyek_tender){
    $realisasi_start_date=get_start_date_tracking_realisasi($proyek_tender['pekerjaan']);
    $realisasi_end_date=get_end_date_tracking_realisasi($proyek_tender['pekerjaan']);
    $realisasi_period=$realisasi_start_date!=""?count_day($realisasi_start_date, $realisasi_end_date):0;

    $data=[];
    for($i=0;$i<$realisasi_period;$i++){
        $tgl=add_date($realisasi_start_date, "+".$i);
        $progress=get_graph_detail_proyek_realisasi_progress($tgl, $proyek_tender['pekerjaan']);

        $data[]=[
            'tgl_realisasi' =>$tgl,
            'progress'      =>$progress
        ];
    }

    return $data;
}
function get_graph_detail_proyek_realisasi_progress($tgl, $pekerjaan){
    $count_rencana=0;
    $sum_data=0;
    foreach($pekerjaan as $val){
        $sum_date=0;
        foreach($val['realisasi'] as $v2){
            if($v2['status']=="applied"){
                $v2_tgl_realisasi=date("Y-m-d", strtotime($v2['tgl_realisasi']));

                if(strtotime($v2_tgl_realisasi)<=strtotime($tgl)){
                    $sum_date+=$v2['qty'];
                }
            }
            
        }

        //jika qty realisasi lebih besar dari rencana set ke 1
        $sum_data+=$sum_date>$val['rencana_qty']?1:$sum_date/($val['rencana_qty']?:1);
        $count_rencana++;
    }

    return floor(($sum_data/($count_rencana?:1))*100);
}

//realisasi tracking
function get_start_date_tracking_realisasi($pekerjaan){
    $start_date="";
    foreach($pekerjaan as $val){
        foreach($val['realisasi'] as $v2){
            if($start_date==""){
                if($v2['status']=="applied"){
                    $start_date=date("Y-m-d", strtotime($v2['tgl_realisasi']));
                }
            }
            else{
                if($v2['status']=="applied"){
                    if(strtotime($start_date)>strtotime($v2['tgl_realisasi'])){
                        $start_date=date("Y-m-d", strtotime($v2['tgl_realisasi']));
                    }
                }
            }
        }
    }

    return $start_date;
}
function get_end_date_tracking_realisasi($pekerjaan){
    $end_date="";
    foreach($pekerjaan as $val){
        foreach($val['realisasi'] as $v2){
            if($end_date==""){
                if($v2['status']=="applied"){
                    $end_date=date("Y-m-d", strtotime($v2['tgl_realisasi']));
                }
            }
            else{
                if($v2['status']=="applied"){
                    if(strtotime($end_date)<strtotime($v2['tgl_realisasi'])){
                        $end_date=date("Y-m-d", strtotime($v2['tgl_realisasi']));
                    }
                }
            }
        }
    }

    return $end_date;
}

//graph kategori
function get_graph_detail_proyek_rencana_kategori($kategori, $proyek_tender){
    $rencana_start_date=$proyek_tender['rencana_repair_start'];
    $rencana_end_date=$proyek_tender['rencana_repair_end'];
    $rencana_period=$proyek_tender['rencana_repair_period'];
    
    //filter
    $params=remove_empty_kategori($kategori);
    $mfilter=multi_array_search($proyek_tender['pekerjaan'], $params);

    $data=[];
    for($i=0;$i<$rencana_period;$i++){
        $tgl=add_date($rencana_start_date, "+".$i);
        $progress=get_graph_detail_proyek_rencana_progress($tgl, $mfilter);

        $data[]=[
            'tgl_rencana'=>$tgl,
            'progress'   =>$progress
        ];
    }

    return $data;
}
function get_graph_detail_proyek_realisasi_kategori($kategori, $proyek_tender){
    $realisasi_start_date=get_start_date_tracking_realisasi($proyek_tender['pekerjaan']);
    $realisasi_end_date=get_end_date_tracking_realisasi($proyek_tender['pekerjaan']);
    $realisasi_period=$realisasi_start_date!=""?count_day($realisasi_start_date, $realisasi_end_date):0;

    //filter
    $params=remove_empty_kategori($kategori);
    $mfilter=multi_array_search($proyek_tender['pekerjaan'], $params);

    $data=[];
    for($i=0;$i<$realisasi_period;$i++){
        $tgl=add_date($realisasi_start_date, "+".$i);
        $progress=get_graph_detail_proyek_realisasi_progress($tgl, $mfilter);

        $data[]=[
            'tgl_realisasi' =>$tgl,
            'progress'      =>$progress
        ];
    }

    return $data;
}