<?php

namespace Model\filter;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class PayConfigFilter extends BaseFilter
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

    public function created_at($query, $date)
    {
        if (isset($date[0]) && isset($date[1])) {
            $startDate = Carbon::parse($date[0])->startOfDay()->toDateTimeString();
            $endDate = Carbon::parse($date[1])->endOfDay()->toDateTimeString();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    }

    public function name($query, $value)
    {
        $query->where('name', $value);
    }

    public function merchant_account($query, $value)
    {
        $query->where('merchant_account', $value);
    }

    public function merchant_name($query, $value)
    {
        $query->whereHas('merchant', function ($query) use ($value) {
            $query->where('name', $value);
        });
    }

}