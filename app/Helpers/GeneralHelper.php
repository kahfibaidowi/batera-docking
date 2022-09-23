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
function array_merge_without($array, $without, $merge)
{
    $new_array=$array;
    foreach($without as $w){
        if(isset($new_array[$w])) unset($new_array[$w]);
    }

    return array_merge($new_array, $merge);
}
function array_without($array, $without)
{
    $new_array=$array;
    foreach($without as $w){
        if(isset($new_array[$w])) unset($new_array[$w]);
    }

    return $new_array;
}
function array_merge_with($array, $with, $merge)
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
            $akumulasi_summary=calculate_akumulasi_summary($p['report']['work_area']);

            $report=array_merge_with($p['report'], ['id_proyek_report', 'id_proyek', 'status'], [
                'progress'      =>$akumulasi_summary['progress'],
                'count_pekerjaan_pending' =>$akumulasi_summary['count_pending'],
                'count_pekerjaan_applied' =>$akumulasi_summary['count_applied'],
                'count_pekerjaan_rejected'=>$akumulasi_summary['count_rejected'],
                'count_pekerjaan'         =>$akumulasi_summary['count_pending']+$akumulasi_summary['count_applied']+$akumulasi_summary['count_rejected']
            ]);
        }

        //data
        if($login_data['role']=="shipyard"){
            if(!is_null($report)){
                if($p['report']['tender']['id_user']==$login_data['id_user']){
                    $proyek[]=array_merge_with($p, ['id_proyek', 'id_kapal', 'nama_proyek', 'tahun'], [
                        'report'=>$report
                    ]);
                }
            }
        }
        else{
            $proyek[]=array_merge_with($p, ['id_proyek', 'id_kapal', 'nama_proyek', 'tahun'], [
                'report'=>$report
            ]);
        }
    }
    
    return $proyek;
}



/*-----------------------------------------------------------------------
 * PROYEK
 *-----------------------------------------------------------------------
 */
function validation_proyek_work_area($work_area){
    //error data
    $sfi_list=[];
    $error=[];
    $error_status=false;

    //function
    function recursive_dropdown($item, &$err_status, $nest, &$id_list){
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
                    if(in_array(trim($value), $id_list)){
                        return $fail("sfi must unique");
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
            'departemen'=>[
                Rule::requiredIf(function()use($item){
                    if(!isset($item['type'])) return true;
                    if($item['type']=="pekerjaan") return true;
                }),
                function($attr, $value, $fail)use($item){
                    if($value=="CM"&&$item['responsible']=="shipowner"){
                        return $fail("if resp shipowner, dept must MD");
                    }
                    if($value=="MD"&&$item['responsible']=="shipyard"){
                        return $fail("if resp shipyard, dept must CM");
                    }
                    return true;
                },
                'in:CM,MD'
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
            'harga_satuan'=>[
                Rule::requiredIf(function()use($item){
                    if(!isset($item['type'])) return true;
                    if($item['type']=="pekerjaan") return true;
                }),
                'numeric',
                'min:0'
            ],
            'kontrak'   =>[
                Rule::requiredIf(function()use($item){
                    if(!isset($item['type'])) return true;
                    if($item['type']=="pekerjaan") return true;
                }),
                'numeric',
                'min:0'
            ],
            'additional'=>[
                Rule::requiredIf(function()use($item){
                    if(!isset($item['type'])) return true;
                    if($item['type']=="pekerjaan") return true;
                }),
                'numeric',
                'min:0'
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
                $error[]=recursive_dropdown($work, $err_status, $nest+1, $id_list);
            }
        }

        return $error;
    }

    //code
    foreach($work_area as $work){
        $error[]=recursive_dropdown($work, $error_status, 0, $sfi_list);
    }

    //execute
    return [
        'error' =>$error_status,
        'data'  =>$error
    ];
}
function recursive_dropdown_timestamps($item, $created, $updated){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_dropdown_timestamps($work, $created, $updated);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        return array_merge($item, [
            'created_at'=>$created,
            'updated_at'=>$updated
        ]);
    }
}
function add_timestamps_proyek_work_area($work_area){
    $created_at=with_timezone(date("Y-m-d H:i:s"));
    $updated_at=with_timezone(date("Y-m-d H:i:s"));

    //function

    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_dropdown_timestamps($work, $created_at, $updated_at);
    }

    return $data;
}
function owner_cost_recursive($work){
    $cost=0;

    if($work['type']=="pekerjaan"){
        if($work['responsible']=="shipowner"){
            $cost+=$work['kontrak']+$work['additional'];
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=owner_cost_recursive($item);
        }
    }

    return $cost;
}
function get_owner_cost($work_area){
    //function recursive

    //script
    $owner_cost=0;
    foreach($work_area as $work){
        $owner_cost+=owner_cost_recursive($work);
    }

    return $owner_cost;
}
function recursive_dropdown_for_sfi($sfi, $item, &$found_sfi){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_dropdown_for_sfi($sfi, $work, $found_sfi);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        if(trim($item['sfi'])==trim($sfi)){
            $found_sfi=true;
        }
        return "";
    }
}
function found_sfi_pekerjaan_work_area($sfi_search, $work_area){
    //var
    $found=false;

    //function

    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_dropdown_for_sfi($sfi_search, $work, $found);
    }

    return $found;
}

