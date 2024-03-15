<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

/***
 * @property int id
 * @property string merchant_account
 * @property string currency
 * @property float balance
 * @method mixed increment($column, $amount = 1, array $extra = [])
 * @method mixed decrement($column, $amount = 1, array $extra = [])
 */
class MerchantPayBalanceModel extends Model
{
    protected $table = 'merchant_pay_balance'; //表名
    protected $primaryKey = 'id'; //主键
    protected $fillable = [
        'merchant_account',
        'currency',
        'balance',
    ];
}