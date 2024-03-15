<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class AdminRoleModel extends Model
{
    protected $table = 'admin_role';
    protected $primaryKey = 'id';

    protected $fillable = [
        'role_name', 'creator_id', 'creator_name', 'auth'
    ];

    const ROLE_BUSINESS = '商户';

    const STATUS_OFF = 0; // 失败
    const STATUS_ON = 1; // 成功
    const STATUS_ARR = [
        self::STATUS_OFF => '失败',
        self::STATUS_ON => '成功',
    ];
}