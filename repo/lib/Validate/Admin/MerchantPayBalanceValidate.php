<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class MerchantPayBalanceValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        "amount" => "require|integer|gt:0",
        "merchant_account" => "require",
        "currency" => "require",
        "type" => "require|integer|in:0,1",
    ];
    protected $field = [
        "amount" => "charge amount",
        "type" => "charge type",
        "merchant_account" => "merchant account",
    ];

    protected $message = [

    ];

    protected $scene = [
        'recharge' => [
            'amount', 'merchant_account', 'currency', 'type'
        ],

    ];


}