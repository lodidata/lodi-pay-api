<?php

namespace Admin\src\resource;

use Illuminate\Database\Eloquent\Model;
use Model\TransferRecordModel;

class TransferRecordFormatter extends ModelFormatter
{

    protected $model = TransferRecordModel::class;

    public static function prune(Model $model): void
    {
        $model->append('status_label');
    }

}