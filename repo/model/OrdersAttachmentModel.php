<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class OrdersAttachmentModel extends Model
{
    protected $table = 'orders_attachment'; //表名
    protected $primaryKey = 'id'; //主键

    protected $fillable = ['inner_order_sn', 'url', 'type', 'remark'];

    protected $casts = [
        'url' => 'json',
    ];

    public $timestamps = false;

    const TYPE_COLLECTION = 0;
    const TYPE_PAY = 1;

    public function getUrlAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        $arr = json_decode($value, true);
        $uploadSettings = app()->settings['upload'];
        $host = $uploadSettings['dsn'][$uploadSettings['useDsn']]['domain'] ?? '';
        foreach ($arr as &$item) {
            $item = $host . $item;
        }

        return $arr;
    }

}