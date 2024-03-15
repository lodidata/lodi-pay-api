<?php

namespace Admin\src\resource;

use Model\OrdersCollectionPayModel;
use Model\OrdersPayModel;
use service\OrdersService;

class AdminLogFormatter extends ModelFormatter
{

    public static function prune(\Illuminate\Database\Eloquent\Model $model): void
    {
        $model->append('status_label');
    }

}