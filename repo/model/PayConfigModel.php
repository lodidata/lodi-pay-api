<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Model\filter\Filterable;

/**
 * @property string merchant_account
 * @property string name
 * @property string key
 * @property string pub_key
 * @property string payurl
 * @property string ip
 * @property int show_type
 * @property int status
 * @property int sort
 * @property string return_type
 * @property string pay_callback_domain
 * @property string params
 * @property string partner_id
 */
class PayConfigModel extends Model
{
    use Filterable;

    protected $table = 'pay_config'; //表名
    protected $primaryKey = 'id'; //主键

    protected $fillable = [
        'merchant_account',
        'name',
        'type',
        'partner_id',
        'key',
        'pub_key',
        'payurl',
        'status',
        'sort',
        'pay_callback_domain',
        'params',
        'ip',
    ];

    protected $casts = [
        'params' => 'json',
    ];

    const STATUS_DEFAULT = 'default';
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    const STATUS_ARR = [
        self::STATUS_DEFAULT => '默认',
        self::STATUS_ENABLED => '开启',
        self::STATUS_DISABLED => '关闭'
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_ARR[$this->status];
    }

    public function merchant(): HasOne
    {
        return $this->hasOne(MerchantModel::class, 'account', 'merchant_account');
    }

}