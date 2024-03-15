<?php

use Model\AdminLogModel;
use Model\AdminModel;
use Logic\Admin\AdminToken;
use Logic\Admin\BaseController;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '账号列表';
    protected $moduleFunName = '修改账号状态';

    public function run($id = 0)
    {
        // 校验id是否合法
        $this->checkID($id);
        /**@var AdminModel $adminModel * */
        $adminModel = AdminModel::query()->where('id', $id)->first();
        if (!$adminModel)
            return $this->lang->set(9);
        else
            $admin = $adminModel->toArray();

        // 封装状态和开关字符信息
        $status = $admin['status'] == AdminModel::STATUS_ON ? AdminModel::STATUS_OFF : AdminModel::STATUS_ON;
        $msg = $status == AdminModel::STATUS_OFF ? '【启用】改为【停用】' : '【停用】改为【启用】';

        $result = AdminModel::query()->where('id', $id)->update(['status' => $status]);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => [$id],
            'uid2' => $id,
            'uname2' => $adminModel->admin_name,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改账号：【' . $adminModel->admin_name . '】的状态由' . $msg,
        ];
        $this->writeAdminLog($logArr);

        // 更新状态成功且修改状态为禁用后移除token
        if ($adminModel->status == AdminModel::STATUS_OFF)
            (new AdminToken($this->ci))->remove($id);
        
        return $this->lang->set($result ? 0 : -2);
    }
};