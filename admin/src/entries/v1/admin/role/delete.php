<?php

use Model\AdminLogModel;
use Model\AdminRoleModel;
use Logic\Admin\BaseController;
use Logic\Admin\Log;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '角色列表';
    protected $moduleFunName = '删除角色';

    public function run($id = 0)
    {
        // 检查id是否合法
        $this->checkID($id);
        // 检查记录是否存在
        $checkRes = AdminRoleModel::query()->where('id', $id)->first();
        if (!$checkRes)
            return $this->lang->set(126);

        $result = AdminRoleModel::query()->where('id', $id)->delete();
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => [$id],
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】删除角色：【' . $checkRes->role_name . '】',
        ];
        $this->writeAdminLog($logArr);

        // 响应数据
        return $this->lang->set(0);
    }
};
