<?php

use Lib\Validate\Admin\TrialValidate;
use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Logic\PayAdmin\Api;
use Model\AdminLogModel;
use Model\OrdersCollectionModel;
use Model\OrdersCollectionTrialModel;
use Model\PayConfigModel;
use Model\TransferRecordModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_ORDER;
    protected $moduleChild = '争议订单';
    protected $moduleFunName = '发起代付';

    public function run($id)
    {
        (new TrialValidate)->paramsCheck('pay.post', $this->request, $this->response);
        $params = $this->request->getParams();
        $row = OrdersCollectionTrialModel::query()->with(['orderCollection.orderPay'])->where('id', $id)->firstOrFail();

        /**@var OrdersCollectionTrialModel $row */
        //发起代付
        $data['inner_order_sn'] = $row->orderCollection->inner_order_sn;
        $data['pay_type'] = $params['pay_type'];
        $data['order_type'] = Api::ORDER_TYPE_COLLECT;
        $result = $this->pay($data);
        if ($result['code'] != 0) {
            throw new Exception($result['message']);
        }

        //添加转账记录
        $innerOrderSn = $result['data']['inner_order_sn'] ?? '';

        $params['status'] = OrdersCollectionModel::query()->where('inner_order_sn', $innerOrderSn)->value('status');
        $this->addTransferRecord($params, $row);

        //更新代付状态
        $row->pay_status = $params['status'] == 6 ? OrdersCollectionTrialModel::PAY_STATUS_SUCCESS : OrdersCollectionTrialModel::PAY_STATUS_FAIL;
        $row->remark = $params['remark'] ?? '';
        $row->save();

        $logArr = [
            'status' => $params['status'] == 6 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起争议订单代付,内部订单号为【' . $row->orderCollection->inner_order_sn . '】',
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set(0);
    }

    private function pay(array $data)
    {
        $result = (new Api($this->ci))->pay($data);
        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起一笔代付，请求结果信息为：' . $result['message'],
            'record' => $data,
        ];
        $this->writeAdminLog($logArr);
        return $result;
    }

    private function addTransferRecord(array $params, OrdersCollectionTrialModel $row): void
    {
        $data['order_sn'] = generateDealNumber();
        $data['status'] = $params['status'] == 6 ? TransferRecordModel::STATUS_SUCCESS_KEY : TransferRecordModel::STATUS_FAIL_KEY;
        $data['pay_inner_order_sn'] = $row->orderCollection->pay_inner_order_sn;
        $data['remark'] = $params['remark'] ?? '';
        $data['bank_card_name'] = $row->orderCollection->orderPay->user_account;
        $data['bank'] = $row->orderCollection->payment;
        $data['bank_card_account'] = $row->orderCollection->orderPay->user_account;
        $data['amount'] = $row->orderCollection->amount;
        $data['received_amount'] = $params['status'] == 6 ? $row->orderCollection->amount : 0;
        $data['merchant_id'] = $row->orderCollection->merchant_id;
        $data['pay_config_id'] = PayConfigModel::query()->where('type', $params['pay_type'])->value('id');
        TransferRecordModel::query()->create($data);
    }


};