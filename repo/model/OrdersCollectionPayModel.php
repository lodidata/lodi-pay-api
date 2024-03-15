<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Model\filter\Filterable;

class OrdersCollectionPayModel extends Model
{
    use Filterable;
    protected $table = 'orders_collection_pay'; //表名
    protected $primaryKey = 'id'; //主键

    public  static $orderTypes = [
        'localPay' => [
            'value' => 1 ,
            'message' => '内充订单'
        ],'thirdPay' => [
            'value' => 2,
            'message' => '兜底订单'
        ]
    ];

    public static $status = [
        'pre_match' => [
            'value' => 1 ,
            'message' => '待匹配'
        ],'pre_upload_ticket' => [
            'value' => 2,
            'message' => '待上传凭证'
        ],'upload_ticket_timeout' => [
            'value' => 3,
            'message' => '上传凭证超时'
        ]
        ,'pre_check' => [
            'value' => 4,
            'message' => '待确认'
        ]
        ,'check_timeout' => [
            'value' => 5,
            'message' => '确认订单超时'
        ]
        ,'complete' => [
            'value' => 6,
            'message' => '订单完成'
        ]
        ,'failed' => [
            'value' => 7,
            'message' => '订单异常'
        ]
        ,'handling' => [
            'value' => 8,
            'message' => '进行中'
        ]
        ,'order_fail' => [
            'value' => 9,
            'message' => '订单失败'
        ],
        'canceled' => [
            'value' => 10,
            'message' => '订单取消'
        ]
    ];


    public function orderPay(): BelongsTo
    {
        return $this->belongsTo(OrdersPayModel::class,'orders_pay_sn' ,'inner_order_sn');
    }

    public function orderCollection(): BelongsTo
    {
        return $this->belongsTo(OrdersCollectionModel::class ,'orders_collection_sn' ,'inner_order_sn');
    }

    public function getOrderTypeLabelAttribute(){
        $orderTypes = Arr::pluck(static::$orderTypes , 'message' , 'value');
        return Arr::get($orderTypes , $this->order_type);
    }

    public function getStatusLabelAttribute()
    {
        $status = Arr::pluck(static::$status , 'message' , 'value');
        return Arr::get($status ,  $this->status);
    }


}

