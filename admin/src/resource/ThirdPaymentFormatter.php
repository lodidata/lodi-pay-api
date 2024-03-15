<?php

namespace Admin\src\resource;

use Illuminate\Database\Eloquent\Model;
use Model\PayConfigModel;

class ThirdPaymentFormatter extends ModelFormatter
{

    protected $model = PayConfigModel::class;

    public static function prune(Model $model): void
    {
        $model->append('status_label');
    }

}