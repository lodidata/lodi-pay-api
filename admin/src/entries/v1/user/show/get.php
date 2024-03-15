<?php

use Logic\Admin\BaseController;
use Model\UserModel;
use Model\OrdersCollectionModel;
use Model\OrdersPayModel;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run($id)
    {
        $row = UserModel::filter($this->request->getParams())->with([
            'tags' => function ($query) {
                return $query->select(['tag.id', 'name']);
            }, 'merchant' => function ($query) {
                return $query->select(['merchant.id', 'name', 'account']);
            },
            'ordersPayLast'=> function($query){
                return $query->select(['order_sn','created_at']);
            },
            'ordersCollectionLast'=> function($query){
                return $query->select(['order_sn','created_at']);
            },
        ])->findOrFail($id);
        //充值数据；
        $collection_num = OrdersCollectionModel::query()->where('user_id','=',$id)
            ->selectRaw('count(id) as id_num, sum(amount) as total_amount')->first()->toArray();
        //充值成功数据；
        $collection_suc_num = OrdersCollectionModel::query()->where(['user_id' => $id, 'status' => OrdersCollectionModel::$statusArr['complete']['value']])
            ->selectRaw('count(id) as id_suc_num, sum(amount) as total_suc_amount')->first()->toArray();
        //提现数据；
        $pay_num = OrdersPayModel::query()->where('user_id','=',$id)
            ->selectRaw('count(id) as id_num, sum(amount) as total_amount')->first()->toArray();
        //提现成功数据；
        $pay_suc_num = OrdersPayModel::query()->where(['user_id' => $id, 'status' => OrdersPayModel::$status['complete']['value']])
            ->selectRaw('count(id) as id_suc_num, sum(amount) as total_suc_amount')->first()->toArray();
        $data['collection_num'] = $collection_num['id_num'] ?? 0;
        $data['collection_total'] = $collection_num['total_amount'] ?? 0;
        $data['collection_suc_num'] = $collection_suc_num['id_suc_num'] ?? 0;
        $data['collection_suc_total'] = $collection_suc_num['total_suc_amount'] ?? 0;
        $data['collection_percent'] = $data['collection_num'] > 0 ? round(($data['collection_suc_num']/$data['collection_num'])*100, 2).'%' : '0%';
        $data['pay_num'] = $pay_num['id_num'] ?? 0;
        $data['pay_total'] = $pay_num['total_amount'] ?? 0;
        $data['pay_suc_num'] = $pay_suc_num['id_suc_num'] ?? 0;
        $data['pay_suc_total'] = $pay_suc_num['total_suc_amount'] ?? 0;
        $data['pay_percent'] = $data['pay_num'] > 0 ? round(($data['pay_suc_num']/$data['pay_num'])*100, 2).'%' : '0%';
        $row->setAttribute('num_data', $data);
        return \Admin\src\resource\UserDetailFormatter::make($row);
    }
};