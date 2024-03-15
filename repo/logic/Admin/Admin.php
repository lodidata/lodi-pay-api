<?php

namespace Logic\Admin;

use Logic\Logic;
use Model\Admin\AdminModel;

class Admin extends Logic{
    /**
     * 用户名、密码匹配
     *
     * @param $user
     * @param $password
     * @return int -1 用户名或密码错误 -2 账号被停用 -3 用户名或密码错误
     */
    public function matchUser($user,$password): int
    {
        $user = AdminModel::where('status', '1')
            ->where('username', $user)
            ->find(1)
            ->toArray();

        if (is_array($user)) {

            if ($user['password'] != md5(md5($password) . $user['salt'])) {
                return $this->lang->set(10046);
            }
        }
        return $user;
    }

}
