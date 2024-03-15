<?php

namespace Admin\src\resource;
use Model\OrdersCollectionPayModel;
use Model\OrdersCollectionTrialModel;

class OrderCollectionTrialFormatter extends ModelFormatter
{

    protected $model = OrdersCollectionTrialModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        //$model->append('order_type_label');
        //$model->append('status_label');
        //OrderPayFormatter::whenLoaded($model , 'orderPay');
        OrderCollectionFormatter::whenLoaded($model , 'orderCollection');
        OrderPayCollectionFormatter::whenLoaded($model, 'matched');
    }

}