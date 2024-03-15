<?php

use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Model\AdminLogModel;
use Model\PayConfigModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_THIRD_PAYMENT;
    protected $moduleChild = '第三方代付';
    protected $moduleFunName = '添加';

    public function run()
    {
        $params = $this->request->getParams();
        $isExist = PayConfigModel::query()
            ->where([
                'type' => $params['type'],
                'merchant_account' => $params['merchant_account'],
            ])
            ->first();
        if ($isExist) {
            throw new Exception($this->lang->text(170));
        }

        $data = $this->paramsHandle($params);
        $result = PayConfigModel::query()->create($data);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】添加第三方代付,名称为【' . $params['name'] . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }

    private function paramsHandle(array $params): array
    {
        $params['sort'] = isset($params['sort']) && intval($params['sort']) > 0 ? $params['sort'] : 0;
        return $params;
    }


};