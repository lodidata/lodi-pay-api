<?php

use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Model\AdminLogModel;
use Model\OrdersCollectionModel;
use Model\TransferRecordModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_THIRD_PAYMENT;
    protected $moduleChild = '第三方代付';
    protected $moduleFunName = '发起转账';

    public function run()
    {
        $params = $this->request->getParams();
        $row = OrdersCollectionModel::query()->where('pay_inner_order_sn', $params['pay_inner_order_sn'])->first();
        if (!$row) {
            return $this->lang->set(161);
        }
        /** @var OrdersCollectionModel $row * */
        $data = $this->paramHandle($params, $row);
        $result = TransferRecordModel::query()->create($data);

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起转账,商户id为【' . $params['pay_inner_order_sn'] . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }

    private function paramHandle(array $params, OrdersCollectionModel $row): array
    {
        $params['order_sn'] = generateDealNumber();
        $params['status'] = TransferRecordModel::STATUS_PROCESSING_KEY;
        $params['bank'] = 'GCASH';
        $params['amount'] = $row->amount;
        return $params;
    }


};