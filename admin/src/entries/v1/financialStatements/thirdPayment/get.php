<?php

use Model\PayConfigModel;
use Logic\Admin\BaseController;
use Admin\src\resource\ThirdPaymentFormatter;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        $row = PayConfigModel::query()
            ->orderBy('sort')
            ->orderByDesc('id')
            ->where('status', PayConfigModel::STATUS_ENABLED)
            ->get(['merchant_account','id','name','status']);
        return ThirdPaymentFormatter::make($row);
    }
};