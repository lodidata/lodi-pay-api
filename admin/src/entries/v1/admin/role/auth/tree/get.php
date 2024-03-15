<?php

use Logic\Admin\BaseController;
use Model\AdminRoleModel;
use Model\AdminRoleAuthModel;

return new class() extends BaseController {
    //前置方法
//    protected $beforeActionList = [
//        'verifyToken', 'authorize'
//    ];

    public function run($id = 0): array
    {
        // 用户角色权限表
        $adminRoleAuths = AdminRoleAuthModel::query()->orderBy( 'sort', 'Asc' )->get( ['id', 'pid', 'auth_name AS title'] )->toArray() ?? [];
        // 角色表
        $adminRoleObj = AdminRoleModel::query()->where( 'id', $id )->first( ['auth', 'member_control'] );
        if ($adminRoleObj)
            $adminRole = $adminRoleObj->toArray();
        else
            $adminRole = ['auth' => '', 'member_control' => ''];
        // 格式化角色权限
        $adminRoleAuths = \Utils\PHPTree::makeTree( $adminRoleAuths, [], explode( ',', $adminRole['auth'] ) );
        // 会员真实姓名，会员银行信息，会员联系信息
        $tmp = json_decode( $adminRole['member_control'], true ) ?? [];
        $memberControl = array_merge( ["true_name" => false, "bank_card" => false, "address_book" => false, "user_search_switch" => false], $tmp );

        return ['tree' => $adminRoleAuths, 'user' => $memberControl, 'user_search_switch' => $memberControl['user_search_switch'] ?? false];
    }
};