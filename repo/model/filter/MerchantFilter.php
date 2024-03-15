<?php
namespace Model\filter;
use Illuminate\Support\Arr;
class MerchantFilter extends BaseFilter
{
    public function name($query, $value) {
        $query->where('name', 'like', '%'.$value.'%');
    }

    public function id($query, $value)
    {
        return $query->where('id' , $value);
    }
    public function created_at($query, $date)
    {
        $startDate = Arr::get($date, 0) ?Arr::get($date, 0) .' 00:00:00' : date('Y-m-d H:i:s', strtotime('-30 days'));
        $endDate = Arr::get($date, 1) ? Arr::get($date, 1) . ' 23:59:59' : date('Y-m-d H:i:s');

        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function is_pay_behalf($query, $value)
    {
        return $query->where('is_pay_behalf' , $value);
    }

    public function is_collection_behalf($query, $value)
    {
        return $query->where('is_collection_behalf' , $value);
    }


}