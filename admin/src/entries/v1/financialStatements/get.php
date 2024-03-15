<?php

namespace financialStatements;

use Admin\src\resource\FinancialStatementsFormatter;
use Illuminate\Support\Arr;
use Logic\Admin\BaseController;
use Model\FinancialStatementsModel;
use DB;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
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
            ->groupBy(['merchant_id'])
            ->select(
                'merchant_id',
                'merchant_name',
                DB::raw("sum(payment_num) as payment_num"),
                DB::raw("sum(payment_amount) as payment_amount"),
                DB::raw("sum(recharge_amount) as recharge_amount"),
                DB::raw("sum(total_amount) as total_amount"),
                DB::raw("sum(recharge_num) as recharge_num")
                )
            ->paginate($this->pageSize);
        return FinancialStatementsFormatter::make($row);
    }

    /**
     * @param int $dayNum
     * @param string $today
     * @return array
     */
    public function getTime(int $dayNum, string $today): array
    {
        return [
            date("Y-m-d H:i:s", strtotime("- {$dayNum} days")),
            $today
        ];
    }

    /**
     * @param $param
     * @return array
     */
    public function getBetweenDate($param): array
    {
        $today = date("Y-m-d 23:59:59", time());
        //默认的
        $optionWhere = [];
        $options_type = $param['options_type'] ?? 0;
        //快捷方式
        switch (intval($options_type)) {
            case 1://今天
                $optionWhere = [
                    date("Y-m-d 00:00:00", time()),
                    $today,
                ];
                break;
            case 2:
                $optionWhere = $this->getTime(7, $today);
                break;
            case 3:
                $optionWhere = $this->getTime(14, $today);
                break;
            case 4:
                $optionWhere = $this->getTime(30, $today);
                break;
        }
        //输入的时间
        if (isset($param['created_at']) && !empty($param['created_at'])) {
            $optionWhere = [
                Arr::has($param, 'created_at.0') ? Arr::get($param, 'created_at.0') . ' 00:00:00' : $this->getTime(30, $today)[0],
                Arr::has($param, 'created_at.1') ? Arr::get($param, 'created_at.1') . ' 23:59:59' :
                    $today,
            ];
        }

        return $optionWhere;
    }

};