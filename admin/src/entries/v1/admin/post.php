<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\AdminRoleModel;
use Model\AdminRoleRelationModel;
use Model\AdminModel;
use Respect\Validation\Exceptions\DateException;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '账号列表';
    protected $moduleFunName = '新增管理员';

    public function run()
    {
        $playLoad = $this->playLoad;

        $params = $this->request->getParams();
        $roleId = $params['role_id'] ?? 0;
        //如果是商户账户类型，要做验证它的角色权限，必须是商户角色
        if ($params['user_type'] == AdminModel::USER_TYPE_BUSINESS) {
            $roleId = AdminRoleModel::query()->where('role_name', AdminRoleModel::ROLE_BUSINESS)->value('id');
            if (!$roleId) {
                return $this->lang->set(190);
            }
        }
        $params = $this->paramsHandle($this->request->getParams(), $playLoad);

        $result = true;
        DB::pdo()->beginTransaction();
        try {
            // 更新记录
            unset($params['role_id']);
            $id = AdminModel::query()->insertGetId($params);
            if (!$id)
                throw new DateException(-5);
            // 更新角色
            if (!empty($roleId)) {
                $checkR = AdminRoleRelationModel::query()->where('admin_id', $id)->where('role_id', $roleId)->exists();
                if (!$checkR) {
                    $rid = AdminRoleRelationModel::query()->insertGetId(['admin_id' => $id, 'role_id' => $roleId]);
                    if (!$rid)
                        throw new DateException(140);
                }
            }
            DB::pdo()->commit();
        } catch (Exception $e) {
            DB::pdo()->rollBack();
            $result = false;
        }

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'uid2' => $id ?? 0,
            'uname2' => $params['admin_name'],
            'remark' => '【' . $playLoad['admin_name'] . '】新增管理员：【' . $params['admin_name'] . '】',
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set($result ? 0 : -2);
    }

    private function paramsHandle(array $params, array $playLoad): array
    {
        if ($params['user_type'] == AdminModel::USER_TYPE_ADMIN) {
            unset($params['merchant_id']);
        }
        unset($params['password_confirm'], $params['user_type']);
        $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        $params['creator_id'] = $playLoad['admin_id'] ?? 0;
        $params['creator_name'] = $playLoad['admin_name'] ?? '';
        return $params;
    }

};