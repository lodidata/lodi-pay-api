<?php

namespace Model\filter;

use Illuminate\Support\Arr;

class FinancialStatementsFilter extends BaseFilter
{
    public function merchant_id($query, $value) {
        $query->where('merchant_id', $value);
    }

    public function merchant_name($query, $value) {
        $query->where('merchant_name', $value);
    }

    public function created_at($query, $date)
    {
        $startDate = Arr::get($date, 0) ?Arr::get($date, 0) .' 00:00:00' : date('Y-m-d 00:00:00', time());
        $endDate = Arr::get($date, 1) ? Arr::get($date, 1) . ' 23:59:59' : date('Y-m-d H:i:s');

        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function finance_date($query, $date)
    {
        $startDate = Arr::get($date, 0) ?Arr::get($date, 0) : date('Y-m-d', time());
        $endDate = Arr::get($date, 1) ? Arr::get($date, 1) : date('Y-m-d');
        $query->whereBetween('finance_date', [$startDate, $endDate]);
    }
}