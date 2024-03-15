<?php

namespace Logic\Admin;

use DB;
use Logic\Logic;
use Utils\Client;
use Model\AdminLogModel;
use Model\AdminModel;

class Log extends Logic
{
    const METHOD_DESCRIPTION = [
        'GET' => '获取',
        'POST' => '创建',
        'PUT' => '修改',
        'PATCH' => '修改',
        'DELETE' => '删除'
    ];

    const METHOD_WRITE_LOG = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    const MODULE_NAME = [
        '商户列表',
        '系统管理',
        '订单管理',
        '用户管理',
        '财务报表',
        '用户登录',
        '第三方代付',
    ];

    const MODULE_MERCHANT = 0;
    const MODULE_SYS = 1;
    const MODULE_ORDER = 2;
    const MODULE_USER = 3;
    const MODULE_BALANCE = 4;
    const MODULE_LOGIN = 5;
    const MODULE_THIRD_PAYMENT = 6;

    /**
     * TODO 新增操作记录
     *
     * @param int $adminId2 :被操作用户ID
     * @param string $uname2 :被操作用户名
     * @param int $module :模块
     * @param string $module_child :子模块
     * @param string $fun_name :功能名称
     * @param string $type :操作类型
     * @param int $status : 操作状态 0：失败 1：成功
     * @param string $remark :详情记录
     *
     * @return int
     */
    public function create(int $adminId2, string $uname2, int $module, string $module_child, string $fun_name, string $type, int $status, string $remark): int
    {
        global $playLoad;
        $data = [
            'ip' => $playLoad['ip'] ?: Client::getIp(),
            'admin_id' => $playLoad['admin_id'] ?? 0,
            'uname' => $playLoad['admin_name'] ?? '',
            'admin_id2' => $adminId2,
            'uname2' => $uname2,
            'auth' => self::MODULE_NAME[$module],
            'module_child' => $module_child,
            'fun_name' => $fun_name,
            'type' => $type,
            'status' => $status,
            'remark' => $remark,
        ];

        return DB::table('admin_log')->insertGetId($data);
    }

    /**
     * TODO 写入log
     *
     * @param string $method 方法名
     * @param int $target_uid 操作会员(可为空)
     * @param string $target_nick :操作目标(可为空)
     * @param int $module_type :子模块类型
     * @param string $module_child :子模块(例如：系统设置)
     * @param string $fun_name :调用方法(例如：登录注册)
     * @param string $remark :详细记录 如：修改xxx
     * @return int
     */
    public function log(string $method, int $target_uid, string $target_nick, int $module_type, string $module_child, string $fun_name, string $remark): int
    {
        global $playLoad;
        $data = [
            'ip' => $playLoad['ip'] ?: Client::getIp(),
            'admin_id' => 104,
            'uname' => '测试',
            'admin_id2' => $target_uid,
            'uname2' => $target_nick,
            'auth' => $module_type,
            'module_child' => $module_child,
            'fun_name' => $fun_name,
            'type' => self::METHOD_DESCRIPTION[$method],// 根据不同方法判断
            'status' => 1,
            'remark' => $remark,
        ];

        return DB::table('admin_log')->insertGetId($data);
    }

    /**
     * @param array $data
     * @return int
     */
    public function write(array $data): int
    {
        // 校验请求类型
        if (!$data || !in_array($data['method'], self::METHOD_WRITE_LOG))
            return false;

        //获取被操作用户的姓名
        if (!empty($data['uid2']) && empty($data['uname2'])) {
            $data['uname2'] = AdminModel::query()->where('id', $data['uid2'])->value('nick_name') ?? '';
        }
        if ($data['module'] == self::MODULE_LOGIN && empty($data['admin_name']) && !empty($data['record']['admin_name'])) {
            $data['admin_name'] = $data['record']['admin_name'];
        }

        $data['method'] = self::METHOD_DESCRIPTION[$data['method']] ?? '未知';
        $data['record'] = is_string($data['record']) ? $data['record'] : json_encode($data['record'], 320);
        $data['module'] = self::MODULE_NAME[$data['module']] ?? '未知';
        return AdminLogModel::query()->insertGetId($data);
    }

    /**
     * 钱包充值记录
     * @param $data
     * @return int
     */
    public function balanceLog($data): int
    {
        $data = [
            'merchant_account' => $data['merchant_account'] ?? 0,
            'transaction_type' => $data['transaction_type'] ?? 1,//'交易类型：1=充值，2=提现，3=点位扣除金额, 4=余额手动划转，5-余额自动划转'
            'order_type' => $data['order_type'] ?? 1,//订单类型：1=充值，2=提现
            'order_sn' => $data['order_sn'] ?? '',//交易订单号
            'change_after' => $data['change_after'] ?? '0.00',//余额变动之后
            'change_before' => $data['change_before'] ?? '',//余额变动之前
            'remark' => $data['remark'] ?? '',//备注
            'admin_id' => $data['admin_id'] ?? 0,//操作人id
            'currency' => $data['currency'] ?? '',//币种
        ];
        return DB::table('merchant_balance_change_log')->insertGetId($data);
    }
}