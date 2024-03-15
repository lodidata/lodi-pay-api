<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

use Admin\src\resource\MerchantFormatter;
use Logic\Admin\BaseController;
use Logic\Admin\WebsiteLogic;
use Model\AdminLogModel;
use Model\MerchantModel;
use Model\CurrencyModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_MERCHANT;
    protected $moduleChild = '商户列表';
    protected $moduleFunName = '新增商户';

    public function run(): MerchantFormatter
    {
        $params = $this->request->getParams();
        if (empty($params['currency_id'])) {
            throw new exception('Please select a currency');
        }
        //secrets
        $merchantSecret = opensslPkeyNew();
        $platformSecret = opensslPkeyNew();

        /**@var CurrencyModel $currency * */
        $currency = CurrencyModel::query()->findOrFail($params['currency_id']);

        $secrets['merchant_key'] = $merchantSecret['private_key'];
        $secrets['merchant_public_key'] = $merchantSecret['public_key'];
        $secrets['secret_key'] = $platformSecret['private_key'];
        $secrets['public_key'] = $platformSecret['public_key'];
        $params['account'] = WebsiteLogic::accountGenerate();
        $params['recharge_waiting_limit'] = 1; //暂不支持后台用户进行调整。
        $merchant = MerchantModel::query()->create($params);
        $balance['currency'] = $currency->currency_type;
        $merchant->payBalance()->create($balance);
        $merchant->collectionBalance()->create($balance);
        $merchant->secrets()->create($secrets);
        $financial['merchant_name'] = $params['name'] ?? '';
        $financial['finance_date'] = date('Y-m-d');
        $merchant->financial()->create($financial);

        $logArr = [
            'status' => AdminLogModel::STATUS_ON,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】新增商户：【' . $params['name'] . '】',
        ];
        $this->writeAdminLog($logArr);
        return MerchantFormatter::make($merchant);
    }
};