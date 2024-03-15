<?php

namespace Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string inner_order_sn
 * @property string remark
 * @property string payment
 * @property int merchant_id
 * @property string match_amount
 * @property float amount
 * @property string status_label
 * @property float balance
 * @property int status
 * @property OrdersCollectionModel|Collection matched
 * @property UserModel|Collection user
 * @property float success_amount
 * @property float handling_amount
 * @property float fail_amount
 * @property int show_thirdpay_status
 */
class OrdersPayModel extends Model
{
    protected $table = 'orders_pay'; //表名
    protected $primaryKey = 'id'; //主键

    public static $status = [
        'pre_match' => [
            'value' => 1,
            'message' => '待匹配'
        ], 'pre_upload_ticket' => [
            'value' => 2,
            'message' => '待上传凭证'
        ], 'upload_ticked_timeout' => [
            'value' => 3,
            'message' => '上传凭证超时'
        ], 'pre_check' => [
            'value' => 4,
            'message' => '待确认'
        ], 'check_timeout' => [
            'value' => 5,
            'message' => '待确认超时'
        ], 'complete' => [
            'value' => 6,
            'message' => '订单成功'
        ], 'failed' => [
            'value' => 7,
            'message' => '订单异常'
        ], 'handling' => [
            'value' => 8,
            'message' => '进行中'
        ], 'order_fail' => [
            'value' => 9,
            'message' => '订单失败'
        ],
        'canceled' => [
            'value' => 10,
            'message' => '订单取消'
        ],
        'reject' => [
            'value' => 11,
            'message' => '订单驳回'
        ]
    ];

    //已匹配的充值订单
    public function collection(): BelongsTo
    {
        return $this->belongsTo(OrdersCollectionModel::class, 'inner_order_sn', 'pay_inner_order_sn');
    }

    public function matched(): HasMany
    {
        return $this->hasMany(OrdersCollectionModel::class, 'pay_inner_order_sn', 'inner_order_sn');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(AdminModel::class, 'admin_id');
    }

    public function currency(): HasOne
    {
        return $this->hasOne(CurrencyModel::class, 'currency_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(MerchantModel::class, 'merchant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }


}

