<?php

use Lib\Validate\Admin\LoginValidate;
use Logic\Admin\AdminToken;
use Logic\Admin\BaseController;
use Logic\Admin\Log;
use Model\AdminLogModel;
use Model\AdminModel;

return new class() extends BaseController {

    //前置方法
    protected $beforeActionList = [
        'verifyToken',
        'authorize'
    ];

    protected $module = Log::MODULE_USER;
    protected $moduleChild = '用户管理';
    protected $moduleFunName = '修改密码';

    public function run()
    {
        (new LoginValidate)->paramsCheck('pw.patch', $this->request, $this->response);
        $params = $this->request->getParams();

        $user = AdminModel::query()->where('id', $this->playLoad['admin_id'])->first();
        if (!$user) {
            return $this->lang->set(180);
        }

        /**@var AdminModel $user */
        if (!password_verify($params['old_password'], $user->password)) {
            return $this->lang->set(181);
        }
        $user->password = password_hash($params['password'], PASSWORD_DEFAULT);
        $user->save();
        (new AdminToken($this->ci))->remove($user->id);

        $logArr = [
            'status' => AdminLogModel::STATUS_ON,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改密码',
        ];
        $this->writeAdminLog($logArr);
        return $this->lang->set(0);
    }


};
