<?php

namespace financialStatements;

use Logic\Admin\BaseController;
use Model\FinancialStatementsModel;
use Utils\Utils;
use DB;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];
    protected $title = [
        'merchant_account' => '商户ID', 'merchant_name' => '商户名称', 'payment_num' => '代付笔数', 'payment_amount' => '代付金额',
        'payment_point' => '代付点位', 'recharge_num' => '代充笔数', 'recharge_amount' => '代充金额', 'recharge_point' => '代充点位',
        'total_amount' => '总计'
    ];

    public function run()
    {
        $row = FinancialStatementsModel::query()
            ->with(['merchant:id,account,name,pay_behalf_point,collection_pay_point'])
            ->filter($this->request->getParams())
            ->whereHas('merchant', function ($query){
                $query->whereNull('deleted_at');
            })
            ->latest('id')
            ->limit(50000)
            ->groupBy(['merchant_id'])
            ->select(
                'merchant_id',
                'merchant_name',
                DB::raw("sum(payment_num) as payment_num"),
                DB::raw("sum(payment_amount) as payment_amount"),
                DB::raw("sum(recharge_amount) as recharge_amount"),
                DB::raw("sum(total_amount) as total_amount"),
                DB::raw("sum(recharge_num) as recharge_num")
            )->get()->toArray();

        $file_name = '财务报表-' . date('Ymd');

        $new_data = [];
        if (!empty($row)) {
            foreach ($row as $k => $item) {
                $data['merchant_account'] = $item['merchant']['account'] ?? '';
                $data['merchant_name'] = $item['merchant_name'] ?: '';
                $data['payment_num'] = $item['payment_num'] ?: 0;
                $data['payment_amount'] = $item['payment_amount'] ?: '0.00';
                $data['payment_point'] = $item['merchant']['pay_behalf_point'] ?: 0;
                $data['recharge_num'] = $item['recharge_num'] ?: 0;
                $data['recharge_amount'] = $item['recharge_amount'] ?: '0.00';
                $data['recharge_point'] = $item['merchant']['collection_pay_point'] ?: 0;
                $data['total_amount'] = $item['total_amount'] ?: '0.00';
                $new_data[] = $data;
                unset($data);
                unset($row[$k]);
            }
        }
        Utils::exportExcel($file_name, $this->title, $new_data);
    }
};