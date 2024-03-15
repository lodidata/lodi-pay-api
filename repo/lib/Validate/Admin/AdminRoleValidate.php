<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class AdminRoleValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        "status" => "require|in:0,1",
        "name" => "require|length:5,15|unique:admin,name,,,AdminModel",
        "password" => "require|length:6,32",
        "password_confirm" => "require|length:6,32|confirm:password",
    ];
    protected $field = [
        "status" => "状态",
        "name" => "用户名",
        "password" => "密码",
        "password_confirm" => "二次密码",
    ];

    protected $message = [

    ];

    protected $scene = [

        'create' => [
            'name', 'password', 'password_confirm'
        ],
        'update' => [
            'name' => 'require|length:5,15|unique:admin,name^id,,,AdminModel', 'password' => 'length:6,32'
        ],
        'get' => [
            'name' => 'length:5,15', 'status' => 'in:0,1'
        ],
        'status' => [
            'status'
        ],
    ];


}