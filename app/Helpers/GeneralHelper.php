<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\PengaturanModel;


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
function is_document_file($string)
{
    $upload_path=storage_path(env("UPLOAD_PATH"));
    
    if(trim($string)==""){
        return false;
    }
    if(file_exists($upload_path."/".$string)){
        $file_info=new \finfo(FILEINFO_MIME_TYPE);
        $file_show=file_get_contents($upload_path."/".$string);

        $extensions=[
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        if(in_array($file_info->buffer($file_show), $extensions)){
            return true;
        }
        return false;
    }
    return false;
}
function count_day($start, $end)
{
    $time_start=strtotime($start);
    $time_end=strtotime($end);
    
    return (($time_end-$time_start)/(24*3600))+1;
}
function explode_request($req_attr)
{
    return explode(".", $req_attr);
}
function is_array_distinct($array, $key, $value)
{
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
function is_found_array($array, $key, $value)
{
    $found=0;

    foreach($array as $val){
        if($val[$key]==$value){
            $found++;
            break;
        }
    }

    return $found>0?true:false;
}
function sum_data_in_array($array, $key)
{
    $sum=0;
    foreach($array as $val){
        $sum+=isset($val[$key])?$val[$key]:0;
    }

    return $sum;
}
function object_to_array($object)
{
    return (array)$object;
}
function array_object_to_array($array_object)
{
    return array_map(function($value){
        return (array)$value;
    }, $array_object);
}
function with_timezone($datetime)
{
    return \Carbon\Carbon::parse($datetime)
            ->timezone(env("APP_TIMEZONE"));
}
function add_date($date, $day_count)
{
    return date("Y-m-d", strtotime($date." ".$day_count." day"));
}
function found_in_array($array, $key, $value)
{
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
function convert_object_to_array($data)
{
    return json_decode(json_encode($data), true);
}
function array_merge_without($array, $without=[], $merge=[])
{
    $new_array=$array;
    foreach($without as $w){
        if(isset($new_array[$w])) unset($new_array[$w]);
    }

    return array_merge($new_array, $merge);
}
function array_without($array, $without=[])
{
    $new_array=$array;
    foreach($without as $w){
        if(isset($new_array[$w])) unset($new_array[$w]);
    }

    return $new_array;
}
function array_merge_with($array, $with=[], $merge=[])
{
    $new_array=[];
    foreach($with as $w){
        if(isset($array[$w])) $new_array[$w]=$array[$w];
    }

    return array_merge($new_array, $merge);
}

/*-----------------------------------------------------------------------
 * HOME
 *-----------------------------------------------------------------------
 */
function generate_summary_kapal($kapal, $login_data){
    $proyek=[];
    foreach($kapal['proyek'] as $p){
        $report=null;
        if(!is_null($p['report'])){
            $summary=get_all_summary_work_area($p['report']['work_area'], [
                'progress'
            ]);

            $report=array_merge_with($p['report'], ['id_proyek_report', 'id_proyek', 'status', 'state'], [
                'summary_work_area' =>array_merge_without($summary, ['items', 'type'])
            ]);
        }

        //data
        if($login_data['role']=="shipyard"){
            if(!is_null($report)){
                if($p['report']['tender']['id_user']==$login_data['id_user']){
                    $proyek[]=array_merge_with($p, ['id_proyek', 'id_kapal', 'tahun'], [
                        'report'=>$report
                    ]);
                }
            }
        }
        else{
            $proyek[]=array_merge_with($p, ['id_proyek', 'id_kapal', 'tahun'], [
                'report'=>$report
            ]);
        }
    }
    
    return $proyek;
}
function generate_summary_proyek($proyek){
    $budget=get_all_summary_work_area($proyek['work_area'], ['summary_kategori']);
    if(is_null($proyek['report'])){
        $kontrak=['summary_kontrak'=>null];
        $aktual=['summary_aktual'=>null];
    }
    else{
        $kontrak=get_all_summary_work_area($proyek['report']['work_area'], ['summary_kategori_kontrak']);
        $aktual=get_all_summary_work_area($proyek['report']['work_area'], ['summary_kategori_aktual_plus_additional']);
    }

    return [
        'budget'    =>$budget['summary_budget'],
        'kontrak'   =>$kontrak['summary_kontrak'],
        'aktual'    =>$aktual['summary_aktual']
    ];
}

/*-----------------------------------------------------------------------
 * WORK AREA
 *-----------------------------------------------------------------------
 */
//check sfi in work area
function recursive_found_sfi_pekerjaan_work_area($sfi, $item, &$found_sfi, $resp){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            if(!$found_sfi){
                recursive_found_sfi_pekerjaan_work_area($sfi, $work, $found_sfi, $resp);
            }
            else{
                break;
            }
        }

        return;
    }
    elseif($item['type']=="pekerjaan"){
        if(trim($item['sfi'])==trim($sfi)){
            if($resp=="shipyard"){
                if($item['responsible']=="shipyard") $found_sfi=true;
            }
            if($resp=="shipowner"){
                if($item['responsible']=="shipowner") $found_sfi=true;
            }
            if($resp=="all"){
                $found_sfi=true;
            }
        }
        return;
    }
}
function found_sfi_pekerjaan_work_area($sfi_search, $work_area, $resp){
    //var
    $found=false;

    //function

    $data=[];
    foreach($work_area as $work){
        if(!$found){
            recursive_found_sfi_pekerjaan_work_area($sfi_search, $work, $found, $resp);
        }
        else{
            break;
        }
    }

    return $found;
}
//summary
function get_all_summary_work_area($work_area, $added_columns){
    $data=recursive_get_summary_work_area([
        'type'  =>"kategori",
        'items' =>$work_area
    ], $added_columns);

    return $data;
}
function get_summary_work_area($work_area, $added_columns=[]){
    //process
    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_get_summary_work_area($work, $added_columns);
    }

    //return
    return $data;
}
function recursive_get_summary_work_area($work_item, $added_columns=[]){
    if($work_item['type']=="kategori"){
        //PROCESS
        $data=[];
        foreach($work_item['items'] as $work){
            $data[]=recursive_get_summary_work_area($work, $added_columns);
        }

        //CUSTOM COLUMNS
        $custom_columns=[];
        //total harga
        if(in_array("total_harga", $added_columns)){
            $cost=summary_total_harga_work_area($work_item);
            
            $custom_columns['total_harga']=$cost;
        }
        //total harga kontrak
        if(in_array("total_harga_kontrak", $added_columns)){
            $cost=summary_total_harga_kontrak_work_area($work_item);
            
            $custom_columns['total_harga_kontrak']=$cost;
        }
        //total harga budget
        if(in_array("total_harga_budget", $added_columns)){
            $cost=summary_total_harga_budget_work_area($work_item);
            
            $custom_columns['total_harga_budget']=$cost;
        }
        //total harga aktual
        if(in_array("total_harga_aktual", $added_columns)){
            $cost=summary_total_harga_aktual_work_area($work_item);
            
            $custom_columns['total_harga_aktual']=$cost;
        }
        //total harga aktual+additional
        if(in_array("total_harga_aktual_plus_additional", $added_columns)){
            $cost=summary_total_harga_aktual_plus_additional_work_area($work_item);
            
            $custom_columns['total_harga_aktual_plus_additional']=$cost;
        }
        //additional
        if(in_array("additional", $added_columns)){
            $cost=summary_additional_work_area($work_item);
            
            $custom_columns['additional']=$cost;
        }
        //progress
        if(in_array("progress", $added_columns)){
            $count_applied=0;
            $count_work=0;

            $sum=summary_progress_work_area($work_item, $count_applied, $count_work);

            $custom_columns['count_pekerjaan']=$count_work;
            $custom_columns['count_pekerjaan_applied']=$count_applied;
            $custom_columns['count_pekerjaan_pending']=$count_work-$count_applied;
            $custom_columns['progress']=$sum/($count_work?:1);
        }
        //summary kategori
        if(in_array("summary_kategori", $added_columns)){
            $sum_kategori=[
                'supplies'=>0,
                'services'=>0,
                'class'   =>0,
                'others'  =>0,
                'yard_cost' =>0,
                'yard_canceled_jobs'=>0
            ];

            summary_kategori($work_item, $sum_kategori);

            $custom_columns['summary_budget']=$sum_kategori;
            $custom_columns['summary_budget']['owner_exp']=$sum_kategori['supplies']+$sum_kategori['services']+$sum_kategori['class']+$sum_kategori['others'];
            $custom_columns['summary_budget']['total_cost']=$custom_columns['summary_budget']['owner_exp']+$sum_kategori['yard_cost']+$sum_kategori['yard_canceled_jobs'];
        }
        //summary kategori budget
        if(in_array("summary_kategori_budget", $added_columns)){
            $sum_kategori=[
                'supplies'=>0,
                'services'=>0,
                'class'   =>0,
                'others'  =>0,
                'yard_cost' =>0,
                'yard_canceled_jobs'=>0
            ];

            summary_kategori_budget($work_item, $sum_kategori);

            $custom_columns['summary_budget']=$sum_kategori;
            $custom_columns['summary_budget']['owner_exp']=$sum_kategori['supplies']+$sum_kategori['services']+$sum_kategori['class']+$sum_kategori['others'];
            $custom_columns['summary_budget']['total_cost']=$custom_columns['summary_budget']['owner_exp']+$sum_kategori['yard_cost']+$sum_kategori['yard_canceled_jobs'];
        }
        //summary kategori kontrak
        if(in_array("summary_kategori_kontrak", $added_columns)){
            $sum_kategori=[
                'supplies'=>0,
                'services'=>0,
                'class'   =>0,
                'others'  =>0,
                'yard_cost' =>0,
                'yard_canceled_jobs'=>0
            ];

            summary_kategori_kontrak($work_item, $sum_kategori);

            $custom_columns['summary_kontrak']=$sum_kategori;
            $custom_columns['summary_kontrak']['owner_exp']=$sum_kategori['supplies']+$sum_kategori['services']+$sum_kategori['class']+$sum_kategori['others'];
            $custom_columns['summary_kontrak']['total_cost']=$custom_columns['summary_kontrak']['owner_exp']+$sum_kategori['yard_cost']+$sum_kategori['yard_canceled_jobs'];
        }
        //summary kategori aktual
        if(in_array("summary_kategori_aktual", $added_columns)){
            $sum_kategori=[
                'supplies'=>0,
                'services'=>0,
                'class'   =>0,
                'others'  =>0,
                'yard_cost' =>0,
                'yard_canceled_jobs'=>0
            ];

            summary_kategori_aktual($work_item, $sum_kategori);

            $custom_columns['summary_aktual']=$sum_kategori;
            $custom_columns['summary_aktual']['owner_exp']=$sum_kategori['supplies']+$sum_kategori['services']+$sum_kategori['class']+$sum_kategori['others'];
            $custom_columns['summary_aktual']['total_cost']=$custom_columns['summary_aktual']['owner_exp']+$sum_kategori['yard_cost']+$sum_kategori['yard_canceled_jobs'];
        }
        //summary kategori aktual plus additional
        if(in_array("summary_kategori_aktual_plus_additional", $added_columns)){
            $sum_kategori=[
                'supplies'=>0,
                'services'=>0,
                'class'   =>0,
                'others'  =>0,
                'yard_cost' =>0,
                'yard_canceled_jobs'=>0
            ];

            summary_kategori_aktual_plus_additional($work_item, $sum_kategori);

            $custom_columns['summary_aktual']=$sum_kategori;
            $custom_columns['summary_aktual']['owner_exp']=$sum_kategori['supplies']+$sum_kategori['services']+$sum_kategori['class']+$sum_kategori['others'];
            $custom_columns['summary_aktual']['total_cost']=$custom_columns['summary_aktual']['owner_exp']+$sum_kategori['yard_cost']+$sum_kategori['yard_canceled_jobs'];
        }
        //date min max
        if(in_array("date_min_max", $added_columns)){
            $min="";
            $max="";

            summary_date_min_max($work_item, $min, $max);
            $custom_columns['start']=$min;
            $custom_columns['end']=$max;
        }
        //last change
        if(in_array("last_change", $added_columns)){
            $last_change="";

            summary_last_change($work_item, $last_change);
            $custom_columns['updated_at']=$last_change;
        }

        //RETURN
        return array_merge($work_item, $custom_columns, [
            'items' =>$data
        ]);
    }
    elseif($work_item['type']=="pekerjaan"){
        return $work_item;
    }
}
//summary columns
function summary_total_harga_work_area($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        $cost+=$work['total_harga'];
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=summary_total_harga_work_area($item);
        }
    }

    return $cost;
}
function summary_total_harga_kontrak_work_area($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        $cost+=$work['total_harga_kontrak'];
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=summary_total_harga_kontrak_work_area($item);
        }
    }

    return $cost;
}
function summary_total_harga_budget_work_area($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        $cost+=$work['total_harga_budget'];
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=summary_total_harga_budget_work_area($item);
        }
    }

    return $cost;
}
function summary_total_harga_aktual_work_area($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        if($work['approved_shipowner']){
            $cost+=$work['total_harga_aktual'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=summary_total_harga_aktual_work_area($item);
        }
    }

    return $cost;
}
function summary_total_harga_aktual_plus_additional_work_area($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        if($work['approved_shipowner']){
            $cost+=$work['total_harga_aktual']+$work['additional'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=summary_total_harga_aktual_plus_additional_work_area($item);
        }
    }

    return $cost;
}
function summary_additional_work_area($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        if($work['approved_shipowner']){
            $cost+=$work['additional'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=summary_additional_work_area($item);
        }
    }

    return $cost;
}
function summary_progress_work_area($work, &$count_applied, &$count_work){
    $sum=0;

    if($work['type']=="pekerjaan"){
        if($work['approved_shipowner']){
            $sum+=$work['progress'];
            $count_applied++;
        }
        $count_work++;
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $sum+=summary_progress_work_area($item, $count_applied, $count_work);
        }
    }

    return $sum;
}
function summary_kategori($work, &$kategori){

    if($work['type']=="pekerjaan"){
        if(isset($kategori[$work['kategori']])){
            $kategori[$work['kategori']]+=$work['total_harga'];
        }
        else{
            $kategori[$work['kategori']]=$work['total_harga'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            summary_kategori($item, $kategori);
        }
    }
}
function summary_kategori_budget($work, &$kategori){

    if($work['type']=="pekerjaan"){
        if(isset($kategori[$work['kategori']])){
            $kategori[$work['kategori']]+=$work['total_harga_budget'];
        }
        else{
            $kategori[$work['kategori']]=$work['total_harga_budget'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            summary_kategori_budget($item, $kategori);
        }
    }
}
function summary_kategori_kontrak($work, &$kategori){

    if($work['type']=="pekerjaan"){
        if(isset($kategori[$work['kategori']])){
            $kategori[$work['kategori']]+=$work['total_harga_kontrak'];
        }
        else{
            $kategori[$work['kategori']]=$work['total_harga_kontrak'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            summary_kategori_kontrak($item, $kategori);
        }
    }
}
function summary_kategori_aktual($work, &$kategori){

    if($work['type']=="pekerjaan"){
        if($work['approved_shipowner']){
            if(isset($kategori[$work['kategori']])){
                $kategori[$work['kategori']]+=$work['total_harga_aktual'];
            }
            else{
                $kategori[$work['kategori']]=$work['total_harga_aktual'];
            }
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            summary_kategori_aktual($item, $kategori);
        }
    }
}
function summary_kategori_aktual_plus_additional($work, &$kategori){

    if($work['type']=="pekerjaan"){
        if($work['approved_shipowner']){
            if(isset($kategori[$work['kategori']])){
                $kategori[$work['kategori']]+=$work['total_harga_aktual']+$work['additional'];
            }
            else{
                $kategori[$work['kategori']]=$work['total_harga_aktual']+$work['additional'];
            }
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            summary_kategori_aktual_plus_additional($item, $kategori);
        }
    }
}
function summary_date_min_max($work, &$min, &$max){
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            summary_date_min_max($item, $min, $max);
        }
    }
    elseif($work['type']=="pekerjaan"){
        if($min=="") $min=$work['start'];
        if($max=="") $max=$work['end'];

        if(strtotime($min)>strtotime($work['start'])) $min=$work['start'];
        if(strtotime($max)<strtotime($work['end'])) $max=$work['end'];
    }
}
function summary_last_change($work, &$updated_at){
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            summary_last_change($item, $updated_at);
        }
    }
    elseif($work['type']=="pekerjaan"){
        if($updated_at=="") $updated_at=$work['updated_at'];

        if(strtotime($updated_at)<strtotime($work['updated_at'])) $updated_at=$work['updated_at'];
    }
}


