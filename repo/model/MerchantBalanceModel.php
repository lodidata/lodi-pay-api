<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class MerchantBalanceModel extends  Model
{
    protected $table = 'merchant_balance'; //表名
    protected $primaryKey = 'id'; //主键
    protected $fillable = [
        'merchant_account',
        'currency',
        'rechange_balance',
        'transfer_balance'
    ];
}