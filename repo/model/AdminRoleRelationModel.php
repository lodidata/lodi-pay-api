<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class AdminRoleRelationModel extends Model
{
    protected $table = 'admin_role_relation';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'role_id'
    ];

    const STATUS_OFF = 0;
    const STATUS_ON = 1;

    const STATUS_ARR = [
        self::STATUS_OFF => '禁用',
        self::STATUS_ON => '启用',
    ];

}