<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class MerchantSecretModel extends  Model
{
    protected $table = 'merchant_secret'; //表名
    protected $primaryKey = 'id'; //主键
    protected $fillable = [
        'merchant_id',
        'merchant_key',
        'merchant_public_key',
        'secret_key',
        'public_key'
    ];
    public $timestamps = false;
}