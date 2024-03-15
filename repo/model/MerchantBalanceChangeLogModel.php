<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class MerchantBalanceChangeLogModel extends  Model
{
    protected $table = 'merchant_balance_change_log'; //表名
    protected $primaryKey = 'id'; //主键

    const ORDER_COLLECTION = 0; // 充值
    const ORDER_PAY = 1; // 提现
    const LOG_TYPE = [
        1 => '充值',
        2 => '提现',
        3 => '点位扣除金额',
        4 => '余额手动划转',
        5 => '余额自动划转',
    ];

    protected $fillable = [
        'merchant_account',
        'transaction_type',
        'order_type',
        'order_sn',
        'change_after',
        'change_before',
        'remark',
        'admin_id',
    ];
}