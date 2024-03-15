<?php

namespace Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Model\filter\Filterable;

/**
 * @property int id
 * @property string name
 * @property int is_pay_behalf
 * @property int is_collection_behalf
 * @property string order_complete_method
 * @property string account
 */
class MerchantModel extends Model
{
    use Filterable;
    use SoftDeletes;

    protected $table = 'merchant'; //表名
    protected $primaryKey = 'id'; //主键
    const ORDER_COMPLETE_METHOD_PAID = 0;
    const ORDER_COMPLETE_METHOD_COLLECTION = 1;

    const ORDER_COMPLETE_METHOD_ARR = [
        self::ORDER_COMPLETE_METHOD_PAID => '充值用户提交凭证完成',
        self::ORDER_COMPLETE_METHOD_COLLECTION => '提款用户确定到账完成',
    ];

    protected $fillable = [
        'id',
        'name',
        'account',
        'is_pay_behalf',
        'pay_behalf_level',
        'pay_behalf_point',
        'is_collection_behalf',
        'collection_pay_level',
        'collection_pay_point',
        'office_url',
        'pay_callback_url',
        'collect_callback_url',
        'order_complete_method',
        'ip_white_list',
        'recharge_waiting_limit'
    ];

    public function getOrderCompleteMethodLabelAttribute(): string
    {
        return $this->order_complete_method ? static::ORDER_COMPLETE_METHOD_ARR[static::ORDER_COMPLETE_METHOD_PAID] : static::ORDER_COMPLETE_METHOD_ARR[static::ORDER_COMPLETE_METHOD_COLLECTION];
    }

    public function getPayBehalfLabelAttribute(): string
    {
        return $this->is_pay_behalf ? "开启" : "关闭";
    }

    public function getCollectionBehalfLabelAttribute(): string
    {
        return $this->is_collection_behalf ? "开启" : "关闭";
    }

    /**
     * 获取数据
     * @param string $mchId
     * @return Builder|Model|object|null
     */
    public static function getOne(string $mchId)
    {
        //展示字段
        $fields = [
            'id',
            'name',
            'account',
            'secret_key',
            'is_pay_behalf',
            'pay_behalf_level',
            'pay_behalf_point',
            'is_collection_behalf',
            'collection_pay_level',
            'collection_pay_point',
            'office_url',
            'pay_callback_url',
            'collect_callback_url'
        ];

        return self::query()->where('account', $mchId)
            ->select($fields)
            ->first();
    }

    public function payBalance(): HasMany
    {
        return $this->hasMany(MerchantPayBalanceModel::class, 'merchant_account', 'account');
    }

    public function collectionBalance(): HasMany
    {
        return $this->hasMany(MerchantCollectionBalanceModel::class, 'merchant_account', 'account');
    }

    public function secrets(): HasMany
    {
        return $this->hasMany(MerchantSecretModel::class, 'merchant_id');
    }

    public function financial(): HasOne
    {
        return $this->hasOne(FinancialStatementsModel::class, 'merchant_id');
    }

}