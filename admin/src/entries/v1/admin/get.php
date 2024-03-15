<?php

use Model\AdminModel;
use Logic\Admin\BaseController;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        $params = $this->request->getParams();
        // 查询
        $adminObj = AdminModel::query()
            ->where('id', '>', AdminModel::SUPER_ADMIN_ID)
            ->when($params['admin_name'] ?? '', function ($adminObj) use ($params) {
                $adminObj->where('admin_name', 'like', $params['admin_name'] . '%');
            })
            ->when(isset($params['status']), function ($adminObj) use ($params) {
                if (in_array($params['status'], array_keys(AdminModel::STATUS_ARR))) {
                    $adminObj->where('status', $params['status']);
                }
            })
            ->with([
                'adminRoleRelation:admin_role.id,role_name'
            ]);
        $adminList = $adminObj->latest()->paginate($this->pageSize);
        return $this->lang->set(0, [], $adminList->items(), ['total' => $adminList->total()]);
    }
};