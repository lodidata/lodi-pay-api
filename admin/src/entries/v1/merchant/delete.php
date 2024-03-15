<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\MerchantModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_MERCHANT;
    protected $moduleChild = '商户列表';
    protected $moduleFunName = '删除商户';

    public function run($id)
    {
        /**@var MerchantModel $row * */
        $row = MerchantModel::query()->findOrFail($id);
        $result = $row->delete();
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => [$id],
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】删除商户：【' . $row->name . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};