/*-----------------------------------------------------------------------
 * PROYEK
 *-----------------------------------------------------------------------
 */
//validation work area
function recursive_validation_proyek_work_area($item, &$err_status, $nest, &$id_list){
    $error=[];

    //max 4 nested
    if($nest>4){
        $err_status=true;
        return ['items'=>['max 4 nested']];
    }

    //validation
    $validation=Validator::make($item, [
        'sfi'       =>[
            "required",
            function($attr, $value, $fail)use($id_list){
                if(in_array(trim($value), $id_list, true)){
                    return $fail("sfi must unique ".trim($value)." ". implode("|", $id_list));
                }
                return true;
            }
        ],
        'pekerjaan' =>"required",
        'type'      =>"required|in:kategori,pekerjaan",
        'items'     =>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="kategori") return true;
            }),
            'array'
        ],
        'start'     =>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan") return true;
            }),
            'date_format:Y-m-d'
        ],
        'end'       =>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan") return true;
            }),
            'date_format:Y-m-d',
            'after_or_equal:start'
        ],
        'responsible'=>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan") return true;
            }),
            'in:shipowner,shipyard'
        ],
        'volume'    =>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan") return true;
            }),
            'numeric',
            'min:0'
        ],
        'satuan'    =>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan") return true;
            })
        ],
        'harga_satuan'=>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan") return true;
            }),
            'numeric',
            'min:0'
        ],
        'kategori'  =>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan") return true;
            }),
            Rule::in(["supplies", "services", "class", "others", "yard_cost", "yard_canceled_jobs"])
        ],
        'catatan'   =>[
            Rule::requiredIf(function()use($item){
                if(!isset($item['type'])) return true;
                if($item['type']=="pekerjaan"){
                    if(!isset($item['catatan'])) return true;
                }
            })
        ]
    ]);
    if($validation->fails()){
        $err_status=true;
        return $validation->errors();
    }
    
    //add sfi for unique
    $id_list[]=trim($item['sfi']);

    //next
    if($item['type']=="kategori"){
        foreach($item['items'] as $work){
            $error[]=recursive_validation_proyek_work_area($work, $err_status, $nest+1, $id_list);
        }
    }

    return $error;
}
function validation_proyek_work_area($work_area){
    //error data
    $sfi_list=[];
    $error=[];
    $error_status=false;

    //code
    foreach($work_area as $work){
        $error[]=recursive_validation_proyek_work_area($work, $error_status, 0, $sfi_list);
    }

    //execute
    return [
        'error' =>$error_status,
        'data'  =>$error
    ];
}
//generate work area from input
function get_dept($resp){
    if($resp=="shipowner"){
        return "MD";
    }
    elseif($resp=="shipyard"){
        return "CM";
    }
}
function recursive_generate_proyek_work_area($item, $created_at, $updated_at){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_generate_proyek_work_area($work, $created_at, $updated_at);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        //params
        $data=[];

        $data['departemen']=get_dept($item['responsible']);
        $data['total_harga']=$item['volume']*$item['harga_satuan'];
        $data['updated_at']=$updated_at;
        if(!isset($item['created_at'])){
            $data['created_at']=$created_at;
        }
        
        //return
        return array_merge($item, $data);
    }
}
function generate_proyek_work_area($work_area){
    //params
    $created_at=with_timezone(date("Y-m-d H:i:s"));
    $updated_at=with_timezone(date("Y-m-d H:i:s"));

    //process
    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_generate_proyek_work_area($work, $created_at, $updated_at);
    }

    //return
    return $data;
}
//owner cost
function recursive_get_owner_cost($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        if($work['responsible']=="shipowner"){
            $cost+=$work['volume']+$work['harga_satuan'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=recursive_get_owner_cost($item);
        }
    }

    return $cost;
}
function get_owner_cost($work_area){
    //function recursive

    //script
    $owner_cost=0;
    foreach($work_area as $work){
        $owner_cost+=recursive_get_owner_cost($work);
    }

    return $owner_cost;
}

