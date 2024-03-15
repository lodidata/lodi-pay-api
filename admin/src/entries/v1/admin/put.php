<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\AdminRoleModel;
use Model\AdminRoleRelationModel;
use Model\AdminModel;
use Logic\Admin\Log;
use Respect\Validation\Exceptions\DateException;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '账号列表';
    protected $moduleFunName = '修改管理员';

    public function run($id = 0)
    {
        $playLoad = $this->playLoad;
        // 批量验证参数
        $this->checkID($id);
        // 获取请求参数并格式化
        $params = $this->request->getParams(); // 获取全局请求参数
        foreach ($params as $key => $param) {
            if (!empty($param)) $params[$key] = trim($param);
        }
        // 封装数据
        $params['creator_id'] = $playLoad['admin_id'] ?? 0;
        $params['creator_name'] = $playLoad['admin_name'] ?? '';
        // 检查记录是否存在
        /**@var AdminModel $adminModel * */
        $adminModel = AdminModel::query()
            ->where('id', $id)
            ->with(['adminRoleRelation:admin_role.id,role_name,admin_role_relation.id as relation_id'])
            ->first();
        if (!$adminModel) {
            return $this->lang->set(126);
        }
        $adminModel->setAttribute('role_id', $adminModel->adminRoleRelation->id);
        // 检查数据是否发生改变
        $checkChange = $this->checkParamsChange($adminModel, $params);
        if ($checkChange[0] == 0) {
            return $this->lang->set(122);
        }

        $msg = $this->getMsg($checkChange, $adminModel, $params);

        $roleId = $params['role_id'] ?? 0;
        unset($params['role_id'], $params['merchant_id']);

        //如果merchant_id存在值，说明它是商家用户，不允许更新为其他角色
        $canUpdateRole = $adminModel->merchant_id > 0;

        $result = true;
        DB::pdo()->beginTransaction();
        try {
            $res = AdminModel::query()->where('id', $id)->update($params);
            if (!$res)
                throw new DateException(-5);
            if (!empty($roleId) && is_numeric($roleId) && !$canUpdateRole) {
                AdminRoleRelationModel::query()->updateOrCreate(
                    ['id' => $adminModel->adminRoleRelation->relation_id],
                    ['admin_id' => $id, 'role_id' => $roleId]
                );
            }
            DB::pdo()->commit();
        } catch (Exception $e) {
            DB::pdo()->rollBack();
            $result = false;
        }
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'uid2' => $id,
            'uname2' => $adminModel->admin_name,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改管理员：【' . $adminModel->admin_name . '】的信息' . $checkChange[1] . $msg,
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set($result ? 0 : -2);
    }

    /**
     * 获取具体日志
     * @param $checkChange
     * @param AdminModel $adminRoleObj
     * @param $data
     * @return string
     */
    protected function getMsg($checkChange, AdminModel $adminRoleObj, $data): string
    {
        if (!isset($data['role_id'])) {
            return '';
        }
        $newName = AdminRoleModel::query()->where('id', $data['role_id'])->value('role_name');
        $oldName = $adminRoleObj->adminRoleRelation->role_name;
        if ($adminRoleObj->adminRoleRelation->id != $data['role_id']) {
            $checkChange[1] .= '角色由【' . $oldName . '】改为【' . $newName . '】';
        }
        $checkChange[1] = rtrim($checkChange[1], '|');
        return $this->changeLogName(
            $checkChange[1],
            ['real_name' => '姓名', 'position' => '职位', 'department' => '部门', 'role_id' => '角色']
        );
    }
};