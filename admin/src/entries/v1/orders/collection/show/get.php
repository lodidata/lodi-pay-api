<?php

use Logic\Admin\BaseController;
use Model\OrdersCollectionModel;
use Admin\src\resource\OrderCollectionFormatter;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run($id): OrderCollectionFormatter
    {
        $row = OrdersCollectionModel::with([
            'user:id,user_account,username',
            'merchant:id,name,account',
            'orderPay.user:id,user_account,username',
            'orderPay.merchant:id,name,account',
            'attachment:id,inner_order_sn,url,created_at'
        ])->findOrFail($id);
        return OrderCollectionFormatter::make($row);
    }
};