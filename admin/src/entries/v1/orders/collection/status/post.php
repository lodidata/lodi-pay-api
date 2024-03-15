<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\OrdersCollectionModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_ORDER;
    protected $moduleChild = '充值订单';
    protected $moduleFunName = '标记争议';

    public function run()
    {
        $params = $this->request->getParams();
        $orderSn = $params['orders_collection_sn'] ?? 0;

        /**@var OrdersCollectionModel $order * */
        $order = OrdersCollectionModel::query()
            ->with(['orderPay', 'trial'])
            ->where('inner_order_sn', $orderSn)
            ->first();

        if (is_null($order)) {
            return $this->lang->set(200);
        }

        if ($order->trial->isNotEmpty()) {
            return $this->lang->set(201);
        }
        if (in_array($order->status, [
            OrdersCollectionModel::$statusArr['pre_match']['value'],
            OrdersCollectionModel::$statusArr['pre_upload_ticket']['value'],
            OrdersCollectionModel::$statusArr['complete']['value'],
            OrdersCollectionModel::$statusArr['canceled']['value'],
        ])) {
            return $this->lang->set(886, '此状态不能改变状态为争议订单');
        }
        $result = $order->trial()->create(['admin_id' => $this->playLoad['admin_id']]);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】将充值订单号【' . $order->inner_order_sn . '】标记为争议订单',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};