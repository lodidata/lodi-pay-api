<?php

use Logic\Admin\BaseController;
use Lib\Validate\Admin\AdminRoleValidate;
use Model\AdminLogModel;
use Model\AdminRoleModel;
use Model\AdminModel;
use Logic\Admin\Log;
use Respect\Validation\Exceptions\DateException;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '角色列表';
    protected $moduleFunName = '修改角色';

    public function run($id = 0)
    {
        // 校验id并获取表明
        $this->checkID($id);
        $params = $this->request->getParams();
        foreach ($params as $key => $param) {
            if (!empty($param)) $params[$key] = trim($param);
        }

        // 检查该记录是否存在
        $adminRoleObj = AdminRoleModel::query()->where('id', $id)->first();
        if (!$adminRoleObj)
            return $this->lang->set(126);
        // 检查数据是否发生改变
        $checkChange = $this->checkParamsChange($adminRoleObj, $params);
        if ($checkChange == 0)
            return $this->lang->set(122);
        // 封装请求参数
        $data = [
            'role_name' => $params['role_name'] ?? '',
            'auth' => $params['auth'] ?? '',
            'creator_id' => $this->playLoad['admin_id'],
            'creator_name' => $this->playLoad['admin_name']
        ];

        $result = AdminRoleModel::query()->where('id', $id)->update($data);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改角色：【' . $params['role_name'] . '】的信息',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};