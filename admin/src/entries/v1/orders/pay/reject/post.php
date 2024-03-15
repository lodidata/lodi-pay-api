<?php

use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Logic\PayAdmin\Api;
use Model\AdminLogModel;
use Model\OrdersPayModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_ORDER;
    protected $moduleChild = '提款订单';
    protected $moduleFunName = '驳回';

    public function run($id)
    {
        $params = $this->request->getParams();
        $row = OrdersPayModel::query()->where('id', $id)->firstOrFail();

        /**@var OrdersPayModel $row */
        $data['inner_order_sn'] = $row->inner_order_sn;
        $result = $this->reject($data);
        if ($result['code'] != 0) {
            return $this->lang->set(886, [$result['message']]);
        }

        $logArr = [
            'status' => AdminLogModel::STATUS_ON,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起驳回,内部订单号为【' . $row->inner_order_sn . '】',
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set(0);
    }

    private function reject(array $data)
    {
        $result = (new Api($this->ci))->reject($data);
        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起驳回，请求结果信息为：' . $result['message'],
            'record' => $data,
        ];
        $this->writeAdminLog($logArr);
        return $result;
    }


};