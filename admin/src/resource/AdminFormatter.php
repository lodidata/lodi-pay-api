<?php

namespace Admin\src\resource;
use Model\AdminModel;

class AdminFormatter extends ModelFormatter
{

    protected $model = AdminModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        $model->setVisible(['id','admin_name','real_name','nick_name']);
    }

}