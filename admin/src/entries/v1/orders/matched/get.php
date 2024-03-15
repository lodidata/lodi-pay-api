<?php

use Logic\Admin\BaseController;
use Model\OrdersCollectionModel;
use Admin\src\resource\OrderCollectionFormatter;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): OrderCollectionFormatter
    {
        $params = $this->request->getParams();
        $row = OrdersCollectionModel::query()
            ->with([
                'orderPay.merchant:id,name,account',
                'attachment',
                'merchant:id,name,account',
            ])
            ->where('pay_inner_order_sn', '!=', '');
        if (!empty($this->playLoad['merchant_id'])) {
            $row->where('merchant_id', $this->playLoad['merchant_id']);
        }
        $row = $row->latest('id')->filter($params)->paginate($this->pageSize);
        return OrderCollectionFormatter::make($row);
    }

};