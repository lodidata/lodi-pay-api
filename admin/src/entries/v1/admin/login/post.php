<?php

use Logic\Admin\BaseController;
use Logic\Admin\AdminToken;
use Logic\Admin\Log;
use Model\AdminLogModel;

return new class extends BaseController {

    protected $module = Log::MODULE_LOGIN;
    protected $moduleChild = '用户登录';
    protected $moduleFunName = '用户登录';

    public function run()
    {
        $params = $this->request->getParams();
        foreach ($params as $key => $param) {
            if (!empty($param)) $params[$key] = trim($param);
        }
        $jsonWebToken = $this->ci->get('settings')['jsonwebtoken'];
        $digital = intval($jsonWebToken['uid_digital']);
        $jwt = new AdminToken($this->ci);
        $res = $jwt->createToken($params, $jsonWebToken['public_key'], $jsonWebToken['expire'], $digital);
        $status = $res->state == 1 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF;
        //记录日志
        $logArr = [
            'status' => $status,
            'record' => $params,
            'remark' => '【' . $params['admin_name'] . '】登录系统',
        ];
        $this->writeAdminLog($logArr);

        return $res;
    }
};
