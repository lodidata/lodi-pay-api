<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class TagValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        "name" => "requireByCreated|length:1,8|unique:\\Model\\TagModel,name",
        "status" => "requireByCreated|in:0,1",
        "description" => "max:255",
    ];

    protected $field = [
        'name' => '站点名称'
    ];

    protected $message = [
        'name.exists' => 'name exists',
        'description.max' => '描述文本最大为255位',
    ];

    protected $scene = [
        'post' => [
            'name',
            'status',
            'description',
        ],
        'put' => [
            'name',
            'status',
            'description',
        ],
    ];

}