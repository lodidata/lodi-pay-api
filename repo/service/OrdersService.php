<?php

namespace Service;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Model\OrdersCollectionModel;
use Model\OrdersPayModel;
use Illuminate\Support\Arr;

class OrdersService
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
     * @throws Exception
     */
    public function dataList(): LengthAwarePaginator
    {
        $where = $this->getCondition();

        $startDate = $endDate = '';
        if (!empty($this->param['created_at'])) {
            $startDate = Carbon::parse($this->param['created_at'][0])->startOfDay()->toDateTimeString();
            $endDate = Carbon::parse($this->param['created_at'][1])->endOfDay()->toDateTimeString();
        }
        $row = OrdersPayModel::query()
            ->with([
                'merchant:id,name,account',
                'user:id,user_account,username',
            ])
            ->where($where)
            ->when($startDate, function ($row) use ($startDate, $endDate) {
                $row->whereBetween('created_at', [$startDate, $endDate]);
            });
        if (!empty($this->playLoad['merchant_id'])) {
            $row->where('merchant_id', $this->playLoad['merchant_id']);
        }
        return $row->select([
            'id',
            'order_sn',
            'inner_order_sn',
            'merchant_id',
            'merchant_account',
            'payment',
            'user_account',
            'currency',
            'status',
            'created_at',
            'user_id',
            'pay_status',
            'amount',
            'match_timeout_amount',
        ])->latest('id')->paginate($this->param['page_size']);
    }

    public function detail($orderId)
    {
        $res = OrdersPayModel::query()
            ->with([
                'matched.merchant',
                'matched.user',
                'matched.attachment',
                'matched.trial:id,orders_collection_sn',
                'merchant:id,name,account',
                'user:id,user_account,username',
            ])
            ->select([
                'id',
                'order_sn',
                'inner_order_sn',
                'payment',
                'user_account',
                'currency',
                'status',
                'created_at',
                'amount',
                'merchant_id',
                'user_id',
            ])->latest()
            ->findOrFail($orderId);
        /**@var OrdersPayModel $res * */
        if ($res->matched->isEmpty()) {
            return $res;
        }
        $orderStatusArr = [
            OrdersCollectionModel::$statusArr['pre_match']['value'],
            OrdersCollectionModel::$statusArr['pre_upload_ticket']['value'],
            OrdersCollectionModel::$statusArr['complete']['value'],
            OrdersCollectionModel::$statusArr['canceled']['value'],
        ];
        foreach ($res->matched as $v) {
            /**@var OrdersCollectionModel $v * */
            $showStatus = 1; //展示
            $orderType = $v->order_type ?? 0;
            $orderStatus = $v->status ?? 0;
            if ($orderType == 2 ||
                ($orderType == 1 && in_array($orderStatus, $orderStatusArr)) ||
                $v->trial->isNotEmpty() ||
                $orderType == 3) {
                //不展示
                $showStatus = 0;
            }
            $v->setAttribute('show_status', $showStatus);
        }
        return $res;
    }


    /**
     * 获取条件
     * @return array
     * @throws Exception
     */
    public function getCondition(): array
    {
        $where = [];

        if (!empty($this->param['merchant_id'])) {
            $where[] = ['merchant_id', '=', $this->param['merchant_id']];
        }

        if (!empty($this->param['merchant_account'])) {
            $where[] = ['merchant_account', 'like', '%' . $this->param['merchant_account'] . '%'];
        }

        if (!empty($this->param['user_account'])) {
            $where[] = ['user_account', 'like', '%' . $this->param['user_account'] . '%'];
        }

        //提款订单=我们平台自己的账号
        if (!empty($this->param['inner_order_sn'])) {
            $where[] = ['inner_order_sn', '=', $this->param['inner_order_sn']];
        }
        $status = Arr::pluck(OrdersPayModel::$status, 'message', 'value');
        //订单状态
        if (isset($this->param['status']) && in_array($this->param['status'], array_keys($status))) {
            $where[] = ['status', '=', $this->param['status']];
        }

        //站点订单
        if (!empty($this->param['order_sn'])) {
            $where[] = ['order_sn', '=', $this->param['order_sn']];
        }

        return $where;
    }
}