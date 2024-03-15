<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\UserModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_USER;
    protected $moduleChild = '用户管理';
    protected $moduleFunName = '删除用户';

    public function run($id)
    {
        /**@var UserModel $row * */
        $row = UserModel::query()->findOrFail($id);
        $result = true;
        DB::pdo()->beginTransaction();
        try {
            //tags 删除
            $row->tagRelation()->delete();
            //用户软删除
            $row->delete();

            Db::pdo()->commit();
        } catch (Exception $e) {
            Db::pdo()->rollBack();
            $result = false;
        }
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => [$id],
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】删除用户【' . $row->username . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};