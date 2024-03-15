<?php

namespace Lib\Validate\Admin;

use Lib\Validate\BaseValidate;
use Model\PayConfigModel;

class ThirdPaymentValidate extends BaseValidate
{
    // 验证规则
    protected $rule = [
        'partner_id' => 'require|max:100',
        'merchant_account' => 'require|max:20',
        'name' => 'require|max:40',
        'payurl' => 'require|max:200',
        'pay_callback_domain' => 'max:100',
        'status' => 'require|in:' .
            PayConfigModel::STATUS_DEFAULT . ',' .
            PayConfigModel::STATUS_DISABLED . ',' .
            PayConfigModel::STATUS_ENABLED,
        'key' => 'require',
        'pub_key' => 'require',
        'ip' => 'max:255',
        'type' => 'require|max:20',
        'sort' => 'number',
    ];

    protected $field = [
        'name' => '站点名称'
    ];

    protected $message = [
        'partner_id.require' => '请填写商户id',
        'merchant_account.require' => '请填写商户名称',
        'name.require' => '请填写代付名称',
        'payurl.require' => '请填写代付接口地址',
        'status.require' => '请选择状态',
        'status.in' => '请选择正确的状态',
        'key.require' => '请填写私钥',
        'pub_key.require' => '请填写公钥',
        'type.require' => '请填写支付类型',
    ];

    protected $scene = [
        'post' => [
            'partner_id',
            'merchant_account',
            'name',
            'payurl',
            'status',
            'pay_callback_domain',
            'key',
            'pub_key',
            'type',
        ],
        'put' => [
            'name',
            'payurl',
            'status',
            'pay_callback_domain',
            'key',
            'pub_key',
            'type',
            'sort',
        ],
    ];

}