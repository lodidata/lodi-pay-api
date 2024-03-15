<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\AdminModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_SYS;
    protected $moduleChild = '账号列表';
    protected $moduleFunName = '修改密码';

    public function run($id = 0)
    {
        $this->checkID($id);
        $params = $this->request->getParams();
        foreach ($params as $key => $param) {
            if (!empty($param)) $params[$key] = trim($param);
        }
        /**@var AdminModel $adminObj * */
        $adminObj = AdminModel::query()->where('id', $id)->first();
        if (!$adminObj) {
            return $this->lang->set(9);
        }
        // 校验账户密码
        if (md5($params['password']) != md5($params['password_confirm'])) {
            return $this->lang->set(124);
        } else {
            $password = password_hash($params['password'], PASSWORD_DEFAULT);
        }

        if (!$password) {
            return $this->lang->set(143);
        }

        $res = AdminModel::query()->where('id', $id)->update(['password' => $password]);
        $logArr = [
            'status' => $res ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'uid2' => $id,
            'uname2' => $adminObj->admin_name,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改管理员：【' . $adminObj->admin_name . '】的密码',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($res ? 0 : -2);
    }
};