<?php

namespace Model\filter;

use Illuminate\Support\Arr;

class OrdersCollectionPayFilter extends BaseFilter
{
    public $relations = [
        'orderPay' => ['orders_pay_sn'],
    ];

    public function __construct($query, $input, $relationsEnabled = true)
    {
        parent::__construct($query, $input, $relationsEnabled);
        if (!Arr::has($this->input, 'created_at')) {
            $this->input = array_merge($this->input, ['created_at' => []]);
        }

    }

    public function status($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('status', $value);
        } else {
            $query->where('status', $value);
        }
    }

    //根据商户号
    public function merchant_id($query, $value)
    {
        $query->where('merchant_id', $value);
    }

    //提款订单号
    public function inner_orders_pay_sn($query, $value)
    {
        $query->where('orders_pay_sn', $value);
    }

    //站点订单号
    public function orders_pay_sn($query, $value)
    {
        $query->where('order_sn', $value);
    }

    //匹配时间
    public function created_at($query, $date)
    {
        $startDate = Arr::get($date, 0) ? Arr::get($date, 0) . ' 00:00:00' : date('Y-m-d H:i:s', strtotime('-30 days'));
        $endDate = Arr::get($date, 1) ? Arr::get($date, 1) . ' 23:59:59' : date('Y-m-d H:i:s');
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }


}