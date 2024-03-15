<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Model\filter\Filterable;

/**
 * @property string orders_collection_sn
 * @property int action_type
 * @property int problem_source
 * @property string description
 * @property string remark
 * @property int pay_status
 * @property object orderCollection
 */
class OrdersCollectionTrialModel extends Model
{
    use Filterable;

    protected $table = 'orders_collection_trial'; //表名
    protected $primaryKey = 'id'; //主键

    const PAY_STATUS_SUCCESS = 1;
    const PAY_STATUS_FAIL = 0;

    protected $fillable = [
        'orders_collection_sn',
        'action_type',
        'problem_source',
        'admin_id',
        'description',
        'remark',
        'pay_status',
    ];
    public static $actionType = [
        'pre_trial' => [
            'value' => 1,
            'message' => '待处理'
        ],
        'failed' => [
            'value' => 2,
            'message' => '订单失败'
        ],
        'success' => [
            'value' => 3,
            'message' => '订单成功'
        ],
    ];

    public static $actionTypeText = [
        1 => '待处理',
        2 => '订单失败',
        3 => '订单成功',
    ];

    public function scopeCanTrial(Builder $builder): Builder
    {
        return $builder->where('action_type', Arr::get(static::$actionType, 'pre_trial.value'));
    }

    public function orderCollection(): BelongsTo
    {
        return $this->belongsTo(OrdersCollectionModel::class, 'orders_collection_sn', 'inner_order_sn');
    }


}

