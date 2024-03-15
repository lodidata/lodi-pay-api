<?php

use Logic\Admin\BaseController;
use Logic\PayAdmin\Api;
use Model\AdminLogModel;
use Model\SettingsModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '系统配置';
    protected $moduleFunName = '编辑系统配置';

    public function run($id)
    {
        $params = $this->request->getParams();
        $row = SettingsModel::query()->findOrFail($id);
        /**@var SettingsModel $row */
        $this->paramsHandle($params, $row);
        $result = $row->save();

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】编辑系统配置【' . $row->name . '】信息',
        ];
        $this->writeAdminLog($logArr);

        $this->adminConfigSync();
        return $this->lang->set($result ? 0 : -2);
    }

    private function paramsHandle(array $params, $model)
    {
        /** @var SettingsModel $model */
        $model->default_config = $params['content'] ?? '';
        $model->key = $params['key'] ?? '';
        $model->info = $params['description'] ?? '';
        $model->name = $params['name'] ?? '';
    }

    private function adminConfigSync()
    {
        $result = (new Api($this->ci))->adminConfigSync();
        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】同步系统配置，请求结果信息为：' . $result['message'],
            'record' => [],
        ];
        $this->writeAdminLog($logArr);
    }

};