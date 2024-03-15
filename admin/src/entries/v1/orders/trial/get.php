<?php

use Logic\Admin\BaseController;
use Admin\src\resource\OrderCollectionTrialFormatter;
use Model\OrdersCollectionTrialModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): OrderCollectionTrialFormatter
    {
        $params = $this->request->getParams();
        $row = OrdersCollectionTrialModel::query()
            ->with([
                'orderCollection.orderPay.merchant:id,name,account',
                'orderCollection.attachment',
                'orderCollection.merchant:id,name,account',
                'orderCollection.transferRecord:id,pay_inner_order_sn,pay_config_id',
                'orderCollection.transferRecord.payConfig:id,name,type',
            ]);
        if (!empty($this->playLoad['merchant_id'])) {
            $value = $this->playLoad['merchant_id'];
            $row->whereHas('orderCollection.orderPay', function ($query) use ($value) {
                $query->where('merchant_id', $value);
            });
        }
        $row = $row->latest('id')->filter($params)->paginate($this->pageSize);
        return OrderCollectionTrialFormatter::make($row);
    }

};