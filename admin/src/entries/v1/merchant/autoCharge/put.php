<?php

use Logic\Admin\BaseController;
use Lib\Validate\Admin\MerchantCollectionBalanceValidate;
use Model\AdminLogModel;
use Model\MerchantCollectionBalanceModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_MERCHANT;
    protected $moduleChild = '商户列表';
    protected $moduleFunName = '修改钱包设置';

    //代付钱包充值
    public function run()
    {
        $params = $this->request->getParams();
        (new MerchantCollectionBalanceValidate())->paramsCheck('recharge', $this->request, $this->response); // 校验参数

        /**@var MerchantCollectionBalanceModel $payBalance * */
        $payBalance = MerchantCollectionBalanceModel::query()
            ->where([
                'merchant_account' => $params['merchant_account'],
                'currency' => $params['currency']
            ])
            ->first();

        if (!$payBalance) {
            return $this->lang->set(126);
        }

        $payBalance->is_auto = $params['is_auto'];
        $payBalance->limit_amount = $params['amount'];
        $result = $payBalance->save();

        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改钱包设置信息' . $this->getMsg($payBalance, $params),
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }

    /**
     * 获取具体日志
     * @param $CollectionBalanceObj
     * @param $data
     * @return string
     */
    protected function getMsg($CollectionBalanceObj, $data): string
    {
        $msg = '';
        if(!$CollectionBalanceObj){
            return $msg;
        }
        $CollectionBalanceObj = $CollectionBalanceObj->toArray();
        if($CollectionBalanceObj['is_auto'] != intval($data['is_auto'])){
            $msg .= '自动转入由【'.MerchantCollectionBalanceModel::AUTO_ARR[$CollectionBalanceObj['is_auto']].'】改为【'.MerchantCollectionBalanceModel::AUTO_ARR[$data['is_auto']].'】|';
        }
        if($CollectionBalanceObj['limit_amount'] != $data['amount']){
            $msg .= '设定金额由【'.$CollectionBalanceObj['limit_amount'].'】改为【'.$data['amount'].'】';
        }
        return $msg;
    }
};