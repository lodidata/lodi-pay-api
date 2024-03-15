<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;
use Model\MerchantModel;

class MerchantValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        "name" => "requireByCreated|length:2,30|unique:\\Model\\MerchantModel,name",
        "is_pay_behalf" => "requireByCreated|in:0,1",
        "pay_behalf_level" => "requireByCreated|integer|max:999999",
        "pay_behalf_point" => "requireByCreated|number|lt:100|regex:/\d{1,2}(\.\d)?/",
        'is_collection_behalf' => 'requireByCreated|in:0,1',
        'collection_pay_level' => 'requireByCreated|integer|max:999999',
        'collection_pay_point' => 'requireByCreated|number|lt:100|regex:/\d{1,2}(\.\d)?/',
        'office_url' => 'requireByCreated|url|max:255',
        'collect_callback_url' => 'requireByCreated|url|max:255',
        'pay_callback_url' => 'requireByCreated|url|max:255',
        'ip_white_list' => 'requireByCreated|max:255'
    ];

    protected $field = [
        'name' => '站点名称'

    ];

    protected $message = [
        'name.exists' => '站点名称已存在',
    ];

    protected $scene = [
        'post' => [
            'name',
            'is_pay_behalf',
            'pay_behalf_level',
            'pay_behalf_point',
            'is_collection_behalf',
            'collection_pay_level',
            'collection_pay_point',
            'office_url',
            'pay_callback_url',
            'collect_callback_url',
            'ip_white_list'
        ],
        'put' => [
            'name',
            'is_pay_behalf',
            'pay_behalf_level',
            'pay_behalf_point',
            'is_collection_behalf',
            'collection_pay_level',
            'collection_pay_point',
            'office_url',
            'pay_callback_url',
            'collect_callback_url',
            'ip_white_list'
        ],
    ];
}