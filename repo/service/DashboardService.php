<?php

namespace Service;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Model\OrdersCollectionModel;
use Model\OrdersCollectionTrialModel;
use Model\OrdersPayModel;

class DashboardService
{
    public $param = [];

    /**
     * 接收参数
     * @param array $param
     */
    public function __construct(array $param)
    {
        $this->param = $param;
    }

    /**
     * 获取代充、代付统计
     * @param $table
     * @return array
     */
    public function dataCount($table): array
    {
        $where = $this->conditions();
        $order = DB::table($table);
        $getTables = (new OrdersPayModel())->getTable();
        if (!empty($where)) {
            $order = $order->whereBetween('created_at', $where);
        }
        //代充订单数
        $data['orderNum'] = $order->count('id');

        //代充订单金额
        $amount = $order->sum('amount');//代付总金额
        $data['orderMoney'] = $amount;

        //匹配成功订单数
        $data['orderNumSuc'] = $order->where(['status' => 6])->count();
        //匹配成功金额
        $amountNum = $order->sum('amount');
        if ($table != $getTables) {//代充
            $data['orderNumMoney'] = $amountNum;
        } else {//提款（代付）
            $balanceNum = $order->get('inner_order_sn')->toArray();//代付所有的订单号
            $data['orderNumMoney'] = 0;
            if (!empty($balanceNum)) {
                $sn = [];
                foreach ($balanceNum as $v) {
                    $sn[] = $v->inner_order_sn;
                }
                $match_amount = OrdersCollectionModel::query()
                    ->whereIn('pay_inner_order_sn', $sn)
                    ->whereNotIn('status', [
                        OrdersCollectionModel::$statusArr['pre_match']['value'],
                        OrdersCollectionModel::$statusArr['canceled']['value'],
                        OrdersCollectionModel::$statusArr['failed']['value'],
                        OrdersCollectionModel::$statusArr['order_fail']['value']])
                    ->sum('amount');
                $data['orderNumMoney'] = sprintf('%.2f', $match_amount);
            }
        }
        return $data;
    }

    /**
     * 争议订单统计
     * @return array
     */
    public function disputeCount(): array
    {
        $actionTypes = OrdersCollectionTrialModel::$actionType;
        $types = [Arr::get($actionTypes, 'pre_trial.value'),
            Arr::get($actionTypes, 'failed.value'),
            Arr::get($actionTypes, 'success.value')];
        $where = $this->conditions();
        $getTrialData = $this->getTrialData($where, $types);

        //争议订单数
        $data['disputeNum'] = $getTrialData[0]->handleNum ?? 0;

        //争议订单金额
        $data['disputeMoney'] = $getTrialData[0]->handleMoney ?? 0;

        //已处理订单数组
        $hasHandle = $this->getHandleData($where, [$types[1], $types[2]]);

        //已处理订单金额
        $data['handleMoney'] = $hasHandle[0]->handleMoney ?? 0;

        //已处理订单数
        $data['handleNum'] = $hasHandle[0]->handleNum ?? 0;

        //未处理订单数组
        $unhandledArr = $this->getHandleData($where, [$types[0]]);

        //未处理订单金额
        $data['unhandleMoney'] = $unhandledArr[0]->handleMoney ?? 0;

        //未处理订单数
        $data['unhandleNum'] = $unhandledArr[0]->handleNum ?? 0;

        return $data;
    }

    /**
     * 争议订单数和金额
     * @param $where
     * @param $types
     * @return Collection
     */
    public function getTrialData($where, $types): Collection
    {
        $params = $this->param;
        $obj = DB::table('orders_collection')
            ->whereIn('inner_order_sn', function ($query) use ($where, $params, $types) {
                $query->select('orders_collection_sn')
                    ->from('orders_collection_trial')
                    ->whereIn('action_type', $types)
                    ->groupBy(['orders_collection_sn'])
                    ->get();
            });
        if (!empty($where) && $params['type'] != 1) {
            $obj = $obj->whereBetween('created_at', $where);
        }
        $obj->where('pay_inner_order_sn', '!=', '');
        return $obj->selectRaw('sum(amount) as handleMoney, count(id) as handleNum')->get();
    }

    /**
     * 已处理和未处理订单数和金额
     * @param $where
     * @param $type
     * @return Collection
     */
    public function getHandleData($where, $type): Collection
    {
        $params = $this->param;
        return DB::table('orders_collection')
            ->whereIn('inner_order_sn', function ($query) use ($where, $params, $type) {
                $query = $query->select('orders_collection_sn')
                    ->from('orders_collection_trial')
                    ->whereIn('action_type', $type);
                if (!empty($where) && $params['type'] != 1) {
                    $query = $query->whereBetween('created_at', $where);
                }
                $query->groupBy(['orders_collection_sn']);
            })
            ->where('pay_inner_order_sn', '!=', '')
            ->selectRaw('sum(amount) as handleMoney, count(id) as handleNum')->get();
    }

    /**
     * 时间条件
     * @return void
     */
    public function conditions(): array
    {
        $time = [];
        //时间查询类型：0-所有日期，1-最近1小时，2-今天，3-昨天，4-前天，5-本周，6-上周
        $type = $this->param['type'] ?? 0;
        $end = date("Y-m-d H:i:s");
        switch (intval($type)) {
            case 1:
                $start = Carbon::parse()->subHour()->toDateTimeString();
                $time = [$start, $end];
                break;
            case 2:
                $start = Carbon::parse()->startOfDay()->toDateTimeString();
                $end = Carbon::parse()->endOfDay()->toDateTimeString();
                $time = [$start, $end];
                break;
            case 3:
                $start = Carbon::parse()->subDay()->startOfDay()->toDateTimeString();
                $end = Carbon::parse()->subDay()->endOfDay()->toDateTimeString();
                $time = [$start, $end];
                break;
            case 4:
                $start = Carbon::parse()->subDays(2)->startOfDay()->toDateTimeString();
                $end = Carbon::parse()->subDays(2)->endOfDay()->toDateTimeString();
                $time = [$start, $end];
                break;
            case 5:
                $start = Carbon::parse()->startOfWeek()->toDateTimeString();
                $end = Carbon::parse()->endOfWeek()->toDateTimeString();
                $time = [$start, $end];
                break;
            case 6:
                $start = Carbon::parse()->subWeek()->startOfWeek()->toDateTimeString();
                $end = Carbon::parse()->subWeek()->endOfWeek()->toDateTimeString();
                $time = [$start, $end];
                break;
        }
        return $time;
    }


}