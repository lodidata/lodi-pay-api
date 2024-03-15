<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Model\filter\Filterable;


/***
 * @property int status
 */
class AdminLogModel extends Model
{
    use Filterable;

    protected $table = 'admin_log';
    protected $primaryKey = 'id';

    public $timestamps = false;

    const STATUS_OFF = 0; // 失败
    const STATUS_ON = 1; // 成功
    const STATUS_ARR = [
        self::STATUS_OFF => '失败',
        self::STATUS_ON => '成功',
    ];
    protected $casts = [
        'record' => 'json',
    ];

    public function getStatusLabelAttribute($value): string
    {
        return $this->status ? static::STATUS_ARR[static::STATUS_ON] : static::STATUS_ARR[static::STATUS_OFF];
    }
}