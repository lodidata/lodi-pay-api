<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Model\filter\Filterable;

/**
 * @property int id
 * @property string order_sn
 * @property string pay_inner_order_sn
 * @property int pay_config_id
 * @property string bank_card_name
 * @property string bank
 * @property string bank_card_account
 * @property double amount
 * @property double received_amount
 * @property int status
 * @property string remark
 * @property object orderCollection
 */
class TransferRecordModel extends Model
{
    use Filterable;

    protected $table = 'transfer_record'; //表名
    protected $primaryKey = 'id'; //主键

    const STATUS_FAIL_KEY = 0;
    const STATUS_PROCESSING_KEY = 1;
    const STATUS_SUCCESS_KEY = 2;

    const STATUS_ARR = [
        self::STATUS_FAIL_KEY => '转账失败',
        self::STATUS_PROCESSING_KEY => '待处理',
        self::STATUS_SUCCESS_KEY => '转账成功'
    ];

    protected $hidden = ['updated_at'];

    protected $fillable = [
        'inner_order_sn',
        'pay_inner_order_sn',
        'pay_config_id',
        'bank',
        'bank_card_account',
        'amount',
        'merchant_id',
        'status',
        'remark',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_ARR[$this->status];
    }


    public function orderCollection(): BelongsTo
    {
        return $this->belongsTo(OrdersCollectionModel::class, 'pay_inner_order_sn', 'pay_inner_order_sn');
    }

    public function payConfig(): HasOne
    {
        return $this->hasOne(PayConfigModel::class, 'id', 'pay_config_id');
    }

    public function orderPay(): HasOne
    {
        return $this->hasOne(OrdersPayModel::class, 'inner_order_sn', 'pay_inner_order_sn');
    }

    public function merchant(): HasOne
    {
        return $this->hasOne(MerchantModel::class, 'id', 'merchant_id');
    }
}

