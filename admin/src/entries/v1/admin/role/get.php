<?php

use Model\AdminRoleModel;
use Logic\Admin\BaseController;
use Lib\Validate\Admin\AdminRoleValidate;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        // 批量更新请求参数
        //(new AdminRoleValidate())->paramsCheck( 'get', $this->request, $this->response );
        $params = $this->request->getParams();
        foreach ($params as $key => $param) {
            if (!empty( $param )) $params[$key] = trim( $param );
            if ($key === 'page' && (!is_numeric( $param ) || $param <= 0)) $params[$key] = $this->page;
            if ($key === 'page_size' && (!is_numeric( $param ) || $param <= 0)) $params[$key] = $this->pageSize;
        }
        // 分页参数
        $common = ['page' => $params['page'], 'page_size' => $params['page_size']];
        // 查询
        $joinObj = DB::table( 'admin_role' )
            ->leftjoin( 'admin', 'admin.id', '=', 'admin_role.creator_id' )
            ->selectRaw( 'admin_role.id,admin_role.role_name,admin_role.auth,admin_role.created_at,admin.admin_name' );
        // 查询条件
        !empty( $params['admin_id'] ) && $joinObj->where( 'admin_role.id', $params['admin_id'] );
        !empty( $params['role_name'] ) && $joinObj->where( 'admin_role.role_name', trim( $params['role_name'] ) );
        // 计数
        $common['total'] = $joinObj->count() ?? 0;
        // 获取排序分页列表
        $joinList = $joinObj->orderBy( 'admin_role.id', 'desc' )->forPage( $common['page'], $common['page_size'] )->get()->toArray();
        $map = [];
        $roleId = \array_column($joinList ,'id');
        //角色人数
        $roleUserCount = \Model\AdminRoleRelationModel::query()->whereIn('role_id' ,$roleId)->select(['role_id' ,DB::raw("count(*) as count")])->groupBy(['role_id'])->get()->keyBy('role_id')->toArray();
        foreach ($joinList as $key => $item) {
            $tmp = [];
            $tmp['id'] = $item->id;
            $tmp['role_name'] = trim( $item->role_name );
            $tmp['auth'] = trim( $item->auth );
            $tmp['admin_name'] = trim( $item->admin_name );
            $tmp['created_at'] = $item->created_at;
            $tmp['admin_role_count'] = isset($roleUserCount[$item->id]) ? $roleUserCount[$item->id]['count'] : 0;
            $map[] = $tmp;
        }

        return $this->lang->set( 0, [], $map, $common );
    }
};