<?php

namespace Admin\src\resource;
use Model\TagModel;


class TagFormatter extends ModelFormatter
{

    protected $model = TagModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        $model->append('status_label');
        AdminFormatter::whenLoaded($model ,'creator');
    }

}