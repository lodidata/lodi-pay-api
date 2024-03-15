<?php

namespace Admin\src\resource;

use Model\OrdersCollectionPayModel;
use Model\OrdersPayModel;
use service\OrdersService;

class OrderDetailFormatter extends ModelFormatter
{

    protected $model = OrdersPayModel::class;

    public static function prune(\Illuminate\Database\Eloquent\Model $model): void
    {

//        $model->match_amount = $model->amount - $model->balance;
        $model->pay_status = self::orderStatus($model->status);
        $childList = $model->child;
        foreach ($childList as $k => $v) {
            $model->child[$k]->child_pay_status = self::orderStatus($v->status);
            $model->child[$k]->attachment = json_decode($model->child[$k]->url, true);
            unset($model->child[$k]['status']);
            unset($model->child[$k]['url']);
        }
        $model->setHidden(['status']);
    }

    /**
     * 获取状态值
     * @param int $status
     * @return string
     */
    public static function orderStatus(int $status): string
    {
//        1待匹配 2进行中 3待上传凭证 4上传凭证超时 5待确认 6待确认超时 7订单成功 8订单失败
        $statusArr = [
            1 => '待匹配',
            2 => '进行中',
            3 => '待上传凭证',
            4 => '上传凭证超时',
            5 => '待确认',
            6 => '待确认超时',
            7 => '订单成功',
            8 => '订单失败'
        ];
        return $statusArr[$status];
    }

}