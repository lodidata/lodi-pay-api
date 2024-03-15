<?php

namespace Admin\src\resource;
use Model\OrdersCollectionPayModel;

class OrderPayCollectionFormatter extends ModelFormatter
{

    protected $model = OrdersCollectionPayModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        $model->append('order_type_label');
        $model->append('status_label');
        OrderPayFormatter::whenLoaded($model , 'orderPay');
        OrderCollectionFormatter::whenLoaded($model , 'orderCollection');
    }

}