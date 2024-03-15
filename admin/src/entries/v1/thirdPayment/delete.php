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
    protected $moduleFunName = '删除';

    public function run($id)
    {
        $row = PayConfigModel::query()->findOrFail($id);

        $result = $row->delete();
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => [$id],
            'remark' => '【' . $this->playLoad['admin_name'] . '】删除第三方代付,id为【' . $id . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }


};