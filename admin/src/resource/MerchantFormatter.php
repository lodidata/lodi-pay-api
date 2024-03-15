<?php

namespace Admin\src\resource;
use Model\MerchantModel;

class MerchantFormatter extends ModelFormatter
{

    protected $model = MerchantModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        $model->append('pay_behalf_label');
        $model->append('collection_behalf_label');
        $model->append('order_complete_method_label');
    }

}