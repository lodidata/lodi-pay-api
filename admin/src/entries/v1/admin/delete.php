<?php

use Model\AdminLogModel;
use Model\AdminModel;
use Logic\Admin\Log;
use Logic\Admin\AdminToken;
use Logic\Admin\BaseController;
use Model\AdminRoleRelationModel;
use Respect\Validation\Exceptions\DateException;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '账号列表';
    protected $moduleFunName = '管理员删除';

    public function run($id = 0)
    {

        $this->checkID($id);
        $adminModel = AdminModel::find($id);
        if (!$adminModel)
            return $this->lang->set(9);

        $result = true;
        DB::pdo()->beginTransaction();
        try {
            // 删除token
            (new AdminToken($this->ci))->remove($id);
            // 删除账户
            $delRes = $adminModel->delete();
            if (!$delRes)
                throw new DateException(141);
            // 删除账户角色关系记录
            $checkR = AdminRoleRelationModel::query()->where('admin_id', $id)->exists();
            if ($checkR) {
                $delR = AdminRoleRelationModel::query()->where('admin_id', $id)->delete();
                if (!$delR)
                    throw new DateException(142);
            }

            DB::pdo()->commit();
        } catch (Exception $e) {
            DB::pdo()->rollBack();
            $result = false;
        }

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => [$id],
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】删除管理员：【' . $adminModel->admin_name . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};