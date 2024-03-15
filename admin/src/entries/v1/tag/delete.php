<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\TagModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_USER;
    protected $moduleChild = '标签管理';
    protected $moduleFunName = '删除标签';

    public function run($id)
    {
        /**@var TagModel $row * */
        $row = TagModel::query()->findOrFail($id);
        $result = true;
        DB::pdo()->beginTransaction();
        try {
            //删除赋予用户的标签
            $row->userTag()->delete();
            //删除
            $row->delete();
            DB::pdo()->commit();
        } catch (Exception $e) {
            DB::pdo()->rollBack();
            $result = false;
        }

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => [$id],
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】删除标签【' . $row->name . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};