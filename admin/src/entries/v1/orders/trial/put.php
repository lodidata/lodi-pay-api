<?php

use Logic\Admin\BaseController;
use Logic\PayAdmin\Api;
use Model\AdminLogModel;
use Model\OrdersCollectionTrialModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];
    protected $module = Log::MODULE_ORDER;
    protected $moduleChild = '争议订单';
    protected $moduleFunName = '处理';


    public function run($id)
    {
        $params = $this->request->getParams();

        /** @var OrdersCollectionTrialModel $row */
        $row = OrdersCollectionTrialModel::query()->findOrFail($id);

        if ($row->action_type != 1 || $row->problem_source != 0) {
            return $this->lang->set(202);
        }

        $this->paramsHandle($params, $row);

        $result = $this->changeStatus($row->orders_collection_sn, $row->action_type);
        if ($result['code'] != 0) {
            return $this->lang->set(886, [$result['message']]);
        }

        $row->save();

        $logArr = [
            'status' => AdminLogModel::STATUS_ON,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】给充值订单号【' . $row->orders_collection_sn . '】的争议订单进行处理，处理结果为：' . OrdersCollectionTrialModel::$actionTypeText[$row->action_type],
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set(0);
    }

    /**
     * 发起web_api通知
     * @param string $innerOrderSn
     * @param int $actionType
     * @return void
     */
    private function changeStatus(string $innerOrderSn, int $actionType)
    {
        $result = $status = '';
        $api = new Api($this->ci);
        switch ($actionType) {
            //订单失败
            case 2:
                $result = $api->changeStatus($innerOrderSn, $api::STATUS_FAIL);
                $status = $api::STATUS_FAIL;
                break;
            //订单完成
            case 3:
                $result = $api->changeStatus($innerOrderSn, $api::STATUS_SUCCESS);
                $status = $api::STATUS_SUCCESS;
                break;
        }

        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】请求web_api改变状态接口，请求结果信息为：' . $result['message'],
            'record' => [
                'inner_order_sn' => $innerOrderSn,
                'status' => $status,
            ],
        ];
        $this->writeAdminLog($logArr);
        return $result;
    }

    public function paramsHandle(array $params, $model)
    {
        /** @var OrdersCollectionTrialModel $model */
        $model->action_type = $params['action_type'];
        $model->problem_source = $params['problem_source'];
        $model->description = $params['description'] ?? '';
    }

};