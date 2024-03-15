<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;
use Model\MerchantModel;

class UserValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        "status" => "requireByCreated|in:0,1",
        "tags" => "requireByCreated|array",
    ];

    protected $field = [

    ];

    protected $message = [
        'status.in' => '状态',
    ];

    protected $scene = [

        'put' => [
            'status',
            'tags',
        ],
    ];
}