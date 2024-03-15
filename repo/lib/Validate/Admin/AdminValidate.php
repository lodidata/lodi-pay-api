<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class AdminValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        'admin_name' => 'require|length:5,15|alphaNum|unique:admin,admin_name,,,AdminModel',
        'password' => 'require|length:6,32|alphaNum',
        'password_confirm' => 'require|length:6,32|confirm:password',
        'status' => 'require|in:0,1',
        'department' => 'length:1,20|chsAlphaNum',
        'position' => 'length:1,20|chsAlphaNum',
        'real_name' => 'length:2,60|chsAlphaNum',
        'nick_name' => 'length:2,30|chsAlphaNum',
        'role_id' => 'require|integer|min:1|exists:\\Model\\AdminRoleModel,id',
        'merchant_id' => 'integer|exists:\\Model\\MerchantModel,id|requireIf:user_type,2',
        'user_type' => 'require|integer|in:1,2',
    ];
    protected $field = [
        'admin_name' => '用户名',
        'password' => '密码',
        'password_confirm' => '二次密码',
        'status' => '状态',
    ];

    protected $message = [
        'role_id.exists' => 'role not exists',
        'password_confirm.confirm' => '两次密码输入不一致',
        'merchant_id.exists' => '商户id不存在',
        'user_type.require' => '请选择用户类型',
    ];

    protected $scene = [
        'post' => [
            'admin_name',
            'password',
            'password_confirm',
            'department',
            'position',
            'real_name',
            'nick_name',
            'role_id',
            'merchant_id',
            'user_type',
        ],

        'put' => [
            // 'admin_name' => 'require|length:5,15|unique:admin,admin_name^id,,,AdminModel',
            'department' => 'length:1,20|chsAlphaNum',
            'position' => 'length:1,20|chsAlphaNum',
            'real_name' => 'length:2,60|chsAlphaNum',
            'nick_name' => 'length:2,30|chsAlphaNum',
            'role_id' => 'integer|min:1|exists:\\Model\\AdminRoleModel,id',
        ],
        'status' => [
            'status'
        ],
        'patch' => [
            'password', 'password_confirm'
        ],
    ];
}