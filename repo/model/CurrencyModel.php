<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string currency_type
 */
class CurrencyModel extends Model
{
    protected $table = 'currency'; //表名
    protected $primaryKey = 'id'; //主键

    const STATUS_OFF = 0; // 下架
    const STATUS_ON = 1; // 上架
    const STATUS_ARR = [
        self::STATUS_OFF => '下架',
        self::STATUS_ON => '上架',
    ];

}