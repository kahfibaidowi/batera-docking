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
}