<?php

use Admin\src\resource\OrderCollectionFormatter;
use Logic\Admin\BaseController;
use Model\OrdersCollectionModel;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run($id): OrderCollectionFormatter
    {
        $row = OrdersCollectionModel::with([
            'orderPay',
            'attachment',
        ])->findOrFail($id);
        return OrderCollectionFormatter::make($row);
    }
};