/*-----------------------------------------------------------------------
 * TENDER
 *-----------------------------------------------------------------------
 */
//generate work area tender
function recursive_generate_tender_work_area($item, $created_at, $updated_at){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_generate_tender_work_area($work, $created_at, $updated_at);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        //params
        $data=[];

        $data['volume_budget']=$item['volume'];
        $data['harga_satuan_budget']=$item['harga_satuan'];
        $data['total_harga_budget']=$item['volume']*$item['harga_satuan'];
        $data['harga_satuan_kontrak']=$item['harga_satuan'];
        $data['total_harga_kontrak']=$item['volume']*$item['harga_satuan'];
        $data['created_at']=$created_at;
        $data['updated_at']=$updated_at;
        
        //return
        return array_merge_without($item, ['harga_satuan'], $data);
    }
}
function generate_tender_work_area($work_area){
    //params
    $created_at=with_timezone(date("Y-m-d H:i:s"));
    $updated_at=with_timezone(date("Y-m-d H:i:s"));

    //process
    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_generate_tender_work_area($work, $created_at, $updated_at);
    }

    //return
    return $data;
}
//update tender work area
function recursive_update_tender_work_area($item, $data_edit){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_update_tender_work_area($work, $data_edit);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        if(trim($item['sfi'])==trim($data_edit['sfi'])){
            //params
            $updated_at=with_timezone(date("Y-m-d H:i:s"));

            //data
            $new_data=[
                'start'     =>$data_edit['start'],
                'end'       =>$data_edit['end'],
                'volume'    =>$data_edit['volume'],
                'harga_satuan_kontrak'  =>$data_edit['harga_satuan_kontrak'],
                'total_harga_kontrak'   =>$data_edit['volume']*$data_edit['harga_satuan_kontrak'],
                'updated_at'=>$updated_at
            ];

            //return
            return array_merge($item, $new_data);
        }
        return $item;
    }
}
function update_tender_work_area($work_area, $edit){
    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_update_tender_work_area($work, $edit);
    }

    return $data;
}

