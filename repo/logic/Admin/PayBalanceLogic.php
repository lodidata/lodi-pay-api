<?php

namespace Logic\Admin;

use Exception;
use Model\MerchantCollectionBalanceModel;
use Model\MerchantPayBalanceModel;
use DB;
use Slim\Container;

class PayBalanceLogic
{
    public static function chargeBalance($id): array
    {
        $data = ['code' => 0, 'msg' => 'ok'];
        if (!$id) {
            $data['msg'] = 'id必传';
            $data['code'] = 1;
            return $data;
        }
        /**@var MerchantCollectionBalanceModel $info * */
        $info = MerchantCollectionBalanceModel::query()->find($id);
        if (!$info) {
            $data['msg'] = '代收数据不存在';
            $data['code'] = 1;
            return $data;
        }
        /**@var MerchantPayBalanceModel $payInfo * */
        $payInfo = MerchantPayBalanceModel::query()->where(['merchant_account' => $info->merchant_account, 'currency' => $info->currency])->first();
        if (!$payInfo) {
            $data['msg'] = '代付数据不存在';
            $data['code'] = 1;
            return $data;
        }
        DB::pdo()->beginTransaction();
        try {
            // 更新记录
            $res = MerchantCollectionBalanceModel::query()->where('id', '=', $info->id)->update(['balance' => 0]);
            $payRes = MerchantPayBalanceModel::query()->where('id', '=', $payInfo->id)->update(['balance' => $payInfo->balance + $info->balance]);
            if (!$res || !$payRes) {
                throw new Exception('数据金额更新失败', 2);
            }

            $slimObj = new Container();
            //代充金额记录
            $logId = (new Log($slimObj))->balanceLog([
                'merchant_account' => $info->merchant_account,
                'transaction_type' => 5,
                'order_type' => 1,
                'change_after' => 0.00,
                'change_before' => $info->balance ?: 0.00,
                'remark' => '钱包代收脚本自动转入代付金额-代收扣减',
                'admin_id' => 0,
                'currency' => $info->currency,
            ]);
            //代收金额记录
            (new Log($slimObj))->balanceLog([
                'merchant_account' => $info->merchant_account,
                'transaction_type' => 5,
                'order_type' => 2,
                'change_after' => ($payInfo->balance + $info->balance) ?: 0.00,
                'change_before' => $payInfo->balance ?: 0.00,
                'remark' => '钱包代收脚本自动转入代付金额-代付增加',
                'admin_id' => 0,
                'currency' => $info->currency,
            ]);
            if (!$logId) {
                throw new Exception('新增日志失败', 3);
            }
            DB::pdo()->commit();
            return $data;
        } catch (Exception $e) {
            DB::pdo()->rollBack();
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
    }
}