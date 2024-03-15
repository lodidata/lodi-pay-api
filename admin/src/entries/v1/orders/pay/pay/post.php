<?php

use Lib\Validate\Admin\TrialValidate;
use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Logic\PayAdmin\Api;
use Model\AdminLogModel;
use Model\OrdersCollectionModel;
use Model\OrdersPayModel;
use Model\PayConfigModel;
use Model\TransferRecordModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_ORDER;
    protected $moduleChild = '提款订单';
    protected $moduleFunName = '发起代付';

    public function run($id)
    {
        (new TrialValidate)->paramsCheck('pay.post', $this->request, $this->response);
        $params = $this->request->getParams();
        $row = OrdersPayModel::query()->with(['user:id,user_account,username'])->where('id', $id)->firstOrFail();

        /**@var OrdersPayModel $row */
        //发起代付
        $data['inner_order_sn'] = $row->inner_order_sn;
        $data['pay_type'] = $params['pay_type'];
        $data['order_type'] = Api::ORDER_TYPE_PAY;
        $result = $this->pay($data);
        if ($result['code'] != 0) {
            return $this->lang->set(886, [$result['message']]);
        }

        //添加转账记录
        $innerOrderSn = $result['data']['inner_order_sn'] ?? '';
        $params['status'] = OrdersCollectionModel::query()->where('inner_order_sn', $innerOrderSn)->value('status');
        $params['inner_order_sn'] = $innerOrderSn;
        $this->addTransferRecord($params, $row);

        $row->remark = $params['remark'] ?? '';
        $row->save();
        $logArr = [
            'status' => $params['status'] == 6 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起提款订单代付,内部订单号为【' . $row->inner_order_sn . '】',
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set(0);
    }

    private function pay(array $data)
    {
        $result = (new Api($this->ci))->pay($data);
        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起一笔提款代付，请求结果信息为：' . $result['message'],
            'record' => $data,
        ];
        $this->writeAdminLog($logArr);
        return $result;
    }

    private function addTransferRecord(array $params, OrdersPayModel $row): void
    {

        $data['inner_order_sn'] = $params['inner_order_sn'];
        $data['status'] = $params['status'] == 6 ? TransferRecordModel::STATUS_SUCCESS_KEY : TransferRecordModel::STATUS_FAIL_KEY;
        $data['pay_inner_order_sn'] = $row->inner_order_sn;
        $data['remark'] = $params['remark'] ?? '';
        $data['bank'] = $row->payment;
        $data['bank_card_account'] = $row->user->user_account;
        $data['amount'] = $params['amount'];
        $data['merchant_id'] = $row->merchant_id;
        $data['pay_config_id'] = PayConfigModel::query()->where('type', $params['pay_type'])->value('id');
        TransferRecordModel::query()->create($data);
    }


};