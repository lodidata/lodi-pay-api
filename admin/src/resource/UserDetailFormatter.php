<?php

namespace Admin\src\resource;
use Carbon\Carbon;
use Model\UserModel;

class UserDetailFormatter extends ModelFormatter
{

    protected $model = UserModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model) : void
    {
        $model->append('status_label');
        $lastOrderSn = null;
        $lasOrderCreatedAt = null;

        //代付订单
        $lastOrderPay =  $model->ordersPayLast()->select(['order_sn','created_at'])->latest()->first();
        if(null !== $lastOrderPay){
            $lastOrderSn = $lastOrderPay->order_sn;
            $lasOrderCreatedAt = $lastOrderPay->created_at;
        }
        //代充订单
        $lastCollection = $model->ordersCollectionLast()->select(['order_sn','created_at'])->latest()->first();
        if(null !== $lastCollection){
            if( $lasOrderCreatedAt && strtotime($lastCollection->created_at) < strtotime( $lasOrderCreatedAt ) ){
                $lastOrderSn = $lastCollection->order_sn;
                $lasOrderCreatedAt = $lastCollection->created_at;
            }
        }

        $model->last_order_sn = $lastOrderSn;
        $model->last_order_created_at = Carbon::parse( $lasOrderCreatedAt )->format('Y-m-d H:i:s');
    }

}