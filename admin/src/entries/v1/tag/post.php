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
    protected $moduleFunName = '新增标签';

    public function run()
    {
        $params = $this->request->getParams();
        $params['admin_id'] = $this->playLoad['admin_id'];
        $result = TagModel::query()->create($params);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】新增标签【' . $params['name'] . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};