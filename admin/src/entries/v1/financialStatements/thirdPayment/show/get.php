<?php

use Model\PayConfigModel;
use Model\TransferRecordModel;
use Logic\Admin\BaseController;
use Lib\Validate\Admin\FinancialStatementsValidate;
use Admin\src\resource\TransferRecordFormatter;

return new class() extends BaseController {
    //前置方法
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run($id)
    {
        $params = $this->request->getParams();
        (new FinancialStatementsValidate())->paramsCheck('third', $this->request, $this->response); // 批量更新请求参数

        $res = TransferRecordModel::query()->where(['merchant_id' => $id, 'pay_config_id' => $params['config_id'], 'status' => TransferRecordModel::STATUS_SUCCESS_KEY])
            ->filter($params)
            ->with(['orderPay:order_sn,inner_order_sn', 'merchant:id,account,name', 'payConfig:type,id'])
            ->paginate($this->pageSize);
        return TransferRecordFormatter::make($res);
    }
};