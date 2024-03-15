<?php

use Model\AdminLogModel;
use Model\AdminRoleAuthModel;
use Logic\Admin\BaseController;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '权限列表';
    protected $moduleFunName = '修改权限';

    public function run($id = 0)
    {

        $this->checkID($id);
        // 获取请求参数
        $params = $this->request->getParams();
        foreach ($params as $key => $param) {
            if (!empty($param)) $params[$key] = trim($param);
        }
        // 检查该记录是否存在
        $adminRoleAuthObj = AdminRoleAuthModel::query()->where('id', $id)->first();
        if (!$adminRoleAuthObj)
            return $this->lang->set(126);

        // 检查数据是否发生改变
        $checkChange = $this->checkParamsChange($adminRoleAuthObj, $params);
        if ($checkChange == 0)
            return $this->lang->set(122);

        $result = AdminRoleAuthModel::query()->where('id', $id)->update($params);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改权限：【' . $params['auth_name'] . '】的信息',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};