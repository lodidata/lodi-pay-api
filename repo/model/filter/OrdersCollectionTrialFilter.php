<?php /** @noinspection PhpUnused */

namespace Model\filter;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class OrdersCollectionTrialFilter extends BaseFilter
{
    public $relations = [
        'orderCollection' => [
            'status',
            'pay_inner_order_sn',
            'inner_order_sn',
            'order_sn',
            'amount',
            'user_account',
            'merchant_id',
        ],
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

    //充值订单号(平台内部自己的)
    public function inner_order_sn($query, $value)
    {
        $query->where('inner_order_sn', $value);
    }

    //充值商户订单号
    public function order_sn($query, $value)
    {
        $query->where('orders_collection.order_sn', $value);
    }

    //充值账户
    public function user_account($query, $value)
    {
        $query->where('user_account', $value);
    }

    //主提现订单(平台内部自己的)
    public function pay_inner_order_sn($query, $value)
    {
        $query->where('pay_inner_order_sn', $value);
    }


    //金额
    public function amount($query, $value)
    {
        $query->where('amount', '>=', $value);
    }

    //商户订单号
    public function pay_order_sn($query, $value)
    {
        $query->whereHas('orderCollection.orderPay', function ($query) use ($value) {
            $query->where('order_sn', $value);
        });
    }

    //提款账户
    public function pay_user_account($query, $value)
    {
        $query->whereHas('orderCollection.orderPay', function ($query) use ($value) {
            $query->where('user_account', $value);
        });
    }

    public function merchant_id($query, $value)
    {
        $query->where('merchant_id', $value);
    }

    //争议时间
    public function created_at($query, $date)
    {
        if (isset($date[0]) && isset($date[1])) {
            $startDate = Carbon::parse($date[0])->startOfDay()->toDateTimeString();
            $endDate = Carbon::parse($date[1])->endOfDay()->toDateTimeString();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    }


}