<?php

use Logic\Admin\BaseController;
use Logic\PayAdmin\Api;
use Model\AdminLogModel;
use Logic\Admin\Log;
use Model\PayConfigModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_THIRD_PAYMENT;
    protected $moduleChild = '第三方代付';
    protected $moduleFunName = '更新';

    public function run($id)
    {
        $params = $this->request->getParams();
        $row = PayConfigModel::query()->findOrFail($id);
        $isExist = PayConfigModel::query()
            ->where([
                'type' => $params['type'],
                'merchant_account' => $params['merchant_account'],
                ['id', '<>', $id],
            ])
            ->first();
        if ($isExist) {
            throw new Exception($this->lang->text(170));
        }
        $merchantAccount = $params['merchant_account'];
        $params = $this->paramsHandle($params);
        $result = $row->update($params);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】更新第三方代付,id为【' . $id . '】',
        ];
        $this->writeAdminLog($logArr);

        $this->payConfigSync(['merchant_account' => $merchantAccount]);
        return $this->lang->set($result ? 0 : -2);
    }

    private function paramsHandle(array $params): array
    {
        unset($params['partner_id'], $params['merchant_account']);
        return $params;
    }

    private function payConfigSync(array $data)
    {
        $result = (new Api($this->ci))->payConfigSync($data);
        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】同步第三方支付配置，请求结果信息为：' . $result['message'],
            'record' => $data,
        ];
        $this->writeAdminLog($logArr);
    }


};