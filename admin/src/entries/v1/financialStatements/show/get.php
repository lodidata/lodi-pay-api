<?php

use Model\OrdersPayModel;
use Model\PayConfigModel;
use Model\TransferRecordModel;
use Model\OrdersCollectionModel;
use Logic\Admin\BaseController;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run($id): array
    {
        $params = $this->request->getParams();

        $timeWhere = [];
        if (!empty($params['created_at'][0]) && !empty($params['created_at'][1])) {
            $timeWhere = [$params['created_at'][0], $params['created_at'][1]];
        }

        $orderStatus = OrdersCollectionModel::$statusArr['complete']['value'];
        $merchantId = !empty($id) ? intval($id) : '';
        $ordersCollection = OrdersCollectionModel::query()
            ->Join('orders_pay', 'orders_pay.inner_order_sn', '=', 'orders_collection.pay_inner_order_sn')
            ->where([
                'orders_collection.merchant_id' => $merchantId,
                'orders_collection.status' => $orderStatus,
            ])->when($timeWhere, function ($ordersCollection) use ($timeWhere) {
                $ordersCollection->whereBetween('orders_collection.created_at', $timeWhere);
            })->select([
                DB::raw('count(orders_collection.id) as total_num'),
                DB::raw('sum(orders_collection.amount) as total_amount'),
            ])
            ->first()
            ->toArray();
        //代充金额数据
        $ordersCollection['total_amount'] = $ordersCollection['total_amount'] ?? 0;

        $ordersPay = OrdersPayModel::query()
            ->Join('orders_collection', 'orders_pay.inner_order_sn', '=', 'orders_collection.pay_inner_order_sn')
            ->where([
                'orders_pay.merchant_id' => $merchantId,
                'orders_collection.status' => $orderStatus,
            ])->when($timeWhere, function ($ordersPay) use ($timeWhere) {
                $ordersPay->whereBetween('orders_collection.created_at', $timeWhere);
            });

        //代付金额数据
        $ordersPayRes = $ordersPay->select([
            DB::raw('count(orders_collection.id) as total_num'),
            DB::raw('sum(orders_collection.amount) as total_amount'),
        ])->first()->toArray();
        $rpayNum = $ordersPay->where('orders_collection.order_type', OrdersCollectionModel::$orderTypes['third_pay']['value'])
            ->select([
                DB::raw('count(orders_collection.id) as rpay_num'),
                DB::raw('sum(orders_collection.amount) as rpay_amount'),
            ])->first()->toArray();
        $ordersPayRes['total_amount'] = $ordersPayRes['total_amount'] ?? 0;
        $ordersPayRes['rpay_num'] = $rpayNum['rpay_num'] ?? 0;
        $ordersPayRes['rpay_amount'] = $rpayNum['rpay_amount'] ?? 0;

        //获取代充支付数量（多个相同匹配的待提单为一单）
        $payCount = OrdersPayModel::query()
            ->where(['merchant_id' => $merchantId, 'status' => $orderStatus])
            ->when($timeWhere, function ($payObj) use ($timeWhere) {
                $payObj->whereBetween('created_at', $timeWhere);
            })
            ->count('id');
        $ordersPayRes['distinct_pay_num'] = $payCount;

        //第三方金额数据
        $thirdPay = PayConfigModel::query()
            ->where('status', PayConfigModel::STATUS_ENABLED)
            ->pluck('name', 'id')->toArray();
        if (!empty($thirdPay)) {
            $configIds = array_keys($thirdPay);
            $record = TransferRecordModel::query()
                ->where('merchant_id', $merchantId)
                ->where('status', TransferRecordModel::STATUS_SUCCESS_KEY)
                ->when($timeWhere, function ($record) use ($timeWhere) {
                    $record->whereBetween('created_at', $timeWhere);
                })->whereIn('pay_config_id', $configIds)
                ->groupBy(['pay_config_id'])
                ->select([
                    'pay_config_id',
                    DB::raw('any_value(merchant_id) as merchant_id'),
                    DB::raw('count(id) as total_num'),
                    DB::raw('sum(amount) as total_amount'),
                ])
                ->get()->each(function ($item) use ($thirdPay) {
                    $item->config_name = $thirdPay[$item->pay_config_id] ?? '';
                })->toArray();

        }
        return [
            'collection_list' => $ordersCollection,
            'pay_list' => $ordersPayRes,
            'third_pay_list' => $record ?? []
        ];
    }
};