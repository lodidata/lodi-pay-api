<?php

namespace Admin\src\resource;
use Model\UserModel;

class UserFormatter extends ModelFormatter
{

    protected $model = UserModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        $model->append('status_label');
    }

}