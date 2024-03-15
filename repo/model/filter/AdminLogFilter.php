<?php

namespace Model\filter;

use Carbon\Carbon;

class AdminLogFilter extends BaseFilter
{
    public function ip($query, $value) {
        $query->where('ip', $value);
    }

    public function uname($query, $value) {
        $query->where('admin_name', 'like', '%'.$value.'%');
    }

    public function uname2($query, $value) {
        $query->where('uname2', 'like', '%'.$value.'%');
    }

    public function module($query, $value)
    {
        $query->where('module', $value);
    }

    //    状态
    public function status($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('status', $value);
        } else {
            $query->where('status', $value);
        }
    }

    //创建时间
    public function created_at($query, $date)
    {
        if (!empty($date[0]) && !empty($date[1])) {
            $startDate = Carbon::parse($date[0])->startOfDay()->toDateTimeString();
            $endDate = Carbon::parse($date[1])->endOfDay()->toDateTimeString();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    }
}