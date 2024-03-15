<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\MerchantPayBalanceModel;
use Model\MerchantCollectionBalanceModel;
use Lib\Validate\Admin\MerchantPayBalanceValidate;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_MERCHANT;
    protected $moduleChild = '商户列表';
    protected $moduleFunName = '修改钱包金额';

    //代付钱包充值
    public function run()
    {
        $params = $this->request->getParams();
        (new MerchantPayBalanceValidate())->paramsCheck('recharge', $this->request, $this->response); // 校验参数
        /**@var MerchantPayBalanceModel $payBalanceObj * */
        $payBalanceObj = MerchantPayBalanceModel::query()
            ->where(['merchant_account' => $params['merchant_account'], 'currency' => $params['currency']])
            ->first();
        if (!$payBalanceObj) {
            return $this->lang->set(152);
        }
        $mag = '钱包代收手动转入代付金额-代付增加';
        $payChangeBefore = $payBalanceObj->balance;
        $payChangeAfter = $payBalanceObj->balance + $params['amount'];

        DB::pdo()->beginTransaction();
        try {
            if (!empty($params['type'])) {
                /**@var MerchantCollectionBalanceModel $collectionBalanceObj * */
                $collectionBalanceObj = MerchantCollectionBalanceModel::query()
                    ->where([
                        'merchant_account' => $params['merchant_account'],
                        'currency' => $params['currency']
                    ])
                    ->first();
                if (!$collectionBalanceObj) {
                    throw new Exception('Merchant wallet not created');
                }
                if ($params['amount'] > $collectionBalanceObj->balance) {
                    throw new Exception('The transferred payment amount cannot be greater than the collection balance');
                }
                $collectionChangeBefore = $collectionBalanceObj->balance;
                $collectionChangeAfter = $collectionBalanceObj->balance - $params['amount'];
                //更新金额
                $resDec = $collectionBalanceObj->decrement('balance', $params['amount']);
                $resInc = $payBalanceObj->increment('balance', $params['amount']);
                if (!$resDec || !$resInc) {
                    throw new Exception('Failed to update amount data');
                }
                //代收金额记录
                $logId = (new Log($this->ci))->balanceLog([
                    'merchant_account' => $params['merchant_account'],
                    'transaction_type' => 4,
                    'order_type' => 1,
                    'change_after' => $collectionChangeAfter ?: 0.00,
                    'change_before' => $collectionChangeBefore ?: 0.00,
                    'remark' => '钱包代收手动转入代付金额-代收扣减',
                    'admin_id' => $this->playLoad['admin_id'],
                    'currency' => $params['currency'],
                ]);
                if (!$logId) {
                    throw new Exception('Failed to add log！');
                }
            } else {
                $resInc = $payBalanceObj->increment('balance', $params['amount']);
                if (!$resInc) {
                    throw new Exception('Failed to update amount data！');
                }
                $mag = '钱包手动代付充值';
            }

            //代充金额转账记录
            $logId = (new Log($this->ci))->balanceLog([
                'merchant_account' => $params['merchant_account'],
                'transaction_type' => 4,
                'order_type' => 2,
                'change_after' => $payChangeAfter ?: 0.00,
                'change_before' => $payChangeBefore ?: 0.00,
                'remark' => $mag,
                'admin_id' => $this->playLoad['admin_id'],
                'currency' => $params['currency'],
            ]);
            if (!$logId) {
                throw new Exception('Failed to add log！');
            }
            DB::pdo()->commit();
        } catch (Exception $e) {
            DB::pdo()->rollBack();
            $this->writeLog(false, $params);
            throw new Exception($e->getMessage());
        }
        $this->writeLog(true, $params);
        return $this->lang->set(0);
    }

    private function writeLog(bool $result, array $params)
    {
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改钱包金额',
        ];
        $this->writeAdminLog($logArr);
    }

};