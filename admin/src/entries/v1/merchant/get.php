<?php

use Logic\Admin\BaseController;
use Model\MerchantModel;
use Admin\src\resource\MerchantFormatter;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): MerchantFormatter
    {
        $row = MerchantModel::query()->with([
            'secrets:id,merchant_key,merchant_id',
            'payBalance:id,currency,balance,merchant_account',
            'collectionBalance:id,currency,balance,merchant_account',
            'financial' => function ($query) {
                return $query->groupBy(['merchant_id'])
                    ->select(
                        'merchant_id',
                        'merchant_name',
                        DB::raw("sum(payment_amount) as payment_amount"),
                        DB::raw("sum(payment_num) as payment_num"),
                        DB::raw("sum(payment_total) as payment_total"),
                        DB::raw("sum(recharge_amount) as recharge_amount"),
                        DB::raw("sum(recharge_num) as recharge_num"),
                        DB::raw("sum(recharge_total) as recharge_total"),
                        DB::raw("sum(trial_collection_num) as trial_collection_num"),
                        DB::raw("sum(trial_pay_num) as trial_pay_num")
                    );
            }
        ])
            ->latest('id')
            ->filter($this->request->getParams())
            ->paginate($this->pageSize);
        return MerchantFormatter::make($row);
    }
};