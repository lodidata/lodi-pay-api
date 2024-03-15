<?php /** @noinspection PhpUnused */

namespace Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Model\filter\Filterable;

/**
 * @property float amount
 * @property string pay_inner_order_sn
 * @property string order_sn
 * @property string inner_order_sn
 * @property int merchant_id
 * @property string merchant_account
 * @property string payment
 * @property string currency
 * @property int order_status
 * @property string user_account
 * @property int order_type
 * @property int status
 * @property string remark
 * @property int show_status 标记争议显示按钮 0=不显示，1=显示争议按钮 2=查看争议按钮
 * @property float success_amount
 * @property float fail_amount
 * @property float third_pay_amount
 * @property OrdersCollectionTrialModel|Collection trial
 */
class OrdersCollectionModel extends Model
{
    use Filterable;

    protected $table = 'orders_collection'; //表名
    protected $primaryKey = 'id'; //主键

    const WAITING = 'waiting';
    const FAIL = 'fail';
    const SUCCESS = 'success';
    const CANCELED = 'canceled';

    public static $orderTypes = [
        'local_pay' => [
            'value' => 1,
            'message' => '内充订单'
        ],
        'third_pay' => [
            'value' => 2,
            'message' => '兜底订单'
        ]
    ];

    public static $statusArr = [
        'pre_match' => [
            'value' => 1,
            'message' => '待匹配'
        ],
        'pre_upload_ticket' => [
            'value' => 2,
            'message' => '待上传凭证'
        ],
        'upload_ticket_timeout' => [
            'value' => 3,
            'message' => '上传凭证超时'
        ],
        'pre_check' => [
            'value' => 4,
            'message' => '待确认'
        ],
        'check_timeout' => [
            'value' => 5,
            'message' => '确认超时'
        ],
        'complete' => [
            'value' => 6,
            'message' => '订单完成'
        ],
        'failed' => [
            'value' => 7,
            'message' => '订单异常'
        ],
        'handling' => [
            'value' => 8,
            'message' => '进行中'
        ],
        'order_fail' => [
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

    public function getStatusLabelAttribute()
    {
        $status = Arr::pluck(static::$statusArr, 'message', 'value');
        return Arr::get($status, $this->status);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(AdminModel::class, 'admin_id');
    }

    public function attachment(): HasOne
    {
        return $this->HasOne(OrdersAttachmentModel::class, 'inner_order_sn', 'inner_order_sn')
            ->where('type', OrdersAttachmentModel::TYPE_COLLECTION)
            ->latest();
    }

    //提现订单
    public function orderPay(): HasOne
    {
        return $this->hasOne(OrdersPayModel::class, 'inner_order_sn', 'pay_inner_order_sn');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(MerchantModel::class, 'merchant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function getOrderTypeLabelAttribute()
    {
        $orderTypes = Arr::pluck(static::$orderTypes, 'message', 'value');
        return Arr::get($orderTypes, $this->order_type);
    }

    public function trial(): HasMany
    {
        return $this->hasMany(OrdersCollectionTrialModel::class, 'orders_collection_sn', 'inner_order_sn');
    }

    public function transferRecord(): HasOne
    {
        //notice:有多条转账记录，但是只取最新的一条给到接口
        return $this->hasOne(TransferRecordModel::class, 'pay_inner_order_sn', 'pay_inner_order_sn')->orderByDesc('created_at');
    }

}

