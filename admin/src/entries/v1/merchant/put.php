<?php

use Admin\src\resource\MerchantFormatter;
use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\FinancialStatementsModel;
use Model\MerchantModel;
use Illuminate\Support\Arr;
use Logic\Admin\Log;
use Logic\PayAdmin\Api;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_MERCHANT;
    protected $moduleChild = '商户列表';
    protected $moduleFunName = '修改商户';

    public function run($id): MerchantFormatter
    {
        $params = $this->request->getParams();
        //不允许更改账号
        if (Arr::has($params, 'account')) {
            Arr::forget($params, 'account');
        }
        /**@var MerchantModel $row * */
        $row = MerchantModel::query()->findOrFail($id);
        $params['recharge_waiting_limit'] = 1; //暂不支持后台用户进行调整。
        $result = $row->update($params);
        $res = $this->changeMerchant(['merchant_account' => $row->account]);//同步缓存merchant
        $up = 1;
        if (isset($params['name'])) {
            $up = FinancialStatementsModel::query()->where('merchant_id', '=', $id)->update(['merchant_name' => $params['name']]);
        }
        if (!$result || !$up) {
            throw new Exception('Failed to update data');
        }
        if ($res['code']) {
            throw new Exception('Failed to change merchant:' . $res['message']);
        }
        $logArr = [
            'status' => AdminLogModel::STATUS_ON,
            'record' => $params,
            'uid2' => $id,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改商户：【' . $row->name . '】信息',
        ];
        $this->writeAdminLog($logArr);

        return MerchantFormatter::make($row);
    }

    private function changeMerchant(array $data)
    {
        $result = (new Api($this->ci))->changeMerchant($data);
        $logArr = [
            'status' => $result['code'] == 0 ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'remark' => '【' . $this->playLoad['admin_name'] . '】发起商户修改，请求结果信息为：' . $result['message'],
            'record' => $data,
        ];
        $this->writeAdminLog($logArr);
        return $result;
    }
};