/*-----------------------------------------------------------------------
 * TENDER
 *-----------------------------------------------------------------------
 */
function recursive_volxharsat_tender($item){
    $sum=0;
    if($item['type']=="kategori"){
        foreach($item['items'] as $work){
            $sum+=recursive_volxharsat_tender($work);
        }
    }
    elseif($item['type']=="pekerjaan"){
        $sum+=$item['volume']*$item['harga_satuan'];
    }

    return $sum;
}
function recursive_dropdown_tender($item){
    if($item['type']=="kategori"){
        $sum=0;
        $data=[];
        foreach($item['items'] as $work){
            $sum+=recursive_volxharsat_tender($work);
            $data[]=recursive_dropdown_tender($work);
        }

        return array_merge($item, [
            'items'         =>$data,
            'total_kontrak' =>$sum
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        return array_merge($item, [
            'total_kontrak' =>$item['volume']*$item['harga_satuan']
        ]);
    }
}
function add_total_kontrak_tender_work_area($proyek_work_area){
    //function

    //data
    $data=[];
    foreach($proyek_work_area as $work){
        $data[]=recursive_dropdown_tender($work);
    }

    return $data;
}

/*-----------------------------------------------------------------------
 * REPORT/SUMMARY
 *-----------------------------------------------------------------------
 */
function recursive_dropdown_report($item, $created, $updated){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_dropdown_report($work, $created, $updated);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        return array_merge($item, [
            'status'    =>"preparation",
            'progress'  =>0,
            'persetujuan'   =>"pending",
            'comment'   =>"",
            'responsible_history'=>[],
            'created_at'=>$created,
            'updated_at'=>$updated
        ]);
    }
}
function generate_report_work_area($proyek_work_area){
    $created_at=with_timezone(date("Y-m-d H:i:s"));
    $updated_at=with_timezone(date("Y-m-d H:i:s"));

    //function

    $data=[];
    foreach($proyek_work_area as $work){
        $data[]=recursive_dropdown_report($work, $created_at, $updated_at);
    }

    return $data;
}
function recursive_dropdown_report_update($item, $resp, $data_edit){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_dropdown_report_update($work, $resp, $data_edit);
        }

        return array_merge($item, [
            'items' =>$data
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        if(trim($item['sfi'])==trim($data_edit['sfi'])){
            //value
            $updated_at=with_timezone(date("Y-m-d H:i:s"));

            //resp
            $dept="";
            if($data_edit['responsible']=="shipowner"){
                $dept="MD";
            }
            elseif($data_edit['responsible']=="shipyard"){
                $dept="CM";
            }

            //data
            $new_data=[
                'start'     =>$data_edit['start'],
                'end'       =>$data_edit['end'],
                'status'    =>$data_edit['status'],
                'progress'  =>$data_edit['progress'],
                'persetujuan'   =>$data_edit['persetujuan'],
                'responsible'   =>$data_edit['responsible'],
                'departemen'   =>$dept,
                'comment'   =>$data_edit['comment'],
                'updated_at'=>$updated_at
            ];
            $resp_history=$item['responsible_history'];
            $resp_history[]=[
                'before'=>array_merge_without($item, ["responsible_history"], []),
                'after' =>array_merge_without($item, ['responsible_history'], $new_data),
                'created_by'=>$resp
            ];

            //return
            return array_merge($item, $new_data, [
                'responsible_history'=>$resp_history
            ]);
        }
        return $item;
    }
}
function update_report_work_area($report_work_area, $login_data, $edit){
    //function

    $data=[];
    foreach($report_work_area as $work){
        $data[]=recursive_dropdown_report_update($work, $login_data, $edit);
    }

    return $data;
}
function calculate_summary_work_area($work_area){

    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_dropdown_summary($work);
    }

    return $data;
}
function get_progress_summary($item){
    //function recursive
    $count_pekerjaan=0;
    $count_pekerjaan_pending=0;
    $count_pekerjaan_reject=0;
    

    //script
    $sum=progress_summary_recursive($item, $count_pekerjaan, $count_pekerjaan_pending, $count_pekerjaan_reject);

    return [
        'count_pending' =>$count_pekerjaan_pending,
        'count_applied' =>$count_pekerjaan,
        'count_rejected'=>$count_pekerjaan_reject,
        'progress'      =>$sum/(($count_pekerjaan+$count_pekerjaan_pending+$count_pekerjaan_reject)?:1)
    ];
}
function progress_summary_recursive($work, &$count_work, &$count_pending, &$count_reject){
    $cost=0;

    if($work['type']=="pekerjaan"){
        if($work['persetujuan']=="applied"){
            $cost+=$work['progress'];
            $count_work++;
        }
        if($work['persetujuan']=="pending"){
            $count_pending++;
        }
        if($work['persetujuan']=="rejected"){
            $count_reject++;
        }
    }
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            $cost+=progress_summary_recursive($item, $count_work, $count_pending, $count_reject);
        }
    }

    return $cost;
}
function recursive_dropdown_summary($item){
    if($item['type']=="kategori"){
        $data=[];
        foreach($item['items'] as $work){
            $data[]=recursive_dropdown_summary($work);
        }

        $progress=get_progress_summary($item);
        return array_merge($item, [
            'items' =>$data,
            'progress'  =>$progress['progress'],
            'count_pekerjaan_pending'   =>$progress['count_pending'],
            'count_pekerjaan_rejected'  =>$progress['count_rejected'],
            'count_pekerjaan_applied'   =>$progress['count_applied'],
            'count_pekerjaan'           =>$progress['count_pending']+$progress['count_rejected']+$progress['count_applied']
        ]);
    }
    elseif($item['type']=="pekerjaan"){
        return $item;
    }
}
function calculate_akumulasi_summary($work_area){
    $data=get_progress_summary([
        'type'  =>"kategori",
        'items' =>$work_area
    ]);

    return $data;
}