/*-----------------------------------------------------------------------
 * REPORT PROJECT
 *-----------------------------------------------------------------------
 */
//generate report work area from tender
function recursive_generate_report_work_area($item, $created_at, $updated_at){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_generate_report_work_area($work, $created_at, $updated_at);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        //params
        $data=[];
        
        $data['status']="preparation";
        $data['progress']=0;
        $data['approved_shipowner']=false;
        $data['approved_shipyard']=false;
        $data['volume']=0;
        $data['volume_kontrak']=$item['volume'];
        $data['harga_satuan_aktual']=$item['harga_satuan_kontrak'];
        $data['total_harga_aktual']=0;
        $data['additional']=0;
        $data['total_harga_aktual_plus_additional']=0;
        $data['komentar']="";
        $data['update_history']=[];
        $data['created_at']=$created_at;
        $data['updated_at']=$updated_at;
        
        //return
        return array_merge($item, $data);
    }
}
function generate_report_work_area($work_area){
    //params
    $created_at=with_timezone(date("Y-m-d H:i:s"));
    $updated_at=with_timezone(date("Y-m-d H:i:s"));

    //process
    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_generate_report_work_area($work, $created_at, $updated_at);
    }

    //return
    return $data;
}
//update report work area
function recursive_update_report_work_area($item, $login_data, $data_edit){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_update_report_work_area($work, $login_data, $data_edit);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        if(trim($item['sfi'])==trim($data_edit['sfi'])){
            //params
            $updated_at=with_timezone(date("Y-m-d H:i:s"));

            //data
            $new_data=[
                'start'     =>$data_edit['start'],
                'end'       =>$data_edit['end'],
                'status'    =>$data_edit['status'],
                'progress'  =>$data_edit['progress'],
                'volume'    =>$data_edit['volume'],
                'harga_satuan_aktual'  =>$data_edit['harga_satuan_aktual'],
                'total_harga_aktual'   =>$data_edit['volume']*$data_edit['harga_satuan_aktual'],
                'additional'=>$data_edit['additional'],
                'total_harga_aktual_plus_additional'=>($data_edit['volume']*$data_edit['harga_satuan_aktual'])+$data_edit['additional'],
                'komentar'  =>$data_edit['komentar'],
                'approved_shipowner'=>false,
                'approved_shipyard' =>false,
                'updated_at'=>$updated_at
            ];
            //update history
            $history=[
                'type'      =>"progress",
                'data'      =>array_merge_without($item, ['update_history'], $new_data),
                'created_by'=>$login_data
            ];
            $update_history=$item['update_history'];
            array_unshift($update_history, $history);

            //return
            return array_merge($item, $new_data, [
                'update_history'=>$update_history
            ]);
        }
        return $item;
    }
}
function update_report_work_area($work_area, $login_data, $edit){
    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_update_report_work_area($work, $login_data, $edit);
    }

    return $data;
}
//update report work area checklist
function recursive_update_report_work_area_checklist($item, $login_data, $data_edit){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_update_report_work_area_checklist($work, $login_data, $data_edit);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        if(trim($item['sfi'])==trim($data_edit['sfi'])){
            //params
            $updated_at=with_timezone(date("Y-m-d H:i:s"));

            //data
            $type=$data_edit['type']=="shipyard"?"approved_shipyard":"approved_shipowner";
            $new_data=[];
            $new_data[$type]=$data_edit['checked']?true:false;
            $new_data['updated_at']=$updated_at;
            //update history
            $history=[
                'type'      =>$type,
                'data'      =>array_merge_without($item, ['update_history'], $new_data),
                'created_by'=>$login_data
            ];
            $update_history=$item['update_history'];
            array_unshift($update_history, $history);

            //return
            return array_merge($item, $new_data, [
                'update_history'=>$update_history
            ]);
        }
        return $item;
    }
}
function update_report_work_area_checklist($work_area, $login_data, $edit){
    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_update_report_work_area_checklist($work, $login_data, $edit);
    }

    return $data;
}

