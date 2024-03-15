<?php

namespace Admin\src\resource;

use Model\OrdersCollectionPayModel;
use Model\OrdersPayModel;
use service\OrdersService;

class FinancialStatementsFormatter extends ModelFormatter
{


    public static function prune(\Illuminate\Database\Eloquent\Model $model): void
    {
        $model->setHidden(['id', 'created_at', 'updated_at']);
    }

}