/*-----------------------------------------------------------------------
 * TRACKING
 *-----------------------------------------------------------------------
 */
function find_date_max_min($work, &$min, &$max){
    if($work['type']=="kategori"){
        foreach($work['items'] as $item){
            find_date_max_min($item, $min, $max);
        }
    }
    elseif($work['type']=="pekerjaan"){
        if($min=="") $min=$work['start'];
        if($max=="") $max=$work['end'];

        if(strtotime($min)>strtotime($work['start'])) $min=$work['start'];
        if(strtotime($max)<strtotime($work['end'])) $max=$work['end'];
    }
}
function recursive_tracking_sum($item, $report=false){
    if($item['type']=="kategori"){
        $date_min="";
        $date_max="";
        find_date_max_min($item, $date_min, $date_max);

        $items=[];
        foreach($item['items'] as $v){
            $items[]=recursive_tracking_sum($v, $report);
        }
        
        $merge2=[];
        if($report){
            $akumulasi_summary=get_progress_summary($item);
            $merge2=[
                'progress'      =>$akumulasi_summary['progress'],
                'count_pekerjaan_pending' =>$akumulasi_summary['count_pending'],
                'count_pekerjaan_applied' =>$akumulasi_summary['count_applied'],
                'count_pekerjaan_rejected'=>$akumulasi_summary['count_rejected'],
                'count_pekerjaan'         =>$akumulasi_summary['count_pending']+$akumulasi_summary['count_applied']+$akumulasi_summary['count_rejected']
            ];
        }
        return array_merge($item, [
            'items' =>$items,
            'start' =>$date_min,
            'end'   =>$date_max
        ], $merge2);
    }
    elseif($item['type']=="pekerjaan"){
        return $item;
    }
}
function generate_tracking_kategori($work_area, $report=false){
    $data=[];
    foreach($work_area as $w){
        $data[]=recursive_tracking_sum($w, $report);
    }

    return $data;
}
function generate_tracking_work_area($proyek_work_area, $report){

    //rencana
    $date_max="";
    $date_min="";
    foreach($proyek_work_area as $pw){
        find_date_max_min($pw, $date_min, $date_max);
    }
    $date_rencana=[
        'work_area' =>generate_tracking_kategori($proyek_work_area),
        'start' =>$date_min,
        'end'   =>$date_max
    ];

    //realisasi
    $date_max="";
    $date_min="";
    foreach($report['work_area'] as $pw){
        find_date_max_min($pw, $date_min, $date_max);
    }
    $akumulasi_summary=calculate_akumulasi_summary($report['work_area']);
    $date_realisasi=[
        'id_proyek_report'  =>$report['id_proyek_report'],
        'status'    =>$report['status'],
        'work_area' =>generate_tracking_kategori($report['work_area'], true),
        'start' =>$date_min,
        'end'   =>$date_max,
        'progress'      =>$akumulasi_summary['progress'],
        'count_pekerjaan_pending' =>$akumulasi_summary['count_pending'],
        'count_pekerjaan_applied' =>$akumulasi_summary['count_applied'],
        'count_pekerjaan_rejected'=>$akumulasi_summary['count_rejected'],
        'count_pekerjaan'         =>$akumulasi_summary['count_pending']+$akumulasi_summary['count_applied']+$akumulasi_summary['count_rejected']
    ];

    //return
    return [
        'rencana'   =>$date_rencana,
        'realisasi' =>$date_realisasi
    ];
}
function generate_tracking_kapal($kapal, $login_data){
    $proyek=[];
    foreach($kapal['proyek'] as $p){
        $report=null;
        if(!is_null($p['report'])){
            $tracking=generate_tracking_work_area($p['work_area'], $p['report']);

            $report=$tracking;
        }

        //data
        if($login_data['role']=="shipyard"){
            if(!is_null($report)){
                if($p['report']['tender']['id_user']==$login_data['id_user']){
                    $proyek[]=array_merge_with($p, ['id_proyek', 'id_kapal', 'nama_proyek', 'tahun'], [
                        'tracking'  =>$report
                    ]);
                }
            }
        }
        else{
            $proyek[]=array_merge_with($p, ['id_proyek', 'id_kapal', 'nama_proyek', 'tahun'], [
                'tracking'  =>$report
            ]);
        }
    }
    
    return $proyek;
}

/*-----------------------------------------------------------------------
 * PENGATURAN
 *-----------------------------------------------------------------------
 */
function get_info_perusahaan(){
    //CHECK PROFILE IN DATABASE
    $key_name=[
        "profile_nama_perusahaan",
        "profile_merk_perusahaan",
        "profile_alamat_perusahaan_1",
        "profile_alamat_perusahaan_2",
        "profile_telepon",
        "profile_fax",
        "profile_npwp",
        "profile_email"
    ];
    $profile_perusahaan=PengaturanModel::whereIn("tipe_pengaturan", $key_name);
    if($profile_perusahaan->count()==0){
        DB::transaction(function() use($key_name){
            foreach($key_name as $val){
                PengaturanModel::create([
                    "tipe_pengaturan"   =>$val,
                    "value_pengaturan"  =>""
                ]);
            }
        });
    }
    
    //SUCCESS
    $profile_perusahaan=PengaturanModel::whereIn("tipe_pengaturan", $key_name)->get();

    $data=[];
    foreach($profile_perusahaan as $val){
        $data[$val['tipe_pengaturan']]=$val['value_pengaturan'];
    }

    return (object)$data;
}