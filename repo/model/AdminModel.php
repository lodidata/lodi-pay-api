<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @method static find(mixed|string $id)
 * @method static select(string[] $array)
 * @method static where(string $string, mixed $admin_name)
 * @property false|mixed|string|null $password
 * @property int|mixed $creator_id
 * @property int|mixed $creator_name
 * @property mixed $real_name
 * @property mixed $admin_name
 * @property int $id
 * @property int status
 * @property int merchant_id
 * @property object adminRoleRelation
 */
class AdminModel extends Model
{
    protected $table = 'admin'; //表名
    protected $primaryKey = 'id'; //主键

    const SUPER_ADMIN_ID = 1;
    const STATUS_OFF = 0; // 禁用
    const STATUS_ON = 1; // 启用
    const STATUS_ARR = [
        self::STATUS_OFF => '禁用',
        self::STATUS_ON => '启用',
    ];

    const USER_TYPE_ADMIN = 1;
    const USER_TYPE_BUSINESS = 2;

    protected $fillable = [
        'admin_name',
        'password',
        'real_name',
        'nick_name',
        'remark',
        'status',
        'creator_id',
        'last_login_ip',
        'last_login_time'
    ];

    protected $appends = [
        'status_str'
    ];


    /**
     * 获取当前时间
     *
     * @return int
     */
    public function freshTimestamp(): int
    {
        return time();
    }

    public function getNickAttribute($value): string
    {
        return empty($value) ? 'anonymity' : $value;
    }


    public function getStatusStrAttribute(): string
    {
        return self::STATUS_ARR[$this->status];
    }

    public function adminRoleRelation(): HasOneThrough
    {
        return $this->hasOneThrough(
            AdminRoleModel::class,
            AdminRoleRelationModel::class,
            'admin_id',
            'id',
            'id',
            'role_id',
        );
    }

}