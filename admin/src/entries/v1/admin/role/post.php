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
    protected $moduleFunName = '新增角色';

    public function run()
    {
        $params = $this->request->getParams();
        foreach ($params as $key => $param) {
            if (!empty($param)) $params[$key] = trim($param);
        }

        if (empty($params['role_name'])) {
            return $this->lang->set(7);
        }

        // 检查角色名称是否已存在
        $checkRoleRes = AdminRoleModel::query()->where('role_name', $params['role_name'])->exists();
        if ($checkRoleRes)
            return $this->lang->set(127);

        // 封装数据
        $data = [
            'auth' => $params['auth'] ?? '', //权限列表,用逗号(,)隔开
            'role_name' => $params['role_name'], // 菜单名称
            'creator_id' => $this->playLoad['admin_id'],
            'creator_name' => $this->playLoad['admin_name']
        ];

        // 新增货游戏类型
        $result = AdminRoleModel::query()->insertGetId($data);
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】新增角色：【' . $params['role_name'] . '】',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};