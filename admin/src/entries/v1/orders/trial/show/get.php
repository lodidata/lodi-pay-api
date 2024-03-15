<?php

use Admin\src\resource\OrderCollectionTrialFormatter;
use Logic\Admin\BaseController;
use Model\OrdersCollectionTrialModel;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run($id): OrderCollectionTrialFormatter
    {
        $row = OrdersCollectionTrialModel::with([
            'orderCollection.orderPay',
            'orderCollection.attachment',
        ])->findOrFail($id);
        return OrderCollectionTrialFormatter::make($row);
    }
};