<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\AdminRoleAuthModel;
use Logic\Admin\Log;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '权限列表';
    protected $moduleFunName = '新增权限';

    public function run()
    {
        $params = $this->request->getParams();
        foreach ($params as $key => $param) {
            if (!empty($param)) $params[$key] = trim($param);
            if ($key === 'method' && !empty($params['method'])) $params['method'] = strtoupper($params[$key]);
        }

        $result = AdminRoleAuthModel::query()->insertGetId($params);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】新增权限：【' . $params['auth_name'] . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};