/*-----------------------------------------------------------------------
 * TRACKING
 *-----------------------------------------------------------------------
 */
//generate tracking kapal
function generate_tracking_kapal($kapal){
    $proyek=[];
    foreach($kapal['proyek'] as $p){
        $aktual=null;
        $kontrak=null;
        $rencana=get_all_summary_work_area($p['work_area'], ['date_min_max']);
        if(!is_null($p['report'])){
            $aktual=get_all_summary_work_area($p['report']['work_area'], ['date_min_max']);
            $kontrak=get_all_summary_work_area($p['report']['tender']['work_area'], ['date_min_max']);
            
        }

        $proyek[]=array_merge_with($p, ['id_proyek', 'id_kapal', 'nama_proyek', 'tahun'], [
            'tracking'  =>[
                'rencana'   =>$rencana,
                'kontrak'   =>$kontrak,
                'aktual'    =>$aktual
            ]
        ]);
    }
    
    return $proyek;
}
//generate collapse all
function recursive_generate_collapse_report_work_area($item, &$items, $parent){
    if($item['type']=="kategori"){
        $items[]=array_merge_without($item, ['items'], [
            'parent'=>$parent
        ]);
        foreach($item['items'] as $work){
            recursive_generate_collapse_report_work_area($work, $items, $parent+1);
        }
    }
    elseif($item['type']=="pekerjaan"){
        $items[]=array_merge($item, ['parent'=>$parent]);
    }
}
function generate_collapse_report_work_area($work_area){
    $items=[];

    foreach($work_area as $work){
        recursive_generate_collapse_report_work_area($work, $items, 1);
    }

    return $items;
}