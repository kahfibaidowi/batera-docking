<?php

namespace App\Repository;

use App\Models\AttachmentModel;


class AttachmentRepo{

    public static function get_attachment($attachment_id)
    {
        //query
        $attachment=AttachmentModel::where("id_attachment", $attachment_id);

        //return
        return $attachment->first()->toArray();
    }

    public static function gets_attachment($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $attachment=AttachmentModel::select("id_attachment", "nama_attachment")->where("nama_attachment", "ilike", "%".$params['q']."%");
        //--order
        $attachment=$attachment->orderByDesc("id_attachment");

        //return
        return $attachment->paginate($params['per_page'])->toArray();
    }
}