<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class FinancialStatementsValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        'type' => "require|in:0,1",
        'config_id' => "require|integer",
    ];

    protected $field = [
        'type' => '类型',
        'config_id' => '配置',
    ];

    protected $message = [
        'type.require' => '类型必传',
        'config_id.require' => '配置id必传',
    ];

    protected $scene = [
        'show' => [
            'type'
        ],
        'third' => [
            'config_id'
        ]

    ];
}