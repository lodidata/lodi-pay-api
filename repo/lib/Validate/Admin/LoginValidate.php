<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class LoginValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        'token' => 'require|length:32',
        'code' => 'require|length:4',
        'admin_name' => 'require|length:4,15',
        'password' => 'require|length:6,32',
        'new_password' => 'require|length:6,32|confirm:password',
        'old_password' => 'require|length:6,32',
    ];

    protected $field = [
        'token' => 'token不能为空',
        'code' => '验证码不能为空',
        'admin_name' => '用户名非法',
        'password' => '密码非法',
    ];

    protected $message = [
        'password.require' => '新密码不能为空',
        'new_password.require' => '二次密码不能为空',
        'new_password.confirm' => '两次密码输入不一致',
        'old_password.require' => '旧密码不能为空',
    ];

    protected $scene = [
        'post' => [
            'token', 'code', 'admin_name', 'password'
        ],
        'pw.patch' => [
            'password',
            'new_password',
            'old_password',
        ],
    ];
}