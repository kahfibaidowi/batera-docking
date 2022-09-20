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
            'departement'=>[
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
function add_timestamps_proyek_work_area($work_area){
    $created_at=with_timezone(date("Y-m-d H:i:s"));
    $updated_at=with_timezone(date("Y-m-d H:i:s"));

    //function
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

    $data=[];
    foreach($work_area as $work){
        $data[]=recursive_dropdown_timestamps($work, $created_at, $updated_at);
    }

    return $data;
}
function get_owner_cost($work_area){
    //function recursive
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

    //script
    $owner_cost=0;
    foreach($work_area as $work){
        $owner_cost+=owner_cost_recursive($work);
    }

    return $owner_cost;
}

/*-----------------------------------------------------------------------
 * TENDER
 *-----------------------------------------------------------------------
 */
function add_total_kontrak_tender_work_area($proyek_work_area){
    //function
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

    //data
    $data=[];
    foreach($proyek_work_area as $work){
        $data[]=recursive_dropdown_tender($work);
    }

    return $data;
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