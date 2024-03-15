<?php

namespace Admin\src\resource;

use Model\OrdersCollectionModel;
use Model\OrdersPayModel;
use Illuminate\Database\Eloquent\Model;

class OrderPayFormatter extends ModelFormatter
{

    protected $model = OrdersPayModel::class;

    public static function prune(Model $model): void
    {
        /** @var  OrdersPayModel $model */
        $matchAmount = OrdersCollectionModel::query()->where(['pay_inner_order_sn' => $model->inner_order_sn])
            ->whereNotIn('status', [
                OrdersCollectionModel::$statusArr['pre_match']['value'],
                OrdersCollectionModel::$statusArr['canceled']['value'],
                OrdersCollectionModel::$statusArr['failed']['value'],
                OrdersCollectionModel::$statusArr['order_fail']['value']])
            ->sum('amount');
        $accounts = self::getAccount($model);
        $model->success_amount = $accounts['success_amount'] ?: 0.00;
        $model->handling_amount = $accounts['handling_amount'] ?: 0.00;
        $model->fail_amount = $accounts['fail_amount'] ?: 0.00;
        $model->show_thirdpay_status = $accounts['show_thirdpay_status'] ?: 0;
        $model->match_amount = $matchAmount ? sprintf("%.2f", $matchAmount) : 0;
        $model->status_label = self::orderStatus($model->status);
        OrderCollectionFormatter::whenLoaded($model, 'collection');
        OrderPayCollectionFormatter::whenLoaded($model, 'matched');
    }

    /**
     * 获取状态值
     * @param int $status
     * @return string
     */
    public static function orderStatus(int $status): string
    {
        $statusArr = [
            1 => 'to_be_matched',
            2 => 'to_be_uploaded',
            3 => 'upload_voucher_timeout',
            4 => 'to_be_confirmed',
            5 => 'pending_confirmation_timeout',
            6 => 'order_success',
            7 => 'order_exception',
            8 => 'in_progress',
            9 => 'order_failed',
            10 => 'order_turn_down',
            11 => 'order_turn_down',
        ];
        return app()->lang->text($statusArr[$status]);
    }

    /**
     * @param OrdersPayModel $model
     * @return int[]
     */
    public static function getAccount(OrdersPayModel $model): array
    {
        /** @var  OrdersCollectionModel $amountArr */
        $amountArr = OrdersCollectionModel::query()
            ->selectRaw('sum(if(order_type = 3,amount,0)) third_pay_amount,sum(if(status = 6,amount,0)) success_amount,sum(if(status = 9,amount,0)) fail_amount')
            ->where(['pay_inner_order_sn' => $model->inner_order_sn])
            ->first();

        $failAmount = $amountArr->fail_amount;
        $successAmount = $amountArr->success_amount;
        $thirdPayAmount = $amountArr->third_pay_amount;
        if ($thirdPayAmount > 0) {
            $failAmount = $thirdPayAmount;
        }
        $amount = $model->amount ?? 0.00;
        $matchTimeoutAmount = $model->match_timeout_amount ?? 0.00;
        $failAmount = $failAmount + $matchTimeoutAmount;
        if ($failAmount > $amount) {
            $failAmount = $amount;
        }
        $handlingAmount = $amount - $successAmount - $failAmount;
        //状态

        $showThirdPayStatus = 0;
        if ($model->status != OrdersCollectionModel::$statusArr['complete']['value'] &&
            $model->status != OrdersCollectionModel::$statusArr['reject']['value'] &&
            intval($handlingAmount) == 0
        ) {
            $showThirdPayStatus = 1;
        }

        return [
            'success_amount' => sprintf("%.2f", $successAmount),
            'handling_amount' => sprintf("%.2f", max($handlingAmount, 0)),
            'fail_amount' => sprintf("%.2f", $failAmount),
            'total_amount' => sprintf("%.2f", $amount),
            'show_thirdpay_status' => $showThirdPayStatus,
        ];
    }

}