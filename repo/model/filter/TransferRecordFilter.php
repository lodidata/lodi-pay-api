<?php

namespace Model\filter;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class TransferRecordFilter extends BaseFilter
{

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

    //主要提现订单
    public function pay_inner_order_sn($query, $value)
    {
        $query->where('pay_inner_order_sn', $value);
    }

    public function bank_card_name($query, $value)
    {
        $query->where('bank_card_name', $value);
    }

    public function pay_config_id($query, $value)
    {
        $query->where('pay_config_id', $value);
    }

    public function created_at($query, $date)
    {
        if (isset($date[0]) && isset($date[1])) {
            $startDate = Carbon::parse($date[0])->startOfDay()->toDateTimeString();
            $endDate = Carbon::parse($date[1])->endOfDay()->toDateTimeString();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    }

    public function bank_card_account($query, $value)
    {
        $query->where('bank_card_account', $value);
    }


}