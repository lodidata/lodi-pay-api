<?php

namespace Logic\Admin;

use Model\FinancialStatementsModel;
use Model\MerchantModel;
use DB;
use Model\OrdersCollectionModel;
use Model\OrdersCollectionTrialModel;
use Model\OrdersPayModel;

class FinancialStatementsLogic
{

    public function index()
    {
        $status = OrdersCollectionModel::$statusArr['complete']['value'];
        MerchantModel::query()->select('id', 'name')
            ->chunk(50, function ($merchantObjList) use ($status) {
                foreach ($merchantObjList as $val) {
                    //代付订单数订单总金额
                    $paymentData = OrdersPayModel::query()
                        ->Join('orders_collection', 'orders_pay.inner_order_sn', '=', 'orders_collection.pay_inner_order_sn')
                        ->where(['orders_pay.merchant_id' => $val->id, 'orders_collection.status' => $status])
                        ->whereBetween('orders_collection.created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
                        ->select(
                            DB::raw('sum(orders_collection.amount) as amount'),
                            DB::raw('count(distinct(orders_pay.id)) as order_num'))
                        ->get()->toArray();
                    //总订充值订单数
                    $rechargeTotalData = OrdersCollectionModel::query()
                        ->where(['merchant_id' => $val->id])
                        ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
                        ->select(Db::raw('count(*) as order_num_total'))
                        ->get()->toArray();

                    //商户争议订单数
                    $trialTotalData = OrdersCollectionTrialModel::query()
                        ->leftJoin('orders_collection', 'orders_collection_trial.orders_collection_sn', '=', 'orders_collection.inner_order_sn')
                        ->where(['orders_collection.merchant_id' => $val->id])
                        ->whereBetween('orders_collection_trial.created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
                        ->select([
                            Db::raw('count(distinct(orders_collection.id)) as trial_collection_num'),
                            Db::raw('count(distinct(orders_collection.pay_inner_order_sn)) as trial_pay_num'),
                        ])
                        ->get()->toArray();

                    //代充订单数和总数
                    $rechargeData = OrdersCollectionModel::query()
                        ->where(['merchant_id' => $val->id, 'status' => $status])
                        ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
                        ->select([
                            Db::raw('sum(amount) as amount'),
                            Db::raw('count(id) as order_num')
                        ])
                        ->get()->toArray();

                    //总订提款订单数
                    $paymentTotalData = OrdersPayModel::query()->where(['merchant_id' => $val->id])
                        ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
                        ->select([
                            Db::raw('count(id) as order_num_total')
                        ])
                        ->get()->toArray();

                    $this->saveData($val, $paymentData, $rechargeData, $paymentTotalData, $rechargeTotalData, $trialTotalData);

                }
            });
    }

    /**
     * 保存|修改数据
     * @param MerchantModel $val
     * @param array $paymentData
     * @param array $rechargeData
     * @param array $paymentTotalData
     * @param array $rechargeTotalData
     * @param array $trialTotalData
     * @return void
     */
    public function saveData(MerchantModel $val, array $paymentData, array $rechargeData, array $paymentTotalData, array $rechargeTotalData, array $trialTotalData)
    {
        $financialModel = FinancialStatementsModel::query()->where(['merchant_id' => $val->id, 'finance_date' => date('Y-m-d', time())])->first();
        if (empty($financialModel)) {//新增
            $financialModel = new FinancialStatementsModel();
        }
        $financialModel->merchant_id = $val->id;
        $financialModel->merchant_name = $val->name;

        $financialModel->payment_num = (int)$paymentData[0]['order_num'];
        $financialModel->payment_amount = (float)$paymentData[0]['amount'];

        $financialModel->recharge_num = (int)$rechargeData[0]['order_num'];
        $financialModel->recharge_amount = (float)$rechargeData[0]['amount'];

        $financialModel->total_amount = (float)($paymentData[0]['amount'] + $rechargeData[0]['amount']);

        $financialModel->payment_total = (int)$paymentTotalData[0]['order_num_total'];
        $financialModel->recharge_total = (int)$rechargeTotalData[0]['order_num_total'];
        $financialModel->trial_collection_num = $trialTotalData[0]->trial_collection_num ?? 0;
        $financialModel->trial_pay_num = $trialTotalData[0]->trial_pay_num ?? 0;

        $financialModel->finance_date = date('Y-m-d', time());
        $financialModel->save();
        unset($financialModel);
    }
}