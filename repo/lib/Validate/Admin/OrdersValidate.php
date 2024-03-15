<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;

class OrdersValidate extends BaseValidate
{

    protected $rule = [
        "options_type" => "isNotEmpty",
        "order_sn" => "isNotEmpty",
        "inner_order_sn" => "isNotEmpty",
        'start_time' => 'isNotEmpty',
        'end_time' => 'isNotEmpty',
        'pay_order_sn' => 'isNotEmpty',
        'collection_order_sn' => 'isNotEmpty',
        'page' => 'isNotEmpty|number',
        'page_size' => 'isNotEmpty|number',
        'remark' => 'require',
        'url' => 'require',
    ];

    protected $message = [
        'options_type.isNotEmpty' => '选项卡不能为空',
        'page.isNotEmpty' => '页码不能为空',
        'page_size.isNotEmpty' => '页显示数量不能为空',
        'order_sn.isNotEmpty' => '站点订单号不能为空',
        'inner_order_sn.isNotEmpty' => '内部单号不能为空',
        'start_time.isNotEmpty' => '开始日期不能为空',
        'end_time.isNotEmpty' => '结束时间不能为空',
        'pay_order_sn.isNotEmpty' => '充值订单号不能为空',
        'collection_order_sn.isNotEmpty' => '提现订单号不能为空'
    ];

    protected $scene = [
        'get' => [
            'page',
            'page_size',
            'options_type'
        ],
        'status.put' => [
            'remark',
            'url',
        ],
    ];

}