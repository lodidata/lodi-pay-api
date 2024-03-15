<?php

use Logic\Admin\BaseController;
use Model\UserModel;

//用户统计
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        $total = [];
        //平台用户总数
        $total['userTotal'] = DB::table( 'user' )->count('id');

        //可内充用户数
        $total['userCanCharge'] = DB::table( 'user' )
            ->leftJoin('user_tag','user.id','=','user_tag.user_id')
            ->leftJoin('tag','user_tag.tag_id','=','tag.id')
            ->where(['name' => '可内充'])
            ->count('user.id');

        //正常用户数
        $total['userNomal'] = DB::table( 'user' )->where(['status' => UserModel::STATUS_ON])->count('id');

        //禁用用户数 = 平台用户总数 - 正常用户数;
        $diff = $total['userTotal'] - $total['userNomal'];
        $total['userDisable'] = max($diff, 0);

        //今日新增用户数
        $today = date('Y-m-d').' 00:00:01';
        $total['newUserToday'] = DB::table( 'user' )->where('created_at','>=', $today)->count('id');

        //已接入站点数
        $total['merchantTotal'] = DB::table( 'merchant' )->whereNull('deleted_at')->count('id');

        return $this->lang->set( 0, [], $total);
    }
};