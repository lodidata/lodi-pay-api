<?php

use Model\AdminLogModel;
use Model\AdminRoleAuthModel;
use Logic\Admin\BaseController;
use Logic\Admin\Log;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '权限列表';
    protected $moduleFunName = '删除权限';

    public function run($id = 0)
    {
        // 检查id是否合法
        $this->checkID($id);
        // 检查记录是否存在
        $checkRes = AdminRoleAuthModel::query()->where('id', $id)->first();
        if (!$checkRes)
            return $this->lang->set(126);

        AdminRoleAuthModel::query()->where('id', $id)->delete();

        $logArr = [
            'status' => AdminLogModel::STATUS_ON,
            'record' => [$id],
            'uid2' => 0,
            'remark' => '【' . $this->playLoad['admin_name'] . '】删除权限：【' . $checkRes->auth_name . '】',
        ];
        $this->writeAdminLog($logArr);

        // 响应数据
        return $this->lang->set(0);
    }
};
