<?php

namespace Logic\Admin;

use Logic\Define\CacheKey;
use Model\MerchantModel;
use Utils\Utils;

class WebsiteLogic
{
    public static function accountGenerate($account_arr = [])
    {

        $account = MerchantModel::withTrashed()->max('account');

        if (empty($account)) {
            $account = 100000000; //初始值
        } else {
            $account = $account + 1; //初始值
        }

        return $account;
    }
}