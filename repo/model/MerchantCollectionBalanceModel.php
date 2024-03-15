<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string merchant_account
 * @property string currency
 * @property int is_auto
 * @property float limit_amount
 * @property float balance
 * @method mixed increment($column, $amount = 1, array $extra = [])
 * @method mixed decrement($column, $amount = 1, array $extra = [])
 */
class MerchantCollectionBalanceModel extends Model
{
    protected $table = 'merchant_collection_balance'; //表名
    protected $primaryKey = 'id'; //主键
    protected $fillable = [
        'merchant_account',
        'currency',
        'balance',
    ];
    const AUTO_ON = 1;
    const AUTO_OFF = 0;

    const AUTO_ARR = [
        self::AUTO_OFF => '关闭',
        self::AUTO_ON => '开启',
    ];

}