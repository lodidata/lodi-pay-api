<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\TagModel;
use Model\AdminModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_USER;
    protected $moduleChild = '标签管理';
    protected $moduleFunName = '修改标签';

    public function run($id)
    {
        $params = $this->request->getParams();
        /**@var TagModel $row * */
        $row = TagModel::query()->findOrFail($id);
        $result = $row->update($params);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改标签【' . $row->name . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};