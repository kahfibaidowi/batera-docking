<?php

namespace App\Repository;

use App\Models\SupplierModel;


class SupplierRepo{

    public static function gets_supplier($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $supplier=SupplierModel::where("nama_supplier", "ilike", "%".$params['q']."%");
        //--order & paginate
        $supplier=$supplier->orderByDesc("id_supplier");
        
        //data
        return $supplier->paginate($params['per_page'])->toArray();
    }

    public static function gets_supplier_by_id($params)
    {
        //query
        $supplier=SupplierModel::whereIn("id_supplier", $params['id_supplier']);
        
        //data
        return $supplier->get()->toArray();
    }

    public static function get_supplier($supplier_id)
    {
        //query
        $supplier=SupplierModel::where("id_supplier", $supplier_id);

        //return
        return $supplier->first()->toArray();
    }
}