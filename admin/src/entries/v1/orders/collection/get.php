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
        $row = OrdersCollectionModel::query()->with([
            'user:id,user_account,username',
            'merchant:id,name,account',
            'orderPay.merchant:id,name,account',
            'orderPay.user:id,user_account,username',
            'attachment:id,inner_order_sn,url',
            'trial:id,orders_collection_sn',
        ]);

        if (!empty($this->playLoad['merchant_id'])) {
            $row->where('merchant_id', $this->playLoad['merchant_id']);
        }
        $row = $row->latest('id')->filter($params)->paginate($this->pageSize);

        $orderStatusArr = [
            OrdersCollectionModel::$statusArr['upload_ticket_timeout']['value'],
        ];
        $row->each(function ($item) use ($orderStatusArr) {
            /**@var OrdersCollectionModel $item * */
            //标记争议按钮处理
            $item->show_status = 1;
            if (($item->order_type == 2) ||
                ($item->order_type == 1 && !in_array($item->status, $orderStatusArr)) || $item->order_type == 3) {
                $item->show_status = 0;
            }
            if ($item->trial->isNotEmpty()) {
                $item->show_status = 2;
            }
        });

        return OrderCollectionFormatter::make($row);
    }

};