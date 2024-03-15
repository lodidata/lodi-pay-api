<?php

namespace Admin\src\resource;
use Model\OrdersCollectionModel;
use Model\OrdersCollectionPayModel;
use Model\OrdersPayModel;
use service\OrdersService;

class OrderCollectionFormatter extends ModelFormatter
{

    protected $model = OrdersCollectionModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        $model->append('order_type_label');
        $model->append('status_label');
       // $model->setHidden(['status','balance']);
        OrderPayFormatter::whenLoaded($model , 'orderPay');
    }
}